/**
 * Works page specific JavaScript for filtering functionality and modal
 */

(function() {
    'use strict';

    // DOM Elements
    const filterTabs = document.querySelectorAll('.filter-tab');
    const workCards = document.querySelectorAll('.work-card');
    const modal = document.getElementById('work-modal');
    const modalOverlay = modal?.querySelector('.modal__overlay');
    const modalClose = modal?.querySelector('.modal__close');
    const modalImage = document.getElementById('modal-image');
    const modalTitle = document.getElementById('modal-title');
    const modalDescription = document.getElementById('modal-description');

    // Work data for modal content
    const workData = {
        'work-1': {
            title: '檸檬 1st Album 『再訪の街 透明な約束』 特典巾着用イラスト（2025）',
            image: '../images/works/work-01-800.jpg',
            description: 'モノクロームの繊細なタッチで描かれた、檸檬さまの1stアルバム特典巾着用イラストです。楽曲の世界観を表現した温かみのある作品となっています。アルバムのコンセプトに合わせ、どこか懐かしさを感じる街並みと透明感のある表現を心がけました。'
        },
        'work-2': {
            title: '左藤吹きガラス工房 メッセージカード用イラスト（2023）',
            image: '../images/works/work-02-800.jpg',
            description: '吹きガラス工房のメッセージカード用に制作したモノクロイラストです。手作りの温もりを感じられる優しいタッチで表現しました。ガラス工芸の繊細さと職人さんの丁寧な手仕事への敬意を込めて制作いたしました。'
        },
        'work-3': {
            title: 'Hanako Web連載『Noyauのムーンレター〜月とアロマのご自愛レスキュー〜』アイキャッチ用イラスト（2023）',
            image: '../images/works/work-03-800.jpg',
            description: 'Hanako Web連載企画のアイキャッチ用カラーイラストです。月とアロマをテーマにした癒しの世界観を色彩豊かに表現しています。読者の方が記事を読む前から心が穏やかになるような、優しく包み込むような色合いを意識して制作しました。'
        }
    };

    /**
     * Initialize works page functionality
     */
    function init() {
        setupFilterTabs();
        setupModal();
    }

    /**
     * Setup filter tab functionality
     */
    function setupFilterTabs() {
        filterTabs.forEach(tab => {
            tab.addEventListener('click', handleFilterClick);
        });
    }

    /**
     * Handle filter tab clicks
     */
    function handleFilterClick(e) {
        const clickedTab = e.target;
        const filter = clickedTab.getAttribute('data-filter');

        // Update active tab
        filterTabs.forEach(tab => tab.classList.remove('active'));
        clickedTab.classList.add('active');

        // Filter work cards
        filterWorkCards(filter);
    }

    /**
     * Filter work cards based on selected filter
     */
    function filterWorkCards(filter) {
        workCards.forEach(card => {
            const cardGenre = card.getAttribute('data-genre');
            
            if (filter === 'all' || cardGenre === filter) {
                card.style.display = 'block';
                card.classList.add('is-visible');
            } else {
                card.style.display = 'none';
                card.classList.remove('is-visible');
            }
        });

        // Update pagination info if needed
        updatePaginationInfo(filter);
    }

    /**
     * Update pagination information based on filter
     */
    function updatePaginationInfo(filter) {
        const paginationInfo = document.querySelector('.pagination__info');
        if (!paginationInfo) return;

        const visibleCards = document.querySelectorAll('.work-card.is-visible').length;
        const totalCards = workCards.length;

        if (filter === 'all') {
            paginationInfo.innerHTML = `
                <span class="pagination__current">1</span> / 1 ページ（全 ${totalCards} 件）
            `;
        } else {
            const filterName = getFilterName(filter);
            paginationInfo.innerHTML = `
                <span class="pagination__current">1</span> / 1 ページ（${filterName}: ${visibleCards} 件）
            `;
        }
    }

    /**
     * Get filter display name
     */
    function getFilterName(filter) {
        const filterNames = {
            'icon': 'アイコン',
            'cd': 'CD付録',
            'signage': '看板',
            'other': 'その他'
        };
        return filterNames[filter] || filter;
    }

    /**
     * Setup modal functionality
     */
    function setupModal() {
        if (!modal) return;

        // Add click listeners to work cards
        workCards.forEach(card => {
            card.addEventListener('click', handleWorkCardClick);
            card.style.cursor = 'pointer';
        });

        // Add click listener to modal close button
        modalClose?.addEventListener('click', closeModal);

        // Add click listener to modal overlay (click outside to close)
        modalOverlay?.addEventListener('click', handleOverlayClick);

        // Add escape key listener
        document.addEventListener('keydown', handleKeyDown);
    }

    /**
     * Handle work card click to open modal
     */
    function handleWorkCardClick(e) {
        e.preventDefault();
        const workId = this.id;
        const workInfo = workData[workId];
        
        if (workInfo) {
            openModal(workInfo);
        }
    }

    /**
     * Open modal with work information
     */
    function openModal(workInfo) {
        if (!modal) return;

        // Update modal content
        modalTitle.textContent = workInfo.title;
        modalImage.src = workInfo.image;
        modalImage.alt = workInfo.title;
        // modalDescription.textContent = workInfo.description; // 説明文を削除

        // Show modal
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    /**
     * Close modal
     */
    function closeModal() {
        if (!modal) return;

        modal.classList.remove('is-open');
        document.body.style.overflow = ''; // Restore scrolling
    }

    /**
     * Handle overlay click to close modal
     */
    function handleOverlayClick(e) {
        if (e.target === modalOverlay) {
            closeModal();
        }
    }

    /**
     * Handle keyboard events
     */
    function handleKeyDown(e) {
        if (e.key === 'Escape' && modal?.classList.contains('is-open')) {
            closeModal();
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
