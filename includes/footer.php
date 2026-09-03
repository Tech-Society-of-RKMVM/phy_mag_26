<?php
/**
 * Shared Footer Component
 */
$footerText = get_setting('footer_text', '© 2026 Wall magazine — RKMV physics department');
?>
<footer>
  <?= e($footerText) ?>
</footer>

</body>

</html>