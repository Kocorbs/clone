<?php
// ml/train.php

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/ML/FeatureExtractor.php';
require_once __DIR__ . '/../src/ML/DecisionTreeClassifier.php';
require_once __DIR__ . '/../src/ML/ModelEvaluator.php';
require_once __DIR__ . '/../src/Models/HistoricalRecord.php';

use App\Core\Database;
use App\ML\FeatureExtractor;
use App\ML\DecisionTreeClassifier;
use App\ML\ModelEvaluator;
use App\Models\HistoricalRecord;

// Check CLI or internal call
$options = getopt("", ["source::", "seed::", "depth::"]);
$source = $options['source'] ?? ($argv[1] ?? 'verified');
$seed = isset($options['seed']) ? (int)$options['seed'] : 42;
$maxDepth = isset($options['depth']) ? (int)$options['depth'] : 5;

echo "=========================================================\n";
echo "   Hinaplanon Disaster Decision Tree Training Pipeline   \n";
echo "=========================================================\n";
echo "Target Source: " . $source . "\n";
echo "Random Seed: " . $seed . " | Max Depth: " . $maxDepth . "\n\n";

$db = Database::getInstance();

$rawRecords = [];
$datasetLabel = "";

if ($source === 'demo') {
    $demoCsv = __DIR__ . '/data/hinaplanon_synthetic_demonstration_records.csv';
    if (!file_exists($demoCsv)) {
        die("Error: Demo dataset CSV not found at {$demoCsv}.\n");
    }
    
    // Also ensure imported into historical_disaster_records table as synthetic
    $existingDemoCount = HistoricalRecord::countSynthetic();
    if ($existingDemoCount === 0) {
        echo "Importing demonstration CSV into database (marked is_synthetic = 1)...\n";
        HistoricalRecord::importCsv($demoCsv, 1, 'System Initializer');
    }

    $rawRecords = $db->query("SELECT * FROM historical_disaster_records WHERE is_synthetic = 1")->fetchAll();
    $datasetLabel = "Synthetic Demonstration Dataset (Demo Environment)";
} else {
    // Official Verified Records only
    $rawRecords = $db->query("SELECT * FROM historical_disaster_records WHERE is_synthetic = 0 AND is_verified = 1")->fetchAll();
    $datasetLabel = "Official BDRRMC Verified Incident Records";

    if (count($rawRecords) < 10) {
        echo "[STATUS: NOT TRAINED — INSUFFICIENT VERIFIED DATA]\n";
        echo "Found only " . count($rawRecords) . " verified historical disaster records.\n";
        echo "A minimum of 10 verified historical records with expert priority labels is required\n";
        echo "to prevent overfitting and data leakage.\n\n";
        echo "Guidance:\n";
        echo "1. To train on the separated Demonstration environment, run:\n";
        echo "   php ml/train.php --source=demo\n";
        echo "2. To import official verified records, use the CSV Import module at:\n";
        echo "   index.php?route=historical-records\n";
        exit(0);
    }
}

$nSamples = count($rawRecords);
echo "Total records loaded: {$nSamples} ({$datasetLabel})\n";

// Extract features and labels
$X = [];
$y = [];
foreach ($rawRecords as $r) {
    $X[] = FeatureExtractor::extract([
        'disaster_type' => $r['disaster_type'],
        'severity_level' => $r['severity_level'],
        'is_urgent' => $r['is_urgent'],
        'purok_risk_level' => $r['purok_risk_level'],
        'affected_households' => $r['affected_households'],
        'affected_population' => $r['affected_population']
    ]);
    $y[] = $r['priority_label'];
}

// Reproducible 80/20 train/test split
mt_srand($seed);
$indices = range(0, $nSamples - 1);
// Shuffle indices deterministically
for ($i = $nSamples - 1; $i > 0; $i--) {
    $j = mt_rand(0, $i);
    $tmp = $indices[$i];
    $indices[$i] = $indices[$j];
    $indices[$j] = $tmp;
}

$trainCount = (int)round($nSamples * 0.80);
$testCount = $nSamples - $trainCount;

$trainX = []; $trainY = [];
$testX = []; $testY = [];

for ($i = 0; $i < $nSamples; $i++) {
    $idx = $indices[$i];
    if ($i < $trainCount) {
        $trainX[] = $X[$idx];
        $trainY[] = $y[$idx];
    } else {
        $testX[] = $X[$idx];
        $testY[] = $y[$idx];
    }
}

echo "Train set: {$trainCount} samples | Test set: {$testCount} samples\n";

// Train Decision Tree Classifier
$classifier = new DecisionTreeClassifier($maxDepth, 4, 0.001);
$classifier->fit($trainX, $trainY);

// Evaluate on held-out test set
$eval = ModelEvaluator::evaluate($classifier, $testX, $testY);

echo "\n--- EVALUATION RESULTS ---\n";
echo "Accuracy:           " . round($eval['accuracy'] * 100, 2) . "%\n";
echo "Macro Precision:    " . round($eval['macro_precision'] * 100, 2) . "%\n";
echo "Macro Recall:       " . round($eval['macro_recall'] * 100, 2) . "%\n";
echo "Macro F1-Score:     " . round($eval['macro_f1'] * 100, 2) . "%\n";
echo "Weighted F1-Score:  " . round($eval['weighted_f1'] * 100, 2) . "%\n\n";

echo "--- 4x4 CONFUSION MATRIX (Actual \\ Predicted) ---\n";
printf("%-10s | %-8s | %-8s | %-8s | %-8s\n", "Actual", "Urgent", "High", "Medium", "Low");
echo "--------------------------------------------------------\n";
foreach (ModelEvaluator::$classes as $act) {
    printf("%-10s | %-8d | %-8d | %-8d | %-8d\n",
        $act,
        $eval['confusion_matrix'][$act]['Urgent'] ?? 0,
        $eval['confusion_matrix'][$act]['High'] ?? 0,
        $eval['confusion_matrix'][$act]['Medium'] ?? 0,
        $eval['confusion_matrix'][$act]['Low'] ?? 0
    );
}

echo "\n--- FEATURE IMPORTANCES ---\n";
foreach ($classifier->getFeatureImportances() as $feat => $imp) {
    printf("  %-22s : %6.2f%%\n", $feat, $imp * 100);
}

// Save active model JSON
$activeModelPath = __DIR__ . '/data/active_model.json';
$classifier->saveToJson($activeModelPath);
echo "\nSaved active model to: {$activeModelPath}\n";

// Persist Model Version into database
$versionName = "DecisionTree-" . date('Ymd-His') . ($source === 'demo' ? '-DEMO' : '');
$db->prepare("UPDATE model_versions SET is_active = 0")->execute();

$stmtVer = $db->prepare("INSERT INTO model_versions (
    version_name, algorithm, hyperparameters_json, dataset_source,
    training_samples_count, test_samples_count, accuracy, precision_score,
    recall_score, f1_score, confusion_matrix_json, tree_rules_json, is_active, trained_by
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");

$stmtVer->execute([
    $versionName,
    'DecisionTreeClassifier (CART/Gini)',
    json_encode(['max_depth' => $maxDepth, 'min_samples_split' => 4, 'random_seed' => $seed]),
    $datasetLabel,
    $trainCount,
    $testCount,
    $eval['accuracy'],
    $eval['macro_precision'],
    $eval['macro_recall'],
    $eval['macro_f1'],
    json_encode($eval['confusion_matrix']),
    json_encode($classifier->getTree()),
    'Disaster Systems Architect'
]);

echo "Registered active model version in database: {$versionName}\n";
echo "=========================================================\n";
