<!-- views/field_reports/show.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-dark);">Field Report #<?= $report['report_id'] ?> — <?= htmlspecialchars($report['purok_name']) ?></h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Submitted by <?= htmlspecialchars($report['responder_name']) ?> on <?= date('F d, Y h:i A', strtotime($report['report_time'])) ?></p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=field-reports" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back to Reports</a>
    </div>
</div>

<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-tower-broadcast"></i> Field Observations & Ground Conditions</div>
        <span class="badge badge-<?= $report['road_accessibility'] === 'Passable' ? 'success' : 'danger' ?>">
            Roads: <?= htmlspecialchars($report['road_accessibility']) ?>
        </span>
    </div>
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; margin-bottom: 16px; padding: 12px; background: #f8fafc; border-radius: 6px;">
            <div>
                <strong>Location:</strong> <?= htmlspecialchars($report['purok_name']) ?>
                <div style="margin-top: 4px;"><?= View::riskBadge($report['purok_risk'] ?? 'Pending Verification') ?></div>
            </div>
            <div>
                <strong>Damaged Houses:</strong> <?= (int)$report['damaged_houses'] ?><br>
                <strong>Casualties / Injured:</strong> <?= (int)$report['casualties_count'] ?>
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label class="form-label">Responder Narrative Update:</label>
            <div style="padding: 14px; background: #ffffff; border: 1px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
                <?= nl2br(htmlspecialchars($report['status_update'])) ?>
            </div>
        </div>

        <?php if (!empty($report['urgent_needs'])): ?>
            <div style="margin-bottom: 16px; padding: 12px; background: #fee2e2; border-left: 4px solid var(--danger); border-radius: 0 6px 6px 0;">
                <strong style="color: var(--danger);"><i class="fa fa-triangle-exclamation"></i> Immediate Urgent Needs:</strong>
                <div style="font-weight: 600; margin-top: 4px; color: #991b1b;"><?= htmlspecialchars($report['urgent_needs']) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($report['disaster_request_id'])): ?>
            <div style="padding-top: 14px; border-top: 1px dashed var(--border); display: flex; justify-content: space-between; align-items: center;">
                <span>Linked Incident Request: <strong>#<?= $report['disaster_request_id'] ?></strong></span>
                <a href="index.php?route=disaster-requests-show&id=<?= $report['disaster_request_id'] ?>" class="btn btn-outline btn-sm">
                    View Linked Request
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
