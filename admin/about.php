<?php
/**
 * Admin About Us Editor
 * Manage Behind-the-Scenes Photos (gallery) and Vision text
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$adminPageTitle = 'About Us Editor';
$adminActiveTab = 'about';

$message = '';
$error   = '';

$pdo = get_db_connection();

// ---------------------------------------------------------------
// Ensure bts_photos table exists (safety net in case migration
// hasn't been run yet on this environment)
// ---------------------------------------------------------------
$pdo->exec("CREATE TABLE IF NOT EXISTS `bts_photos` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image_path`  VARCHAR(500) NOT NULL,
  `caption`     VARCHAR(300) NOT NULL DEFAULT '',
  `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ---------------------------------------------------------------
// Handle POST actions
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please reload and try again.';
    } else {
        $action = $_POST['action'] ?? 'save_settings';

        // --- Delete a single photo ---
        if ($action === 'delete_photo') {
            $photoId = (int)($_POST['photo_id'] ?? 0);
            if ($photoId > 0) {
                $stmt = $pdo->prepare("SELECT image_path FROM bts_photos WHERE id = ?");
                $stmt->execute([$photoId]);
                $row = $stmt->fetch();
                if ($row) {
                    $fullPath = __DIR__ . '/../' . $row['image_path'];
                    if (file_exists($fullPath)) @unlink($fullPath);
                    $pdo->prepare("DELETE FROM bts_photos WHERE id = ?")->execute([$photoId]);
                    $message = 'Photo removed.';
                }
            }
        }

        // --- Upload new photos ---
        elseif ($action === 'upload_photos') {
            $caption = trim($_POST['bts_photo_caption'] ?? '');
            $files   = $_FILES['bts_photos_upload'] ?? [];

            if (!empty($files['name'][0])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $photoDir     = __DIR__ . '/../assets/images/bts/';
                if (!is_dir($photoDir)) mkdir($photoDir, 0777, true);

                $uploaded = 0;
                $count    = count($files['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime  = finfo_file($finfo, $files['tmp_name'][$i]);
                    finfo_close($finfo);

                    if (!in_array($mime, $allowedTypes)) continue;

                    $ext      = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $filename = 'bts_' . time() . '_' . $i . '.' . $ext;
                    $destPath = $photoDir . $filename;

                    if (move_uploaded_file($files['tmp_name'][$i], $destPath)) {
                        // Get next sort order
                        $maxSort = $pdo->query("SELECT MAX(sort_order) FROM bts_photos")->fetchColumn();
                        $pdo->prepare("INSERT INTO bts_photos (image_path, caption, sort_order) VALUES (?, ?, ?)")
                            ->execute(['assets/images/bts/' . $filename, $caption, (int)$maxSort + 1]);
                        $uploaded++;
                    }
                }
                $message = $uploaded > 0 ? "{$uploaded} photo(s) uploaded successfully!" : 'No valid photos were uploaded.';
            } else {
                $error = 'Please choose at least one photo file.';
            }
        }

        // --- Save text settings only ---
        else {
            $heroTitle    = trim($_POST['about_hero_title'] ?? '');
            $heroSubtitle = trim($_POST['about_hero_subtitle'] ?? '');
            $visionText   = trim($_POST['about_vision_text'] ?? '');
            $btsTitle     = trim($_POST['about_bts_title'] ?? '');
            $btsDesc      = trim($_POST['about_bts_desc'] ?? '');

            set_setting('about_hero_title',    $heroTitle ?: 'About Our Wall Magazine');
            set_setting('about_hero_subtitle', $heroSubtitle);
            set_setting('about_vision_text',   $visionText);
            set_setting('about_bts_title',     $btsTitle ?: 'Behind The Scenes');
            set_setting('about_bts_desc',      $btsDesc);

            $message = 'Settings saved successfully!';
        }
    }
}

// ---------------------------------------------------------------
// Fetch current values
// ---------------------------------------------------------------
$heroTitle    = get_setting('about_hero_title', 'About Our Wall Magazine');
$heroSubtitle = get_setting('about_hero_subtitle', 'The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students.');
$visionText   = get_setting('about_vision_text', '');
$btsTitle     = get_setting('about_bts_title', 'Behind The Scenes: Making of the Magazine');
$btsDesc      = get_setting('about_bts_desc', '');

$btsPhotos = $pdo->query("SELECT * FROM bts_photos ORDER BY sort_order ASC, id ASC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header">
    <h2>ℹ️ About Us Editor</h2>
    <a href="../about.php" target="_blank" class="btn btn-primary btn-sm">🌐 View Live Page</a>
  </div>
  <div class="card-body">
    <p style="color: #64748b; margin-bottom: 0;">
      Customize the hero text, vision statement, and manage Behind-the-Scenes photos.
    </p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<!-- ============================================================
     TEXT SETTINGS CARD
     ============================================================ -->
<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header">
    <h2>📝 Page Content Settings</h2>
  </div>
  <div class="card-body">
    <form method="POST" action="about.php">
      <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
      <input type="hidden" name="action" value="save_settings">

      <div class="form-group">
        <label for="about_hero_title">Hero Heading</label>
        <input type="text" id="about_hero_title" name="about_hero_title" class="form-control"
          value="<?= e($heroTitle) ?>" required>
      </div>

      <div class="form-group">
        <label for="about_hero_subtitle">Hero Subtitle</label>
        <textarea id="about_hero_subtitle" name="about_hero_subtitle" class="form-control" rows="2"><?= e($heroSubtitle) ?></textarea>
      </div>

      <div class="form-group">
        <label for="about_vision_text">Our Vision Text <span style="color: #64748b; font-weight: normal;">(HTML supported)</span></label>
        <textarea id="about_vision_text" name="about_vision_text" class="form-control" rows="5"><?= e($visionText) ?></textarea>
      </div>

      <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid #e2e8f0;">

      <div class="form-group">
        <label for="about_bts_title">BTS Section Title</label>
        <input type="text" id="about_bts_title" name="about_bts_title" class="form-control"
          value="<?= e($btsTitle) ?>">
      </div>

      <div class="form-group">
        <label for="about_bts_desc">BTS Section Description</label>
        <textarea id="about_bts_desc" name="about_bts_desc" class="form-control" rows="2"><?= e($btsDesc) ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary">💾 Save Settings</button>
    </form>
  </div>
</div>

<!-- ============================================================
     BTS PHOTO GALLERY MANAGER
     ============================================================ -->
<div class="card">
  <div class="card-header">
    <h2>📸 Behind The Scenes Photos</h2>
    <span style="font-size: 0.85rem; color: #64748b;"><?= count($btsPhotos) ?> photo(s) uploaded</span>
  </div>
  <div class="card-body">

    <!-- Upload form -->
    <form method="POST" action="about.php" enctype="multipart/form-data"
          id="bts-upload-form" style="margin-bottom: 2rem;">
      <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
      <input type="hidden" name="action" value="upload_photos">

      <div class="form-group">
        <label for="bts_photos_upload">
          Upload Photos
          <span style="color: #64748b; font-weight: normal;">(JPG, PNG, WebP — multiple allowed)</span>
        </label>
        <input type="file" id="bts_photos_upload" name="bts_photos_upload[]" class="form-control"
               accept="image/jpeg,image/png,image/webp,image/gif" multiple
               onchange="previewBtsPhotos(this)">
      </div>

      <!-- Live preview strip -->
      <div id="bts-preview-strip"
           style="display:none; display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;"></div>

      <div class="form-group">
        <label for="bts_photo_caption">Caption (applied to all uploaded photos in this batch)</label>
        <input type="text" id="bts_photo_caption" name="bts_photo_caption" class="form-control"
               placeholder="e.g. Editorial team working on layouts">
      </div>

      <button type="submit" id="btn-upload" class="btn btn-primary">📤 Upload Photos</button>
    </form>

    <!-- Existing gallery grid -->
    <?php if (!empty($btsPhotos)): ?>
      <hr style="border:0; border-top:1px solid #e2e8f0; margin-bottom:1.5rem;">
      <h3 style="font-size: 0.95rem; color: #475569; margin-bottom: 1rem;">Existing Photos</h3>
      <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap:1rem;">
        <?php foreach ($btsPhotos as $photo): ?>
          <div style="position:relative; border-radius:8px; overflow:hidden; background:#f8fafc;
                      border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
            <img src="../<?= e($photo['image_path']) ?>" alt="<?= e($photo['caption']) ?>"
                 style="width:100%; height:140px; object-fit:cover; display:block;">
            <?php if ($photo['caption']): ?>
              <p style="font-size:0.78rem; color:#475569; padding:0.4rem 0.6rem; margin:0;
                         white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                 title="<?= e($photo['caption']) ?>">
                <?= e($photo['caption']) ?>
              </p>
            <?php endif; ?>
            <!-- Delete button -->
            <form method="POST" action="about.php" style="position:absolute; top:5px; right:5px;">
              <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
              <input type="hidden" name="action" value="delete_photo">
              <input type="hidden" name="photo_id" value="<?= (int)$photo['id'] ?>">
              <button type="submit"
                      style="background:rgba(220,38,38,0.85); color:#fff; border:none; border-radius:50%;
                             width:26px; height:26px; font-size:0.75rem; cursor:pointer; line-height:1;"
                      onclick="return confirm('Delete this photo?')" title="Delete">✕</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:#94a3b8; text-align:center; padding:2rem 0;">
        No BTS photos yet. Upload some above!
      </p>
    <?php endif; ?>

  </div>
</div>

<script>
function previewBtsPhotos(input) {
    const strip = document.getElementById('bts-preview-strip');
    strip.innerHTML = '';
    strip.style.display = 'none';
    if (!input.files || !input.files.length) return;

    strip.style.display = 'flex';
    Array.from(input.files).forEach(function (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'height:90px; width:auto; border-radius:6px; object-fit:cover; border:2px solid #e2e8f0;';
            strip.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
