<?php
// database/migration_v2.php

require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

echo "=== Running Database Migration v2 (Enhancements & Additive Features) ===\n";

$db = Database::getInstance();
$driver = Database::getDriver();

echo "Database Driver: {$driver}\n";

$db->beginTransaction();

try {
    // 1. Create notifications table
    $db->exec("CREATE TABLE IF NOT EXISTS notifications (
        notification_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NULL,
        target_role TEXT NULL,
        notification_type TEXT NOT NULL,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        related_record_type TEXT NULL,
        related_record_id INTEGER NULL,
        priority TEXT NOT NULL DEFAULT 'Normal' CHECK(priority IN ('Urgent', 'High', 'Normal', 'Info')),
        is_read INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        read_at DATETIME NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    );");
    echo "  [OK] notifications table verified/created.\n";

    // 2. Create evacuation_shelters table
    $db->exec("CREATE TABLE IF NOT EXISTS evacuation_shelters (
        shelter_id INTEGER PRIMARY KEY AUTOINCREMENT,
        shelter_name TEXT NOT NULL,
        purok_id INTEGER NOT NULL,
        location TEXT NOT NULL,
        current_occupancy INTEGER NOT NULL DEFAULT 0,
        max_capacity INTEGER NOT NULL DEFAULT 100,
        availability_status TEXT NOT NULL DEFAULT 'Open' CHECK(availability_status IN ('Open', 'Near Full', 'Full', 'Closed')),
        contact_person TEXT,
        contact_number TEXT,
        latitude REAL,
        longitude REAL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (purok_id) REFERENCES purok(purok_id)
    );");
    echo "  [OK] evacuation_shelters table verified/created.\n";

    // 3. Create incident_status_history table
    $db->exec("CREATE TABLE IF NOT EXISTS incident_status_history (
        history_id INTEGER PRIMARY KEY AUTOINCREMENT,
        disaster_request_id INTEGER NOT NULL,
        changed_by_user_id INTEGER NOT NULL,
        previous_status TEXT NOT NULL,
        new_status TEXT NOT NULL,
        remarks TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (disaster_request_id) REFERENCES disaster_request_form(disaster_request_id),
        FOREIGN KEY (changed_by_user_id) REFERENCES users(user_id)
    );");
    echo "  [OK] incident_status_history table verified/created.\n";

    // 4. Create audit_logs table
    $db->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        audit_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NULL,
        user_name TEXT NOT NULL,
        user_role TEXT NOT NULL,
        action TEXT NOT NULL,
        entity_type TEXT NOT NULL,
        entity_id INTEGER NULL,
        details TEXT,
        ip_address TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    );");
    echo "  [OK] audit_logs table verified/created.\n";

    // 5. Additive columns for disaster_request_form
    $drCols = $db->query("PRAGMA table_info(disaster_request_form)")->fetchAll(PDO::FETCH_ASSOC);
    $drColNames = array_column($drCols, 'name');

    if (!in_array('reference_no', $drColNames)) {
        $db->exec("ALTER TABLE disaster_request_form ADD COLUMN reference_no TEXT NULL;");
        echo "  [OK] Added column reference_no to disaster_request_form.\n";
    }
    if (!in_array('assigned_to_user_id', $drColNames)) {
        $db->exec("ALTER TABLE disaster_request_form ADD COLUMN assigned_to_user_id INTEGER NULL;");
        echo "  [OK] Added column assigned_to_user_id to disaster_request_form.\n";
    }
    if (!in_array('assigned_at', $drColNames)) {
        $db->exec("ALTER TABLE disaster_request_form ADD COLUMN assigned_at DATETIME NULL;");
        echo "  [OK] Added column assigned_at to disaster_request_form.\n";
    }
    if (!in_array('verified_by_user_id', $drColNames)) {
        $db->exec("ALTER TABLE disaster_request_form ADD COLUMN verified_by_user_id INTEGER NULL;");
        echo "  [OK] Added column verified_by_user_id to disaster_request_form.\n";
    }
    if (!in_array('verified_at', $drColNames)) {
        $db->exec("ALTER TABLE disaster_request_form ADD COLUMN verified_at DATETIME NULL;");
        echo "  [OK] Added column verified_at to disaster_request_form.\n";
    }
    if (!in_array('is_sos', $drColNames)) {
        $db->exec("ALTER TABLE disaster_request_form ADD COLUMN is_sos INTEGER NOT NULL DEFAULT 0;");
        echo "  [OK] Added column is_sos to disaster_request_form.\n";
    }
    if (!in_array('contact_number', $drColNames)) {
        $db->exec("ALTER TABLE disaster_request_form ADD COLUMN contact_number TEXT NULL;");
        echo "  [OK] Added column contact_number to disaster_request_form.\n";
    }

    // 6. Additive columns for users table
    $uCols = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
    $uColNames = array_column($uCols, 'name');

    if (!in_array('middle_name', $uColNames)) {
        $db->exec("ALTER TABLE users ADD COLUMN middle_name TEXT NULL;");
        echo "  [OK] Added column middle_name to users.\n";
    }
    if (!in_array('age', $uColNames)) {
        $db->exec("ALTER TABLE users ADD COLUMN age INTEGER NULL;");
        echo "  [OK] Added column age to users.\n";
    }
    if (!in_array('birthdate', $uColNames)) {
        $db->exec("ALTER TABLE users ADD COLUMN birthdate DATE NULL;");
        echo "  [OK] Added column birthdate to users.\n";
    }
    if (!in_array('purok_id', $uColNames)) {
        $db->exec("ALTER TABLE users ADD COLUMN purok_id INTEGER NULL;");
        echo "  [OK] Added column purok_id to users.\n";
    }
    if (!in_array('username', $uColNames)) {
        $db->exec("ALTER TABLE users ADD COLUMN username TEXT NULL;");
        echo "  [OK] Added column username to users.\n";
    }

    // Make sure id_number is indexed/unique where not null
    $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_id_number ON users(id_number) WHERE id_number IS NOT NULL AND id_number != '';");
    echo "  [OK] Verified unique index on users(id_number).\n";

    // 7. Seed Evacuation Shelters if empty
    $shelterCount = (int)$db->query("SELECT COUNT(*) FROM evacuation_shelters")->fetchColumn();
    if ($shelterCount === 0) {
        $purok1Id = (int)$db->query("SELECT purok_id FROM purok WHERE purok_name = 'Purok 1' LIMIT 1")->fetchColumn() ?: 2;
        $purok6Id = (int)$db->query("SELECT purok_id FROM purok WHERE purok_name = 'Purok 6' LIMIT 1")->fetchColumn() ?: 13;
        $purok2Id = (int)$db->query("SELECT purok_id FROM purok WHERE purok_name = 'Purok 2' LIMIT 1")->fetchColumn() ?: 3;

        $shelters = [
            [
                'shelter_name' => 'Hinaplanon Barangay Gym (Main Evacuation Center)',
                'purok_id' => $purok1Id,
                'location' => 'Purok 1, National Highway Junction, Hinaplanon',
                'current_occupancy' => 85,
                'max_capacity' => 350,
                'availability_status' => 'Open',
                'contact_person' => 'Kgd. Roberto Tan',
                'contact_number' => '0917-555-1201',
                'latitude' => 8.2435,
                'longitude' => 124.2562
            ],
            [
                'shelter_name' => 'South Hinaplanon Evacuation Center',
                'purok_id' => $purok6Id,
                'location' => 'Purok 6 Upland Safe Zone, Hinaplanon',
                'current_occupancy' => 40,
                'max_capacity' => 250,
                'availability_status' => 'Open',
                'contact_person' => 'Sec. Elena Morales',
                'contact_number' => '0918-555-2302',
                'latitude' => 8.2380,
                'longitude' => 124.2520
            ],
            [
                'shelter_name' => 'Hinaplanon Central Elementary School Covered Court',
                'purok_id' => $purok2Id,
                'location' => 'Purok 2 School Campus, Hinaplanon',
                'current_occupancy' => 0,
                'max_capacity' => 200,
                'availability_status' => 'Open',
                'contact_person' => 'Principal Jose Almendras',
                'contact_number' => '0919-555-3403',
                'latitude' => 8.2410,
                'longitude' => 124.2548
            ],
        ];

        $insShelter = $db->prepare("INSERT INTO evacuation_shelters (
            shelter_name, purok_id, location, current_occupancy, max_capacity, 
            availability_status, contact_person, contact_number, latitude, longitude
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($shelters as $sh) {
            $insShelter->execute([
                $sh['shelter_name'], $sh['purok_id'], $sh['location'],
                $sh['current_occupancy'], $sh['max_capacity'], $sh['availability_status'],
                $sh['contact_person'], $sh['contact_number'], $sh['latitude'], $sh['longitude']
            ]);
        }
        echo "  [OK] Seeded 3 official evacuation shelters with coordinates and capacities.\n";
    }

    // 8. Seed Initial Notifications for roles if empty
    $notifCount = (int)$db->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    if ($notifCount === 0) {
        $insNotif = $db->prepare("INSERT INTO notifications (
            user_id, target_role, notification_type, title, message, priority, is_read, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Alert for residents
        $insNotif->execute([
            null, 'Resident', 'emergency_alert',
            'Weather Advisory: Continuous Heavy Rains (Shear Line)',
            'DOST-PAGASA and ICDRRMO advise all residents in riverside Puroks (Bayug Island, P-1, P-2A, P-2B, P-3A, P-9) to prepare 72-hour Go-Bags and monitor Mandulog River water levels.',
            'High', 0, date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]);

        // Alert for responders
        $insNotif->execute([
            null, 'Responder', 'incident_assigned',
            'Pre-Positioning Order: Water Rescue Assets',
            'Responders are directed to inspect inflatable rescue boats and stage equipment at Hinaplanon Barangay Gym.',
            'Urgent', 0, date('Y-m-d H:i:s', strtotime('-1 hour'))
        ]);

        // Alert for Barangay Head
        $insNotif->execute([
            null, 'Barangay Head', 'registration_approval',
            'Pending Resident Registrations Awaiting Verification',
            'New resident registration requests have been submitted and require identity verification.',
            'Normal', 0, date('Y-m-d H:i:s', strtotime('-30 minutes'))
        ]);

        // Alert for ICDRRMO
        $insNotif->execute([
            null, 'ICDRRMO', 'shelter_warning',
            'Shelter Monitoring: Main Gym at 24% Capacity',
            'Hinaplanon Barangay Gym currently accommodates 85 individuals (Max: 350). Supplies are reported adequate.',
            'Info', 0, date('Y-m-d H:i:s', strtotime('-15 minutes'))
        ]);

        echo "  [OK] Seeded initial system notifications across all roles.\n";
    }

    // Ensure Purok records have geographic coordinates for Leaflet map markers
    $purokCoords = [
        'Bayug Island' => [8.2520, 124.2505],
        'Purok 1' => [8.2435, 124.2560],
        'Purok 2' => [8.2415, 124.2545],
        'Purok 2A' => [8.2448, 124.2530],
        'Purok 2B' => [8.2465, 124.2520],
        'Purok 2C' => [8.2405, 124.2535],
        'Purok 2D' => [8.2425, 124.2580],
        'Purok 3' => [8.2395, 124.2555],
        'Purok 3A' => [8.2470, 124.2505],
        'Purok 4A' => [8.2375, 124.2535],
        'Purok 5 (Cabugason)' => [8.2430, 124.2610],
        'Purok 5A' => [8.2485, 124.2490],
        'Purok 6' => [8.2380, 124.2515],
        'Purok 6A' => [8.2390, 124.2495],
        'Purok 6B' => [8.2365, 124.2500],
        'Purok 6C' => [8.2355, 124.2480],
        'Purok 7' => [8.2350, 124.2530],
        'Purok 8' => [8.2325, 124.2565],
        'Purok 9' => [8.2500, 124.2475],
        'Phase 3 & 5 (Bayan)' => [8.2445, 124.2635],
    ];

    $updPurok = $db->prepare("UPDATE purok SET latitude = ?, longitude = ? WHERE purok_name = ?");
    foreach ($purokCoords as $pName => $coords) {
        $updPurok->execute([$coords[0], $coords[1], $pName]);
    }
    echo "  [OK] Updated Hinaplanon Purok coordinates for interactive map rendering.\n";

    $db->commit();
    echo "=== Migration v2 Completed Successfully ===\n";
} catch (\Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    die("Migration v2 Failed: " . $e->getMessage() . "\n");
}
