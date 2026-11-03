<!-- views/preparedness/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-dark);">Disaster Preparedness Activities</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Evacuation drills, community seminars, first-aid training, and awareness campaigns.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=preparedness-calendar" class="btn btn-outline"><i class="fa fa-calendar-days"></i> Calendar View</a>
        <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
            <a href="index.php?route=preparedness-create" class="btn btn-primary"><i class="fa fa-calendar-plus"></i> Schedule Activity</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-list-check"></i> Preparedness Activities Schedule & Evaluations</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Activity Type</th>
                    <th>Title & Description</th>
                    <th>Target Purok</th>
                    <th>Schedule Date & Time</th>
                    <th>Location / Venue</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No preparedness activities recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($activities as $act): ?>
                        <tr>
                            <td><span class="badge badge-info"><?= htmlspecialchars($act['activity_type']) ?></span></td>
                            <td>
                                <strong><?= htmlspecialchars($act['title']) ?></strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                    <?= htmlspecialchars(substr($act['description'], 0, 90)) ?>...
                                </div>
                            </td>
                            <td><?= htmlspecialchars($act['purok_name'] ?? 'Barangay-wide') ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($act['schedule_date'])) ?></td>
                            <td><?= htmlspecialchars($act['location']) ?></td>
                            <td><?= View::statusBadge($act['status']) ?></td>
                            <td>
                                <?php if ($act['status'] === 'Completed' && !empty($act['evaluation_id'])): ?>
                                    <span class="badge badge-success"><i class="fa fa-star"></i> Evaluated (<?= $act['objectives_met_rating'] ?>/5)</span>
                                <?php elseif (in_array($currentRole, ['Barangay Head', 'ICDRRMO', 'Responder'])): ?>
                                    <a href="index.php?route=preparedness-evaluate&id=<?= $act['preparedness_activities_id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fa fa-clipboard-check"></i> Evaluate
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">Scheduled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
