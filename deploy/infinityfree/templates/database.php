<?php
// Hosting-only configuration. Set the password using the hosting File Manager.
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'sql102.infinityfree.com',
            'port' => 3306,
            'database' => 'if0_43310179_hinaplanon_drrm',
            'username' => 'if0_43310179',
            'password' => 'REPLACE_WITH_HOSTING_PASSWORD',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];
