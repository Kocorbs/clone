<?php
// src/ML/FeatureExtractor.php

namespace App\ML;

class FeatureExtractor {
    public static array $featureNames = [
        'disaster_type',
        'severity_level',
        'is_urgent',
        'purok_risk_level',
        'affected_households',
        'affected_population'
    ];

    public static function extract(array $record): array {
        return [
            'disaster_type' => (string)($record['disaster_type'] ?? 'Flood'),
            'severity_level' => (string)($record['severity_level'] ?? 'Medium'),
            'is_urgent' => (int)(!empty($record['is_urgent']) ? 1 : 0),
            'purok_risk_level' => (string)($record['purok_risk_level'] ?? 'Medium'),
            'affected_households' => (float)($record['affected_households'] ?? 10),
            'affected_population' => (float)($record['affected_population'] ?? 40),
        ];
    }

    public static function isNumericFeature(string $feature): bool {
        return in_array($feature, ['affected_households', 'affected_population', 'is_urgent'], true);
    }
}
