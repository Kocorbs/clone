<?php
// src/ML/RandomForestClassifier.php

namespace App\ML;

class RandomForestClassifier {
    private int $nEstimators;
    private int $maxDepth;
    private int $minSamplesSplit;
    private float $maxFeaturesRatio;
    private array $trees = [];
    private array $featureImportances = [];
    private array $classes = ['Critical', 'High', 'Moderate', 'Low'];
    private int $randomSeed;

    public function __construct(
        int $nEstimators = 15,
        int $maxDepth = 5,
        int $minSamplesSplit = 3,
        float $maxFeaturesRatio = 0.75,
        int $randomSeed = 42
    ) {
        $this->nEstimators = $nEstimators;
        $this->maxDepth = $maxDepth;
        $this->minSamplesSplit = $minSamplesSplit;
        $this->maxFeaturesRatio = $maxFeaturesRatio;
        $this->randomSeed = $randomSeed;
    }

    /**
     * Train the Random Forest ensemble on training samples X and labels y.
     */
    public function fit(array $X, array $y): void {
        $nSamples = count($y);
        if ($nSamples === 0 || count($X) !== $nSamples) {
            throw new \InvalidArgumentException("Random Forest requires non-empty training dataset of matching lengths.");
        }

        mt_srand($this->randomSeed);
        $this->trees = [];
        $featureNames = array_keys($X[0]);
        $nFeatures = count($featureNames);
        $subsetSize = max(2, (int)round($nFeatures * $this->maxFeaturesRatio));

        // Initialize feature importances
        $accumulatedImportances = array_fill_keys($featureNames, 0.0);

        for ($b = 0; $b < $this->nEstimators; $b++) {
            // 1. Bootstrap sample (sampling with replacement)
            $bootstrapX = [];
            $bootstrapY = [];
            for ($i = 0; $i < $nSamples; $i++) {
                $idx = mt_rand(0, $nSamples - 1);
                $bootstrapX[] = $X[$idx];
                $bootstrapY[] = $y[$idx];
            }

            // 2. Random feature subset
            $shuffledFeatures = $featureNames;
            shuffle($shuffledFeatures);
            $selectedFeatures = array_slice($shuffledFeatures, 0, $subsetSize);

            // 3. Train Decision Tree
            $tree = new DecisionTreeClassifier($this->maxDepth, $this->minSamplesSplit);
            $tree->fit($bootstrapX, $bootstrapY);

            // Accumulate feature importances
            $treeImportances = $tree->getFeatureImportances();
            foreach ($treeImportances as $feat => $imp) {
                if (isset($accumulatedImportances[$feat])) {
                    $accumulatedImportances[$feat] += $imp;
                }
            }

            $this->trees[] = [
                'tree' => $tree,
                'features' => $selectedFeatures
            ];
        }

        // Normalize aggregated feature importances
        $totalImp = array_sum($accumulatedImportances);
        if ($totalImp > 0) {
            foreach ($accumulatedImportances as $f => $val) {
                $this->featureImportances[$f] = round($val / $totalImp, 4);
            }
        } else {
            $uniform = round(1.0 / count($featureNames), 4);
            $this->featureImportances = array_fill_keys($featureNames, $uniform);
        }
    }

    /**
     * Predict priority class and confidence distribution for a single sample.
     */
    public function predict(array $features): array {
        if (empty($this->trees)) {
            throw new \RuntimeException("Random Forest model has not been trained yet.");
        }

        $votes = array_fill_keys($this->classes, 0);
        $probSums = array_fill_keys($this->classes, 0.0);
        $samplePaths = [];

        foreach ($this->trees as $idx => $treeData) {
            /** @var DecisionTreeClassifier $tree */
            $tree = $treeData['tree'];
            $res = $tree->predict($features);
            $predClass = $res['predicted_priority'] ?? 'Moderate';
            
            // Map legacy 'Urgent' or 'Medium' to 'Critical' / 'Moderate' if needed
            $mappedClass = self::normalizeClass($predClass);
            $votes[$mappedClass] = ($votes[$mappedClass] ?? 0) + 1;

            if (!empty($res['probabilities'])) {
                foreach ($res['probabilities'] as $cls => $prob) {
                    $normCls = self::normalizeClass($cls);
                    $probSums[$normCls] = ($probSums[$normCls] ?? 0.0) + $prob;
                }
            }

            if ($idx < 3) {
                $samplePaths[] = "Tree #" . ($idx + 1) . ": " . implode(' -> ', array_slice($res['decision_path'] ?? [], 0, 2));
            }
        }

        // Determine majority class
        arsort($votes);
        $majorityClass = array_key_first($votes);
        $totalTrees = count($this->trees);
        $confidence = round(($votes[$majorityClass] / $totalTrees) * 100, 1);

        // Normalize ensemble probabilities
        $finalProbs = [];
        foreach ($probSums as $cls => $sum) {
            $finalProbs[$cls] = round(($sum / $totalTrees) * 100, 1);
        }

        return [
            'predicted_priority' => $majorityClass,
            'confidence' => $confidence,
            'probabilities' => $finalProbs,
            'votes' => $votes,
            'trees_count' => $totalTrees,
            'decision_path' => $samplePaths
        ];
    }

    public static function normalizeClass(string $c): string {
        if ($c === 'Urgent') return 'Critical';
        if ($c === 'Medium') return 'Moderate';
        if (in_array($c, ['Critical', 'High', 'Moderate', 'Low'], true)) return $c;
        return 'Moderate';
    }

    public function getFeatureImportances(): array {
        return $this->featureImportances;
    }

    public function toJson(): string {
        $serializedTrees = [];
        foreach ($this->trees as $t) {
            $serializedTrees[] = [
                'tree_structure' => $t['tree']->getTreeStructure(),
                'feature_importances' => $t['tree']->getFeatureImportances(),
                'features' => $t['features']
            ];
        }

        return json_encode([
            'algorithm' => 'RandomForestClassifier',
            'n_estimators' => $this->nEstimators,
            'max_depth' => $this->maxDepth,
            'min_samples_split' => $this->minSamplesSplit,
            'random_seed' => $this->randomSeed,
            'feature_importances' => $this->featureImportances,
            'trees' => $serializedTrees,
            'classes' => $this->classes
        ], JSON_PRETTY_PRINT);
    }

    public function loadFromJson(string $filePath): void {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Model JSON file not found at: {$filePath}");
        }
        $data = json_decode(file_get_contents($filePath), true);
        if (!$data || !isset($data['trees'])) {
            throw new \RuntimeException("Invalid Random Forest JSON format.");
        }

        $this->nEstimators = $data['n_estimators'] ?? count($data['trees']);
        $this->maxDepth = $data['max_depth'] ?? 5;
        $this->minSamplesSplit = $data['min_samples_split'] ?? 3;
        $this->featureImportances = $data['feature_importances'] ?? [];
        $this->classes = $data['classes'] ?? ['Critical', 'High', 'Moderate', 'Low'];

        $this->trees = [];
        foreach ($data['trees'] as $tData) {
            $tree = new DecisionTreeClassifier($this->maxDepth, $this->minSamplesSplit);
            $tree->setTreeStructure($tData['tree_structure']);
            $this->trees[] = [
                'tree' => $tree,
                'features' => $tData['features'] ?? []
            ];
        }
    }
}

