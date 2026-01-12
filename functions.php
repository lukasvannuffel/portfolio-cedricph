<?php
/**
 * Theme Functions
 */

// Theme Support
function mytheme_setup() {
    // Title tag support
    add_theme_support('title-tag');
    
    // Featured images
    add_theme_support('post-thumbnails');
    
    // Custom logo
    add_theme_support('custom-logo');
    
    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Register Navigation Menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'mijn-custom-theme'),
        // 'footer' => __('Footer Menu', 'mijn-custom-theme'), // Optioneel
    ));
}
add_action('after_setup_theme', 'mytheme_setup');

// Enqueue Scripts & Styles
function mytheme_scripts() {
    // Main CSS
    wp_enqueue_style(
        'main-style',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        '1.0.0'
    );
    
    // Main JS
    wp_enqueue_script(
        'main-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'mytheme_scripts');

/**
 * ------------------------------------------------------------
 * -------------------- CUSTOM FUNCTIONS --------------------
 * ------------------------------------------------------------
 */

/**
 * Add custom classes and attributes to navigation menu items
 * This helps with smooth scrolling to sections
 */
function mytheme_nav_menu_link_attributes($atts, $item, $args) {
    // Only apply to primary menu
    if ($args->theme_location === 'primary') {
        // If menu item URL contains a hash, ensure it's properly formatted
        if (isset($atts['href']) && strpos($atts['href'], '#') !== false) {
            $atts['class'] = isset($atts['class']) ? $atts['class'] . ' section-link' : 'section-link';
        }
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'mytheme_nav_menu_link_attributes', 10, 3);

/**
 * Remove active classes from menu items that link to hash sections
 * We'll handle active states via JavaScript based on URL hash
 */
function mytheme_nav_menu_css_class($classes, $item, $args) {
    // Only apply to primary menu
    if ($args->theme_location === 'primary') {
        // Get the URL
        $url = $item->url;
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // Check if URL contains a hash
        $has_hash = (strpos($url, '#') !== false);
        
        // If it's a hash link, remove WordPress default active classes
        // JavaScript will handle the active state based on URL hash
        if ($has_hash) {
            $classes = array_diff($classes, array('current-menu-item', 'current_page_item'));
        }
        
        // For home link (no hash), keep active only if:
        // - We're on front page AND
        // - No hash in current URL
        if (!$has_hash && ($url === home_url('/') || $url === home_url())) {
            $current_has_hash = (strpos($current_url, '#') !== false);
            if (!is_front_page() || $current_has_hash) {
                $classes = array_diff($classes, array('current-menu-item', 'current_page_item'));
            }
        }
    }
    
    return $classes;
}
add_filter('nav_menu_css_class', 'mytheme_nav_menu_css_class', 10, 3);

/**
 * Add body class for front page to help with styling
 */
function mytheme_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'is-front-page';
    }
    return $classes;
}
add_filter('body_class', 'mytheme_body_classes');

?>