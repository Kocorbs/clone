<?php
// tests/test_relief_hub.php

error_reporting(E_ALL & ~E_WARNING);
ini_set('display_errors', '0');

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Auth.php';
require_once __DIR__ . '/../src/Core/View.php';
require_once __DIR__ . '/../src/Core/Request.php';

require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Purok.php';
require_once __DIR__ . '/../src/Models/Supply.php';
require_once __DIR__ . '/../src/Models/PreparednessActivity.php';
require_once __DIR__ . '/../src/Models/Notification.php';
require_once __DIR__ . '/../src/Models/AuditLog.php';
require_once __DIR__ . '/../src/Models/Beneficiary.php';

require_once __DIR__ . '/../src/Controllers/NotificationController.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';

use App\Core\Auth;
use App\Models\User;
use App\Models\Beneficiary;
use App\Controllers\NotificationController;
use App\Controllers\UserController;

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
echo "   Relief Goods & Community Preparedness Hub Verification Suite     \n";
echo "====================================================================\n\n";

// 1. Resident Login & Dashboard View
Auth::login('juan.delacruz@gmail.com', 'resident123');
$user = Auth::user();

// Ensure clean initial state for Juan Dela Cruz
$testBenInit = Beneficiary::findByUserId((int)$user['user_id']);
if ($testBenInit) {
    Beneficiary::updateStatus((int)$testBenInit['beneficiary_id'], 'Eligible', 2, 'Restored to Eligible.');
}

ob_start();
$controller = new NotificationController();
$controller->index();
$hubOutput = ob_get_clean();

echo "TEST SUITE 1: Resident Hub Presentation & Required Sections\n";
assertTest("Resident can access Relief Goods & Community Preparedness Hub", strlen($hubOutput) > 2000);
assertTest("Section 1: Welcome banner displays logged-in resident's name", strpos($hubOutput, 'Mabuhay, Juan Dela Cruz') !== false);
assertTest("Section 1: Informs resident of relief program inclusion status", strpos($hubOutput, 'PROGRAM ENROLLMENT STATUS') !== false);
assertTest("Section 1: Personalized eligibility & claiming guidance message", 
    strpos($hubOutput, 'Officially Included in Active Relief Program') !== false || 
    strpos($hubOutput, 'Relief Package Successfully Claimed') !== false || 
    strpos($hubOutput, 'Enrollment Verification') !== false
);

assertTest("Section 2: Beneficiary list table is present", strpos($hubOutput, 'Relief Distribution Beneficiary List') !== false);
assertTest("Section 2: Logged-in resident's entry is highlighted with 'YOU'", strpos($hubOutput, 'class="highlight-row"') !== false && strpos($hubOutput, '>YOU<') !== false);
assertTest("Section 2: Other beneficiaries are anonymized for privacy", strpos($hubOutput, 'fa fa-user-shield') !== false && preg_match('/[A-Z]\*+\s+[A-Z]\*+/', $hubOutput) === 1);
assertTest("Section 2: Filter tabs for Eligible, Pending, Claimed present", strpos($hubOutput, 'beneficiary_status=Eligible') !== false && strpos($hubOutput, 'beneficiary_status=Pending') !== false);

assertTest("Section 3: My Ayuda Priority & Claiming Status card is rendered", strpos($hubOutput, 'My Ayuda Priority & Claiming Status') !== false);
assertTest("Section 3: Shows priority level badge", strpos($hubOutput, 'Priority') !== false);
assertTest("Section 3: Shows claiming authorization status", strpos($hubOutput, 'Claiming Authorization') !== false);
assertTest("Section 3: Displays allocated assistance type & quantity", strpos($hubOutput, 'Allocated Assistance') !== false && strpos($hubOutput, 'Standard Family Food Pack') !== false);
assertTest("Section 3: Displays official claiming schedule & pickup station", strpos($hubOutput, 'Official Claiming Schedule & Pickup Station') !== false && strpos($hubOutput, 'Hinaplanon Covered Court') !== false);
assertTest("Section 3: Explains prioritization based on ML / hazard mapping", strpos($hubOutput, 'Why was your household prioritized?') !== false && strpos($hubOutput, 'CamScanner Hazard Map & CART Decision Tree') !== false);

assertTest("Section 4: Relief Goods Inventory & Distribution card is rendered", strpos($hubOutput, 'Relief Goods Inventory & Centralized Distribution') !== false);
assertTest("Section 4: Displays real inventory items (Food Packs, Clean Water, etc.)", strpos($hubOutput, 'Standard Family Food Pack') !== false && strpos($hubOutput, 'Stock Remaining:') !== false);
assertTest("Section 4: Assistance guidance & emergency desk contact shown", strpos($hubOutput, 'BDRRMC Desk') !== false);

