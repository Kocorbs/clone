<?php
// scratch/build_purok_master_dataset.php

$baseDir = 'c:/Users/User/Downloads/thesis/scratch/unpacked/TS Basyang - Not final/xl';

// 1. Shared Strings
$sharedStrings = [];
if (file_exists($baseDir . '/sharedStrings.xml')) {
    $reader = new XMLReader();
    $reader->open($baseDir . '/sharedStrings.xml');
    $currentStr = "";
    while ($reader->read()) {
        if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName === 'si') {
            $currentStr = "";
        } elseif ($reader->nodeType == XMLReader::ELEMENT && $reader->localName === 't') {
            $currentStr .= $reader->readString();
        } elseif ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName === 'si') {
            $sharedStrings[] = $currentStr;
        }
    }
    $reader->close();
}

function parseSheetRows($sheetFile, $sharedStrings) {
    if (!file_exists($sheetFile)) return [];
    $xml = file_get_contents($sheetFile);
    preg_match_all('/<row [^>]*r="(\d+)"[^>]*>(.*?)<\/row>/s', $xml, $rowMatches, PREG_SET_ORDER);
    $rows = [];
    foreach ($rowMatches as $rm) {
        $rNum = (int)$rm[1];
        $cellsXml = $rm[2];
        preg_match_all('/<c [^>]*r="([A-Z]+)\d+"(?:[^>]*t="([^"]+)")?[^>]*>(?:<v>(.*?)<\/v>)?<\/c>/s', $cellsXml, $cMatches, PREG_SET_ORDER);
        $rowCells = [];
        foreach ($cMatches as $cm) {
            $col = $cm[1];
            $type = $cm[2] ?? '';
            $val = $cm[3] ?? '';
            if ($type === 's' && isset($sharedStrings[(int)$val])) {
                $val = $sharedStrings[(int)$val];
            }
            $rowCells[$col] = trim((string)$val);
        }
        $rows[$rNum] = $rowCells;
    }
    return $rows;
}

// Canonical Purok Normalizer
function normalizePurokName(string $raw): string {
    $r = trim($raw);
    if (empty($r)) return 'Unassigned';
    
    // Direct matches
    if (preg_match('/bayug\s*island/i', $r)) return 'Bayug Island';
    if (preg_match('/(purok\s*9\s*bayug\s*riverside|prk\s*9\s*bayug\s*riverside|purok\s*9[,\s]+bayug\s*riverside|purok\s*9\s*bayug\s*river)/i', $r)) return 'Purok 9 (Bayug Riverside)';
    if (preg_match('/purok\s*9a\s*bayug|prk\s*9a\s*bayug/i', $r)) return 'Purok 9A (Bayug)';
    if (preg_match('/purok\s*9/i', $r)) return 'Purok 9 (Bayug Riverside)';
    
    if (preg_match('/(zone\s*(?:diamond|emerald|moonstone|sapphire|ruby)|purok\s*1\s*hinaplanon|prk\s*1\s*hinaplanon|purok\s*1\b|prk\s*1\b)/i', $r)) return 'Purok 1';
    
    if (preg_match('/(purok\s*2a|prk\s*2a|green\s*village)/i', $r)) return 'Purok 2A (Green Village)';
    if (preg_match('/(purok\s*2b|prk\s*2b)/i', $r)) return 'Purok 2B (Katipunan)';
    if (preg_match('/(purok\s*2c|prk\s*2c)/i', $r)) return 'Purok 2C (Katipunan)';
    if (preg_match('/(purok\s*2\b|prk\s*2\b|katipunan)/i', $r)) return 'Purok 2 (Katipunan)';
    
    if (preg_match('/(purok\s*3a|prk\s*3a|pioneer|red\s*cross)/i', $r)) return 'Purok 3A (Pioneer Village & Red Cross Village)';
    if (preg_match('/(purok\s*3\s*dao|prk\s*3\s*dao|purok\s*dao)/i', $r)) return 'Purok 3 Dao';
    if (preg_match('/(purok\s*3\s*cabugason|prk\s*3\s*cabugason|purok\s*3\b|prk\s*3\b)/i', $r)) return 'Purok 3 (Cabugason)';
    
    if (preg_match('/(purok\s*4a|prk\s*4a)/i', $r)) return 'Purok 4A (Tambo)';
    if (preg_match('/(purok\s*4\b|prk\s*4\b)/i', $r)) return 'Purok 4 (Tambo)';
    
    if (preg_match('/(purok\s*5a|prk\s*5a)/i', $r)) return 'Purok 5A (Tambo)';
    if (preg_match('/(purok\s*5b|prk\s*5b|neupoda)/i', $r)) return 'Purok 5B (NEUPODA Village)';
    if (preg_match('/(purok\s*5\b|prk\s*5\b)/i', $r)) return 'Purok 5 (Tambo)';
    
    if (preg_match('/(purok\s*6a|prk\s*6a)/i', $r)) return 'Purok 6A (Tambo)';
    if (preg_match('/(purok\s*6b|prk\s*6b)/i', $r)) return 'Purok 6B (Tambo)';
    if (preg_match('/(purok\s*6c|prk\s*6c)/i', $r)) return 'Purok 6C (Tambo)';
    if (preg_match('/(purok\s*6\b|prk\s*6\b)/i', $r)) return 'Purok 6 (Tambo)';
    
    if (preg_match('/(purok\s*7|prk\s*7)/i', $r)) return 'Purok 7 (Tambo)';
    if (preg_match('/(purok\s*8|prk\s*8)/i', $r)) return 'Purok 8 (Tambo)';
    if (preg_match('/(phase\s*3|phase\s*5|bahayan|bayan)/i', $r)) return 'Phase 3 & 5 (Bahayan)';
    
    return 'Other / Unassigned';
}

