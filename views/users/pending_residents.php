<!-- views/users/pending_residents.php -->
<?php
use App\Core\Auth;
$role = Auth::role();
$totalResidents = count($allResidents ?? []);
$pendingCount = count($pendingResidents ?? []);
$approvedCount = count($approvedResidents ?? []);
$declinedCount = count($declinedResidents ?? []);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-users-gear text-primary"></i> Residents Account Management
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">
            Barangay Hinaplanon Citizen Identity Gatekeeper & Resident Credential Management
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <span class="badge badge-primary" style="font-size: 0.85rem; padding: 8px 14px;">
            <i class="fa fa-shield-halved"></i> Authorized Role: <?= htmlspecialchars($role) ?>
        </span>
    </div>
</div>

<!-- ========================================== -->
<!-- 1. RESIDENT REGISTRATION & ACCOUNT METRICS -->
<!-- ========================================== -->
<div class="grid-cols-4" style="margin-bottom: 20px;">
    <div class="stat-card" style="border-left: 4px solid var(--primary);">
        <div class="stat-label"><i class="fa fa-address-book text-primary"></i> Total Residents</div>
        <div class="stat-value"><?= $totalResidents ?></div>
        <div class="stat-sub">Registered Hinaplanon residents</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid var(--warning);">
        <div class="stat-label"><i class="fa fa-user-clock text-warning"></i> Pending Approvals</div>
        <div class="stat-value" style="color: <?= $pendingCount > 0 ? 'var(--warning)' : 'var(--text-muted)' ?>;"><?= $pendingCount ?></div>
        <div class="stat-sub"><?= $pendingCount > 0 ? 'Requires identity verification' : 'All registrations verified' ?></div>
    </div>

    <div class="stat-card" style="border-left: 4px solid var(--success);">
        <div class="stat-label"><i class="fa fa-user-check text-success"></i> Active Accounts</div>
        <div class="stat-value" style="color: var(--success);"><?= $approvedCount ?></div>
        <div class="stat-sub">Verified & authorized residents</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid var(--danger);">
        <div class="stat-label"><i class="fa fa-user-xmark text-danger"></i> Declined Requests</div>
        <div class="stat-value" style="color: var(--danger);"><?= $declinedCount ?></div>
        <div class="stat-sub">Census verification failed</div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. RESIDENT ACCOUNT UNIFIED FRAME          -->
