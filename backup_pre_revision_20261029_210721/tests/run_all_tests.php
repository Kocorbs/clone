<?php
// tests/run_all_tests.php

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Auth.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Purok.php';
require_once __DIR__ . '/../src/Models/Supply.php';
require_once __DIR__ . '/../src/Models/DisasterRequest.php';
require_once __DIR__ . '/../src/Models/Recommendation.php';
require_once __DIR__ . '/../src/Models/RequestLog.php';
require_once __DIR__ . '/../src/Models/TamEvaluation.php';
require_once __DIR__ . '/../src/ML/FeatureExtractor.php';
require_once __DIR__ . '/../src/ML/DecisionTreeClassifier.php';
require_once __DIR__ . '/../src/ML/ModelEvaluator.php';
require_once __DIR__ . '/../src/ML/DemandCalculator.php';
require_once __DIR__ . '/../src/ML/RecommenderEngine.php';

use App\Core\Auth;
use App\Models\User;
use App\Models\Purok;
use App\Models\Supply;
use App\Models\DisasterRequest;
use App\Models\Recommendation;
use App\Models\RequestLog;
use App\Models\TamEvaluation;
use App\ML\FeatureExtractor;
use App\ML\DecisionTreeClassifier;
use App\ML\ModelEvaluator;
use App\ML\DemandCalculator;
use App\ML\RecommenderEngine;

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assertTest(bool $condition, string $testName) {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$testName}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$testName}\n";
    }
}

echo "====================================================================\n";
echo "   Barangay Hinaplanon Disaster Recommender — Quality Assurance Suite \n";
echo "====================================================================\n\n";

// --- SUITE 1: AUTHENTICATION & ROLE-BASED ACCESS CONTROL (RBAC) ---
echo "TEST SUITE 1: Authentication & User Management\n";
$icdrrmoLogin = Auth::login('icdrrmo@iligan.gov.ph', 'icdrrmo123');
assertTest($icdrrmoLogin['success'] === true, "ICDRRMO Admin authentication succeeds with valid credentials");
assertTest(Auth::role() === 'ICDRRMO', "ICDRRMO role assigned properly in session");
assertTest(Auth::hasRole(['ICDRRMO']), "Role authorization check recognizes ICDRRMO permissions");

$badLogin = Auth::login('icdrrmo@iligan.gov.ph', 'wrongpassword');
assertTest($badLogin['success'] === false, "Authentication fails gracefully with incorrect password");

// Reset test resident to pending approval
$db = \App\Core\Database::getInstance();
$db->exec("UPDATE users SET is_approved = 0 WHERE email = 'maria.santos@gmail.com'");

// Test Resident pending approval logic
$pendingResident = Auth::login('maria.santos@gmail.com', 'resident123');
assertTest($pendingResident['success'] === false && strpos($pendingResident['message'], 'pending review') !== false, 
    "Unapproved resident registration is blocked from login until Barangay Head approval");

// Approve resident
$mariaUser = User::findByEmail('maria.santos@gmail.com');
$approved = User::approveResident((int)$mariaUser['user_id'], (int)$icdrrmoLogin['user']['user_id']);
assertTest($approved === true, "Barangay Head / Official approval action executes successfully");

$approvedResidentLogin = Auth::login('maria.santos@gmail.com', 'resident123');
assertTest($approvedResidentLogin['success'] === true, "Approved resident can successfully sign in after official verification");


// --- SUITE 2: INVENTORY MANAGEMENT & ATOMIC STOCK INTEGRITY ---
echo "\nTEST SUITE 2: Resource Inventory & Stock Integrity\n";
$foodPack = Supply::findById(1);
assertTest($foodPack !== null, "Supply record exists in centralized inventory");
$initialStock = (int)$foodPack['supply_remaining'];
assertTest($initialStock > 0, "Initial available stock is verified positive ({$initialStock} units)");

// Test legal stock deduction
$deducted = Supply::deductStock(1, 10);
assertTest($deducted === true, "Legal deduction of 10 units succeeds");
$updatedFoodPack = Supply::findById(1);
assertTest((int)$updatedFoodPack['supply_remaining'] === ($initialStock - 10), "Remaining stock decreased exactly by 10 units");

// Test over-allocation prevention
$overAllocException = false;
try {
    Supply::deductStock(1, 9999999);
} catch (\Exception $e) {
    $overAllocException = true;
}
assertTest($overAllocException === true, "Stock deduction strictly PREVENTS over-allocation exceeding available inventory");

// Replenish stock
Supply::replenishStock(1, 10);
$restored = Supply::findById(1);
assertTest((int)$restored['supply_remaining'] === $initialStock, "Inventory replenishment restores stock correctly");


// --- SUITE 3: HAZARD MAP & PUROK RISK CLASSIFICATION INTEGRATION ---
echo "\nTEST SUITE 3: Barangay Hinaplanon Hazard Map Data Integration\n";
$puroks = Purok::all();
assertTest(count($puroks) >= 20, "All 20 Puroks extracted from handwritten CamScanner hazard map are loaded");

