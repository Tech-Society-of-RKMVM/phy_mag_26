<?php
/**
 * Admin Dashboard
 */

$adminPageTitle = 'Dashboard Overview';
$adminActiveTab = 'dashboard';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = get_db_connection();

// Get counts
$totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$publishedCount = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
$draftCount = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'draft'")->fetchColumn();
$totalViews = $pdo->query("SELECT SUM(views_count) FROM articles")->fetchColumn() ?: 0;

// Batch distribution
$batchStmt = $pdo->query("SELECT author_batch, COUNT(*) as cnt FROM articles GROUP BY author_batch ORDER BY cnt DESC");
$batchStats = $batchStmt->fetchAll();

// Recent articles
$recentStmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 6");
$recentArticles = $recentStmt->fetchAll();

$editorialTitle = get_setting('editorial_title', 'The Editorial');
?>

<!-- Quick Action Banner -->
<div class="card" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; margin-bottom: 2rem;">
  <div class="card-body" style="padding: 1.75rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: gap: 1.5rem;">
    <div>
      <h2 style="color: white; font-size: 1.35rem; margin-bottom: 0.35rem;">Physics Department Magazine Administration</h2>
      <p style="color: #94a3b8; font-size: 0.95rem;">Manage publications, reorder article layout dynamically, and update editorial content.</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
      <a href="editorial.php" class="btn btn-primary" style="background: #b45309; border: none;">
        📰 Edit Editorial
      </a>
      <a href="reorder.php" class="btn btn-secondary">
        🔀 Drag & Drop Reorder
      </a>
      <a href="article-add.php" class="btn btn-primary">
        + New Article
      </a>
    </div>
  </div>
</div>

<!-- Metric Cards -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-title">Total Articles</div>
    <div class="stat-value"><?= (int)$totalArticles ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-title">Published (Active)</div>
    <div class="stat-value" style="color: #16a34a;"><?= (int)$publishedCount ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-title">Archived / Drafts</div>
    <div class="stat-value" style="color: #d97706;"><?= (int)$draftCount ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-title">Total Article Views</div>
    <div class="stat-value" style="color: #2563eb;"><?= (int)$totalViews ?></div>
  </div>
</div>

<!-- Quick Batch Breakdown -->
<div class="card">
  <div class="card-header">
    <h2>Student Batch Contribution Breakdown</h2>
  </div>
  <div class="card-body">
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
      <?php if (!empty($batchStats)): ?>
        <?php foreach ($batchStats as $b): ?>
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-weight: 700; color: #1e293b;"><?= e($b['author_batch'] ?: 'Unassigned') ?></span>
            <span class="badge badge-info"><?= (int)$b['cnt'] ?> Articles</span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color: #64748b;">No batches recorded.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Recent Articles Table -->
<div class="card">
  <div class="card-header">
    <h2>Recent Articles</h2>
    <div style="display: flex; gap: 0.5rem;">
      <a href="reorder.php" class="btn btn-secondary btn-sm">🔀 Reorder Grid</a>
      <a href="articles.php" class="btn btn-secondary btn-sm">View All Articles &rarr;</a>
    </div>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Thumbnail</th>
          <th>Article Title</th>
          <th>Author</th>
          <th>Batch</th>
          <th>Status</th>
          <th>Edition</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($recentArticles)): ?>
          <?php foreach ($recentArticles as $art): ?>
            <tr>
              <td style="font-weight: bold; color: #64748b;">#<?= (int)$art['sort_order'] ?></td>
              <td style="width: 70px;">
                <img src="../<?= e($art['image_path']) ?>" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
              </td>
              <td>
                <strong><?= e($art['title']) ?></strong>
              </td>
              <td><?= e($art['author_name']) ?></td>
              <td><span class="badge badge-batch"><?= e($art['author_batch']) ?></span></td>
              <td>
                <span class="badge <?= $art['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                  <?= ($art['status'] === 'published') ? 'Published' : 'Archived' ?>
                </span>
              </td>
              <td><?= e($art['edition_year']) ?></td>
              <td>
                <a href="article-edit.php?id=<?= $art['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                <a href="../article.php?slug=<?= urlencode($art['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">Preview</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align: center; color: #64748b; padding: 2rem;">No articles found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
