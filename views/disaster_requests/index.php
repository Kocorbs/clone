<!-- views/disaster_requests/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-dark);">Disaster Incident & Assistance Request Records</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Barangay Hinaplanon incident request records linked to Decision Tree prioritization.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=disaster-requests-create" class="btn btn-primary">
            <i class="fa fa-file-circle-plus"></i> Fill Up Disaster Request Form
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 10px;">
        <div class="card-title"><i class="fa fa-list-check"></i> Recorded Incident Requests</div>
        <div style="display: flex; gap: 8px;">
            <a href="index.php?route=disaster-requests" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>">All</a>
            <a href="index.php?route=disaster-requests&status=Pending" class="btn btn-sm <?= $statusFilter === 'Pending' ? 'btn-primary' : 'btn-outline' ?>">Pending</a>
            <a href="index.php?route=disaster-requests&status=Recommended" class="btn btn-sm <?= $statusFilter === 'Recommended' ? 'btn-primary' : 'btn-outline' ?>">Recommended</a>
            <a href="index.php?route=disaster-requests&status=Approved" class="btn btn-sm <?= $statusFilter === 'Approved' ? 'btn-primary' : 'btn-outline' ?>">Approved</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Req #</th>
                    <th>Date</th>
                    <th>Purok & Hazard Level</th>
                    <th>Disaster / Weather</th>
                    <th>Severity & Urgency</th>
                    <th>Affected Pop.</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">No disaster requests matching the criteria.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><strong>#<?= $req['disaster_request_id'] ?></strong></td>
                            <td><?= date('M d, Y', strtotime($req['disaster_date'])) ?></td>
                            <td>
                                <div><strong><?= htmlspecialchars($req['purok_name']) ?></strong></div>
                                <?= View::riskBadge($req['purok_risk'] ?? 'Pending Verification') ?>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($req['disaster_type']) ?></div>
                                <small style="color: var(--text-muted);"><?= htmlspecialchars($req['weather_name']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?= htmlspecialchars($req['severity_level']) ?></span>
                                <?= $req['is_urgent'] ? '<span class="badge badge-urgent"><i class="fa fa-bolt"></i> Urgent</span>' : '' ?>
                            </td>
                            <td>
                                <div><?= (int)$req['affected_households'] ?> HH</div>
                                <small style="color: var(--text-muted);"><?= (int)$req['affected_population'] ?> persons</small>
                            </td>
                            <td><?= View::statusBadge($req['status']) ?></td>
                            <td>
                                <a href="index.php?route=disaster-requests-show&id=<?= $req['disaster_request_id'] ?>" class="btn btn-outline btn-sm">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
