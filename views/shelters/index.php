<!-- views/shelters/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark);">Evacuation Shelter Management & Monitoring</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Real-time tracking of capacity, occupancy, and availability across Barangay Hinaplanon.</p>
    </div>
    <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('addShelterCard').style.display = document.getElementById('addShelterCard').style.display === 'none' ? 'block' : 'none';">
            <i class="fa fa-plus-circle"></i> Register New Shelter
        </button>
    <?php endif; ?>
</div>

<!-- Shelter KPI Cards -->
<div class="grid-cols-4">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-label">Total Shelters</div>
            <div class="stat-icon" style="background:#eff6ff; color:#2563eb;"><i class="fa fa-building-shield"></i></div>
        </div>
        <div class="stat-value"><?= (int)$summary['total_shelters'] ?></div>
        <div class="stat-sub">Official designated evacuation centers</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-label">Total Capacity</div>
            <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;"><i class="fa fa-users"></i></div>
        </div>
        <div class="stat-value"><?= number_format((int)$summary['total_capacity']) ?></div>
        <div class="stat-sub">Max evacuee capacity combined</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-label">Current Occupancy</div>
            <div class="stat-icon" style="background:#fffbeb; color:#d97706;"><i class="fa fa-bed"></i></div>
        </div>
        <div class="stat-value" style="color: var(--primary);"><?= number_format((int)$summary['total_occupancy']) ?></div>
        <div class="stat-sub"><?= $summary['occupancy_rate'] ?>% total occupancy rate</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-label">Available Slots</div>
            <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;"><i class="fa fa-person-shelter"></i></div>
        </div>
        <div class="stat-value" style="color: var(--success);"><?= number_format((int)$summary['available_slots']) ?></div>
        <div class="stat-sub"><?= (int)$summary['open_count'] ?> Open shelters | <?= (int)$summary['full_count'] ?> Full</div>
    </div>
</div>

<!-- Add Shelter Collapsible Form -->
<?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
    <div class="card" id="addShelterCard" style="display: none; border: 2px solid var(--primary-light);">
        <div class="card-header" style="background: var(--primary-surface);">
            <div class="card-title"><i class="fa fa-plus-circle text-primary"></i> Register New Evacuation Center</div>
        </div>
        <div class="card-body">
            <form action="index.php?route=shelters-create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Shelter Facility Name *</label>
                        <input type="text" name="shelter_name" class="form-control" placeholder="e.g. Hinaplanon Central High School Covered Gym" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Purok Location *</label>
                        <select name="purok_id" class="form-control" required>
                            <option value="">-- Select Purok --</option>
                            <?php foreach ($puroks as $p): ?>
                                <option value="<?= $p['purok_id'] ?>"><?= htmlspecialchars($p['purok_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Physical Address / Vicinity *</label>
                        <input type="text" name="location" class="form-control" placeholder="Street, Purok, Landmark..." required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Maximum Capacity (Persons) *</label>
                        <input type="number" name="max_capacity" class="form-control" min="10" value="200" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Contact Person / Shelter Manager</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="Name of in-charge">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="tel" name="contact_number" class="form-control" placeholder="0917-000-0000">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Latitude Coordinates (Optional)</label>
                        <input type="number" step="any" name="latitude" class="form-control" placeholder="8.2412">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Longitude Coordinates (Optional)</label>
                        <input type="number" step="any" name="longitude" class="form-control" placeholder="124.2543">
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Shelter</button>
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('addShelterCard').style.display='none';">Cancel</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Shelters Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-building-shield text-primary"></i> Designated Evacuation Centers</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Facility Name</th>
                    <th>Purok & Risk</th>
                    <th>Address / Vicinity</th>
                    <th>Occupancy / Capacity</th>
                    <th>Status</th>
                    <th>Focal Person</th>
                    <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head', 'Responder'])): ?>
                        <th>Update Occupancy</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shelters as $s): ?>
                    <?php 
                        $pct = (int)$s['max_capacity'] > 0 ? round(((int)$s['current_occupancy'] / (int)$s['max_capacity']) * 100) : 0;
                        $barColor = $pct >= 90 ? 'var(--danger)' : ($pct >= 70 ? 'var(--warning)' : 'var(--success)');
                    ?>
                    <tr>
                        <td>
                            <strong style="font-size: 0.95rem; color: var(--primary-dark);"><?= htmlspecialchars($s['shelter_name']) ?></strong>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($s['purok_name']) ?></div>
                            <?= View::riskBadge($s['purok_risk'] ?? 'Pending') ?>
                        </td>
                        <td><small style="color: var(--text-secondary);"><?= htmlspecialchars($s['location']) ?></small></td>
                        <td style="min-width: 160px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 3px;">
                                <strong><?= (int)$s['current_occupancy'] ?> / <?= (int)$s['max_capacity'] ?></strong>
                                <span><?= $pct ?>%</span>
                            </div>
                            <div style="background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden;">
                                <div style="background: <?= $barColor ?>; height: 100%; width: <?= min(100, $pct) ?>%;"></div>
                            </div>
                        </td>
                        <td><?= View::statusBadge($s['availability_status']) ?></td>
                        <td>
                            <div style="font-size: 0.82rem; font-weight: 600;"><?= htmlspecialchars($s['contact_person'] ?? '—') ?></div>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($s['contact_number'] ?? '—') ?></small>
                        </td>
                        <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head', 'Responder'])): ?>
                            <td>
                                <form action="index.php?route=shelters-update" method="POST" style="display: flex; gap: 4px; align-items: center;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="shelter_id" value="<?= $s['shelter_id'] ?>">
                                    <input type="number" name="current_occupancy" class="form-control" style="width: 75px; padding: 4px 6px; font-size: 0.8rem;" min="0" max="<?= (int)$s['max_capacity'] ?>" value="<?= (int)$s['current_occupancy'] ?>" required>
                                    <select name="availability_status" class="form-control" style="width: 100px; padding: 4px 6px; font-size: 0.78rem;">
                                        <option value="Open" <?= $s['availability_status'] === 'Open' ? 'selected' : '' ?>>Open</option>
                                        <option value="Near Full" <?= $s['availability_status'] === 'Near Full' ? 'selected' : '' ?>>Near Full</option>
                                        <option value="Full" <?= $s['availability_status'] === 'Full' ? 'selected' : '' ?>>Full</option>
                                        <option value="Closed" <?= $s['availability_status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm" title="Save updated occupancy">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
