<?php
/**
 * Admin Sidebar & Header Layout
 */

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

if (!isset($adminPageTitle)) {
    $adminPageTitle = 'Dashboard';
}
if (!isset($adminActiveTab)) {
    $adminActiveTab = 'dashboard';
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminPageTitle) ?> - Physics Magazine Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<!-- Sidebar Navigation -->
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <span>⚛</span> Physics Magazine
  </div>

  <ul class="sidebar-menu">
    <li>
      <a href="index.php" class="<?= $adminActiveTab === 'dashboard' ? 'active' : '' ?>">
        📊 Dashboard
      </a>
    </li>
    <li>
      <a href="articles.php" class="<?= $adminActiveTab === 'articles' ? 'active' : '' ?>">
        📝 All Articles
      </a>
    </li>
    <li>
      <a href="reorder.php" class="<?= $adminActiveTab === 'reorder' ? 'active' : '' ?>">
        🔀 Reorder & Archive
      </a>
    </li>
    <li>
      <a href="editorial.php" class="<?= $adminActiveTab === 'editorial' ? 'active' : '' ?>">
        📰 Homepage Editorial
      </a>
    </li>
    <li>
      <a href="comic.php" class="<?= $adminActiveTab === 'comic' ? 'active' : '' ?>">
        🎨 Comic Editor
      </a>
    </li>
    <li>
      <a href="about.php" class="<?= $adminActiveTab === 'about' ? 'active' : '' ?>">
        ℹ️ About Us & Video
      </a>
    </li>
    <li>
      <a href="article-add.php" class="<?= $adminActiveTab === 'article-add' ? 'active' : '' ?>">
        ➕ Add New Article
      </a>
    </li>
  </ul>

  <div class="sidebar-footer">
    <div>
      <strong><?= e($adminName) ?></strong>
    </div>
    <a href="logout.php">Log Out</a>
  </div>
</aside>

<!-- Main Area -->
<div class="admin-main">
  <div class="admin-topbar">
    <h1><?= e($adminPageTitle) ?></h1>
    <div class="topbar-actions">
      <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">
        🌐 View Live Website
      </a>
      <a href="reorder.php" class="btn btn-secondary btn-sm">
        🔀 Reorder
      </a>
      <a href="article-add.php" class="btn btn-primary btn-sm">
        + New Article
      </a>
    </div>
  </div>

  <div class="admin-content">
