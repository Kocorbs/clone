<?php
declare(strict_types=1);

// Run locally only. Never upload this script or the credentials text file.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$sqlFile = __DIR__ . '/initial_data_mysql.sql';
$credentialsFile = __DIR__ . '/ADMIN_LOGIN_PRIVATE.txt';
if (file_exists($sqlFile) || file_exists($credentialsFile)) {
    throw new RuntimeException('Initial data already generated; refusing to replace credentials.');
}
$password = bin2hex(random_bytes(12));
$hash = password_hash($password, PASSWORD_BCRYPT);
function quoteSql(string $value): string {
    return "'" . str_replace(["\\", "'", "\r", "\n"], ["\\\\", "''", '\\r', '\\n'], $value) . "'";
}
$sql = "-- Initial setup: one admin and existing project hazard classifications.\n";
$sql .= "-- No resident records, sample inventory, or demo passwords are imported.\n";
$sql .= "SET NAMES utf8mb4;\nSTART TRANSACTION;\n";
$sql .= "INSERT INTO barangay (barangay_name, location, latitude, longitude, population_size)\n";
$sql .= "SELECT 'Hinaplanon', 'Iligan City, Lanao del Norte, Northern Mindanao', 8.2412, 124.2543, 15636\n";
$sql .= "WHERE NOT EXISTS (SELECT 1 FROM barangay WHERE barangay_name = 'Hinaplanon');\n";
$sql .= "SET @barangay_id = (SELECT barangay_id FROM barangay WHERE barangay_name = 'Hinaplanon' LIMIT 1);\n";
$puroks = [
    ['Bayug Island', 'High', 'Pink'],
    ['Purok 1', 'High', 'Pink'],
    ['Purok 2', 'Medium', 'Orange'],
    ['Purok 2A', 'High', 'Pink'],
    ['Purok 2B', 'High', 'Pink'],
    ['Purok 2C', 'Medium', 'Orange'],
    ['Purok 2D', 'Medium', 'Orange'],
    ['Purok 3', 'Medium', 'Orange'],
    ['Purok 3A', 'High', 'Pink'],
    ['Purok 4A', 'Medium', 'Orange'],
    ['Purok 5 (Cabugason)', 'Medium', 'Orange'],
    ['Purok 5A', 'High', 'Pink'],
    ['Purok 6', 'Low', 'Yellow'],
    ['Purok 6A', 'Medium', 'Orange'],
    ['Purok 6B', 'Low', 'Yellow'],
    ['Purok 6C', 'Low', 'Yellow'],
    ['Purok 7', 'Low', 'Yellow'],
    ['Purok 8', 'Pending Verification', 'Gray'],
    ['Purok 9', 'High', 'Pink'],
    ['Phase 3 & 5 (Bayan)', 'Medium', 'Orange'],
];
foreach ($puroks as [$name, $risk, $color]) {
    $values = implode(', ', array_map('quoteSql', [$name, $risk, $color]));
    $sql .= "INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)\n";
    $sql .= "SELECT @barangay_id, $values WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = " . quoteSql($name) . ");\n";
}
$sql .= "INSERT INTO users (user_type, id_number, lastname, firstname, email, password_hash, is_approved)\n";
$sql .= "SELECT 'ICDRRMO', 'ADMIN-001', 'Administrator', 'System', 'icdrrmo@iligan.gov.ph', " . quoteSql($hash) . ", 1\n";
$sql .= "WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'icdrrmo@iligan.gov.ph');\nCOMMIT;\n";
file_put_contents($sqlFile, $sql);
file_put_contents($credentialsFile,
    "PRIVATE - DO NOT UPLOAD OR SHARE\r\n\r\n" .
    "Website: https://brgy-hinaplanon.ifree.page/\r\n" .
    "Email: icdrrmo@iligan.gov.ph\r\nPassword: $password\r\n\r\n" .
    "Use manual login, not Quick-Fill Demo Roles. Store securely in a password manager.\r\n"
);
if (!password_verify($password, $hash)) {
    throw new RuntimeException('Password verification failed.');
}
echo "Initial SQL and private login file created. Password hash verified. No secrets printed.\n";
