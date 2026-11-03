<?php
// tests/test_field_report_details.php

error_reporting(E_ALL & ~E_WARNING);
ini_set('display_errors', '0');

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Auth.php';
require_once __DIR__ . '/../src/Core/View.php';
require_once __DIR__ . '/../src/Core/Request.php';

require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Purok.php';
require_once __DIR__ . '/../src/Models/FieldReport.php';
require_once __DIR__ . '/../src/Models/DisasterRequest.php';
require_once __DIR__ . '/../src/Models/Notification.php';
require_once __DIR__ . '/../src/Models/AuditLog.php';

require_once __DIR__ . '/../src/Controllers/FieldReportController.php';

use App\Core\Auth;
use App\Models\FieldReport;
use App\Models\User;
use App\Models\Purok;
use App\Controllers\FieldReportController;

Auth::startSession();

$total = 0;
$passed = 0;

function assertTest(string $desc, bool $cond) {
    global $total, $passed;
    $total++;
    if ($cond) {
        $passed++;
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
    }
}

echo "====================================================================\n";
echo "   Field Report Details & Role Separation Verification Suite        \n";
echo "====================================================================\n\n";

// Ensure a test responder exists
$responder = User::findByEmail('responder1@hinaplanon.gov.ph');
if (!$responder) {
    die("Responder user not found.\n");
}

// 1. Create 3 test reports: Pending, Verified, and Rejected
$purok = Purok::all()[0];
$purokId = (int)$purok['purok_id'];
$purokName = $purok['purok_name'];

// Pending Report
$pendingReportId = FieldReport::create([
    'user_id' => (int)$responder['user_id'],
    'purok_id' => $purokId,
    'responder_name' => $responder['firstname'] . ' ' . $responder['lastname'],
    'status_update' => 'Water rising near riverbank, road partially submerged.',
    'road_accessibility' => 'Not Passable / Blocked',
    'casualties_count' => 2,
    'damaged_houses' => 8,
    'urgent_needs' => 'Rubber boats, 50 life vests, medical first aid kits',
    'latitude' => 8.2435,
    'longitude' => 124.2541,
    'status' => 'Submitted'
]);

// Verified Report
$verifiedReportId = FieldReport::create([
    'user_id' => (int)$responder['user_id'],
    'purok_id' => $purokId,
    'responder_name' => $responder['firstname'] . ' ' . $responder['lastname'],
    'status_update' => 'Sector cleared, debris removal ongoing.',
    'road_accessibility' => 'Passable',
    'casualties_count' => 0,
    'damaged_houses' => 3,
    'urgent_needs' => 'Relief food packs',
    'status' => 'Submitted'
]);
FieldReport::verify($verifiedReportId, 2, 'Verified by Barangay Official on-site inspection.');

// Rejected Report
$rejectedReportId = FieldReport::create([
    'user_id' => (int)$responder['user_id'],
    'purok_id' => $purokId,
    'responder_name' => $responder['firstname'] . ' ' . $responder['lastname'],
    'status_update' => 'Report with conflicting casualty figures.',
    'road_accessibility' => 'Passable',
    'casualties_count' => 15,
    'damaged_houses' => 30,
    'urgent_needs' => 'Heavy cranes',
    'status' => 'Submitted'
]);
FieldReport::reject($rejectedReportId, 2, 'Casualty count conflicts with medical triage log; please re-verify.');

echo "TEST SUITE 1: ICDRRMO Read-Only Monitoring View\n";
Auth::login('icdrrmo@iligan.gov.ph', 'icdrrmo123');
$_GET['id'] = $pendingReportId;

ob_start();
$controller = new FieldReportController();
$controller->show();
$icdrrmoOutput = ob_get_clean();

assertTest("ICDRRMO can open field report details successfully", strlen($icdrrmoOutput) > 500);
assertTest("Field report details display reporting responder name", strpos($icdrrmoOutput, htmlspecialchars($responder['firstname'] . ' ' . $responder['lastname'])) !== false);
assertTest("Field report details display responder badge or contact", strpos($icdrrmoOutput, 'REPORTING RESPONDER') !== false);
assertTest("Field report details display target purok name", strpos($icdrrmoOutput, htmlspecialchars($purokName)) !== false);
assertTest("Field report details display road accessibility status", strpos($icdrrmoOutput, 'Not Passable / Blocked') !== false);
assertTest("Field report details display damaged houses count", strpos($icdrrmoOutput, '8') !== false);
assertTest("Field report details display casualties count", strpos($icdrrmoOutput, '2') !== false);
assertTest("Field report details display urgent needs / required supplies", strpos($icdrrmoOutput, 'Rubber boats, 50 life vests') !== false);
assertTest("Field report details display narrative status update", strpos($icdrrmoOutput, 'Water rising near riverbank') !== false);
assertTest("Field report details display pending review status", strpos($icdrrmoOutput, 'Pending Review') !== false);
assertTest("ICDRRMO sees read-only monitoring advisory banner", strpos($icdrrmoOutput, 'ICDRRMO Citywide Read-Only Monitoring') !== false);
assertTest("ICDRRMO view DOES NOT contain Verify form", strpos($icdrrmoOutput, 'field-reports-verify') === false);
assertTest("ICDRRMO view DOES NOT contain Reject form", strpos($icdrrmoOutput, 'field-reports-reject') === false);

echo "\nTEST SUITE 2: Barangay Head Review & Action View\n";
Auth::login('brgyhead@hinaplanon.gov.ph', 'brgyhead123');
$_GET['id'] = $pendingReportId;

ob_start();
$controller = new FieldReportController();
$controller->show();
$bhOutput = ob_get_clean();

assertTest("Barangay Head can open field report details successfully", strlen($bhOutput) > 500);
assertTest("Barangay Head sees Verification & Review Console", strpos($bhOutput, 'Barangay Head Verification & Review Console') !== false);
assertTest("Barangay Head view contains Verify form", strpos($bhOutput, 'action="index.php?route=field-reports-verify"') !== false);
assertTest("Barangay Head view contains Reject form", strpos($bhOutput, 'action="index.php?route=field-reports-reject"') !== false);
assertTest("Barangay Head view DOES NOT display ICDRRMO read-only banner", strpos($bhOutput, 'ICDRRMO Citywide Read-Only Monitoring') === false);

echo "\nTEST SUITE 3: Verification & Rejection Information Rendering\n";
// Test Verified Report under ICDRRMO
Auth::login('icdrrmo@iligan.gov.ph', 'icdrrmo123');
$_GET['id'] = $verifiedReportId;
ob_start();
$controller->show();
$verifiedOutput = ob_get_clean();

assertTest("Verified report displays 'Field Report Officially Verified' badge/banner", strpos($verifiedOutput, 'Field Report Officially Verified') !== false);
assertTest("Verified report displays verification remarks", strpos($verifiedOutput, 'Verified by Barangay Official on-site inspection.') !== false);

// Test Rejected Report under ICDRRMO
$_GET['id'] = $rejectedReportId;
ob_start();
$controller->show();
$rejectedOutput = ob_get_clean();

assertTest("Rejected report displays 'Field Report Needs Revision / Rejected' badge/banner", strpos($rejectedOutput, 'Field Report Needs Revision / Rejected') !== false);
assertTest("Rejected report displays Rejection Reason / Justification", strpos($rejectedOutput, 'Casualty count conflicts with medical triage log; please re-verify.') !== false);

echo "\n====================================================================\n";
echo "SUMMARY: {$passed} / {$total} Tests Passed (" . round(($passed / $total) * 100) . "%)\n";
echo "====================================================================\n";

if ($passed !== $total) {
    exit(1);
}
