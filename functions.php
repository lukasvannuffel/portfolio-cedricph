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
 * But keep WordPress default behavior for regular pages (like Portfolio)
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
        
        // For Portfolio pages, keep WordPress default behavior
        // WordPress will automatically add current-menu-ancestor to parent
        // when a child page is active
    }
    
    return $classes;
}
add_filter('nav_menu_css_class', 'mytheme_nav_menu_css_class', 10, 3);

/**
 * Custom walker: render parent items with URL "#" as a span (dropdown trigger only), not a link.
 */
class Cedricph_Dropdown_Nav_Walker extends Walker_Nav_Menu {

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        if ($depth !== 0 || !$args || !isset($args->theme_location)) {
            parent::start_el($output, $item, $depth, $args, $id);
            return;
        }

        $is_dropdown_only = (
            $args->theme_location === 'primary'
            && in_array('menu-item-has-children', (array) $item->classes, true)
            && $this->is_hash_only_url($item->url)
        );

        if (!$is_dropdown_only) {
            parent::start_el($output, $item, $depth, $args, $id);
            return;
        }

        $indent = ($depth) ? str_repeat("\t", $depth) : '';
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $class_names = implode(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= $indent . '<li' . $class_names . '>';
        $item_output = $args->before ?? '';
        $item_output .= '<span class="menu-item-dropdown-trigger" role="button" tabindex="0">';
        $item_output .= ($args->link_before ?? '') . apply_filters('the_title', $item->title, $item->ID) . ($args->link_after ?? '');
        $item_output .= '</span>';
        $item_output .= $args->after ?? '';
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    private function is_hash_only_url($url) {
        $url = trim((string) $url);
        if ($url === '' || $url === '#') {
            return true;
        }
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        return $path === '' && ($fragment !== false || strpos($url, '#') !== false);
    }
}

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

/**
 * Register ACF Field Groups
 * Hero Section Fields for Front Page
 */
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
        'key' => 'group_hero_section',
        'title' => 'Hero Section',
        'fields' => array(
            array(
                'key' => 'field_hero_background_image',
                'label' => 'Background Image',
                'name' => 'hero_background_image',
                'type' => 'image',
                'instructions' => 'Upload a background image for the hero section. Recommended size: 1920x1080px or larger.',
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
            ),
            array(
                'key' => 'field_hero_title',
                'label' => 'Title',
                'name' => 'hero_title',
                'type' => 'text',
                'instructions' => 'Enter the main hero title (e.g., "Capturing Moments That Matter")',
                'required' => 1,
                'default_value' => 'Capturing Moments That Matter',
                'placeholder' => 'Enter hero title',
            ),
            array(
                'key' => 'field_hero_subtitle',
                'label' => 'Subtitle / Description',
                'name' => 'hero_subtitle',
                'type' => 'textarea',
                'instructions' => 'Enter the subtitle or description text below the title',
                'required' => 0,
                'rows' => 3,
                'default_value' => 'Event & portrait photography that tells your story through authentic, cinematic imagery',
                'placeholder' => 'Enter subtitle text',
            ),
            array(
                'key' => 'field_hero_cta_text',
                'label' => 'CTA Button Text',
                'name' => 'hero_cta_text',
                'type' => 'text',
                'instructions' => 'Enter the text for the call-to-action button',
                'required' => 0,
                'default_value' => 'View portfolio',
                'placeholder' => 'e.g., View portfolio',
            ),
            array(
                'key' => 'field_hero_cta_link',
                'label' => 'CTA Button Link',
                'name' => 'hero_cta_link',
                'type' => 'link',
                'instructions' => 'Select where the CTA button should link to',
                'required' => 0,
                'return_format' => 'array',
            ),
            array(
                'key' => 'field_hero_show_scroll_indicator',
                'label' => 'Show Scroll Indicator',
                'name' => 'hero_show_scroll_indicator',
                'type' => 'true_false',
                'instructions' => 'Display a scroll indicator arrow at the bottom of the hero section',
                'required' => 0,
                'default_value' => 1,
                'ui' => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'page_type',
                    'operator' => '==',
                    'value' => 'front_page',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => 'Hero section fields for the front page',
    ));

    // About Section Fields for Front Page
    acf_add_local_field_group(array(
        'key' => 'group_about_section',
        'title' => 'About Section',
        'fields' => array(
            array(
                'key' => 'field_about_profile_image',
                'label' => 'Profile Image',
                'name' => 'about_profile_image',
                'type' => 'image',
                'instructions' => 'Upload a profile image for the about section. Recommended size: 600x600px or larger (square format works best).',
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
            ),
            array(
                'key' => 'field_about_text',
                'label' => 'About Text',
                'name' => 'about_text',
                'type' => 'wysiwyg',
                'instructions' => 'Enter the about me text content. You can format the text using the editor.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'page_type',
                    'operator' => '==',
                    'value' => 'front_page',
                ),
            ),
        ),
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => 'About section fields for the front page',
    ));

    // Contact Section Fields for Front Page
    acf_add_local_field_group(array(
        'key' => 'group_contact_section',
        'title' => 'Contact Section',
        'fields' => array(
            array(
                'key' => 'field_contact_description',
                'label' => 'Description Text',
                'name' => 'contact_description',
                'type' => 'wysiwyg',
                'instructions' => 'Enter the description text that appears below the title. You can format the text using the editor.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
                'default_value' => 'Interested in working together? Fill out the form below and I\'ll get back to you within 24 hours.',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'page_type',
                    'operator' => '==',
                    'value' => 'front_page',
                ),
            ),
        ),
        'menu_order' => 2,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => 'Contact section fields for the front page',
    ));
}

?>