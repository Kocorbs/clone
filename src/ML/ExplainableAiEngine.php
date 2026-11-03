<?php
// src/ML/ExplainableAiEngine.php

namespace App\ML;

class ExplainableAiEngine {
    /**
     * Generate Explainable AI breakdown and natural language reasoning
     *
     * @param array $purok Purok master record
     * @param string $priority Priority Level (Critical, High, Moderate, Low)
     * @param float $compositeScore Composite risk score
     * @param array $contributingScores Sub-scores or feature contributions
     * @param array $allocation Recommended supply amounts
     * @param array $fieldReports Active verified field status reports
     * @return array Structured XAI metadata
     */
    public static function explain(
        array $purok,
        string $priority,
        float $compositeScore,
        array $contributingScores,
        array $allocation,
        array $fieldReports = []
    ): array {
        $pName = $purok['purok_name'];
        $households = (int)($purok['affected_households'] ?? ($purok['households'] ?? 0));
        $pop = (int)($purok['affected_population'] ?? ($purok['population'] ?? 0));
        $exposure = $purok['flood_hazard_exposure'] ?? $purok['risk_classification'] ?? 'Moderate';
        $totDmg = (int)($purok['totally_damaged_houses'] ?? ($purok['historical_totally_damaged'] ?? 0));
        $partDmg = (int)($purok['partially_damaged_houses'] ?? ($purok['historical_partially_damaged'] ?? 0));
        $seniors = (int)($purok['senior_citizens'] ?? 0);
        $pwds = (int)($purok['pwds'] ?? 0);
        $fourPs = (int)($purok['four_ps_members'] ?? 0);

        // Check recent verified field reports
        $recentReportsCount = 0;
        $roadIssue = null;
        $activeCasualties = 0;
        foreach ($fieldReports as $fr) {
            if (($fr['status'] ?? '') === 'Verified') {
                $recentReportsCount++;
                $activeCasualties += (int)($fr['casualties_count'] ?? 0);
                if (in_array($fr['road_accessibility'] ?? '', ['Flooded', 'Not Passable / Blocked'], true)) {
                    $roadIssue = $fr['road_accessibility'];
                }
            }
        }

        // Calculate Factor Relative Importance Weights for XAI Visual Breakdown
        $weights = RuleBasedPrioritizer::$weights;
        $expFactor = ($contributingScores['flood_exposure'] ?? 50) * $weights['flood_exposure'];
        $hhFactor  = ($contributingScores['affected_households'] ?? 50) * $weights['affected_households'];
        $dmgFactor = ($contributingScores['housing_damage'] ?? 20) * $weights['housing_damage'];
        $vulnFactor = ($contributingScores['vulnerability'] ?? 30) * $weights['vulnerability'];
        $accessFactor = ($contributingScores['field_accessibility'] ?? 20) * $weights['field_accessibility'];
        $unmetFactor = ($contributingScores['unmet_needs'] ?? 20) * $weights['unmet_needs'];

        $sumFactors = max(1.0, $expFactor + $hhFactor + $dmgFactor + $vulnFactor + $accessFactor + $unmetFactor);

        $breakdown = [
            [
                'factor' => 'Flood Hazard Exposure',
                'description' => "Geographic exposure level: {$exposure} (River/coast proximity)",
                'contribution_pct' => round(($expFactor / $sumFactors) * 100, 1),
                'score' => $contributingScores['flood_exposure'] ?? 50,
                'influence' => ($exposure === 'Critical' || $exposure === 'High') ? 'Strong Upward' : 'Moderate'
            ],
            [
                'factor' => 'Displaced Household Density',
                'description' => "{$households} affected families ({$pop} residents)",
                'contribution_pct' => round(($hhFactor / $sumFactors) * 100, 1),
                'score' => $contributingScores['affected_households'] ?? 50,
                'influence' => ($households > 150) ? 'Strong Upward' : 'Neutral'
            ],
            [
                'factor' => 'Housing Destruction Severity',
                'description' => "{$totDmg} totally destroyed homes, {$partDmg} partially damaged",
                'contribution_pct' => round(($dmgFactor / $sumFactors) * 100, 1),
                'score' => $contributingScores['housing_damage'] ?? 20,
                'influence' => ($totDmg > 0) ? 'Critical Upward' : 'Neutral'
            ],
            [
                'factor' => 'Demographic Vulnerability',
                'description' => "{$seniors} elderly, {$pwds} PWDs, and {$fourPs} 4Ps beneficiary families",
                'contribution_pct' => round(($vulnFactor / $sumFactors) * 100, 1),
                'score' => $contributingScores['vulnerability'] ?? 30,
                'influence' => ($seniors + $pwds > 30) ? 'Moderate Upward' : 'Baseline'
            ],
            [
                'factor' => 'Ground Passability & Field Status',
                'description' => $roadIssue ? "Ground report confirmed road: {$roadIssue}" : ($recentReportsCount > 0 ? "{$recentReportsCount} verified ground status report(s) on file" : "Standard accessible route"),
                'contribution_pct' => round(($accessFactor / $sumFactors) * 100, 1),
                'score' => $contributingScores['field_accessibility'] ?? 20,
                'influence' => $roadIssue ? 'Strong Urgency' : 'Neutral'
            ],
            [
                'factor' => 'Unmet Relief Needs',
                'description' => "Outstanding unmet relief deficit awaiting delivery",
                'contribution_pct' => round(($unmetFactor / $sumFactors) * 100, 1),
                'score' => $contributingScores['unmet_needs'] ?? 20,
                'influence' => 'Proportional'
            ]
        ];

        // Compose Clear Thesis-Grade Human-Readable Narrative
        $narrativeSentences = [];

        // Sentence 1: Core Priority Statement
        $narrativeSentences[] = "{$pName} has been classified as {$priority} Priority (Composite Risk Score: {$compositeScore}/100) based on verified disaster parameters from the Barangay Hinaplanon BDRRM framework.";

        // Sentence 2: Key Driving Factors
        $drivers = [];
        if ($exposure === 'Critical' || $exposure === 'High') {
            $drivers[] = "{$exposure} flood hazard exposure along Mandulug River channel";
        }
        if ($households >= 150) {
            $drivers[] = "a substantial population of {$households} affected households";
        }
        if ($totDmg > 0) {
            $drivers[] = "{$totDmg} confirmed totally destroyed residential structure(s)";
        }
        if ($seniors + $pwds >= 25) {
            $drivers[] = "high concentration of vulnerable residents ({$seniors} seniors, {$pwds} PWDs)";
        }
        if ($roadIssue) {
            $drivers[] = "ground status reports indicating road access is {$roadIssue}";
        }

        if (!empty($drivers)) {
            $narrativeSentences[] = "Primary contributing factors include " . implode(", ", $drivers) . ".";
        } else {
            $narrativeSentences[] = "The evaluation reflects baseline vulnerability indicators with no immediate reports of structural collapse or access cutoff.";
        }

        // Sentence 3: Resource Allocation Rationale
        $riceKg = $allocation['rice_kg'] ?? ($households * 5);
        $canned = $allocation['canned_tins'] ?? ($households * 4);
        $water = $allocation['water_bottles'] ?? ($households * 2);
        $hygiene = $allocation['hygiene_kits'] ?? $households;
        $blankets = $allocation['blankets'] ?? max($totDmg, (int)ceil($households * 0.2));

        $narrativeSentences[] = "An intelligent relief quota of {$riceKg} kg Rice (5kg standard family packs), {$canned} Canned Goods, {$water} Water units, {$hygiene} Hygiene Kits, and {$blankets} Blankets is recommended to satisfy verified household sustenance requirements.";

        // Sentence 4: Stock / Shortage Advisory
        if (!empty($allocation['shortage_notice'])) {
            $narrativeSentences[] = "Stock Advisory: " . $allocation['shortage_notice'];
        }

        $fullNarrative = implode(" ", $narrativeSentences);

        // Data Completeness Assessment
        $completeness = 'High (Verified)';
        if (($purok['risk_classification'] ?? '') === 'Pending Verification') {
            $completeness = 'Moderate (Awaiting GPS Boundary Survey)';
        } elseif ($households <= 0) {
            $completeness = 'Partial (Census Under Review)';
        }

        return [
            'priority' => $priority,
            'composite_score' => $compositeScore,
            'explanation_narrative' => $fullNarrative,
            'breakdown' => $breakdown,
            'data_completeness' => $completeness,
            'driving_factors' => $drivers,
            'evacuation_haven' => $purok['evacuation_center'] ?? 'Designated Barangay Center',
            'verified_sources' => [
                'BDRRM Plan 2024-2026 Table 18 & 26 (Purok Risk Profiling)',
                'TS Basyang Official Incident & Relief Ledger (ECOWEB / CARD NGO)',
                'Barangay Secretary Handwritten Validation Records'
            ]
        ];
    }
}

