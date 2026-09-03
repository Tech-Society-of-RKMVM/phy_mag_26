<?php
/**
 * Previous Year Edition / Archives
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

// Fetch previous years articles (edition_year < 2026 or specific previous year)
$stmt = $pdo->prepare("
    SELECT id, slug, title, summary, author_name, author_batch, image_path, edition_year 
    FROM articles 
    WHERE status = 'published' AND edition_year < 2026 
    ORDER BY edition_year DESC, sort_order ASC
");
$stmt->execute();
$pastArticles = $stmt->fetchAll();

$pageTitle = 'Previous Year - Physics Wall Magazine';
$activePage = 'previous';
$isArticlePage = false;

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <h2>Previous Year's Highlights</h2>
  <p>A curated collection of the most notable stories and achievements from past editions, preserved for reflection and
    inspiration.</p>
</section>

<main>
  <section class="articles">
    <?php if (!empty($pastArticles)): ?>
      <?php foreach ($pastArticles as $article): ?>
        <a href="article.php?slug=<?= urlencode($article['slug']) ?>">
          <article>
            <img src="<?= e($article['image_path']) ?>" alt="<?= e($article['title']) ?>">
            <h3><?= e($article['title']) ?></h3>
            <div class="article-card-meta">
              <span><?= e($article['author_name']) ?></span>
              <?php if (!empty($article['author_batch'])): ?>
                <span class="batch-badge"><?= e($article['author_batch']) ?></span>
              <?php endif; ?>
              <span style="float: right; color: #999;"><?= e($article['edition_year']) ?></span>
            </div>
            <p class="article-summary"><?= e($article['summary']) ?></p>
          </article>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="text-align: center; grid-column: 1 / -1; padding: 3rem 1rem; color: #666;">
        <h3>Archives are being curated</h3>
        <p style="margin-top: 0.5rem;">Previous year edition articles will appear here once digitized and published via
          the admin portal.</p>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>