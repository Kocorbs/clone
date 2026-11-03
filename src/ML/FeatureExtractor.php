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
        'affected_population',
        'totally_damaged_houses',
        'partially_damaged_houses',
        'vulnerable_members',
        'road_accessibility'
    ];

    public static function extract(array $record): array {
        return [
            'disaster_type' => (string)($record['disaster_type'] ?? 'Flood'),
            'severity_level' => (string)($record['severity_level'] ?? ($record['flood_hazard_exposure'] ?? 'Medium')),
            'is_urgent' => (int)(!empty($record['is_urgent']) || !empty($record['totally_damaged_houses']) ? 1 : 0),
            'purok_risk_level' => (string)($record['purok_risk_level'] ?? ($record['risk_classification'] ?? 'Medium')),
            'affected_households' => (float)($record['affected_households'] ?? ($record['households'] ?? 10)),
            'affected_population' => (float)($record['affected_population'] ?? ($record['population'] ?? 40)),
            'totally_damaged_houses' => (float)($record['totally_damaged_houses'] ?? ($record['historical_totally_damaged'] ?? 0)),
            'partially_damaged_houses' => (float)($record['partially_damaged_houses'] ?? ($record['historical_partially_damaged'] ?? 0)),
            'vulnerable_members' => (float)($record['vulnerable_members'] ?? (($record['senior_citizens'] ?? 0) + ($record['pwds'] ?? 0))),
            'road_accessibility' => (string)($record['road_accessibility'] ?? 'Passable')
        ];
    }

    public static function isNumericFeature(string $feature): bool {
        return in_array($feature, [
            'affected_households',
            'affected_population',
            'is_urgent',
            'totally_damaged_houses',
            'partially_damaged_houses',
            'vulnerable_members'
        ], true);
    }
}
