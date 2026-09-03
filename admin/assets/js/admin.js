/**
 * Admin Panel Scripts
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if (titleInput && slugInput && slugInput.dataset.autogen !== 'false') {
        titleInput.addEventListener('input', function() {
            const slug = titleInput.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
        });

        slugInput.addEventListener('input', function() {
            slugInput.dataset.autogen = 'false';
        });
    }

    // Image Upload Preview
    const imageFileInput = document.getElementById('image_file');
    const imagePreview = document.getElementById('image_preview');

    if (imageFileInput && imagePreview) {
        imageFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Batch selector helper (custom batch input toggle)
    const batchSelect = document.getElementById('author_batch_select');
    const batchCustom = document.getElementById('author_batch_custom');
    if (batchSelect && batchCustom) {
        batchSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                batchCustom.style.display = 'block';
                batchCustom.focus();
            } else {
                batchCustom.style.display = 'none';
                batchCustom.value = this.value;
            }
        });
    }
});
