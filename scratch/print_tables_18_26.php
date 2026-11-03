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

echo "=== TABLE 18 (SITIO/PUROK) ===\n";
foreach ($tables[18] as $r) {
    echo implode(" | ", $r) . "\n";
}

echo "\n=== TABLE 26 (POPULATION AFFECTED) ===\n";
foreach ($tables[26] as $r) {
    echo implode(" | ", $r) . "\n";
}

