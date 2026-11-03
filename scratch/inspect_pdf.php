<?php
$pdfPath = 'c:/Users/User/Downloads/thesis/data/hinaplanon_raw/Barangay Secretary.pdf';
$content = file_get_contents($pdfPath);
echo "PDF size: " . strlen($content) . " bytes\n";

// Search for streams or text
// Let's extract plain text strings
preg_match_all('/[\x20-\x7E\s]{4,100}/', $content, $matches);
$textSnippets = [];
foreach ($matches[0] as $m) {
    $trimmed = trim($m);
    if (strlen($trimmed) >= 4) {
        $textSnippets[] = $trimmed;
    }
}
echo "Total text snippets: " . count($textSnippets) . "\n";
// Print first 50 snippets
for ($i = 0; $i < min(50, count($textSnippets)); $i++) {
    echo " - " . $textSnippets[$i] . "\n";
}

