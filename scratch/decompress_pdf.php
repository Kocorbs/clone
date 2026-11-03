<?php
$pdf = file_get_contents('c:/Users/User/Downloads/thesis/data/hinaplanon_raw/HINAPLANON_BDRRMCPlan_2024-2026.pdf');
preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $pdf, $matches);
echo "Total streams: " . count($matches[1]) . "\n";
$fullText = "";
foreach ($matches[1] as $s) {
    $decomp = @gzuncompress($s);
    if ($decomp !== false) {
        // extract BT ... ET text
        preg_match_all('/\((.*?)\)\s*Tj/s', $decomp, $tjs);
        foreach ($tjs[1] as $t) {
            $fullText .= $t . " ";
        }
    }
}
echo "Decompressed text length: " . strlen($fullText) . "\n";
if (strpos($fullText, 'Diamond') !== false) {
    echo "Found Diamond!\n";
    // Search around Diamond
    $pos = strpos($fullText, 'Diamond');
    echo substr($fullText, max(0, $pos - 100), 400) . "\n";
}

