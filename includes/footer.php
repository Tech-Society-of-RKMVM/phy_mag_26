<?php
/**
 * Shared Footer Component
 */
$footerText = get_setting('footer_text', '© 2026 Wall magazine — RKMV physics department');
?>
<footer>
  <?= e($footerText) ?>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const scroller = document.getElementById('site-bg-scroller');
  if (!scroller) return;
  const slides = scroller.querySelectorAll('.bg-slide');
  if (slides.length <= 1) return;

  let currentIdx = 0;
  const speed = parseInt(scroller.dataset.speed, 10) || 7;

  setInterval(function() {
    slides[currentIdx].classList.remove('active');
    currentIdx = (currentIdx + 1) % slides.length;
    slides[currentIdx].classList.add('active');
  }, speed * 1000);
});
</script>

</body>

</html>