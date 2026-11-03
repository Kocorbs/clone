<?php
$pdf = file_get_contents('c:/Users/User/Downloads/thesis/data/hinaplanon_raw/Barangay Secretary.pdf');

// Find all streams containing JFIF
$pos = 0;
$imgIdx = 1;
while (($pos = strpos($pdf, "\xFF\xD8\xFF", $pos)) !== false) {
    $end = strpos($pdf, "\xFF\xD9", $pos);
    if ($end !== false) {
        $len = $end - $pos + 2;
        $jpegData = substr($pdf, $pos, $len);
        file_put_contents("c:/Users/User/Downloads/thesis/scratch/extracted_secretary_{$imgIdx}.jpg", $jpegData);
        echo "Extracted image {$imgIdx}: {$len} bytes\n";
        $imgIdx++;
        $pos = $end + 2;
    } else {
        break;
    }
}

