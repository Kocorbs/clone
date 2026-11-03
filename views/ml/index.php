<!-- views/ml/index.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark);">Decision Tree Machine Learning Pipeline</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Supervised Decision Tree classification for disaster resource allocation prioritization.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=historical-records" class="btn btn-outline"><i class="fa fa-database"></i> Manage Datasets</a>
        <a href="index.php?route=ml-download-template" class="btn btn-outline"><i class="fa fa-download"></i> CSV Template</a>
    </div>
</div>

<div class="alert alert-info">
    <i class="fa fa-shield-halved"></i>
    <div>
        <strong>Research Integrity Protocol (Chapter 1 & 3):</strong> Model evaluation metrics must only be computed from genuine, verified evaluation sets. Synthetic demonstration records are kept explicitly separated and labeled. If verified historical incident data are insufficient, the model state is declared as <strong>Not Trained — Insufficient Verified Data</strong>.
    </div>
</div>

<!-- Active Model Status Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-microchip"></i> Active Model Status & Verification</div>
        <?php if ($activeModel): ?>
            <span class="badge badge-success"><i class="fa fa-check-circle"></i> Model Loaded & Operational</span>
        <?php else: ?>
            <span class="badge badge-danger"><i class="fa fa-circle-exclamation"></i> Not Trained — Insufficient Verified Data</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($activeModel): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <div class="stat-card">
                    <div class="stat-label">Model Version</div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary-dark); margin: 6px 0;"><?= htmlspecialchars($activeModel['version_name']) ?></div>
                    <div class="stat-sub"><?= htmlspecialchars($activeModel['algorithm']) ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Dataset Source</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin: 6px 0;"><?= htmlspecialchars($activeModel['dataset_source']) ?></div>
                    <div class="stat-sub">Train: <?= (int)$activeModel['training_samples_count'] ?> | Test: <?= (int)$activeModel['test_samples_count'] ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Test Set Accuracy</div>
                    <div class="stat-value" style="color: var(--success);"><?= round((float)$activeModel['accuracy'] * 100, 2) ?>%</div>
                    <div class="stat-sub">Held-out 20% test samples</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Weighted F1-Score</div>
                    <div class="stat-value" style="color: var(--primary-light);"><?= round((float)$activeModel['f1_score'] * 100, 2) ?>%</div>
                    <div class="stat-sub">Precision: <?= round((float)$activeModel['precision_score'] * 100, 2) ?>% | Recall: <?= round((float)$activeModel['recall_score'] * 100, 2) ?>%</div>
                </div>
            </div>

            <!-- Confusion Matrix Display -->
            <div class="grid-cols-2">
                <div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;">
                        <i class="fa fa-table-cells"></i> 4x4 Confusion Matrix (Held-out Test Evaluation)
                    </h4>
                    <?php 
                        $cm = json_decode($activeModel['confusion_matrix_json'], true) ?: [];
                        $classes = ['Urgent', 'High', 'Medium', 'Low'];
                    ?>
                    <div class="table-responsive">
                        <table class="table" style="text-align: center;">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">Actual \ Pred</th>
                                    <th>Urgent</th>
                                    <th>High</th>
                                    <th>Medium</th>
                                    <th>Low</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $act): ?>
                                    <tr>
                                        <th style="text-align: left; background: #f8fafc;"><?= $act ?></th>
                                        <?php foreach ($classes as $pred): ?>
                                            <td style="<?= $act === $pred ? 'background: #dcfce7; font-weight: 800;' : '' ?>">
                                                <?= (int)($cm[$act][$pred] ?? 0) ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;">
                        <i class="fa fa-chart-column"></i> Decision Tree Feature Importances
                    </h4>
                    <div style="background: #ffffff; padding: 12px; border: 1px solid var(--border); border-radius: 6px;">
                        <?php if (!empty($modelJson['feature_importances'])): ?>
                            <?php foreach ($modelJson['feature_importances'] as $feat => $imp): ?>
                                <div style="margin-bottom: 8px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 2px;">
                                        <strong><?= htmlspecialchars($feat) ?></strong>
                                        <span><?= number_format($imp * 100, 2) ?>%</span>
                                    </div>
                                    <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                                        <div style="background: var(--primary-light); height: 100%; width: <?= ($imp * 100) ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="font-size: 0.82rem; color: var(--text-muted);">Feature importances calculated during tree induction.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="padding: 24px; text-align: center; background: #f8fafc; border-radius: 6px;">
                <div style="font-size: 2.5rem; color: var(--warning); margin-bottom: 8px;"><i class="fa fa-triangle-exclamation"></i></div>
                <h3 style="color: var(--text-main); margin-bottom: 6px;">Model Not Trained — Insufficient Verified Data</h3>
                <p style="color: var(--text-muted); font-size: 0.88rem; max-width: 500px; margin: 0 auto 16px auto;">
                    No model is currently active for verified historical records. To comply with thesis requirements, you can train on the separated Demonstration Environment for experimental verification, or import official records.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Model Retraining Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-rotate"></i> Train / Update Decision Tree Pipeline</div>
    </div>
    <div class="card-body">
        <form action="index.php?route=ml-train" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Training Dataset Source *</label>
                    <select name="dataset_source" class="form-control" required>
                        <option value="verified">Verified Official Records (<?= $verifiedCount ?> records available)</option>
                        <option value="demo" selected>Synthetic Demonstration Dataset (Demo Environment — <?= $syntheticCount ?> records)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Decision Tree Maximum Depth</label>
                    <input type="number" name="max_depth" class="form-control" min="2" max="10" value="5">
                    <small style="color: var(--text-muted);">Limits tree depth to avoid overfitting (Default: 5).</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" onclick="return confirm('Initiate Decision Tree training?');">
                <i class="fa fa-play"></i> Execute Decision Tree Training Pipeline
            </button>
        </form>
    </div>
</div>
