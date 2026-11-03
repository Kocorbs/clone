<?php
declare(strict_types=1);

// Write diagnostics only inside the web-protected private application folder.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/_app/database/php-errors.log');
error_reporting(E_ALL);

require __DIR__ . '/_app/public/index.php';
