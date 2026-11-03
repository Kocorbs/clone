<?php
$baseDir = 'c:/Users/User/Downloads/thesis/scratch/unpacked/TS Basyang - Not final/xl';

// 1. Load shared strings
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

// 1. Analyze Sheet 1 (CARD Final)
echo "=== SHEET 1: CARD Final ===\n";
$rows1 = parseSheetRows($baseDir . '/worksheets/sheet1.xml', $sharedStrings);
echo "Total rows: " . count($rows1) . "\n";
echo "Header: " . implode(" | ", $rows1[2] ?? []) . "\n";
$purokCounts1 = [];
$vulnCounts1 = [];
foreach ($rows1 as $rNum => $r) {
    if ($rNum <= 2) continue;
    $purok = $r['H'] ?? 'Unknown';
    if (!empty($purok)) {
        $purokCounts1[$purok] = ($purokCounts1[$purok] ?? 0) + 1;
    }
    $vuln = $r['O'] ?? '';
    if (!empty($vuln)) {
        $vulnCounts1[$vuln] = ($vulnCounts1[$vuln] ?? 0) + 1;
    }
}
echo "Puroks in CARD Final:\n";
arsort($purokCounts1);
foreach ($purokCounts1 as $p => $c) {
    echo " - {$p}: {$c} households\n";
}
echo "Vulnerabilities in CARD Final:\n";
foreach ($vulnCounts1 as $v => $c) {
    echo " - {$v}: {$c}\n";
}

// 2. Analyze Sheet 4 (TOTALLY Damaged)
echo "\n=== SHEET 4: TOTALLY Damaged Houses ===\n";
$rows4 = parseSheetRows($baseDir . '/worksheets/sheet4.xml', $sharedStrings);
echo "Total rows: " . count($rows4) . "\n";
echo "Header: " . implode(" | ", $rows4[1] ?? []) . "\n";
$purokCounts4 = [];
foreach ($rows4 as $rNum => $r) {
    if ($rNum <= 1) continue;
    $purok = $r['H'] ?? 'Unknown';
    if (!empty($purok)) {
        $purokCounts4[$purok] = ($purokCounts4[$purok] ?? 0) + 1;
    }
}
echo "Puroks in TOTALLY Damaged:\n";
arsort($purokCounts4);
foreach ($purokCounts4 as $p => $c) {
    echo " - {$p}: {$c}\n";
}

// 3. Analyze Sheet 8 (CARD Distribution - Partially vs Totally)
echo "\n=== SHEET 8: CARD Distribution (Damage Types & Status) ===\n";
$rows8 = parseSheetRows($baseDir . '/worksheets/sheet8.xml', $sharedStrings);
echo "Total rows: " . count($rows8) . "\n";
echo "Header: " . implode(" | ", $rows8[2] ?? []) . "\n";
$purokCounts8 = [];
$dmgCounts8 = [];
foreach ($rows8 as $rNum => $r) {
    if ($rNum <= 2) continue;
    $purok = $r['H'] ?? 'Unknown';
    if (!empty($purok)) {
        $purokCounts8[$purok] = ($purokCounts8[$purok] ?? 0) + 1;
    }
}
echo "Puroks in CARD Distribution:\n";
arsort($purokCounts8);
foreach ($purokCounts8 as $p => $c) {
    echo " - {$p}: {$c}\n";
}

