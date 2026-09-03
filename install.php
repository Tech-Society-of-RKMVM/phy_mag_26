<?php
/**
 * 1-Click Database Setup & Migrator
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/database/seed_articles.php';

$message = '';
$isSuccess = false;

try {
    $pdo = get_db_connection();
    seed_initial_database($pdo);
    $isSuccess = true;
    $message = "Database `phy_mag_db` and all tables initialized successfully with 10 articles and default admin!";
} catch (Exception $e) {
    $isSuccess = false;
    $message = "Installation failed: " . $e->getMessage();
}

if (php_sapi_name() === 'cli') {
    echo ($isSuccess ? "[SUCCESS] " : "[ERROR] ") . $message . PHP_EOL;
    exit($isSuccess ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Physics Wall Magazine</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); max-width: 550px; text-align: center; }
        .success { color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1rem; border-radius: 8px; margin: 1.5rem 0; }
        .error { color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; margin: 1.5rem 0; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #1e293b; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 0.5rem; transition: 0.2s; }
        .btn:hover { background: #0f172a; }
        .btn-gold { background: #b45309; }
        .btn-gold:hover { background: #92400e; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Department of Physics Wall Magazine</h2>
        <h3>Database Installation & Seeding</h3>
        <div class="<?= $isSuccess ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php if ($isSuccess): ?>
            <p><strong>Default Admin Login:</strong><br>Username: <code>admin</code><br>Password: <code>admin123</code></p>
            <div style="margin-top: 1.5rem;">
                <a href="index.php" class="btn btn-gold">View Website &rarr;</a>
                <a href="admin/index.php" class="btn">Admin Panel &rarr;</a>
            </div>
        <?php else: ?>
            <p>Please check your MySQL service in XAMPP and refresh this page.</p>
        <?php endif; ?>
    </div>
</body>
</html>