echo "Testing normalization on sample strings...\n";
$samples = [
    'Purok 1 Hinaplanon - 352', 'Zone Moonstone, Purok 1', 'Bayug Island', 'Green Village, Purok 2A Katipunan',
    'PUROK 9 BAYUG RIVERSIDE', 'Purok 5B NEUPODA Village - 171', 'Purok 5A Tambo - 300', 'Purok 3 Dao',
    'Purok 3A Pioneer Village', 'Purok 4 Tambo', 'Purok 4A Tambo', 'Purok 7 Tambo', 'Purok 8 Tambo - 18'
];
foreach ($samples as $s) {
    echo " '{$s}' => " . normalizePurokName($s) . "\n";
}

// 2. Parse ECOWEB Relief (Sheet 3)
echo "\n--- Parsing ECOWEB Relief (Sheet 3) ---\n";
$rows3 = parseSheetRows($baseDir . '/worksheets/sheet3.xml', $sharedStrings);
$ecowebBeneficiariesByPurok = [];
$headerCols = $rows3[1] ?? [];
foreach ($rows3 as $rNum => $r) {
    if ($rNum < 2) continue;
    foreach ($r as $col => $val) {
        if ($col === 'A' || empty($val)) continue;
        $header = $headerCols[$col] ?? '';
        $normPurok = normalizePurokName($header);
        if ($normPurok !== 'Other / Unassigned') {
            $ecowebBeneficiariesByPurok[$normPurok][] = $val;
        }
    }
}
echo "ECOWEB Beneficiaries count by Normalized Purok:\n";
foreach ($ecowebBeneficiariesByPurok as $p => $list) {
    echo " - {$p}: " . count($list) . " beneficiaries\n";
}

// 3. Parse Totally Damaged (Sheet 4)
echo "\n--- Parsing TOTALLY Damaged (Sheet 4) ---\n";
$rows4 = parseSheetRows($baseDir . '/worksheets/sheet4.xml', $sharedStrings);
$totallyDamagedByPurok = [];
foreach ($rows4 as $rNum => $r) {
    if ($rNum < 2) continue;
    $pRaw = $r['H'] ?? '';
    if (empty($pRaw)) continue;
    $norm = normalizePurokName($pRaw);
    if ($norm !== 'Other / Unassigned') {
        $totallyDamagedByPurok[$norm][] = ($r['B'] ?? '') . ', ' . ($r['C'] ?? '');
    }
}
echo "Totally Damaged Houses by Purok:\n";
foreach ($totallyDamagedByPurok as $p => $list) {
    echo " - {$p}: " . count($list) . " houses\n";
}

// 4. Parse CARD Distribution (Sheet 8) for Partially vs Totally
echo "\n--- Parsing CARD Distribution (Sheet 8) ---\n";
$rows8 = parseSheetRows($baseDir . '/worksheets/sheet8.xml', $sharedStrings);
$partiallyDamagedByPurok = [];
$cardBeneficiariesByPurok = [];
foreach ($rows8 as $rNum => $r) {
    if ($rNum < 3) continue;
    $pRaw = $r['H'] ?? '';
    if (empty($pRaw)) continue;
    $norm = normalizePurokName($pRaw);
    if ($norm !== 'Other / Unassigned') {
        $cardBeneficiariesByPurok[$norm][] = ($r['B'] ?? '') . ', ' . ($r['C'] ?? '');
        // Sheet 8 column D is partially, E is totally or header indicates damage
        $partiallyDamagedByPurok[$norm] = ($partiallyDamagedByPurok[$norm] ?? 0) + 1;
    }
}
echo "CARD Beneficiaries by Purok:\n";
foreach ($cardBeneficiariesByPurok as $p => $list) {
    echo " - {$p}: " . count($list) . " beneficiaries\n";
}

// 5. Parse Senior Citizens (Sheet 9)
echo "\n--- Parsing Senior Citizens (Sheet 9) ---\n";
$rows9 = parseSheetRows($baseDir . '/worksheets/sheet9.xml', $sharedStrings);
$seniorsByPurok = [];
foreach ($rows9 as $rNum => $r) {
    if ($rNum < 3) continue;
    $pRaw = $r['H'] ?? '';
    if (empty($pRaw)) continue;
    $norm = normalizePurokName($pRaw);
    if ($norm !== 'Other / Unassigned') {
        $seniorsByPurok[$norm] = ($seniorsByPurok[$norm] ?? 0) + 1;
    }
}
echo "Senior Citizens by Purok:\n";
foreach ($seniorsByPurok as $p => $cnt) {
    echo " - {$p}: {$cnt} seniors\n";
}

