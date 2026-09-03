<?php
/**
 * Single Dynamic Article View
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$article = null;
if (!empty($slug)) {
  $stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ? AND status = 'published' LIMIT 1");
  $stmt->execute([$slug]);
  $article = $stmt->fetch();
} elseif ($id > 0) {
  $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND status = 'published' LIMIT 1");
  $stmt->execute([$id]);
  $article = $stmt->fetch();
}

if (!$article) {
  // 404 handler
  header("HTTP/1.0 404 Not Found");
  $pageTitle = 'Article Not Found';
  $activePage = 'this_year';
  $isArticlePage = true;
  require_once __DIR__ . '/includes/header.php';
  echo '<main class="article-container" style="text-align: center; padding: 5rem 2rem;">';
  echo '<h2>Article Not Found</h2>';
  echo '<p>The requested article could not be found or has been removed.</p>';
  echo '<a href="index.php" class="back-link">&larr; Back to This Year\'s Articles</a>';
  echo '</main>';
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

// Increment view count
$updateViews = $pdo->prepare("UPDATE articles SET views_count = views_count + 1 WHERE id = ?");
$updateViews->execute([$article['id']]);

$pageTitle = $article['title'];
$activePage = ($article['edition_year'] == 2026) ? 'this_year' : 'previous';
$isArticlePage = true;

require_once __DIR__ . '/includes/header.php';
?>

<main class="article-container">
  <h2><?= e($article['title']) ?></h2>
  <div class="article-meta">
    <span>Published: <?= e(format_article_date($article['published_date'])) ?></span>
    <span>&mdash;</span>
    <span>Author: <strong><?= e($article['author_name']) ?></strong></span>
    <?php if (!empty($article['author_batch'])): ?>
      <span class="batch-tag"><?= e($article['author_batch']) ?></span>
    <?php endif; ?>
  </div>

  <?php if (!empty($article['image_path'])): ?>
    <img src="<?= e($article['image_path']) ?>" alt="<?= e($article['title']) ?>" class="featured-image">
  <?php endif; ?>

  <div class="article-body">
    <?= $article['content'] ?>
  </div>

  <a href="index.php" class="back-link">&larr; Back to This Year's Articles</a>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>