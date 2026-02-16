<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <!-- Logo -->
            <div class="site-branding">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title">
                        <span class="site-title-full"><?php echo esc_html(get_bloginfo('name', 'display')); ?></span>
                        <span class="site-title-mobile">CP</span>
                    </a>
                    <?php
                }
                ?>
            </div>

            <!-- Navigation -->
            <nav class="main-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class' => 'nav-menu',
                    'container' => false,
                    'fallback_cb' => false, // No fallback when menu is not set.
                    'walker' => new Cedricph_Dropdown_Nav_Walker(),
                ));
                ?>
            </nav>

            <!-- Instagram Link -->
            <div class="header-social">
                <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="instagram-link" aria-label="Visit Instagram">
                    <svg class="instagram-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Navigation Overlay -->
<nav class="mobile-nav-overlay" id="mobileNav">
    <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">
        <span></span>
        <span></span>
    </button>
    <div class="mobile-nav-accent-line"></div>
    <div class="mobile-nav-container">
        <ul class="mobile-nav-list">
            <li class="mobile-nav-item">
                <a href="<?php echo esc_url(home_url('/#hero')); ?>" class="mobile-nav-link"><?php esc_html_e('Home', 'cedricph'); ?></a>
            </li>
            <li class="mobile-nav-item">
                <a href="<?php echo esc_url(home_url('/#about')); ?>" class="mobile-nav-link"><?php esc_html_e('About', 'cedricph'); ?></a>
            </li>
            <li class="mobile-nav-item">
                <a href="<?php echo esc_url(home_url('/#featured')); ?>" class="mobile-nav-link"><?php esc_html_e('Featured', 'cedricph'); ?></a>
            </li>
            <li class="mobile-nav-item mobile-nav-has-dropdown" id="mobilePortfolioItem">
                <div class="mobile-dropdown-toggle">
                    <a href="#" class="mobile-nav-link"><?php esc_html_e('Portfolio', 'cedricph'); ?></a>
                </div>
                <ul class="mobile-dropdown-menu">
                    <li class="mobile-dropdown-item">
                        <a href="<?php echo esc_url(cedricph_get_portfolio_page_url('analog')); ?>" class="mobile-dropdown-link"><?php esc_html_e('Analog', 'cedricph'); ?></a>
                    </li>
                    <li class="mobile-dropdown-item">
                        <a href="<?php echo esc_url(cedricph_get_portfolio_page_url('digital')); ?>" class="mobile-dropdown-link"><?php esc_html_e('Digital', 'cedricph'); ?></a>
                    </li>
                </ul>
            </li>
            <li class="mobile-nav-item">
                <a href="<?php echo esc_url(home_url('/#contact')); ?>" class="mobile-nav-link"><?php esc_html_e('Contact', 'cedricph'); ?></a>
            </li>
        </ul>
        <div class="mobile-nav-footer">
            <p class="mobile-nav-footer-text">
                <?php echo esc_html__('Capturing moments through the lens.', 'cedricph'); ?>
            </p>
        </div>
    </div>
</nav>