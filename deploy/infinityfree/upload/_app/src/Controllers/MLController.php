<?php
// src/Controllers/MLController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Core\Database;
use App\Models\HistoricalRecord;
use App\ML\DecisionTreeClassifier;
use App\ML\ModelEvaluator;
use App\ML\FeatureExtractor;
use Exception;

class MLController {
    public function index(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        $db = Database::getInstance();

        // Get active model from database
        $activeModelRow = $db->query("SELECT * FROM model_versions WHERE is_active = 1 ORDER BY model_version_id DESC LIMIT 1")->fetch();
        $allVersions = $db->query("SELECT * FROM model_versions ORDER BY created_at DESC")->fetchAll();

        $verifiedCount = HistoricalRecord::countVerified();
        $syntheticCount = HistoricalRecord::countSynthetic();

        // Check if active_model.json exists
        $modelFilePath = __DIR__ . '/../../ml/data/active_model.json';
        $modelJson = file_exists($modelFilePath) ? json_decode(file_get_contents($modelFilePath), true) : null;

        View::render('ml.index', [
            'title' => 'Decision Tree ML & Model Management',
            'activeModel' => $activeModelRow,
            'allVersions' => $allVersions,
            'verifiedCount' => $verifiedCount,
            'syntheticCount' => $syntheticCount,
            'modelJson' => $modelJson
        ]);
    }

    public function train(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security validation failed.');
            header("Location: index.php?route=ml-model");
            exit;
        }

        $source = Request::post('dataset_source', 'verified');
        $maxDepth = (int)Request::post('max_depth', 5);
        $seed = 42;

        $db = Database::getInstance();
        $rawRecords = [];
        $datasetLabel = "";

        if ($source === 'demo') {
            $demoCsv = __DIR__ . '/../../ml/data/hinaplanon_synthetic_demonstration_records.csv';
            if (HistoricalRecord::countSynthetic() === 0 && file_exists($demoCsv)) {
                HistoricalRecord::importCsv($demoCsv, 1, Auth::user()['firstname'] . ' ' . Auth::user()['lastname']);
            }
            $rawRecords = $db->query("SELECT * FROM historical_disaster_records WHERE is_synthetic = 1")->fetchAll();
            $datasetLabel = "Synthetic Demonstration Dataset (Demo Environment)";
        } else {
            $rawRecords = $db->query("SELECT * FROM historical_disaster_records WHERE is_synthetic = 0 AND is_verified = 1")->fetchAll();
            $datasetLabel = "Official BDRRMC Verified Incident Records";

            if (count($rawRecords) < 10) {
                Auth::setFlash('warning', 'Model Training Aborted: Insufficient verified historical records (Found ' . count($rawRecords) . ', Minimum 10 required). In compliance with research ethics, models must not be trained on insufficient data.');
                header("Location: index.php?route=ml-model");
                exit;
            }
        }

        $nSamples = count($rawRecords);
        if ($nSamples === 0) {
            Auth::setFlash('danger', 'Selected dataset is empty.');
            header("Location: index.php?route=ml-model");
            exit;
        }

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

        $classifier = new DecisionTreeClassifier($maxDepth, 4, 0.001);
        $classifier->fit($trainX, $trainY);
        $eval = ModelEvaluator::evaluate($classifier, $testX, $testY);

        $activeModelPath = __DIR__ . '/../../ml/data/active_model.json';
        $classifier->saveToJson($activeModelPath);

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
            Auth::user()['firstname'] . ' ' . Auth::user()['lastname']
        ]);

        Auth::setFlash('success', "Decision Tree pipeline trained successfully! Model version '{$versionName}' active with Accuracy: " . round($eval['accuracy'] * 100, 2) . "% and Weighted F1: " . round($eval['weighted_f1'] * 100, 2) . "%.");
        header("Location: index.php?route=ml-model");
        exit;
    }

    public function downloadTemplate(): void {
        Auth::requireLogin();
        $path = __DIR__ . '/../../ml/data/template_historical_disaster_records.csv';
        if (!file_exists($path)) {
            Auth::setFlash('danger', 'Template not found.');
            header("Location: index.php?route=ml-model");
            exit;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="hinaplanon_historical_template.csv"');
        readfile($path);
        exit;
    }

    public function historicalRecords(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        $records = HistoricalRecord::all(true);
        $verifiedCount = HistoricalRecord::countVerified();
        $syntheticCount = HistoricalRecord::countSynthetic();

        View::render('ml.historical_records', [
            'title' => 'Historical Disaster Incident Dataset',
            'records' => $records,
            'verifiedCount' => $verifiedCount,
            'syntheticCount' => $syntheticCount
        ]);
    }

    public function importCsv(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security validation failed.');
            header("Location: index.php?route=historical-records");
            exit;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            Auth::setFlash('warning', 'Please select a valid CSV file to upload.');
            header("Location: index.php?route=historical-records");
            exit;
        }

        $tmpFile = $_FILES['csv_file']['tmp_name'];
        $isSynthetic = Request::post('is_synthetic') ? 1 : 0;
        $userName = Auth::user()['firstname'] . ' ' . Auth::user()['lastname'];

        $result = HistoricalRecord::importCsv($tmpFile, $isSynthetic, $userName);

        if ($result['success']) {
            $msg = "Imported {$result['imported_count']} records successfully.";
            if (!empty($result['errors'])) {
                $msg .= " (" . count($result['errors']) . " row validation errors encountered and skipped).";
            }
            Auth::setFlash('success', $msg);
        } else {
            Auth::setFlash('danger', 'CSV Import Failed: ' . $result['message']);
        }

        header("Location: index.php?route=historical-records");
        exit;
    }
}
