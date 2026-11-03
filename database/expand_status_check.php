<?php
// database/expand_status_check.php

require_once __DIR__ . '/../src/Core/Database.php';

$db = App\Core\Database::getInstance();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Expanding status check constraint on disaster_request_form...\n";

$db->exec("PRAGMA foreign_keys=off;");
$db->beginTransaction();

try {
    // 1. Create table with expanded statuses
    $db->exec("CREATE TABLE disaster_request_form_expanded (
        disaster_request_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        barangay_id INTEGER NOT NULL,
        purok_id INTEGER NOT NULL,
        request_id INTEGER,
        disaster_type TEXT NOT NULL,
        weather_name TEXT NOT NULL,
        description TEXT NOT NULL,
        severity_level TEXT NOT NULL,
        is_urgent INTEGER NOT NULL DEFAULT 0,
        affected_households INTEGER NOT NULL DEFAULT 1,
        affected_population INTEGER NOT NULL DEFAULT 1,
        disaster_date DATE NOT NULL,
        status TEXT NOT NULL DEFAULT 'Pending' CHECK(status IN (
            'Pending', 'Recommended', 'Approved', 'Declined', 'Completed',
            'Reported', 'Under Verification', 'Verified', 'Assigned', 'Responding', 'Resolved', 'Rejected'
        )),
        latitude REAL,
        longitude REAL,
        reference_no TEXT,
        assigned_to_user_id INTEGER,
        assigned_at DATETIME,
        verified_by_user_id INTEGER,
        verified_at DATETIME,
        is_sos INTEGER DEFAULT 0,
        contact_number TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        deleted_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (barangay_id) REFERENCES barangay(barangay_id),
        FOREIGN KEY (purok_id) REFERENCES purok(purok_id),
        FOREIGN KEY (assigned_to_user_id) REFERENCES users(user_id),
        FOREIGN KEY (verified_by_user_id) REFERENCES users(user_id)
    )");

    // 2. Copy all data
    $db->exec("INSERT INTO disaster_request_form_expanded (
        disaster_request_id, user_id, barangay_id, purok_id, request_id,
        disaster_type, weather_name, description, severity_level, is_urgent,
        affected_households, affected_population, disaster_date, status,
        latitude, longitude, reference_no, assigned_to_user_id, assigned_at,
        verified_by_user_id, verified_at, is_sos, contact_number,
        created_at, updated_at, deleted_at
    ) SELECT 
        disaster_request_id, user_id, barangay_id, purok_id, request_id,
        disaster_type, weather_name, description, severity_level, is_urgent,
        affected_households, affected_population, disaster_date, status,
        latitude, longitude, reference_no, assigned_to_user_id, assigned_at,
        verified_by_user_id, verified_at, is_sos, contact_number,
        created_at, updated_at, deleted_at
    FROM disaster_request_form");

    // 3. Drop old table and rename
    $db->exec("DROP TABLE disaster_request_form");
    $db->exec("ALTER TABLE disaster_request_form_expanded RENAME TO disaster_request_form");

    $db->commit();
    $db->exec("PRAGMA foreign_keys=on;");

    echo "Status CHECK constraint expanded successfully!\n";
} catch (Exception $e) {
    $db->rollBack();
    $db->exec("PRAGMA foreign_keys=on;");
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
