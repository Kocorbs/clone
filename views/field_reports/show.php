<!-- views/field_reports/show.php -->
<?php 
use App\Core\View;
use App\Core\Auth;
$role = Auth::role();
$status = $report['status'] ?? 'Submitted';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-tower-broadcast text-primary"></i> 
            Field Report #<?= $report['report_id'] ?> — <?= htmlspecialchars($report['purok_name']) ?>
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">
            Submitted by <?= htmlspecialchars($report['responder_name']) ?> on <?= date('F d, Y h:i A', strtotime($report['report_time'])) ?>
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=field-reports" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back to Reports</a>
    </div>
</div>

<div class="card" style="max-width: 780px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); border-top: 4px solid var(--primary);">
    <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="card-title"><i class="fa fa-road"></i> Ground Status & Verification Review</div>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <span class="badge badge-<?= $report['road_accessibility'] === 'Passable' ? 'success' : 'danger' ?>" style="font-size: 0.82rem;">
                <i class="fa fa-car"></i> <?= htmlspecialchars($report['road_accessibility']) ?>
            </span>
            <?php if ($status === 'Verified'): ?>
                <span class="badge badge-success" style="font-size: 0.82rem;"><i class="fa fa-circle-check"></i> Verified</span>
            <?php elseif ($status === 'Rejected'): ?>
                <span class="badge badge-danger" style="font-size: 0.82rem;"><i class="fa fa-circle-xmark"></i> Rejected</span>
            <?php else: ?>
                <span class="badge badge-warning" style="font-size: 0.82rem;"><i class="fa fa-clock"></i> Pending Review</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">
        <!-- Review Status Banner -->
        <?php if ($status === 'Verified'): ?>
            <div class="alert alert-success" style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 18px; margin-bottom: 20px; border-radius: 8px;">
                <i class="fa fa-circle-check" style="font-size: 1.4rem; color: var(--success); margin-top: 2px;"></i>
                <div>
                    <strong style="color: #065f46; font-size: 0.95rem;">Field Report Officially Verified</strong>
                    <div style="font-size: 0.85rem; color: #047857; margin-top: 2px;">
                        Verified by <strong><?= htmlspecialchars(($report['reviewer_first'] ?? 'Barangay Official') . ' ' . ($report['reviewer_last'] ?? '')) ?></strong> (<?= htmlspecialchars($report['reviewer_role'] ?? 'Barangay Head') ?>) on <?= !empty($report['reviewed_at']) ? date('M d, Y h:i A', strtotime($report['reviewed_at'])) : 'Verified' ?>.
                    </div>
                    <?php if (!empty($report['review_notes'])): ?>
                        <div style="font-size: 0.85rem; background: #ecfdf5; border-left: 3px solid #10b981; padding: 6px 10px; margin-top: 8px; color: #064e3b;">
                            <strong>Verification Notes:</strong> <?= htmlspecialchars($report['review_notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($status === 'Rejected'): ?>
            <div class="alert alert-danger" style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 18px; margin-bottom: 20px; border-radius: 8px;">
                <i class="fa fa-circle-xmark" style="font-size: 1.4rem; color: var(--danger); margin-top: 2px;"></i>
                <div>
                    <strong style="color: #991b1b; font-size: 0.95rem;">Field Report Needs Revision / Rejected</strong>
                    <div style="font-size: 0.85rem; color: #b91c1c; margin-top: 2px;">
                        Reviewed by <strong><?= htmlspecialchars(($report['reviewer_first'] ?? 'Barangay Official') . ' ' . ($report['reviewer_last'] ?? '')) ?></strong> (<?= htmlspecialchars($report['reviewer_role'] ?? 'Barangay Head') ?>) on <?= !empty($report['reviewed_at']) ? date('M d, Y h:i A', strtotime($report['reviewed_at'])) : 'Reviewed' ?>.
                    </div>
                    <div style="font-size: 0.85rem; background: #fef2f2; border-left: 3px solid #ef4444; padding: 8px 12px; margin-top: 8px; color: #7f1d1d; border-radius: 4px;">
                        <strong>Rejection Reason / Justification:</strong> <?= htmlspecialchars(!empty($report['review_notes']) ? $report['review_notes'] : 'No specific rejection reason provided.') ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; margin-bottom: 20px; border-radius: 8px;">
                <i class="fa fa-clock" style="font-size: 1.3rem; color: #b45309;"></i>
                <div style="font-size: 0.88rem; color: #92400e;">
                    <strong>Pending Review:</strong> This field report has been routed to the Barangay Head and ICDRRMO. It will remain in pending status until reviewed.
                </div>
            </div>
        <?php endif; ?>

        <!-- Location & Damage Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px; padding: 14px; background: #f1f5f9; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div>
                <span style="font-size: 0.78rem; color: #64748b; font-weight: 700;">PUROK LOCATION & RISK</span>
                <div style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-top: 2px;">
                    <?= htmlspecialchars($report['purok_name']) ?>
                </div>
                <div style="margin-top: 4px;"><?= View::riskBadge($report['purok_risk'] ?? 'Pending Verification') ?></div>
            </div>
            <div>
                <span style="font-size: 0.78rem; color: #64748b; font-weight: 700;">GROUND IMPACT & ROAD STATUS</span>
                <div style="font-size: 0.88rem; color: #334155; margin-top: 4px;">
                    Road Accessibility: <strong><?= htmlspecialchars($report['road_accessibility']) ?></strong><br>
                    Damaged / Inundated Houses: <strong><?= (int)$report['damaged_houses'] ?></strong><br>
                    Casualties / Injured Persons: <strong><?= (int)$report['casualties_count'] ?></strong>
                </div>
            </div>
        </div>

        <!-- Responder Information -->
        <div style="margin-bottom: 18px; padding: 12px 14px; background: #ffffff; border: 1px solid var(--border-light); border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div>
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 700;">REPORTING RESPONDER DETAILS</span>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                        <i class="fa fa-user-shield text-primary"></i> <?= htmlspecialchars($report['responder_name']) ?>
                    </div>
                </div>
                <div style="text-align: right; font-size: 0.82rem; color: #475569;">
                    Badge / ID: <code style="font-weight: 700; color: var(--primary-dark);"><?= !empty($report['responder_badge']) ? htmlspecialchars($report['responder_badge']) : 'N/A' ?></code><br>
                    Contact: <strong><?= !empty($report['resp_phone']) ? htmlspecialchars($report['resp_phone']) : 'N/A' ?></strong>
                </div>
            </div>
        </div>

        <!-- Narrative Update -->
        <div style="margin-bottom: 18px;">
            <label class="form-label" style="font-weight: 700; color: #1e293b;">Detailed Ground Narrative Update:</label>
            <div style="padding: 16px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1.6; color: #1e293b;">
                <?= nl2br(htmlspecialchars($report['status_update'])) ?>
            </div>
        </div>

        <!-- Urgent Needs / Required Supplies -->
        <div style="margin-bottom: 18px; padding: 14px; background: <?= !empty($report['urgent_needs']) ? '#fee2e2' : '#f8fafc' ?>; border-left: 4px solid <?= !empty($report['urgent_needs']) ? 'var(--danger)' : '#94a3b8' ?>; border-radius: 0 8px 8px 0; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
            <strong style="color: <?= !empty($report['urgent_needs']) ? 'var(--danger)' : '#475569' ?>; display: flex; align-items: center; gap: 6px;">
                <i class="fa fa-kit-medical"></i> Required Supplies & Urgent Logistics Needs:
            </strong>
            <div style="font-weight: <?= !empty($report['urgent_needs']) ? '700' : '500' ?>; margin-top: 4px; color: <?= !empty($report['urgent_needs']) ? '#991b1b' : '#64748b' ?>; font-size: 0.95rem;">
                <?= !empty($report['urgent_needs']) ? htmlspecialchars($report['urgent_needs']) : 'None reported (Standard resources sufficient).' ?>
            </div>
        </div>

        <!-- GPS Coordinates if present -->
        <?php if (!empty($report['latitude']) && !empty($report['longitude'])): ?>
            <div style="margin-bottom: 18px; font-size: 0.85rem; color: #475569; padding: 10px; background: #f8fafc; border-radius: 6px;">
                <i class="fa fa-location-crosshairs text-primary"></i> Georeferenced Coordinates: 
                <strong><?= htmlspecialchars($report['latitude']) ?>, <?= htmlspecialchars($report['longitude']) ?></strong>
            </div>
        <?php endif; ?>

        <!-- Linked Incident Request -->
        <?php if (!empty($report['disaster_request_id'])): ?>
            <div style="padding: 12px 14px; background: #f8fafc; border: 1px solid var(--border-light); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <div>
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 700;">LINKED INCIDENT</span>
                    <div style="font-size: 0.9rem; font-weight: 700; color: var(--primary-dark);">
                        Disaster Request #<?= $report['disaster_request_id'] ?> (<?= htmlspecialchars($report['disaster_type'] ?? 'Disaster') ?>)
                    </div>
                </div>
                <a href="index.php?route=disaster-requests-show&id=<?= $report['disaster_request_id'] ?>" class="btn btn-outline btn-sm">
                    <i class="fa fa-arrow-up-right-from-square"></i> View Linked Request
                </a>
            </div>
        <?php endif; ?>

        <!-- ============================================== -->
        <!-- 4. OFFICIAL REVIEW & VERIFICATION ACTION FORM  -->
        <!-- ============================================== -->
        <?php if ($role === 'Barangay Head'): ?>
            <div style="margin-top: 24px; padding-top: 20px; border-top: 2px dashed #cbd5e1;">
                <h4 style="font-size: 1rem; font-weight: 800; color: var(--primary-dark); margin: 0 0 12px 0;">
                    <i class="fa fa-user-shield"></i> Barangay Head Verification & Review Console
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <!-- Verify Form -->
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 16px; border-radius: 8px;">
                        <h5 style="margin: 0 0 8px 0; color: #166534; font-weight: 700; font-size: 0.9rem;">
                            <i class="fa fa-check-circle"></i> Verify Report
                        </h5>
                        <p style="font-size: 0.8rem; color: #15803d; margin: 0 0 10px 0;">
                            Validate that this ground report is verified and can be used for citywide allocation.
                        </p>
                        <form action="index.php?route=field-reports-verify" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                            <input type="hidden" name="report_id" value="<?= (int)$report['report_id'] ?>">
                            <div class="form-group" style="margin-bottom: 10px;">
                                <input type="text" name="review_notes" class="form-control" style="font-size: 0.82rem;" placeholder="Verification remarks (optional)" value="<?= $status === 'Verified' ? htmlspecialchars($report['review_notes'] ?? '') : 'Ground report confirmed and verified by official.' ?>">
                            </div>
                            <button type="submit" class="btn btn-success btn-sm" style="width: 100%; justify-content: center;">
                                <i class="fa fa-check"></i> <?= $status === 'Verified' ? 'Update Verification' : 'Verify Ground Report' ?>
                            </button>
                        </form>
                    </div>

                    <!-- Reject Form -->
                    <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 16px; border-radius: 8px;">
                        <h5 style="margin: 0 0 8px 0; color: #991b1b; font-weight: 700; font-size: 0.9rem;">
                            <i class="fa fa-xmark-circle"></i> Reject / Request Revision
                        </h5>
                        <p style="font-size: 0.8rem; color: #b91c1c; margin: 0 0 10px 0;">
                            Flag this report for inaccuracy or return it to the responder with feedback.
                        </p>
                        <form action="index.php?route=field-reports-reject" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                            <input type="hidden" name="report_id" value="<?= (int)$report['report_id'] ?>">
                            <div class="form-group" style="margin-bottom: 10px;">
                                <input type="text" name="rejection_reason" class="form-control" style="font-size: 0.82rem;" required placeholder="Reason for rejection *" value="<?= $status === 'Rejected' ? htmlspecialchars($report['review_notes'] ?? '') : '' ?>">
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; justify-content: center;">
                                <i class="fa fa-xmark"></i> Reject Field Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php elseif ($role === 'ICDRRMO'): ?>
            <div style="margin-top: 24px; padding-top: 20px; border-top: 2px dashed #cbd5e1;">
                <div class="alert alert-info" style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 0.88rem; border-radius: 8px;">
                    <i class="fa fa-eye" style="font-size: 1.25rem; color: var(--info);"></i>
                    <div>
                        <strong>ICDRRMO Citywide Read-Only Monitoring:</strong> Operational verification and rejection authority is assigned to the Barangay Head. ICDRRMO monitors ground situation updates across all barangays in real time.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
