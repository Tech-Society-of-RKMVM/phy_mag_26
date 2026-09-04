<?php
/**
 * Admin Backgrounds & Atmospheric Haze Manager
 * Full-Body Background Slideshow, Custom Photos, and Realtime Haze Customizer
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$pdo = get_db_connection();

$adminPageTitle = 'Background & Atmosphere Manager';
$adminActiveTab = 'backgrounds';

$message = '';
$error = '';

// Handle POST: Update Full-Page Atmosphere & Haze Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_global_bg'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please reload and try again.';
    } else {
        $bgEnabled = isset($_POST['bg_enabled']) ? '1' : '0';
        $bgStyle = trim($_POST['bg_overlay_style'] ?? 'warm_amber');
        $bgBrightness = (float)($_POST['bg_brightness'] ?? 0.65);
        $bgBlur = (int)($_POST['bg_blur'] ?? 0);
        $bgSpeed = (int)($_POST['bg_transition_speed'] ?? 7);

        set_setting('bg_enabled', $bgEnabled);
        set_setting('bg_overlay_style', $bgStyle);
        set_setting('bg_brightness', (string)$bgBrightness);
        set_setting('bg_blur', (string)$bgBlur);
        set_setting('bg_transition_speed', (string)$bgSpeed);

        $message = 'Full-body background atmosphere settings saved successfully!';
    }
}

// Handle POST: Add New Background Image to Rotation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_background'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $title = trim($_POST['bg_title'] ?? '');
        $chosenAsset = trim($_POST['existing_asset'] ?? '');
        $finalPath = '';

        if (isset($_FILES['bg_file']) && $_FILES['bg_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handle_image_upload('bg_file', 'backgrounds');
            if ($uploadResult['success']) {
                $finalPath = $uploadResult['path'];
            } else {
                $error = $uploadResult['error'];
            }
        } elseif (!empty($chosenAsset)) {
            $finalPath = $chosenAsset;
        }

        if (empty($finalPath) && empty($error)) {
            $error = 'Please upload a background image or select an existing photo.';
        }

        if (empty($error)) {
            $maxOrder = (int)$pdo->query("SELECT MAX(sort_order) FROM background_images")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO background_images (image_path, title, is_active, sort_order) VALUES (?, ?, 1, ?)");
            $stmt->execute([$finalPath, $title ?: basename($finalPath), $maxOrder + 1]);
            $message = 'New background photo added to rotation successfully!';
        }
    }
}

// Fetch Global Background Settings
$bgConfig = get_background_config();

// Fetch background rotation images
$stmt = $pdo->query("SELECT id, image_path, title, is_active, sort_order FROM background_images ORDER BY sort_order ASC, id ASC");
$backgrounds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Available Images for Quick-Adding
$availableImages = glob(__DIR__ . '/../assets/images/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- Include SortableJS for Drag-and-Drop Reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header">
    <h2>🖼️ Full-Body Background &amp; Atmospheric Haze Manager</h2>
    <div>
      <a href="../index.php" target="_blank" class="btn btn-primary btn-sm">🌐 View Live Website</a>
    </div>
  </div>
  <div class="card-body">
    <p style="color: #64748b; margin-bottom: 0;">
      Control the full-page atmospheric background across the entire website. When you upload or select a custom photo, the interactive simulator below updates <strong>instantly in real time</strong>.
    </p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<!-- Save status toast -->
<div id="save-status" style="display: none; margin-bottom: 1.5rem; padding: 0.8rem 1.2rem; border-radius: 6px; font-weight: 600;"></div>

<!-- Realtime Live Interactive Simulator Card -->
<div class="card" style="margin-bottom: 2rem; border: 2px solid #3b82f6;">
  <div class="card-header" style="background: linear-gradient(135deg, #eff6ff, #f8fafc); border-bottom: 1px solid #bfdbfe;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <span style="font-size: 1.5rem;">✨</span>
      <div>
        <h2 style="font-size: 1.2rem; color: #1e3a8a; margin: 0;">Real-Time Atmospheric Preview Simulator</h2>
        <small style="color: #64748b;">Updates live as you choose photos, adjust brightness, blur, or switch overlay tones</small>
      </div>
    </div>
    <div style="display: flex; gap: 0.5rem;">
      <button type="button" id="btn-toggle-preview-mode" class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">
        🔄 Switch Preview: Card / Hero
      </button>
    </div>
  </div>
  <div class="card-body" style="padding: 1.5rem;">
    
    <!-- Viewport Box -->
    <div id="realtime-viewport" class="realtime-viewport-box">
      
      <!-- Background Photo Layer -->
      <div id="sim-bg-layer" class="sim-bg-layer" style="background-image: url('../<?= !empty($backgrounds[0]['image_path']) ? e($backgrounds[0]['image_path']) : 'assets/images/1000089462.jpg' ?>');"></div>
      
      <!-- Gradient Overlay Haze Layer -->
      <div id="sim-overlay-layer" class="sim-overlay-layer <?= 'bg-overlay-' . e($bgConfig['overlay_style']) ?>"></div>

      <!-- Preview Mode A: Floating Frosted Glass Article Card -->
      <div id="preview-item-card" class="sim-floating-card">
        <div class="sim-card-photo" style="background-image: url('../assets/images/Maglev.webp');">
          <span class="sim-card-badge">Physics Article</span>
        </div>
        <div class="sim-card-content">
          <div class="sim-card-tag">UG 3 &bull; Arka Biswas</div>
          <h3 class="sim-card-title">Quantum Levitation &amp; Maglev Superconductors</h3>
          <p class="sim-card-desc">
            Investigating Type-II superconductors, Meissner effect flux pinning, and zero-resistance electromagnetic transport...
          </p>
        </div>
      </div>

      <!-- Preview Mode B: Editorial Hero Banner (Initially Hidden) -->
      <div id="preview-item-hero" class="sim-floating-hero" style="display: none;">
        <span style="display: inline-block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #b45309; margin-bottom: 0.4rem;">
          Department Editorial
        </span>
        <h2 style="font-family: 'Crimson Pro', Georgia, serif; font-size: 1.5rem; color: #1e293b; margin-bottom: 0.5rem;">
          The Department of Physics Wall Magazine
        </h2>
        <p style="font-size: 0.82rem; color: #475569; line-height: 1.5; margin: 0 auto; max-width: 500px;">
          A creative platform for students to explore quantum mechanics, astrophysics, and computational physics.
        </p>
      </div>

      <!-- Live Photo Info Label -->
      <div id="sim-photo-label" class="sim-photo-info-tag">
        📷 Active Photo: <span id="sim-photo-name"><?= !empty($backgrounds[0]['image_path']) ? e(basename($backgrounds[0]['image_path'])) : '1000089462.jpg' ?></span>
      </div>

    </div>

  </div>
</div>

<div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 2rem; align-items: start;">

  <!-- Left Column: Atmosphere Controls & Upload -->
  <div>
    
    <!-- Atmosphere & Haze Customizer -->
    <div class="card" style="margin-bottom: 2rem;">
      <div class="card-header">
        <h2>🎨 Full-Body Atmosphere &amp; Haze Controls</h2>
      </div>
      <div class="card-body">
        <form method="POST" action="backgrounds.php" id="atmosphere-form">
          <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
          <input type="hidden" name="action_save_global_bg" value="1">

          <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <label style="margin-bottom: 0.2rem; font-weight: 700;">Enable Full-Body Atmospheric Background</label>
              <small style="color: #64748b; display: block;">Displays full-screen photo rotation with frosted glass depth</small>
            </div>
            <label class="toggle-switch-label">
              <input type="checkbox" name="bg_enabled" value="1" <?= $bgConfig['enabled'] ? 'checked' : '' ?>>
              <span class="toggle-slider"></span>
            </label>
          </div>

          <hr style="margin: 1.25rem 0; border: 0; border-top: 1px solid #e2e8f0;">

          <!-- Overlay Style Presets with Instant Realtime Switching -->
          <div class="form-group">
            <label style="font-weight: 700; margin-bottom: 0.5rem; display: block;">Atmospheric Overlay Tone Preset</label>
            
            <div class="preset-options-grid">
              
              <label class="preset-card <?= $bgConfig['overlay_style'] === 'warm_amber' ? 'selected' : '' ?>">
                <input type="radio" name="bg_overlay_style" value="warm_amber" <?= $bgConfig['overlay_style'] === 'warm_amber' ? 'checked' : '' ?>>
                <div class="preset-swatch swatch-warm"></div>
                <div class="preset-info">
                  <strong>Warm Heritage Amber</strong>
                  <small>Belur Math warm golden glow</small>
                </div>
              </label>

              <label class="preset-card <?= $bgConfig['overlay_style'] === 'dark_atmospheric' ? 'selected' : '' ?>">
                <input type="radio" name="bg_overlay_style" value="dark_atmospheric" <?= $bgConfig['overlay_style'] === 'dark_atmospheric' ? 'checked' : '' ?>>
                <div class="preset-swatch swatch-dark"></div>
                <div class="preset-info">
                  <strong>Dark Cosmic Atmosphere</strong>
                  <small>Deep slate high contrast</small>
                </div>
              </label>

              <label class="preset-card <?= $bgConfig['overlay_style'] === 'deep_teal' ? 'selected' : '' ?>">
                <input type="radio" name="bg_overlay_style" value="deep_teal" <?= $bgConfig['overlay_style'] === 'deep_teal' ? 'checked' : '' ?>>
                <div class="preset-swatch swatch-teal"></div>
                <div class="preset-info">
                  <strong>Scientific Deep Teal</strong>
                  <small>Oceanic physics department palette</small>
                </div>
              </label>

              <label class="preset-card <?= $bgConfig['overlay_style'] === 'glass_minimal' ? 'selected' : '' ?>">
                <input type="radio" name="bg_overlay_style" value="glass_minimal" <?= $bgConfig['overlay_style'] === 'glass_minimal' ? 'checked' : '' ?>>
                <div class="preset-swatch swatch-minimal"></div>
                <div class="preset-info">
                  <strong>Minimal Frosted Crystal</strong>
                  <small>Clean translucent white veil</small>
                </div>
              </label>

            </div>
          </div>

          <!-- Brightness Slider with Realtime Feedback -->
          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
              <label for="bg_brightness" style="margin-bottom: 0; font-weight: 700;">Photo Brightness</label>
              <span id="val-brightness" style="font-weight: 700; color: #3b82f6;"><?= (int)($bgConfig['brightness'] * 100) ?>%</span>
            </div>
            <input type="range" id="bg_brightness" name="bg_brightness" class="range-slider" min="0.2" max="1.0" step="0.05" value="<?= $bgConfig['brightness'] ?>">
          </div>

          <!-- Blur Slider with Realtime Feedback -->
          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
              <label for="bg_blur" style="margin-bottom: 0; font-weight: 700;">Atmospheric Soft Blur</label>
              <span id="val-blur" style="font-weight: 700; color: #3b82f6;"><?= $bgConfig['blur'] ?>px</span>
            </div>
            <input type="range" id="bg_blur" name="bg_blur" class="range-slider" min="0" max="15" step="1" value="<?= $bgConfig['blur'] ?>">
          </div>

          <!-- Slideshow Interval -->
          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
              <label for="bg_transition_speed" style="margin-bottom: 0; font-weight: 700;">Slideshow Rotation Interval</label>
              <span id="val-speed" style="font-weight: 700; color: #3b82f6;"><?= $bgConfig['transition_speed'] ?> sec</span>
            </div>
            <input type="range" id="bg_transition_speed" name="bg_transition_speed" class="range-slider" min="3" max="20" step="1" value="<?= $bgConfig['transition_speed'] ?>">
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1.5rem; font-size: 1rem;">
            💾 Save Full-Body Atmosphere
          </button>
        </form>
      </div>
    </div>

    <!-- Upload / Add New Background Photo Form (Live Preview on Choose) -->
    <div class="card">
      <div class="card-header">
        <h2>➕ Upload &amp; Preview New Photo</h2>
      </div>
      <div class="card-body">
        <form method="POST" action="backgrounds.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
          <input type="hidden" name="action_add_background" value="1">

          <div class="form-group">
            <label for="bg_title">Background Label / Description (Optional)</label>
            <input type="text" id="bg_title" name="bg_title" class="form-control" placeholder="e.g. Vidyamandira Physics Lab">
          </div>

          <!-- File Upload with Instant Client-Side Image Loading into Simulator -->
          <div class="form-group">
            <label for="bg_file" style="font-weight: 700;">Choose Photo from Computer (Instant Live Preview)</label>
            <input type="file" id="bg_file" name="bg_file" class="form-control" accept="image/*">
            <small style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 0.25rem;">
              💡 Selecting an image file immediately updates the live simulator above so you can see how it looks before uploading!
            </small>
          </div>

          <!-- Existing Images Dropdown with Instant Simulator Switching -->
          <div class="form-group">
            <label for="existing_asset" style="font-weight: 700;">Or Pick Existing Project Photo</label>
            <select id="existing_asset" name="existing_asset" class="form-control">
              <option value="">-- Choose an existing photo to preview --</option>
              <?php foreach ($availableImages as $img): 
                $rel = str_replace(__DIR__ . '/../', '', str_replace('\\', '/', $img));
              ?>
                <option value="<?= e($rel) ?>"><?= e(basename($rel)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%;">
            ➕ Add Photo to Rotation
          </button>
        </form>
      </div>
    </div>

  </div>

  <!-- Right Column: Current Backgrounds Rotation List -->
  <div>
    <div class="card">
      <div class="card-header">
        <h2>🔄 Active Background Rotation (<?= count($backgrounds) ?> Photos)</h2>
        <span style="font-size: 0.85rem; color: #64748b;">Drag to reorder &bull; Click to preview</span>
      </div>
      <div class="card-body">
        <?php if (empty($backgrounds)): ?>
          <p style="text-align: center; color: #64748b; padding: 2rem;">
            No custom backgrounds added yet. The site will use default department photography.
          </p>
        <?php else: ?>
          <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
            Click on any photo thumbnail below to immediately preview it in the simulator above:
          </p>

          <div id="bg-sortable-list" class="bg-sortable-container">
            <?php foreach ($backgrounds as $index => $bg): ?>
              <div class="bg-card-item" data-id="<?= $bg['id'] ?>" data-path="<?= e($bg['image_path']) ?>" data-title="<?= e($bg['title'] ?: basename($bg['image_path'])) ?>">
                <div class="bg-handle">☰</div>
                
                <div class="bg-thumb-preview" title="Click to preview in simulator">
                  <img src="../<?= e($bg['image_path']) ?>" alt="<?= e($bg['title']) ?>">
                </div>

                <div class="bg-item-meta">
                  <strong><?= e($bg['title'] ?: basename($bg['image_path'])) ?></strong>
                  <code style="font-size: 0.75rem; color: #64748b;"><?= e($bg['image_path']) ?></code>
                </div>

                <div class="bg-item-controls">
                  <label class="toggle-switch-label" title="Toggle active in rotation">
                    <input type="checkbox" class="bg-toggle-checkbox" data-id="<?= $bg['id'] ?>" <?= $bg['is_active'] ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                  </label>
                  <button type="button" class="btn-delete-bg" data-id="<?= $bg['id'] ?>" title="Remove photo">
                    🗑
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="margin-top: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" id="btn-save-bg-order" class="btn btn-primary btn-sm">
              💾 Save Order
            </button>
            <span style="font-size: 0.85rem; color: #64748b;">Order auto-saves on drag</span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<style>
/* Realtime Viewport Simulator Box */
.realtime-viewport-box {
  position: relative;
  height: 380px;
  width: 100%;
  border-radius: 14px;
  overflow: hidden;
  background: #0f172a;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0,0,0,0.20), inset 0 0 0 1px rgba(255,255,255,0.1);
}

