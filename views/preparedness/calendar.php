<!-- views/preparedness/calendar.php -->
<?php 
use App\Core\View; 
use App\Core\Auth;
use App\Core\Request;

$currentRole = Auth::role();
$selectedMonthStr = $selectedMonth ?? Request::get('month', date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', (string)$selectedMonthStr)) {
    $selectedMonthStr = date('Y-m');
}

$selectedMonthTimestamp = strtotime($selectedMonthStr . '-01');
$daysInMonth = (int)date('t', $selectedMonthTimestamp);
$firstDayOfWeek = (int)date('w', $selectedMonthTimestamp); // 0 (Sun) to 6 (Sat)
$monthNameYear = date('F Y', $selectedMonthTimestamp);
$todayDateStr = date('Y-m-d');
$todayDay = (int)date('j');
$currentMonthNum = date('Y-m');

$prevMonth = date('Y-m', strtotime('-1 month', $selectedMonthTimestamp));
$nextMonth = date('Y-m', strtotime('+1 month', $selectedMonthTimestamp));

// Prepare JSON-ready event dataset indexed by date (YYYY-MM-DD)
$calendarJsonData = [];
$activitiesByDay = [];

foreach ($activities as $act) {
    $actDateKey = date('Y-m-d', strtotime($act['schedule_date']));
    $actMonthKey = date('Y-m', strtotime($act['schedule_date']));
    $isPast = ($act['status'] === 'Completed' || strtotime($act['schedule_date']) < time());

    if ($actMonthKey === $selectedMonthStr) {
        $dayNum = (int)date('j', strtotime($act['schedule_date']));
        $activitiesByDay[$dayNum][] = $act;
    }

    $calendarJsonData[$actDateKey][] = [
        'id' => (int)$act['preparedness_activities_id'],
        'title' => $act['title'],
        'type' => $act['activity_type'],
        'description' => $act['description'],
        'schedule_date' => $act['schedule_date'],
        'date_formatted' => date('l, F d, Y', strtotime($act['schedule_date'])),
        'time' => date('h:i A', strtotime($act['schedule_date'])),
        'location' => $act['location'],
        'purok_name' => !empty($act['purok_name']) ? $act['purok_name'] : 'All Hinaplanon Puroks',
        'max_participants' => (int)$act['max_participants'],
        'assigned_personnel' => !empty($act['assigned_personnel']) ? $act['assigned_personnel'] : 'BDRRMC Officer In-Charge',
        'volunteer_person' => !empty($act['volunteer_person']) ? $act['volunteer_person'] : 'Purok Disaster Volunteers & Youth Council',
        'status' => $act['status'],
        'is_past' => $isPast,
        'organizer' => trim(($act['organizer_first'] ?? 'BDRRMC') . ' ' . ($act['organizer_last'] ?? 'Admin')),
        'evaluation' => !empty($act['evaluation_id']) ? [
            'actual_turnout' => (int)$act['actual_turnout'],
            'rating' => (int)$act['objectives_met_rating'],
            'problems' => $act['problems_encountered'] ?? '',
            'supplies' => $act['supplies_used'] ?? '',
            'recommendations' => $act['recommendations_for_improvement'] ?? ''
        ] : null
    ];
}
?>

<style>
/* Interactive Calendar Day Cells */
.cal-day-cell {
    min-height: 96px;
    border-radius: 8px;
    padding: 7px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    cursor: pointer;
    position: relative;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
    border: 1px solid var(--border-light);
    user-select: none;
}
.cal-day-cell:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    border-color: #3b82f6 !important;
    z-index: 2;
}
.cal-day-cell.is-today {
    background: #f0f8f7 !important;
    border: 2px solid #1B7E74 !important;
}
.cal-day-cell.has-events {
    background: #fdfdf9;
    border-color: #f59e0b;
}
.cal-day-cell.cal-day-active {
    outline: 3px solid #1B7E74 !important;
    outline-offset: -1px;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.25) !important;
    z-index: 3;
}