<!-- ========================================== -->
<div class="card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #f8fafc; border-bottom: 1px solid var(--border-light);">
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <button type="button" class="btn btn-sm <?= $pendingCount > 0 ? 'btn-primary' : 'btn-outline' ?> tab-btn" id="tab-btn-pending" onclick="switchResidentTab('pending')">
                <i class="fa fa-user-clock"></i> Pending Approvals
                <?php if ($pendingCount > 0): ?>
                    <span class="badge badge-warning" style="margin-left: 4px;"><?= $pendingCount ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-sm <?= $pendingCount === 0 ? 'btn-primary' : 'btn-outline' ?> tab-btn" id="tab-btn-approved" onclick="switchResidentTab('approved')">
                <i class="fa fa-user-check"></i> Approved Residents & Passwords
                <span class="badge badge-success" style="margin-left: 4px;"><?= $approvedCount ?></span>
            </button>
            <button type="button" class="btn btn-sm btn-outline tab-btn" id="tab-btn-all" onclick="switchResidentTab('all')">
                <i class="fa fa-address-book"></i> All Resident Records (<?= $totalResidents ?>)
            </button>
            <button type="button" class="btn btn-sm btn-outline tab-btn" id="tab-btn-relief" onclick="switchResidentTab('relief')">
                <i class="fa fa-hand-holding-heart"></i> Relief Beneficiary Management (<?= count($reliefBeneficiaries ?? []) ?>)
            </button>
        </div>

        <div style="display: flex; gap: 8px; align-items: center; min-width: 280px;">
            <div style="position: relative; width: 100%;">
                <i class="fa fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                <input type="text" id="residentSearchInput" class="form-control" style="padding-left: 32px; font-size: 0.85rem;" placeholder="Search name, ID, purok, or email..." onkeyup="filterResidentTables()">
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- ============================================== -->
        <!-- TAB 1: PENDING RESIDENT APPROVALS              -->
        <!-- ============================================== -->
        <div id="tab-content-pending" style="display: <?= $pendingCount > 0 ? 'block' : 'none' ?>;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--primary-dark); margin: 0;">
                        <i class="fa fa-user-clock text-warning"></i> Resident Registration Verification Queue
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
                        Resident registrations submitted with Barangay IDs require Barangay Head validation before access is activated.
                    </p>
                </div>
            </div>

            <?php if (empty($pendingResidents)): ?>
                <div class="alert alert-info" style="display: flex; align-items: center; gap: 12px; padding: 20px; border-radius: 8px;">
                    <i class="fa fa-circle-check" style="font-size: 1.5rem; color: var(--info);"></i>
                    <div>
                        <strong>No pending resident registrations!</strong>
                        <div style="font-size: 0.85rem; margin-top: 2px;">All registered residents have been processed. New signups requiring verification will appear here automatically.</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table resident-table" id="table-pending">
                        <thead>
                            <tr>
                                <th>Barangay ID Number</th>
                                <th>Resident Full Name</th>
                                <th>Purok Assignment</th>
                                <th>Age / Gender</th>
                                <th>Contact Mobile</th>
                                <th>Registration Date</th>
                                <th>Initial Password</th>
                                <th>Verification Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingResidents as $res): ?>
                                <tr class="resident-row">
                                    <td>
                                        <strong style="font-family: monospace; font-size: 0.95rem; color: var(--primary-dark); background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">
                                            <?= htmlspecialchars($res['id_number'] ?? '—') ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($res['firstname'] . ' ' . ($res['middle_name'] ? $res['middle_name'] . ' ' : '') . $res['lastname']) ?></strong></div>
                                        <small style="color: var(--text-muted);"><?= htmlspecialchars($res['email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($res['purok_name'] ?? 'Not Specified') ?></span>
                                    </td>
                                    <td>
                                        <?= !empty($res['age']) ? (int)$res['age'] . ' yrs' : '—' ?>
                                        <small style="color: var(--text-muted);">(<?= htmlspecialchars($res['gender'] ?? '—') ?>)</small>
                                    </td>
                                    <td><?= htmlspecialchars($res['cellphone_number'] ?? '—') ?></td>
                                    <td><?= date('M d, Y h:i A', strtotime($res['created_at'])) ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span id="pending-pwd-<?= $res['user_id'] ?>" style="font-family: monospace; font-weight: 700; color: #334155; letter-spacing: 2px;">••••••••</span>
                                            <button type="button" class="btn btn-outline btn-sm btn-icon" onclick="togglePasswordDisplay('pending-pwd-<?= $res['user_id'] ?>', '<?= htmlspecialchars($res['plain_password'] ?? 'resident123', ENT_QUOTES) ?>', this)" title="Show/Hide Password" style="padding: 2px 7px; font-size: 0.75rem;">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <!-- Approve Form -->
                                            <form action="index.php?route=approve-resident" method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="user_id" value="<?= (int)$res['user_id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                            </form>

                                            <!-- Decline Form -->
                                            <form action="index.php?route=reject-resident" method="POST" style="display: inline;" onsubmit="return confirm('Decline this resident registration?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="user_id" value="<?= (int)$res['user_id'] ?>">
                                                <input type="hidden" name="rejection_reason" value="Identity not verified against Barangay Hinaplanon Census records">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-xmark"></i> Decline
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: APPROVED RESIDENTS & PASSWORD ACCESS    -->
        <!-- ============================================== -->
        <div id="tab-content-approved" style="display: <?= $pendingCount === 0 ? 'block' : 'none' ?>;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--primary-dark); margin: 0;">
                        <i class="fa fa-user-check text-success"></i> Approved Barangay Hinaplanon Residents
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
                        View verified resident accounts, reveal forgot-passwords upon citizen request, or reset credentials securely.
                    </p>
                </div>
                <div>
                    <span style="font-size: 0.8rem; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                        <i class="fa fa-key"></i> Passwords viewable and resettable by Barangay Head
                    </span>
                </div>
            </div>

            <?php if (empty($approvedResidents)): ?>
                <div class="alert alert-info">No approved residents found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table resident-table" id="table-approved">
                        <thead>
                            <tr>
                                <th>Barangay ID</th>
                                <th>Resident Name</th>
                                <th>Purok Assignment</th>
                                <th>Contact Mobile</th>
                                <th>Email Identifier</th>
                                <th>Account Password</th>
                                <th>Credential Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedResidents as $res): ?>
                                <tr class="resident-row">
                                    <td>
                                        <strong style="font-family: monospace; font-size: 0.95rem; color: var(--primary-dark); background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">
                                            <?= htmlspecialchars($res['id_number'] ?? '—') ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($res['firstname'] . ' ' . ($res['middle_name'] ? $res['middle_name'] . ' ' : '') . $res['lastname']) ?></strong></div>
                                        <small style="color: var(--text-muted);"><?= !empty($res['age']) ? (int)$res['age'] . ' yrs, ' : '' ?><?= htmlspecialchars($res['gender'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($res['purok_name'] ?? 'Hinaplanon') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($res['cellphone_number'] ?? '—') ?></td>
                                    <td><small style="color: #475569;"><?= htmlspecialchars($res['email']) ?></small></td>
                                    <td>
                                        <!-- Password View with Eye Toggle -->
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span id="approved-pwd-<?= $res['user_id'] ?>" style="font-family: monospace; font-size: 0.95rem; font-weight: 700; color: #1e293b; letter-spacing: 2px;">••••••••</span>
                                            <button type="button" class="btn btn-outline btn-sm btn-icon" onclick="togglePasswordDisplay('approved-pwd-<?= $res['user_id'] ?>', '<?= htmlspecialchars($res['plain_password'] ?? 'resident123', ENT_QUOTES) ?>', this)" title="Click to See Resident Password">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Reset / Change Password Button -->
                                        <button type="button" class="btn btn-primary btn-sm" onclick="openResetPasswordModal(<?= htmlspecialchars(json_encode([
                                            'user_id' => (int)$res['user_id'],
                                            'name' => $res['firstname'] . ' ' . $res['lastname'],
                                            'id_number' => $res['id_number'] ?? '—',
                                            'email' => $res['email'],
                                            'current_password' => $res['plain_password'] ?? 'resident123'
                                        ]), ENT_QUOTES) ?>)">
                                            <i class="fa fa-key"></i> Change Password
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: ALL RESIDENT DIRECTORY                  -->
        <!-- ============================================== -->
        <div id="tab-content-all" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--primary-dark); margin: 0;">
                        <i class="fa fa-address-book text-primary"></i> Master Resident Directory
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
                        Comprehensive list of all registered citizens including approved, pending, and declined accounts.
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table resident-table" id="table-all">
                    <thead>
                        <tr>
                            <th>Barangay ID</th>
                            <th>Resident Name</th>
                            <th>Purok Assignment</th>
                            <th>Status</th>
                            <th>Contact Mobile</th>
                            <th>Account Password</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allResidents as $res): ?>
                            <tr class="resident-row">
                                <td>
                                    <strong style="font-family: monospace; font-size: 0.95rem; color: var(--primary-dark); background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">
                                        <?= htmlspecialchars($res['id_number'] ?? '—') ?>
                                    </strong>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($res['firstname'] . ' ' . $res['lastname']) ?></strong></div>
                                    <small style="color: var(--text-muted);"><?= htmlspecialchars($res['email']) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($res['purok_name'] ?? 'Hinaplanon') ?></span>
                                </td>
                                <td>
                                    <?php if ($res['is_approved'] == 1): ?>
                                        <span class="badge badge-success"><i class="fa fa-check"></i> Approved</span>
                                    <?php elseif ($res['is_approved'] == 0): ?>
                                        <span class="badge badge-warning"><i class="fa fa-clock"></i> Pending Review</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fa fa-ban"></i> Declined</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($res['cellphone_number'] ?? '—') ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span id="all-pwd-<?= $res['user_id'] ?>" style="font-family: monospace; font-size: 0.95rem; font-weight: 700; color: #1e293b; letter-spacing: 2px;">••••••••</span>
                                        <button type="button" class="btn btn-outline btn-sm btn-icon" onclick="togglePasswordDisplay('all-pwd-<?= $res['user_id'] ?>', '<?= htmlspecialchars($res['plain_password'] ?? 'resident123', ENT_QUOTES) ?>', this)" title="Show/Hide Password">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openResetPasswordModal(<?= htmlspecialchars(json_encode([
                                        'user_id' => (int)$res['user_id'],
                                        'name' => $res['firstname'] . ' ' . $res['lastname'],
                                        'id_number' => $res['id_number'] ?? '—',
                                        'email' => $res['email'],
                                        'current_password' => $res['plain_password'] ?? 'resident123'
                                    ]), ENT_QUOTES) ?>)">
                                        <i class="fa fa-key"></i> Reset
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 4: RELIEF BENEFICIARIES & AYUDA ALLOCATION -->
        <!-- ============================================== -->
        <div id="tab-content-relief" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #065f46; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-hand-holding-heart text-success"></i> Relief Beneficiary Management & Ayuda Verification
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
                        Manage resident claiming eligibility, verify beneficiaries, approve relief allocations, and configure distribution schedules.
                    </p>
                </div>
                <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                    <?php if ($role === 'ICDRRMO'): ?>
                        <a href="index.php?route=relief-claims-oversight" class="btn btn-primary btn-sm" style="font-size: 0.78rem;">
                            <i class="fa fa-shield-halved"></i> Open Oversight Console
                        </a>
                    <?php endif; ?>
                    <span class="badge" style="background:#dcfce7; color:#15803d;">Eligible: <?= (int)($beneficiaryStats['eligible'] ?? 0) ?></span>
                    <span class="badge" style="background:#fef3c7; color:#b45309;">Pending: <?= (int)($beneficiaryStats['pending'] ?? 0) ?></span>
                    <span class="badge" style="background:#dbeafe; color:#1e40af;">Approved: <?= (int)($beneficiaryStats['approved'] ?? 0) ?></span>
                    <span class="badge" style="background:#ccfbf1; color:#0f766e;">Ready: <?= (int)($beneficiaryStats['ready_to_claim'] ?? 0) ?></span>
                    <span class="badge" style="background:#e0f2fe; color:#0369a1;">Claimed: <?= (int)($beneficiaryStats['claimed'] ?? 0) ?></span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Resident Beneficiary</th>
                            <th>Purok & Risk</th>
                            <th>Priority Assessment</th>
                            <th>Current Status</th>
                            <th>Update Status</th>
                            <th>Schedule Date & Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reliefBeneficiaries)): ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">No relief beneficiaries recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reliefBeneficiaries as $ben): ?>
                                <tr class="resident-row">
                                    <td>
                                        <strong><?= htmlspecialchars($ben['firstname'] . ' ' . $ben['lastname']) ?></strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            <?= htmlspecialchars($ben['email']) ?> | Tel: <?= htmlspecialchars($ben['cellphone_number'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($ben['purok_name'] ?? 'Hinaplanon') ?></strong>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                                            Risk: <?= htmlspecialchars($ben['purok_risk'] ?? 'Medium') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= ($ben['priority_level'] === 'High' ? 'danger' : ($ben['priority_level'] === 'Medium' ? 'warning' : 'info')) ?>">
                                            <?= htmlspecialchars($ben['priority_level']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($ben['assistance_status'] === 'Eligible'): ?>
                                            <span class="badge badge-success"><i class="fa fa-circle-check"></i> Eligible</span>
                                        <?php elseif ($ben['assistance_status'] === 'Approved'): ?>
                                            <span class="badge" style="background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe; font-weight:700;"><i class="fa fa-clipboard-check"></i> Approved</span>
                                        <?php elseif ($ben['assistance_status'] === 'Ready to Claim'): ?>
                                            <span class="badge" style="background:#ccfbf1; color:#0f766e; border:1px solid #99f6e4; font-weight:700;"><i class="fa fa-boxes-packing"></i> Ready to Claim</span>
                                        <?php elseif ($ben['assistance_status'] === 'Claimed'): ?>
                                            <span class="badge badge-primary"><i class="fa fa-box-archive"></i> Claimed</span>
                                        <?php elseif ($ben['assistance_status'] === 'Not Eligible'): ?>
                                            <span class="badge badge-danger"><i class="fa fa-circle-xmark"></i> Not Eligible</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><i class="fa fa-clock"></i> Pending Review</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="index.php?route=beneficiary-status" method="POST" style="display: flex; gap: 4px; align-items: center; margin: 0;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="beneficiary_id" value="<?= (int)$ben['beneficiary_id'] ?>">
                                            <select name="assistance_status" class="form-control" style="font-size: 0.75rem; padding: 2px 6px; height: 28px; width: 110px;">
                                                <option value="Eligible" <?= $ben['assistance_status'] === 'Eligible' ? 'selected' : '' ?>>Eligible</option>
                                                <option value="Pending" <?= $ben['assistance_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Approved" <?= $ben['assistance_status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                                <option value="Ready to Claim" <?= $ben['assistance_status'] === 'Ready to Claim' ? 'selected' : '' ?>>Ready to Claim</option>
                                                <option value="Claimed" <?= $ben['assistance_status'] === 'Claimed' ? 'selected' : '' ?>>Claimed</option>
                                                <option value="Not Eligible" <?= $ben['assistance_status'] === 'Not Eligible' ? 'selected' : '' ?>>Not Eligible</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm" style="padding: 2px 8px; font-size: 0.75rem;" title="Update Status">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>

                                        <!-- Quick Contextual Actions -->
                                        <div style="margin-top: 4px; display: flex; gap: 4px;">
                                            <?php if ($ben['assistance_status'] === 'Pending'): ?>
                                                <form action="index.php?route=beneficiary-status" method="POST" style="margin: 0; display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="beneficiary_id" value="<?= (int)$ben['beneficiary_id'] ?>">
                                                    <input type="hidden" name="assistance_status" value="Approved">
                                                    <button type="submit" class="btn btn-sm btn-primary" style="font-size: 0.7rem; padding: 1px 6px;" title="Approve Claim">
                                                        <i class="fa fa-check-circle"></i> Approve
                                                    </button>
                                                </form>
                                            <?php elseif ($ben['assistance_status'] === 'Approved'): ?>
                                                <form action="index.php?route=beneficiary-status" method="POST" style="margin: 0; display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="beneficiary_id" value="<?= (int)$ben['beneficiary_id'] ?>">
                                                    <input type="hidden" name="assistance_status" value="Ready to Claim">
                                                    <button type="submit" class="btn btn-sm btn-outline" style="font-size: 0.7rem; padding: 1px 6px; color: #0f766e; border-color: #0f766e;" title="Mark Ready to Claim">
                                                        <i class="fa fa-boxes-packing"></i> Mark Ready
                                                    </button>
                                                </form>
                                            <?php elseif ($ben['assistance_status'] === 'Ready to Claim'): ?>
                                                <form action="index.php?route=beneficiary-distribute" method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Confirm release of relief package to resident?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="beneficiary_id" value="<?= (int)$ben['beneficiary_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success" style="font-size: 0.7rem; padding: 1px 6px;" title="Confirm Actual Distribution">
                                                        <i class="fa fa-hand-holding-box"></i> Distribute
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form action="index.php?route=beneficiary-schedule" method="POST" style="display: flex; gap: 4px; align-items: center; margin: 0;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="beneficiary_id" value="<?= (int)$ben['beneficiary_id'] ?>">
                                            <input type="date" name="claiming_date" class="form-control" style="font-size: 0.75rem; padding: 2px 4px; height: 28px; width: 125px;" value="<?= htmlspecialchars($ben['claiming_date'] ?? date('Y-m-d', strtotime('+3 days'))) ?>" required>
                                            <input type="hidden" name="claiming_time" value="<?= htmlspecialchars($ben['claiming_time'] ?? '08:00 AM - 12:00 PM') ?>">
                                            <input type="hidden" name="claiming_location" value="<?= htmlspecialchars($ben['claiming_location'] ?? 'Hinaplanon Covered Court, Station B') ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.75rem;" title="Update Claiming Date">
                                                <i class="fa fa-calendar-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 3. CHANGE / RESET PASSWORD MODAL DIALOG       -->
<!-- ============================================== -->
<div id="resetPasswordModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div class="modal-container" style="background: #ffffff; border-radius: 12px; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        <div style="background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%); padding: 18px 22px; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(255,255,255,0.2); width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa fa-key"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #ffffff;">Reset Resident Password</h3>
                    <p style="margin: 0; font-size: 0.78rem; opacity: 0.9;">Barangay Head Citizen Credential Management</p>
                </div>
            </div>
            <button type="button" onclick="closeResetPasswordModal()" style="background: none; border: none; color: #ffffff; font-size: 1.25rem; cursor: pointer; padding: 4px;">
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <form action="index.php?route=residents-change-password" method="POST" style="padding: 22px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="user_id" id="modal_user_id" value="">

            <div style="background: #f1f5f9; padding: 12px 14px; border-radius: 8px; margin-bottom: 18px; border: 1px solid #e2e8f0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">RESIDENT NAME</span>
                    <strong id="modal_resident_name" style="font-size: 0.9rem; color: #1e293b;">—</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">BARANGAY ID</span>
                    <code id="modal_resident_id" style="font-weight: 700; color: var(--primary-dark);">—</code>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; padding-top: 6px; border-top: 1px dashed #cbd5e1;">
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">CURRENT PASSWORD</span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span id="modal_current_pwd" style="font-family: monospace; font-size: 0.9rem; font-weight: 700; color: #0f172a; letter-spacing: 1px;">••••••••</span>
                        <button type="button" class="btn btn-outline btn-sm btn-icon" id="modal_toggle_cur_btn" onclick="toggleModalCurrentPassword()" title="See current password" style="padding: 2px 6px; font-size: 0.75rem;">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="font-weight: 700; color: #1e293b; font-size: 0.88rem;">
                    New Password *
                </label>
                <div style="position: relative;">
                    <input type="text" name="new_password" id="modal_new_password" class="form-control" required minlength="6" placeholder="Enter new password (min. 6 chars)" style="padding-right: 42px; font-family: monospace; font-size: 0.95rem; font-weight: 600;">
                    <button type="button" onclick="toggleNewPasswordVisibility()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; padding: 4px;">
                        <i id="new_pwd_eye_icon" class="fa fa-eye-slash"></i>
                    </button>
                </div>
                <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 4px;">
                    This password will be immediately visible on this screen and activated for the resident's login.
                </small>
            </div>

            <div style="display: flex; gap: 8px; margin-bottom: 20px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="generateRandomPassword()" style="font-size: 0.8rem;">
                    <i class="fa fa-wand-magic-sparkles"></i> Auto-Generate Suggestion
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setStandardDefaultPassword()" style="font-size: 0.8rem;">
                    <i class="fa fa-rotate-left"></i> Set Default (resident123)
                </button>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-light); padding-top: 16px;">
                <button type="button" class="btn btn-outline" onclick="closeResetPasswordModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check"></i> Save & Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Switch between tabs
function switchResidentTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline');
    });
    const targetBtn = document.getElementById('tab-btn-' + tabName);
    if (targetBtn) {
        targetBtn.classList.remove('btn-outline');
        targetBtn.classList.add('btn-primary');
    }

    document.getElementById('tab-content-pending').style.display = (tabName === 'pending') ? 'block' : 'none';
    document.getElementById('tab-content-approved').style.display = (tabName === 'approved') ? 'block' : 'none';
    document.getElementById('tab-content-all').style.display = (tabName === 'all') ? 'block' : 'none';
    if (document.getElementById('tab-content-relief')) {
        document.getElementById('tab-content-relief').style.display = (tabName === 'relief') ? 'block' : 'none';
    }
}

