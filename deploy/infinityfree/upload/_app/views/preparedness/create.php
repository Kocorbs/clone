<!-- views/preparedness/create.php -->
<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-calendar-plus"></i> Schedule Preparedness Activity</div>
    </div>
    <div class="card-body">
        <form action="index.php?route=preparedness-create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Activity Type *</label>
                <select name="activity_type" class="form-control" required>
                    <option value="Disaster Drill">Disaster Drill (Flood Evacuation Simulation)</option>
                    <option value="Training & Seminar">Training & Seminar (First Aid, BLS, Rescuer)</option>
                    <option value="Information Campaign">Information Campaign (Early Warning & Kit Prep)</option>
                    <option value="Evacuation Simulation">Evacuation Simulation</option>
                    <option value="Community Meeting">Community DRRM Meeting</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Activity Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Mandulog River Early Warning Simulation Drill" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Objectives *</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Outline the activity purpose, target participants, and procedure..." required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Target Purok (Optional)</label>
                    <select name="target_purok_id" class="form-control">
                        <option value="">-- All Hinaplanon Puroks --</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['purok_id'] ?>"><?= htmlspecialchars($p['purok_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Estimated Attendees Capacity</label>
                    <input type="number" name="max_participants" class="form-control" min="10" value="100">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Schedule Date & Time *</label>
                    <input type="datetime-local" name="schedule_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Venue / Assembly Location *</label>
                    <input type="text" name="location" class="form-control" placeholder="e.g. Hinaplanon Barangay Gym / Covered Court" required>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Schedule Activity</button>
                <a href="index.php?route=preparedness" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
