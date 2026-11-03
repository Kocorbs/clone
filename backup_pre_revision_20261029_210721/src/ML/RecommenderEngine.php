<?php
// src/ML/RecommenderEngine.php

namespace App\ML;

use App\Models\DisasterRequest;
use App\Models\Purok;
use App\Models\Supply;
use App\Models\Recommendation;
use App\Models\RequestLog;
use Exception;

class RecommenderEngine {
    private static string $modelPath = __DIR__ . '/../../ml/data/active_model.json';

    public static function generateForDisasterRequest(int $disasterRequestId, ?int $targetSupplyId = null): array {
        $req = DisasterRequest::findById($disasterRequestId);
        if (!$req) {
            throw new Exception("Disaster Request #{$disasterRequestId} not found.");
        }

        $purok = Purok::findById((int)$req['purok_id']);
        $purokRisk = $purok['risk_classification'] ?? 'Pending Verification';

        // 1. Prepare ML Features
        $features = FeatureExtractor::extract([
            'disaster_type' => $req['disaster_type'],
            'severity_level' => $req['severity_level'],
            'is_urgent' => (int)$req['is_urgent'],
            'purok_risk_level' => $purokRisk,
            'affected_households' => (int)$req['affected_households'],
            'affected_population' => (int)$req['affected_population']
        ]);

        // 2. Predict Priority using Decision Tree Classifier
        $model = new DecisionTreeClassifier();
        $predictedPriority = 'Medium';
        $confidence = 75.0;
        $decisionPath = [];
        $modelVersion = 'Not Trained — Insufficient Verified Data';

        if (file_exists(self::$modelPath)) {
            try {
                $model->loadFromJson(self::$modelPath);
                $prediction = $model->predict($features);
                $predictedPriority = $prediction['predicted_priority'];
                $confidence = $prediction['confidence'];
                $decisionPath = $prediction['decision_path'];
                $modelVersion = 'DecisionTree-v1.0';
            } catch (Exception $e) {
                // If corrupted or unreadable, fall back to transparent rule baseline
                $decisionPath[] = "Fallback rule triggered: " . $e->getMessage();
            }
        } else {
            // Documented Baseline Heuristic when Decision Tree is not yet trained
            $decisionPath[] = "Decision Tree not yet trained on verified data.";
            if ($req['severity_level'] === 'Critical' || $req['is_urgent'] == 1 || $purokRisk === 'High') {
                $predictedPriority = ($req['severity_level'] === 'Critical' && $req['is_urgent'] == 1) ? 'Urgent' : 'High';
                $confidence = 70.0;
                $decisionPath[] = "Baseline rule: Critical/Urgent/High Risk triggered priority '{$predictedPriority}'.";
            } elseif ($purokRisk === 'Low' && $req['severity_level'] === 'Low') {
                $predictedPriority = 'Low';
                $confidence = 70.0;
                $decisionPath[] = "Baseline rule: Low Risk & Low Severity triggered priority 'Low'.";
            } else {
                $predictedPriority = 'Medium';
                $confidence = 65.0;
                $decisionPath[] = "Baseline rule: Standard risk-severity combination triggered priority 'Medium'.";
            }
        }

        // 3. Determine Supply
        $supplies = Supply::all();
        $selectedSupply = null;

        if ($targetSupplyId) {
            $selectedSupply = Supply::findById($targetSupplyId);
        }
        
        if (!$selectedSupply && !empty($supplies)) {
            // Default to Relief Goods (Family Food Pack)
            foreach ($supplies as $s) {
                if ($s['supply_type'] === 'Relief Goods') {
                    $selectedSupply = $s;
                    break;
                }
            }
            if (!$selectedSupply) {
                $selectedSupply = $supplies[0];
            }
        }

        $availableStock = $selectedSupply ? (int)$selectedSupply['supply_remaining'] : 0;
        $supplyType = $selectedSupply ? $selectedSupply['supply_type'] : 'Relief Goods';

        // 4. Calculate Resource Need
        $demand = DemandCalculator::calculateNeed(
            $predictedPriority,
            (int)$req['affected_households'],
            (int)$req['affected_population'],
            $supplyType,
            $availableStock
        );

        // 5. Build Explanation
        $explanationData = [
            'disaster_request_id' => $disasterRequestId,
            'purok_name' => $purok['purok_name'] ?? 'Purok 1',
            'purok_risk_level' => $purokRisk,
            'features' => $features,
            'predicted_priority' => $predictedPriority,
            'confidence' => $confidence,
            'decision_path' => $decisionPath,
            'model_version' => $modelVersion,
            'demand_calculation' => $demand
        ];

        $reason = sprintf(
            "Priority '%s' predicted by %s (Confidence: %.1f%%). %s for %d affected households in %s (%s Hazard Zone). %s",
            $predictedPriority,
            $modelVersion,
            $confidence,
            $demand['formula_note'],
            (int)$req['affected_households'],
            $purok['purok_name'] ?? 'Hinaplanon',
            $purokRisk,
            $demand['shortage_notice']
        );

        // 6. Save Recommendation Record
        $recId = Recommendation::create([
            'request_id' => $req['request_id'] ?? null,
            'disaster_request_id' => $disasterRequestId,
            'purok_id' => (int)$req['purok_id'],
            'predicted_priority' => $predictedPriority,
            'recommended_supply_id' => $selectedSupply ? (int)$selectedSupply['supply_id'] : null,
            'recommended_quantity' => $demand['recommended_quantity'],
            'confidence_score' => $confidence,
            'recommendation_reason' => $reason,
            'explanation_details' => json_encode($explanationData, JSON_PRETTY_PRINT),
            'model_version' => $modelVersion
        ]);

        // Update Disaster Request Form status to 'Recommended'
        DisasterRequest::updateStatus($disasterRequestId, 'Recommended');

        // Audit log
        RequestLog::create([
            'request_id' => $req['request_id'] ?? null,
            'recommendation_id' => $recId,
            'recommended_by' => $modelVersion,
            'action_taken' => 'Recommendation Generated',
            'action_by' => 'Decision Tree Engine',
            'remarks' => "Generated recommendation #{$recId} with predicted priority '{$predictedPriority}' and quantity {$demand['recommended_quantity']} units."
        ]);

        return [
            'recommendation_id' => $recId,
            'priority' => $predictedPriority,
            'recommended_quantity' => $demand['recommended_quantity'],
            'confidence' => $confidence,
            'reason' => $reason,
            'explanation' => $explanationData
        ];
    }
}