assertTest("Section 5: Community General Alerts section rendered", strpos($hubOutput, 'Community General Alerts') !== false);
assertTest("Section 6: Important Reminders section rendered with protocols", strpos($hubOutput, 'Important Claiming Reminders') !== false && strpos($hubOutput, 'Valid Identification Requirement') !== false);
assertTest("Section 7: Community Preparedness Announcements rendered", strpos($hubOutput, 'Community Preparedness Announcements') !== false && strpos($hubOutput, 'Emergency Hotlines') !== false);

echo "\nTEST SUITE 2: Anonymization & Data Privacy Security\n";
$anonName = Beneficiary::anonymizeName('Maria', 'Santos');
assertTest("Beneficiary::anonymizeName masks first and last name correctly", $anonName === 'M**** S*****');
$anonShort = Beneficiary::anonymizeName('Al', 'Co');
assertTest("Short names masked safely", strpos($anonShort, '*') !== false);

echo "\nTEST SUITE 3: Barangay Head & ICDRRMO Beneficiary Management\n";
// Barangay Head Login
Auth::login('brgyhead@hinaplanon.gov.ph', 'brgyhead123');

// Test updating beneficiary status
$testBen = Beneficiary::findByUserId((int)$user['user_id']);
$benId = (int)$testBen['beneficiary_id'];

$statusOk = Beneficiary::updateStatus($benId, 'Claimed', 2, 'Claimed at Station B with Barangay ID verification.');
assertTest("Barangay Head can update beneficiary assistance status to 'Claimed'", $statusOk === true);

$updatedBen = Beneficiary::findById($benId);
assertTest("Beneficiary assistance status saved as 'Claimed'", $updatedBen['assistance_status'] === 'Claimed');
assertTest("Beneficiary claim timestamp recorded", !empty($updatedBen['claimed_at']));

// Test schedule update
$newDate = date('Y-m-d', strtotime('+5 days'));
$schedOk = Beneficiary::updateSchedule($benId, $newDate, '01:00 PM - 05:00 PM', 'Hinaplanon North Distribution Post', 2);
assertTest("Barangay Head can update claiming schedule", $schedOk === true);

$rescheduledBen = Beneficiary::findById($benId);
assertTest("Barangay Head can update claiming schedule date", $rescheduledBen['claiming_date'] === $newDate);
assertTest("Barangay Head can update claiming location", $rescheduledBen['claiming_location'] === 'Hinaplanon North Distribution Post');

// Restore to Eligible for clean state
Beneficiary::updateStatus($benId, 'Eligible', 2, 'Restored to Eligible for resident testing.');
Beneficiary::updateSchedule($benId, date('Y-m-d', strtotime('+3 days')), '08:00 AM - 12:00 PM', 'Hinaplanon Covered Court, Station B (Purok 1)', 2);

echo "\nTEST SUITE 4: Residents' Claim Actions & Multi-Stage Lifecycle Tracking\n";
// Re-login as Resident Juan Dela Cruz
Auth::login('juan.delacruz@gmail.com', 'resident123');

// Phase 1: Resident in 'Eligible' status
ob_start();
$controller = new NotificationController();
$controller->index();
$eligibleView = ob_get_clean();

assertTest("Claim Ayuda action button is displayed when resident is Eligible", strpos($eligibleView, 'Claim Ayuda') !== false && strpos($eligibleView, 'openClaimAyudaModal()') !== false);
assertTest("Ayuda Claim Lifecycle Tracker is rendered", strpos($eligibleView, 'Ayuda Claim Lifecycle Tracker') !== false);
assertTest("Ayuda Claim Tracker includes all 4 stages", 
    strpos($eligibleView, '1. Claim Submitted') !== false &&
    strpos($eligibleView, '2. Barangay Approved') !== false &&
    strpos($eligibleView, '3. Ready to Claim') !== false &&
    strpos($eligibleView, '4. Ayuda Claimed') !== false
);
assertTest("Confirmation modal #claimAyudaModal exists in DOM", strpos($eligibleView, 'id="claimAyudaModal"') !== false);
assertTest("Confirmation modal posts to 'route=residents-claim-ayuda'", strpos($eligibleView, 'index.php?route=residents-claim-ayuda') !== false);
assertTest("Confirmation modal includes anti-duplicate single-claim notice", strpos($eligibleView, 'Strict Single-Claim Rule') !== false);
assertTest("Confirmation modal requires confirmation checkbox", strpos($eligibleView, 'id="confirmClaimCheckbox"') !== false);

// Phase 2: Resident submits claim (Transitions to 'Pending')
$claimOk = Beneficiary::requestClaim($benId, (int)$user['user_id']);
assertTest("Resident can successfully submit claim request (Eligible -> Pending)", $claimOk === true);