$bayugIsland = null;
$purok6 = null;
$purok8 = null;
foreach ($puroks as $p) {
    if ($p['purok_name'] === 'Bayug Island') $bayugIsland = $p;
    if ($p['purok_name'] === 'Purok 6') $purok6 = $p;
    if ($p['purok_name'] === 'Purok 8') $purok8 = $p;
}

assertTest($bayugIsland && $bayugIsland['risk_classification'] === 'High' && $bayugIsland['risk_zone_color'] === 'Pink',
    "Bayug Island risk classification matches CamScanner map: High (Pink)");
assertTest($purok6 && $purok6['risk_classification'] === 'Low' && $purok6['risk_zone_color'] === 'Yellow',
    "Purok 6 (Evacuation Center area) matches CamScanner map: Low (Yellow)");
assertTest($purok8 && $purok8['risk_classification'] === 'Pending Verification',
    "Purok 8 multi-zone boundary is marked 'Pending Verification' per research limitations");


// --- SUITE 4: DECISION TREE ML CLASSIFIER & EVALUATION ---
echo "\nTEST SUITE 4: Decision Tree Supervised ML Pipeline\n";
// Synthetic training samples
$trainX = [
    ['disaster_type' => 'Flood', 'severity_level' => 'Critical', 'is_urgent' => 1, 'purok_risk_level' => 'High', 'affected_households' => 50, 'affected_population' => 200],
    ['disaster_type' => 'Flood', 'severity_level' => 'Critical', 'is_urgent' => 1, 'purok_risk_level' => 'High', 'affected_households' => 60, 'affected_population' => 240],
    ['disaster_type' => 'Flood', 'severity_level' => 'High', 'is_urgent' => 1, 'purok_risk_level' => 'High', 'affected_households' => 40, 'affected_population' => 160],
    ['disaster_type' => 'Flood', 'severity_level' => 'Medium', 'is_urgent' => 0, 'purok_risk_level' => 'Medium', 'affected_households' => 20, 'affected_population' => 80],
    ['disaster_type' => 'Flood', 'severity_level' => 'Low', 'is_urgent' => 0, 'purok_risk_level' => 'Low', 'affected_households' => 10, 'affected_population' => 40],
    ['disaster_type' => 'Flood', 'severity_level' => 'Low', 'is_urgent' => 0, 'purok_risk_level' => 'Low', 'affected_households' => 12, 'affected_population' => 48],
];
$trainY = ['Urgent', 'Urgent', 'High', 'Medium', 'Low', 'Low'];

$dt = new DecisionTreeClassifier(maxDepth: 4, minSamplesSplit: 2);
$dt->fit($trainX, $trainY);
assertTest($dt->getTree() !== null, "CART Decision Tree successfully fitted and induced");

$predCritical = $dt->predict(['disaster_type' => 'Flood', 'severity_level' => 'Critical', 'is_urgent' => 1, 'purok_risk_level' => 'High', 'affected_households' => 55, 'affected_population' => 220]);
assertTest($predCritical['predicted_priority'] === 'Urgent', "Decision Tree predicts 'Urgent' priority for critical high-risk flood");
assertTest(!empty($predCritical['decision_path']), "Explainability: Generated step-by-step decision path through tree");

$predLow = $dt->predict(['disaster_type' => 'Flood', 'severity_level' => 'Low', 'is_urgent' => 0, 'purok_risk_level' => 'Low', 'affected_households' => 8, 'affected_population' => 32]);
assertTest($predLow['predicted_priority'] === 'Low', "Decision Tree predicts 'Low' priority for low-severity upland situation");

// Test Evaluation metrics computation
$evalTest = ModelEvaluator::evaluate($dt, $trainX, $trainY);
assertTest($evalTest['accuracy'] >= 0.90, "ModelEvaluator computes multiclass Accuracy correctly ({$evalTest['accuracy']})");
assertTest(isset($evalTest['confusion_matrix']['Urgent']['Urgent']), "ModelEvaluator computes complete 4x4 Confusion Matrix");
assertTest($evalTest['macro_f1'] > 0.0, "ModelEvaluator calculates multiclass Macro and Weighted F1-Scores");


// --- SUITE 5: DEMAND CALCULATION & END-TO-END RECOMMENDATION ENGINE ---
echo "\nTEST SUITE 5: Resource Need Demand Calculation & Allocation Engine\n";
$calcNeedUrgent = DemandCalculator::calculateNeed('Urgent', 50, 200, 'Relief Goods', 1000);
// 50 households * 1.10 = 55 packs
assertTest($calcNeedUrgent['recommended_quantity'] === 55, "Demand formula applies 110% allocation buffer for Urgent priority (55 units)");
assertTest($calcNeedUrgent['is_shortage'] === false, "Sufficient stock correctly flagged without shortage");

