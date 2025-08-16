/**
 * Main JavaScript for さとうゆうillustration Portfolio Site
 */

(function() {
    'use strict';

    // DOM Elements
    const hamburger = document.querySelector('.hamburger');
    const drawer = document.querySelector('.drawer');
    const drawerOverlay = document.querySelector('.drawer__overlay');
    const drawerClose = document.querySelector('.drawer__close');
    const drawerLinks = document.querySelectorAll('.drawer__nav-link');
    const headerLinks = document.querySelectorAll('.header__nav-link');

    // State
    let isDrawerOpen = false;

    /**
     * Initialize the application
     */
    function init() {
        setupEventListeners();
        setupSmoothScrolling();
        setupAccessibility();
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Hamburger menu
        if (hamburger) {
            hamburger.addEventListener('click', toggleDrawer);
        }

        // Drawer overlay and close button
        if (drawerOverlay) {
            drawerOverlay.addEventListener('click', closeDrawer);
        }
        if (drawerClose) {
            drawerClose.addEventListener('click', closeDrawer);
        }

        // Drawer navigation links
        drawerLinks.forEach(link => {
            link.addEventListener('click', handleDrawerLinkClick);
        });

        // Header navigation links
        headerLinks.forEach(link => {
            link.addEventListener('click', handleHeaderLinkClick);
        });

        // Escape key to close drawer
        document.addEventListener('keydown', handleKeydown);

        // Window resize
        window.addEventListener('resize', handleResize);

        // Scroll events for header
        window.addEventListener('scroll', handleScroll);
    }

    /**
     * Setup smooth scrolling for anchor links
     */
    function setupSmoothScrolling() {
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        
        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Skip if it's just "#"
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    /**
     * Setup accessibility features
     */
    function setupAccessibility() {
        // Focus trap for drawer
        setupFocusTrap();
        
        // Skip link (if needed)
        setupSkipLink();
    }

    /**
     * Setup focus trap for drawer
     */
    function setupFocusTrap() {
        if (!drawer) return;

        const focusableElements = drawer.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return;

        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        drawer.addEventListener('keydown', function(e) {
            if (!isDrawerOpen) return;

            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    // Tab
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }
        });
    }

    /**
     * Setup skip link functionality
     */
    function setupSkipLink() {
        const skipLink = document.querySelector('.skip-link');
        if (skipLink) {
            skipLink.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.focus();
                    target.scrollIntoView();
                }
            });
        }
    }

    /**
     * Toggle drawer open/close
     */
    function toggleDrawer() {
        if (isDrawerOpen) {
            closeDrawer();
        } else {
            openDrawer();
        }
    }

    /**
     * Open drawer
     */
    function openDrawer() {
        if (!drawer || !hamburger) return;

        isDrawerOpen = true;
        drawer.classList.add('is-open');
        hamburger.setAttribute('aria-expanded', 'true');
        hamburger.setAttribute('aria-label', 'メニューを閉じる');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Focus on close button
        setTimeout(() => {
            if (drawerClose) {
                drawerClose.focus();
            }
        }, 300);
    }

    /**
     * Close drawer
     */
    function closeDrawer() {
        if (!drawer || !hamburger) return;

        isDrawerOpen = false;
        drawer.classList.remove('is-open');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.setAttribute('aria-label', 'メニューを開く');
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Return focus to hamburger
        hamburger.focus();
    }

    /**
     * Handle drawer link clicks
     */
    function handleDrawerLinkClick(e) {
        const href = e.target.getAttribute('href');
        
        // Close drawer for anchor links
        if (href && href.startsWith('#')) {
            setTimeout(() => {
                closeDrawer();
            }, 100);
        }
    }

    /**
     * Handle header link clicks
     */
    function handleHeaderLinkClick(e) {
        // Add active state or other functionality if needed
    }

    /**
     * Handle keydown events
     */
    function handleKeydown(e) {
        // Close drawer on Escape
        if (e.key === 'Escape' && isDrawerOpen) {
            closeDrawer();
        }
    }

    /**
     * Handle window resize
     */
    function handleResize() {
        // Close drawer on desktop breakpoint
        if (window.innerWidth >= 768 && isDrawerOpen) {
            closeDrawer();
        }
    }

    /**
     * Handle scroll events
     */
    function handleScroll() {
        const header = document.querySelector('.header');
        if (!header) return;

        if (window.scrollY > 100) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
    }

    /**
     * Utility function to debounce function calls
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Form validation utilities (for future use)
     */
    const FormValidator = {
        /**
         * Validate email format
         */
        isValidEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Validate required field
         */
        isRequired: function(value) {
            return value && value.trim().length > 0;
        },

        /**
         * Validate minimum length
         */
        minLength: function(value, min) {
            return value && value.trim().length >= min;
        },

        /**
         * Show error message
         */
        showError: function(field, message) {
            const errorElement = field.parentNode.querySelector('.form-error');
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }
            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
        },

        /**
         * Clear error message
         */
        clearError: function(field) {
            const errorElement = field.parentNode.querySelector('.form-error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
            field.classList.remove('is-invalid');
            field.setAttribute('aria-invalid', 'false');
        }
    };

    /**
     * Image lazy loading (for future use)
     */
    function setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }

    /**
     * Performance monitoring
     */
    function setupPerformanceMonitoring() {
        // Log Core Web Vitals if available
        if ('web-vital' in window) {
            // Implementation would go here
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Export utilities for global use
    window.SatouIllustration = {
        FormValidator: FormValidator,
        closeDrawer: closeDrawer,
        openDrawer: openDrawer
    };

})();