$pendingBen = Beneficiary::findById($benId);
assertTest("Beneficiary status updated to 'Pending'", $pendingBen['assistance_status'] === 'Pending');
assertTest("Beneficiary claim_requested_at timestamp is populated", !empty($pendingBen['claim_requested_at']));

// Phase 3: Duplicate claim prevention
$dupClaim = Beneficiary::requestClaim($benId, (int)$user['user_id']);
assertTest("Duplicate claim is strictly prevented (requestClaim returns false when Pending)", $dupClaim === false);

// Render Pending View
ob_start();
$controller->index();
$pendingView = ob_get_clean();
assertTest("Pending view shows 'Claim Request Under Review' callout", strpos($pendingView, 'Claim Request Under Review') !== false);
assertTest("Pending view hides the 'Claim Ayuda' action button to prevent duplicates", strpos($pendingView, 'onclick="openClaimAyudaModal()"') === false);

// Phase 4: Barangay Head / ICDRRMO verifies and approves claim
Auth::login('brgyhead@hinaplanon.gov.ph', 'brgyhead123');
$approveOk = Beneficiary::updateStatus($benId, 'Approved', 2, 'Verified resident details against Barangay Registry.');
assertTest("Barangay Head can approve resident's claim request", $approveOk === true);

$approvedBen = Beneficiary::findById($benId);
assertTest("Beneficiary status transitioned to 'Approved'", $approvedBen['assistance_status'] === 'Approved');
assertTest("Beneficiary is_approved flag is set to 1", (int)$approvedBen['is_approved'] === 1);
assertTest("Beneficiary verified_at timestamp is populated", !empty($approvedBen['verified_at']));

// Render Approved View for Resident
Auth::login('juan.delacruz@gmail.com', 'resident123');
ob_start();
$controller->index();
$approvedView = ob_get_clean();
assertTest("Approved view displays 'Claim Request Officially Approved'", strpos($approvedView, 'Claim Request Officially Approved') !== false);

// Phase 5: Relief goods staged at pickup station ('Ready to Claim')
Auth::login('brgyhead@hinaplanon.gov.ph', 'brgyhead123');
$readyOk = Beneficiary::updateStatus($benId, 'Ready to Claim', 2, 'Relief goods packed and staged at Station B.');
assertTest("Status transitions to 'Ready to Claim' when goods staged", $readyOk === true);

$readyBen = Beneficiary::findById($benId);
assertTest("Beneficiary status in database is 'Ready to Claim'", $readyBen['assistance_status'] === 'Ready to Claim');

// Render Ready to Claim View for Resident
Auth::login('juan.delacruz@gmail.com', 'resident123');
ob_start();
$controller->index();
$readyView = ob_get_clean();
assertTest("Ready to Claim view displays pickup readiness notice", strpos($readyView, 'Your Relief Package is Ready for Pickup!') !== false);

// Phase 6: Authorized personnel confirms distribution
Auth::login('brgyhead@hinaplanon.gov.ph', 'brgyhead123');
$distOk = Beneficiary::confirmDistribution($benId, 2, 'Released to Juan Dela Cruz with valid Barangay ID.');
assertTest("Authorized personnel confirms distribution (Ready to Claim -> Claimed)", $distOk === true);

$claimedBen = Beneficiary::findById($benId);
assertTest("Beneficiary status updated to 'Claimed'", $claimedBen['assistance_status'] === 'Claimed');
assertTest("Beneficiary claimed_at timestamp is populated", !empty($claimedBen['claimed_at']));
assertTest("Beneficiary distributed_by ID is recorded", (int)$claimedBen['distributed_by'] === 2);

// Render Claimed View for Resident
Auth::login('juan.delacruz@gmail.com', 'resident123');
ob_start();
$controller->index();
$claimedView = ob_get_clean();
assertTest("Claimed view shows 'Relief Goods Successfully Claimed & Released'", strpos($claimedView, 'Relief Goods Successfully Claimed') !== false);

// Clean up: Restore test fixture state for resident Juan Dela Cruz
Beneficiary::updateStatus($benId, 'Eligible', 2, 'Restored to Eligible after full test run.');
Beneficiary::updateSchedule($benId, date('Y-m-d', strtotime('+3 days')), '08:00 AM - 12:00 PM', 'Hinaplanon Covered Court, Station B (Purok 1)', 2);

echo "\n====================================================================\n";
echo "SUMMARY: {$passed} / {$total} Tests Passed (" . round(($passed / $total) * 100) . "%)\n";
echo "====================================================================\n";

if ($passed !== $total) {
    exit(1);
}
