<?php
/**
 * All Articles List & Management
 */

$adminPageTitle = 'Manage Articles';
$adminActiveTab = 'articles';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = get_db_connection();

$search = trim($_GET['q'] ?? '');
$batchFilter = trim($_GET['batch'] ?? '');
$yearFilter = trim($_GET['year'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$query = "SELECT * FROM articles WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR author_name LIKE ? OR summary LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($batchFilter)) {
    $query .= " AND author_batch = ?";
    $params[] = $batchFilter;
}

if (!empty($yearFilter)) {
    $query .= " AND edition_year = ?";
    $params[] = (int)$yearFilter;
}

if (!empty($statusFilter)) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY edition_year DESC, sort_order ASC, id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Get distinct batches and years for filter dropdowns
$batches = $pdo->query("SELECT DISTINCT author_batch FROM articles WHERE author_batch != '' ORDER BY author_batch ASC")->fetchAll(PDO::FETCH_COLUMN);
$years = $pdo->query("SELECT DISTINCT edition_year FROM articles ORDER BY edition_year DESC")->fetchAll(PDO::FETCH_COLUMN);
?>

<?php if (isset($_SESSION['flash_msg'])): ?>
  <div class="alert alert-success">
    <?= e($_SESSION['flash_msg']) ?>
    <?php unset($_SESSION['flash_msg']); ?>
  </div>
<?php endif; ?>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem;">
  <div class="card-body" style="padding: 1rem 1.5rem;">
    <form method="GET" action="articles.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search title or author..." class="form-control" style="max-width: 250px;">
      
      <select name="batch" class="form-control" style="max-width: 160px;">
        <option value="">All Batches</option>
        <?php foreach ($batches as $b): ?>
          <option value="<?= e($b) ?>" <?= $batchFilter === $b ? 'selected' : '' ?>><?= e($b) ?></option>
        <?php endforeach; ?>
      </select>

      <select name="year" class="form-control" style="max-width: 140px;">
        <option value="">All Years</option>
        <?php foreach ($years as $y): ?>
          <option value="<?= e($y) ?>" <?= $yearFilter == $y ? 'selected' : '' ?>><?= e($y) ?></option>
        <?php endforeach; ?>
      </select>

      <select name="status" class="form-control" style="max-width: 140px;">
        <option value="">All Status</option>
        <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft / Archived</option>
      </select>

      <button type="submit" class="btn btn-secondary">Filter</button>
      <?php if (!empty($search) || !empty($batchFilter) || !empty($yearFilter) || !empty($statusFilter)): ?>
        <a href="articles.php" class="btn btn-secondary">Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Articles Table -->
<div class="card">
  <div class="card-header">
    <h2>Articles (<?= count($articles) ?>)</h2>
    <div style="display: flex; gap: 0.5rem;">
      <a href="reorder.php" class="btn btn-secondary btn-sm">🔀 Drag & Drop Reorder</a>
      <a href="article-add.php" class="btn btn-primary btn-sm">+ Add New Article</a>
    </div>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 50px;">Order</th>
          <th>Thumb</th>
          <th>Title & Excerpt</th>
          <th>Author & Batch</th>
          <th>Edition</th>
          <th>Published Date</th>
          <th>Views</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($articles)): ?>
          <?php foreach ($articles as $art): ?>
            <tr>
              <td style="text-align: center; font-weight: bold; color: #64748b;">
                #<?= (int)$art['sort_order'] ?>
              </td>
              <td style="width: 70px;">
                <img src="../<?= e($art['image_path']) ?>" alt="" style="width: 60px; height: 42px; object-fit: cover; border-radius: 4px;">
              </td>
              <td>
                <strong style="color: #1e293b; font-size: 1rem;"><?= e($art['title']) ?></strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.2rem;"><?= e(mb_strimwidth($art['summary'], 0, 80, '...')) ?></p>
              </td>
              <td>
                <div><?= e($art['author_name']) ?></div>
                <span class="badge badge-batch"><?= e($art['author_batch']) ?></span>
              </td>
              <td><?= e($art['edition_year']) ?></td>
              <td style="font-size: 0.85rem; color: #64748b;"><?= e($art['published_date']) ?></td>
              <td><?= (int)$art['views_count'] ?></td>
              <td>
                <span class="badge <?= $art['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                  <?= ($art['status'] === 'published') ? 'Published' : 'Archived' ?>
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 0.4rem;">
                  <a href="article-edit.php?id=<?= $art['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                  <a href="../article.php?slug=<?= urlencode($art['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">View</a>
                  <form method="POST" action="article-delete.php" onsubmit="return confirm('Are you sure you want to delete this article?');" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    <input type="hidden" name="id" value="<?= $art['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="text-align: center; color: #64748b; padding: 2.5rem;">
              No articles match the current filter.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
