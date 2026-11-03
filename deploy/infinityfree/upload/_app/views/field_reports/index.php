<!-- views/field_reports/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-dark);">Responder Ground Field Reports</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Verified situational reports, road accessibility, and urgent resource requirements from the field.</p>
    </div>
    <?php if (in_array($currentRole, ['Responder', 'ICDRRMO', 'Barangay Head'])): ?>
        <a href="index.php?route=field-reports-create" class="btn btn-primary">
            <i class="fa fa-paper-plane"></i> Submit Field Status Update
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-truck-medical"></i> Verified Incident Updates</div>
    </div>
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
                    <th>Urgent Needs</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">No field reports recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($reports as $rep): ?>
                        <tr>
                            <td><code>FLD-<?= str_pad((string)$rep['report_id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                            <td><?= date('M d, h:i A', strtotime($rep['report_time'])) ?></td>
                            <td>
                                <div><strong><?= htmlspecialchars($rep['purok_name']) ?></strong></div>
                                <?= View::riskBadge($rep['purok_risk'] ?? 'Pending Verification') ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($rep['responder_name']) ?></strong>
                                <?php if (!empty($rep['responder_badge'])): ?>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($rep['responder_badge']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $rep['road_accessibility'] === 'Passable' ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars($rep['road_accessibility']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">Houses: <?= (int)$rep['damaged_houses'] ?> | Casualties: <?= (int)$rep['casualties_count'] ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; color: #b91c1c; font-weight: 600;">
                                    <?= htmlspecialchars($rep['urgent_needs'] ?? 'None specified') ?>
                                </div>
                            </td>
                            <td>
                                <a href="index.php?route=field-reports-show&id=<?= $rep['report_id'] ?>" class="btn btn-outline btn-sm">
                                    <i class="fa fa-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
