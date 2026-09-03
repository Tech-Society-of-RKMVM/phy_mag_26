<?php
/**
 * Shared Header Component
 */
if (!isset($pageTitle)) {
    $pageTitle = 'Department Of Physics - Wall Magazine';
}
if (!isset($activePage)) {
    $activePage = 'this_year';
}
$isArticleHeader = !empty($isArticlePage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

<?php if ($isArticleHeader): ?>
<header class="article-header">
  <a href="index.php"><img src="assets/images/rkm.jpg" alt="RKMV Logo" class="logo"></a>
  <h1>RKMV Physics Department Annual Magazine</h1>
  <nav>
    <ul>
      <li><a class="<?= $activePage === 'this_year' ? 'active' : '' ?>" href="index.php">This Year</a></li>
      <li><a class="<?= $activePage === 'previous' ? 'active' : '' ?>" href="previous.php">Previous Year</a></li>
      <li><a class="<?= $activePage === 'about' ? 'active' : '' ?>" href="about.php">About Us</a></li>
    </ul>
  </nav>
</header>
<?php else: ?>
<header>
  <a href="index.php"><img src="assets/images/rkm-removebg-preview.png" alt="RKMV Logo" class="logo"></a>
  <h3>Ramakrishna Mission Vidyamandira</h3>
  <h1>Department Of Physics</h1>
  <h1>Wall Magazine</h1>
  <nav>
    <ul>
      <li><a class="<?= $activePage === 'this_year' ? 'active' : '' ?>" href="index.php">This Year</a></li>
      <li><a class="<?= $activePage === 'previous' ? 'active' : '' ?>" href="previous.php">Previous Year</a></li>
      <li><a class="<?= $activePage === 'about' ? 'active' : '' ?>" href="about.php">About Us</a></li>
    </ul>
  </nav>
</header>
<?php endif; ?>
