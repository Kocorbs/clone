<!-- views/preparedness/calendar.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-dark);">Barangay Hinaplanon Community Activity Calendar</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Upcoming disaster response drills, evacuation exercises, and public orientations.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=preparedness" class="btn btn-outline"><i class="fa fa-list"></i> Table View</a>
        <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
            <a href="index.php?route=preparedness-create" class="btn btn-primary"><i class="fa fa-plus"></i> Schedule Activity</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid-cols-2">
    <?php foreach ($activities as $act): ?>
        <div class="card" style="border-top: 4px solid var(--primary-light);">
            <div class="card-header">
                <div>
                    <span class="badge badge-info"><?= htmlspecialchars($act['activity_type']) ?></span>
                    <strong style="margin-left: 8px;"><?= htmlspecialchars($act['title']) ?></strong>
                </div>
                <span class="badge badge-<?= $act['status'] === 'Completed' ? 'success' : 'warning' ?>">
                    <?= htmlspecialchars($act['status']) ?>
                </span>
            </div>
            <div class="card-body">
                <p style="font-size: 0.88rem; margin-bottom: 12px; color: var(--text-main);">
                    <?= nl2br(htmlspecialchars($act['description'])) ?>
                </p>
                <div style="font-size: 0.82rem; color: var(--text-muted); display: grid; grid-template-columns: 1fr; gap: 6px;">
                    <div><i class="fa fa-calendar-day text-primary"></i> <strong>Date & Time:</strong> <?= date('F d, Y — h:i A', strtotime($act['schedule_date'])) ?></div>
                    <div><i class="fa fa-location-dot text-danger"></i> <strong>Venue / Location:</strong> <?= htmlspecialchars($act['location']) ?></div>
                    <div><i class="fa fa-bullseye text-warning"></i> <strong>Target Area:</strong> <?= htmlspecialchars($act['purok_name'] ?? 'All Hinaplanon Puroks') ?></div>
                    <div><i class="fa fa-users text-success"></i> <strong>Target Attendees:</strong> <?= (int)$act['max_participants'] ?> participants</div>
                </div>

                <?php if ($act['status'] === 'Completed' && !empty($act['evaluation_id'])): ?>
                    <div style="margin-top: 14px; padding: 10px; background: #f0fdf4; border-radius: 6px; font-size: 0.8rem; border-left: 3px solid var(--success);">
                        <strong>Post-Activity Assessment:</strong> <?= $act['actual_turnout'] ?> attended | Rating: <?= $act['objectives_met_rating'] ?>/5 Stars
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
