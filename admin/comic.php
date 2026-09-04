<?php
/**
 * Admin Comic Editor
 * Manage comic panels, drag-and-drop reordering, and prologue/epilogue texts
 */

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$adminPageTitle = 'Comic Book Editor';
$adminActiveTab = 'comic';

$message = '';
$error = '';

// Handle POST: Update Comic Texts & Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_settings'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please reload and try again.';
    } else {
        $title = trim($_POST['comic_title'] ?? '');
        $topText = trim($_POST['comic_top_text'] ?? '');
        $bottomText = trim($_POST['comic_bottom_text'] ?? '');

        set_setting('comic_title', $title ?: 'Department Of Physics Comic');
        set_setting('comic_top_text', $topText);
        set_setting('comic_bottom_text', $bottomText);

        $message = 'Comic settings & texts saved successfully!';
    }
}

// Handle POST: Upload New Panel(s)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_upload_panel'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $panelTitle = trim($_POST['panel_title'] ?? '');
        $chosenAsset = trim($_POST['existing_asset'] ?? '');
        $finalImagePath = '';

        // Check file upload first
        if (isset($_FILES['panel_file']) && $_FILES['panel_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handle_image_upload('panel_file', 'comic');
            if ($uploadResult['success']) {
                $finalImagePath = $uploadResult['path'];
            } else {
                $error = $uploadResult['error'];
            }
        } elseif (!empty($chosenAsset)) {
            $finalImagePath = $chosenAsset;
        } else {
            $error = 'Please either upload a panel image or select an existing asset.';
        }

        if (empty($error) && !empty($finalImagePath)) {
            // Get highest sort order
            $maxOrderStmt = $pdo->query("SELECT MAX(sort_order) FROM comic_panels");
            $nextOrder = ((int)$maxOrderStmt->fetchColumn()) + 1;

            $insStmt = $pdo->prepare("INSERT INTO comic_panels (title, image_path, sort_order) VALUES (?, ?, ?)");
            $insStmt->execute([$panelTitle, $finalImagePath, $nextOrder]);

            $message = 'New comic panel page added successfully!';
        }
    }
}

