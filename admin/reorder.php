<?php
/**
 * Interactive Drag-and-Drop Article Reordering & Archiving
 * Inspired by Photography Club Event Order & Archival UI
 */

$adminPageTitle = 'Reorder & Archive Articles';
$adminActiveTab = 'reorder';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = get_db_connection();

// Fetch published articles (active)
$activeStmt = $pdo->query("
    SELECT id, slug, title, summary, author_name, author_batch, image_path, sort_order, edition_year 
    FROM articles 
    WHERE status = 'published' 
    ORDER BY sort_order ASC, id ASC
");
$activeArticles = $activeStmt->fetchAll();

// Fetch draft / archived articles
$archivedStmt = $pdo->query("
    SELECT id, slug, title, summary, author_name, author_batch, image_path, sort_order, edition_year 
    FROM articles 
    WHERE status = 'draft' 
    ORDER BY id DESC
");
$archivedArticles = $archivedStmt->fetchAll();
?>

<div class="card">
  <div class="card-header">
    <div>
      <h2>Drag & Drop Article Ordering & Archiving</h2>
      <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;">
        Reorder article cards in the same 3-column magazine layout as the public homepage. Drag to rearrange or click Archive/Unarchive.
      </p>
    </div>
    <div style="display: flex; align-items: center; gap: 1rem;">
      <span id="saveStatusMsg" style="font-size: 0.9rem; font-weight: 600; color: #15803d;"></span>
      <button type="button" onclick="saveOrderAndArchive()" class="btn btn-primary" id="saveOrderBtn">
        💾 Save Order & Changes
      </button>
    </div>
  </div>

  <div class="card-body">
    <!-- Active / Published Section -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h3 style="font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
        🟢 Active Magazine Articles (<span id="activeCount"><?= count($activeArticles) ?></span>)
      </h3>
      <span style="font-size: 0.85rem; color: #64748b;">(These appear live on the website in this exact 3-column order)</span>
    </div>

    <div id="activeArticlesGrid" class="reorder-grid">
      <!-- Injected via JavaScript -->
    </div>

    <!-- Archived / Drafts Section -->
    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 2px dashed var(--border);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="font-size: 1.15rem; color: #64748b; display: flex; align-items: center; gap: 0.5rem;">
          📦 Archived / Draft Articles (<span id="archivedCount"><?= count($archivedArticles) ?></span>)
        </h3>
        <span style="font-size: 0.85rem; color: #64748b;">(Hidden from the public homepage)</span>
      </div>

      <div id="archivedArticlesGrid" class="reorder-grid">
        <!-- Injected via JavaScript -->
      </div>
    </div>
  </div>

  <div class="card-footer" style="padding: 1.25rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
    <a href="articles.php" class="btn btn-secondary btn-sm">&larr; Back to Table View</a>
    <button type="button" onclick="saveOrderAndArchive()" class="btn btn-primary">
      💾 Save Order & Changes
    </button>
  </div>
</div>

<script>
// Initial Data from PHP
let activeArticles = <?= json_encode(array_map(function($a) {
    return [
        'id' => (int)$a['id'],
        'title' => $a['title'],
        'summary' => $a['summary'],
        'author_name' => $a['author_name'],
        'author_batch' => $a['author_batch'],
        'image_path' => $a['image_path'],
        'edition_year' => $a['edition_year'],
        'is_archived' => 0
    ];
}, $activeArticles)) ?>;

let archivedArticles = <?= json_encode(array_map(function($a) {
    return [
        'id' => (int)$a['id'],
        'title' => $a['title'],
        'summary' => $a['summary'],
        'author_name' => $a['author_name'],
        'author_batch' => $a['author_batch'],
        'image_path' => $a['image_path'],
        'edition_year' => $a['edition_year'],
        'is_archived' => 1
    ];
}, $archivedArticles)) ?>;

let draggedIndex = null;

function renderArticles() {
    const activeGrid = document.getElementById('activeArticlesGrid');
    const archivedGrid = document.getElementById('archivedArticlesGrid');
    document.getElementById('activeCount').textContent = activeArticles.length;
    document.getElementById('archivedCount').textContent = archivedArticles.length;

    // Render Active Grid
    if (activeArticles.length === 0) {
        activeGrid.innerHTML = '<p style="color: #64748b; grid-column: 1/-1; text-align: center; padding: 2rem;">No active articles. Unarchive some below or add new ones.</p>';
    } else {
        activeGrid.innerHTML = activeArticles.map((art, idx) => `
            <div class="draggable-article-card" 
                 draggable="true"
                 data-id="${art.id}"
                 data-index="${idx}"
                 ondragstart="handleDragStart(event, ${idx})"
                 ondragover="handleDragOver(event, ${idx})"
                 ondragleave="handleDragLeave(event)"
                 ondrop="handleDrop(event, ${idx})"
                 ondragend="handleDragEnd(event)">
                <span class="article-order-badge">#${idx + 1}</span>
                <button type="button" class="article-archive-btn" onclick="archiveArticle(${art.id})" title="Archive Article">Archive</button>
                <div class="card-thumb-wrap">
                    <img src="../${escapeHtml(art.image_path)}" alt="${escapeHtml(art.title)}">
                </div>
                <div class="card-info">
                    <div class="card-title">${escapeHtml(art.title)}</div>
                    <div class="card-author">
                        ${escapeHtml(art.author_name)} 
                        ${art.author_batch ? `<span class="badge badge-batch">${escapeHtml(art.author_batch)}</span>` : ''}
                    </div>
                    ${art.summary ? `<div class="card-summary">${escapeHtml(art.summary)}</div>` : ''}
                </div>
            </div>
        `).join('');
    }

    // Render Archived Grid
    if (archivedArticles.length === 0) {
        archivedGrid.innerHTML = '<p style="color: #64748b; grid-column: 1/-1; text-align: center; padding: 2rem;">No archived articles.</p>';
    } else {
        archivedGrid.innerHTML = archivedArticles.map((art, idx) => `
            <div class="draggable-article-card" 
                 style="opacity: 0.7; border-color: #cbd5e1;"
                 data-id="${art.id}">
                <span class="article-order-badge" style="background: rgba(220,38,38,0.85);">Archived</span>
                <button type="button" class="article-unarchive-btn" onclick="unarchiveArticle(${art.id})" title="Restore to Active">Unarchive</button>
                <div class="card-thumb-wrap">
                    <img src="../${escapeHtml(art.image_path)}" alt="${escapeHtml(art.title)}">
                </div>
                <div class="card-info">
                    <div class="card-title">${escapeHtml(art.title)}</div>
                    <div class="card-author">
                        ${escapeHtml(art.author_name)}
                        ${art.author_batch ? `<span class="badge badge-batch">${escapeHtml(art.author_batch)}</span>` : ''}
                    </div>
                    ${art.summary ? `<div class="card-summary">${escapeHtml(art.summary)}</div>` : ''}
                </div>
            </div>
        `).join('');
    }
}

function archiveArticle(articleId) {
    const idx = activeArticles.findIndex(a => a.id === articleId);
    if (idx !== -1) {
        const item = activeArticles.splice(idx, 1)[0];
        item.is_archived = 1;
        archivedArticles.unshift(item);
        renderArticles();
        showStatus('Order changed. Click Save to persist.', '#d97706');
    }
}

function unarchiveArticle(articleId) {
    const idx = archivedArticles.findIndex(a => a.id === articleId);
    if (idx !== -1) {
        const item = archivedArticles.splice(idx, 1)[0];
        item.is_archived = 0;
        activeArticles.push(item);
        renderArticles();
        showStatus('Order changed. Click Save to persist.', '#d97706');
    }
}

// --- Drag and Drop Handlers with Smooth Auto-Scroll ---
let autoScrollAnimationFrame = null;
let targetScrollSpeed = 0;
let activeScrollSpeed = 0;

function handleDragStart(e, idx) {
    draggedIndex = idx;
    e.currentTarget.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragOver(e, idx) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (draggedIndex === null || draggedIndex === idx) return;
    const target = e.currentTarget;
    target.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e, idx) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    if (draggedIndex === null || draggedIndex === idx) return;

    // Reorder array
    const movedItem = activeArticles.splice(draggedIndex, 1)[0];
    activeArticles.splice(idx, 0, movedItem);

    draggedIndex = null;
    renderArticles();
    showStatus('Order changed. Click Save to persist.', '#d97706');
}

function handleDragEnd(e) {
    draggedIndex = null;
    document.querySelectorAll('.draggable-article-card').forEach(card => {
        card.classList.remove('dragging', 'drag-over');
    });
}

function showStatus(msg, color) {
    const el = document.getElementById('saveStatusMsg');
    el.textContent = msg;
    el.style.color = color || '#15803d';
}

// --- AJAX Save Order and Archive status ---
function saveOrderAndArchive() {
    showStatus('Saving changes...', '#2563eb');
    const saveBtn = document.getElementById('saveOrderBtn');
    saveBtn.disabled = true;

    const payloadArticles = [];

    // Active articles with 1-based sequential sort_order
    activeArticles.forEach((art, idx) => {
        payloadArticles.push({
            id: art.id,
            sort_order: idx + 1,
            is_archived: 0
        });
    });

    // Archived articles
    archivedArticles.forEach((art) => {
        payloadArticles.push({
            id: art.id,
            sort_order: 9999,
            is_archived: 1
        });
    });

    fetch('api_reorder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ articles: payloadArticles })
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        if (data.status === 'success') {
            showStatus('✔ Changes saved successfully!', '#15803d');
            setTimeout(() => { showStatus('', '#15803d'); }, 3000);
        } else {
            showStatus('❌ Error: ' + data.message, '#dc2626');
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        showStatus('❌ Network error saving order.', '#dc2626');
    });
}

function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', renderArticles);
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
