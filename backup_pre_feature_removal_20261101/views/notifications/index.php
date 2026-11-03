<!-- views/notifications/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                Notifications & Emergency Alerts Center
            </h2>
            <?php if ($unreadCount > 0): ?>
                <span class="badge badge-danger" style="font-size: 0.8rem;">
                    <?= (int)$unreadCount ?> Unread
                </span>
            <?php endif; ?>
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Real-time emergency broadcasts, incident updates, resource allocations, and account notifications.
        </p>
    </div>

    <div style="display: flex; gap: 8px;">
        <?php if ($unreadCount > 0): ?>
            <form action="index.php?route=notifications-mark-all" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <button type="submit" class="btn btn-outline">
                    <i class="fa fa-envelope-open-text"></i> Mark All as Read
                </button>
            </form>
        <?php endif; ?>
        <a href="index.php?route=dashboard" class="btn btn-outline">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Category Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
    <a href="index.php?route=notifications" class="btn btn-sm <?= empty($activeCategory) ? 'btn-primary' : 'btn-outline' ?>">
        All Notifications
    </a>
    <a href="index.php?route=notifications&category=emergency_alert" class="btn btn-sm <?= $activeCategory === 'emergency_alert' ? 'btn-danger' : 'btn-outline' ?>">
        <i class="fa fa-triangle-exclamation"></i> Emergency Alerts
    </a>
    <a href="index.php?route=notifications&category=incident_report" class="btn btn-sm <?= $activeCategory === 'incident_report' ? 'btn-primary' : 'btn-outline' ?>">
        <i class="fa fa-life-ring"></i> Incidents & SOS
    </a>
    <a href="index.php?route=notifications&category=allocation" class="btn btn-sm <?= $activeCategory === 'allocation' ? 'btn-primary' : 'btn-outline' ?>">
        <i class="fa fa-boxes-stacked"></i> Resource Allocations
    </a>
    <a href="index.php?route=notifications&category=account" class="btn btn-sm <?= $activeCategory === 'account' ? 'btn-primary' : 'btn-outline' ?>">
        <i class="fa fa-user-check"></i> Account Approvals
    </a>
</div>

<!-- Notification Feed -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa fa-bell text-primary"></i> 
            <?= empty($activeCategory) ? 'All Activity Feed' : ucwords(str_replace('_', ' ', $activeCategory)) ?>
        </div>
        <div style="font-size: 0.82rem; color: var(--text-muted);">
            Showing <?= count($notifications) ?> items
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($notifications)): ?>
            <div style="text-align: center; padding: 48px 16px; color: var(--text-muted);">
                <i class="fa fa-bell-slash" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                <div style="font-weight: 600; font-size: 1rem; color: #475569;">No notifications in this category.</div>
                <p style="font-size: 0.85rem; margin-top: 4px;">You're completely up to date with BDRRMC operations.</p>
            </div>
        <?php else: ?>
            <div class="notification-list">
                <?php foreach ($notifications as $n): ?>
                    <?php
                        $isAlert = ($n['category'] === 'emergency_alert');
                        $isUnread = empty($n['is_read']);
                        
                        $iconClass = 'fa-bell text-primary';
                        $bgIcon = '#eff6ff';
                        if ($n['category'] === 'emergency_alert') {
                            $iconClass = 'fa-triangle-exclamation text-danger';
                            $bgIcon = '#fee2e2';
                        } elseif ($n['category'] === 'incident_report') {
                            $iconClass = 'fa-life-ring text-warning';
                            $bgIcon = '#fef3c7';
                        } elseif ($n['category'] === 'allocation') {
                            $iconClass = 'fa-boxes-stacked text-success';
                            $bgIcon = '#dcfce7';
                        } elseif ($n['category'] === 'account') {
                            $iconClass = 'fa-user-check text-info';
                            $bgIcon = '#e0f2fe';
                        }
                    ?>
                    <div style="display: flex; gap: 16px; padding: 16px 20px; border-bottom: 1px solid var(--border); background-color: <?= $isUnread ? '#f8fafc' : '#ffffff' ?>; position: relative;">
                        <?php if ($isUnread): ?>
                            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background-color: <?= $isAlert ? 'var(--danger)' : 'var(--primary)' ?>;"></div>
                        <?php endif; ?>

                        <div style="width: 42px; height: 42px; border-radius: 50%; background: <?= $bgIcon ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                            <i class="fa <?= $iconClass ?>"></i>
                        </div>

                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap;">
                                <div>
                                    <strong style="font-size: 0.95rem; color: #0f172a;"><?= htmlspecialchars($n['title']) ?></strong>
                                    <?php if ($isUnread): ?>
                                        <span class="badge badge-danger" style="font-size: 0.68rem; margin-left: 6px;">NEW</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); white-space: nowrap;">
                                    <i class="fa fa-clock"></i> <?= date('M d, Y h:i A', strtotime($n['created_at'])) ?>
                                </div>
                            </div>

                            <p style="color: #334155; font-size: 0.88rem; margin: 6px 0 10px 0; line-height: 1.45;">
                                <?= nl2br(htmlspecialchars($n['message'])) ?>
                            </p>

                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <?php if (!empty($n['reference_type']) && !empty($n['reference_id'])): ?>
                                    <?php if ($n['reference_type'] === 'disaster_request'): ?>
                                        <a href="index.php?route=disaster-requests-show&id=<?= (int)$n['reference_id'] ?>" class="btn btn-outline btn-sm">
                                            <i class="fa fa-eye"></i> View Incident #<?= (int)$n['reference_id'] ?>
                                        </a>
                                    <?php elseif ($n['reference_type'] === 'recommendation'): ?>
                                        <a href="index.php?route=recommendations-show&id=<?= (int)$n['reference_id'] ?>" class="btn btn-outline btn-sm">
                                            <i class="fa fa-microchip"></i> View Recommendation #<?= (int)$n['reference_id'] ?>
                                        </a>
                                    <?php elseif ($n['reference_type'] === 'user'): ?>
                                        <a href="index.php?route=pending-residents" class="btn btn-outline btn-sm">
                                            <i class="fa fa-user-clock"></i> Review Resident
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($isUnread): ?>
                                    <form action="index.php?route=notifications-mark-read" method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="notification_id" value="<?= (int)$n['notification_id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--text-muted); font-size: 0.75rem;">
                                            <i class="fa fa-check"></i> Mark read
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
