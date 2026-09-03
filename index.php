<?php
/**
 * Homepage - This Year Edition
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

// Fetch editorial settings
$editorialTitle = get_setting('editorial_title', 'The Editorial');
$editorialContent = get_setting('editorial_content', '');

// Fetch published articles for current year edition
$stmt = $pdo->prepare("
    SELECT id, slug, title, summary, author_name, author_batch, image_path 
    FROM articles 
    WHERE status = 'published' AND edition_year = 2026 
    ORDER BY sort_order ASC, id ASC
");
$stmt->execute();
$articles = $stmt->fetchAll();

// If no 2026 articles found, fetch all published articles
if (empty($articles)) {
  $stmt = $pdo->query("SELECT id, slug, title, summary, author_name, author_batch, image_path FROM articles WHERE status = 'published' ORDER BY sort_order ASC, id ASC");
  $articles = $stmt->fetchAll();
}

$pageTitle = 'This Year - Physics Wall Magazine';
$activePage = 'this_year';
$isArticlePage = false;

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <h2><?= e($editorialTitle) ?></h2>
  <?php if (!empty($editorialContent)): ?>
    <?= $editorialContent ?>
  <?php else: ?>
    <p>Welcome to the Physics Department Wall Magazine.</p>
  <?php endif; ?>
</section>

<main>
  <section class="articles">
    <?php if (!empty($articles)): ?>
      <?php foreach ($articles as $article): ?>
        <a href="article.php?slug=<?= urlencode($article['slug']) ?>">
          <article>
            <img src="<?= e($article['image_path']) ?>" alt="<?= e($article['title']) ?>">
            <h3><?= e($article['title']) ?></h3>
            <div class="article-card-meta">
              <span><?= e($article['author_name']) ?></span>
              <?php if (!empty($article['author_batch'])): ?>
                <span class="batch-badge"><?= e($article['author_batch']) ?></span>
              <?php endif; ?>
            </div>
            <p class="article-summary"><?= e($article['summary']) ?></p>
          </article>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align: center; grid-column: 1 / -1; color: #777;">No articles published yet.</p>
    <?php endif; ?>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>