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
    <aside class="sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-logo">
                <img src="assets/images/hinaplanon_seal.png" alt="Barangay Hinaplanon Official Seal" class="brand-logo-img">
            </div>
            <div class="brand-title-wrap">
                <div class="brand-title-main">
                    <div class="brand-line"><span class="brand-initial">H</span>INAPLANON</div>
                    <div class="brand-line"><span class="brand-initial">D</span>RRM</div>
                    <div class="brand-line"><span class="brand-initial">S</span>YSTEM</div>
                </div>
                <div class="brand-subtitle">Resource Allocation Recommender</div>
            </div>
        </div>

        <ul class="sidebar-nav">
            <?php if ($currentRole === 'Resident'): ?>
                <!-- ======================================================== -->
                <!-- 1. RESIDENT NAVIGATION                                   -->
                <!-- ======================================================== -->
                <li class="<?= ($_GET['route'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
                    <a href="index.php?route=dashboard"><i class="fa-solid fa-house"></i> Dashboard Overview</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-clock-rotate-left"></i> Community Preparedness</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['preparedness', 'preparedness-calendar']) ? 'active' : '' ?>">
                    <a href="index.php?route=preparedness-calendar"><i class="fa fa-clock-rotate-left"></i> Event History</a>
                </li>


                <div class="nav-section-title"><i class="fa fa-hand-holding-heart"></i> Citizen Welfare & Relief</div>
                <li class="<?= ($_GET['route'] ?? '') === 'notifications' ? 'active' : '' ?>">
                    <a href="index.php?route=notifications">
                        <i class="fa fa-hand-holding-heart"></i> Relief Goods & Hub (Notifications & Alerts)
                        <?php if (!empty($unreadNotificationCount) && $unreadNotificationCount > 0): ?>
                            <span class="badge badge-priority-urgent" style="margin-left: auto;"><?= $unreadNotificationCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <div class="nav-section-title"><i class="fa fa-user-shield"></i> Account</div>
                <li class="<?= ($_GET['route'] ?? '') === 'profile' ? 'active' : '' ?>">
                    <a href="index.php?route=profile"><i class="fa fa-id-card"></i> Profile Management</a>
                </li>

            <?php elseif ($currentRole === 'Responder'): ?>
                <!-- ======================================================== -->
                <!-- 2. RESPONDER NAVIGATION                                  -->
                <!-- ======================================================== -->
                <li class="<?= ($_GET['route'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
                    <a href="index.php?route=dashboard"><i class="fa-solid fa-house"></i> Dashboard Overview</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-clock-rotate-left"></i> Field Records</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['preparedness', 'preparedness-calendar', 'preparedness-evaluate']) ? 'active' : '' ?>">
                    <a href="index.php?route=preparedness-calendar"><i class="fa fa-clock-rotate-left"></i> Event History</a>
                </li>


                <div class="nav-section-title"><i class="fa fa-brain"></i> Decision Support</div>
                <li class="<?= ($_GET['route'] ?? '') === 'recommendations' ? 'active' : '' ?>">
                    <a href="index.php?route=recommendations"><i class="fa fa-network-wired"></i> Priority Allocations</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-boxes-stacked"></i> Resource Management</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['reports', 'inventory', 'resource-management']) ? 'active' : '' ?>">
                    <a href="index.php?route=reports"><i class="fa fa-boxes-stacked"></i> Resource Management</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-handshake-angle"></i> Field Operations</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['field-reports', 'field-reports-create', 'field-reports-show']) ? 'active' : '' ?>">
                    <a href="index.php?route=field-reports"><i class="fa fa-truck-medical"></i> Responder Field Status</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-user-shield"></i> Account</div>
                <li class="<?= ($_GET['route'] ?? '') === 'profile' ? 'active' : '' ?>">
                    <a href="index.php?route=profile"><i class="fa fa-id-card"></i> Profile Management</a>
                </li>

            <?php elseif ($currentRole === 'Barangay Head'): ?>
                <!-- ======================================================== -->
                <!-- 3. BARANGAY HEAD (ADMIN) NAVIGATION                      -->
                <!-- ======================================================== -->
                <li class="<?= ($_GET['route'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
                    <a href="index.php?route=dashboard"><i class="fa-solid fa-house"></i> Dashboard Overview</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-shield-halved"></i> Disaster Preparedness</div>
                <li class="<?= ($_GET['route'] ?? '') === 'hazard-map' ? 'active' : '' ?>">
                    <a href="index.php?route=hazard-map"><i class="fa fa-map-location-dot"></i> Live Hazard Map</a>
                </li>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['preparedness', 'preparedness-calendar', 'preparedness-create', 'preparedness-evaluate']) ? 'active' : '' ?>">
                    <a href="index.php?route=preparedness-calendar"><i class="fa fa-calendar-check"></i> Event Lists</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-file-signature"></i> Disaster Requests</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['disaster-requests-create', 'disaster-requests', 'disaster-requests-show']) ? 'active' : '' ?>">
                    <a href="index.php?route=disaster-requests-create"><i class="fa fa-file-circle-plus"></i> Fill Up Disaster Request Form</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-brain"></i> Machine Learning</div>
                <li class="<?= ($_GET['route'] ?? '') === 'recommendations' ? 'active' : '' ?>">
                    <a href="index.php?route=recommendations"><i class="fa fa-network-wired"></i> AI Priority Recommendations</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-boxes-stacked"></i> Resource Management</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['reports', 'inventory', 'resource-management']) ? 'active' : '' ?>">
                    <a href="index.php?route=reports"><i class="fa fa-boxes-stacked"></i> Resource Management</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-handshake-angle"></i> Field Operations</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['field-reports', 'field-reports-create', 'field-reports-show']) ? 'active' : '' ?>">
                    <a href="index.php?route=field-reports"><i class="fa fa-truck-medical"></i> Responder Field Status</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-users-gear"></i> Administration</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['pending-residents', 'residents-management']) ? 'active' : '' ?>">
                    <a href="index.php?route=pending-residents"><i class="fa fa-users-gear"></i> Residents Account Management</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-user-shield"></i> Account</div>
                <li class="<?= ($_GET['route'] ?? '') === 'profile' ? 'active' : '' ?>">
                    <a href="index.php?route=profile"><i class="fa fa-id-card"></i> Profile Management</a>
                </li>

            <?php elseif ($currentRole === 'ICDRRMO'): ?>
                <!-- ======================================================== -->
                <!-- 4. ICDRRMO (SUPER ADMIN) NAVIGATION                      -->
                <!-- ======================================================== -->
                <li class="<?= ($_GET['route'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
                    <a href="index.php?route=dashboard"><i class="fa-solid fa-house"></i> Dashboard Overview</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-shield-halved"></i> Disaster Preparedness</div>
                <li class="<?= ($_GET['route'] ?? '') === 'hazard-map' ? 'active' : '' ?>">
                    <a href="index.php?route=hazard-map"><i class="fa fa-map-location-dot"></i> Live Hazard Map</a>
                </li>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['preparedness', 'preparedness-calendar', 'preparedness-create', 'preparedness-evaluate']) ? 'active' : '' ?>">
                    <a href="index.php?route=preparedness-calendar"><i class="fa fa-calendar-check"></i> Event Logs</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-file-signature"></i> Disaster Requests</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['disaster-requests-create', 'disaster-requests', 'disaster-requests-show']) ? 'active' : '' ?>">
                    <a href="index.php?route=disaster-requests-create"><i class="fa fa-file-circle-plus"></i> Fill Up Disaster Request Form</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-brain"></i> Machine Learning</div>
                <li class="<?= ($_GET['route'] ?? '') === 'recommendations' ? 'active' : '' ?>">
                    <a href="index.php?route=recommendations"><i class="fa fa-network-wired"></i> AI Priority Recommendations</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-boxes-stacked"></i> Resource Management</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['reports', 'inventory', 'resource-management']) ? 'active' : '' ?>">
                    <a href="index.php?route=reports"><i class="fa fa-boxes-stacked"></i> Resource Management</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-handshake-angle"></i> Field Operations</div>
                <li class="<?= in_array(($_GET['route'] ?? ''), ['field-reports', 'field-reports-create', 'field-reports-show']) ? 'active' : '' ?>">
                    <a href="index.php?route=field-reports"><i class="fa fa-truck-medical"></i> Responder Field Status</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-users-gear"></i> Administration</div>
                <li class="<?= ($_GET['route'] ?? '') === 'users' ? 'active' : '' ?>">
                    <a href="index.php?route=users"><i class="fa fa-user-gear"></i> System User Accounts</a>
                </li>
                <li class="<?= ($_GET['route'] ?? '') === 'pending-residents' ? 'active' : '' ?>">
                    <a href="index.php?route=pending-residents"><i class="fa fa-user-check"></i> Resident Approvals</a>
                </li>
                <li class="<?= ($_GET['route'] ?? '') === 'relief-claims-oversight' ? 'active' : '' ?>">
                    <a href="index.php?route=relief-claims-oversight"><i class="fa fa-hand-holding-heart"></i> Relief Claims Oversight</a>
                </li>

                <div class="nav-section-title"><i class="fa fa-user-shield"></i> Account</div>
                <li class="<?= ($_GET['route'] ?? '') === 'profile' ? 'active' : '' ?>">
                    <a href="index.php?route=profile"><i class="fa fa-id-card"></i> Profile Management</a>
                </li>
            <?php endif; ?>
        </ul>

        <!-- Pinned Sidebar Bottom / Logout (Document Tracking System style) -->
        <div class="sidebar-footer">
            <a href="index.php?route=logout" class="sidebar-logout-link" title="Sign Out">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content Area -->
    <div class="main-content-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation Sidebar">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="page-title-header">
                    <?= htmlspecialchars($title ?? 'Barangay Hinaplanon DRRM') ?>
                </div>
            </div>

            <div class="topbar-actions">
                <!-- Notification Bell with Unread Badge (Resident only) -->
                <?php if ($currentRole === 'Resident'): ?>
                    <a href="index.php?route=notifications" class="notif-bell-btn" title="View Notifications">
                        <i class="fa fa-bell"></i>
                        <span id="notifBadgeCount" class="notif-badge-count" style="<?= empty($unreadNotificationCount) ? 'display: none;' : '' ?>">
                            <?= (int)($unreadNotificationCount ?? 0) ?>
                        </span>
                    </a>
                <?php endif; ?>

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