.sim-bg-layer {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  background-size: cover;
  background-position: center;
  transition: filter 0.3s ease, background-image 0.4s ease;
}

.sim-overlay-layer {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  pointer-events: none;
  transition: background 0.4s ease;
}

/* Simulated Floating Frosted Glass Card */
.sim-floating-card {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 440px;
  background: rgba(255, 255, 255, 0.90);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1px solid rgba(255, 255, 255, 0.65);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 15px 40px rgba(0,0,0,0.18), 0 1px 3px rgba(0,0,0,0.06);
  display: flex;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.sim-floating-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

.sim-card-photo {
  width: 140px;
  background-size: cover;
  background-position: center;
  position: relative;
  flex-shrink: 0;
}

.sim-card-badge {
  position: absolute;
  top: 8px; left: 8px;
  background: rgba(0,0,0,0.65);
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  backdrop-filter: blur(4px);
}

.sim-card-content {
  padding: 1.25rem;
  flex: 1;
}

.sim-card-tag {
  font-size: 0.72rem;
  font-weight: 700;
  color: #b45309;
  margin-bottom: 0.25rem;
}

.sim-card-title {
  font-family: 'Crimson Pro', Georgia, serif;
  font-size: 1.15rem;
  color: #1e293b;
  line-height: 1.3;
  margin-bottom: 0.35rem;
  font-weight: 700;
}

.sim-card-desc {
  font-size: 0.78rem;
  color: #475569;
  line-height: 1.45;
  margin: 0;
}

/* Simulated Hero Banner */
.sim-floating-hero {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 580px;
  background: linear-gradient(135deg, rgba(255, 246, 229, 0.94), rgba(255, 255, 255, 0.92));
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1px solid rgba(255, 255, 255, 0.7);
  border-top: 4px solid #b45309;
  border-bottom: 4px solid #b45309;
  border-radius: 12px;
  padding: 1.75rem 2rem;
  text-align: center;
  box-shadow: 0 15px 40px rgba(0,0,0,0.18);
}

/* Info Tag on Simulator */
.sim-photo-info-tag {
  position: absolute;
  bottom: 12px; left: 14px;
  z-index: 3;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(8px);
  color: #f1f5f9;
  font-size: 0.75rem;
  padding: 4px 10px;
  border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.15);
  pointer-events: none;
}

