<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

$cols = array_column($db->query('PRAGMA table_info(preparedness_activities)')->fetchAll(), 'name');

if (!in_array('assigned_personnel', $cols, true)) {
    $db->exec('ALTER TABLE preparedness_activities ADD COLUMN assigned_personnel TEXT DEFAULT NULL');
    echo "Added column 'assigned_personnel' to preparedness_activities.\n";
} else {
    echo "Column 'assigned_personnel' already exists.\n";
}

if (!in_array('volunteer_person', $cols, true)) {
    $db->exec('ALTER TABLE preparedness_activities ADD COLUMN volunteer_person TEXT DEFAULT NULL');
    echo "Added column 'volunteer_person' to preparedness_activities.\n";
} else {
    echo "Column 'volunteer_person' already exists.\n";
}

// Update existing seeded records
$stmt1 = $db->prepare("UPDATE preparedness_activities SET assigned_personnel = :p, volunteer_person = :v WHERE preparedness_activities_id = 1");
$stmt1->execute([
    'p' => 'Officer In-Charge: BDRRMC Lead Rescuer',
    'v' => 'Volunteer Lead: Red Cross Youth & Purok 1 Volunteer Volunteers'
]);

$stmt2 = $db->prepare("UPDATE preparedness_activities SET assigned_personnel = :p, volunteer_person = :v WHERE preparedness_activities_id = 2");
$stmt2->execute([
    'p' => 'Lead Trainer: ICDRRMO Paramedic Team',
    'v' => 'Volunteer Coordinator: Barangay Health Workers & Youth Volunteers'
]);

$stmt3 = $db->prepare("UPDATE preparedness_activities SET assigned_personnel = :p, volunteer_person = :v WHERE preparedness_activities_id = 3");
$stmt3->execute([
    'p' => 'Lead Facilitator: BDRRMC Operations Head',
    'v' => 'Volunteer Lead: Hinaplanon Youth Volunteers & Purok Leaders'
]);

echo "Populated assigned personnel and volunteers for existing preparedness activities.\n";

