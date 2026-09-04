/**
 * Interactive Comic Book Reader Engine
 * Department of Physics Wall Magazine
 */

document.addEventListener('DOMContentLoaded', function() {
    const readerRoot = document.getElementById('comic-reader-app');
    if (!readerRoot) return;

    // Retrieve panels data injected via data-panels JSON attribute
    const panelsDataRaw = readerRoot.getAttribute('data-panels');
    let panels = [];
    try {
        panels = JSON.parse(panelsDataRaw || '[]');
    } catch (e) {
        console.error('Failed to parse comic panels JSON:', e);
        panels = [];
    }

    if (panels.length === 0) {
        return;
    }

    // State
    let currentPage = 0; // 0-indexed (page index: 0 is cover/page 1)
    let isDualPage = window.innerWidth >= 992;
    let readingMode = 'book'; // 'book' or 'scroll'
    let isFullscreen = false;
    let zoomLevel = 1.0;

    // DOM Elements
    const bookContainer = document.getElementById('book-viewport');
    const bookStage = document.getElementById('book-stage');
    const scrollContainer = document.getElementById('comic-scroll-view');
    const prevBtn = document.getElementById('comic-prev-btn');
    const nextBtn = document.getElementById('comic-next-btn');
    const pageNumIndicator = document.getElementById('comic-page-num');
    const pageScrubber = document.getElementById('comic-scrubber');
    const modeBtnBook = document.getElementById('btn-mode-book');
    const modeBtnScroll = document.getElementById('btn-mode-scroll');
    const fullscreenBtn = document.getElementById('comic-fullscreen-btn');
    const thumbStrip = document.getElementById('comic-thumbnails');

    const totalPages = panels.length;
    pageScrubber.max = totalPages;

    function checkDualPage() {
        const wide = window.innerWidth >= 992;
        if (wide !== isDualPage) {
            isDualPage = wide;
            renderBook();
        }
    }

    window.addEventListener('resize', checkDualPage);

    // Build thumbnails
    if (thumbStrip) {
        thumbStrip.innerHTML = '';
        panels.forEach((p, idx) => {
            const thumb = document.createElement('div');
            thumb.className = `comic-thumb ${idx === 0 ? 'active' : ''}`;
            thumb.dataset.pageIndex = idx;
            thumb.innerHTML = `
                <img src="${p.image_path}" alt="Page ${idx + 1}" loading="lazy">
                <span>P. ${idx + 1}</span>
            `;
            thumb.addEventListener('click', () => {
                goToPage(idx);
            });
            thumbStrip.appendChild(thumb);
        });
    }

    // Build Continuous Scroll View
    if (scrollContainer) {
        scrollContainer.innerHTML = '';
        panels.forEach((p, idx) => {
            const item = document.createElement('div');
            item.className = 'scroll-panel-item';
            item.innerHTML = `
                <div class="scroll-panel-header">
                    <span class="scroll-panel-badge">Page ${idx + 1} of ${totalPages}</span>
                    ${p.title ? `<span class="scroll-panel-title">${escapeHtml(p.title)}</span>` : ''}
                </div>
                <div class="scroll-panel-img-wrap">
                    <img src="${p.image_path}" alt="Comic Panel ${idx + 1}" loading="lazy">
                </div>
            `;
            scrollContainer.appendChild(item);
        });
    }

    function renderBook() {
        if (!bookStage) return;

        bookStage.innerHTML = '';

        if (!isDualPage || totalPages === 1) {
            // Single page layout
            const curPanel = panels[currentPage];
            const pageEl = document.createElement('div');
            pageEl.className = 'book-single-page';
            pageEl.innerHTML = `
                <div class="page-inner">
                    <img src="${curPanel.image_path}" alt="Page ${currentPage + 1}">
                    <div class="page-number-footer">${currentPage + 1} / ${totalPages}</div>
                </div>
            `;
            bookStage.appendChild(pageEl);
        } else {
            // Dual page spread layout
            // If currentPage is even (0, 2, 4...), left is currentPage, right is currentPage + 1
            // Or page 0 as standalone Cover, then spreads
            let leftIdx = currentPage;
            let rightIdx = currentPage + 1;

            const spreadEl = document.createElement('div');
            spreadEl.className = 'book-spread';

            // Left Page
            const leftPage = document.createElement('div');
            leftPage.className = 'book-page book-page-left';
            if (leftIdx < totalPages) {
                leftPage.innerHTML = `
                    <div class="page-inner">
                        <img src="${panels[leftIdx].image_path}" alt="Page ${leftIdx + 1}">
                        <div class="page-number-footer">${leftIdx + 1}</div>
                    </div>
                `;
            } else {
                leftPage.innerHTML = `<div class="page-inner blank-page"></div>`;
            }

            // Book Spine Divider
            const spine = document.createElement('div');
            spine.className = 'book-spine';

            // Right Page
            const rightPage = document.createElement('div');
            rightPage.className = 'book-page book-page-right';
            if (rightIdx < totalPages) {
                rightPage.innerHTML = `
                    <div class="page-inner">
                        <img src="${panels[rightIdx].image_path}" alt="Page ${rightIdx + 1}">
                        <div class="page-number-footer">${rightIdx + 1}</div>
                    </div>
                `;
            } else {
                rightPage.innerHTML = `<div class="page-inner blank-page"></div>`;
            }

            spreadEl.appendChild(leftPage);
            spreadEl.appendChild(spine);
            spreadEl.appendChild(rightPage);
            bookStage.appendChild(spreadEl);
        }

        updateControls();
    }

    function updateControls() {
        const step = isDualPage ? 2 : 1;
        
        // Page indicator text
        if (pageNumIndicator) {
            if (isDualPage && currentPage + 1 < totalPages) {
                pageNumIndicator.textContent = `Pages ${currentPage + 1}-${currentPage + 2} of ${totalPages}`;
            } else {
                pageNumIndicator.textContent = `Page ${currentPage + 1} of ${totalPages}`;
            }
        }

        if (pageScrubber) {
            pageScrubber.value = currentPage + 1;
        }

        // Button disabled states
        if (prevBtn) {
            prevBtn.disabled = currentPage <= 0;
            prevBtn.style.opacity = currentPage <= 0 ? '0.35' : '1';
        }
        if (nextBtn) {
            const hasNext = isDualPage ? (currentPage + 2 < totalPages) : (currentPage + 1 < totalPages);
            nextBtn.disabled = !hasNext;
            nextBtn.style.opacity = !hasNext ? '0.35' : '1';
        }

        // Update active thumbnail
        if (thumbStrip) {
            const allThumbs = thumbStrip.querySelectorAll('.comic-thumb');
            allThumbs.forEach(th => {
                const idx = parseInt(th.dataset.pageIndex, 10);
                if (idx === currentPage || (isDualPage && idx === currentPage + 1)) {
                    th.classList.add('active');
                    th.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    th.classList.remove('active');
                }
            });
        }
    }

    function goToPage(index) {
        if (index < 0) index = 0;
        if (index >= totalPages) index = totalPages - 1;

        if (isDualPage && index % 2 !== 0 && index > 0) {
            index = index - 1; // Align to left spread page
        }

        currentPage = index;
        renderBook();
    }

    function nextPage() {
        const step = isDualPage ? 2 : 1;
        if (currentPage + step < totalPages) {
            goToPage(currentPage + step);
        }
    }

    function prevPage() {
        const step = isDualPage ? 2 : 1;
        if (currentPage - step >= 0) {
            goToPage(currentPage - step);
        } else if (currentPage > 0) {
            goToPage(0);
        }
    }

    // Event Listeners
    if (prevBtn) prevBtn.addEventListener('click', prevPage);
    if (nextBtn) nextBtn.addEventListener('click', nextPage);

    if (pageScrubber) {
        pageScrubber.addEventListener('input', function() {
            const targetIdx = parseInt(this.value, 10) - 1;
            goToPage(targetIdx);
        });
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (['ArrowLeft', 'KeyA'].includes(e.code)) {
            prevPage();
        } else if (['ArrowRight', 'KeyD', 'Space'].includes(e.code) && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            nextPage();
        } else if (e.code === 'Home') {
            goToPage(0);
        } else if (e.code === 'End') {
            goToPage(totalPages - 1);
        }
    });

    // Touch Swipe gestures for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    if (bookContainer) {
        bookContainer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        bookContainer.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
    }

    function handleSwipe() {
        const swipeDiff = touchEndX - touchStartX;
        if (Math.abs(swipeDiff) > 45) {
            if (swipeDiff < 0) {
                // Swiped Left -> Next page
                nextPage();
            } else {
                // Swiped Right -> Prev page
                prevPage();
            }
        }
    }

    // Mode Toggle (Book Flip vs Scroll)
    if (modeBtnBook && modeBtnScroll) {
        modeBtnBook.addEventListener('click', function() {
            readingMode = 'book';
            modeBtnBook.classList.add('active');
            modeBtnScroll.classList.remove('active');
            if (bookContainer) bookContainer.style.display = 'block';
            if (scrollContainer) scrollContainer.style.display = 'none';
            if (thumbStrip) thumbStrip.style.display = 'flex';
            renderBook();
        });

        modeBtnScroll.addEventListener('click', function() {
            readingMode = 'scroll';
            modeBtnScroll.classList.add('active');
            modeBtnBook.classList.remove('active');
            if (bookContainer) bookContainer.style.display = 'none';
            if (scrollContainer) scrollContainer.style.display = 'block';
            if (thumbStrip) thumbStrip.style.display = 'none';
        });
    }

    // Fullscreen Toggle
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            const elem = document.getElementById('comic-reader-viewport');
            if (!document.fullscreenElement) {
                if (elem.requestFullscreen) elem.requestFullscreen();
                else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
                fullscreenBtn.innerHTML = '🗗 Exit Fullscreen';
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                fullscreenBtn.innerHTML = '⛶ Fullscreen';
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    // Initial render
    renderBook();
});
