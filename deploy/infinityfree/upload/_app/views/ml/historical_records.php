<!-- views/ml/historical_records.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark);">Historical Disaster Incident Records</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Training datasets for the supervised Decision Tree priority classifier.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=ml-download-template" class="btn btn-outline"><i class="fa fa-download"></i> Download CSV Template</a>
        <a href="index.php?route=ml-model" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Model Pipeline</a>
    </div>
</div>

<div class="grid-cols-2" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-label">Verified Official Historical Records</div>
        <div class="stat-value" style="color: var(--primary-dark);"><?= (int)$verifiedCount ?></div>
        <div class="stat-sub">Official disaster records verified with qualified DRRM personnel</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Synthetic Demonstration Records</div>
        <div class="stat-value" style="color: var(--warning);"><?= (int)$syntheticCount ?></div>
        <div class="stat-sub">Explicitly separated for demonstration & validation purposes</div>
    </div>
</div>

<!-- CSV Upload Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-file-csv"></i> Import Historical Disaster Records (CSV)</div>
    </div>
    <div class="card-body">
        <form action="index.php?route=historical-import" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Select Validated CSV File *</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem;">Must contain required schema columns matching the template.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Record Classification *</label>
                    <select name="is_synthetic" class="form-control" required>
                        <option value="0">Verified Official Records (BDRRMC / ICDRRMC Certified)</option>
                        <option value="1">Synthetic Demonstration Scenario (Demo Environment)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 8px;">
                <i class="fa fa-upload"></i> Upload & Validate CSV
            </button>
        </form>
    </div>
</div>

<!-- Records Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-database"></i> Stored Dataset Records (<?= count($records) ?>)</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Purok</th>
                    <th>Risk</th>
                    <th>Severity</th>
                    <th>HH</th>
                    <th>Priority Label</th>
                    <th>Data Source</th>
                    <th>Environment</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="9" style="text-align: center; color: var(--text-muted);">No records currently stored.</td></tr>
                <?php else: ?>
                    <?php foreach (array_slice($records, 0, 50) as $r): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($r['disaster_date'])) ?></td>
                            <td><?= htmlspecialchars($r['disaster_type']) ?></td>
                            <td><strong><?= htmlspecialchars($r['affected_purok']) ?></strong></td>
                            <td><?= View::riskBadge($r['purok_risk_level']) ?></td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($r['severity_level']) ?></span></td>
                            <td><?= (int)$r['affected_households'] ?></td>
                            <td><?= View::priorityBadge($r['priority_label']) ?></td>
                            <td><small style="color: var(--text-muted);"><?= htmlspecialchars($r['data_source']) ?></small></td>
                            <td>
                                <?php if ($r['is_synthetic']): ?>
                                    <span class="badge badge-warning">Synthetic Demo</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Verified Official</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
