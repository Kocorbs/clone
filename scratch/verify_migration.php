<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = App\Core\Database::getInstance();

$countPuroks = $db->query("SELECT count(*) FROM purok")->fetchColumn();
$countVerifiedDisasters = $db->query("SELECT count(*) FROM historical_disaster_records WHERE is_synthetic = 0 AND is_verified = 1")->fetchColumn();
$totalPop = $db->query("SELECT sum(population) FROM purok")->fetchColumn();
$totalHH = $db->query("SELECT sum(households) FROM purok")->fetchColumn();

echo "Puroks count: {$countPuroks}\n";
echo "Verified disaster records count: {$countVerifiedDisasters}\n";
echo "Total Population across Puroks: {$totalPop}\n";
echo "Total Households across Puroks: {$totalHH}\n";

