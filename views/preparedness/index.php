<!-- views/preparedness/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
            <?php if ($currentRole === 'ICDRRMO'): ?>
                Event Logs & Activities Ledger
            <?php elseif ($currentRole === 'Barangay Head'): ?>
                Event Lists & Activities Ledger
            <?php else: ?>
                Event History & Activities Ledger
            <?php endif; ?>
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Community drills, simulation readiness exercises, volunteer assignments, and past event evaluations.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=preparedness-calendar" class="btn btn-primary"><i class="fa fa-calendar-days"></i> Interactive Calendar View</a>
        <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
            <a href="index.php?route=preparedness-create" class="btn btn-outline"><i class="fa fa-calendar-plus"></i> Schedule Activity</a>
        <?php endif; ?>
    </div>
</div>

<!-- Calendar Highlights Overview -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-label"><i class="fa fa-calendar-days text-primary"></i> Total Drills Scheduled</div>
        <div class="stat-value"><?= (int)($summary['total'] ?? count($activities)) ?></div>
        <div class="stat-sub">Overall community activities</div>
    </div>

    <div class="stat-card">
        <div class="stat-label"><i class="fa fa-clock text-warning"></i> Upcoming Exercises</div>
        <div class="stat-value" style="color: #b45309;"><?= count($upcoming) ?></div>
        <div class="stat-sub">Active drills awaiting deployment</div>
    </div>

    <div class="stat-card">
        <div class="stat-label"><i class="fa fa-check-double text-success"></i> Past & Evaluated</div>
        <div class="stat-value" style="color: #047857;"><?= count($past) ?></div>
        <div class="stat-sub">Completed readiness events</div>
    </div>

    <div class="stat-card">
        <div class="stat-label"><i class="fa fa-users text-info"></i> Recorded Turnout</div>
        <div class="stat-value"><?= number_format((int)($summary['total_turnout'] ?? 0)) ?></div>
        <div class="stat-sub">Total citizens & volunteers engaged</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 10px;">
        <div class="card-title">
            <i class="fa fa-list-check"></i> 
            <?php if ($currentRole === 'ICDRRMO'): ?>
                Event Logs & Personnel Assignment Ledger
            <?php elseif ($currentRole === 'Barangay Head'): ?>
                Event Lists & Personnel Assignment Ledger
            <?php else: ?>
                Event History & Personnel Assignment Ledger
            <?php endif; ?>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="index.php?route=preparedness" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>">All Activities</a>
            <a href="index.php?route=preparedness&status=Scheduled" class="btn btn-sm <?= $statusFilter === 'Scheduled' ? 'btn-primary' : 'btn-outline' ?>">Upcoming Drills</a>
            <a href="index.php?route=preparedness&status=Completed" class="btn btn-sm <?= $statusFilter === 'Completed' ? 'btn-primary' : 'btn-outline' ?>">Past Events</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Type & ID</th>
                    <th>Activity Title & Description</th>
                    <th>Target Area</th>
                    <th>Schedule Date & Time</th>
                    <th>Assigned Personnel & Volunteers</th>
                    <th>Venue Location</th>
                    <th>Status / Evaluation</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">No preparedness activities recorded matching the filter.</td></tr>
                <?php else: ?>
                    <?php foreach ($activities as $act): ?>
                        <?php 
                            $isPast = ($act['status'] === 'Completed' || strtotime($act['schedule_date']) < time());
                        ?>
                        <tr>
                            <td>
                                <div><span class="badge badge-info"><?= htmlspecialchars($act['activity_type']) ?></span></div>
                                <small style="color: var(--text-muted); font-weight: 600;">#<?= $act['preparedness_activities_id'] ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($act['title']) ?></strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                    <?= htmlspecialchars(substr($act['description'], 0, 85)) ?>...
                                </div>
                            </td>
                            <td><?= htmlspecialchars($act['purok_name'] ?? 'Barangay-wide') ?></td>
                            <td>
                                <div><strong><?= date('M d, Y', strtotime($act['schedule_date'])) ?></strong></div>
                                <small style="color: var(--text-muted);"><?= date('h:i A', strtotime($act['schedule_date'])) ?></small>
                                <?php if ($isPast): ?>
                                    <div><span class="badge badge-success" style="font-size: 0.68rem;">Past Event</span></div>
                                <?php else: ?>
                                    <div><span class="badge badge-warning" style="font-size: 0.68rem;">Upcoming</span></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 0.8rem; margin-bottom: 3px;">
                                    <i class="fa fa-user-shield text-primary" title="Assigned Personnel"></i> 
                                    <strong><?= htmlspecialchars($act['assigned_personnel'] ?: 'BDRRMC Officer In-Charge') ?></strong>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-secondary);">
                                    <i class="fa fa-hand-holding-heart text-success" title="Volunteer Person/Team"></i> 
                                    <?= htmlspecialchars($act['volunteer_person'] ?: 'Community Volunteers') ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($act['location']) ?></td>
                            <td>
                                <div><?= View::statusBadge($act['status']) ?></div>
                                <?php if ($act['status'] === 'Completed' && !empty($act['evaluation_id'])): ?>
                                    <div style="font-size: 0.75rem; color: #166534; margin-top: 4px; font-weight: 600;">
                                        <i class="fa fa-star text-warning"></i> <?= (int)$act['objectives_met_rating'] ?>/5 Stars (<?= (int)$act['actual_turnout'] ?> Turnout)
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($act['status'] === 'Completed' && !empty($act['evaluation_id'])): ?>
                                    <a href="index.php?route=preparedness-calendar#activity-card-<?= $act['preparedness_activities_id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fa fa-eye"></i> View Report
                                    </a>
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
