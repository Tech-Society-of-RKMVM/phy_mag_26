<?php
/**
 * Add New Article
 */

$adminPageTitle = 'Add New Article';
$adminActiveTab = 'article-add';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = get_db_connection();
$error = '';
$success = '';

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
    $published_date = !empty($_POST['published_date']) ? $_POST['published_date'] : date('Y-m-d');
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if (empty($slug)) {
      $slug = generate_slug($title);
    } else {
      $slug = generate_slug($slug);
    }

    // Image handling: either upload a new file, or select existing image from assets
    $image_path = 'assets/images/dept.jpg'; // fallback
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
      $error = 'Please fill in the required fields: Title, Author Name, and Article Content.';
    }

    if (empty($error)) {
      // Check for duplicate slug
      $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
      $checkStmt->execute([$slug]);
      if ($checkStmt->fetchColumn() > 0) {
        $slug .= '-' . time();
      }

      $insertStmt = $pdo->prepare("
                INSERT INTO articles (slug, title, summary, author_name, author_batch, image_path, content, edition_year, published_date, status, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

      try {
        $insertStmt->execute([
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
          $sort_order
        ]);
        $_SESSION['flash_msg'] = "Article '{$title}' created successfully!";
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
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
  <div class="card-header">
    <h2>Add New Magazine Article</h2>
    <a href="articles.php" class="btn btn-secondary btn-sm">&larr; Back to Articles</a>
  </div>
  <div class="card-body">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="article-add.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

      <div class="form-group">
        <label for="title">Article Title <span style="color: red;">*</span></label>
        <input type="text" id="title" name="title" class="form-control" required
          placeholder="e.g. Breaking The Rules of Time" value="<?= e($_POST['title'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="slug">URL Slug <span style="color: #64748b; font-weight: normal;">(auto-generated)</span></label>
        <input type="text" id="slug" name="slug" class="form-control" placeholder="breaking-the-rules-of-time"
          value="<?= e($_POST['slug'] ?? '') ?>">
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="author_name">Author Name <span style="color: red;">*</span></label>
          <input type="text" id="author_name" name="author_name" class="form-control" required
            placeholder="e.g. Aman Mondal" value="<?= e($_POST['author_name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="author_batch_select">Author Batch Year <span style="color: red;">*</span></label>
          <select id="author_batch_select" name="author_batch_select" class="form-control">
            <option value="UG 1">UG 1</option>
            <option value="UG 2" selected>UG 2</option>
            <option value="UG 3">UG 3</option>
            <option value="UG 4">UG 4</option>
            <option value="PG 1">PG 1</option>
            <option value="PG 2">PG 2</option>
            <option value="Faculty">Faculty</option>
            <option value="Alumni">Alumni</option>
            <option value="custom">Other / Custom...</option>
          </select>
          <input type="text" id="author_batch_custom" name="author_batch_custom" class="form-control"
            style="display: none; margin-top: 0.5rem;" placeholder="e.g. Research Scholar, UG-4">
        </div>
      </div>

      <div class="form-group">
        <label for="summary">Short Summary / Tagline <span style="color: #64748b; font-weight: normal;">(displayed on
            magazine cards)</span></label>
        <input type="text" id="summary" name="summary" class="form-control"
          placeholder="e.g. Breaking the rules of time..." value="<?= e($_POST['summary'] ?? '') ?>">
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="edition_year">Edition Year</label>
          <input type="number" id="edition_year" name="edition_year" class="form-control"
            value="<?= e($_POST['edition_year'] ?? '2026') ?>">
        </div>

        <div class="form-group">
          <label for="published_date">Published Date</label>
          <input type="date" id="published_date" name="published_date" class="form-control"
            value="<?= e($_POST['published_date'] ?? date('Y-m-d')) ?>">
        </div>

        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status" class="form-control">
            <option value="published" selected>Published</option>
            <option value="draft">Draft</option>
          </select>
        </div>

        <div class="form-group">
          <label for="sort_order">Display Order</label>
          <input type="number" id="sort_order" name="sort_order" class="form-control"
            value="<?= e($_POST['sort_order'] ?? '0') ?>">
        </div>
      </div>

      <!-- Featured Image Selection -->
      <div class="form-group"
        style="background: #f8fafc; border: 1px solid var(--border); padding: 1.25rem; border-radius: 8px;">
        <label for="image_file">Upload Featured Image</label>
        <input type="file" id="image_file" name="image_file" class="form-control" accept="image/*">

        <div style="margin-top: 1rem;">
          <label style="font-size: 0.85rem; color: #64748b;">Or choose from existing department image library:</label>
          <select name="existing_asset" class="form-control" style="font-size: 0.9rem;">
            <option value="">-- Select Existing Image --</option>
            <?php foreach ($availableImages as $img):
              $rel = 'assets/images/' . basename($img);
              ?>
              <option value="<?= e($rel) ?>"><?= e(basename($img)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="image-preview-container">
          <img id="image_preview" src="" alt="Preview" class="image-preview-thumb" style="display: none;">
        </div>
      </div>

      <!-- Article Content -->
      <div class="form-group">
        <label for="content">Article Body Content <span style="color: red;">*</span> <span
            style="color: #64748b; font-weight: normal;">(HTML paragraphs supported)</span></label>
        <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.4rem;">Tip: You can use regular paragraphs like
          <code>&lt;p&gt;Text...&lt;/p&gt;</code>, bold with <code>&lt;strong&gt;</code>, or headings with
          <code>&lt;h3&gt;</code>.</p>
        <textarea id="content" name="content" class="form-control" style="min-height: 250px;"
          required><?= e($_POST['content'] ?? '') ?></textarea>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Save & Publish Article</button>
        <a href="articles.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>