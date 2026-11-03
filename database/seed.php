<?php
// database/seed.php

require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

echo "=== Initializing Hinaplanon Disaster Recommender Database ===\n";

$db = Database::getInstance();
$driver = Database::getDriver();

echo "Database Driver: " . $driver . "\n";

if ($driver === 'sqlite') {
    $schemaFile = __DIR__ . '/schema_sqlite.sql';
    $sql = file_get_contents($schemaFile);
    $db->exec($sql);
    echo "SQLite Schema executed successfully.\n";
}

// 1. Seed Barangay
$stmt = $db->prepare("SELECT COUNT(*) FROM barangay WHERE barangay_name = 'Hinaplanon'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $stmt = $db->prepare("INSERT INTO barangay (barangay_name, location, latitude, longitude, population_size) 
        VALUES ('Hinaplanon', 'Iligan City, Lanao del Norte, Northern Mindanao', 8.2412, 124.2543, 15636)");
    $stmt->execute();
    echo "Seeded Barangay Hinaplanon (Pop: 15,636).\n";
}

$barangayId = $db->query("SELECT barangay_id FROM barangay WHERE barangay_name = 'Hinaplanon'")->fetchColumn();

// 2. Seed Puroks strictly extracted from the handwritten Hinaplanon Hazard Map
$puroks = [
    ['Bayug Island', 'High', 'Pink', 'Coastal river mouth island; devastating flood impact Sendong 2011.'],
    ['Purok 1', 'High', 'Pink', 'Adjacent to Mandulog River bank & Brgy Gym Evacuation Center.'],
    ['Purok 2', 'Medium', 'Orange', 'Central inland zone between river bank and bridge.'],
    ['Purok 2A', 'High', 'Pink', 'Direct Mandulog riverbank zone.'],
    ['Purok 2B', 'High', 'Pink', 'River bank sector north of bridge.'],
    ['Purok 2C', 'Medium', 'Orange', 'Adjacent to creek drainage.'],
    ['Purok 2D', 'Medium', 'Orange', 'East of Mandulog river toward Cabugason.'],
    ['Purok 3', 'Medium', 'Orange', 'Near concrete bridge and tributary creek.'],
    ['Purok 3A', 'High', 'Pink', 'Riverfront zone between 2A and 5A.'],
    ['Purok 4A', 'Medium', 'Orange', 'South of creek crossing National Highway.'],
    ['Purok 5 (Cabugason)', 'Medium', 'Orange', 'Eastern section bordering Brgy Luinab.'],
    ['Purok 5A', 'High', 'Pink', 'North-eastern riverside sector near P-9.'],
    ['Purok 6', 'Low', 'Yellow', 'Safe upland zone; South Evacuation Center location.'],
    ['Purok 6A', 'Medium', 'Orange', 'Western corridor along National Highway.'],
    ['Purok 6B', 'Low', 'Yellow', 'Inland low-hazard residential cluster.'],
    ['Purok 6C', 'Low', 'Yellow', 'Western border adjoining Brgy Bagong Silang.'],
    ['Purok 7', 'Low', 'Yellow', 'Southern safe zone adjoining designated evacuation field.'],
    ['Purok 8', 'Pending Verification', 'Gray', 'Borderline area adjacent to Brgy Del Carmen; multi-zone gradient.'],
    ['Purok 9', 'High', 'Pink', 'North-west river bend; frequent flash flood inundation.'],
    ['Phase 3 & 5 (Bayan)', 'Medium', 'Orange', 'Subdivision area adjacent to Cabugason.']
];

$stmtCheck = $db->prepare("SELECT COUNT(*) FROM purok WHERE purok_name = ?");
$stmtInsertPurok = $db->prepare("INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color, hazard_source_reference, notes) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($puroks as $p) {
    $stmtCheck->execute([$p[0]]);
    if ($stmtCheck->fetchColumn() == 0) {
        $stmtInsertPurok->execute([$barangayId, $p[0], $p[1], $p[2], 'Handwritten Hazard Map Hinaplanon CamScanner', $p[3]]);
    }
}
echo "Seeded " . count($puroks) . " Puroks from Hinaplanon Hazard Map.\n";

