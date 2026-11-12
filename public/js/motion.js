/**
 * RW SMART — Motion Graphics & Scroll Reveal Engine
 * Designed for Progressive Enhancement, Zero Layout Shift, and Full Accessibility.
 */
(() => {
    // 1. Accessibility & Browser Compatibility Check
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || !('IntersectionObserver' in window)) {
        return; // Exit cleanly, all content remains 100% visible and unhindered
    }

    const root = document.documentElement;
    root.classList.add('js-motion');

    // 2. Intersection Observer Configuration
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            io.unobserve(el);
            el.classList.add('m-in');

            // Trigger numerical count-up if element or children have counters
            if (el.matches('[data-count], .stat-value, .stat-number')) {
                countUp(el);
            }
            el.querySelectorAll('[data-count], .stat-value, .stat-number').forEach(countUp);

            // Clean up motion attributes on transitionend to protect native CSS hover/focus
            const cleanup = () => {
                el.removeAttribute('data-m');
                el.classList.remove('m-in');
                el.style.removeProperty('--d');
            };
            el.addEventListener('transitionend', cleanup, { once: true });
            // Safety fallback in case transitionend does not fire
            setTimeout(cleanup, 1200);
        });
    }, {
        threshold: 0.08,
        rootMargin: '0px 0px -6% 0px'
    });

    // 3. Selective Auto-Tagging Matrix
    const AUTO = [
        // Headers & Titles (Surface Card Headers)
        ['.page-head, .page-header-saas, .section-header-center, .page-header, .brand-heading, h1.page-title, h1.hero-title', 'rise'],
        ['.page-header-actions, .hero-cta-group', 'right'],
        // Banners, Badges & Alerts
        ['.banner-soft, .alert, .alert-saas, .hero-badge, .hero-trust-bar', 'left'],
        // Summary & KPI Metric Cards
        ['.stat-tile, .stat-card-saas, .stat-card, .metric-card, .stats-grid-card .stat-item', 'zoom'],
        // Segmented Tabs & Navigations
        ['.nav-tabs-saas, .segmented-tabs', 'rise'],
        // Feature & Service Modules & Product Cards
        ['.product-card, .catalog-card, .quick-service-card, .service-tile, .module-card, .feature-item', 'zoom'],
        // Ronda / Schedule cards
        ['.schedule-card, .ronda-card', 'right'],
        // Content Cards (only if not directly wrapping a data table to avoid double nested animation)
        ['.form-card, .table-card, .saas-card, .card, .feature-card, .service-card, .role-card, .step-card, .security-box, .cta-box, .box', 'rise'],
        // Card Headers (for cards with tables)
        ['.card-header-saas, .card-header', 'rise'],
        // Table Rows (up to first 12 rows for optimal performance)
        ['.table-saas tbody tr:nth-child(-n+12), .table tbody tr:nth-child(-n+12)', 'rise'],
        // Progress Bars & Fill Meters
        ['.progress-bar, .bar-fill', 'bar'],
        // Chips, Demo accounts, Badges
        ['.demo-pill, .badge-chip, .badge-category-glass', 'pop'],
        // Empty State Displays
        ['.empty-card, .empty-state, td.text-center.py-5', 'rise'],
        // Form field groups (when standalone)
        ['form .mb-3, form .row.g-3, .form-group', 'rise']
    ];

    /**
     * Tags elements with motion attributes based on selector rules
     */
    function tag(scope = document) {
        if (!scope || !scope.querySelectorAll) return;

        AUTO.forEach(([selector, motionType]) => {
            scope.querySelectorAll(selector).forEach((el) => {
                // Ignore already tagged, explicitly excluded, or modal elements
                if (el.dataset.m || el.closest('[data-no-motion], .modal, [role=dialog], canvas, svg, aside, .navbar-saas, .landing-nav, .topbar')) {
                    return;
                }

                // Avoid double animation: If element is a card containing a table or other animated children, skip card container
                if (el.matches('.saas-card, .card') && el.querySelector('table, .stat-card-saas, .stat-card')) {
                    return;
                }

                // Avoid animating form groups if parent card is already being animated
                if (el.matches('form .mb-3, form .row.g-3, .form-group') && el.closest('.saas-card, .card, .auth-form, .brand-panel')) {
                    return;
                }

                el.dataset.m = motionType;
            });
        });

        // Compute stagger delays among immediate siblings and register with observer
        scope.querySelectorAll('[data-m]:not([data-seen])').forEach((el) => {
            el.dataset.seen = '1';
            const parent = el.parentElement;
            let index = 0;
            if (parent) {
                const siblings = [...parent.children].filter((child) => child.hasAttribute('data-m'));
                index = siblings.indexOf(el);
            }
            const delayMs = Math.min(Math.max(index, 0), 10) * 70;
            el.style.setProperty('--d', `${delayMs}ms`);
            io.observe(el);
        });

        // Also observe standalone count elements
        scope.querySelectorAll('[data-count]:not([data-seen])').forEach((el) => {
            el.dataset.seen = '1';
            io.observe(el);
        });
    }

    /**
     * Numerical Count-Up Animation
     * Strict filtering: only counts summary statistics.
     * Never alters NIK, No. KK, phone numbers, dates, or non-numeric codes.
     */
    function countUp(el) {
        if (!el || el.dataset.done) return;

        const originalText = el.textContent.trim();
        // Match prefix, numeric characters with optional dots, and suffix
        const match = originalText.match(/^(\D*?)(\d[\d.]*)(\D*)$/);
        if (!match) return;

        const prefix = match[1];
        const rawNumber = match[2];
        const suffix = match[3];

        // Skip dates, timestamps, or hyphenated codes (e.g. 12/11/2026, 2026-11-12, 14:30)
        if (originalText.includes('/') || originalText.includes('-') || originalText.includes(':')) return;

        // Skip phone numbers (e.g. 08123456789) or RT codes starting with 0
        if (/^0\d/.test(rawNumber)) return;

        const digitsOnly = rawNumber.replace(/\D/g, '');
        const isCurrency = prefix.includes('Rp') || suffix.includes('Rp');

        // Skip NIK / No. KK (16 digits) and non-currency long IDs
        if (!isCurrency && digitsOnly.length > 9) return;
        if (digitsOnly.length === 16) return;

        const endValue = parseInt(digitsOnly, 10);
        if (isNaN(endValue)) return;

        el.dataset.done = '1';
        const duration = 900;
        const startTime = performance.now();

        const step = (currentTime) => {
            const progress = Math.min((currentTime - startTime) / duration, 1);
            // Ease-out cubic formula
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.round(endValue * easeOut);

            el.textContent = prefix + currentValue.toLocaleString('id-ID') + suffix;

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                // Always restore the exact original string from server
                el.textContent = originalText;
            }
        };

        requestAnimationFrame(step);
    }

    // 4. Auth Tabs Sliding Glider (Progressive Visual Enhancement)
    function initAuthTabGlider() {
        const tabContainer = document.querySelector('.auth-nav-tabs');
        if (!tabContainer) return;

        let glider = tabContainer.querySelector('.tab-pill-glider');
        if (!glider) {
            glider = document.createElement('div');
            glider.className = 'tab-pill-glider';
            glider.setAttribute('aria-hidden', 'true');
            tabContainer.prepend(glider);
        }

        const updateGlider = () => {
            const activeBtn = tabContainer.querySelector('.tab-btn.active');
            if (activeBtn && glider) {
                const containerRect = tabContainer.getBoundingClientRect();
                const activeRect = activeBtn.getBoundingClientRect();
                const offsetLeft = activeRect.left - containerRect.left;
                glider.style.width = `${activeRect.width}px`;
                glider.style.transform = `translateX(${offsetLeft}px)`;
            }
        };

        tabContainer.querySelectorAll('.tab-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                setTimeout(updateGlider, 20);
            });
        });

        // Initial glider position
        setTimeout(updateGlider, 50);
        window.addEventListener('resize', updateGlider, { passive: true });
    }

    // 5. Sidebar First-Session Entrance Stagger
    function initSidebarEntrance() {
        try {
            if (!sessionStorage.getItem('rw_sidebar_entered')) {
                sessionStorage.setItem('rw_sidebar_entered', '1');
                const sidebarItems = document.querySelectorAll('.sidebar-nav-item');
                sidebarItems.forEach((item, idx) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-10px)';
                    item.style.transition = 'opacity 0.4s cubic-bezier(0.2, 0.7, 0.2, 1), transform 0.4s cubic-bezier(0.2, 0.7, 0.2, 1)';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'none';
                    }, Math.min(idx, 16) * 35);
                });
            }
        } catch (e) {
            // Safe fallback if sessionStorage is restricted
        }
    }

    // 6. Navbar Shadow on Scroll & Fallback Progress Bar
    function initScrollEffects() {
        const topbar = document.querySelector('.navbar-saas, .landing-nav, .topbar, header.navbar');
        const progressBar = document.querySelector('.m-progress');

        if (topbar) {
            topbar.classList.add('m-shadow-on-scroll');
        }

        const onScroll = () => {
            const scrollY = window.scrollY || document.documentElement.scrollTop;
            if (topbar) {
                topbar.classList.toggle('m-scrolled', scrollY > 8);
            }

            // JavaScript fallback for scroll progress when CSS scroll(root) is not supported
            if (progressBar && (!CSS.supports || !CSS.supports('animation-timeline', 'scroll(root)'))) {
                const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
                const ratio = totalHeight > 0 ? Math.min(Math.max(scrollY / totalHeight, 0), 1) : 0;
                progressBar.style.transform = `scaleX(${ratio})`;
            }
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // 7. Initialize Engine on DOM Ready & Watch for Mutations
    document.addEventListener('DOMContentLoaded', () => {
        tag();
        initAuthTabGlider();
        initSidebarEntrance();
        initScrollEffects();

        // Observe dynamically inserted elements (DataTables, AJAX modals, etc.)
        if ('MutationObserver' in window && document.body) {
            const mo = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            tag(node);
                        }
                    });
                });
            });
            mo.observe(document.body, { childList: true, subtree: true });
        }
    });
})();
