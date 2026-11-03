<?php
// src/ML/PurokAssessmentEngine.php

namespace App\ML;

use App\Core\Database;
use App\Models\Supply;
use PDO;

class PurokAssessmentEngine {
    private static string $rfModelPath = __DIR__ . '/../../ml/data/active_purok_model.json';

    /**
     * Run full assessment on all puroks in Barangay Hinaplanon
     *
     * @param bool $forceRuleBased If true, skips ML model and uses deterministic rule-based prioritizer
     * @return array Array of assessed purok records ranked 1 to N
     */
    public static function runAssessment(bool $forceRuleBased = false): array {
        $db = Database::getInstance();

        // 1. Fetch all Puroks
        $puroks = $db->query("SELECT * FROM purok ORDER BY purok_id ASC")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($puroks)) {
            return [];
        }

        // 2. Fetch all verified Field Reports grouped by purok_id
        $verifiedReports = $db->query("
            SELECT * FROM field_reports 
            WHERE status = 'Verified' AND (deleted_at IS NULL)
            ORDER BY report_time DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $reportsByPurok = [];
        foreach ($verifiedReports as $fr) {
            $reportsByPurok[(int)$fr['purok_id']][] = $fr;
        }

        // 3. Check for trained Random Forest Model
        $rfModel = null;
        $assessmentMethod = 'Configurable Rule-Based Prioritization Engine';
        $modelVersion = 'v1.0-rule-prioritizer';

        if (!$forceRuleBased && file_exists(self::$rfModelPath)) {
            try {
                $rfModel = new RandomForestClassifier();
                $rfModel->loadFromJson(self::$rfModelPath);
                $assessmentMethod = 'Machine Learning (Random Forest Ensemble - 15 Trees)';
                $modelVersion = 'v2.1-random-forest-ensemble';
            } catch (\Exception $e) {
                $rfModel = null;
            }
        }

        // 4. Load Current Supply Inventory
        $supplies = Supply::all();
        $inventoryMap = [];
        foreach ($supplies as $s) {
            $inventoryMap[$s['supply_type']][] = $s;
        }

        // Get total remaining stock for key relief categories
        $foodRemaining = 0;
        foreach ($supplies as $s) {
            if ($s['supply_type'] === 'Relief Goods') {
                $foodRemaining += (int)$s['supply_remaining'];
            }
        }
        $waterRemaining = 0;
        foreach ($supplies as $s) {
            if ($s['supply_type'] === 'Clean Water') {
                $waterRemaining += (int)$s['supply_remaining'];
            }
        }
        $hygieneRemaining = 0;
        foreach ($supplies as $s) {
            if ($s['supply_type'] === 'Hygiene Kits') {
                $hygieneRemaining += (int)$s['supply_remaining'];
            }
        }
        $shelterRemaining = 0;
        foreach ($supplies as $s) {
            if ($s['supply_type'] === 'Shelter Kits') {
                $shelterRemaining += (int)$s['supply_remaining'];
            }
        }

        $evaluatedPuroks = [];

        // 5. Evaluate each purok
        foreach ($puroks as $p) {
            $pId = (int)$p['purok_id'];
            $pReports = $reportsByPurok[$pId] ?? [];
            $unmetNeeds = max(0, (int)($p['households'] ?? 0) - (int)($p['historical_beneficiaries'] ?? 0));

            // A. Rule-based evaluation (provides baseline composite score and sub-scores)
            $ruleResult = RuleBasedPrioritizer::evaluate($p, $pReports, $unmetNeeds);
            $compositeScore = $ruleResult['composite_score'];
            $priorityLevel = $ruleResult['priority_level'];
            $confidence = $ruleResult['confidence'];
            $subScores = $ruleResult['scores'];

            // B. ML Ensemble Prediction if active
            $mlClass = null;
            if ($rfModel !== null) {
                try {
                    $features = FeatureExtractor::extract([
                        'disaster_type' => 'Flood',
                        'severity_level' => $p['flood_hazard_exposure'] ?? 'Medium',
                        'is_urgent' => ($p['flood_hazard_exposure'] === 'Critical' || (int)($p['historical_totally_damaged'] ?? 0) > 0) ? 1 : 0,
                        'purok_risk_level' => $p['risk_classification'] ?? 'Medium',
                        'affected_households' => (float)($p['households'] ?? 10),
                        'affected_population' => (float)($p['population'] ?? 40),
                        'totally_damaged_houses' => (float)($p['historical_totally_damaged'] ?? 0),
                        'partially_damaged_houses' => (float)($p['historical_partially_damaged'] ?? 0),
                        'vulnerable_members' => (float)(($p['senior_citizens'] ?? 0) + ($p['pwds'] ?? 0)),
                        'road_accessibility' => !empty($pReports) ? ($pReports[0]['road_accessibility'] ?? 'Passable') : 'Passable'
                    ]);

                    $mlPred = $rfModel->predict($features);
                    $mlClass = $mlPred['predicted_priority'];
                    $confidence = $mlPred['confidence'];
                } catch (\Exception $ex) {
                    $mlClass = null;
                }
            }

            $hh = max(1, (int)($p['households'] ?? 10));
            $totDmg = (int)($p['historical_totally_damaged'] ?? 0);

            // Blending & Calibration: Prioritize by composite risk score with ML validation
            if ($compositeScore >= 75.0 || ($subScores['flood_exposure'] >= 80 && $totDmg >= 2)) {
                $priorityLevel = 'Critical';
            } elseif ($compositeScore >= 52.0 || ($subScores['flood_exposure'] >= 80)) {
                $priorityLevel = ($mlClass === 'Critical' && $compositeScore >= 68.0) ? 'Critical' : 'High';
            } elseif ($compositeScore >= 32.0) {
                $priorityLevel = ($mlClass === 'High' && $compositeScore >= 48.0) ? 'High' : 'Moderate';
            } else {
                $priorityLevel = 'Low';
            }

            // C. Multiplier based on verified priority level
            $multipliers = [
                'Critical' => 1.10,
                'High'     => 1.00,
                'Moderate' => 0.75,
                'Low'      => 0.50
            ];
            $mult = $multipliers[$priorityLevel] ?? 0.75;

            // Calculate Demand per supply
            $calcRiceKg = (int)ceil($hh * 5 * $mult);
            $calcCannedTins = (int)ceil($hh * 4 * $mult);
            $calcWaterBottles = (int)ceil($hh * 2 * $mult);
            $calcHygieneKits = (int)ceil($hh * 1 * $mult);
            $calcBlankets = (int)ceil(($totDmg * 2 + ceil($hh * 0.15)) * $mult);

            // Stock capping and shortage tracking
            $isStockCapped = 0;
            $shortageNotice = "";

            // Check if food pack inventory can support this purok's demand
            // Standard food pack covers 5kg rice + 4 cans
            $foodPackDemand = (int)ceil($calcRiceKg / 5);
            $recommendedFoodPacks = $foodPackDemand;
            if ($foodPackDemand > $foodRemaining && $foodRemaining > 0) {
                $isStockCapped = 1;
                $recommendedFoodPacks = $foodRemaining;
                $shortageNotice = "Calculated demand is {$foodPackDemand} food packs ({$calcRiceKg} kg rice), capped to current available stock ({$foodRemaining} packs). Immediate ICDRRMO replenishment requested.";
            }

            $allocation = [
                'rice_kg' => $calcRiceKg,
                'canned_tins' => $calcCannedTins,
                'water_bottles' => $calcWaterBottles,
                'hygiene_kits' => $calcHygieneKits,
                'blankets' => $calcBlankets,
                'shortage_notice' => $shortageNotice
            ];

            // D. Explainable AI Reasoning
            $xai = ExplainableAiEngine::explain(
                $p,
                $priorityLevel,
                $compositeScore,
                $subScores,
                $allocation,
                $pReports
            );

            $evaluatedPuroks[] = [
                'purok_id' => $pId,
                'purok_name' => $p['purok_name'],
                'risk_classification' => $p['risk_classification'],
                'risk_zone_color' => $p['risk_zone_color'],
                'latitude' => (float)$p['latitude'],
                'longitude' => (float)$p['longitude'],
                'priority_level' => $priorityLevel,
                'composite_risk_score' => $compositeScore,
                'assessment_method' => $assessmentMethod,
                'model_version' => $modelVersion,
                'confidence_score' => $confidence,
                'affected_households' => $hh,
                'affected_population' => (int)($p['population'] ?? ($hh * 4)),
                'vulnerable_members' => (int)(($p['senior_citizens'] ?? 0) + ($p['pwds'] ?? 0)),
                'totally_damaged_houses' => $totDmg,
                'partially_damaged_houses' => (int)($p['historical_partially_damaged'] ?? 0),
                'flood_exposure_score' => (float)($subScores['flood_exposure'] ?? 50),
                'damage_severity_score' => (float)($subScores['housing_damage'] ?? 20),
                'unmet_needs_score' => (float)($subScores['unmet_needs'] ?? 20),
                'recommended_rice_kg' => $calcRiceKg,
                'recommended_canned_tins' => $calcCannedTins,
                'recommended_water_bottles' => $calcWaterBottles,
                'recommended_hygiene_kits' => $calcHygieneKits,
                'recommended_blankets' => $calcBlankets,
                'stock_capped' => $isStockCapped,
                'shortage_notice' => $shortageNotice,
                'explanation_narrative' => $xai['explanation_narrative'],
                'factor_breakdown' => json_encode($xai['breakdown']),
                'data_completeness' => $xai['data_completeness'],
                'evacuation_haven' => $xai['evacuation_haven']
            ];
        }

        // 6. Sort Puroks by Composite Risk Score descending to determine Priority Ranking (1 to N)
        usort($evaluatedPuroks, function ($a, $b) {
            if ($b['composite_risk_score'] == $a['composite_risk_score']) {
                return $b['affected_households'] <=> $a['affected_households'];
            }
            return ($b['composite_risk_score'] > $a['composite_risk_score']) ? 1 : -1;
        });

        // 7. Persist or Update Rankings in purok_assessments
        $delStmt = $db->prepare("DELETE FROM purok_assessments");
        $delStmt->execute();

        $insStmt = $db->prepare("
            INSERT INTO purok_assessments (
                purok_id, priority_rank, priority_level, composite_risk_score,
                assessment_method, model_version, confidence_score,
                affected_households, affected_population, vulnerable_members,
                totally_damaged_houses, partially_damaged_houses,
                flood_exposure_score, damage_severity_score, unmet_needs_score,
                recommended_rice_kg, recommended_canned_tins, recommended_water_bottles,
                recommended_hygiene_kits, recommended_blankets,
                allocation_status, stock_capped, shortage_notice,
                explanation_narrative, factor_breakdown, data_completeness
            ) VALUES (
                :purok_id, :rank, :priority, :score,
                :method, :version, :confidence,
                :hh, :pop, :vuln,
                :tot_dmg, :part_dmg,
                :flood_score, :damage_score, :unmet_score,
                :rice, :canned, :water,
                :hygiene, :blankets,
                'Recommended', :stock_capped, :shortage_notice,
                :explanation, :breakdown, :completeness
            )
        ");

        foreach ($evaluatedPuroks as $rankIdx => &$item) {
            $rank = $rankIdx + 1;
            $item['priority_rank'] = $rank;

            $insStmt->execute([
                ':purok_id' => $item['purok_id'],
                ':rank' => $rank,
                ':priority' => $item['priority_level'],
                ':score' => $item['composite_risk_score'],
                ':method' => $item['assessment_method'],
                ':version' => $item['model_version'],
                ':confidence' => $item['confidence_score'],
                ':hh' => $item['affected_households'],
                ':pop' => $item['affected_population'],
                ':vuln' => $item['vulnerable_members'],
                ':tot_dmg' => $item['totally_damaged_houses'],
                ':part_dmg' => $item['partially_damaged_houses'],
                ':flood_score' => $item['flood_exposure_score'],
                ':damage_score' => $item['damage_severity_score'],
                ':unmet_score' => $item['unmet_needs_score'],
                ':rice' => $item['recommended_rice_kg'],
                ':canned' => $item['recommended_canned_tins'],
                ':water' => $item['recommended_water_bottles'],
                ':hygiene' => $item['recommended_hygiene_kits'],
                ':blankets' => $item['recommended_blankets'],
                ':stock_capped' => $item['stock_capped'],
                ':shortage_notice' => $item['shortage_notice'],
                ':explanation' => $item['explanation_narrative'],
                ':breakdown' => $item['factor_breakdown'],
                ':completeness' => $item['data_completeness']
            ]);
        }

        return $evaluatedPuroks;
    }

    /**
     * Recalculate priority assessment when an approved field report arrives
     */
    public static function recalculateForPurok(int $purokId): array {
        // Re-evaluates entire ranking to maintain consistent 1-to-N ordering
        return self::runAssessment();
    }

    /**
     * Fetch all current purok assessments joined with purok names and hazard zones
     */
    public static function getLatestAssessments(): array {
        $db = Database::getInstance();
        $res = $db->query("
            SELECT pa.*, p.purok_name, p.risk_classification, p.risk_zone_color, 
                   p.latitude, p.longitude, p.evacuation_center, p.flood_hazard_exposure
            FROM purok_assessments pa
            JOIN purok p ON pa.purok_id = p.purok_id
            ORDER BY pa.priority_rank ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($res)) {
            // First time run
            return self::runAssessment();
        }
        return $res;
    }
}
