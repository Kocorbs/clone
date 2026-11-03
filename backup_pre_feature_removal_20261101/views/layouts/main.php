<!-- views/layouts/main.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Disaster Preparedness') ?> — Hinaplanon DRRM</title>
    <!-- Leaflet CSS for Interactive Hazard Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Application Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
    <!-- Modern Emergency Management Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="fa fa-shield-halved"></i>
            </div>
            <div>
                <div class="brand-title">Hinaplanon DRRM</div>
                <div class="brand-subtitle">Intelligent Resource Recommender</div>
            </div>
        </div>

        <ul class="sidebar-nav">
            <!-- DASHBOARD -->
            <li class="<?= ($_GET['route'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
                <a href="index.php?route=dashboard"><i class="fa fa-gauge-high"></i> Dashboard Overview</a>
            </li>

            <!-- EMERGENCY SOS (Visible to all, highlighted for residents) -->
            <?php if ($currentRole === 'Resident'): ?>
                <li class="<?= ($_GET['route'] ?? '') === 'emergency-sos' ? 'active' : '' ?>" style="margin: 8px 0;">
                    <a href="index.php?route=emergency-sos" class="sidebar-sos-btn">
                        <i class="fa fa-life-ring"></i> EMERGENCY SOS
                    </a>
                </li>
            <?php endif; ?>

            <!-- DISASTER MONITORING -->
            <div class="nav-section-title"><i class="fa fa-satellite-dish"></i> Disaster Monitoring</div>
            <li class="<?= ($_GET['route'] ?? '') === 'hazard-map' ? 'active' : '' ?>">
                <a href="index.php?route=hazard-map"><i class="fa fa-map-location-dot"></i> Live Hazard Map</a>
            </li>
            <li class="<?= ($_GET['route'] ?? '') === 'disaster-requests' ? 'active' : '' ?>">
                <a href="index.php?route=disaster-requests"><i class="fa fa-triangle-exclamation"></i> Disaster Incident Reports</a>
            </li>
            <li class="<?= ($_GET['route'] ?? '') === 'shelters' ? 'active' : '' ?>">
                <a href="index.php?route=shelters"><i class="fa fa-building-shield"></i> Evacuation Shelters</a>
            </li>

            <!-- MACHINE LEARNING (Super Admin & Admin) -->
            <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                <div class="nav-section-title"><i class="fa fa-brain"></i> Machine Learning</div>
                <li class="<?= ($_GET['route'] ?? '') === 'recommendations' ? 'active' : '' ?>">
                    <a href="index.php?route=recommendations"><i class="fa fa-network-wired"></i> AI Priority Recommendations</a>
                </li>
                <li class="<?= ($_GET['route'] ?? '') === 'ml-model' ? 'active' : '' ?>">
                    <a href="index.php?route=ml-model"><i class="fa fa-diagram-project"></i> Decision Tree Engine</a>
                </li>
                <li class="<?= ($_GET['route'] ?? '') === 'historical-records' ? 'active' : '' ?>">
                    <a href="index.php?route=historical-records"><i class="fa fa-database"></i> Historical Datasets</a>
                </li>
            <?php elseif ($currentRole === 'Responder'): ?>
                <div class="nav-section-title"><i class="fa fa-brain"></i> Decision Support</div>
                <li class="<?= ($_GET['route'] ?? '') === 'recommendations' ? 'active' : '' ?>">
                    <a href="index.php?route=recommendations"><i class="fa fa-network-wired"></i> Priority Allocations</a>
                </li>
            <?php endif; ?>

            <!-- RELIEF & INVENTORY MANAGEMENT -->
            <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head', 'Responder'])): ?>
                <div class="nav-section-title"><i class="fa fa-boxes-stacked"></i> Relief Management</div>
                <li class="<?= ($_GET['route'] ?? '') === 'inventory' ? 'active' : '' ?>">
                    <a href="index.php?route=inventory"><i class="fa fa-warehouse"></i> Supply Inventory</a>
                </li>
                <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                    <li class="<?= ($_GET['route'] ?? '') === 'reports' ? 'active' : '' ?>">
                        <a href="index.php?route=reports"><i class="fa fa-file-invoice"></i> Allocation Ledgers & Reports</a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- COMMUNITY & RESPONSE -->
            <div class="nav-section-title"><i class="fa fa-handshake-angle"></i> Community & Field</div>
            <li class="<?= in_array(($_GET['route'] ?? ''), ['preparedness', 'preparedness-calendar']) ? 'active' : '' ?>">
                <a href="index.php?route=preparedness"><i class="fa fa-calendar-check"></i> Preparedness Drills & Calendar</a>
            </li>
            <li class="<?= in_array(($_GET['route'] ?? ''), ['field-reports', 'field-reports-create']) ? 'active' : '' ?>">
                <a href="index.php?route=field-reports"><i class="fa fa-truck-medical"></i> Responder Field Status</a>
            </li>

            <!-- NOTIFICATIONS & COMMUNICATION -->
            <div class="nav-section-title"><i class="fa fa-bullhorn"></i> Communication</div>
            <li class="<?= ($_GET['route'] ?? '') === 'notifications' ? 'active' : '' ?>">
                <a href="index.php?route=notifications">
                    <i class="fa fa-bell"></i> Notifications & Alerts
                    <?php if (!empty($unreadNotificationCount) && $unreadNotificationCount > 0): ?>
                        <span class="badge badge-priority-urgent" style="margin-left: auto;"><?= $unreadNotificationCount ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- ADMINISTRATION -->
            <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                <div class="nav-section-title"><i class="fa fa-users-gear"></i> Administration</div>
                <?php if ($currentRole === 'ICDRRMO'): ?>
                    <li class="<?= ($_GET['route'] ?? '') === 'users' ? 'active' : '' ?>">
                        <a href="index.php?route=users"><i class="fa fa-user-gear"></i> System User Accounts</a>
                    </li>
                <?php endif; ?>
                <li class="<?= ($_GET['route'] ?? '') === 'pending-residents' ? 'active' : '' ?>">
                    <a href="index.php?route=pending-residents"><i class="fa fa-user-check"></i> Resident Approvals</a>
                </li>
                <li class="<?= ($_GET['route'] ?? '') === 'audit-logs' ? 'active' : '' ?>">
                    <a href="index.php?route=audit-logs"><i class="fa fa-scale-balanced"></i> System Audit Trail</a>
                </li>
            <?php endif; ?>

            <!-- RESEARCH & EVALUATION -->
            <div class="nav-section-title"><i class="fa fa-graduation-cap"></i> Research Evaluation</div>
            <li class="<?= in_array(($_GET['route'] ?? ''), ['evaluation-form', 'evaluation-results']) ? 'active' : '' ?>">
                <a href="index.php?route=evaluation-results"><i class="fa fa-chart-pie"></i> TAM & ISO 9126 Results</a>
            </li>
            <li class="<?= ($_GET['route'] ?? '') === 'profile' ? 'active' : '' ?>">
                <a href="index.php?route=profile"><i class="fa fa-id-card"></i> Profile Management</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content-wrapper">
        <header class="topbar">
            <div class="page-title-header">
                <?= htmlspecialchars($title ?? 'Barangay Hinaplanon DRRM') ?>
            </div>

            <div class="topbar-actions">
                <!-- Emergency SOS Quick Button for Residents in Topbar -->
                <?php if ($currentRole === 'Resident'): ?>
                    <a href="index.php?route=emergency-sos" class="btn btn-danger btn-sm" style="font-weight: 700;">
                        <i class="fa fa-life-ring"></i> SOS Request
                    </a>
                <?php endif; ?>

                <!-- Notification Bell with Unread Badge -->
                <a href="index.php?route=notifications" class="notif-bell-btn" title="View Notifications">
                    <i class="fa fa-bell"></i>
                    <span id="notifBadgeCount" class="notif-badge-count" style="<?= empty($unreadNotificationCount) ? 'display: none;' : '' ?>">
                        <?= (int)($unreadNotificationCount ?? 0) ?>
                    </span>
                </a>

                <!-- User Profile & Sign Out -->
                <div class="user-profile-badge">
                    <span class="role-pill role-<?= strtolower(str_replace(' ', '-', $currentRole)) ?>">
                        <?= htmlspecialchars($currentRole) ?>
                    </span>
                    <a href="index.php?route=profile" style="color: var(--text-primary); font-weight: 600; font-size: 0.88rem;">
                        <?= htmlspecialchars($currentUser['firstname'] ?? '') ?> <?= htmlspecialchars($currentUser['lastname'] ?? '') ?>
                    </a>
                    <a href="index.php?route=logout" class="btn btn-outline btn-sm" title="Sign Out">
                        <i class="fa fa-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </header>

        <main class="content-container">
            <!-- Flash Alert Notifications -->
            <?php if (!empty($flashes)): ?>
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($type) ?>">
                            <i class="fa fa-circle-info" style="margin-top: 2px;"></i>
                            <div><?= htmlspecialchars($msg) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<!-- Leaflet JS for Maps -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- Application Scripts -->
<script src="assets/js/app.js"></script>
</body>
</html>
