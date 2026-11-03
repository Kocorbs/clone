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

echo "Checking all tables for purok rows with non-empty numbers...\n";
foreach ($tables as $tIdx => $tbl) {
    foreach ($tbl as $rIdx => $row) {
        $rowStr = implode(" | ", $row);
        if (preg_match('/purok\s*\d/i', $rowStr) || preg_match('/bayug/i', $rowStr)) {
            // Check if there are numbers in this row
            if (preg_match('/\d+/', $rowStr)) {
                echo "T#{$tIdx} R#{$rIdx}: {$rowStr}\n";
            }
        }
    }
}

