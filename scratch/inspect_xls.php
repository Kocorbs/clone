<?php
$data = file_get_contents('c:/Users/User/Downloads/thesis/data/hinaplanon_raw/Totally and Partially Listing.xls');
echo "File size: " . strlen($data) . " bytes\n";

// Search for ASCII and UTF-16LE strings matching purok names, "totally", "partially", etc.
preg_match_all('/(?:[A-Z0-9\s,\-\.\(\)\/]{4,60})/i', $data, $matches);
$found = [];
foreach ($matches[0] as $str) {
    $s = trim($str);
    if (strlen($s) >= 4 && (stripos($s, 'purok') !== false || stripos($s, 'totally') !== false || stripos($s, 'partially') !== false || stripos($s, 'hinaplanon') !== false || stripos($s, 'bayug') !== false)) {
        $found[$s] = ($found[$s] ?? 0) + 1;
    }
}
echo "Matching strings found: " . count($found) . "\n";
$i = 0;
foreach ($found as $str => $c) {
    echo " - [{$c}x] {$str}\n";
    $i++;
    if ($i >= 30) break;
}

