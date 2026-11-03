<!-- views/layouts/auth.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Sign In') ?> — Hinaplanon DRRM</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page-wrapper">
    <div class="auth-box">
        <div class="auth-header">
            <h2><i class="fa fa-shield-halved text-primary"></i> Hinaplanon DRRM</h2>
            <p>Intelligent Resource Allocation Recommender</p>
        </div>

        <?php if (!empty($flashes)): ?>
            <?php foreach ($flashes as $type => $messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="alert alert-<?= htmlspecialchars($type) ?>">
                        <div><?= htmlspecialchars($msg) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?= $content ?>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
