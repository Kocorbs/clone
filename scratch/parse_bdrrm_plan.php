<?php
$xmlPath = 'c:/Users/User/Downloads/thesis/scratch/unpacked/HINAPLANON_BDRRMPlan_2024-2026/word/document.xml';

$reader = new XMLReader();
$reader->open($xmlPath);

$tables = [];
$currentTable = [];
$currentRow = [];
$currentCell = "";
$inCell = false;

while ($reader->read()) {
    if ($reader->nodeType == XMLReader::ELEMENT) {
        if ($reader->localName === 'tbl') {
            $currentTable = [];
        } elseif ($reader->localName === 'tr') {
            $currentRow = [];
        } elseif ($reader->localName === 'tc') {
            $currentCell = "";
            $inCell = true;
        } elseif ($reader->localName === 't' && $inCell) {
            $currentCell .= $reader->readString();
        }
    } elseif ($reader->nodeType == XMLReader::END_ELEMENT) {
        if ($reader->localName === 'tc') {
            $currentRow[] = trim($currentCell);
            $inCell = false;
        } elseif ($reader->localName === 'tr') {
            if (!empty($currentRow)) {
                $currentTable[] = $currentRow;
            }
        } elseif ($reader->localName === 'tbl') {
            if (!empty($currentTable)) {
                $tables[] = $currentTable;
            }
        }
    }
}
$reader->close();

echo "Total tables found in BDRRM plan: " . count($tables) . "\n";

foreach ($tables as $idx => $tbl) {
    $tblStr = "";
    foreach ($tbl as $row) {
        $tblStr .= implode(" | ", $row) . "\n";
    }
    // Search for tables mentioning purok, population, household, vulnerability, or 23 puroks
    if (stripos($tblStr, 'purok') !== false || stripos($tblStr, 'household') !== false || stripos($tblStr, 'population') !== false) {
        echo "\n=== TABLE #{$idx} (Rows: " . count($tbl) . ") ===\n";
        // Show first 10 rows
        for ($i = 0; $i < min(15, count($tbl)); $i++) {
            echo implode(" | ", $tbl[$i]) . "\n";
        }
        if (count($tbl) > 15) {
            echo "... (" . (count($tbl) - 15) . " more rows)\n";
        }
    }
}

