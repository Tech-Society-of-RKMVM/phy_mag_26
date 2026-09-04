<?php
/**
 * Admin About Us & Contributors Editor
 * Manage Behind-the-Scenes Video, Vision texts, and Editorial Contributors
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$pdo = get_db_connection();

$adminPageTitle = 'About Us & Contributors Editor';
$adminActiveTab = 'about';

$message = '';
$error = '';

// Handle POST: Update Page Settings & Video
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_page_settings'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please reload and try again.';
    } else {
        $heroTitle = trim($_POST['about_hero_title'] ?? '');
        $heroSubtitle = trim($_POST['about_hero_subtitle'] ?? '');
        $visionText = trim($_POST['about_vision_text'] ?? '');
        $btsTitle = trim($_POST['about_bts_title'] ?? '');
        $btsDesc = trim($_POST['about_bts_desc'] ?? '');
        $btsVideoUrl = trim($_POST['about_bts_video_url'] ?? '');

        set_setting('about_hero_title', $heroTitle ?: 'About Our Wall Magazine');
        set_setting('about_hero_subtitle', $heroSubtitle);
        set_setting('about_vision_text', $visionText);
        set_setting('about_bts_title', $btsTitle ?: 'Behind The Scenes');
        set_setting('about_bts_desc', $btsDesc);
        set_setting('about_bts_video_url', $btsVideoUrl);

        $message = 'About page information & video settings saved successfully!';
    }
}

// Handle POST: Add or Update Contributor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_contributor'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $contributorId = (int)($_POST['contributor_id'] ?? 0);
        $name = trim($_POST['contributor_name'] ?? '');
        $role = trim($_POST['contributor_role'] ?? '');
        
        $batchSelect = trim($_POST['contributor_batch_select'] ?? '');
        $batchCustom = trim($_POST['contributor_batch_custom'] ?? '');
        $batch = ($batchSelect === 'custom') ? $batchCustom : $batchSelect;

        $bio = trim($_POST['contributor_bio'] ?? '');
        $chosenAsset = trim($_POST['existing_avatar'] ?? '');
        $finalAvatarPath = '';

        if (empty($name) || empty($role)) {
            $error = 'Contributor name and role are required.';
        } else {
            // Check avatar upload
            if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = handle_image_upload('avatar_file', 'contributors');
                if ($uploadResult['success']) {
                    $finalAvatarPath = $uploadResult['path'];
                } else {
                    $error = $uploadResult['error'];
                }
            } elseif (!empty($chosenAsset)) {
                $finalAvatarPath = $chosenAsset;
            }

            if (empty($error)) {
                if ($contributorId > 0) {
                    // Update existing
                    if (!empty($finalAvatarPath)) {
                        $stmt = $pdo->prepare("UPDATE contributors SET name = ?, role = ?, batch = ?, bio = ?, avatar_path = ? WHERE id = ?");
                        $stmt->execute([$name, $role, $batch, $bio, $finalAvatarPath, $contributorId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE contributors SET name = ?, role = ?, batch = ?, bio = ? WHERE id = ?");
                        $stmt->execute([$name, $role, $batch, $bio, $contributorId]);
                    }
                    $message = "Contributor '$name' updated successfully!";
                } else {
                    // Insert new
                    $maxOrder = (int)$pdo->query("SELECT MAX(sort_order) FROM contributors")->fetchColumn();
                    $stmt = $pdo->prepare("INSERT INTO contributors (name, role, batch, bio, avatar_path, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $role, $batch, $bio, $finalAvatarPath ?: null, $maxOrder + 1]);
                    $message = "Contributor '$name' added successfully!";
                }
            }
        }
    }
}

// Fetch Page Settings
$heroTitle = get_setting('about_hero_title', 'About Our Wall Magazine');
$heroSubtitle = get_setting('about_hero_subtitle', 'The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students.');
$visionText = get_setting('about_vision_text', '');
$btsTitle = get_setting('about_bts_title', 'Behind The Scenes: Making of the Magazine');
$btsDesc = get_setting('about_bts_desc', '');
$btsVideoUrl = get_setting('about_bts_video_url', '');
$embedVideoUrl = get_embed_video_url($btsVideoUrl);

// Fetch Contributors
$stmt = $pdo->query("SELECT id, name, role, batch, avatar_path, bio, sort_order FROM contributors ORDER BY sort_order ASC, id ASC");
$contributors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Common batches
$commonBatches = ['UG 1', 'UG 2', 'UG 3', 'UG 4', 'PG 1', 'PG 2', 'Faculty', 'Alumni', 'Passed out 2026'];

// Available Images for Avatars
$availableImages = glob(__DIR__ . '/../assets/images/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
$contributorImages = glob(__DIR__ . '/../assets/images/contributors/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
$allImages = array_merge($contributorImages, $availableImages);

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- Include SortableJS for Drag-and-Drop Reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header">
    <h2>ℹ️ About Us, Video & Contributors Manager</h2>
    <div>
      <a href="../about.php" target="_blank" class="btn btn-primary btn-sm">🌐 View Live About Page</a>
    </div>
  </div>
  <div class="card-body">
    <p style="color: #64748b; margin-bottom: 0;">
      Customize the vision statement, embed your Behind-the-Scenes video (YouTube, Vimeo, or MP4), and manage editorial board and student contributors.
    </p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<!-- Save status toast notification -->
<div id="save-status" style="display: none; margin-bottom: 1rem; padding: 0.8rem 1.2rem; border-radius: 6px; font-weight: 600;"></div>

<div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 2rem; align-items: start;">

  <!-- Left Column: Page Settings & Behind the Scenes Video -->
  <div>
    
    <div class="card" style="margin-bottom: 2rem;">
      <div class="card-header">
        <h2>🎬 Behind The Scenes Video & Info</h2>
      </div>
      <div class="card-body">
        <form method="POST" action="about.php">
          <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
          <input type="hidden" name="action_page_settings" value="1">

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
            <textarea id="about_vision_text" name="about_vision_text" class="form-control" rows="4"><?= e($visionText) ?></textarea>
          </div>

          <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid #e2e8f0;">

          <div class="form-group">
            <label for="about_bts_video_url">
              Behind The Scenes Video URL <span style="color: #64748b; font-weight: normal;">(YouTube, Vimeo, or direct video file)</span>
            </label>
            <input type="url" id="about_bts_video_url" name="about_bts_video_url" class="form-control" 
              placeholder="e.g. https://www.youtube.com/watch?v=dQw4w9WgXcQ" value="<?= e($btsVideoUrl) ?>">
          </div>

          <?php if (!empty($btsVideoUrl)): ?>
            <div style="margin-bottom: 1.25rem;">
              <label style="font-size: 0.85rem; color: #64748b; display: block; margin-bottom: 0.4rem;">Live Video Preview:</label>
              <div style="aspect-ratio: 16/9; max-width: 100%; border-radius: 8px; overflow: hidden; background: #000;">
                <iframe src="<?= e($embedVideoUrl) ?>" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
              </div>
            </div>
          <?php endif; ?>

          <div class="form-group">
            <label for="about_bts_title">Video Showcase Title</label>
            <input type="text" id="about_bts_title" name="about_bts_title" class="form-control" 
              value="<?= e($btsTitle) ?>">
          </div>

          <div class="form-group">
            <label for="about_bts_desc">Video Description</label>
            <textarea id="about_bts_desc" name="about_bts_desc" class="form-control" rows="2"><?= e($btsDesc) ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary">
            💾 Save About & Video Settings
          </button>
        </form>
      </div>
    </div>

    <!-- Contributors Reorder List -->
    <div class="card">
      <div class="card-header">
        <h2>👥 Contributors Directory (<?= count($contributors) ?> Members)</h2>
        <span style="font-size: 0.85rem; color: #64748b;">Drag items to reorder</span>
      </div>
      <div class="card-body">
        <?php if (empty($contributors)): ?>
          <p style="text-align: center; color: #64748b; padding: 2rem;">No contributors added yet.</p>
        <?php else: ?>
          <div id="contributors-list" class="contributors-sortable-list">
            <?php foreach ($contributors as $index => $c): ?>
              <div class="contributor-item" data-id="<?= $c['id'] ?>">
                <div class="contributor-handle">☰</div>
                
                <div class="contributor-avatar-small">
                  <?php if (!empty($c['avatar_path']) && file_exists(__DIR__ . '/../' . $c['avatar_path'])): ?>
                    <img src="../<?= e($c['avatar_path']) ?>" alt="<?= e($c['name']) ?>">
                  <?php else: ?>
                    <div class="avatar-letter"><?= strtoupper(mb_substr($c['name'], 0, 1, 'UTF-8')) ?></div>
                  <?php endif; ?>
                </div>

                <div class="contributor-details">
                  <div class="contributor-title-line">
                    <strong><?= e($c['name']) ?></strong>
                    <span class="badge badge-batch"><?= e($c['batch']) ?></span>
                  </div>
                  <div class="contributor-role-text"><?= e($c['role']) ?></div>
                </div>

                <div class="contributor-actions">
                  <button type="button" class="btn btn-secondary btn-sm btn-edit-contributor" 
                    data-id="<?= $c['id'] ?>"
                    data-name="<?= e($c['name']) ?>"
                    data-role="<?= e($c['role']) ?>"
                    data-batch="<?= e($c['batch']) ?>"
                    data-bio="<?= e($c['bio']) ?>"
                    data-avatar="<?= e($c['avatar_path']) ?>">
                    ✏ Edit
                  </button>
                  <button type="button" class="btn-delete-contributor" data-id="<?= $c['id'] ?>" title="Delete">
                    🗑
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="margin-top: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" id="btn-save-contributors-order" class="btn btn-primary btn-sm">
              💾 Save List Order
            </button>
            <span style="font-size: 0.85rem; color: #64748b;">Order auto-saves on drag</span>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Right Column: Add / Edit Contributor Form -->
  <div>
    <div class="card" style="position: sticky; top: 1.5rem;">
      <div class="card-header">
        <h2 id="form-contributor-title">➕ Add Contributor</h2>
        <button type="button" id="btn-reset-contributor-form" class="btn btn-secondary btn-sm" style="display: none;">
          Cancel Edit
        </button>
      </div>
      <div class="card-body">
        <form method="POST" action="about.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
          <input type="hidden" name="action_save_contributor" value="1">
          <input type="hidden" id="contributor_id" name="contributor_id" value="0">

          <div class="form-group">
            <label for="contributor_name">Full Name <span style="color: red;">*</span></label>
            <input type="text" id="contributor_name" name="contributor_name" class="form-control" required
              placeholder="e.g. Aman Mondal">
          </div>

          <div class="form-group">
            <label for="contributor_role">Role / Designation <span style="color: red;">*</span></label>
            <input type="text" id="contributor_role" name="contributor_role" class="form-control" required
              placeholder="e.g. Editor-in-Chief, Illustrator, Writer">
          </div>

          <div class="form-group">
            <label for="contributor_batch_select">Batch / Category</label>
            <select id="contributor_batch_select" name="contributor_batch_select" class="form-control">
              <?php foreach ($commonBatches as $b): ?>
                <option value="<?= e($b) ?>" <?= $b === 'UG 3' ? 'selected' : '' ?>><?= e($b) ?></option>
              <?php endforeach; ?>
              <option value="custom">Other / Custom...</option>
            </select>
            <input type="text" id="contributor_batch_custom" name="contributor_batch_custom" class="form-control"
              style="display: none; margin-top: 0.5rem;" placeholder="e.g. Research Scholar, Guest Author">
          </div>

          <div class="form-group">
            <label for="avatar_file">Avatar Image (Optional)</label>
            <input type="file" id="avatar_file" name="avatar_file" class="form-control" accept="image/*">
            <small style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 0.25rem;">
              Saves to <code>assets/images/contributors/</code>
            </small>
          </div>

          <div class="form-group">
            <label for="existing_avatar">Or Select Existing Image</label>
            <select id="existing_avatar" name="existing_avatar" class="form-control">
              <option value="">-- None / Keep current avatar --</option>
              <?php foreach ($allImages as $imgPath): 
                $relPath = str_replace(__DIR__ . '/../', '', str_replace('\\', '/', $imgPath));
              ?>
                <option value="<?= e($relPath) ?>"><?= e(basename($relPath)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="contributor_bio">Short Bio / Contribution Note</label>
            <textarea id="contributor_bio" name="contributor_bio" class="form-control" rows="3"
              placeholder="e.g. Coordinating student submissions and scientific review."></textarea>
          </div>

          <button type="submit" id="btn-submit-contributor" class="btn btn-primary" style="width: 100%;">
            ➕ Add Contributor
          </button>
        </form>
      </div>
    </div>
  </div>

</div>

<style>
/* Contributors Sortable List */
.contributors-sortable-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.contributor-item {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
  transition: transform 0.15s, box-shadow 0.15s;
}