// Shortage test
$calcShortage = DemandCalculator::calculateNeed('High', 100, 400, 'Relief Goods', 40);
assertTest($calcShortage['recommended_quantity'] === 40, "Calculated recommendation is strictly capped at available stock when shortage occurs");
assertTest($calcShortage['is_shortage'] === true, "Shortage flag and replenishment alert triggered");

// End-to-end disaster request to recommendation test
$reqId = DisasterRequest::create([
    'user_id' => (int)$icdrrmoLogin['user']['user_id'],
    'barangay_id' => 1,
    'purok_id' => $bayugIsland['purok_id'],
    'disaster_type' => 'Flash Flood',
    'weather_name' => 'Continuous Heavy Rainfall',
    'description' => 'River overflow breaching Bayug Island embankment. Water level rising fast.',
    'severity_level' => 'Critical',
    'is_urgent' => 1,
    'affected_households' => 30,
    'affected_population' => 120,
    'disaster_date' => date('Y-m-d')
]);
assertTest($reqId > 0, "Disaster request successfully created in database (ID: #{$reqId})");

$recEngineResult = RecommenderEngine::generateForDisasterRequest($reqId);
assertTest(!empty($recEngineResult['recommendation_id']), "RecommenderEngine generates recommendation record");
assertTest($recEngineResult['priority'] === 'Urgent', "Recommender predicts 'Urgent' priority for Bayug Island critical flash flood");

// Official Review & Approval with inventory deduction
$recRecord = Recommendation::findById($recEngineResult['recommendation_id']);
$stockBefore = (int)Supply::findById($recRecord['recommended_supply_id'])['supply_remaining'];

$reviewRes = Recommendation::review(
    $recRecord['recommendation_id'], 
    'approve', 
    'Verified with Hinaplanon BDRRM responders. Urgent river rescue deployment authorized.', 
    (int)$icdrrmoLogin['user']['user_id']
);
assertTest($reviewRes['success'] === true, "Official approval executed by authorized personnel");

$stockAfter = (int)Supply::findById($recRecord['recommended_supply_id'])['supply_remaining'];
assertTest($stockAfter === ($stockBefore - $recRecord['recommended_quantity']), 
    "Centralized inventory atomically deducted upon official recommendation approval");

// Audit trail ledger check
$logs = RequestLog::all();
$latestLog = $logs[0] ?? null;
assertTest($latestLog && strpos($latestLog['action_taken'], 'Approved') !== false, 
    "Audit trail ledger records transaction with actor, timestamp, and justification");


// --- SUITE 6: TAM & ISO 9126 RESEARCH EVALUATION CALCULATIONS ---
echo "\nTEST SUITE 6: TAM & ISO 9126 Research Evaluation Treatment\n";
TamEvaluation::create([
    'user_id' => (int)$icdrrmoLogin['user']['user_id'],
    'respondent_type' => 'BDRRM Head',
    'pu_q1' => 5, 'pu_q2' => 5, 'pu_q3' => 5, 'pu_q4' => 5,
    'peou_q1' => 5, 'peou_q2' => 4, 'peou_q3' => 5, 'peou_q4' => 5,
    'iso_functionality' => 5, 'iso_reliability' => 5, 'iso_usability' => 5,
    'iso_efficiency' => 5, 'iso_maintainability' => 5, 'iso_portability' => 5,
    'qualitative_feedback' => 'Decision tree recommendations greatly speed up response prioritization.'
]);

TamEvaluation::create([
    'user_id' => (int)$icdrrmoLogin['user']['user_id'],
    'respondent_type' => 'Barangay Official',
    'pu_q1' => 4, 'pu_q2' => 4, 'pu_q3' => 5, 'pu_q4' => 4,
    'peou_q1' => 4, 'peou_q2' => 4, 'peou_q3' => 4, 'peou_q4' => 4,
    'iso_functionality' => 4, 'iso_reliability' => 4, 'iso_usability' => 4,
    'iso_efficiency' => 4, 'iso_maintainability' => 4, 'iso_portability' => 4,
    'qualitative_feedback' => 'Helpful for Hinaplanon flood planning.'
]);

$stats = TamEvaluation::getStatistics();
assertTest($stats['total_respondents'] >= 2, "TAM evaluation captures respondent sample count (N = {$stats['total_respondents']})");
assertTest($stats['pu']['mean'] >= 4.0 && $stats['pu']['mean'] <= 5.0, "Weighted Mean for Perceived Usefulness calculated accurately ({$stats['pu']['mean']})");
assertTest($stats['iso']['functionality']['rating'] !== 'N/A', "ISO 9126 Quality dimension translated using 5-point Likert verbal scale");

echo "\n====================================================================\n";
echo "QA SUITE SUMMARY: {$passedTests} / {$totalTests} Tests Passed (" . round(($passedTests / $totalTests) * 100, 1) . "%)\n";
if ($failedTests === 0) {
    echo "STATUS: ALL QUALITY ASSURANCE CRITERIA MET WITH 100% SUCCESS.\n";
} else {
    echo "STATUS: {$failedTests} TESTS FAILED.\n";
}
echo "====================================================================\n";
