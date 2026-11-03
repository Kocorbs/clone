<?php
$baseDir = 'c:/Users/User/Downloads/thesis/scratch/unpacked/TS Basyang - Not final/xl';

// 1. Read shared strings
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
echo "Shared strings count: " . count($sharedStrings) . "\n";

// 2. Read sheet names from workbook.xml
$sheets = [];
$wbXml = file_get_contents($baseDir . '/workbook.xml');
preg_match_all('/<sheet [^>]*name="([^"]+)"[^>]*sheetId="([^"]+)"[^>]*r:id="([^"]+)"/i', $wbXml, $matches, PREG_SET_ORDER);
foreach ($matches as $m) {
    $sheets[] = ['name' => $m[1], 'id' => $m[2], 'rId' => $m[3]];
    echo "Sheet: {$m[1]} (id: {$m[2]}, rId: {$m[3]})\n";
}

// 3. Inspect each sheet
foreach (glob($baseDir . '/worksheets/sheet*.xml') as $sheetFile) {
    $baseName = basename($sheetFile);
    echo "\n--- File: {$baseName} ---\n";
    $xml = file_get_contents($sheetFile);
    
    // Parse rows
    preg_match_all('/<row [^>]*r="(\d+)"[^>]*>(.*?)<\/row>/s', $xml, $rowMatches, PREG_SET_ORDER);
    echo "Total rows: " . count($rowMatches) . "\n";
    
    // Print first 5 rows
    $count = 0;
    foreach ($rowMatches as $rm) {
        $rNum = $rm[1];
        $cellsXml = $rm[2];
        preg_match_all('/<c [^>]*r="([A-Z]+\d+)"(?:[^>]*t="([^"]+)")?[^>]*>(?:<v>(.*?)<\/v>)?<\/c>/s', $cellsXml, $cMatches, PREG_SET_ORDER);
        $rowCells = [];
        foreach ($cMatches as $cm) {
            $ref = $cm[1];
            $type = $cm[2] ?? '';
            $val = $cm[3] ?? '';
            if ($type === 's' && isset($sharedStrings[(int)$val])) {
                $val = $sharedStrings[(int)$val];
            }
            $rowCells[] = "{$ref}: {$val}";
        }
        echo "Row {$rNum}: " . implode(" | ", array_slice($rowCells, 0, 8)) . "\n";
        $count++;
        if ($count >= 6) break;
    }
}

