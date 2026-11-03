<!-- views/field_reports/index.php -->
<?php
use App\Core\View;
use App\Core\Auth;
$role = Auth::role();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark); margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-truck-medical text-primary"></i> 
            <?php if ($role === 'Responder'): ?>
                Responder Ground Field Reports & Tracking
            <?php elseif ($role === 'Barangay Head'): ?>
                Responder Field Reports — Barangay Head Verification Console
            <?php else: ?>
                ICDRRMO Citywide Ground Status & Disaster Monitoring
            <?php endif; ?>
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">
            <?php if ($role === 'Responder'): ?>
                Submit real-time situation reports and track validation status by the Barangay Head and ICDRRMO.
            <?php elseif ($role === 'Barangay Head'): ?>
                Review, verify, and validate field reports submitted by responder teams in Barangay Hinaplanon.
            <?php else: ?>
                Monitor multi-purok road accessibility, casualties, damages, and ground observations across all barangays.
            <?php endif; ?>
        </p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if ($role === 'Responder'): ?>
            <a href="index.php?route=field-reports-create" class="btn btn-primary">
                <i class="fa fa-paper-plane"></i> Submit Ground Field Report
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. KPI SUMMARY STAT CARDS                      -->
<!-- ============================================== -->
<div class="grid-cols-4" style="margin-bottom: 20px;">
    <div class="stat-card" style="border-left: 4px solid var(--primary);">
        <div class="stat-label">
            <i class="fa fa-clipboard-list text-primary"></i> 
            <?= ($role === 'Responder' && ($scopeFilter ?? '') === 'my') ? 'My Total Submissions' : 'Total Field Reports' ?>
        </div>
        <div class="stat-value"><?= (int)($role === 'Responder' && ($scopeFilter ?? '') === 'my' ? $myCounts['total'] : $counts['total']) ?></div>
        <div class="stat-sub">Ground situational logs recorded</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid var(--warning);">
        <div class="stat-label"><i class="fa fa-clock text-warning"></i> Pending Verification</div>
        <div class="stat-value" style="color: <?= ((int)$counts['submitted'] > 0) ? 'var(--warning)' : 'var(--text-muted)' ?>;">
            <?= (int)($role === 'Responder' && ($scopeFilter ?? '') === 'my' ? $myCounts['submitted'] : $counts['submitted']) ?>
        </div>
        <div class="stat-sub">Awaiting Barangay Head review</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid var(--success);">
        <div class="stat-label"><i class="fa fa-circle-check text-success"></i> Verified Reports</div>
        <div class="stat-value" style="color: var(--success);">
            <?= (int)($role === 'Responder' && ($scopeFilter ?? '') === 'my' ? $myCounts['verified'] : $counts['verified']) ?>
        </div>
        <div class="stat-sub">Confirmed ground observations</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid var(--danger);">
        <div class="stat-label"><i class="fa fa-triangle-exclamation text-danger"></i> Inundated / Blocked Roads</div>
        <div class="stat-value" style="color: var(--danger);"><?= (int)$counts['blocked_roads'] ?></div>
        <div class="stat-sub">Flooded or obstructed sectors</div>
    </div>
</div>

