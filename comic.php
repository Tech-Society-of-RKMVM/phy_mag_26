<?php
/**
 * Interactive Comic Book Page
 * Department of Physics Wall Magazine
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

// Fetch Comic Settings
$comicTitle = get_setting('comic_title', 'Department Of Physics Comic');
$comicTopText = get_setting('comic_top_text', '');
$comicBottomText = get_setting('comic_bottom_text', '');

// Fetch Panels
$stmt = $pdo->query("SELECT id, title, image_path, sort_order FROM comic_panels ORDER BY sort_order ASC, id ASC");
$panels = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $comicTitle . ' - Department of Physics Wall Magazine';
$activePage = 'comic';
$isArticlePage = false;

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero comic-hero">
  <h2><?= e($comicTitle) ?></h2>
  <p>An original scientific and creative comic serial presented by the Department of Physics, Ramakrishna Mission Vidyamandira.</p>
</section>

<main class="comic-main-container">
  <?php if (!empty($comicTopText)): ?>
    <section class="comic-prologue-box">
      <div class="comic-text-content">
        <?= $comicTopText ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (empty($panels)): ?>
    <div class="comic-empty-state">
      <div class="empty-icon">🎨</div>
      <h3>Comic Under Production</h3>
      <p>The comic panels are currently being inked and lettered by our department artists. Please check back shortly!</p>
      <?php if (is_admin_logged_in()): ?>
        <a href="admin/comic.php" class="btn-comic-action">Go to Admin Comic Editor &rarr;</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <!-- Interactive Comic Reader App -->
    <div id="comic-reader-app" data-panels='<?= json_encode($panels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'>
      
      <!-- Top Control Toolbar -->
      <div class="comic-toolbar">
        <div class="toolbar-left">
          <span class="toolbar-title">📖 <?= e($comicTitle) ?></span>
          <span id="comic-page-num" class="page-badge">Loading pages...</span>
        </div>

        <div class="toolbar-center">
          <div class="mode-toggle-group">
            <button type="button" id="btn-mode-book" class="mode-btn active" title="Flipbook View">
              📖 Book Flip
            </button>
            <button type="button" id="btn-mode-scroll" class="mode-btn" title="Continuous Scroll View">
              📜 Webtoon Scroll
            </button>
          </div>
        </div>

        <div class="toolbar-right">
          <button type="button" id="comic-fullscreen-btn" class="tool-icon-btn" title="Toggle Fullscreen">
            ⛶ Fullscreen
          </button>
        </div>
      </div>

      <!-- Main Book Viewport -->
      <div id="comic-reader-viewport" class="comic-viewport-wrapper">
        
        <!-- Book Flip View -->
        <div id="book-viewport" class="book-perspective-area">
          <button type="button" id="comic-prev-btn" class="book-nav-arrow arrow-left" aria-label="Previous Page" title="Previous (Left Arrow)">
            &#10094;
          </button>

          <div id="book-stage" class="book-3d-stage">
            <!-- Rendered dynamically by comic-reader.js -->
          </div>

          <button type="button" id="comic-next-btn" class="book-nav-arrow arrow-right" aria-label="Next Page" title="Next (Right Arrow)">
            &#10095;
          </button>
        </div>

        <!-- Continuous Scroll View (hidden by default) -->
        <div id="comic-scroll-view" class="comic-scroll-container" style="display: none;">
          <!-- Rendered dynamically by comic-reader.js -->
        </div>

      </div>

      <!-- Bottom Reader Scrubber Bar -->
      <div class="comic-scrubber-bar">
        <input type="range" id="comic-scrubber" min="1" max="<?= count($panels) ?>" value="1" step="1" title="Jump to page">
      </div>

      <!-- Thumbnail Strip Drawer -->
      <div class="comic-thumb-drawer">
        <div id="comic-thumbnails" class="comic-thumb-strip">
          <!-- Rendered by comic-reader.js -->
        </div>
      </div>

      <div class="comic-nav-hint">
        <span>💡 <strong>Tip:</strong> Use <kbd>&larr;</kbd> <kbd>&rarr;</kbd> arrow keys or swipe on mobile to turn pages.</span>
      </div>

    </div>
  <?php endif; ?>

  <?php if (!empty($comicBottomText)): ?>
    <section class="comic-epilogue-box">
      <div class="comic-text-content">
        <?= $comicBottomText ?>
      </div>
    </section>
  <?php endif; ?>

  <div style="margin-top: 2.5rem; text-align: center;">
    <a href="index.php" class="back-link">&larr; Back to Magazine Articles</a>
  </div>
</main>

<script src="assets/js/comic-reader.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
