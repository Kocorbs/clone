<?php
// src/ML/DecisionTreeClassifier.php

namespace App\ML;

class DecisionTreeClassifier {
    private int $maxDepth;
    private int $minSamplesSplit;
    private float $minImpurityDecrease;
    private ?array $tree = null;
    private array $classes = ['Urgent', 'High', 'Medium', 'Low'];
    private array $featureImportances = [];

    public function __construct(int $maxDepth = 5, int $minSamplesSplit = 4, float $minImpurityDecrease = 0.001) {
        $this->maxDepth = $maxDepth;
        $this->minSamplesSplit = $minSamplesSplit;
        $this->minImpurityDecrease = $minImpurityDecrease;
    }

    /**
     * Train the Decision Tree classifier.
     * @param array $X Array of feature associative arrays
     * @param array $y Array of target labels (e.g. ['Urgent', 'High', ...])
     */
    public function fit(array $X, array $y): void {
        if (empty($X) || empty($y) || count($X) !== count($y)) {
            throw new \InvalidArgumentException("Training dataset and labels must be non-empty and of equal length.");
        }

        // Initialize feature importances
        $this->featureImportances = [];
        foreach (FeatureExtractor::$featureNames as $f) {
            $this->featureImportances[$f] = 0.0;
        }

        $this->tree = $this->buildTree($X, $y, 0);

        // Normalize feature importances
        $totalImportance = array_sum($this->featureImportances);
        if ($totalImportance > 0) {
            foreach ($this->featureImportances as $f => $val) {
                $this->featureImportances[$f] = round($val / $totalImportance, 4);
            }
        }
    }

    private function buildTree(array $X, array $y, int $depth): array {
        $nSamples = count($y);
        $classCounts = $this->countClasses($y);
        $gini = $this->calculateGini($classCounts, $nSamples);
        $majority = $this->getMajorityClass($classCounts);
        $probs = $this->calculateProbabilities($classCounts, $nSamples);

        // Check stopping conditions
        if ($depth >= $this->maxDepth || $nSamples < $this->minSamplesSplit || $gini <= 0.0001) {
            return [
                'type' => 'leaf',
                'predicted_class' => $majority,
                'probabilities' => $probs,
                'samples' => $nSamples,
                'class_counts' => $classCounts,
                'gini' => round($gini, 4)
            ];
        }

        // Find the best split
        $bestSplit = $this->findBestSplit($X, $y, $gini);

        if (!$bestSplit || $bestSplit['gain'] < $this->minImpurityDecrease) {
            return [
                'type' => 'leaf',
                'predicted_class' => $majority,
                'probabilities' => $probs,
                'samples' => $nSamples,
                'class_counts' => $classCounts,
                'gini' => round($gini, 4)
            ];
        }

        // Record feature importance contribution (Gain * sample proportion)
        $feature = $bestSplit['feature'];
        $this->featureImportances[$feature] += ($bestSplit['gain'] * $nSamples);

        // Recursively build left and right subtrees
        $leftSubtree = $this->buildTree($bestSplit['left_X'], $bestSplit['left_y'], $depth + 1);
        $rightSubtree = $this->buildTree($bestSplit['right_X'], $bestSplit['right_y'], $depth + 1);

        return [
            'type' => 'split',
            'feature' => $feature,
            'is_numeric' => $bestSplit['is_numeric'],
            'threshold' => $bestSplit['threshold'],
            'gain' => round($bestSplit['gain'], 4),
            'samples' => $nSamples,
            'gini' => round($gini, 4),
            'left' => $leftSubtree,
            'right' => $rightSubtree
        ];
    }

    private function findBestSplit(array $X, array $y, float $currentGini): ?array {
        $nSamples = count($y);
        $bestGain = -1.0;
        $bestSplit = null;

        foreach (FeatureExtractor::$featureNames as $feature) {
            $isNumeric = FeatureExtractor::isNumericFeature($feature);
            $values = array_column($X, $feature);
            $uniqueValues = array_values(array_unique($values));

            if (count($uniqueValues) <= 1) {
                continue;
            }

            if ($isNumeric) {
                sort($uniqueValues);
                // Test midpoints as candidate thresholds
                for ($i = 0; $i < count($uniqueValues) - 1; $i++) {
                    $threshold = ($uniqueValues[$i] + $uniqueValues[$i + 1]) / 2.0;
                    $split = $this->evaluateNumericSplit($X, $y, $feature, $threshold, $currentGini, $nSamples);
                    if ($split && $split['gain'] > $bestGain) {
                        $bestGain = $split['gain'];
                        $bestSplit = $split;
                    }
                }
            } else {
                // Categorical: feature == value vs feature != value
                foreach ($uniqueValues as $val) {
                    $split = $this->evaluateCategoricalSplit($X, $y, $feature, $val, $currentGini, $nSamples);
                    if ($split && $split['gain'] > $bestGain) {
                        $bestGain = $split['gain'];
                        $bestSplit = $split;
                    }
                }
            }
        }

        return $bestSplit;
    }

    private function evaluateNumericSplit(array $X, array $y, string $feature, float $threshold, float $currentGini, int $nSamples): ?array {
        $leftX = []; $leftY = [];
        $rightX = []; $rightY = [];

        foreach ($X as $idx => $row) {
            if ((float)$row[$feature] <= $threshold) {
                $leftX[] = $row;
                $leftY[] = $y[$idx];
            } else {
                $rightX[] = $row;
                $rightY[] = $y[$idx];
            }
        }

        if (empty($leftY) || empty($rightY)) {
            return null;
        }

        $nLeft = count($leftY);
        $nRight = count($rightY);
        $giniLeft = $this->calculateGini($this->countClasses($leftY), $nLeft);
        $giniRight = $this->calculateGini($this->countClasses($rightY), $nRight);

        $weightedGini = ($nLeft / $nSamples) * $giniLeft + ($nRight / $nSamples) * $giniRight;
        $gain = $currentGini - $weightedGini;

        return [
            'feature' => $feature,
            'is_numeric' => true,
            'threshold' => $threshold,
            'gain' => $gain,
            'left_X' => $leftX,
            'left_y' => $leftY,
            'right_X' => $rightX,
            'right_y' => $rightY
        ];
    }