<!-- ============================================== -->
<!-- 2. FIELD REPORTS CONSOLE                       -->
<!-- ============================================== -->
<div class="card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #f8fafc; border-bottom: 1px solid var(--border-light);">
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <!-- Scope Switcher for Responders -->
            <?php if ($role === 'Responder'): ?>
                <div style="display: flex; border: 1px solid var(--border); border-radius: 6px; overflow: hidden; margin-right: 8px;">
                    <a href="index.php?route=field-reports&scope=my<?= !empty($statusFilter) ? '&status='.$statusFilter : '' ?>" class="btn btn-sm <?= ($scopeFilter ?? '') === 'my' ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 0; border: none;">
                        <i class="fa fa-user"></i> My Submissions (<?= (int)$myCounts['total'] ?>)
                    </a>
                    <a href="index.php?route=field-reports&scope=all<?= !empty($statusFilter) ? '&status='.$statusFilter : '' ?>" class="btn btn-sm <?= ($scopeFilter ?? '') === 'all' ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 0; border: none;">
                        <i class="fa fa-users"></i> All Reports (<?= (int)$counts['total'] ?>)
                    </a>
                </div>
            <?php endif; ?>

            <!-- Status Filter Pills -->
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <a href="index.php?route=field-reports<?= !empty($scopeFilter) ? '&scope='.$scopeFilter : '' ?>" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>">
                    All (<?= (int)($role === 'Responder' && ($scopeFilter ?? '') === 'my' ? $myCounts['total'] : $counts['total']) ?>)
                </a>
                <a href="index.php?route=field-reports&status=Submitted<?= !empty($scopeFilter) ? '&scope='.$scopeFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Submitted' ? 'btn-primary' : 'btn-outline' ?>">
                    <i class="fa fa-clock"></i> Pending (<?= (int)($role === 'Responder' && ($scopeFilter ?? '') === 'my' ? $myCounts['submitted'] : $counts['submitted']) ?>)
                </a>
                <a href="index.php?route=field-reports&status=Verified<?= !empty($scopeFilter) ? '&scope='.$scopeFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Verified' ? 'btn-primary' : 'btn-outline' ?>">
                    <i class="fa fa-check"></i> Verified (<?= (int)($role === 'Responder' && ($scopeFilter ?? '') === 'my' ? $myCounts['verified'] : $counts['verified']) ?>)
                </a>
                <a href="index.php?route=field-reports&status=Rejected<?= !empty($scopeFilter) ? '&scope='.$scopeFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Rejected' ? 'btn-primary' : 'btn-outline' ?>">
                    <i class="fa fa-xmark"></i> Rejected (<?= (int)($role === 'Responder' && ($scopeFilter ?? '') === 'my' ? $myCounts['rejected'] : $counts['rejected']) ?>)
                </a>
            </div>
        </div>

        <!-- Purok Filter Dropdown -->
        <div style="display: flex; gap: 8px; align-items: center;">
            <form method="GET" action="index.php" style="display: flex; gap: 6px; align-items: center; margin: 0;">
                <input type="hidden" name="route" value="field-reports">
                <?php if (!empty($statusFilter)): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                <?php endif; ?>
                <?php if (!empty($scopeFilter)): ?>
                    <input type="hidden" name="scope" value="<?= htmlspecialchars($scopeFilter) ?>">
                <?php endif; ?>
                <select name="purok_id" class="form-control" style="font-size: 0.85rem; padding: 4px 10px; height: 32px;" onchange="this.form.submit()">
                    <option value="">-- All Hinaplanon Puroks --</option>
                    <?php foreach ($puroks as $p): ?>
                        <option value="<?= $p['purok_id'] ?>" <?= ((string)$purokFilter === (string)$p['purok_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['purok_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="card-body">
        <?php if (empty($reports)): ?>
            <div class="alert alert-info" style="display: flex; align-items: center; gap: 12px; padding: 20px;">
                <i class="fa fa-circle-info" style="font-size: 1.4rem; color: var(--info);"></i>
                <div>
                    <strong>No field reports found matching your criteria.</strong>
                    <div style="font-size: 0.85rem; margin-top: 2px;">
                        <?php if ($role === 'Responder'): ?>
                            You have no submitted field reports recorded under this filter. Click "Submit Ground Field Report" to post real-time updates.
                        <?php else: ?>
                            New submissions from responder teams will appear here automatically in real-time.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Timestamp</th>
                            <th>Purok & Risk</th>
                            <th>Responder Personnel</th>
                            <th>Road Accessibility</th>
                            <th>Damages / Casualties</th>
                            <th>Review Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $rep): ?>
                            <?php 
                            $status = $rep['status'] ?? 'Submitted';
                            ?>
                            <tr>
                                <td>
                                    <strong style="font-family: monospace; font-size: 0.95rem; color: var(--primary-dark);">
                                        FLD-<?= str_pad((string)$rep['report_id'], 3, '0', STR_PAD_LEFT) ?>
                                    </strong>
                                </td>
                                <td>
                                    <div><?= date('M d, Y', strtotime($rep['report_time'])) ?></div>
                                    <small style="color: var(--text-muted);"><?= date('h:i A', strtotime($rep['report_time'])) ?></small>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($rep['purok_name']) ?></strong></div>
                                    <?= View::riskBadge($rep['purok_risk'] ?? 'Pending Verification') ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($rep['responder_name']) ?></strong>
                                    <?php if (!empty($rep['responder_badge'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">Badge: <?= htmlspecialchars($rep['responder_badge']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($rep['road_accessibility'] === 'Passable'): ?>
                                        <span class="badge badge-success"><i class="fa fa-car"></i> Passable</span>
                                    <?php elseif ($rep['road_accessibility'] === 'Passable to Heavy Vehicles Only'): ?>
                                        <span class="badge badge-warning"><i class="fa fa-truck"></i> Heavy 6x6 Only</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fa fa-ban"></i> <?= htmlspecialchars($rep['road_accessibility']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">
                                        Houses: <strong><?= (int)$rep['damaged_houses'] ?></strong> | Casualties: <strong><?= (int)$rep['casualties_count'] ?></strong>
                                    </div>
                                    <?php if (!empty($rep['urgent_needs'])): ?>
                                        <small style="color: var(--danger); font-weight: 600; display: block; margin-top: 2px;">
                                            <i class="fa fa-kit-medical"></i> <?= htmlspecialchars($rep['urgent_needs']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status === 'Verified'): ?>
                                        <span class="badge badge-success"><i class="fa fa-circle-check"></i> Verified</span>
                                        <?php if (!empty($rep['reviewer_first'])): ?>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                By: <?= htmlspecialchars($rep['reviewer_first'] . ' ' . $rep['reviewer_last']) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($status === 'Rejected'): ?>
                                        <span class="badge badge-danger"><i class="fa fa-circle-xmark"></i> Rejected</span>
                                        <?php if (!empty($rep['review_notes'])): ?>
                                            <div style="font-size: 0.72rem; color: var(--danger); margin-top: 2px;" title="<?= htmlspecialchars($rep['review_notes']) ?>">
                                                <?= htmlspecialchars(mb_strimwidth($rep['review_notes'], 0, 24, '...')) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><i class="fa fa-clock"></i> Pending Review</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                        <a href="index.php?route=field-reports-show&id=<?= $rep['report_id'] ?>" class="btn btn-outline btn-sm" title="View Full Report Details">
                                            <i class="fa fa-eye"></i> Details
                                        </a>

                                        <!-- Barangay Head Quick Verification Buttons -->
                                        <?php if ($role === 'Barangay Head' && $status === 'Submitted'): ?>
                                            <form action="index.php?route=field-reports-verify" method="POST" style="display: inline;" onsubmit="return confirm('Verify and confirm this field report?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                                <input type="hidden" name="report_id" value="<?= (int)$rep['report_id'] ?>">
                                                <input type="hidden" name="review_notes" value="Ground report confirmed and verified by official.">
                                                <button type="submit" class="btn btn-success btn-sm" title="Verify Report">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>

                                            <button type="button" class="btn btn-danger btn-sm" title="Reject Report" onclick="openRejectModal(<?= (int)$rep['report_id'] ?>, '<?= htmlspecialchars($rep['purok_name'], ENT_QUOTES) ?>')">
                                                <i class="fa fa-xmark"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================== -->
<!-- 3. REJECTION REASON MODAL DIALOG               -->
<!-- ============================================== -->
<div id="rejectReportModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.65); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="background: #be123c; padding: 16px 20px; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa fa-triangle-exclamation" style="font-size: 1.2rem;"></i>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #ffffff;">Reject Field Report</h3>
            </div>
            <button type="button" onclick="closeRejectModal()" style="background: none; border: none; color: #ffffff; font-size: 1.25rem; cursor: pointer;">
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <form action="index.php?route=field-reports-reject" method="POST" style="padding: 20px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="report_id" id="modal_reject_report_id" value="">

            <p style="font-size: 0.88rem; color: #475569; margin-top: 0;">
                You are rejecting field report <strong id="modal_reject_purok"></strong>. Please provide a justification or feedback so the responder can revise their submission:
            </p>

            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="font-weight: 700;">Rejection Reason / Clarification Required *</label>
                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="e.g. Ground coordinates do not match reported purok, or water level marks contradicted by resident hotline reports..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fa fa-xmark"></i> Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(reportId, purokName) {
    document.getElementById('modal_reject_report_id').value = reportId;
    document.getElementById('modal_reject_purok').textContent = 'FLD-' + String(reportId).padStart(3, '0') + ' (' + purokName + ')';
    document.getElementById('rejectReportModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectReportModal').style.display = 'none';
}
</script>
