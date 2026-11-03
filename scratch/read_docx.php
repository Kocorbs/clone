<?php
$docxPath = 'c:/Users/User/Downloads/thesis/data/hinaplanon_raw/HINAPLANON_BDRRMPlan_2024-2026.docx';

$zip = new ZipArchive();
if ($zip->open($docxPath) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    // Strip tags or parse tables
    // Let's find mentions of "purok" or tables
    $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n---ROW---\n"], $xml));
    
    // Save to a text file for inspection
    file_put_contents('c:/Users/User/Downloads\thesis/scratch/bdrrm_plan_extracted.txt', $text);
    echo "Extracted docx text, size: " . strlen($text) . " bytes\n";
} else {
    echo "Failed to open docx\n";
}

