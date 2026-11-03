<?php
// src/ML/ModelEvaluator.php

namespace App\ML;

class ModelEvaluator {
    public static array $classes = ['Urgent', 'High', 'Medium', 'Low'];

    public static function evaluate(DecisionTreeClassifier $model, array $testX, array $testY): array {
        $n = count($testY);
        if ($n === 0) {
            return [
                'total_test_samples' => 0,
                'accuracy' => 0.0,
                'macro_precision' => 0.0,
                'macro_recall' => 0.0,
                'macro_f1' => 0.0,
                'weighted_precision' => 0.0,
                'weighted_recall' => 0.0,
                'weighted_f1' => 0.0,
                'confusion_matrix' => [],
                'per_class' => []
            ];
        }

        // Initialize confusion matrix: actual (rows) x predicted (columns)
        $matrix = [];
        foreach (self::$classes as $actual) {
            $matrix[$actual] = [];
            foreach (self::$classes as $pred) {
                $matrix[$actual][$pred] = 0;
            }
        }

        $correct = 0;
        $predictions = [];

        foreach ($testX as $idx => $x) {
            $predResult = $model->predict($x);
            $predClass = $predResult['predicted_priority'];
            $actualClass = $testY[$idx];

            $predictions[] = [
                'actual' => $actualClass,
                'predicted' => $predClass,
                'confidence' => $predResult['confidence'],
                'path' => $predResult['decision_path']
            ];

            if (isset($matrix[$actualClass][$predClass])) {
                $matrix[$actualClass][$predClass]++;
            }

            if ($predClass === $actualClass) {
                $correct++;
            }
        }

        $accuracy = round($correct / $n, 4);

        // Per-class metrics
        $perClass = [];
        $totalSupport = $n;
        $sumPrec = 0.0;
        $sumRec = 0.0;
        $sumF1 = 0.0;
        $weightPrec = 0.0;
        $weightRec = 0.0;
        $weightF1 = 0.0;
        $activeClassCount = 0;

        foreach (self::$classes as $c) {
            $tp = $matrix[$c][$c] ?? 0;
            
            // FP = sum of column c excluding tp
            $fp = 0;
            foreach (self::$classes as $other) {
                if ($other !== $c) {
                    $fp += ($matrix[$other][$c] ?? 0);
                }
            }

            // FN = sum of row c excluding tp
            $fn = 0;
            foreach (self::$classes as $other) {
                if ($other !== $c) {
                    $fn += ($matrix[$c][$other] ?? 0);
                }
            }

            $support = array_sum($matrix[$c]);

            $prec = ($tp + $fp) > 0 ? round($tp / ($tp + $fp), 4) : 0.0;
            $rec = ($tp + $fn) > 0 ? round($tp / ($tp + $fn), 4) : 0.0;
            $f1 = ($prec + $rec) > 0 ? round((2 * $prec * $rec) / ($prec + $rec), 4) : 0.0;

            $perClass[$c] = [
                'support' => $support,
                'tp' => $tp,
                'fp' => $fp,
                'fn' => $fn,
                'precision' => $prec,
                'recall' => $rec,
                'f1' => $f1
            ];

            if ($support > 0 || ($tp + $fp) > 0) {
                $activeClassCount++;
                $sumPrec += $prec;
                $sumRec += $rec;
                $sumF1 += $f1;

                $weightPrec += ($prec * $support);
                $weightRec += ($rec * $support);
                $weightF1 += ($f1 * $support);
            }
        }

        $macroPrec = $activeClassCount > 0 ? round($sumPrec / $activeClassCount, 4) : 0.0;
        $macroRec = $activeClassCount > 0 ? round($sumRec / $activeClassCount, 4) : 0.0;
        $macroF1 = $activeClassCount > 0 ? round($sumF1 / $activeClassCount, 4) : 0.0;

        $weightedPrec = $totalSupport > 0 ? round($weightPrec / $totalSupport, 4) : 0.0;
        $weightedRec = $totalSupport > 0 ? round($weightRec / $totalSupport, 4) : 0.0;
        $weightedF1 = $totalSupport > 0 ? round($weightF1 / $totalSupport, 4) : 0.0;

        return [
            'total_test_samples' => $n,
            'correct_count' => $correct,
            'accuracy' => $accuracy,
            'macro_precision' => $macroPrec,
            'macro_recall' => $macroRec,
            'macro_f1' => $macroF1,
            'weighted_precision' => $weightedPrec,
            'weighted_recall' => $weightedRec,
            'weighted_f1' => $weightedF1,
            'confusion_matrix' => $matrix,
            'per_class' => $perClass,
            'sample_predictions' => array_slice($predictions, 0, 10)
        ];
    }
}
