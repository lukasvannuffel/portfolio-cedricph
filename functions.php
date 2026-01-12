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


?>