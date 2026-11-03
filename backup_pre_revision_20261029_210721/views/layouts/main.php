<!-- views/layouts/main.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Disaster Preparedness') ?> — Hinaplanon DRRM</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-title"><i class="fa fa-shield-halved"></i> Hinaplanon DRRM</div>
            <div class="brand-subtitle">Intelligent Resource Recommender</div>
        </div>
        <ul class="sidebar-nav">
            <li class="<?= ($_GET['route'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
                <a href="index.php?route=dashboard"><i class="fa fa-gauge-high"></i> Dashboard</a>
            </li>

            <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                <div class="nav-section-title">Administration</div>
                <?php if ($currentRole === 'ICDRRMO'): ?>
                    <li class="<?= ($_GET['route'] ?? '') === 'users' ? 'active' : '' ?>">
                        <a href="index.php?route=users"><i class="fa fa-users-gear"></i> User Accounts</a>
                    </li>
                <?php endif; ?>
                <li class="<?= ($_GET['route'] ?? '') === 'pending-residents' ? 'active' : '' ?>">
                    <a href="index.php?route=pending-residents"><i class="fa fa-user-check"></i> Resident Approvals</a>
                </li>
            <?php endif; ?>

            <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head', 'Responder'])): ?>
                <div class="nav-section-title">Emergency Resources</div>
                <li class="<?= ($_GET['route'] ?? '') === 'inventory' ? 'active' : '' ?>">
                    <a href="index.php?route=inventory"><i class="fa fa-boxes-stacked"></i> Supply Inventory</a>
                </li>
            <?php endif; ?>

            <div class="nav-section-title">Operations & AI</div>
            <li class="<?= ($_GET['route'] ?? '') === 'disaster-requests' ? 'active' : '' ?>">
                <a href="index.php?route=disaster-requests"><i class="fa fa-triangle-exclamation"></i> Disaster Requests</a>
            </li>
            <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head', 'Responder'])): ?>
                <li class="<?= ($_GET['route'] ?? '') === 'recommendations' ? 'active' : '' ?>">
                    <a href="index.php?route=recommendations"><i class="fa fa-network-wired"></i> AI Recommendations</a>
                </li>
            <?php endif; ?>
            <li class="<?= ($_GET['route'] ?? '') === 'hazard-map' ? 'active' : '' ?>">
                <a href="index.php?route=hazard-map"><i class="fa fa-map-location-dot"></i> Hinaplanon Hazard Map</a>
            </li>

            <div class="nav-section-title">Community & Response</div>
            <li class="<?= in_array(($_GET['route'] ?? ''), ['preparedness', 'preparedness-calendar']) ? 'active' : '' ?>">
                <a href="index.php?route=preparedness"><i class="fa fa-calendar-check"></i> Preparedness Activities</a>
            </li>
            <li class="<?= in_array(($_GET['route'] ?? ''), ['field-reports', 'field-reports-create']) ? 'active' : '' ?>">
                <a href="index.php?route=field-reports"><i class="fa fa-truck-medical"></i> Field Reporting</a>
            </li>

            <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                <div class="nav-section-title">Intelligence & ML</div>
                <li class="<?= ($_GET['route'] ?? '') === 'ml-model' ? 'active' : '' ?>">
                    <a href="index.php?route=ml-model"><i class="fa fa-diagram-project"></i> Decision Tree Engine</a>
                </li>
                <li class="<?= ($_GET['route'] ?? '') === 'historical-records' ? 'active' : '' ?>">
                    <a href="index.php?route=historical-records"><i class="fa fa-database"></i> Historical Dataset</a>
                </li>
                <li class="<?= ($_GET['route'] ?? '') === 'reports' ? 'active' : '' ?>">
                    <a href="index.php?route=reports"><i class="fa fa-file-invoice"></i> Reports & Export</a>
                </li>
            <?php endif; ?>

            <div class="nav-section-title">Research & Thesis</div>
            <li class="<?= in_array(($_GET['route'] ?? ''), ['evaluation-form', 'evaluation-results']) ? 'active' : '' ?>">
                <a href="index.php?route=evaluation-results"><i class="fa fa-chart-pie"></i> TAM & ISO 9126 Evaluation</a>
            </li>
            <li class="<?= ($_GET['route'] ?? '') === 'profile' ? 'active' : '' ?>">
                <a href="index.php?route=profile"><i class="fa fa-id-card"></i> My Profile</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content-wrapper">
        <header class="topbar">
            <div class="page-title-header">
                <?= htmlspecialchars($title ?? 'Barangay Hinaplanon DRRM') ?>
            </div>
            <div class="user-profile-badge">
                <span class="role-pill role-<?= strtolower(str_replace(' ', '-', $currentRole)) ?>">
                    <?= htmlspecialchars($currentRole) ?>
                </span>
                <span style="font-size: 0.88rem; font-weight: 600;">
                    <?= htmlspecialchars($currentUser['firstname'] ?? '') ?> <?= htmlspecialchars($currentUser['lastname'] ?? '') ?>
                </span>
                <a href="index.php?route=logout" class="btn btn-outline btn-sm" title="Sign Out">
                    <i class="fa fa-right-from-bracket"></i> Sign Out
                </a>
            </div>
        </header>

        <main class="content-container">
            <?php if (!empty($flashes)): ?>
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($type) ?>">
                            <i class="fa fa-circle-info"></i>
                            <div><?= htmlspecialchars($msg) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
