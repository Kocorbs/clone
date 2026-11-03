<!-- views/preparedness/evaluate.php -->
<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-star"></i> Post-Activity Event Evaluation</div>
    </div>
    <div class="card-body">
        <div style="background-color: #f8fafc; padding: 12px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid var(--primary-light);">
            <h4 style="color: var(--primary-dark);"><?= htmlspecialchars($activity['title']) ?></h4>
            <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">
                <?= htmlspecialchars($activity['activity_type']) ?> | Date: <?= date('F d, Y h:i A', strtotime($activity['schedule_date'])) ?> | Venue: <?= htmlspecialchars($activity['location']) ?>
            </div>
        </div>

        <form action="index.php?route=preparedness-evaluate&id=<?= $activity['preparedness_activities_id'] ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Actual Participant Turnout (Count) *</label>
                    <input type="number" name="actual_turnout" class="form-control" min="1" value="50" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Objectives Met Rating (1 to 5 Stars) *</label>
                    <select name="objectives_met_rating" class="form-control" required>
                        <option value="5">5 — Excellent (All drill goals achieved)</option>
                        <option value="4">4 — Very Good (Most objectives met effectively)</option>
                        <option value="3">3 — Satisfactory (Adequate execution)</option>
                        <option value="2">2 — Fair (Minor operational delays)</option>
                        <option value="1">1 — Poor (Goals not achieved)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Problems / Bottlenecks Encountered:</label>
                <textarea name="problems_encountered" class="form-control" rows="2" placeholder="e.g. Delayed siren broadcast, congestion at narrow alleyways near creek..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Supplies / Resources Consumed:</label>
                <textarea name="supplies_used" class="form-control" rows="2" placeholder="e.g. 50 informational brochures, 2 Megaphones, 50 bottled waters..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Recommendations for Future Drills / Activities:</label>
                <textarea name="recommendations_for_improvement" class="form-control" rows="2" placeholder="e.g. Conduct night-time drill simulation, install additional hazard signage..."></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa fa-check-circle"></i> Save Evaluation & Mark Completed</button>
                <a href="index.php?route=preparedness" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