// 3. Seed Users
$users = [
    [
        'user_type' => 'ICDRRMO',
        'id_number' => 'ICDRRMO-2026-001',
        'image' => 'assets/images/avatar_icdrrmo.png',
        'lastname' => 'Mabuti',
        'firstname' => 'Marc Erfred',
        'gender' => 'Male',
        'cellphone_number' => '0917-123-4567',
        'email' => 'icdrrmo@iligan.gov.ph',
        'password' => 'icdrrmo123',
        'is_approved' => 1
    ],
    [
        'user_type' => 'Barangay Head',
        'id_number' => 'BH-HIN-001',
        'image' => 'assets/images/avatar_captain.png',
        'lastname' => 'Nadayag',
        'firstname' => 'Jerry',
        'gender' => 'Male',
        'cellphone_number' => '0918-234-5678',
        'email' => 'brgyhead@hinaplanon.gov.ph',
        'password' => 'brgyhead123',
        'is_approved' => 1
    ],
    [
        'user_type' => 'Responder',
        'id_number' => 'RESCUE-042',
        'image' => 'assets/images/avatar_responder.png',
        'lastname' => 'Nadayag',
        'firstname' => 'Angel Mae',
        'gender' => 'Female',
        'cellphone_number' => '0919-345-6789',
        'email' => 'responder1@hinaplanon.gov.ph',
        'password' => 'responder123',
        'is_approved' => 1
    ],
    [
        'user_type' => 'Resident',
        'id_number' => 'RES-2026-001',
        'image' => 'assets/images/avatar_resident.png',
        'lastname' => 'Dela Cruz',
        'firstname' => 'Juan',
        'gender' => 'Male',
        'cellphone_number' => '0920-456-7890',
        'email' => 'juan.delacruz@gmail.com',
        'password' => 'resident123',
        'is_approved' => 1
    ],
    [
        'user_type' => 'Resident',
        'id_number' => 'RES-2026-002',
        'image' => 'assets/images/avatar_resident.png',
        'lastname' => 'Santos',
        'firstname' => 'Maria',
        'gender' => 'Female',
        'cellphone_number' => '0921-567-8901',
        'email' => 'maria.santos@gmail.com',
        'password' => 'resident123',
        'is_approved' => 0 // Pending approval by Barangay Head
    ]
];

$stmtUserCheck = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmtUserInsert = $db->prepare("INSERT INTO users (user_type, id_number, image, lastname, firstname, gender, cellphone_number, email, password_hash, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($users as $u) {
    $stmtUserCheck->execute([$u['email']]);
    if ($stmtUserCheck->fetchColumn() == 0) {
        $hash = password_hash($u['password'], PASSWORD_BCRYPT);
        $stmtUserInsert->execute([
            $u['user_type'], $u['id_number'], $u['image'], $u['lastname'],
            $u['firstname'], $u['gender'], $u['cellphone_number'],
            $u['email'], $hash, $u['is_approved']
        ]);
    }
}
echo "Seeded " . count($users) . " System Users across all roles.\n";

// 4. Seed Supplies
$supplies = [
    ['Relief Goods', 'Standard Family Food Pack (Rice, Canned Goods, Coffee)', 1000, 1000, 0, 'In Stock', 'food packs'],
    ['Medical Supplies', 'Emergency First Aid & Trauma Kit', 300, 300, 0, 'In Stock', 'kits'],
    ['Rescue Equipment', 'Inflatable Rescue Boat with 40HP Outboard Motor', 10, 10, 0, 'In Stock', 'units'],
    ['Rescue Equipment', 'High-Buoyancy Life Vest & Water Safety Ring', 250, 250, 0, 'In Stock', 'pieces'],
    ['Clean Water', 'Potable Drinking Water (5-Gallon Purified Jug)', 800, 800, 0, 'In Stock', 'jugs'],
    ['Rescue Equipment', 'Waterproof Searchlight & Emergency Megaphone', 50, 50, 0, 'In Stock', 'units'],
    ['Hygiene Kits', 'Family Hygiene & Sanitation Pack', 600, 600, 0, 'In Stock', 'packs'],
    ['Shelter Kits', 'Emergency Tarpaulin & Family Sleeping Mat Pack', 400, 400, 0, 'In Stock', 'kits']
];

