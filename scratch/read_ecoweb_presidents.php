<?php
$baseDir = 'c:/Users/User/Downloads/thesis/scratch/unpacked/TS Basyang - Not final/xl';

// Load shared strings
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

function parseSheetRowsSimple($sheetFile, $sharedStrings) {
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

echo "=== SHEET 3: ECOWEB Relief ===\n";
$rows3 = parseSheetRowsSimple($baseDir . '/worksheets/sheet3.xml', $sharedStrings);
echo "Header Row 1: " . implode(" | ", $rows3[1] ?? []) . "\n";
echo "Header Row 2: " . implode(" | ", $rows3[2] ?? []) . "\n";
// Let's count non-empty values per column
$colCounts = [];
foreach ($rows3 as $rNum => $r) {
    if ($rNum < 2) continue;
    foreach ($r as $col => $val) {
        if (!empty($val) && $col !== 'A') {
            $colCounts[$col] = ($colCounts[$col] ?? 0) + 1;
        }
    }
}
foreach ($colCounts as $col => $cnt) {
    $headerName = $rows3[1][$col] ?? "Col {$col}";
    echo " - Col {$col} ({$headerName}): {$cnt} records\n";
}

echo "\n=== SHEET 5: PUROK PRESIDENTS LISTINGS ===\n";
$rows5 = parseSheetRowsSimple($baseDir . '/worksheets/sheet5.xml', $sharedStrings);
echo "Header Row 1: " . implode(" | ", $rows5[1] ?? []) . "\n";
$colCounts5 = [];
foreach ($rows5 as $rNum => $r) {
    if ($rNum < 2) continue;
    foreach ($r as $col => $val) {
        if (!empty($val) && $col !== 'A') {
            $colCounts5[$col] = ($colCounts5[$col] ?? 0) + 1;
        }
    }
}
foreach ($colCounts5 as $col => $cnt) {
    $headerName = $rows5[1][$col] ?? "Col {$col}";
    echo " - Col {$col} ({$headerName}): {$cnt} records\n";
}

echo "\n=== SHEET 12: ADDITIONS ===\n";
$rows12 = parseSheetRowsSimple($baseDir . '/worksheets/sheet12.xml', $sharedStrings);
echo "Header Row 1: " . implode(" | ", $rows12[1] ?? []) . "\n";
$colCounts12 = [];
foreach ($rows12 as $rNum => $r) {
    if ($rNum < 2) continue;
    foreach ($r as $col => $val) {
        if (!empty($val) && $col !== 'A') {
            $colCounts12[$col] = ($colCounts12[$col] ?? 0) + 1;
        }
    }
}
foreach ($colCounts12 as $col => $cnt) {
    $headerName = $rows12[1][$col] ?? "Col {$col}";
    echo " - Col {$col} ({$headerName}): {$cnt} records\n";
}