    private function evaluateCategoricalSplit(array $X, array $y, string $feature, string $targetVal, float $currentGini, int $nSamples): ?array {
        $leftX = []; $leftY = [];
        $rightX = []; $rightY = [];

        foreach ($X as $idx => $row) {
            if ((string)$row[$feature] === (string)$targetVal) {
                $leftX[] = $row;
                $leftY[] = $y[$idx];
            } else {
                $rightX[] = $row;
                $rightY[] = $y[$idx];
            }
        }

        if (empty($leftY) || empty($rightY)) {
            return null;
        }

        $nLeft = count($leftY);
        $nRight = count($rightY);
        $giniLeft = $this->calculateGini($this->countClasses($leftY), $nLeft);
        $giniRight = $this->calculateGini($this->countClasses($rightY), $nRight);

        $weightedGini = ($nLeft / $nSamples) * $giniLeft + ($nRight / $nSamples) * $giniRight;
        $gain = $currentGini - $weightedGini;

        return [
            'feature' => $feature,
            'is_numeric' => false,
            'threshold' => $targetVal,
            'gain' => $gain,
            'left_X' => $leftX,
            'left_y' => $leftY,
            'right_X' => $rightX,
            'right_y' => $rightY
        ];
    }

    private function calculateGini(array $classCounts, int $total): float {
        if ($total === 0) return 0.0;
        $sumSq = 0.0;
        foreach ($classCounts as $cnt) {
            $p = $cnt / $total;
            $sumSq += ($p * $p);
        }
        return 1.0 - $sumSq;
    }

    private function countClasses(array $y): array {
        $counts = array_fill_keys($this->classes, 0);
        foreach ($y as $label) {
            if (isset($counts[$label])) {
                $counts[$label]++;
            } else {
                $counts[$label] = 1;
            }
        }
        return $counts;
    }

    private function getMajorityClass(array $classCounts): string {
        arsort($classCounts);
        return array_key_first($classCounts) ?? 'Medium';
    }

    private function calculateProbabilities(array $classCounts, int $total): array {
        $probs = [];
        foreach ($this->classes as $c) {
            $cnt = $classCounts[$c] ?? 0;
            $probs[$c] = $total > 0 ? round($cnt / $total, 4) : 0.0;
        }
        return $probs;
    }

    /**
     * Predict priority class, confidence, and exact decision path for a single record.
     */
    public function predict(array $x): array {
        if ($this->tree === null) {
            throw new \RuntimeException("Model is not trained. Fit the model or load a saved tree first.");
        }

        $node = $this->tree;
        $path = [];

        while ($node['type'] === 'split') {
            $feature = $node['feature'];
            $val = $x[$feature] ?? null;

            if ($node['is_numeric']) {
                $thresh = (float)$node['threshold'];
                if ((float)$val <= $thresh) {
                    $path[] = "{$feature} <= {$thresh}";
                    $node = $node['left'];
                } else {
                    $path[] = "{$feature} > {$thresh}";
                    $node = $node['right'];
                }
            } else {
                $target = (string)$node['threshold'];
                if ((string)$val === $target) {
                    $path[] = "{$feature} == '{$target}'";
                    $node = $node['left'];
                } else {
                    $path[] = "{$feature} != '{$target}'";
                    $node = $node['right'];
                }
            }
        }

        $predicted = $node['predicted_class'];
        $confidence = $node['probabilities'][$predicted] ?? 1.0;

        return [
            'predicted_priority' => $predicted,
            'confidence' => round($confidence * 100, 2), // percentage
            'probabilities' => $node['probabilities'],
            'samples_in_leaf' => $node['samples'],
            'leaf_gini' => $node['gini'] ?? 0.0,
            'decision_path' => $path,
            'rule_summary' => implode(' AND ', $path) . " => Priority: " . $predicted
        ];
    }

    public function getTree(): ?array {
        return $this->tree;
    }

    public function setTree(?array $tree): void {
        $this->tree = $tree;
    }

    public function getTreeStructure(): ?array {
        return $this->tree;
    }

    public function setTreeStructure(?array $tree): void {
        $this->tree = $tree;
    }

    public function getFeatureImportances(): array {
        return $this->featureImportances;
    }

    public function saveToJson(string $filePath): void {
        $data = [
            'algorithm' => 'DecisionTreeClassifier (CART/Gini)',
            'hyperparameters' => [
                'max_depth' => $this->maxDepth,
                'min_samples_split' => $this->minSamplesSplit,
                'min_impurity_decrease' => $this->minImpurityDecrease,
            ],
            'classes' => $this->classes,
            'feature_names' => FeatureExtractor::$featureNames,
            'feature_importances' => $this->featureImportances,
            'tree' => $this->tree,
            'saved_at' => date('Y-m-d H:i:s')
        ];
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function loadFromJson(string $filePath): void {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Model file not found: {$filePath}");
        }
        $json = file_get_contents($filePath);
        $data = json_decode($json, true);
        if (!$data || !isset($data['tree'])) {
            throw new \RuntimeException("Corrupt or invalid model JSON file.");
        }
        $this->tree = $data['tree'];
        $this->featureImportances = $data['feature_importances'] ?? [];
        if (isset($data['classes'])) {
            $this->classes = $data['classes'];
        }
    }
}
