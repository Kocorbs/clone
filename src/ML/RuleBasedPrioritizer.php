<?php
// src/ML/RuleBasedPrioritizer.php

namespace App\ML;

class RuleBasedPrioritizer {
    /**
     * Weights for multi-criteria disaster prioritization scoring rubric
     */
    public static array $weights = [
        'flood_exposure'       => 0.25, // 25% Flood hazard zone & river proximity
        'affected_households'  => 0.20, // 20% Scale of affected/displaced families
        'housing_damage'       => 0.20, // 20% Totally and partially damaged houses
        'vulnerability'        => 0.15, // 15% Senior citizens, PWDs, solo parents, 4Ps
        'field_accessibility' => 0.10, // 10% Verified road blockages & flood depths
        'unmet_needs'          => 0.10  // 10% Outstanding assistance deficit
    ];

    /**
     * Compute rule-based priority assessment for a purok profile
     */
    public static function evaluate(array $purokData, array $fieldReports = [], int $unmetNeeds = 0): array {
        // 1. Flood Exposure Score (0 - 100)
        $floodExposure = $purokData['flood_hazard_exposure'] ?? $purokData['risk_classification'] ?? 'Moderate';
        $exposureScores = [
            'Critical' => 100,
            'High'     => 80,
            'Medium'   => 55,
            'Moderate' => 55,
            'Low'      => 25,
            'Pending Verification' => 50
        ];
        $scoreExposure = $exposureScores[$floodExposure] ?? 50;

        // 2. Affected Households Score (0 - 100)
        // Scaled against maximum purok household density in Hinaplanon (~350 households in Purok 1 / 5A)
        $affectedHh = (int)($purokData['affected_households'] ?? ($purokData['households'] ?? 50));
        $scoreHouseholds = min(100, round(($affectedHh / 350.0) * 100, 2));

        // 3. Housing Damage Score (0 - 100)
        $totDmg = (int)($purokData['totally_damaged_houses'] ?? ($purokData['historical_totally_damaged'] ?? 0));
        $partDmg = (int)($purokData['partially_damaged_houses'] ?? ($purokData['historical_partially_damaged'] ?? 0));
        
        // Check active field reports for recent verified damage
        foreach ($fieldReports as $fr) {
            if (($fr['status'] ?? '') === 'Verified') {
                $dmgCount = (int)($fr['damaged_houses'] ?? 0);
                if ($dmgCount > 0) {
                    $partDmg += $dmgCount;
                }
            }
        }
        $damageWeighted = ($totDmg * 20) + ($partDmg * 3);
        $scoreDamage = min(100, round($damageWeighted, 2));

        // 4. Vulnerability Score (0 - 100)
        $seniors = (int)($purokData['senior_citizens'] ?? 0);
        $pwds = (int)($purokData['pwds'] ?? 0);
        $fourPs = (int)($purokData['four_ps_members'] ?? 0);
        $vulnCount = $seniors + ($pwds * 2) + $fourPs;
        $scoreVuln = min(100, round(($vulnCount / 120.0) * 100, 2));

        // 5. Field Accessibility Score (0 - 100)
        $scoreAccess = 20; // Default passable
        foreach ($fieldReports as $fr) {
            if (($fr['status'] ?? '') === 'Verified') {
                $acc = $fr['road_accessibility'] ?? 'Passable';
                if ($acc === 'Flooded' || $acc === 'Not Passable / Blocked') {
                    $scoreAccess = 100;
                    break;
                } elseif ($acc === 'Passable to Heavy Vehicles Only') {
                    $scoreAccess = max($scoreAccess, 70);
                }
            }
        }

        // 6. Unmet Needs Score (0 - 100)
        $scoreUnmet = min(100, round(($unmetNeeds / 200.0) * 100, 2));

        // Compute Weighted Composite Risk Score
        $compositeScore = round(
            ($scoreExposure * self::$weights['flood_exposure']) +
            ($scoreHouseholds * self::$weights['affected_households']) +
            ($scoreDamage * self::$weights['housing_damage']) +
            ($scoreVuln * self::$weights['vulnerability']) +
            ($scoreAccess * self::$weights['field_accessibility']) +
            ($scoreUnmet * self::$weights['unmet_needs']),
            2
        );

        // Determine Priority Level
        if ($compositeScore >= 75.0 || ($scoreExposure >= 80 && $totDmg >= 2)) {
            $priority = 'Critical';
        } elseif ($compositeScore >= 52.0 || ($scoreExposure >= 80)) {
            $priority = 'High';
        } elseif ($compositeScore >= 32.0) {
            $priority = 'Moderate';
        } else {
            $priority = 'Low';
        }

        // Confidence calculation based on data verification
        $confidence = 90.0;
        if (($purokData['risk_classification'] ?? '') === 'Pending Verification') {
            $confidence = 65.0;
        }

        return [
            'priority_level' => $priority,
            'composite_score' => $compositeScore,
            'confidence' => $confidence,
            'scores' => [
                'flood_exposure' => $scoreExposure,
                'affected_households' => $scoreHouseholds,
                'housing_damage' => $scoreDamage,
                'vulnerability' => $scoreVuln,
                'field_accessibility' => $scoreAccess,
                'unmet_needs' => $scoreUnmet
            ],
            'weights' => self::$weights
        ];
    }
}

