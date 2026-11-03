<?php
// public/index.php
declare(strict_types=1);

// Error reporting for robust development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Router;
use App\Core\Auth;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\InventoryController;
use App\Controllers\DisasterRequestController;
use App\Controllers\RecommendationController;
use App\Controllers\PreparednessController;
use App\Controllers\FieldReportController;
use App\Controllers\HazardMapController;
use App\Controllers\MLController;
use App\Controllers\ReportController;
use App\Controllers\EvaluationController;
use App\Controllers\NotificationController;
use App\Controllers\ShelterController;
use App\Controllers\MapApiController;
use App\Controllers\SosController;
use App\Controllers\AuditController;

Auth::startSession();

$router = new Router();

// Authentication Routes
$router->get('login', [AuthController::class, 'showLogin']);
$router->post('login', [AuthController::class, 'login']);
$router->get('logout', [AuthController::class, 'logout']);
$router->get('register', [AuthController::class, 'showRegister']);
$router->post('register', [AuthController::class, 'register']);
$router->get('forgot-password', [AuthController::class, 'showForgotPassword']);
$router->get('profile', [AuthController::class, 'profile']);
$router->post('profile', [AuthController::class, 'profile']);

// Dashboard Routes
$router->get('dashboard', [DashboardController::class, 'index']);

// Notifications Center & Real-time Polling API
$router->get('notifications', [NotificationController::class, 'index']);
$router->get('notifications-poll', [NotificationController::class, 'pollApi']);
$router->post('notifications-read', [NotificationController::class, 'markRead']);
$router->post('notifications-mark-read', [NotificationController::class, 'markRead']);
$router->post('notifications-read-all', [NotificationController::class, 'markAllRead']);
$router->post('notifications-mark-all', [NotificationController::class, 'markAllRead']);

// Emergency SOS Request
$router->get('emergency-sos', [SosController::class, 'form']);
$router->post('emergency-sos', [SosController::class, 'submit']);

// Evacuation Shelter Monitoring
$router->get('shelters', [ShelterController::class, 'index']);
$router->post('shelters-create', [ShelterController::class, 'create']);
$router->post('shelters-update', [ShelterController::class, 'updateOccupancy']);

// Live Map JSON Data API
$router->get('api-map-data', [MapApiController::class, 'getMapData']);

// Module 1: User Management
$router->get('users', [UserController::class, 'index']);
$router->get('users-create', [UserController::class, 'create']);
$router->post('users-create', [UserController::class, 'create']);
$router->get('pending-residents', [UserController::class, 'pendingResidents']);
$router->post('approve-resident', [UserController::class, 'approveResident']);
$router->post('reject-resident', [UserController::class, 'rejectResident']);

// Module 2: Resource & Inventory Management
$router->get('inventory', [InventoryController::class, 'index']);
$router->get('inventory-create', [InventoryController::class, 'create']);
$router->post('inventory-create', [InventoryController::class, 'create']);
$router->post('inventory-replenish', [InventoryController::class, 'replenish']);
$router->get('inventory-export', [InventoryController::class, 'exportCsv']);

// Module 3: Disaster Request Form & Incident Workflow
$router->get('disaster-requests', [DisasterRequestController::class, 'index']);
$router->get('disaster-requests-create', [DisasterRequestController::class, 'create']);
$router->post('disaster-requests-create', [DisasterRequestController::class, 'create']);
$router->get('disaster-requests-show', [DisasterRequestController::class, 'show']);
$router->post('disaster-requests-recommend', [DisasterRequestController::class, 'generateRecommendation']);
$router->post('disaster-requests-verify', [DisasterRequestController::class, 'verify']);
$router->post('disaster-requests-assign', [DisasterRequestController::class, 'assign']);
$router->post('disaster-requests-status', [DisasterRequestController::class, 'updateStatus']);

// Module 4: Decision Tree Recommendations
$router->get('recommendations', [RecommendationController::class, 'index']);
$router->get('recommendations-show', [RecommendationController::class, 'show']);
$router->post('recommendations-review', [RecommendationController::class, 'review']);

// Module 5: Preparedness Activity Scheduling
$router->get('preparedness', [PreparednessController::class, 'index']);
$router->get('preparedness-calendar', [PreparednessController::class, 'calendar']);
$router->get('preparedness-create', [PreparednessController::class, 'create']);
$router->post('preparedness-create', [PreparednessController::class, 'create']);
$router->get('preparedness-evaluate', [PreparednessController::class, 'evaluate']);
$router->post('preparedness-evaluate', [PreparednessController::class, 'evaluate']);

// Module 6: Field Reporting
$router->get('field-reports', [FieldReportController::class, 'index']);
$router->get('field-reports-create', [FieldReportController::class, 'create']);
$router->post('field-reports-create', [FieldReportController::class, 'create']);
$router->get('field-reports-show', [FieldReportController::class, 'show']);

// Phase 3 & 8: Live Interactive Hazard Map
$router->get('hazard-map', [HazardMapController::class, 'index']);
$router->post('hazard-map-update', [HazardMapController::class, 'updateRisk']);

// Phase 4 & 5: ML Pipeline & Historical Records
$router->get('ml-model', [MLController::class, 'index']);
$router->post('ml-train', [MLController::class, 'train']);
$router->get('ml-download-template', [MLController::class, 'downloadTemplate']);
$router->get('historical-records', [MLController::class, 'historicalRecords']);
$router->post('historical-import', [MLController::class, 'importCsv']);

// Module 7: Reporting, Audit & Export
$router->get('reports', [ReportController::class, 'index']);
$router->get('reports-export-allocations', [ReportController::class, 'exportAllocationsCsv']);
$router->get('audit-logs', [AuditController::class, 'index']);

// Research Evaluation (TAM & ISO 9126)
$router->get('evaluation-form', [EvaluationController::class, 'form']);
$router->post('evaluation-submit', [EvaluationController::class, 'submit']);
$router->get('evaluation-results', [EvaluationController::class, 'results']);
$router->get('evaluation-export', [EvaluationController::class, 'exportCsv']);

// Dispatch route
$router->dispatch();
