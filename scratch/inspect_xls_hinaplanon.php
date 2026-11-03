<?php
$data = file_get_contents('c:/Users/User/Downloads/thesis/data/hinaplanon_raw/Totally and Partially Listing.xls');
preg_match_all('/(?:[^\x00-\x1F\x7F-\xFF]{4,80})/i', $data, $matches);
$hinaplanonMatches = [];
foreach ($matches[0] as $str) {
    if (stripos($str, 'hinaplanon') !== false) {
        $hinaplanonMatches[trim($str)] = ($hinaplanonMatches[trim($str)] ?? 0) + 1;
    }
}
echo "Hinaplanon occurrences: " . count($hinaplanonMatches) . "\n";
foreach (array_slice($hinaplanonMatches, 0, 30) as $s => $c) {
    echo " - [{$c}x] {$s}\n";
}

