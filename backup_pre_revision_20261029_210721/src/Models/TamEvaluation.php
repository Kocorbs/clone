<?php
// src/Models/TamEvaluation.php

namespace App\Models;

use App\Core\Database;

class TamEvaluation {
    public static function all(): array {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM tam_evaluations ORDER BY submitted_at DESC")->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO tam_evaluations (
            user_id, respondent_type,
            pu_q1, pu_q2, pu_q3, pu_q4,
            peou_q1, peou_q2, peou_q3, peou_q4,
            iso_functionality, iso_reliability, iso_usability, iso_efficiency, iso_maintainability, iso_portability,
            qualitative_feedback
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['user_id'] ?? null,
            $data['respondent_type'],
            (int)$data['pu_q1'],
            (int)$data['pu_q2'],
            (int)$data['pu_q3'],
            (int)$data['pu_q4'],
            (int)$data['peou_q1'],
            (int)$data['peou_q2'],
            (int)$data['peou_q3'],
            (int)$data['peou_q4'],
            (int)$data['iso_functionality'],
            (int)$data['iso_reliability'],
            (int)$data['iso_usability'],
            (int)$data['iso_efficiency'],
            (int)$data['iso_maintainability'],
            (int)$data['iso_portability'],
            $data['qualitative_feedback'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public static function getStatistics(): array {
        $records = self::all();
        $n = count($records);

        if ($n === 0) {
            return [
                'total_respondents' => 0,
                'breakdown' => [],
                'pu' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A'],
                'peou' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A'],
                'iso' => [
                    'functionality' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A'],
                    'reliability' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A'],
                    'usability' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A'],
                    'efficiency' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A'],
                    'maintainability' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A'],
                    'portability' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A'],
                ],
                'overall_quality' => ['mean' => 0, 'sd' => 0, 'rating' => 'N/A']
            ];
        }

        $breakdown = [];
        $puScores = [];
        $peouScores = [];
        $isoScores = [
            'functionality' => [],
            'reliability' => [],
            'usability' => [],
            'efficiency' => [],
            'maintainability' => [],
            'portability' => []
        ];
        $allIsoScores = [];

        foreach ($records as $r) {
            $type = $r['respondent_type'];
            $breakdown[$type] = ($breakdown[$type] ?? 0) + 1;

            $puAvg = ($r['pu_q1'] + $r['pu_q2'] + $r['pu_q3'] + $r['pu_q4']) / 4.0;
            $puScores[] = $puAvg;

            $peouAvg = ($r['peou_q1'] + $r['peou_q2'] + $r['peou_q3'] + $r['peou_q4']) / 4.0;
            $peouScores[] = $peouAvg;

            $isoScores['functionality'][] = (float)$r['iso_functionality'];
            $isoScores['reliability'][] = (float)$r['iso_reliability'];
            $isoScores['usability'][] = (float)$r['iso_usability'];
            $isoScores['efficiency'][] = (float)$r['iso_efficiency'];
            $isoScores['maintainability'][] = (float)$r['iso_maintainability'];
            $isoScores['portability'][] = (float)$r['iso_portability'];

            $allIsoScores[] = ((float)$r['iso_functionality'] + (float)$r['iso_reliability'] + (float)$r['iso_usability'] + (float)$r['iso_efficiency'] + (float)$r['iso_maintainability'] + (float)$r['iso_portability']) / 6.0;
        }

        $calc = function(array $arr) {
            $count = count($arr);
            if ($count === 0) return ['mean' => 0.0, 'sd' => 0.0, 'rating' => 'N/A'];
            $mean = array_sum($arr) / $count;
            $variance = 0.0;
            foreach ($arr as $val) {
                $variance += pow($val - $mean, 2);
            }
            $sd = $count > 1 ? sqrt($variance / ($count - 1)) : 0.0;
            return [
                'mean' => round($mean, 2),
                'sd' => round($sd, 2),
                'rating' => self::interpretLikert($mean)
            ];
        };

        $isoStats = [];
        foreach ($isoScores as $k => $vals) {
            $isoStats[$k] = $calc($vals);
        }

        return [
            'total_respondents' => $n,
            'breakdown' => $breakdown,
            'pu' => $calc($puScores),
            'peou' => $calc($peouScores),
            'iso' => $isoStats,
            'overall_quality' => $calc($allIsoScores)
        ];
    }

    public static function interpretLikert(float $score): string {
        if ($score >= 4.21) return 'Strongly Agree / Excellent';
        if ($score >= 3.41) return 'Agree / Very Satisfactory';
        if ($score >= 2.61) return 'Neutral / Satisfactory';
        if ($score >= 1.81) return 'Disagree / Fair';
        return 'Strongly Disagree / Poor';
    }
}
