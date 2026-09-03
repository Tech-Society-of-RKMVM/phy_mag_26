<?php
/**
 * Homepage Editorial & Site Settings Editor
 */

$adminPageTitle = 'Edit Homepage Editorial';
$adminActiveTab = 'editorial';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = get_db_connection();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $editorialTitle = trim($_POST['editorial_title'] ?? 'The Editorial');
        $rawContent = trim($_POST['editorial_content'] ?? '');

        // If the content doesn't contain HTML tags (<p>), auto-format paragraphs
        if (strpos($rawContent, '<p>') === false && !empty($rawContent)) {
            $paragraphs = preg_split('/\r\n\r\n|\n\n/', $rawContent);
            $formatted = '';
            foreach ($paragraphs as $para) {
                $trimmed = trim($para);
                if (!empty($trimmed)) {
                    $formatted .= "<p>" . htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8') . "</p>\n";
                }
            }
            $editorialContent = $formatted;
        } else {
            $editorialContent = $rawContent;
        }

        $footerText = trim($_POST['footer_text'] ?? '');
        $siteTitle = trim($_POST['site_title'] ?? '');

        set_setting('editorial_title', $editorialTitle);
        set_setting('editorial_content', $editorialContent);
        if (!empty($footerText)) set_setting('footer_text', $footerText);
        if (!empty($siteTitle)) set_setting('site_title', $siteTitle);

        $message = 'Homepage Editorial section updated successfully!';
    }
}

$editorialTitle = get_setting('editorial_title', 'The Editorial');
$editorialContent = get_setting('editorial_content', '');
$footerText = get_setting('footer_text', '© 2026 Wall magazine — RKMV physics department');
$siteTitle = get_setting('site_title', 'Department of Physics - Wall Magazine');
?>

<div class="card" style="max-width: 1000px; margin: 0 auto;">
  <div class="card-header">
    <div>
      <h2>Homepage Editorial Content Editor</h2>
      <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;">
        Update the lead editorial piece featured at the top of the homepage hero section.
      </p>
    </div>
    <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">🌐 Preview Homepage &rarr;</a>
  </div>
  <div class="card-body">
    <?php if (!empty($message)): ?>
      <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="editorial.php">
      <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

      <div class="form-group">
        <label for="editorial_title" style="font-size: 1rem;">Editorial Section Headline</label>
        <input type="text" id="editorial_title" name="editorial_title" class="form-control" style="font-size: 1.1rem; font-weight: 600;" value="<?= e($editorialTitle) ?>" required>
      </div>

      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
          <label for="editorial_content" style="font-size: 1rem; margin-bottom: 0;">Editorial Article Body</label>
          <span style="font-size: 0.8rem; color: #64748b;">Supports plain paragraphs (separated by blank lines) or <code>&lt;p&gt;</code> HTML tags.</span>
        </div>
        <textarea id="editorial_content" name="editorial_content" class="form-control" style="min-height: 380px; font-size: 1rem; line-height: 1.6;" required><?= e($editorialContent) ?></textarea>
      </div>

      <!-- Live Preview Container -->
      <div style="margin-top: 1.5rem; background: #fff6e5; border: 2px solid #000; padding: 2rem; border-radius: 8px;">
        <h4 style="text-align: center; color: #2e2e2e; text-transform: uppercase; font-size: 1.2rem; margin-bottom: 1rem;">
          Live Homepage Preview
        </h4>
        <div id="editorial_preview" style="max-width: 850px; margin: auto; text-align: justify; color: #444; line-height: 1.7; font-family: 'Georgia', serif;">
          <?= $editorialContent ?>
        </div>
      </div>

      <div class="form-grid" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
        <div class="form-group">
          <label for="site_title">Public Magazine Title</label>
          <input type="text" id="site_title" name="site_title" class="form-control" value="<?= e($siteTitle) ?>">
        </div>

        <div class="form-group">
          <label for="footer_text">Footer Copyright Text</label>
          <input type="text" id="footer_text" name="footer_text" class="form-control" value="<?= e($footerText) ?>">
        </div>
      </div>

      <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
          💾 Save Editorial Changes
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Live update preview when typing in textarea
document.getElementById('editorial_content').addEventListener('input', function() {
    const preview = document.getElementById('editorial_preview');
    let text = this.value;
    if (!text.includes('<p>')) {
        let paras = text.split(/\n\n+/).filter(p => p.trim().length > 0);
        preview.innerHTML = paras.map(p => `<p style="margin-bottom: 0.8rem;">${escapeHtml(p.trim())}</p>`).join('');
    } else {
        preview.innerHTML = text;
    }
});

function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>