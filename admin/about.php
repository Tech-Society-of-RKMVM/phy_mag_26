<?php
/**
 * Admin About Us Editor
 * Manage Behind-the-Scenes Video (file upload) and Vision text
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$adminPageTitle = 'About Us Editor';
$adminActiveTab = 'about';

$message = '';
$error   = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please reload and try again.';
    } else {
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

        // Handle video upload
        if (isset($_FILES['bts_video_file']) && $_FILES['bts_video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['bts_video_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Upload error code: ' . $file['error'];
            } else {
                // Validate MIME type
                $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowedTypes)) {
                    $error = 'Invalid video format. Please upload MP4, WebM, OGG, or MOV.';
                } else {
                    $videoDir = __DIR__ . '/../assets/videos/';
                    if (!is_dir($videoDir)) {
                        mkdir($videoDir, 0777, true);
                    }

                    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $filename = 'bts_video_' . time() . '.' . $ext;
                    $destPath = $videoDir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        // Delete old video if there was one
                        $oldPath = get_setting('about_bts_video_path', '');
                        if ($oldPath && file_exists(__DIR__ . '/../' . $oldPath)) {
                            @unlink(__DIR__ . '/../' . $oldPath);
                        }
                        set_setting('about_bts_video_path', 'assets/videos/' . $filename);
                        $message = 'Settings and video saved successfully!';
                    } else {
                        $error = 'Failed to save the uploaded video file.';
                    }
                }
            }
        }

        // Handle "remove video" action
        if (isset($_POST['remove_video'])) {
            $oldPath = get_setting('about_bts_video_path', '');
            if ($oldPath && file_exists(__DIR__ . '/../' . $oldPath)) {
                @unlink(__DIR__ . '/../' . $oldPath);
            }
            set_setting('about_bts_video_path', '');
            $message = 'Video removed successfully.';
        }

        if (empty($error) && empty($message)) {
            $message = 'Settings saved successfully!';
        }
    }
}

// Fetch current settings
$heroTitle    = get_setting('about_hero_title', 'About Our Wall Magazine');
$heroSubtitle = get_setting('about_hero_subtitle', 'The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students.');
$visionText   = get_setting('about_vision_text', '');
$btsTitle     = get_setting('about_bts_title', 'Behind The Scenes: Making of the Magazine');
$btsDesc      = get_setting('about_bts_desc', '');
$btsVideoPath = get_setting('about_bts_video_path', '');

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header">
    <h2>ℹ️ About Us Editor</h2>
    <a href="../about.php" target="_blank" class="btn btn-primary btn-sm">🌐 View Live Page</a>
  </div>
  <div class="card-body">
    <p style="color: #64748b; margin-bottom: 0;">
      Customize the hero text, vision statement, and upload a Behind-the-Scenes video.
    </p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>🎬 Page Content &amp; Video Settings</h2>
  </div>
  <div class="card-body">
    <form method="POST" action="about.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

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

      <h3 style="font-size: 1rem; margin-bottom: 1.25rem; color: #1e293b;">🎥 Behind The Scenes Video</h3>

      <?php if (!empty($btsVideoPath) && file_exists(__DIR__ . '/../' . $btsVideoPath)): ?>
        <!-- Current video preview -->
        <div style="margin-bottom: 1.5rem;">
          <label style="font-size: 0.85rem; color: #64748b; display: block; margin-bottom: 0.5rem;">
            Current Video:
            <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;"><?= e(basename($btsVideoPath)) ?></code>
          </label>
          <video controls preload="metadata"
            style="max-width: 560px; width: 100%; border-radius: 8px; background: #000; display: block;">
            <source src="../<?= e($btsVideoPath) ?>" type="video/mp4">
            Your browser does not support the video tag.
          </video>
          <div style="margin-top: 0.75rem;">
            <button type="submit" name="remove_video" value="1"
              class="btn btn-sm"
              style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 6px; padding: 0.3rem 0.9rem; cursor: pointer;"
              onclick="return confirm('Remove the current video?')">
              🗑 Remove Video
            </button>
          </div>
        </div>
      <?php else: ?>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1rem;">No video uploaded yet.</p>
      <?php endif; ?>

      <div class="form-group">
        <label for="bts_video_file">
          <?= !empty($btsVideoPath) ? 'Replace Video' : 'Upload Video' ?>
          <span style="color: #64748b; font-weight: normal;">(MP4, WebM, OGG — max upload size set in php.ini)</span>
        </label>
        <input type="file" id="bts_video_file" name="bts_video_file" class="form-control"
          accept="video/mp4,video/webm,video/ogg,video/quicktime">
        <small style="color: #64748b; display: block; margin-top: 0.4rem;">
          The file will be saved to <code>assets/videos/</code>. Large files may take a moment to upload.
        </small>
      </div>

      <!-- Upload progress bar (shown while uploading) -->
      <div id="upload-progress-wrap" style="display: none; margin-bottom: 1rem;">
        <label style="font-size: 0.85rem; color: #64748b;">Uploading…</label>
        <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
          <div id="upload-progress-bar"
            style="height: 100%; width: 0%; background: linear-gradient(90deg, #3b82f6, #6366f1); transition: width 0.3s;"></div>
        </div>
      </div>

      <div class="form-group">
        <label for="about_bts_title">Video Section Title</label>
        <input type="text" id="about_bts_title" name="about_bts_title" class="form-control"
          value="<?= e($btsTitle) ?>">
      </div>

      <div class="form-group">
        <label for="about_bts_desc">Video Description</label>
        <textarea id="about_bts_desc" name="about_bts_desc" class="form-control" rows="2"><?= e($btsDesc) ?></textarea>
      </div>

      <button type="submit" id="btn-save" class="btn btn-primary">
        💾 Save Settings
      </button>
    </form>
  </div>
</div>

<script>
(function () {
    const fileInput = document.getElementById('bts_video_file');
    const form      = fileInput ? fileInput.closest('form') : null;
    const progressWrap = document.getElementById('upload-progress-wrap');
    const progressBar  = document.getElementById('upload-progress-bar');
    const saveBtn      = document.getElementById('btn-save');

    if (!form || !fileInput) return;

    // Show file size warning
    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const sizeMB = (file.size / 1024 / 1024).toFixed(1);
        if (file.size > 500 * 1024 * 1024) {
            alert('Warning: This file is ' + sizeMB + ' MB. Make sure your PHP upload_max_filesize allows it.');
        }
    });

    // Show animated progress bar on submit (if a file is chosen)
    form.addEventListener('submit', function (e) {
        if (!fileInput.files || !fileInput.files.length) return; // no file, normal submit
        if (progressWrap) progressWrap.style.display = 'block';
        if (saveBtn) saveBtn.disabled = true;

        // Animate bar indeterminately until page reloads
        let pct = 0;
        const interval = setInterval(function () {
            pct = Math.min(pct + Math.random() * 8, 92);
            if (progressBar) progressBar.style.width = pct + '%';
        }, 400);
    });
})();
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