// Fetch current panels
$panelsStmt = $pdo->query("SELECT id, title, image_path, sort_order, created_at FROM comic_panels ORDER BY sort_order ASC, id ASC");
$panels = $panelsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing assets for quick selection
$availableImages = glob(__DIR__ . '/../../assets/images/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
$comicImages = glob(__DIR__ . '/../../assets/images/comic/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
$allImages = array_merge($comicImages, $availableImages);

// Fetch settings
$comicTitle = get_setting('comic_title', 'Department Of Physics Comic Issue #1');
$comicTopText = get_setting('comic_top_text', '');
$comicBottomText = get_setting('comic_bottom_text', '');

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- Include SortableJS for Drag-and-Drop Reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header">
    <h2>🎨 Department Comic Serial & Book Viewer Manager</h2>
    <div>
      <a href="../comic.php" target="_blank" class="btn btn-primary btn-sm">📖 Preview Live Comic</a>
    </div>
  </div>
  <div class="card-body">
    <p style="color: #64748b; margin-bottom: 0;">
      Organize the comic book pages, upload new panels, drag and drop to reorder pages, and customize the introductory prologue and closing credits text.
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

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem; align-items: start;">

  <!-- Left Column: Panels Reorder Grid & Text Settings -->
  <div>
    
    <!-- Comic Pages Drag-and-Drop Grid -->
    <div class="card" style="margin-bottom: 2rem;">
      <div class="card-header">
        <h2>📄 Comic Book Pages & Panels (<?= count($panels) ?> Total)</h2>
        <span style="font-size: 0.85rem; color: #64748b;">Drag cards to reorder</span>
      </div>
      <div class="card-body">
        
        <?php if (empty($panels)): ?>
          <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎨</div>
            <h3>No comic panels uploaded yet</h3>
            <p>Upload your first comic page using the panel on the right.</p>
          </div>
        <?php else: ?>
          <div id="panels-grid" class="panels-sortable-grid">
            <?php foreach ($panels as $index => $panel): ?>
              <div class="panel-card" data-id="<?= $panel['id'] ?>">
                <div class="panel-drag-handle" title="Drag to reorder">
                  <span class="handle-icon">☰</span>
                  <span class="page-badge-num">Page #<span class="rank-display"><?= $index + 1 ?></span></span>
                </div>

                <div class="panel-preview-img-wrap">
                  <img src="../<?= e($panel['image_path']) ?>" alt="Page <?= $index + 1 ?>" loading="lazy">
                </div>

                <div class="panel-card-footer">
                  <input type="text" class="panel-title-input" value="<?= e($panel['title']) ?>" 
                    placeholder="Page / Episode title..." data-id="<?= $panel['id'] ?>">
                  <div class="panel-actions">
                    <button type="button" class="btn-delete-panel" data-id="<?= $panel['id'] ?>" title="Delete Panel">
                      🗑 Delete
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" id="btn-save-order" class="btn btn-primary">
              💾 Save Page Order
            </button>
            <span style="font-size: 0.85rem; color: #64748b;">Changes save automatically when dragged.</span>
          </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Comic Text & Metadata Settings -->
    <div class="card">
      <div class="card-header">
        <h2>✏️ Comic Intro & Outro Texts</h2>
      </div>
      <div class="card-body">
        <form method="POST" action="comic.php">
          <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
          <input type="hidden" name="action_settings" value="1">

          <div class="form-group">
            <label for="comic_title">Comic Series / Issue Title</label>
            <input type="text" id="comic_title" name="comic_title" class="form-control" 
              value="<?= e($comicTitle) ?>" required>
          </div>

          <div class="form-group">
            <label for="comic_top_text">
              Top Text / Prologue <span style="color: #64748b; font-weight: normal;">(displayed above the book viewer, HTML supported)</span>
            </label>
            <textarea id="comic_top_text" name="comic_top_text" class="form-control" rows="4" 
              placeholder="e.g. <p>Welcome to our annual physics comic issue...</p>"><?= e($comicTopText) ?></textarea>
          </div>

          <div class="form-group">
            <label for="comic_bottom_text">
              Bottom Text / Credits & Outro <span style="color: #64748b; font-weight: normal;">(displayed below the book viewer, HTML supported)</span>
            </label>
            <textarea id="comic_bottom_text" name="comic_bottom_text" class="form-control" rows="4" 
              placeholder="e.g. <p>Illustrated by the Department of Physics creative team...</p>"><?= e($comicBottomText) ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary">
            💾 Save Text Settings
          </button>
        </form>
      </div>
    </div>

  </div>

  <!-- Right Column: Add New Panel Form -->
  <div>
    <div class="card" style="position: sticky; top: 1.5rem;">
      <div class="card-header">
        <h2>➕ Upload New Panel</h2>
      </div>
      <div class="card-body">
        <form method="POST" action="comic.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
          <input type="hidden" name="action_upload_panel" value="1">

          <div class="form-group">
            <label for="panel_title">Page / Episode Title (Optional)</label>
            <input type="text" id="panel_title" name="panel_title" class="form-control" 
              placeholder="e.g. Page 4 - Quantum Leap">
          </div>

          <div class="form-group">
            <label for="panel_file">Upload Comic Image File</label>
            <input type="file" id="panel_file" name="panel_file" class="form-control" accept="image/*">
            <small style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 0.25rem;">
              Saves directly to <code>assets/images/comic/</code> (JPG, PNG, WEBP).
            </small>
          </div>

          <div class="form-group">
            <label for="existing_asset">Or Choose Existing Image</label>
            <select id="existing_asset" name="existing_asset" class="form-control">
              <option value="">-- Select from assets library --</option>
              <?php foreach ($allImages as $imgPath): 
                $relPath = str_replace(__DIR__ . '/../../', '', str_replace('\\', '/', $imgPath));
              ?>
                <option value="<?= e($relPath) ?>"><?= e(basename($relPath)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div id="new-panel-preview" style="display: none; margin-bottom: 1rem; text-align: center;">
            <img id="new-panel-img" src="" alt="Preview" 
              style="max-width: 100%; max-height: 200px; border-radius: 6px; border: 1px solid #cbd5e1;">
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%;">
            ➕ Add Comic Page
          </button>
        </form>
      </div>
    </div>
  </div>

</div>

<style>
/* Comic Panels Grid */
.panels-sortable-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
}

.panel-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  display: flex;
  flex-direction: column;
  transition: transform 0.15s, box-shadow 0.15s;
}

.panel-card:hover {
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

.panel-card.sortable-ghost {
  opacity: 0.4;
  border: 2px dashed #3b82f6;
  background: #eff6ff;
}

.panel-drag-handle {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  padding: 0.5rem 0.75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: grab;
  user-select: none;
}

.panel-drag-handle:active {
  cursor: grabbing;
}

.handle-icon {
  color: #94a3b8;
  font-size: 1.1rem;
}

.page-badge-num {
  font-size: 0.8rem;
  font-weight: 700;
  color: #1e293b;
  background: #e2e8f0;
  padding: 2px 8px;
  border-radius: 12px;
}

.panel-preview-img-wrap {
  background: #0f172a;
  height: 220px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.panel-preview-img-wrap img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.panel-card-footer {
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  background: #ffffff;
}

.panel-title-input {
  width: 100%;
  font-size: 0.85rem;
  padding: 0.4rem 0.5rem;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
}

.panel-title-input:focus {
  outline: none;
  border-color: #3b82f6;
}

.panel-actions {
  display: flex;
  justify-content: flex-end;
}

.btn-delete-panel {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fca5a5;
  border-radius: 4px;
  padding: 0.25rem 0.6rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-delete-panel:hover {
  background: #ef4444;
  color: #ffffff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('panels-grid');
    const saveBtn = document.getElementById('btn-save-order');
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

    // Initialize SortableJS
    if (grid) {
        new Sortable(grid, {
            animation: 150,
            handle: '.panel-drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                updateBadges();
                saveOrder(true);
            }
        });
    }

    function updateBadges() {
        const cards = grid.querySelectorAll('.panel-card');
        cards.forEach((card, index) => {
            const numEl = card.querySelector('.rank-display');
            if (numEl) numEl.textContent = index + 1;
        });
    }

    function saveOrder(isAuto = false) {
        const cards = grid.querySelectorAll('.panel-card');
        const order = Array.from(cards).map(c => parseInt(c.dataset.id, 10));

        fetch('api_comic.php?action=reorder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order: order })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showStatus(isAuto ? '✓ Panel order auto-saved' : '✓ Order saved successfully!', true);
            } else {
                showStatus('Error: ' + (data.error || 'Failed to save order'), false);
            }
        })
        .catch(err => {
            showStatus('Network error saving order', false);
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', () => saveOrder(false));
    }

    // Delete Panel Handlers
    document.querySelectorAll('.btn-delete-panel').forEach(btn => {
        btn.addEventListener('click', function() {
            const panelId = this.dataset.id;
            if (!confirm('Are you sure you want to remove this comic page?')) return;

            const formData = new FormData();
            formData.append('id', panelId);

            fetch('api_comic.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`.panel-card[data-id="${panelId}"]`);
                    if (card) card.remove();
                    updateBadges();
                    showStatus('✓ Panel removed', true);
                } else {
                    showStatus('Error: ' + (data.error || 'Failed to delete'), false);
                }
            })
            .catch(() => showStatus('Network error deleting panel', false));
        });
    });

    // Panel Title Inline Updates
    document.querySelectorAll('.panel-title-input').forEach(input => {
        input.addEventListener('change', function() {
            const panelId = this.dataset.id;
            const title = this.value;

            const formData = new FormData();
            formData.append('id', panelId);
            formData.append('title', title);

            fetch('api_comic.php?action=update_panel', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showStatus('✓ Page title updated', true);
                }
            });
        });
    });

    // File Preview Handler
    const fileInput = document.getElementById('panel_file');
    const previewWrap = document.getElementById('new-panel-preview');
    const previewImg = document.getElementById('new-panel-img');
    const selectAsset = document.getElementById('existing_asset');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (selectAsset) {
        selectAsset.addEventListener('change', function() {
            if (this.value) {
                previewImg.src = '../' + this.value;
                previewWrap.style.display = 'block';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
