/**
 * Main JavaScript
 * Handles navigation smooth scrolling, mobile menu, header scroll, hero zoom,
 * active menu state, about image hover, and project carousel.
 */
(function() {
    'use strict';

    const SCROLL_THRESHOLD = 50;
    const HERO_MAX_ZOOM = 0.15;
    const RESIZE_DEBOUNCE_MS = 250;
    const DESKTOP_BREAKPOINT = 768;
    const MIN_SWIPE_DISTANCE = 50;

    const ThemeInit = {
        dom: {},
        utils: {},
        navigation: { init: () => {} },
        mobileMenu: { init: () => {}, open: () => {}, close: () => {} },
        headerScroll: { init: () => {} },
        heroZoom: { init: () => {} },
        activeMenuState: { init: () => {} },
        aboutImageHover: { init: () => {} },
        projectCarousel: { init: () => {} },
    };

    /**
     * Populate DOM cache once after DOMContentLoaded.
     */
    function populateDomCache() {
        ThemeInit.dom = {
            siteHeader: document.querySelector('.site-header'),
            navMenu: document.querySelector('.nav-menu'),
            mobileToggle: document.querySelector('.mobile-menu-toggle'),
            mobileNav: document.getElementById('mobileNav'),
            mobileNavClose: document.getElementById('mobileNavClose'),
            mobilePortfolioItem: document.getElementById('mobilePortfolioItem'),
            hero: document.getElementById('hero'),
            heroBgImage: document.querySelector('.hero-bg-image'),
            aboutImage: document.querySelector('.about-image'),
        };
    }

    /**
     * Remove all active classes from every menu item in the given nav menu.
     * @param {Element} navMenu - The .nav-menu container element.
     */
    function removeAllNavMenuActiveClasses(navMenu) {
        if (!navMenu) {
            return;
        }
        const allMenuItems = navMenu.querySelectorAll('li');
        allMenuItems.forEach((item) => {
            item.classList.remove('current-menu-item', 'current_page_item', 'is-active', 'current-menu-ancestor');
        });
    }

    /**
     * Add active classes to a menu item and its parent if it has children.
     * @param {Element} menuItemLi - The <li> element to mark active.
     */
    function addActiveToMenuItem(menuItemLi) {
        if (!menuItemLi) {
            return;
        }
        menuItemLi.classList.add('current-menu-item', 'is-active');
        const parentMenuItem = menuItemLi.parentElement?.closest('li');
        if (parentMenuItem?.classList.contains('menu-item-has-children')) {
            parentMenuItem.classList.add('current-menu-ancestor', 'is-active');
        }
    }

    /**
     * Set the active menu item: clear all, then set the given item (and parent) active.
     * @param {Element} navMenu - The .nav-menu container element.
     * @param {Element|null} activeMenuItemLi - The <li> to mark active, or null to only clear.
     */
    function setNavMenuActive(navMenu, activeMenuItemLi) {
        removeAllNavMenuActiveClasses(navMenu);
        addActiveToMenuItem(activeMenuItemLi);
    }

    /**
     * Create a scroll listener throttled with requestAnimationFrame.
     * @param {function(): void} callback - Called on scroll (rAF-throttled).
     * @returns {function(): void} Handler to pass to addEventListener('scroll', ...).
     */
    function createRafScrollHandler(callback) {
        let ticking = false;
        return () => {
            if (ticking) {
                return;
            }
            ticking = true;
            window.requestAnimationFrame(() => {
                callback();
                ticking = false;
            });
        };
    }

    /**
     * Check if a link href is a home link (no hash, root URL).
     * @param {string|null} href - The link's href.
     * @returns {boolean}
     */
    function isHomeLink(href) {
        if (!href) {
            return false;
        }
        const origin = window.location.origin;
        return (
            href === '/' ||
            href === `${origin}/` ||
            (!href.includes('#') && href.endsWith('/'))
        );
    }

    /**
     * Check if the current page is the front page (home).
     * @returns {boolean}
     */
    function isHomePage() {
        const path = window.location.pathname;
        return path === '/' || path === '/index.php';
    }

    ThemeInit.utils = {
        setNavMenuActive,
        removeAllNavMenuActiveClasses,
        addActiveToMenuItem,
        createRafScrollHandler,
        isHomeLink,
        isHomePage,
    };

    /**
     * Initialize smooth scrolling for navigation links (hash + home).
     */
    ThemeInit.navigation.init = function initNavigation() {
        const { navMenu, siteHeader } = ThemeInit.dom;
        const closeMobileMenu = ThemeInit.mobileMenu.close;

        if (!navMenu) {
            return;
        }

        const navLinks = navMenu.querySelectorAll('a[href^="#"]');
        navLinks.forEach((link) => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (!href || href === '#' || !href.startsWith('#')) {
                    return;
                }
                e.preventDefault();

                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                if (!targetElement) {
                    return;
                }

                // Use scrollIntoView which respects CSS scroll-padding-top
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });

                history.pushState(null, null, href);

                const menuItem = link.closest('li');
                setNavMenuActive(navMenu, menuItem);

                closeMobileMenu();
            });
        });

        const homeLinks = navMenu.querySelectorAll('a');
        homeLinks.forEach((link) => {
            const href = link.getAttribute('href');
            if (!isHomeLink(href)) {
                return;
            }
            link.addEventListener('click', (e) => {
                if (!isHomePage()) {
                    return;
                }
                e.preventDefault();

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth',
                });

                history.pushState(null, null, window.location.pathname);

                const menuItem = link.closest('li');
                setNavMenuActive(navMenu, menuItem);

                closeMobileMenu();
            });
        });
    };

    /**
     * Open mobile menu.
     */
    ThemeInit.mobileMenu.open = function openMobileMenu() {
        const { mobileToggle, mobileNav } = ThemeInit.dom;
        if (!mobileToggle || !mobileNav) {
            return;
        }
        mobileToggle.classList.add('active');
        mobileToggle.setAttribute('aria-expanded', 'true');
        mobileNav.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    /**
     * Close mobile menu.
     */
    ThemeInit.mobileMenu.close = function closeMobileMenu() {
        const { mobileToggle, mobileNav } = ThemeInit.dom;
        if (!mobileToggle || !mobileNav) {
            return;
        }
        mobileToggle.classList.remove('active');
        mobileToggle.setAttribute('aria-expanded', 'false');
        mobileNav.classList.remove('active');
        document.body.style.overflow = '';
    };

    /**
     * Initialize mobile menu toggle, close button, portfolio dropdown, link clicks, escape key, and resize.
     */
    ThemeInit.mobileMenu.init = function initMobileMenu() {
        const { mobileToggle, mobileNav, mobileNavClose, mobilePortfolioItem } = ThemeInit.dom;
        const closeMobileMenu = ThemeInit.mobileMenu.close;
        const openMobileMenu = ThemeInit.mobileMenu.open;

        if (!mobileToggle || !mobileNav) {
            return;
        }

        mobileToggle.addEventListener('click', () => {
            const isExpanded = mobileToggle.getAttribute('aria-expanded') === 'true';
            if (isExpanded) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        if (mobileNavClose) {
            mobileNavClose.addEventListener('click', () => closeMobileMenu());
        }

        if (mobilePortfolioItem) {
            const dropdownToggle = mobilePortfolioItem.querySelector('.mobile-dropdown-toggle');
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    mobilePortfolioItem.classList.toggle('open');
                });
            }
        }

        const navLinks = mobileNav.querySelectorAll('.mobile-nav-link, .mobile-dropdown-link');
        navLinks.forEach((link) => {
            link.addEventListener('click', () => {
                const isDropdownToggle =
                    link.closest('.mobile-nav-has-dropdown') &&
                    link.classList.contains('mobile-nav-link');
                if (isDropdownToggle) {
                    return;
                }
                closeMobileMenu();
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNav.classList.contains('active')) {
                closeMobileMenu();
            }
        });

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth > DESKTOP_BREAKPOINT) {
                    closeMobileMenu();
                }
            }, RESIZE_DEBOUNCE_MS);
        });
    };

    /**
     * Initialize header scroll effect (background when scrolled).
     */
    ThemeInit.headerScroll.init = function initHeaderScroll() {
        const { siteHeader } = ThemeInit.dom;
        if (!siteHeader) {
            return;
        }

        const handleScroll = () => {
            const scrollTop = window.pageYOffset ?? document.documentElement.scrollTop;
            if (scrollTop > SCROLL_THRESHOLD) {
                siteHeader.classList.add('scrolled');
            } else {
                siteHeader.classList.remove('scrolled');
            }
        };

        window.addEventListener('scroll', createRafScrollHandler(handleScroll));
        handleScroll();
    };

    /**
     * Initialize hero background zoom on scroll.
     */
    ThemeInit.heroZoom.init = function initHeroBackgroundZoom() {
        const { hero, heroBgImage } = ThemeInit.dom;
        if (!hero || !heroBgImage) {
            return;
        }

        const updateHeroZoom = () => {
            const scrollY = window.pageYOffset ?? document.documentElement.scrollTop;
            const heroHeight = hero.offsetHeight;
            const progress = Math.min(1, scrollY / heroHeight);
            const scale = 1 + progress * HERO_MAX_ZOOM;
            heroBgImage.style.transform = `scale(${scale})`;
        };

        window.addEventListener('scroll', createRafScrollHandler(updateHeroZoom));
        updateHeroZoom();
    };

    /**
     * Find the menu item <li> for a given hash (or home). Used by active menu state.
     * @param {Element} navMenu - The .nav-menu element.
     * @param {string} hash - Normalized hash (e.g. '' for home, '#about' for section).
     * @returns {Element|null} The <li> to activate, or null.
     */
    function findMenuItemByHash(navMenu, hash) {
        const allLinks = navMenu.querySelectorAll('a');
        let targetLink = null;

        const normalizedHash = hash ? hash.toLowerCase() : '';

        if (!normalizedHash || normalizedHash === '' || normalizedHash === '#') {
            const homeUrl = `${window.location.origin}/`;
            for (const link of allLinks) {
                const href = link.getAttribute('href');
                if (
                    href &&
                    !href.includes('#') &&
                    (href === homeUrl || href === '/' || href === window.location.origin)
                ) {
                    targetLink = link;
                    break;
                }
            }
        } else {
            const hashOnly = normalizedHash.startsWith('#') ? normalizedHash : `#${normalizedHash}`;
            for (const link of allLinks) {
                const href = link.getAttribute('href');
                if (!href) {
                    continue;
                }
                const hrefLower = href.toLowerCase();
                if (
                    hrefLower === hashOnly ||
                    hrefLower.endsWith(hashOnly) ||
                    hrefLower.includes(hashOnly)
                ) {
                    targetLink = link;
                    break;
                }
            }
        }

        return targetLink ? targetLink.closest('li') : null;
    }

    /**
     * Initialize active menu state from URL hash and path (front-page sections + portfolio).
     */
    ThemeInit.activeMenuState.init = function initActiveMenuState() {
        const { navMenu } = ThemeInit.dom;
        if (!navMenu) {
            return;
        }

        const { setNavMenuActive, removeAllNavMenuActiveClasses, addActiveToMenuItem } =
            ThemeInit.utils;

        function setActiveMenuItem(hash) {
            const menuItemLi = findMenuItemByHash(navMenu, hash);
            setNavMenuActive(navMenu, menuItemLi);
        }

        function checkHash() {
            setActiveMenuItem(window.location.hash);
        }

        function checkCurrentPage() {
            const currentPath = window.location.pathname.toLowerCase();
            const currentHash = window.location.hash.toLowerCase();

            if (currentHash && currentHash !== '#') {
                setActiveMenuItem(currentHash);
                return;
            }

            if (currentPath.includes('/portfolio')) {
                removeAllNavMenuActiveClasses(navMenu);
                const allLinks = navMenu.querySelectorAll('a');

                allLinks.forEach((link) => {
                    const href = link.getAttribute('href');
                    if (!href) {
                        return;
                    }
                    try {
                        const linkUrl = new URL(href, window.location.origin);
                        const linkPath = linkUrl.pathname.toLowerCase();

                        if (
                            linkPath === currentPath ||
                            (currentPath.startsWith(linkPath) && linkPath !== '/')
                        ) {
                            const menuItem = link.closest('li');
                            addActiveToMenuItem(menuItem);
                        }

                        if (linkPath === '/portfolio' || linkPath === '/portfolio/') {
                            const portfolioMenuItem = link.closest('li');
                            if (portfolioMenuItem && currentPath.includes('/portfolio')) {
                                portfolioMenuItem.classList.add('current-menu-ancestor', 'is-active');
                            }
                        }
                    } catch {
                        if (
                            href.toLowerCase().includes(currentPath) ||
                            currentPath.includes(href.toLowerCase())
                        ) {
                            const menuItem = link.closest('li');
                            addActiveToMenuItem(menuItem);
                        }
                    }
                });
            } else {
                checkHash();
            }
        }

        window.addEventListener('hashchange', () => {
            const hash = window.location.hash;
            if (hash) {
                setActiveMenuItem(hash);
            } else {
                checkCurrentPage();
            }
        });

        window.addEventListener('popstate', checkCurrentPage);
        checkCurrentPage();
    };

    /**
     * Initialize about image hover (tilt / follow cursor).
     */
    ThemeInit.aboutImageHover.init = function initAboutImageHover() {
        const { aboutImage } = ThemeInit.dom;
        if (!aboutImage) {
            return;
        }

        const maxTilt = 15;
        const maxMove = 10;

        aboutImage.addEventListener('mouseenter', () => {
            aboutImage.style.transition = 'transform 0.1s ease-out';
        });

        aboutImage.addEventListener('mouseleave', () => {
            aboutImage.style.transition = 'transform 0.5s ease-out';
            aboutImage.style.transform = 'translate(0, 0) rotateX(0) rotateY(0)';
        });

        aboutImage.addEventListener('mousemove', (e) => {
            const rect = aboutImage.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            const mouseX = (e.clientX - centerX) / (rect.width / 2);
            const mouseY = (e.clientY - centerY) / (rect.height / 2);

            const rotateY = mouseX * maxTilt;
            const rotateX = -mouseY * maxTilt;
            const translateX = mouseX * maxMove;
            const translateY = mouseY * maxMove;

            aboutImage.style.transform = `translate(${translateX}px, ${translateY}px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });
    };

    /**
     * Initialize project carousel(s): prev/next, indicators, keyboard, touch swipe.
     */
    ThemeInit.projectCarousel.init = function initProjectCarousel() {
        const carousels = document.querySelectorAll('[data-carousel]');
        if (!carousels.length) {
            return;
        }

        carousels.forEach((carousel) => {
            const slides = carousel.querySelectorAll('.carousel-slide');
            const prevBtn = carousel.querySelector('.carousel-prev');
            const nextBtn = carousel.querySelector('.carousel-next');
            const indicators = carousel.querySelectorAll('.carousel-indicator');
            const counter = carousel.querySelector('.carousel-counter');

            if (!slides.length) {
                return;
            }

            let currentSlide = 0;
            const totalSlides = slides.length;

            function normalizeIndex(index) {
                if (index < 0) {
                    return totalSlides - 1;
                }
                if (index >= totalSlides) {
                    return 0;
                }
                return index;
            }

            function goToSlide(index) {
                const nextIndex = normalizeIndex(index);
                slides[currentSlide].classList.remove('active');
                slides[nextIndex].classList.add('active');

                if (indicators.length) {
                    indicators[currentSlide].classList.remove('active');
                    indicators[nextIndex].classList.add('active');
                }

                if (counter) {
                    const currentDisplay = counter.querySelector('.current-slide');
                    if (currentDisplay) {
                        currentDisplay.textContent = String(nextIndex + 1);
                    }
                }

                currentSlide = nextIndex;
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));
            }

            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => goToSlide(index));
            });

            document.addEventListener('keydown', (e) => {
                const rect = carousel.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
                if (!isVisible) {
                    return;
                }
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    goToSlide(currentSlide - 1);
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    goToSlide(currentSlide + 1);
                }
            });

            let touchStartX = 0;
            let touchEndX = 0;

            carousel.addEventListener(
                'touchstart',
                (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                },
                { passive: true }
            );

            carousel.addEventListener(
                'touchend',
                (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    const swipeDistance = touchEndX - touchStartX;
                    if (Math.abs(swipeDistance) < MIN_SWIPE_DISTANCE) {
                        return;
                    }
                    if (swipeDistance > 0) {
                        goToSlide(currentSlide - 1);
                    } else {
                        goToSlide(currentSlide + 1);
                    }
                },
                { passive: true }
            );
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        populateDomCache();
        ThemeInit.navigation.init();
        ThemeInit.mobileMenu.init();
        ThemeInit.headerScroll.init();
        ThemeInit.heroZoom.init();
        ThemeInit.activeMenuState.init();
        ThemeInit.aboutImageHover.init();
        ThemeInit.projectCarousel.init();
    });
})();
