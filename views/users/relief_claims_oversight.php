<!-- views/users/relief_claims_oversight.php -->
<?php
use App\Core\Auth;
use App\Core\View;

$role = Auth::role();
$totalCount = (int)($stats['total'] ?? count($beneficiaries ?? []));
$pendingCount = (int)($stats['pending'] ?? 0);
$approvedCount = (int)($stats['approved'] ?? 0);
$readyCount = (int)($stats['ready_to_claim'] ?? 0);
$claimedCount = (int)($stats['claimed'] ?? 0);
$notEligibleCount = (int)($stats['not_eligible'] ?? 0);
$highPriorityCount = (int)($stats['high_priority'] ?? 0);

// Helper for status badge styling
if (!function_exists('getOversightStatusBadge')) {
    function getOversightStatusBadge(string $status): string {
        switch ($status) {
            case 'Eligible':
                return '<span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-weight:700;"><i class="fa fa-circle-check"></i> Eligible</span>';
            case 'Pending':
                return '<span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-weight:700;"><i class="fa fa-clock"></i> Pending Review</span>';
            case 'Approved':
                return '<span class="badge" style="background:#e0e7ff; color:#4338ca; border:1px solid #c7d2fe; font-weight:700;"><i class="fa fa-shield-check"></i> Approved</span>';
            case 'Ready to Claim':
                return '<span class="badge" style="background:#fef08a; color:#854d0e; border:1px solid #fde047; font-weight:700;"><i class="fa fa-box-open"></i> Ready to Claim</span>';
            case 'Claimed':
                return '<span class="badge" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-weight:700;"><i class="fa fa-box-archive"></i> Claimed</span>';
            case 'Not Eligible':
                return '<span class="badge" style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; font-weight:700;"><i class="fa fa-circle-xmark"></i> Not Eligible</span>';
            default:
                return '<span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:600;">' . htmlspecialchars($status) . '</span>';
        }
    }
}
?>

