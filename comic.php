<?php
/**
 * Department Comic Serial Page
 * Clean vertical continuous reading format
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

// Fetch Comic Settings
$comicTitle = get_setting('comic_title', 'Department Of Physics Comic Issue #1');
$comicTopText = get_setting('comic_top_text', '');
$comicBottomText = get_setting('comic_bottom_text', '');

// Fetch Panels in Sort Order
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
    <section class="comic-text-box comic-prologue">
      <div class="comic-text-content">
        <?= $comicTopText ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (empty($panels)): ?>
    <div class="comic-empty-state">
      <div class="empty-icon">🎨</div>
      <h3>Comic Under Production</h3>
      <p>The comic panels are currently being prepared by our department artists. Please check back shortly!</p>

    </div>
  <?php else: ?>
    <!-- Sequential Comic Panels (One per row) -->
    <div class="comic-stream">
      <?php foreach ($panels as $index => $panel): ?>
        <article class="comic-panel-row">
          <?php if (!empty($panel['title'])): ?>
            <div class="comic-panel-header">
              <span class="comic-panel-num">Page <?= $index + 1 ?></span>
              <h3 class="comic-panel-title"><?= e($panel['title']) ?></h3>
            </div>
          <?php endif; ?>

          <div class="comic-image-card">
            <img src="<?= e($panel['image_path']) ?>" alt="Comic Panel <?= $index + 1 ?>" loading="lazy">
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($comicBottomText)): ?>
    <section class="comic-text-box comic-epilogue">
      <div class="comic-text-content">
        <?= $comicBottomText ?>
      </div>
    </section>
  <?php endif; ?>

  <div style="margin-top: 3rem; text-align: center;">
    <a href="index.php" class="back-link">&larr; Back to Magazine Articles</a>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