// Live search filter across resident tables
function filterResidentTables() {
    const q = document.getElementById('residentSearchInput').value.toLowerCase().trim();
    document.querySelectorAll('.resident-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (text.indexOf(q) !== -1) ? '' : 'none';
    });
}

// Inline toggle password display
function togglePasswordDisplay(elemId, actualPassword, btnElem) {
    const elem = document.getElementById(elemId);
    if (!elem) return;
    const icon = btnElem.querySelector('i');
    
    if (elem.dataset.revealed === 'true') {
        elem.textContent = '••••••••';
        elem.dataset.revealed = 'false';
        elem.style.letterSpacing = '2px';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    } else {
        elem.textContent = actualPassword;
        elem.dataset.revealed = 'true';
        elem.style.letterSpacing = 'normal';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
}

// Modal Password Management
let currentModalPlainPwd = '';

function openResetPasswordModal(residentData) {
    document.getElementById('modal_user_id').value = residentData.user_id;
    document.getElementById('modal_resident_name').textContent = residentData.name;
    document.getElementById('modal_resident_id').textContent = residentData.id_number;
    currentModalPlainPwd = residentData.current_password || 'resident123';
    
    const curPwdElem = document.getElementById('modal_current_pwd');
    curPwdElem.textContent = '••••••••';
    curPwdElem.dataset.revealed = 'false';
    const curBtn = document.getElementById('modal_toggle_cur_btn').querySelector('i');
    if (curBtn) {
        curBtn.className = 'fa fa-eye';
    }

    // Default suggested password
    document.getElementById('modal_new_password').value = '';
    document.getElementById('resetPasswordModal').style.display = 'flex';
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').style.display = 'none';
}

function toggleModalCurrentPassword() {
    const curPwdElem = document.getElementById('modal_current_pwd');
    const icon = document.getElementById('modal_toggle_cur_btn').querySelector('i');
    if (curPwdElem.dataset.revealed === 'true') {
        curPwdElem.textContent = '••••••••';
        curPwdElem.dataset.revealed = 'false';
        icon.className = 'fa fa-eye';
    } else {
        curPwdElem.textContent = currentModalPlainPwd;
        curPwdElem.dataset.revealed = 'true';
        icon.className = 'fa fa-eye-slash';
    }
}

function toggleNewPasswordVisibility() {
    const input = document.getElementById('modal_new_password');
    const icon = document.getElementById('new_pwd_eye_icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa fa-eye';
    }
}

function generateRandomPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789#@!';
    let pwd = 'Hinap#';
    for (let i = 0; i < 4; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('modal_new_password').value = pwd;
}

function setStandardDefaultPassword() {
    document.getElementById('modal_new_password').value = 'resident123';
}
</script>