<style>
.oversight-card {
    background: #ffffff;
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    margin-bottom: 24px;
    overflow: hidden;
}
.oversight-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.oversight-table td {
    padding: 11px 12px;
    font-size: 0.84rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
.notif-feed-item {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    font-size: 0.83rem;
}
.notif-feed-item:last-child {
    border-bottom: none;
}
</style>

<!-- Top Oversight Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fa fa-hand-holding-heart text-success"></i> Relief Claims Monitoring & Oversight
            </h2>
            <span class="badge badge-primary" style="font-size: 0.8rem; padding: 4px 10px;">
                <i class="fa fa-shield-halved"></i> <?= htmlspecialchars($role) ?> Super Admin
            </span>
        </div>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px; margin-bottom: 0;">
            Central Surveillance Desk • Real-time Ayuda claims tracking, Purok ML risk prioritization, and executive verification & intervention console.
        </p>
    </div>

    <div style="display: flex; gap: 8px; align-items: center;">
        <a href="index.php?route=pending-residents" class="btn btn-outline btn-sm">
            <i class="fa fa-users-gear"></i> Resident Approvals
        </a>
        <a href="index.php?route=dashboard" class="btn btn-outline btn-sm">
            <i class="fa fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. REAL-TIME NOTIFICATIONS ACTIVITY FEED       -->
<!-- ============================================== -->
<div class="oversight-card" style="border-left: 4px solid #3b82f6; margin-bottom: 20px;">
    <div style="padding: 12px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 0.88rem; font-weight: 700; color: #1e3a8a; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-bell text-primary"></i> Real-Time Relief Claims Activity Log
        </div>
        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.72rem;">
            <?= count($reliefNotifications ?? []) ?> Recent Logged Events
        </span>
    </div>
    <div style="max-height: 180px; overflow-y: auto;">
        <?php if (empty($reliefNotifications)): ?>
            <div style="padding: 16px; text-align: center; color: #64748b; font-size: 0.85rem;">
                <i class="fa fa-circle-check text-success"></i> No pending alerts. All submitted claims and distribution events will stream here in real time.
            </div>
        <?php else: ?>
            <?php foreach (array_slice($reliefNotifications, 0, 5) as $rn): ?>
                <?php
                    $iconClass = 'fa-circle-info text-info';
                    $bgStyle = '#ffffff';
                    if (strpos($rn['notification_type'], 'requested') !== false) {
                        $iconClass = 'fa-file-circle-plus text-warning';
                        $bgStyle = '#fffbeb';
                    } elseif (strpos($rn['notification_type'], 'approved') !== false) {
                        $iconClass = 'fa-circle-check text-primary';
                        $bgStyle = '#eff6ff';
                    } elseif (strpos($rn['notification_type'], 'rejected') !== false) {
                        $iconClass = 'fa-circle-xmark text-danger';
                        $bgStyle = '#fef2f2';
                    } elseif (strpos($rn['notification_type'], 'distributed') !== false) {
                        $iconClass = 'fa-box-archive text-success';
                        $bgStyle = '#f0fdf4';
                    }
                ?>
                <div class="notif-feed-item" style="background: <?= $bgStyle ?>;">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fa <?= $iconClass ?>" style="font-size: 1.05rem; margin-top: 2px;"></i>
                        <div>
                            <strong style="color: #0f172a; font-size: 0.84rem;"><?= htmlspecialchars($rn['title']) ?></strong>
                            <div style="color: #475569; font-size: 0.8rem; margin-top: 1px;"><?= htmlspecialchars($rn['message']) ?></div>
                        </div>
                    </div>
                    <div style="text-align: right; white-space: nowrap; font-size: 0.74rem; color: #94a3b8;">
                        <i class="fa fa-clock"></i> <?= date('M d, h:i A', strtotime($rn['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================== -->
<!-- 2. KPI METRICS (EXECUTIVE SUMMARY)             -->
<!-- ============================================== -->
<div class="grid-cols-4" style="margin-bottom: 20px;">
    <div class="stat-card" style="border-left: 4px solid #64748b;">
        <div class="stat-label"><i class="fa fa-users text-primary"></i> Total Beneficiaries</div>
        <div class="stat-value"><?= $totalCount ?></div>
        <div class="stat-sub">Enrolled in relief cycle</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #f59e0b;">
        <div class="stat-label"><i class="fa fa-clock text-warning"></i> Pending Verification</div>
        <div class="stat-value" style="color: #b45309;"><?= $pendingCount ?></div>
        <div class="stat-sub"><?= $pendingCount > 0 ? 'Awaiting Barangay / ICDRRMO approval' : 'All claims processed' ?></div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #4f46e5;">
        <div class="stat-label"><i class="fa fa-shield-check text-primary"></i> Approved Claims</div>
        <div class="stat-value" style="color: #4338ca;"><?= $approvedCount ?></div>
        <div class="stat-sub">Verified & scheduled for pickup</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #10b981;">
        <div class="stat-label"><i class="fa fa-circle-check text-success"></i> Claimed & Released</div>
        <div class="stat-value" style="color: #15803d;"><?= $claimedCount ?></div>
        <div class="stat-sub">Confirmed physical distribution</div>
    </div>
</div>

<!-- Secondary Metrics Pills -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
    <span class="badge" style="background: #fef08a; color: #854d0e; border: 1px solid #fde047; font-size: 0.8rem; padding: 6px 14px;">
        <i class="fa fa-box-open"></i> Ready to Claim: <strong><?= $readyCount ?></strong>
    </span>
    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-size: 0.8rem; padding: 6px 14px;">
        <i class="fa fa-circle-xmark"></i> Disqualified / Not Eligible: <strong><?= $notEligibleCount ?></strong>
    </span>
    <span class="badge" style="background: #fce7f3; color: #9d174d; border: 1px solid #fbcfe8; font-size: 0.8rem; padding: 6px 14px;">
        <i class="fa fa-triangle-exclamation"></i> High Priority Households: <strong><?= $highPriorityCount ?></strong>
    </span>
</div>

<!-- ============================================== -->
<!-- 3. RELIEF CLAIMS MONITORING MASTER TABLE       -->
<!-- ============================================== -->
<div class="oversight-card">
    <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <!-- Filter Tabs / Pills -->
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <a href="index.php?route=relief-claims-oversight" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                All (<?= $totalCount ?>)
            </a>
            <a href="index.php?route=relief-claims-oversight&status=Pending<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?><?= !empty($priorityFilter) ? '&priority='.$priorityFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Pending' ? 'btn-warning' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                Pending (<?= $pendingCount ?>)
            </a>
            <a href="index.php?route=relief-claims-oversight&status=Approved<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?><?= !empty($priorityFilter) ? '&priority='.$priorityFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Approved' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                Approved (<?= $approvedCount ?>)
            </a>
            <a href="index.php?route=relief-claims-oversight&status=Ready+to+Claim<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?><?= !empty($priorityFilter) ? '&priority='.$priorityFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Ready to Claim' ? 'btn-warning' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                Ready (<?= $readyCount ?>)
            </a>
            <a href="index.php?route=relief-claims-oversight&status=Claimed<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?><?= !empty($priorityFilter) ? '&priority='.$priorityFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Claimed' ? 'btn-success' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                Claimed (<?= $claimedCount ?>)
            </a>
            <a href="index.php?route=relief-claims-oversight&status=Eligible<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?><?= !empty($priorityFilter) ? '&priority='.$priorityFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Eligible' ? 'btn-outline' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                Eligible (<?= (int)($stats['eligible'] ?? 0) ?>)
            </a>
            <a href="index.php?route=relief-claims-oversight&status=Not+Eligible<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?><?= !empty($priorityFilter) ? '&priority='.$priorityFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Not Eligible' ? 'btn-danger' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                Not Eligible (<?= $notEligibleCount ?>)
            </a>
        </div>

        <!-- Filter Dropdowns & Live Search -->
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <!-- Purok Filter -->
            <form method="GET" action="index.php" style="display: flex; gap: 6px; align-items: center; margin: 0;">
                <input type="hidden" name="route" value="relief-claims-oversight">
                <?php if (!empty($statusFilter)): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                <?php endif; ?>
                <select name="purok_id" class="form-control" style="font-size: 0.8rem; padding: 4px 8px; height: 32px; width: 170px;" onchange="this.form.submit()">
                    <option value="">-- All Puroks --</option>
                    <?php foreach ($puroks as $p): ?>
                        <option value="<?= $p['purok_id'] ?>" <?= ((string)$purokFilter === (string)$p['purok_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['purok_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Priority Filter -->
                <select name="priority" class="form-control" style="font-size: 0.8rem; padding: 4px 8px; height: 32px; width: 130px;" onchange="this.form.submit()">
                    <option value="">-- All Priority --</option>
                    <option value="High" <?= $priorityFilter === 'High' ? 'selected' : '' ?>>High Priority</option>
                    <option value="Medium" <?= $priorityFilter === 'Medium' ? 'selected' : '' ?>>Medium Priority</option>
                    <option value="Low" <?= $priorityFilter === 'Low' ? 'selected' : '' ?>>Low Priority</option>
                </select>
            </form>

            <!-- Search Input -->
            <div style="position: relative;">
                <i class="fa fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem;"></i>
                <input type="text" id="oversightSearchInput" class="form-control" style="padding-left: 30px; font-size: 0.8rem; height: 32px; width: 200px;" placeholder="Filter resident or ID..." onkeyup="filterOversightTable()">
            </div>
        </div>
    </div>

    <div class="table-responsive" style="max-height: 580px; overflow-y: auto;">
        <table class="table oversight-table" id="oversightTable" style="margin: 0; width: 100%;">
            <thead>
                <tr>
                    <th>Resident / Household</th>
                    <th>Purok & Risk</th>
                    <th>Priority Level</th>
                    <th>Allocated Aid</th>
                    <th>Assistance Status</th>
                    <th>Claiming Schedule & Location</th>
                    <th>Claiming History & Timestamps</th>
                    <th style="text-align: right;">ICDRRMO Intervention</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($beneficiaries)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 32px;">
                            <i class="fa fa-folder-open" style="font-size: 1.8rem; display: block; margin-bottom: 8px;"></i>
                            No relief beneficiary records found matching the active filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($beneficiaries as $b): ?>
                        <tr class="oversight-row" data-search="<?= strtolower(htmlspecialchars($b['firstname'] . ' ' . $b['lastname'] . ' ' . ($b['id_number'] ?? '') . ' ' . ($b['purok_name'] ?? '') . ' ' . $b['assistance_status'])) ?>">
                            <!-- Resident Details -->
                            <td>
                                <strong style="color: #0f172a; font-size: 0.9rem;">
                                    <?= htmlspecialchars($b['firstname'] . ' ' . $b['lastname']) ?>
                                </strong>
                                <div style="font-family: monospace; font-size: 0.76rem; color: #475569; margin-top: 2px;">
                                    <i class="fa fa-id-card text-muted"></i> <?= htmlspecialchars($b['id_number'] ?? 'ID Pending') ?>
                                </div>
                                <div style="font-size: 0.74rem; color: #64748b;">
                                    <?= htmlspecialchars($b['cellphone_number'] ?? $b['email']) ?>
                                </div>
                            </td>

                            <!-- Purok & Hazard Risk -->
                            <td>
                                <strong style="color: #1e293b;"><?= htmlspecialchars($b['purok_name'] ?? 'Hinaplanon') ?></strong>
                                <div><?= View::riskBadge($b['purok_risk'] ?? 'Pending Verification') ?></div>
                            </td>

                            <!-- ML Priority Level -->
                            <td>
                                <?= View::priorityBadge($b['priority_level']) ?>
                                <small style="display: block; font-size: 0.72rem; color: #64748b; margin-top: 3px; max-width: 170px;" title="<?= htmlspecialchars($b['priority_reason'] ?? 'CART Decision Tree risk model') ?>">
                                    <?= htmlspecialchars(mb_strimwidth($b['priority_reason'] ?? 'Decision Tree hazard model', 0, 42, '...')) ?>
                                </small>
                            </td>

                            <!-- Allocated Aid -->
                            <td>
                                <strong style="color: #065f46; font-size: 0.88rem;">
                                    <?= (int)$b['allocated_quantity'] ?>x
                                </strong>
                                <span style="font-size: 0.8rem; color: #334155;">
                                    <?= htmlspecialchars($b['assistance_type'] ?? 'Standard Family Food Pack') ?>
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <?= getOversightStatusBadge($b['assistance_status']) ?>
                                <?php if (!empty($b['is_approved'])): ?>
                                    <small style="display: block; font-size: 0.7rem; color: #15803d; margin-top: 2px;">
                                        <i class="fa fa-check"></i> Authorized
                                    </small>
                                <?php endif; ?>
                            </td>

                            <!-- Claiming Schedule & Station -->
                            <td>
                                <div style="font-weight: 700; color: #1e40af; font-size: 0.82rem;">
                                    <i class="fa fa-calendar-day"></i> <?= !empty($b['claiming_date']) ? date('M d, Y', strtotime($b['claiming_date'])) : 'Pending' ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #1e3a8a;">
                                    Window: <?= htmlspecialchars($b['claiming_time'] ?? '08:00 AM - 12:00 PM') ?>
                                </div>
                                <small style="color: #64748b; display: block;">
                                    <i class="fa fa-location-dot text-danger"></i> <?= htmlspecialchars(mb_strimwidth($b['claiming_location'] ?? 'Hinaplanon Station', 0, 26, '...')) ?>
                                </small>
                            </td>

                            <!-- History & Timestamps -->
                            <td>
                                <div style="font-size: 0.75rem; color: #475569;">
                                    <?php if (!empty($b['claim_requested_at'])): ?>
                                        <div><strong style="color: #92400e;">Requested:</strong> <?= date('M d, h:i A', strtotime($b['claim_requested_at'])) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($b['verified_at'])): ?>
                                        <div>
                                            <strong style="color: #4338ca;">Verified:</strong> <?= date('M d, h:i A', strtotime($b['verified_at'])) ?>
                                            <?php if (!empty($b['verifier_first'])): ?>
                                                <small style="color: #64748b;">(<?= htmlspecialchars($b['verifier_first'] . ' ' . $b['verifier_last']) ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($b['claimed_at'])): ?>
                                        <div>
                                            <strong style="color: #0369a1;">Claimed:</strong> <?= date('M d, h:i A', strtotime($b['claimed_at'])) ?>
                                            <?php if (!empty($b['distributor_first'])): ?>
                                                <small style="color: #64748b;">(<?= htmlspecialchars($b['distributor_first'] . ' ' . $b['distributor_last']) ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($b['remarks'])): ?>
                                        <div style="font-style: italic; color: #64748b; margin-top: 2px;">
                                            Note: <?= htmlspecialchars(mb_strimwidth($b['remarks'], 0, 36, '...')) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Executive Actions & Intervention -->
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: flex; gap: 4px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                                    <!-- Contextual Quick Actions -->
                                    <?php if ($b['assistance_status'] === 'Pending'): ?>
                                        <form action="index.php?route=beneficiary-status" method="POST" style="margin: 0; display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="beneficiary_id" value="<?= (int)$b['beneficiary_id'] ?>">
                                            <input type="hidden" name="assistance_status" value="Approved">
                                            <input type="hidden" name="redirect" value="oversight">
                                            <input type="hidden" name="remarks" value="Verified & approved via ICDRRMO Oversight Console.">
                                            <button type="submit" class="btn btn-sm btn-primary" style="font-size: 0.72rem; padding: 2px 7px;" title="Approve Claim">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                        </form>
                                    <?php elseif ($b['assistance_status'] === 'Approved'): ?>
                                        <form action="index.php?route=beneficiary-status" method="POST" style="margin: 0; display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="beneficiary_id" value="<?= (int)$b['beneficiary_id'] ?>">
                                            <input type="hidden" name="assistance_status" value="Ready to Claim">
                                            <input type="hidden" name="redirect" value="oversight">
                                            <input type="hidden" name="remarks" value="Marked Ready to Claim by ICDRRMO Oversight.">
                                            <button type="submit" class="btn btn-sm btn-outline" style="font-size: 0.72rem; padding: 2px 7px; color: #854d0e; border-color: #fde047; background: #fefce8;" title="Mark Ready to Claim">
                                                <i class="fa fa-box-open"></i> Ready
                                            </button>
                                        </form>
                                    <?php elseif ($b['assistance_status'] === 'Ready to Claim'): ?>
                                        <form action="index.php?route=beneficiary-distribute" method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Confirm release of relief package to resident?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="beneficiary_id" value="<?= (int)$b['beneficiary_id'] ?>">
                                            <input type="hidden" name="redirect" value="oversight">
                                            <button type="submit" class="btn btn-sm btn-success" style="font-size: 0.72rem; padding: 2px 7px;" title="Confirm Distribution">
                                                <i class="fa fa-hand-holding-heart"></i> Distribute
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Executive Intervention Modal Button -->
                                    <button type="button" class="btn btn-sm btn-outline" style="font-size: 0.72rem; padding: 2px 7px; color: #4338ca; border-color: #c7d2fe;" onclick="openInterventionModal(<?= htmlspecialchars(json_encode([
                                        'beneficiary_id' => (int)$b['beneficiary_id'],
                                        'resident_name' => $b['firstname'] . ' ' . $b['lastname'],
                                        'purok_name' => $b['purok_name'] ?? 'Hinaplanon',
                                        'id_number' => $b['id_number'] ?? '—',
                                        'status' => $b['assistance_status'],
                                        'priority' => $b['priority_level'],
                                        'allocated_aid' => $b['allocated_quantity'] . 'x ' . ($b['assistance_type'] ?? 'Food Pack'),
                                        'claiming_date' => $b['claiming_date'] ?? date('Y-m-d', strtotime('+3 days')),
                                        'claiming_time' => $b['claiming_time'] ?? '08:00 AM - 12:00 PM',
                                        'claiming_location' => $b['claiming_location'] ?? 'Hinaplanon Covered Court, Station B',
                                        'remarks' => $b['remarks'] ?? ''
                                    ]), ENT_QUOTES, 'UTF-8') ?>)" title="ICDRRMO Executive Intervention">
                                        <i class="fa fa-pen-to-square"></i> Intervene
                                    </button>

                                    <!-- History Detail Modal Button -->
                                    <button type="button" class="btn btn-sm btn-outline" style="font-size: 0.72rem; padding: 2px 7px; color: #475569;" onclick="openHistoryModal(<?= htmlspecialchars(json_encode([
                                        'resident_name' => $b['firstname'] . ' ' . $b['lastname'],
                                        'id_number' => $b['id_number'] ?? '—',
                                        'purok_name' => $b['purok_name'] ?? 'Hinaplanon',
                                        'status' => $b['assistance_status'],
                                        'priority_level' => $b['priority_level'],
                                        'priority_reason' => $b['priority_reason'] ?? '',
                                        'claim_requested_at' => $b['claim_requested_at'] ?? 'Not yet requested',
                                        'verified_at' => $b['verified_at'] ?? 'Pending',
                                        'verifier' => trim(($b['verifier_first'] ?? '') . ' ' . ($b['verifier_last'] ?? '')),
                                        'claimed_at' => $b['claimed_at'] ?? 'Not yet claimed',
                                        'distributor' => trim(($b['distributor_first'] ?? '') . ' ' . ($b['distributor_last'] ?? '')),
                                        'claiming_location' => $b['claiming_location'] ?? '—',
                                        'remarks' => $b['remarks'] ?? 'None recorded'
                                    ]), ENT_QUOTES, 'UTF-8') ?>)" title="View Claim History Timeline">
                                        <i class="fa fa-timeline"></i> History
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================== -->
<!-- 4. ICDRRMO EXECUTIVE INTERVENTION MODAL       -->
<!-- ============================================== -->
<div id="oversightInterventionModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.65); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 14px; max-width: 540px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: fadeInModal 0.2s ease-out;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 16px 20px; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa fa-shield-halved" style="font-size: 1.25rem;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #ffffff;">ICDRRMO Executive Intervention</h3>
                    <small style="opacity: 0.85; font-size: 0.74rem;">Super Admin Status Override & Schedule Management</small>
                </div>
            </div>
            <button type="button" onclick="closeInterventionModal()" style="background: none; border: none; color: #ffffff; font-size: 1.25rem; cursor: pointer; padding: 4px;">
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <!-- Form -->
        <form action="index.php?route=beneficiary-status" method="POST" style="padding: 20px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="beneficiary_id" id="modal_intervene_ben_id" value="">
            <input type="hidden" name="redirect" value="oversight">

            <!-- Resident Info Banner -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong id="modal_intervene_name" style="font-size: 0.95rem; color: #0f172a;">Resident Name</strong>
                        <div style="font-size: 0.78rem; color: #64748b;">
                            Purok: <span id="modal_intervene_purok" style="font-weight: 600; color: #1e293b;"></span> | 
                            Barangay ID: <span id="modal_intervene_id"></span>
                        </div>
                    </div>
                    <span id="modal_intervene_prio_badge"></span>
                </div>
                <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #e2e8f0; font-size: 0.78rem; color: #065f46;">
                    Allocation: <strong id="modal_intervene_aid"></strong>
                </div>
            </div>

            <!-- Status Selector -->
            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                    Executive Assistance Status *
                </label>
                <select name="assistance_status" id="modal_intervene_status" class="form-control" required style="font-weight: 600;">
                    <option value="Eligible">Eligible (Pre-Claim Inclusion)</option>
                    <option value="Pending">Pending (Under Review)</option>
                    <option value="Approved">Approved (Verified & Scheduled)</option>
                    <option value="Ready to Claim">Ready to Claim (Staged at Station)</option>
                    <option value="Claimed">Claimed (Confirmed Released)</option>
                    <option value="Not Eligible">Not Eligible (Disqualified / Outside Boundary)</option>
                </select>
                <small style="color: #64748b; font-size: 0.74rem; display: block; margin-top: 3px;">
                    Updating this status sends an instant real-time notification to the resident and logs an intervention event in the Barangay Head alert stream.
                </small>
            </div>

            <!-- Justification / Remarks -->
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                    Intervention Justification / Remarks *
                </label>
                <textarea name="remarks" id="modal_intervene_remarks" class="form-control" rows="2" required placeholder="e.g. Verified by ICDRRMO per flood triage cross-check; household approved for emergency relief."></textarea>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeInterventionModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    <i class="fa fa-shield-check"></i> Apply Intervention
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- 5. CLAIM HISTORY & TIMELINE MODAL             -->
<!-- ============================================== -->
<div id="claimHistoryDetailModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.65); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 14px; max-width: 500px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: fadeInModal 0.2s ease-out;">
        <div style="background: linear-gradient(135deg, #334155 0%, #1e293b 100%); padding: 16px 20px; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-timeline" style="font-size: 1.15rem;"></i>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #ffffff;">Claim Lifecycle & Audit Trail</h3>
            </div>
            <button type="button" onclick="closeHistoryModal()" style="background: none; border: none; color: #ffffff; font-size: 1.25rem; cursor: pointer; padding: 4px;">
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <div style="padding: 20px; max-height: 480px; overflow-y: auto;">
            <div style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                <strong id="hist_name" style="font-size: 1rem; color: #0f172a;">Resident Name</strong>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">
                    Barangay ID: <code id="hist_id"></code> | Purok: <strong id="hist_purok"></strong>
                </div>
            </div>

            <!-- Timeline Steps -->
            <div style="display: flex; flex-direction: column; gap: 14px; font-size: 0.84rem;">
                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-brain"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a;">AI Priority Classification:</strong>
                        <div id="hist_priority" style="margin-top: 2px;"></div>
                        <small id="hist_reason" style="color: #64748b; display: block; margin-top: 2px;"></small>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-hand-holding-heart"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a;">Resident Claim Request:</strong>
                        <div id="hist_requested" style="color: #64748b; margin-top: 2px;"></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-shield-check"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a;">Administrative Verification / Approval:</strong>
                        <div id="hist_verified" style="color: #64748b; margin-top: 2px;"></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-box-archive"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a;">Distribution & Release Confirmation:</strong>
                        <div id="hist_claimed" style="color: #64748b; margin-top: 2px;"></div>
                        <div style="color: #64748b; font-size: 0.78rem; margin-top: 2px;">
                            Pickup Station: <span id="hist_location"></span>
                        </div>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-top: 6px;">
                    <strong style="color: #475569; font-size: 0.78rem; text-transform: uppercase;">Official Notes / Remarks:</strong>
                    <div id="hist_remarks" style="color: #1e293b; font-size: 0.82rem; margin-top: 3px; font-style: italic;"></div>
                </div>
            </div>

            <div style="margin-top: 18px; text-align: right;">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeHistoryModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterOversightTable() {
    const input = document.getElementById('oversightSearchInput');
    const filter = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.oversight-row');

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (text.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openInterventionModal(data) {
    document.getElementById('modal_intervene_ben_id').value = data.beneficiary_id;
    document.getElementById('modal_intervene_name').textContent = data.resident_name;
    document.getElementById('modal_intervene_purok').textContent = data.purok_name;
    document.getElementById('modal_intervene_id').textContent = data.id_number;
    document.getElementById('modal_intervene_aid').textContent = data.allocated_aid;
    document.getElementById('modal_intervene_status').value = data.status;
    document.getElementById('modal_intervene_remarks').value = data.remarks || '';

    const badgeSpan = document.getElementById('modal_intervene_prio_badge');
    badgeSpan.innerHTML = '<span class="badge badge-info">' + data.priority + ' Priority</span>';

    document.getElementById('oversightInterventionModal').style.display = 'flex';
}

function closeInterventionModal() {
    document.getElementById('oversightInterventionModal').style.display = 'none';
}

function openHistoryModal(data) {
    document.getElementById('hist_name').textContent = data.resident_name;
    document.getElementById('hist_id').textContent = data.id_number;
    document.getElementById('hist_purok').textContent = data.purok_name;
    document.getElementById('hist_priority').textContent = data.priority_level + ' Priority';
    document.getElementById('hist_reason').textContent = data.priority_reason || 'Purok ML Hazard evaluation';
    document.getElementById('hist_requested').textContent = data.claim_requested_at !== 'Not yet requested' ? data.claim_requested_at : 'No claim submitted yet';
    document.getElementById('hist_verified').textContent = data.verified_at !== 'Pending' ? (data.verified_at + (data.verifier ? ' (By ' + data.verifier + ')' : '')) : 'Pending Verification';
    document.getElementById('hist_claimed').textContent = data.claimed_at !== 'Not yet claimed' ? (data.claimed_at + (data.distributor ? ' (By ' + data.distributor + ')' : '')) : 'Awaiting physical release';
    document.getElementById('hist_location').textContent = data.claiming_location;
    document.getElementById('hist_remarks').textContent = data.remarks;

    document.getElementById('claimHistoryDetailModal').style.display = 'flex';
}

function closeHistoryModal() {
    document.getElementById('claimHistoryDetailModal').style.display = 'none';
}

// Close modals when clicking backdrop
window.addEventListener('click', function(e) {
    const intModal = document.getElementById('oversightInterventionModal');
    if (e.target === intModal) closeInterventionModal();

    const histModal = document.getElementById('claimHistoryDetailModal');
    if (e.target === histModal) closeHistoryModal();
});
</script>

