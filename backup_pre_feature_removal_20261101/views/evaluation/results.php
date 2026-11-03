<!-- views/evaluation/results.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark);">TAM & ISO 9126 Statistical Analysis</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Automated Weighted Mean, Standard Deviation, and Likert Verbal Interpretation (St. Peter's College, 2026).
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=evaluation-form" class="btn btn-primary"><i class="fa fa-pen-to-square"></i> Take Evaluation Survey</a>
        <a href="index.php?route=evaluation-export" class="btn btn-outline"><i class="fa fa-file-csv"></i> Export Survey CSV</a>
    </div>
</div>

<div class="grid-cols-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-label">Total Survey Responses (N)</div>
        <div class="stat-value"><?= (int)$stats['total_respondents'] ?></div>
        <div class="stat-sub">Thesis target: 100 Total Enumeration</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Perceived Usefulness (PU)</div>
        <div class="stat-value" style="color: var(--primary);"><?= number_format($stats['pu']['mean'], 2) ?></div>
        <div class="stat-sub">SD: <?= number_format($stats['pu']['sd'], 2) ?> — <strong><?= htmlspecialchars($stats['pu']['rating']) ?></strong></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Perceived Ease of Use (PEOU)</div>
        <div class="stat-value" style="color: var(--secondary);"><?= number_format($stats['peou']['mean'], 2) ?></div>
        <div class="stat-sub">SD: <?= number_format($stats['peou']['sd'], 2) ?> — <strong><?= htmlspecialchars($stats['peou']['rating']) ?></strong></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">ISO 9126 Software Quality</div>
        <div class="stat-value" style="color: var(--success);"><?= number_format($stats['overall_quality']['mean'], 2) ?></div>
        <div class="stat-sub">Overall Rating: <strong><?= htmlspecialchars($stats['overall_quality']['rating']) ?></strong></div>
    </div>
</div>

<div class="grid-cols-2">
    <!-- ISO 9126 Characteristics Breakdown -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-chart-bar"></i> ISO 9126 Software Quality Evaluation Table</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Quality Dimension</th>
                        <th>Weighted Mean</th>
                        <th>Std. Dev (SD)</th>
                        <th>Verbal Interpretation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $labels = [
                            'functionality' => '3.1 Functionality',
                            'reliability' => '3.2 Reliability',
                            'usability' => '3.3 Usability',
                            'efficiency' => '3.4 Efficiency',
                            'maintainability' => '3.5 Maintainability',
                            'portability' => '3.6 Portability'
                        ];
                        foreach ($labels as $k => $lbl):
                            $row = $stats['iso'][$k] ?? ['mean' => 0.0, 'sd' => 0.0, 'rating' => 'N/A'];
                    ?>
                        <tr>
                            <td><strong><?= $lbl ?></strong></td>
                            <td><strong style="color: var(--primary);"><?= number_format($row['mean'], 2) ?></strong></td>
                            <td><?= number_format($row['sd'], 2) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($row['rating']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Respondent Demographics & Likert Scale Reference -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-users"></i> Respondent Distribution & Scoring Reference</div>
        </div>
        <div class="card-body">
            <h4 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 8px;">Respondent Participation (Total Enumeration Sampling):</h4>
            <div style="background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border); margin-bottom: 16px; font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>BDRRM Head:</span>
                    <strong><?= $stats['breakdown']['BDRRM Head'] ?? 0 ?> / 1 target</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>Barangay Officials involved in DRRM:</span>
                    <strong><?= $stats['breakdown']['Barangay Official'] ?? 0 ?> / 15 target</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>Personnel / Responders / Volunteers:</span>
                    <strong><?= $stats['breakdown']['Personnel/Volunteer'] ?? 0 ?> / 20 target</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Residents participating in preparedness:</span>
                    <strong><?= $stats['breakdown']['Resident'] ?? 0 ?> / 64 target</strong>
                </div>
            </div>

            <h4 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 8px;">Thesis Scoring Scale & Interpretation:</h4>
            <div class="table-responsive">
                <table class="table" style="font-size: 0.78rem;">
                    <thead><tr><th>Scale Range</th><th>Verbal Interpretation</th><th>Descriptive Rating</th></tr></thead>
                    <tbody>
                        <tr><td>4.21 – 5.00</td><td>Strongly Agree</td><td>Excellent</td></tr>
                        <tr><td>3.41 – 4.20</td><td>Agree</td><td>Very Satisfactory</td></tr>
                        <tr><td>2.61 – 3.40</td><td>Neutral</td><td>Satisfactory</td></tr>
                        <tr><td>1.81 – 2.60</td><td>Disagree</td><td>Fair</td></tr>
                        <tr><td>1.00 – 1.80</td><td>Strongly Disagree</td><td>Poor</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