/* Day Details Modal */
.cal-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.68);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
    padding: 16px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.22s ease-in-out;
}
.cal-modal-backdrop.is-open {
    opacity: 1;
    pointer-events: auto;
}
.cal-modal-dialog {
    background: #ffffff;
    border-radius: 12px;
    width: 100%;
    max-width: 680px;
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: translateY(20px) scale(0.96);
    transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.cal-modal-backdrop.is-open .cal-modal-dialog {
    transform: translateY(0) scale(1);
}
.cal-modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: #f8fafc;
}
.cal-modal-close {
    background: transparent;
    border: none;
    font-size: 1.6rem;
    line-height: 1;
    color: #64748b;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.15s ease;
}
.cal-modal-close:hover {
    color: #0f172a;
    background: #e2e8f0;
}
.cal-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.cal-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
    flex-wrap: wrap;
    gap: 8px;
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
            <?php if ($currentRole === 'ICDRRMO'): ?>
                ICDRRMO Event Logs & Activity Calendar
            <?php elseif ($currentRole === 'Barangay Head'): ?>
                Barangay Hinaplanon Event Lists & Activity Calendar
            <?php else: ?>
                Barangay Hinaplanon Preparedness Drills & Activity Calendar
            <?php endif; ?>
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Interactive calendar schedules, post-drill evaluation reports, and assigned personnel / volunteer rosters.
        </p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="index.php?route=preparedness" class="btn btn-outline"><i class="fa fa-list"></i> Table Roster</a>
        <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
            <a href="index.php?route=preparedness-create" class="btn btn-primary"><i class="fa fa-calendar-plus"></i> Schedule New Activity</a>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. CALENDAR HIGHLIGHTS (METRICS & OVERVIEW) -->
<!-- ============================================== -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card" style="border-left: 4px solid var(--primary-light);">
        <div class="stat-label"><i class="fa fa-calendar-days text-primary"></i> Total Scheduled Events</div>
        <div class="stat-value"><?= (int)($summary['total'] ?? count($activities)) ?></div>
        <div class="stat-sub">Overall community readiness programs</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #f59e0b; background: linear-gradient(to bottom, #ffffff, #fffbeb);">
        <div class="stat-label"><i class="fa fa-bolt-lightning text-warning"></i> Upcoming Drills</div>
        <div class="stat-value" style="color: #b45309;"><?= count($upcoming) ?></div>
        <div class="stat-sub">Next readiness exercises awaiting deployment</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #10b981; background: linear-gradient(to bottom, #ffffff, #f0fdf4);">
        <div class="stat-label"><i class="fa fa-check-double text-success"></i> Past & Completed Events</div>
        <div class="stat-value" style="color: #047857;"><?= count($past) ?></div>
        <div class="stat-sub">Successfully conducted & evaluated drills</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #06b6d4;">
        <div class="stat-label"><i class="fa fa-users-rays text-info"></i> Community Turnout</div>
        <div class="stat-value"><?= number_format((int)($summary['total_turnout'] ?? 0)) ?></div>
        <div class="stat-sub">Recorded resident attendees engaged</div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 2. MONTHLY CALENDAR HIGHLIGHT MATRIX (VISUAL CALENDAR GRID) -->
