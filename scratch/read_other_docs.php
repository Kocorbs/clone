<?php
foreach (['Barangay Disaster Action Plan', 'BARANGAY TEMPORARY EMERGENCY RESPONSE PROGRAM'] as $folder) {
    $xmlPath = "c:/Users/User/Downloads/thesis/scratch/unpacked/{$folder}/word/document.xml";
    if (file_exists($xmlPath)) {
        $xml = file_get_contents($xmlPath);
        $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n---ROW---\n"], $xml));
        echo "\n============================================\n";
        echo "=== FOLDER: {$folder} (length: " . strlen($text) . ") ===\n";
        echo "============================================\n";
        // Show first 1500 chars
        echo substr($text, 0, 1500) . "\n...\n";
    }
}

