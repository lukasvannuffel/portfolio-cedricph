/**
 * Main JavaScript
 * Handles navigation smooth scrolling, mobile menu, header scroll, hero zoom,
 * active menu state, about image hover, and project carousel.
 */
(function() {
    'use strict';

    const SCROLL_THRESHOLD = 50;
    const HERO_MAX_ZOOM = 0.15;
    const LOGO_SCALE_MIN = 0.5;
    const LOGO_FADE_MIN = 0.25;
    const LOGO_TRANSLATE_VH = 10;
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
        heroLogoScroll: { init: () => {} },
        activeMenuState: { init: () => {} },
        aboutImageHover: { init: () => {} },
        projectCarousel: { init: () => {} },
        portfolioFilter: { init: () => {} },
        featuredLightbox: { init: () => {} },
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
            heroLogo: document.querySelector('.hero-title--logo'),
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
     * Initialize hero logo scroll effect: scale down, move up, fade as user scrolls through hero.
     */
    ThemeInit.heroLogoScroll.init = function initHeroLogoScroll() {
        const { hero, heroLogo } = ThemeInit.dom;
        if (!hero || !heroLogo) {
            return;
        }

        const HERO_LOGO_ENTRANCE_MS = 1400;

        const updateLogoScroll = () => {
            const scrollY = window.pageYOffset ?? document.documentElement.scrollTop;
            const heroHeight = hero.offsetHeight;
            let progress = Math.min(1, scrollY / heroHeight);
            progress = 1 - (1 - progress) ** 1.2;
            const scale = 1 - progress * (1 - LOGO_SCALE_MIN);
            const translateY = -progress * LOGO_TRANSLATE_VH;
            const opacity = 1 - progress * (1 - LOGO_FADE_MIN);
            heroLogo.style.transform = `scale(${scale}) translateY(${translateY}vh)`;
            heroLogo.style.opacity = String(opacity);
        };

        window.addEventListener('scroll', createRafScrollHandler(updateLogoScroll));

        setTimeout(function runAfterEntrance() {
            updateLogoScroll();
        }, HERO_LOGO_ENTRANCE_MS);
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
            const wrapper = carousel.closest('.project-carousel-wrapper');
            const slides = carousel.querySelectorAll('.carousel-slide');
            const prevBtn = carousel.querySelector('.carousel-prev');
            const nextBtn = carousel.querySelector('.carousel-next');
            const indicators = wrapper
                ? wrapper.querySelectorAll('.carousel-thumbnail')
                : carousel.querySelectorAll('.carousel-indicator');
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
                    indicators[nextIndex].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center',
                    });
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

    /**
     * Client-side portfolio filter: search by title + multi-select category.
     * Reads data-title and data-categories attributes on .project-card elements.
     * Runs on archive pages only (exits early when #portfolio-grid is absent).
     */
    ThemeInit.portfolioFilter.init = function initPortfolioFilter() {
        const grid = document.getElementById('portfolio-grid');
        if (!grid) {
            return;
        }

        const filterWrap    = document.querySelector('.portfolio-filters');
        const searchInput   = document.querySelector('.portfolio-filters__search-input');
        const clearBtn      = document.querySelector('.portfolio-filters__search-clear');
        const dropdownBtn   = document.querySelector('.portfolio-filters__dropdown-btn');
        const dropdownLabel = document.querySelector('.portfolio-filters__dropdown-label');
        const listbox       = document.getElementById('portfolio-filter-listbox');
        const options       = listbox ? Array.from(listbox.querySelectorAll('.portfolio-filters__option')) : [];
        const activeTagsEl  = document.querySelector('.portfolio-filters__active-tags');

        if (!filterWrap || !searchInput || !dropdownBtn || !listbox) {
            return;
        }

        let selectedCats  = [];
        let searchQuery   = '';
        let noResultsNode = null;

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function getCards() {
            return Array.from(grid.querySelectorAll('.project-card'));
        }

        function applyFilters() {
            const cards = getCards();
            let visibleCount = 0;

            cards.forEach((card) => {
                const titleAttr = card.dataset.title || '';
                const catAttr   = card.dataset.categories || '';
                const cardCats  = catAttr ? catAttr.split(' ').filter(Boolean) : [];

                const searchMatch = searchQuery === '' || titleAttr.includes(searchQuery);
                const catMatch    = selectedCats.length === 0 || selectedCats.some((cat) => cardCats.includes(cat));
                const visible     = searchMatch && catMatch;

                card.classList.toggle('is-hidden', !visible);
                if (visible) {
                    visibleCount += 1;
                }
            });

            if (noResultsNode) {
                noResultsNode.remove();
                noResultsNode = null;
            }
            if (visibleCount === 0) {
                noResultsNode = document.createElement('p');
                noResultsNode.className = 'portfolio-no-results';
                noResultsNode.innerHTML =
                    '<strong>' + (searchQuery ? 'No results for \u201C' + searchQuery + '\u201D' : 'No projects found') + '</strong>' +
                    'Try a different search term or remove active filters.';
                grid.appendChild(noResultsNode);
            }
        }

        let searchDebounceTimer = null;

        searchInput.addEventListener('input', () => {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                searchQuery = searchInput.value.trim().toLowerCase();
                if (clearBtn) {
                    clearBtn.hidden = searchQuery === '';
                }
                applyFilters();
            }, 180);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                searchQuery = '';
                clearBtn.hidden = true;
                searchInput.focus();
                applyFilters();
            });
        }

        function openDropdown() {
            dropdownBtn.setAttribute('aria-expanded', 'true');
            listbox.classList.add('is-open');
        }

        function closeDropdown() {
            dropdownBtn.setAttribute('aria-expanded', 'false');
            listbox.classList.remove('is-open');
        }

        dropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdownBtn.getAttribute('aria-expanded') === 'true';
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        document.addEventListener('click', (e) => {
            if (!filterWrap.contains(e.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown();
                dropdownBtn.focus();
            }
        });

        function syncDropdownUI() {
            options.forEach((opt) => {
                opt.setAttribute('aria-selected', String(selectedCats.includes(opt.dataset.value)));
            });

            if (dropdownLabel) {
                if (selectedCats.length === 0) {
                    dropdownLabel.textContent = 'All Categories';
                } else if (selectedCats.length === 1) {
                    dropdownLabel.textContent = capitalize(selectedCats[0]);
                } else {
                    dropdownLabel.textContent = selectedCats.length + ' Categories';
                }
            }

            if (activeTagsEl) {
                activeTagsEl.innerHTML = '';
                selectedCats.forEach((cat) => {
                    const tag = document.createElement('span');
                    tag.className = 'portfolio-filters__tag';
                    tag.innerHTML =
                        '<span class="portfolio-filters__tag-label">' + capitalize(cat) + '</span>' +
                        '<button class="portfolio-filters__tag-remove" type="button" aria-label="Remove ' + capitalize(cat) + ' filter">' +
                        '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                        '</button>';
                    tag.querySelector('.portfolio-filters__tag-remove').addEventListener('click', () => {
                        selectedCats = selectedCats.filter((c) => c !== cat);
                        syncDropdownUI();
                        applyFilters();
                    });
                    activeTagsEl.appendChild(tag);
                });
            }
        }

        function toggleOption(optionEl) {
            const value = optionEl.dataset.value;
            if (!value) {
                return;
            }
            if (selectedCats.includes(value)) {
                selectedCats = selectedCats.filter((c) => c !== value);
            } else {
                selectedCats = [...selectedCats, value];
            }
            syncDropdownUI();
            applyFilters();
        }

        options.forEach((opt) => {
            opt.addEventListener('click', () => toggleOption(opt));
            opt.addEventListener('keydown', (e) => {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    toggleOption(opt);
                }
            });
        });

        syncDropdownUI();
        applyFilters();
    };

    /**
     * Featured gallery lightbox with keyboard and swipe support.
     */
    ThemeInit.featuredLightbox.init = function () {
        const lightbox = document.getElementById('featuredLightbox');
        if (!lightbox) {
            return;
        }

        const triggers = document.querySelectorAll('.featured-grid__trigger');
        if (!triggers.length) {
            return;
        }

        const img = lightbox.querySelector('.featured-lightbox__img');
        const closeBtn = lightbox.querySelector('.featured-lightbox__close');
        const prevBtn = lightbox.querySelector('.featured-lightbox__prev');
        const nextBtn = lightbox.querySelector('.featured-lightbox__next');
        const currentEl = lightbox.querySelector('.featured-lightbox__current');
        const totalEl = lightbox.querySelector('.featured-lightbox__total');

        let currentIndex = 0;
        const images = Array.from(triggers).map((trigger) => ({
            src: trigger.getAttribute('data-full-src'),
            alt: trigger.querySelector('img').getAttribute('alt') || '',
        }));

        if (totalEl) {
            totalEl.textContent = String(images.length);
        }

        function showImage(index) {
            currentIndex = ((index % images.length) + images.length) % images.length;
            img.src = images[currentIndex].src;
            img.alt = images[currentIndex].alt;
            if (currentEl) {
                currentEl.textContent = String(currentIndex + 1);
            }
        }

        function openLightbox(index) {
            showImage(index);
            lightbox.removeAttribute('hidden');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeLightbox() {
            lightbox.setAttribute('hidden', '');
            document.body.style.overflow = '';
            img.src = '';
            triggers[currentIndex].focus();
        }

        function nextImage() {
            showImage(currentIndex + 1);
        }

        function prevImage() {
            showImage(currentIndex - 1);
        }

        /* Trigger clicks */
        triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const index = parseInt(trigger.getAttribute('data-index'), 10);
                openLightbox(index);
            });
        });

        /* Close */
        closeBtn.addEventListener('click', closeLightbox);
        lightbox.querySelector('.featured-lightbox__backdrop').addEventListener('click', closeLightbox);

        /* Prev / Next */
        prevBtn.addEventListener('click', prevImage);
        nextBtn.addEventListener('click', nextImage);

        /* Keyboard navigation */
        document.addEventListener('keydown', (e) => {
            if (lightbox.hasAttribute('hidden')) {
                return;
            }

            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowRight') {
                nextImage();
            } else if (e.key === 'ArrowLeft') {
                prevImage();
            }
        });

        /* Touch swipe support */
        let touchStartX = 0;
        let touchStartY = 0;

        lightbox.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].clientX;
            touchStartY = e.changedTouches[0].clientY;
        }, { passive: true });

        lightbox.addEventListener('touchend', (e) => {
            const deltaX = e.changedTouches[0].clientX - touchStartX;
            const deltaY = e.changedTouches[0].clientY - touchStartY;

            if (Math.abs(deltaX) < MIN_SWIPE_DISTANCE || Math.abs(deltaY) > Math.abs(deltaX)) {
                return;
            }

            if (deltaX > 0) {
                prevImage();
            } else {
                nextImage();
            }
        }, { passive: true });
    };

    document.addEventListener('DOMContentLoaded', () => {
        populateDomCache();
        ThemeInit.navigation.init();
        ThemeInit.mobileMenu.init();
        ThemeInit.headerScroll.init();
        ThemeInit.heroZoom.init();
        ThemeInit.heroLogoScroll.init();
        ThemeInit.activeMenuState.init();
        ThemeInit.aboutImageHover.init();
        ThemeInit.projectCarousel.init();
        ThemeInit.portfolioFilter.init();
        ThemeInit.featuredLightbox.init();
    });
})();
