/**
 * Main JavaScript
 * Handles navigation smooth scrolling and mobile menu toggle
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initNavigation();
        initMobileMenu();
        initHeaderScroll();
        initActiveMenuState();
    });

    /**
     * Initialize smooth scrolling for navigation links
     */
    function initNavigation() {
        const navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');
        
        navLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Only handle hash links (sections on same page)
                if (href && href !== '#' && href.startsWith('#')) {
                    e.preventDefault();
                    
                    const targetId = href.substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        const headerHeight = document.querySelector('.site-header').offsetHeight;
                        const targetPosition = targetElement.offsetTop - headerHeight;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                        
                        // Update URL hash
                        history.pushState(null, null, href);
                        
                        // Update active menu state immediately
                        const navMenu = document.querySelector('.nav-menu');
                        if (navMenu) {
                            const allMenuItems = navMenu.querySelectorAll('li');
                            allMenuItems.forEach(function(item) {
                                item.classList.remove('current-menu-item', 'current_page_item', 'is-active');
                            });
                            
                            const menuItem = this.closest('li');
                            if (menuItem) {
                                menuItem.classList.add('current-menu-item', 'is-active');
                            }
                        }
                        
                        // Close mobile menu if open
                        closeMobileMenu();
                    }
                }
            });
        });
    }

    /**
     * Initialize mobile menu toggle
     */
    function initMobileMenu() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const mainNavigation = document.querySelector('.main-navigation');
        
        if (!mobileToggle || !mainNavigation) {
            return;
        }
        
        mobileToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (mainNavigation.classList.contains('active') && 
                !mainNavigation.contains(e.target) && 
                !mobileToggle.contains(e.target)) {
                closeMobileMenu();
            }
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mainNavigation.classList.contains('active')) {
                closeMobileMenu();
            }
        });
        
        // Close menu when clicking on a nav link
        const navLinks = mainNavigation.querySelectorAll('a');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                closeMobileMenu();
            });
        });
    }

    /**
     * Open mobile menu
     */
    function openMobileMenu() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const mainNavigation = document.querySelector('.main-navigation');
        const body = document.body;
        
        if (mobileToggle && mainNavigation) {
            mobileToggle.setAttribute('aria-expanded', 'true');
            mainNavigation.classList.add('active');
            body.style.overflow = 'hidden'; // Prevent body scroll when menu is open
        }
    }

    /**
     * Close mobile menu
     */
    function closeMobileMenu() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const mainNavigation = document.querySelector('.main-navigation');
        const body = document.body;
        
        if (mobileToggle && mainNavigation) {
            mobileToggle.setAttribute('aria-expanded', 'false');
            mainNavigation.classList.remove('active');
            body.style.overflow = ''; // Restore body scroll
        }
    }

    /**
     * Initialize header scroll effect
     * Adds background color when scrolled
     */
    function initHeaderScroll() {
        const siteHeader = document.querySelector('.site-header');
        
        if (!siteHeader) {
            return;
        }
        
        let lastScrollTop = 0;
        const scrollThreshold = 50; // Pixels to scroll before adding background
        
        function handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > scrollThreshold) {
                siteHeader.classList.add('scrolled');
            } else {
                siteHeader.classList.remove('scrolled');
            }
            
            lastScrollTop = scrollTop;
        }
        
        // Use requestAnimationFrame for smooth performance
        let ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });
        
        // Check initial scroll position
        handleScroll();
    }

    /**
     * Initialize active menu state based on URL hash
     * Handles: / (Home), /#about (About), /#featured (Featured), /#contact (Contact)
     * Also handles Portfolio pages and parent-child relationships
     */
    function initActiveMenuState() {
        const navMenu = document.querySelector('.nav-menu');
        
        if (!navMenu) {
            return;
        }
        
        // Remove all active classes first
        function removeAllActiveClasses() {
            const allMenuItems = navMenu.querySelectorAll('li');
            allMenuItems.forEach(function(item) {
                item.classList.remove('current-menu-item', 'current_page_item', 'is-active');
            });
        }
        
        // Add active class to specific menu item
        function setActiveMenuItem(hash) {
            removeAllActiveClasses();
            
            const allLinks = navMenu.querySelectorAll('a');
            let targetLink = null;
            
            // Normalize hash
            hash = hash ? hash.toLowerCase() : '';
            
            if (!hash || hash === '' || hash === '#') {
                // Home page - find link to home URL (no hash)
                const homeUrl = window.location.origin + '/';
                allLinks.forEach(function(link) {
                    const href = link.getAttribute('href');
                    // Check if it's a home link (no hash, or just /)
                    if (href && !href.includes('#') && (href === homeUrl || href === '/' || href === window.location.origin)) {
                        targetLink = link;
                    }
                });
            } else {
                // Find link with matching hash
                // Hash can be like "#about" or "/#about" or "http://site.com/#about"
                const hashOnly = hash.startsWith('#') ? hash : '#' + hash;
                
                allLinks.forEach(function(link) {
                    const href = link.getAttribute('href');
                    if (href) {
                        const hrefLower = href.toLowerCase();
                        // Check if href ends with the hash or contains the hash
                        if (hrefLower === hashOnly || 
                            hrefLower.endsWith(hashOnly) || 
                            hrefLower.includes(hashOnly)) {
                            targetLink = link;
                        }
                    }
                });
            }
            
            if (targetLink) {
                const menuItem = targetLink.closest('li');
                if (menuItem) {
                    menuItem.classList.add('current-menu-item', 'is-active');
                    
                    // Also mark parent menu item as active if this is a child item
                    const parentMenuItem = menuItem.parentElement.closest('li');
                    if (parentMenuItem && parentMenuItem.classList.contains('menu-item-has-children')) {
                        parentMenuItem.classList.add('current-menu-ancestor', 'is-active');
                    }
                }
            }
        }
        
        // Check current page URL for Portfolio pages
        function checkCurrentPage() {
            const currentPath = window.location.pathname.toLowerCase();
            const currentHash = window.location.hash.toLowerCase();
            
            // If we have a hash, use hash-based logic (for front-page sections)
            if (currentHash && currentHash !== '#') {
                setActiveMenuItem(currentHash);
                return;
            }
            
            // Check for Portfolio pages
            if (currentPath.includes('/portfolio')) {
                removeAllActiveClasses();
                
                const allLinks = navMenu.querySelectorAll('a');
                
                // Find and activate the current page link
                allLinks.forEach(function(link) {
                    const href = link.getAttribute('href');
                    if (href) {
                        try {
                            const linkUrl = new URL(href, window.location.origin);
                            const linkPath = linkUrl.pathname.toLowerCase();
                            
                            // Check if this link matches current page
                            if (linkPath === currentPath || 
                                (currentPath.startsWith(linkPath) && linkPath !== '/')) {
                                const menuItem = link.closest('li');
                                if (menuItem) {
                                    menuItem.classList.add('current-menu-item', 'is-active');
                                    
                                    // Also activate parent if this is a child
                                    const parentMenuItem = menuItem.parentElement.closest('li');
                                    if (parentMenuItem && parentMenuItem.classList.contains('menu-item-has-children')) {
                                        parentMenuItem.classList.add('current-menu-ancestor', 'is-active');
                                    }
                                }
                            }
                            
                            // Also activate Portfolio parent if we're on any portfolio page
                            if (linkPath === '/portfolio' || linkPath === '/portfolio/') {
                                const portfolioMenuItem = link.closest('li');
                                if (portfolioMenuItem && currentPath.includes('/portfolio')) {
                                    portfolioMenuItem.classList.add('current-menu-ancestor', 'is-active');
                                }
                            }
                        } catch (e) {
                            // If URL parsing fails, try simple string comparison
                            if (href.toLowerCase().includes(currentPath) || currentPath.includes(href.toLowerCase())) {
                                const menuItem = link.closest('li');
                                if (menuItem) {
                                    menuItem.classList.add('current-menu-item', 'is-active');
                                    
                                    // Also activate parent if this is a child
                                    const parentMenuItem = menuItem.parentElement.closest('li');
                                    if (parentMenuItem && parentMenuItem.classList.contains('menu-item-has-children')) {
                                        parentMenuItem.classList.add('current-menu-ancestor', 'is-active');
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // For other pages or home, check hash
                checkHash();
            }
        }
        
        // Check initial hash
        function checkHash() {
            const hash = window.location.hash;
            setActiveMenuItem(hash);
        }
        
        // Listen for hash changes
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash;
            if (hash) {
                setActiveMenuItem(hash);
            } else {
                checkCurrentPage();
            }
        });
        
        // Listen for popstate (back/forward buttons)
        window.addEventListener('popstate', checkCurrentPage);
        
        // Initial check
        checkCurrentPage();
    }

    /**
     * Handle window resize - close mobile menu on desktop
     */
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        }, 250);
    });
})();