$stmtSupplyCheck = $db->prepare("SELECT COUNT(*) FROM supply WHERE supply_name = ?");
$stmtSupplyInsert = $db->prepare("INSERT INTO supply (barangay_id, supply_type, supply_name, supply_quantity, supply_remaining, total_release, supply_status, unit_of_measure) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($supplies as $s) {
    $stmtSupplyCheck->execute([$s[1]]);
    if ($stmtSupplyCheck->fetchColumn() == 0) {
        $stmtSupplyInsert->execute([$barangayId, $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6]]);
    }
}
echo "Seeded " . count($supplies) . " Inventory Supply Items.\n";

// 5. Seed Preparedness Activities
$bhUser = $db->query("SELECT user_id FROM users WHERE user_type = 'Barangay Head' LIMIT 1")->fetchColumn();
$purok1 = $db->query("SELECT purok_id FROM purok WHERE purok_name = 'Purok 1' LIMIT 1")->fetchColumn();
$purok6 = $db->query("SELECT purok_id FROM purok WHERE purok_name = 'Purok 6' LIMIT 1")->fetchColumn();

$activities = [
    [
        'activity_type' => 'Disaster Drill',
        'title' => 'Annual Community Flood Evacuation Drill 2026',
        'description' => 'Comprehensive daytime simulation of riverbank early warning and phased evacuation to Hinaplanon Barangay Gym.',
        'target_purok_id' => $purok1,
        'schedule_date' => date('Y-m-d 08:30:00', strtotime('+7 days')),
        'location' => 'Hinaplanon Barangay Gym, Purok 1',
        'status' => 'Scheduled',
        'max_participants' => 300
    ],
    [
        'activity_type' => 'Training & Seminar',
        'title' => 'First Aid & Basic Life Support Training for Purok Responders',
        'description' => 'Hands-on emergency wound care, cardiopulmonary resuscitation (CPR), and patient extrication training with City Rescuers.',
        'target_purok_id' => $purok6,
        'schedule_date' => date('Y-m-d 13:00:00', strtotime('+14 days')),
        'location' => 'South Evacuation Center, Purok 6',
        'status' => 'Scheduled',
        'max_participants' => 75
    ],
    [
        'activity_type' => 'Information Campaign',
        'title' => 'Shear Line & Typhoon Readiness Orientation',
        'description' => 'Information campaign highlighting flood hazard zones, emergency contacts, and 72-hour family survival kit checklists.',
        'target_purok_id' => null,
        'schedule_date' => date('Y-m-d 09:00:00', strtotime('-10 days')),
        'location' => 'Hinaplanon Covered Court',
        'status' => 'Completed',
        'max_participants' => 150
    ]
];

$stmtActCheck = $db->prepare("SELECT COUNT(*) FROM preparedness_activities WHERE title = ?");
$stmtActInsert = $db->prepare("INSERT INTO preparedness_activities (user_id, barangay_id, activity_type, title, description, target_purok_id, schedule_date, location, status, max_participants) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($activities as $act) {
    $stmtActCheck->execute([$act['title']]);
    if ($stmtActCheck->fetchColumn() == 0) {
        $stmtActInsert->execute([$bhUser, $barangayId, $act['activity_type'], $act['title'], $act['description'], $act['target_purok_id'], $act['schedule_date'], $act['location'], $act['status'], $act['max_participants']]);
    }
}
echo "Seeded " . count($activities) . " Preparedness Activities.\n";

// 6. Seed one completed activity evaluation
$completedActId = $db->query("SELECT preparedness_activities_id FROM preparedness_activities WHERE status = 'Completed' LIMIT 1")->fetchColumn();
if ($completedActId) {
    $stmtEvalCheck = $db->prepare("SELECT COUNT(*) FROM event_evaluations WHERE preparedness_activities_id = ?");
    $stmtEvalCheck->execute([$completedActId]);
    if ($stmtEvalCheck->fetchColumn() == 0) {
        $stmtEvalInsert = $db->prepare("INSERT INTO event_evaluations (preparedness_activities_id, user_id, actual_turnout, objectives_met_rating, problems_encountered, supplies_used, recommendations_for_improvement) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtEvalInsert->execute([
            $completedActId, $bhUser, 142, 5,
            'Microphone feedback issues during sound system setup; limited seating for elderly attendees.',
            '150 informational leaflets, 150 bottled waters, 1 megaphone.',
            'Set up separate priority seating for pregnant women and senior citizens in future sessions.'
        ]);
        echo "Seeded Event Evaluation for completed activity.\n";
    }
}

echo "=== Seeding Complete ===\n";
