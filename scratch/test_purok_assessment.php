<?php
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/ML/FeatureExtractor.php';
require_once __DIR__ . '/../src/ML/DecisionTreeClassifier.php';
require_once __DIR__ . '/../src/ML/RandomForestClassifier.php';
require_once __DIR__ . '/../src/ML/RuleBasedPrioritizer.php';
require_once __DIR__ . '/../src/ML/ExplainableAiEngine.php';
require_once __DIR__ . '/../src/ML/PurokAssessmentEngine.php';
require_once __DIR__ . '/../src/Models/Supply.php';

use App\ML\PurokAssessmentEngine;

$results = PurokAssessmentEngine::runAssessment();
echo "Total Puroks Assessed: " . count($results) . "\n\n";

foreach ($results as $r) {
    echo "Rank #{$r['priority_rank']} | {$r['purok_name']} | Priority: {$r['priority_level']} (Score: {$r['composite_risk_score']}) | HH: {$r['affected_households']} | Rice: {$r['recommended_rice_kg']} kg | Capped: {$r['stock_capped']}\n";
    echo "  XAI: " . substr($r['explanation_narrative'], 0, 160) . "...\n\n";
}

