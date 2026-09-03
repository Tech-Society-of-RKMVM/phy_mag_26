<?php
/**
 * Edit Existing Article
 */

$adminPageTitle = 'Edit Article';
$adminActiveTab = 'articles';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = get_db_connection();
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
  header('Location: articles.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
  $_SESSION['flash_msg'] = 'Article not found.';
  header('Location: articles.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!validate_csrf_token($csrf)) {
    $error = 'Invalid CSRF token. Please refresh and try again.';
  } else {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $author_name = trim($_POST['author_name'] ?? '');

    $author_batch_select = trim($_POST['author_batch_select'] ?? '');
    $author_batch_custom = trim($_POST['author_batch_custom'] ?? '');
    $author_batch = ($author_batch_select === 'custom') ? $author_batch_custom : $author_batch_select;

    $edition_year = (int) ($_POST['edition_year'] ?? 2026);
    $published_date = !empty($_POST['published_date']) ? $_POST['published_date'] : $article['published_date'];
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if (empty($slug)) {
      $slug = generate_slug($title);
    } else {
      $slug = generate_slug($slug);
    }

    // Image Handling
    $image_path = $article['image_path']; // preserve existing by default
    $existing_asset = trim($_POST['existing_asset'] ?? '');

    if (!empty($_FILES['image_file']['name'])) {
      $uploadRes = handle_image_upload('image_file');
      if ($uploadRes['success']) {
        $image_path = $uploadRes['path'];
      } else {
        $error = 'Image upload error: ' . $uploadRes['error'];
      }
    } elseif (!empty($existing_asset)) {
      $image_path = $existing_asset;
    }

    if (empty($title) || empty($author_name) || empty($content)) {
      $error = 'Please fill in Title, Author Name, and Article Content.';
    }

    if (empty($error)) {
      // Check for duplicate slug on other articles
      $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ? AND id != ?");
      $checkStmt->execute([$slug, $id]);
      if ($checkStmt->fetchColumn() > 0) {
        $slug .= '-' . time();
      }

      $updateStmt = $pdo->prepare("
                UPDATE articles 
                SET slug = ?, title = ?, summary = ?, author_name = ?, author_batch = ?, 
                    image_path = ?, content = ?, edition_year = ?, published_date = ?, 
                    status = ?, sort_order = ?
                WHERE id = ?
            ");

      try {
        $updateStmt->execute([
          $slug,
          $title,
          $summary,
          $author_name,
          $author_batch,
          $image_path,
          $content,
          $edition_year,
          $published_date,
          $status,
          $sort_order,
          $id
        ]);

        $_SESSION['flash_msg'] = "Article '{$title}' updated successfully!";
        header('Location: articles.php');
        exit;
      } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
      }
    }
  }
}

// Fetch existing assets for quick selection
$availableImages = glob(__DIR__ . '/../../assets/images/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

$commonBatches = ['UG 1', 'UG 2', 'UG 3', 'PG 1', 'PG 2', 'Faculty', 'Alumni'];
$isCustomBatch = !in_array($article['author_batch'], $commonBatches) && !empty($article['author_batch']);
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
  <div class="card-header">
    <h2>Edit Magazine Article: #<?= $article['id'] ?></h2>
    <div>
      <a href="../article.php?slug=<?= urlencode($article['slug']) ?>" target="_blank"
        class="btn btn-secondary btn-sm">Preview Public Page</a>
      <a href="articles.php" class="btn btn-secondary btn-sm">&larr; Back to Articles</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="article-edit.php?id=<?= $article['id'] ?>" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

      <div class="form-group">
        <label for="title">Article Title <span style="color: red;">*</span></label>
        <input type="text" id="title" name="title" class="form-control" required value="<?= e($article['title']) ?>">
      </div>

      <div class="form-group">
        <label for="slug">URL Slug</label>
        <input type="text" id="slug" name="slug" data-autogen="false" class="form-control"
          value="<?= e($article['slug']) ?>">
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="author_name">Author Name <span style="color: red;">*</span></label>
          <input type="text" id="author_name" name="author_name" class="form-control" required
            value="<?= e($article['author_name']) ?>">
        </div>

        <div class="form-group">
          <label for="author_batch_select">Author Batch Year <span style="color: red;">*</span></label>
          <select id="author_batch_select" name="author_batch_select" class="form-control">
            <?php foreach ($commonBatches as $b): ?>
              <option value="<?= e($b) ?>" <?= ($article['author_batch'] === $b) ? 'selected' : '' ?>><?= e($b) ?></option>
            <?php endforeach; ?>
            <option value="custom" <?= $isCustomBatch ? 'selected' : '' ?>>Other / Custom...</option>
          </select>
          <input type="text" id="author_batch_custom" name="author_batch_custom" class="form-control"
            style="display: <?= $isCustomBatch ? 'block' : 'none' ?>; margin-top: 0.5rem;"
            placeholder="e.g. Research Scholar" value="<?= e($article['author_batch']) ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="summary">Short Summary / Tagline</label>
        <input type="text" id="summary" name="summary" class="form-control" value="<?= e($article['summary']) ?>">
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="edition_year">Edition Year</label>
          <input type="number" id="edition_year" name="edition_year" class="form-control"
            value="<?= e($article['edition_year']) ?>">
        </div>

        <div class="form-group">
          <label for="published_date">Published Date</label>
          <input type="date" id="published_date" name="published_date" class="form-control"
            value="<?= e($article['published_date']) ?>">
        </div>

        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status" class="form-control">
            <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
          </select>
        </div>

        <div class="form-group">
          <label for="sort_order">Display Order</label>
          <input type="number" id="sort_order" name="sort_order" class="form-control"
            value="<?= e($article['sort_order']) ?>">
        </div>
      </div>

      <!-- Featured Image Handling -->
      <div class="form-group"
        style="background: #f8fafc; border: 1px solid var(--border); padding: 1.25rem; border-radius: 8px;">
        <label>Current Featured Image:</label>
        <div class="image-preview-container" style="margin-bottom: 1rem;">
          <img src="../<?= e($article['image_path']) ?>" alt="" class="image-preview-thumb">
          <span style="font-size: 0.85rem; color: #64748b;">Path: <code><?= e($article['image_path']) ?></code></span>
        </div>

        <label for="image_file">Upload New Image <span style="color: #64748b; font-weight: normal;">(optional, replaces
            current image)</span></label>
        <input type="file" id="image_file" name="image_file" class="form-control" accept="image/*">

        <div style="margin-top: 1rem;">
          <label style="font-size: 0.85rem; color: #64748b;">Or switch to library image:</label>
          <select name="existing_asset" class="form-control" style="font-size: 0.9rem;">
            <option value="">-- Keep Current Image --</option>
            <?php foreach ($availableImages as $img):
              $rel = 'assets/images/' . basename($img);
              ?>
              <option value="<?= e($rel) ?>" <?= ($article['image_path'] === $rel) ? 'selected' : '' ?>>
                <?= e(basename($img)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="image-preview-container">
          <img id="image_preview" src="" alt="Preview" class="image-preview-thumb" style="display: none;">
        </div>
      </div>

      <!-- Article Content -->
      <div class="form-group">
        <label for="content">Article Body Content <span style="color: red;">*</span></label>
        <textarea id="content" name="content" class="form-control" style="min-height: 280px;"
          required><?= e($article['content']) ?></textarea>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Save Changes</button>
        <a href="articles.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>