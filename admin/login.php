<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Physics Wall Magazine</title>
  <link rel="stylesheet" href="assets/css/admin.css">
  <style>
    body {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 1rem;
    }
    .login-box {
      background: white;
      border-radius: 12px;
      padding: 2.5rem;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    .login-header img {
      height: 70px;
      margin-bottom: 1rem;
    }
    .login-header h2 {
      font-size: 1.4rem;
      color: #0f172a;
    }
    .login-header p {
      color: #64748b;
      font-size: 0.9rem;
      margin-top: 0.25rem;
    }
    .login-footer {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.85rem;
      color: #64748b;
    }
    .login-footer a {
      color: #b45309;
      text-decoration: none;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="login-box">
  <div class="login-header">
    <img src="../assets/images/rkm.jpg" alt="RKMV Logo">
    <h2>Department of Physics</h2>
    <p>Wall Magazine Administration</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="e.g. admin">
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem; font-size: 1rem; margin-top: 1rem;">
      Log In &rarr;
    </button>
  </form>

  <div class="login-footer">
    <a href="../index.php">&larr; Return to Public Website</a>
  </div>
</div>

</body>
</html>