.contributor-item:hover {
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.contributor-item.sortable-ghost {
  opacity: 0.4;
  border: 2px dashed #3b82f6;
  background: #eff6ff;
}

.contributor-handle {
  cursor: grab;
  color: #94a3b8;
  font-size: 1.2rem;
  user-select: none;
}

.contributor-handle:active {
  cursor: grabbing;
}

.contributor-avatar-small {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  overflow: hidden;
  background: #f1f5f9;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.contributor-avatar-small img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-letter {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.contributor-details {
  flex: 1;
  min-width: 0;
}

.contributor-title-line {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.contributor-role-text {
  font-size: 0.82rem;
  color: #64748b;
  margin-top: 2px;
}

.contributor-actions {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.btn-delete-contributor {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fca5a5;
  border-radius: 4px;
  padding: 0.3rem 0.5rem;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-delete-contributor:hover {
  background: #ef4444;
  color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('contributors-list');
    const saveOrderBtn = document.getElementById('btn-save-contributors-order');
    const statusBox = document.getElementById('save-status');

    function showStatus(msg, isSuccess) {
        if (!statusBox) return;
        statusBox.textContent = msg;
        statusBox.style.display = 'block';
        if (isSuccess) {
            statusBox.style.background = '#dcfce7';
            statusBox.style.color = '#166534';
            statusBox.style.border = '1px solid #86efac';
        } else {
            statusBox.style.background = '#fee2e2';
            statusBox.style.color = '#991b1b';
            statusBox.style.border = '1px solid #fca5a5';
        }
        setTimeout(() => {
            statusBox.style.display = 'none';
        }, 4000);
    }

    // SortableJS initialization
    if (list) {
        new Sortable(list, {
            animation: 150,
            handle: '.contributor-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                saveOrder(true);
            }
        });
    }

    function saveOrder(isAuto = false) {
        const items = list.querySelectorAll('.contributor-item');
        const order = Array.from(items).map(item => parseInt(item.dataset.id, 10));

        fetch('api_contributors.php?action=reorder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order: order })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showStatus(isAuto ? '✓ Contributors order auto-saved' : '✓ Order saved successfully!', true);
            } else {
                showStatus('Error: ' + (data.error || 'Failed to save order'), false);
            }
        })
        .catch(() => showStatus('Network error saving order', false));
    }

    if (saveOrderBtn) {
        saveOrderBtn.addEventListener('click', () => saveOrder(false));
    }

    // Delete Contributor
    document.querySelectorAll('.btn-delete-contributor').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (!confirm('Are you sure you want to remove this contributor?')) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('api_contributors.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`.contributor-item[data-id="${id}"]`);
                    if (item) item.remove();
                    showStatus('✓ Contributor removed', true);
                } else {
                    showStatus('Error: ' + (data.error || 'Failed to delete'), false);
                }
            })
            .catch(() => showStatus('Network error deleting contributor', false));
        });
    });

    // Edit Contributor Form Populate
    const formTitle = document.getElementById('form-contributor-title');
    const resetBtn = document.getElementById('btn-reset-contributor-form');
    const submitBtn = document.getElementById('btn-submit-contributor');
    const inputId = document.getElementById('contributor_id');
    const inputName = document.getElementById('contributor_name');
    const inputRole = document.getElementById('contributor_role');
    const selectBatch = document.getElementById('contributor_batch_select');
    const customBatch = document.getElementById('contributor_batch_custom');
    const inputBio = document.getElementById('contributor_bio');

    document.querySelectorAll('.btn-edit-contributor').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const role = this.dataset.role;
            const batch = this.dataset.batch;
            const bio = this.dataset.bio;

            inputId.value = id;
            inputName.value = name;
            inputRole.value = role;
            inputBio.value = bio;

            // Handle batch select vs custom
            let found = false;
            for (let opt of selectBatch.options) {
                if (opt.value === batch) {
                    selectBatch.value = batch;
                    customBatch.style.display = 'none';
                    found = true;
                    break;
                }
            }
            if (!found && batch) {
                selectBatch.value = 'custom';
                customBatch.style.display = 'block';
                customBatch.value = batch;
            }

            formTitle.textContent = '✏️ Edit Contributor: ' + name;
            submitBtn.textContent = '💾 Update Contributor';
            resetBtn.style.display = 'inline-block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            inputId.value = '0';
            inputName.value = '';
            inputRole.value = '';
            inputBio.value = '';
            selectBatch.value = 'UG 3';
            customBatch.style.display = 'none';
            customBatch.value = '';

            formTitle.textContent = '➕ Add Contributor';
            submitBtn.textContent = '➕ Add Contributor';
            resetBtn.style.display = 'none';
        });
    }

    // Toggle custom batch input
    if (selectBatch && customBatch) {
        selectBatch.addEventListener('change', function() {
            if (this.value === 'custom') {
                customBatch.style.display = 'block';
                customBatch.focus();
            } else {
                customBatch.style.display = 'none';
                customBatch.value = this.value;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