/* Preset Cards */
.preset-options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.preset-card {
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #ffffff;
  transition: all 0.2s;
}

.preset-card input {
  display: none;
}

.preset-card.selected {
  border-color: #3b82f6;
  background: #eff6ff;
  box-shadow: 0 0 0 1px #3b82f6;
}

.preset-swatch {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  flex-shrink: 0;
  border: 1px solid rgba(0,0,0,0.1);
}

.swatch-warm {
  background: linear-gradient(135deg, #b45309, #fef3c7);
}

.swatch-dark {
  background: linear-gradient(135deg, #0f172a, #334155);
}

.swatch-teal {
  background: linear-gradient(135deg, #0f766e, #ccfbf1);
}

.swatch-minimal {
  background: linear-gradient(135deg, #e2e8f0, #ffffff);
}

.preset-info strong {
  display: block;
  font-size: 0.85rem;
  color: #1e293b;
}

.preset-info small {
  color: #64748b;
  font-size: 0.75rem;
}

.range-slider {
  width: 100%;
  accent-color: #3b82f6;
  cursor: pointer;
}

/* Sortable list styles */
.bg-sortable-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.bg-card-item {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
  transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
  cursor: pointer;
}

.bg-card-item:hover {
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  border-color: #93c5fd;
}

.bg-card-item.sortable-ghost {
  opacity: 0.4;
  border: 2px dashed #3b82f6;
  background: #eff6ff;
}

.bg-handle {
  cursor: grab;
  color: #94a3b8;
  font-size: 1.2rem;
  user-select: none;
}

.bg-handle:active {
  cursor: grabbing;
}

.bg-thumb-preview {
  width: 60px;
  height: 42px;
  border-radius: 4px;
  overflow: hidden;
  background: #000;
  flex-shrink: 0;
  border: 1px solid #cbd5e1;
}

.bg-thumb-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.bg-item-meta {
  flex: 1;
  min-width: 0;
}

.bg-item-meta strong {
  display: block;
  font-size: 0.95rem;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.bg-item-controls {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.btn-delete-bg {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fca5a5;
  border-radius: 4px;
  padding: 0.35rem 0.6rem;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-delete-bg:hover {
  background: #ef4444;
  color: white;
}

/* Toggle Switch */
.toggle-switch-label {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  cursor: pointer;
}

.toggle-switch-label input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #cbd5e1;
  border-radius: 24px;
  transition: 0.3s;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: 0.3s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

input:checked + .toggle-slider {
  background-color: #10b981;
}

input:checked + .toggle-slider:before {
  transform: translateX(20px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('bg-sortable-list');
    const saveOrderBtn = document.getElementById('btn-save-bg-order');
    const statusBox = document.getElementById('save-status');

    // Simulator Elements
    const simBg = document.getElementById('sim-bg-layer');
    const simOverlay = document.getElementById('sim-overlay-layer');
    const simPhotoName = document.getElementById('sim-photo-name');
    const previewCard = document.getElementById('preview-item-card');
    const previewHero = document.getElementById('preview-item-hero');
    const togglePreviewBtn = document.getElementById('btn-toggle-preview-mode');

    // Form Controls
    const sliderBrightness = document.getElementById('bg_brightness');
    const sliderBlur = document.getElementById('bg_blur');
    const sliderSpeed = document.getElementById('bg_transition_speed');
    const valBrightness = document.getElementById('val-brightness');
    const valBlur = document.getElementById('val-blur');
    const valSpeed = document.getElementById('val-speed');
    const presetCards = document.querySelectorAll('.preset-card');

    // Photo Input Controls
    const fileInput = document.getElementById('bg_file');
    const assetSelect = document.getElementById('existing_asset');

    // Update Simulator Styles
    function updateSimulator() {
        if (!simBg || !simOverlay) return;
        const bVal = sliderBrightness ? sliderBrightness.value : 0.65;
        const blurVal = sliderBlur ? sliderBlur.value : 0;
        
        simBg.style.filter = `brightness(${bVal}) ${blurVal > 0 ? `blur(${blurVal}px)` : ''}`;
        
        if (valBrightness) valBrightness.textContent = Math.round(bVal * 100) + '%';
        if (valBlur) valBlur.textContent = blurVal + 'px';
        if (valSpeed && sliderSpeed) valSpeed.textContent = sliderSpeed.value + ' sec';

        const checkedRadio = document.querySelector('input[name="bg_overlay_style"]:checked');
        if (checkedRadio) {
            simOverlay.className = 'sim-overlay-layer bg-overlay-' + checkedRadio.value;
        }
    }

    // Set Simulator Background Image
    function setSimBg(url, label) {
        if (!simBg) return;
        simBg.style.backgroundImage = `url('${url}')`;
        if (simPhotoName && label) {
            simPhotoName.textContent = label;
        }
    }

    // 1. Instant preview when user chooses a file from computer
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const objectUrl = URL.createObjectURL(file);
                setSimBg(objectUrl, file.name + ' (Local Preview)');
            }
        });
    }

    // 2. Instant preview when user selects an existing project asset
    if (assetSelect) {
        assetSelect.addEventListener('change', function() {
            if (this.value) {
                setSimBg('../' + this.value, this.value.split('/').pop());
            }
        });
    }

    // 3. Instant preview when clicking any item in the rotation list
    document.querySelectorAll('.bg-card-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't trigger when clicking checkboxes or delete button
            if (e.target.closest('.bg-item-controls') || e.target.closest('.bg-handle')) return;
            const path = this.dataset.path;
            const title = this.dataset.title;
            if (path) {
                setSimBg('../' + path, title || path.split('/').pop());
                document.getElementById('realtime-viewport')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // 4. Sliders live feedback
    if (sliderBrightness) sliderBrightness.addEventListener('input', updateSimulator);
    if (sliderBlur) sliderBlur.addEventListener('input', updateSimulator);
    if (sliderSpeed) sliderSpeed.addEventListener('input', updateSimulator);

    // 5. Preset card selection
    presetCards.forEach(card => {
        card.addEventListener('click', function() {
            presetCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                updateSimulator();
            }
        });
    });

    // 6. Switch preview between Card and Hero Banner
    if (togglePreviewBtn && previewCard && previewHero) {
        togglePreviewBtn.addEventListener('click', function() {
            if (previewCard.style.display === 'none') {
                previewCard.style.display = 'flex';
                previewHero.style.display = 'none';
            } else {
                previewCard.style.display = 'none';
                previewHero.style.display = 'block';
            }
        });
    }

    updateSimulator();

    // Toast function
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

    // SortableJS Drag & Drop
    if (list) {
        new Sortable(list, {
            animation: 150,
            handle: '.bg-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                saveOrder(true);
            }
        });
    }

    function saveOrder(isAuto = false) {
        const items = list.querySelectorAll('.bg-card-item');
        const order = Array.from(items).map(item => parseInt(item.dataset.id, 10));

        fetch('api_backgrounds.php?action=reorder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order: order })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showStatus(isAuto ? '✓ Order auto-saved' : '✓ Background order saved successfully!', true);
            } else {
                showStatus('Error: ' + (data.error || 'Failed to save order'), false);
            }
        })
        .catch(() => showStatus('Network error saving order', false));
    }

    if (saveOrderBtn) {
        saveOrderBtn.addEventListener('click', () => saveOrder(false));
    }

    // Toggle Active in Rotation
    document.querySelectorAll('.bg-toggle-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = this.dataset.id;
            const active = this.checked ? 1 : 0;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('active', active);

            fetch('api_backgrounds.php?action=toggle', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showStatus(active ? '✓ Photo added to rotation' : '✓ Photo removed from rotation', true);
                } else {
                    showStatus('Error: ' + (data.error || 'Toggle failed'), false);
                }
            })
            .catch(() => showStatus('Network error toggling background', false));
        });
    });

    // Delete Photo
    document.querySelectorAll('.btn-delete-bg').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            if (!confirm('Are you sure you want to remove this background photo?')) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('api_backgrounds.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`.bg-card-item[data-id="${id}"]`);
                    if (item) item.remove();
                    showStatus('✓ Background deleted', true);
                } else {
                    showStatus('Error: ' + (data.error || 'Failed to delete'), false);
                }
            })
            .catch(() => showStatus('Network error deleting background', false));
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