<!-- ============================================================== -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div class="card-title">
            <i class="fa fa-calendar-check text-primary"></i> Monthly Highlights & Activity Schedule
        </div>

        <!-- Month Navigation Controls -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="index.php?route=preparedness-calendar&month=<?= $prevMonth ?>" class="btn btn-outline btn-sm" title="Previous Month">
                <i class="fa fa-chevron-left"></i> Prev
            </a>
            <span style="font-weight: 800; color: var(--primary-dark); font-size: 0.95rem; min-width: 130px; text-align: center;">
                <?= $monthNameYear ?>
            </span>
            <a href="index.php?route=preparedness-calendar&month=<?= $nextMonth ?>" class="btn btn-outline btn-sm" title="Next Month">
                Next <i class="fa fa-chevron-right"></i>
            </a>
            <?php if ($selectedMonthStr !== $currentMonthNum): ?>
                <a href="index.php?route=preparedness-calendar&month=<?= $currentMonthNum ?>" class="btn btn-sm btn-outline" style="margin-left: 4px;" title="Jump to current month">
                    Current Month
                </a>
            <?php endif; ?>
        </div>

        <!-- Status Legend -->
        <div style="display: flex; align-items: center; gap: 12px; font-size: 0.8rem;">
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #3b82f6; display: inline-block;"></span> Upcoming
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span> Completed
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #1B7E74; display: inline-block; border: 2px solid #a3e2db;"></span> Today
            </span>
        </div>
    </div>

    <div class="card-body" style="padding: 16px;">
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; margin-bottom: 8px;">
            <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $w): ?>
                <div style="font-weight: 700; font-size: 0.78rem; color: var(--text-muted); padding: 6px 0;"><?= $w ?></div>
            <?php endforeach; ?>
        </div>

        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
            <!-- Blank leading days -->
            <?php for ($i = 0; $i < $firstDayOfWeek; $i++): ?>
                <div style="min-height: 96px; background: #f8fafc; border: 1px dashed var(--border-light); border-radius: 8px; opacity: 0.4;"></div>
            <?php endfor; ?>

            <!-- Calendar Days -->
            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                    $dateStr = sprintf('%s-%02d', $selectedMonthStr, $d);
                    $isToday = ($dateStr === $todayDateStr);
                    $hasEvents = !empty($activitiesByDay[$d]);
                    $eventsCount = $hasEvents ? count($activitiesByDay[$d]) : 0;
                    $dayFormattedLong = date('l, F d, Y', strtotime($dateStr));
                ?>
                <div class="cal-day-cell <?= $isToday ? 'is-today' : '' ?> <?= $hasEvents ? 'has-events' : '' ?>"
                     id="cal-day-<?= $dateStr ?>"
                     onclick="openDateDetails('<?= $dateStr ?>')"
                     title="Click to view details for <?= htmlspecialchars($dayFormattedLong) ?>">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; font-weight: <?= $isToday ? '800' : '600' ?>; color: <?= $isToday ? '#1d4ed8' : 'var(--text-primary)' ?>;">
                            <?= $d ?>
                        </span>
                        <div>
                            <?php if ($isToday): ?>
                                <span style="background: #1B7E74; color: #fff; font-size: 9px; padding: 1px 5px; border-radius: 3px; font-weight: 700;">TODAY</span>
                            <?php endif; ?>
                            <?php if ($eventsCount > 0): ?>
                                <span class="badge badge-info" style="font-size: 9px; padding: 1px 4px; border-radius: 3px;">
                                    <?= $eventsCount ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($hasEvents): ?>
                        <div style="margin-top: 4px; display: flex; flex-direction: column; gap: 3px;">
                            <?php foreach ($activitiesByDay[$d] as $ev): ?>
                                <?php $isComp = ($ev['status'] === 'Completed'); ?>
                                <div style="font-size: 0.68rem; padding: 2px 5px; border-radius: 4px; background: <?= $isComp ? '#10b981' : '#3b82f6' ?>; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600;" 
                                     title="<?= htmlspecialchars($ev['title']) ?> (<?= $ev['activity_type'] ?>)">
                                    <?= htmlspecialchars($ev['title']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="flex: 1;"></div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: flex-end; margin-top: 2px;">
                        <span style="font-size: 9px; color: #94a3b8; opacity: 0.8;">
                            <i class="fa fa-circle-info"></i>
                        </span>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Inline Selected Date Notification / Quick Action -->
        <div id="inlineDateBanner" style="margin-top: 14px; padding: 10px 14px; background: #f8fafc; border: 1px solid var(--border-light); border-radius: 6px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-hand-pointer text-primary"></i>
                <span id="inlineDateText" style="font-size: 0.85rem; color: var(--text-primary); font-weight: 600;">
                    Click any date on the calendar above to view all scheduled activity details, personnel, and evaluations.
                </span>
            </div>
            <div id="inlineDateActions"></div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 3. INTERACTIVE FILTER TABS -->
<!-- ============================================== -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
    <div style="display: flex; gap: 6px;" id="eventFilterTabs">
        <button type="button" class="btn btn-sm btn-primary" onclick="filterEvents('all', this)">
            <i class="fa fa-layer-group"></i> All Events (<?= count($activities) ?>)
        </button>
        <button type="button" class="btn btn-sm btn-outline" onclick="filterEvents('upcoming', this)">
            <i class="fa fa-calendar-plus text-warning"></i> Upcoming Drills (<?= count($upcoming) ?>)
        </button>
        <button type="button" class="btn btn-sm btn-outline" onclick="filterEvents('past', this)">
            <i class="fa fa-clock-rotate-left text-success"></i> Past Events Archive (<?= count($past) ?>)
        </button>
    </div>
    <div style="font-size: 0.8rem; color: var(--text-muted);">
        <i class="fa fa-shield-halved text-primary"></i> Every drill records dedicated personnel and volunteer assignments.
    </div>
</div>

<!-- ============================================================== -->
<!-- 4. ACTIVITY EVENT CARDS (UPCOMING & PAST WITH ASSIGNED PERSONNEL) -->
<!-- ============================================================== -->
<div class="grid-cols-2" id="eventCardsGrid">
    <?php foreach ($activities as $act): ?>
        <?php 
            $isPast = ($act['status'] === 'Completed' || strtotime($act['schedule_date']) < time());
            $borderColor = $isPast ? '#10b981' : '#1B7E74';
            $filterCategory = $isPast ? 'past' : 'upcoming';
        ?>
        <div class="card event-activity-card" 
             id="activity-card-<?= $act['preparedness_activities_id'] ?>" 
             data-category="<?= $filterCategory ?>"
             style="border-top: 4px solid <?= $borderColor ?>; display: flex; flex-direction: column; justify-content: space-between;">
            
            <div class="card-header" style="background: <?= $isPast ? '#f0fdf4' : '#f8fafc' ?>;">
                <div>
                    <span class="badge badge-info"><?= htmlspecialchars($act['activity_type']) ?></span>
                    <?php if ($isPast): ?>
                        <span class="badge badge-success" style="margin-left: 6px;"><i class="fa fa-check"></i> Past Event</span>
                    <?php else: ?>
                        <span class="badge badge-warning" style="margin-left: 6px;"><i class="fa fa-clock"></i> Upcoming</span>
                    <?php endif; ?>
                </div>
                <strong style="color: var(--primary-dark); font-size: 0.95rem;">
                    #<?= $act['preparedness_activities_id'] ?>
                </strong>
            </div>

            <div class="card-body" style="flex: 1;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--primary-dark); margin: 0 0 8px 0;">
                    <?= htmlspecialchars($act['title']) ?>
                </h3>

                <p style="font-size: 0.86rem; margin-bottom: 14px; color: var(--text-secondary); line-height: 1.45;">
                    <?= nl2br(htmlspecialchars($act['description'])) ?>
                </p>

                <!-- Logistics & Venue Details -->
                <div style="padding: 10px 12px; background: #f8fafc; border-radius: 6px; font-size: 0.82rem; margin-bottom: 12px; border: 1px solid var(--border-light); display: grid; grid-template-columns: 1fr; gap: 6px;">
                    <div>
                        <i class="fa fa-calendar-day text-primary" style="width: 16px;"></i> 
                        <strong>Schedule:</strong> <?= date('F d, Y — h:i A', strtotime($act['schedule_date'])) ?>
                    </div>
                    <div>
                        <i class="fa fa-location-dot text-danger" style="width: 16px;"></i> 
                        <strong>Venue / Location:</strong> <?= htmlspecialchars($act['location']) ?>
                    </div>
                    <div>
                        <i class="fa fa-bullseye text-warning" style="width: 16px;"></i> 
                        <strong>Target Area:</strong> <?= htmlspecialchars($act['purok_name'] ?? 'All Hinaplanon Puroks') ?>
                    </div>
                    <div>
                        <i class="fa fa-users text-info" style="width: 16px;"></i> 
                        <strong>Capacity / Expected:</strong> <?= (int)$act['max_participants'] ?> participants
                    </div>
                </div>

                <!-- ASSIGNED PERSONNEL & VOLUNTEER ROSTER SECTION -->
                <div style="padding: 10px 12px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.82rem; margin-bottom: 12px;">
                    <div style="font-weight: 700; color: var(--primary-dark); margin-bottom: 6px; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="fa fa-id-badge text-primary"></i> Event Personnel & Volunteer Assignments
                    </div>
                    <div style="margin-bottom: 4px;">
                        <span class="badge badge-primary" style="font-size: 0.75rem; margin-right: 4px;">
                            <i class="fa fa-user-shield"></i> Assigned Personnel
                        </span>
                        <strong style="color: var(--primary-dark);">
                            <?= htmlspecialchars($act['assigned_personnel'] ?: 'BDRRMC Officer In-Charge') ?>
                        </strong>
                    </div>
                    <div>
                        <span class="badge badge-success" style="font-size: 0.75rem; margin-right: 4px;">
                            <i class="fa fa-hand-holding-heart"></i> Volunteer Person / Team
                        </span>
                        <span style="color: var(--text-secondary); font-weight: 600;">
                            <?= htmlspecialchars($act['volunteer_person'] ?: 'Purok Disaster Volunteers & Youth Council') ?>
                        </span>
                    </div>
                </div>

                <!-- PAST EVENT EVALUATION SECTION -->
                <?php if ($isPast && !empty($act['evaluation_id'])): ?>
                    <div style="padding: 12px; background: #f0fdf4; border-radius: 6px; font-size: 0.82rem; border-left: 4px solid var(--success); border: 1px solid #bbf7d0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <strong style="color: #166534; font-size: 0.88rem;">
                                <i class="fa fa-star text-warning"></i> Post-Activity Evaluation Report
                            </strong>
                            <span class="badge badge-success"><?= (int)$act['actual_turnout'] ?> Attended</span>
                        </div>
                        <div style="margin-bottom: 4px;">
                            <strong>Turnout Rating:</strong> 
                            <span style="color: #d97706; font-weight: 700;">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="fa fa-star<?= $s <= (int)$act['objectives_met_rating'] ? '' : '-o' ?>"></i>
                                <?php endfor; ?>
                                (<?= (int)$act['objectives_met_rating'] ?> / 5 Stars)
                            </span>
                        </div>
                        <?php if (!empty($act['problems_encountered'])): ?>
                            <div style="color: #334155; margin-top: 4px;">
                                <strong>Feedback / Observations:</strong> <?= htmlspecialchars($act['problems_encountered']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($act['recommendations_for_improvement'])): ?>
                            <div style="color: #166534; font-style: italic; margin-top: 4px;">
                                <strong>Next Improvement:</strong> <?= htmlspecialchars($act['recommendations_for_improvement']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($isPast && in_array($currentRole, ['Barangay Head', 'ICDRRMO', 'Responder'])): ?>
                    <div style="padding: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #92400e;">Event concluded. Awaiting post-drill evaluation submission.</span>
                        <a href="index.php?route=preparedness-evaluate&id=<?= $act['preparedness_activities_id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fa fa-clipboard-check"></i> Evaluate Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-footer" style="background: #ffffff; padding: 12px 16px; border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.78rem; color: var(--text-muted);">
                    Organized by: <?= htmlspecialchars($act['organizer_first'] ?? 'BDRRMC') ?> <?= htmlspecialchars($act['organizer_last'] ?? 'Admin') ?>
                </span>
                <?php if ($act['status'] === 'Scheduled' && in_array($currentRole, ['Barangay Head', 'ICDRRMO', 'Responder'])): ?>
                    <a href="index.php?route=preparedness-evaluate&id=<?= $act['preparedness_activities_id'] ?>" class="btn btn-outline btn-sm">
                        <i class="fa fa-clipboard-check"></i> Record Evaluation
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ============================================== -->
<!-- 5. DATE DETAILS MODAL DIALOG -->
<!-- ============================================== -->
<div id="dateDetailsModal" class="cal-modal-backdrop" onclick="closeModalOnBackdrop(event)">
    <div class="cal-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="calModalDateTitle">
        <div class="cal-modal-header">
            <div>
                <span class="badge badge-primary" id="calModalBadge" style="margin-bottom: 4px; display: inline-block;">
                    <i class="fa fa-calendar-day"></i> Selected Date
                </span>
                <h3 id="calModalDateTitle" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--primary-dark);">
                    <!-- Populated via JavaScript -->
                </h3>
                <div id="calModalDateSubtitle" style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;">
                    <!-- Activity count -->
                </div>
            </div>
            <button type="button" class="cal-modal-close" onclick="closeDateDetailsModal()" aria-label="Close details dialog">&times;</button>
        </div>

        <div class="cal-modal-body" id="calModalBody">
            <!-- Populated via JavaScript -->
        </div>

        <div class="cal-modal-footer">
            <div id="calModalFooterAction"></div>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeDateDetailsModal()">Close Window</button>
        </div>
    </div>
</div>

<script>
    const calendarEvents = <?= json_encode($calendarJsonData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const canSchedule = <?= in_array($currentRole, ['Barangay Head', 'ICDRRMO']) ? 'true' : 'false' ?>;
    const canEvaluate = <?= in_array($currentRole, ['Barangay Head', 'ICDRRMO', 'Responder']) ? 'true' : 'false' ?>;
    let selectedActiveDate = null;

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatDateFriendly(dateStr) {
        try {
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                const dateObj = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                return dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
        } catch(e) {}
        return dateStr;
    }

    function openDateDetails(dateStr) {
        selectedActiveDate = dateStr;
        const formattedDate = formatDateFriendly(dateStr);
        const dayEvents = calendarEvents[dateStr] || [];

        // 1. Highlight active day in grid
        document.querySelectorAll('.cal-day-cell').forEach(el => el.classList.remove('cal-day-active'));
        const activeCell = document.getElementById('cal-day-' + dateStr);
        if (activeCell) {
            activeCell.classList.add('cal-day-active');
        }

        // 2. Update inline banner
        const inlineText = document.getElementById('inlineDateText');
        const inlineActions = document.getElementById('inlineDateActions');
        if (dayEvents.length > 0) {
            inlineText.innerHTML = `<strong>Selected: ${formattedDate}</strong> — <span class="badge badge-success" style="margin-left: 4px;">${dayEvents.length} Activity Found</span> (${escapeHtml(dayEvents[0].title)})`;
            inlineActions.innerHTML = `<button type="button" class="btn btn-primary btn-sm" onclick="openDateDetailsModal()"><i class="fa fa-expand"></i> View Full Details</button>`;
        } else {
            inlineText.innerHTML = `<strong>Selected: ${formattedDate}</strong> — <span class="text-muted">No drills scheduled for this date.</span>`;
            if (canSchedule) {
                inlineActions.innerHTML = `<a href="index.php?route=preparedness-create&date=${dateStr}" class="btn btn-primary btn-sm"><i class="fa fa-calendar-plus"></i> Schedule Drill</a>`;
            } else {
                inlineActions.innerHTML = `<button type="button" class="btn btn-outline btn-sm" onclick="openDateDetailsModal()"><i class="fa fa-eye"></i> View Date Status</button>`;
            }
        }

        // 3. Render Modal Content
        renderModalContent(dateStr, formattedDate, dayEvents);

        // 4. Show Modal Dialog
        openDateDetailsModal();
    }

    function renderModalContent(dateStr, formattedDate, dayEvents) {
        const titleEl = document.getElementById('calModalDateTitle');
        const subtitleEl = document.getElementById('calModalDateSubtitle');
        const badgeEl = document.getElementById('calModalBadge');
        const bodyEl = document.getElementById('calModalBody');
        const footerActionEl = document.getElementById('calModalFooterAction');

        titleEl.textContent = formattedDate;

        if (dayEvents.length > 0) {
            badgeEl.className = 'badge badge-success';
            badgeEl.innerHTML = `<i class="fa fa-calendar-check"></i> Scheduled Event Day`;
            subtitleEl.textContent = `${dayEvents.length} preparedness ${dayEvents.length === 1 ? 'activity' : 'activities'} scheduled on this day.`;

            let html = '';
            dayEvents.forEach(act => {
                const borderLeftColor = act.is_past ? '#10b981' : '#1B7E74';
                const statusBadge = act.is_past 
                    ? `<span class="badge badge-success"><i class="fa fa-check"></i> Past / Completed</span>` 
                    : `<span class="badge badge-warning"><i class="fa fa-clock"></i> Upcoming Drill</span>`;

                // Post-Activity Evaluation block if available
                let evalBlockHtml = '';
                if (act.evaluation) {
                    let starIcons = '';
                    for (let s = 1; s <= 5; s++) {
                        starIcons += `<i class="fa fa-star${s <= act.evaluation.rating ? '' : '-o'}"></i>`;
                    }
                    evalBlockHtml = `
                        <div style="padding: 12px; background: #f0fdf4; border-radius: 6px; font-size: 0.82rem; border-left: 4px solid var(--success); border: 1px solid #bbf7d0; margin-top: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <strong style="color: #166534; font-size: 0.88rem;">
                                    <i class="fa fa-star text-warning"></i> Post-Activity Evaluation Report
                                </strong>
                                <span class="badge badge-success">${act.evaluation.actual_turnout} Attended</span>
                            </div>
                            <div style="margin-bottom: 4px;">
                                <strong>Turnout Rating:</strong> 
                                <span style="color: #d97706; font-weight: 700;">
                                    ${starIcons} (${act.evaluation.rating} / 5 Stars)
                                </span>
                            </div>
                            ${act.evaluation.problems ? `
                                <div style="color: #334155; margin-top: 4px;">
                                    <strong>Feedback / Observations:</strong> ${escapeHtml(act.evaluation.problems)}
                                </div>
                            ` : ''}
                            ${act.evaluation.recommendations ? `
                                <div style="color: #166534; font-style: italic; margin-top: 4px;">
                                    <strong>Next Improvement:</strong> ${escapeHtml(act.evaluation.recommendations)}
                                </div>
                            ` : ''}
                        </div>
                    `;
                } else if (act.is_past && canEvaluate) {
                    evalBlockHtml = `
                        <div style="padding: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <span style="color: #92400e;">Event concluded. Awaiting post-drill evaluation submission.</span>
                            <a href="index.php?route=preparedness-evaluate&id=${act.id}" class="btn btn-warning btn-sm">
                                <i class="fa fa-clipboard-check"></i> Evaluate Now
                            </a>
                        </div>
                    `;
                }

                // Action buttons
                let actionsHtml = '';
                if (!act.is_past && canEvaluate) {
                    actionsHtml += `
                        <a href="index.php?route=preparedness-evaluate&id=${act.id}" class="btn btn-outline btn-sm">
                            <i class="fa fa-clipboard-check"></i> Record Evaluation
                        </a>
                    `;
                }
                actionsHtml += `
                    <button type="button" class="btn btn-outline btn-sm" onclick="jumpToCard(${act.id})">
                        <i class="fa fa-arrow-down"></i> Scroll to Card
                    </button>
                `;

                html += `
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; background: #ffffff; border-left: 5px solid ${borderLeftColor}; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <span class="badge badge-info">${escapeHtml(act.type)}</span>
                                ${statusBadge}
                            </div>
                            <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary-dark);">
                                Activity #${act.id}
                            </span>
                        </div>

                        <h4 style="margin: 0 0 8px 0; font-size: 1.1rem; font-weight: 800; color: var(--primary-dark);">
                            ${escapeHtml(act.title)}
                        </h4>

                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0 0 12px 0; line-height: 1.45;">
                            ${escapeHtml(act.description)}
                        </p>

                        <!-- Logistics Details Grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 10px 12px; background: #f8fafc; border-radius: 6px; font-size: 0.8rem; margin-bottom: 12px; border: 1px solid #e2e8f0;">
                            <div>
                                <i class="fa fa-clock text-primary" style="width: 16px;"></i>
                                <strong>Time:</strong> ${act.time}
                            </div>
                            <div>
                                <i class="fa fa-location-dot text-danger" style="width: 16px;"></i>
                                <strong>Venue:</strong> ${escapeHtml(act.location)}
                            </div>
                            <div>
                                <i class="fa fa-bullseye text-warning" style="width: 16px;"></i>
                                <strong>Coverage:</strong> ${escapeHtml(act.purok_name)}
                            </div>
                            <div>
                                <i class="fa fa-users text-info" style="width: 16px;"></i>
                                <strong>Capacity:</strong> ${act.max_participants} attendees
                            </div>
                        </div>

                        <!-- Assigned Lead Personnel & Volunteers -->
                        <div style="padding: 10px 12px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.82rem; margin-bottom: 10px;">
                            <div style="font-weight: 700; color: var(--primary-dark); margin-bottom: 6px; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em;">
                                <i class="fa fa-id-badge text-primary"></i> Event Personnel & Volunteer Assignments
                            </div>
                            <div style="margin-bottom: 4px;">
                                <span class="badge badge-primary" style="font-size: 0.72rem; margin-right: 4px;">
                                    <i class="fa fa-user-shield"></i> Assigned Personnel
                                </span>
                                <strong style="color: var(--primary-dark);">${escapeHtml(act.assigned_personnel)}</strong>
                            </div>
                            <div>
                                <span class="badge badge-success" style="font-size: 0.72rem; margin-right: 4px;">
                                    <i class="fa fa-hand-holding-heart"></i> Volunteer Person / Team
                                </span>
                                <span style="color: var(--text-secondary); font-weight: 600;">${escapeHtml(act.volunteer_person)}</span>
                            </div>
                        </div>

                        ${evalBlockHtml}

                        <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 12px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                            ${actionsHtml}
                        </div>
                    </div>
                `;
            });

            bodyEl.innerHTML = html;

            if (canSchedule) {
                footerActionEl.innerHTML = `
                    <a href="index.php?route=preparedness-create&date=${dateStr}" class="btn btn-primary btn-sm">
                        <i class="fa fa-calendar-plus"></i> Add Another Drill on This Date
                    </a>
                `;
            } else {
                footerActionEl.innerHTML = `
                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                        <i class="fa fa-shield-halved text-primary"></i> Hinaplanon Disaster Preparedness Operations
                    </span>
                `;
            }

        } else {
            // Empty State
            badgeEl.className = 'badge badge-outline';
            badgeEl.innerHTML = `<i class="fa fa-calendar"></i> No Scheduled Activities`;
            subtitleEl.textContent = `No drills or simulation exercises are recorded for this date.`;

            let emptyHtml = `
                <div style="text-align: center; padding: 36px 16px;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: #f0f8f7; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                        <i class="fa fa-calendar-day" style="font-size: 1.8rem; color: #3b82f6;"></i>
                    </div>
                    <h4 style="margin: 0 0 6px 0; font-size: 1.15rem; font-weight: 800; color: var(--primary-dark);">
                        No Preparedness Drills on this Date
                    </h4>
                    <p style="color: var(--text-muted); font-size: 0.88rem; max-width: 420px; margin: 0 auto 20px auto; line-height: 1.5;">
                        There are currently no community flood evacuations, first-aid seminars, or disaster orientations scheduled on <strong>${formattedDate}</strong>.
                    </p>
            `;

            if (canSchedule) {
                emptyHtml += `
                    <a href="index.php?route=preparedness-create&date=${dateStr}" class="btn btn-primary">
                        <i class="fa fa-calendar-plus"></i> Schedule Preparedness Drill on This Date
                    </a>
                `;
            } else {
                emptyHtml += `
                    <span style="font-size: 0.82rem; color: var(--text-muted); background: #f8fafc; border: 1px solid var(--border-light); padding: 6px 14px; border-radius: 20px;">
                        <i class="fa fa-info-circle text-primary"></i> Check back regularly for updates from BDRRMC Hinaplanon.
                    </span>
                `;
            }

            emptyHtml += `</div>`;
            bodyEl.innerHTML = emptyHtml;
            footerActionEl.innerHTML = '';
        }
    }

    function openDateDetailsModal() {
        const modal = document.getElementById('dateDetailsModal');
        if (modal) {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDateDetailsModal() {
        const modal = document.getElementById('dateDetailsModal');
        if (modal) {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }

    function closeModalOnBackdrop(event) {
        if (event.target && event.target.id === 'dateDetailsModal') {
            closeDateDetailsModal();
        }
    }

    function jumpToCard(id) {
        closeDateDetailsModal();
        const card = document.getElementById('activity-card-' + id);
        if (card) {
            const cat = card.getAttribute('data-category');
            const allBtn = document.querySelector('#eventFilterTabs button:first-child');
            if (allBtn) {
                filterEvents('all', allBtn);
            }
            setTimeout(() => {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                card.style.transition = 'box-shadow 0.3s ease, border-color 0.3s ease';
                card.style.boxShadow = '0 0 0 4px rgba(37, 99, 235, 0.45)';
                setTimeout(() => {
                    card.style.boxShadow = '';
                }, 2500);
            }, 150);
        }
    }

    function filterEvents(category, buttonElement) {
        // Toggle active button style
        const buttons = document.querySelectorAll('#eventFilterTabs button');
        buttons.forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline');
        });
        buttonElement.classList.remove('btn-outline');
        buttonElement.classList.add('btn-primary');

        // Filter cards
        const cards = document.querySelectorAll('.event-activity-card');
        cards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            if (category === 'all' || cardCategory === category) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Keyboard support: Escape key closes modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            closeDateDetailsModal();
        }
    });
</script>