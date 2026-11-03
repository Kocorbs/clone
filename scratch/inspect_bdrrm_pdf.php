<?php
$pdfPath = 'c:/Users/User/Downloads/thesis/data/hinaplanon_raw/HINAPLANON_BDRRMCPlan_2024-2026.pdf';
$fp = fopen($pdfPath, 'rb');
$header = fread($fp, 1024);
fclose($fp);
echo "PDF Header: " . substr($header, 0, 100) . "\n";

// Check if it has flate decode streams with text
$content = file_get_contents($pdfPath);
preg_match_all('/\/Filter\s*\/FlateDecode/', $content, $flateMatches);
echo "FlateDecode streams: " . count($flateMatches[0]) . "\n";

// Search for literal text occurrences of "Zone Diamond" or "15,424" or numbers
if (strpos($content, '15,424') !== false) {
    echo "Found 15,424 in raw PDF\n";
}
if (strpos($content, 'Diamond') !== false) {
    echo "Found Diamond in raw PDF\n";
}

