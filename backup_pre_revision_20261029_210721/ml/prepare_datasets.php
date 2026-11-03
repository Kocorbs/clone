<?php
// ml/prepare_datasets.php

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

// 1. Template CSV (Empty with headers and 1 sample row for guidance)
$templatePath = $dataDir . '/template_historical_disaster_records.csv';
$templateHeaders = [
    'disaster_date', 'disaster_type', 'weather_condition', 'affected_barangay', 
    'affected_purok', 'purok_risk_level', 'severity_level', 'is_urgent', 
    'affected_households', 'affected_population', 'requested_supply_type', 
    'requested_quantity', 'allocated_quantity', 'priority_label', 'data_source'
];

$fp = fopen($templatePath, 'w');
fputcsv($fp, $templateHeaders);
// Guidance row
fputcsv($fp, [
    '2026-02-15', 'Flood', 'Heavy Rain', 'Hinaplanon', 
    'Purok 1', 'High', 'Critical', '1', 
    '45', '180', 'Relief Goods', 
    '50', '50', 'Urgent', 'BDRRMC Historical Incident Log'
]);
fclose($fp);
echo "Created CSV Template: {$templatePath}\n";

// 2. Synthetic Demonstration Records (Strictly labeled as Demonstration / Synthetic)
$demoPath = $dataDir . '/hinaplanon_synthetic_demonstration_records.csv';
$fpDemo = fopen($demoPath, 'w');
fputcsv($fpDemo, $templateHeaders);

$disasterTypes = ['Flood', 'Flash Flood', 'Typhoon', 'Landslide'];
$weathers = ['Heavy Continuous Rain', 'Shear Line', 'Monsoon Surge', 'Tropical Depression'];

// Synthetic scenarios reflecting Sendong (2011), Pablo (2012), Basyang (2026), and seasonal river swells
$purokData = [
    ['Bayug Island', 'High', 'Critical', 1, 80, 320, 'Urgent'],
    ['Bayug Island', 'High', 'High', 1, 60, 240, 'Urgent'],
    ['Purok 1', 'High', 'Critical', 1, 55, 220, 'Urgent'],
    ['Purok 1', 'High', 'High', 1, 40, 160, 'High'],
    ['Purok 1', 'High', 'Medium', 0, 30, 120, 'Medium'],
    ['Purok 2A', 'High', 'Critical', 1, 50, 200, 'Urgent'],
    ['Purok 2A', 'High', 'High', 1, 35, 140, 'High'],
    ['Purok 2B', 'High', 'High', 1, 45, 180, 'High'],
    ['Purok 3A', 'High', 'High', 0, 30, 120, 'High'],
    ['Purok 5A', 'High', 'Critical', 1, 40, 160, 'Urgent'],
    ['Purok 9', 'High', 'High', 1, 35, 140, 'High'],
    ['Purok 2', 'Medium', 'Medium', 0, 25, 100, 'Medium'],
    ['Purok 2C', 'Medium', 'High', 1, 30, 120, 'High'],
    ['Purok 2D', 'Medium', 'Medium', 0, 20, 80, 'Medium'],
    ['Purok 3', 'Medium', 'Medium', 0, 25, 100, 'Medium'],
    ['Purok 4A', 'Medium', 'Low', 0, 15, 60, 'Low'],
    ['Purok 5 (Cabugason)', 'Medium', 'Medium', 0, 35, 140, 'Medium'],
    ['Purok 6A', 'Medium', 'Low', 0, 15, 60, 'Low'],
    ['Phase 3 & 5 (Bayan)', 'Medium', 'Low', 0, 20, 80, 'Low'],
    ['Purok 6', 'Low', 'Low', 0, 10, 40, 'Low'],
    ['Purok 6B', 'Low', 'Low', 0, 12, 48, 'Low'],
    ['Purok 6C', 'Low', 'Low', 0, 10, 40, 'Low'],
    ['Purok 7', 'Low', 'Low', 0, 8, 32, 'Low'],
    ['Purok 8', 'Pending Verification', 'Medium', 0, 20, 80, 'Medium'],
];

// Generate 120 structured demonstration examples covering various combinations
$recordsCount = 0;
for ($round = 1; $round <= 5; $round++) {
    foreach ($purokData as $base) {
        $pName = $base[0];
        $pRisk = $base[1];
        $pSev = $base[2];
        $pUrg = $base[3];
        $pHouseholds = max(5, $base[4] + rand(-5, 15));
        $pPop = $pHouseholds * rand(3, 5);
        $pPriority = $base[6];

        // Slight realistic variance
        if ($pRisk === 'High' && $pSev === 'Critical') {
            $pPriority = 'Urgent';
        } elseif ($pRisk === 'Low' && $pSev === 'Low') {
            $pPriority = 'Low';
        }

        $date = date('Y-m-d', strtotime("-" . rand(30, 700) . " days"));
        $dType = $disasterTypes[array_rand($disasterTypes)];
        $wCond = $weathers[array_rand($weathers)];
        $suppType = ($dType === 'Flood' || $dType === 'Flash Flood') ? 'Relief Goods' : 'Medical Supplies';
        $reqQty = $pHouseholds;
        $allocQty = ($pPriority === 'Urgent') ? (int)ceil($reqQty * 1.1) : (int)ceil($reqQty * 0.9);

        fputcsv($fpDemo, [
            $date, $dType, $wCond, 'Hinaplanon',
            $pName, $pRisk, $pSev, $pUrg,
            $pHouseholds, $pPop, $suppType,
            $reqQty, $allocQty, $pPriority,
            'Synthetic Demonstration Dataset (Hinaplanon Scenario Modeling)'
        ]);
        $recordsCount++;
    }
}

fclose($fpDemo);
echo "Created Synthetic Demonstration Dataset ({$recordsCount} records): {$demoPath}\n";
