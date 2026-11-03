<!-- views/errors/404.php -->
<div class="card" style="text-align: center; padding: 40px;">
    <h1 style="font-size: 3rem; color: var(--danger); margin-bottom: 12px;">404</h1>
    <h2 style="margin-bottom: 12px;">Page or Route Not Found</h2>
    <p style="color: var(--text-muted); margin-bottom: 24px;">The requested destination <code><?= htmlspecialchars($route ?? '') ?></code> does not exist.</p>
    <a href="index.php?route=dashboard" class="btn btn-primary"><i class="fa fa-home"></i> Return to Dashboard</a>
</div>
