/**
 * Works page specific JavaScript for filtering functionality
 */

(function() {
    'use strict';

    // DOM Elements
    const filterTabs = document.querySelectorAll('.filter-tab');
    const workCards = document.querySelectorAll('.work-card');

    /**
     * Initialize works page functionality
     */
    function init() {
        setupFilterTabs();
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

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
