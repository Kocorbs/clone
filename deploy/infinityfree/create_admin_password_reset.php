<?php
declare(strict_types=1);

// Local recovery utility. Never upload this script or the private login file.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$credentials = file_get_contents(__DIR__ . '/ADMIN_LOGIN_PRIVATE.txt');
if ($credentials === false || !preg_match('/^Password: ([a-f0-9]{24})\r?$/m', $credentials, $match)) {
    throw new RuntimeException('Expected generated login file was not found or has changed.');
}
$hash = password_hash($match[1], PASSWORD_BCRYPT);
if (!password_verify($match[1], $hash)) {
    throw new RuntimeException('Password hash validation failed.');
}
$sql = "-- Restore only this admin's password to the existing private login file.\n";
$sql .= "UPDATE `if0_43310179_hinaplanon_drrm`.`users`\n";
$sql .= "SET `password_hash` = '" . $hash . "'\n";
$sql .= "WHERE `user_id` = 1 AND `email` = 'icdrrmo@iligan.gov.ph' AND `user_type` = 'ICDRRMO' AND `deleted_at` IS NULL;\n";
file_put_contents(__DIR__ . '/reset_admin_password.sql', $sql);
echo "Admin password recovery SQL generated and hash verified; no password printed.\n";
