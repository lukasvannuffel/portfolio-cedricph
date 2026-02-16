<?php
/**
 * Theme Functions
 */

/**
 * Registers theme support and navigation menus.
 *
 * @return void
 */
function mytheme_setup(): void {
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
        'primary' => __('Primary Menu', 'cedricph'),
    ));
}
add_action('after_setup_theme', 'mytheme_setup');

/**
 * Registers the Project custom post type.
 *
 * @return void
 */
function cedricph_register_project_cpt(): void {
    $labels = array(
        'name'                  => _x('Projects', 'Post Type General Name', 'cedricph'),
        'singular_name'         => _x('Project', 'Post Type Singular Name', 'cedricph'),
        'menu_name'             => __('Projects', 'cedricph'),
        'name_admin_bar'        => __('Project', 'cedricph'),
        'archives'              => __('Project Archives', 'cedricph'),
        'attributes'            => __('Project Attributes', 'cedricph'),
        'parent_item_colon'     => __('Parent Project:', 'cedricph'),
        'all_items'             => __('All Projects', 'cedricph'),
        'add_new_item'          => __('Add New Project', 'cedricph'),
        'add_new'               => __('Add New', 'cedricph'),
        'new_item'              => __('New Project', 'cedricph'),
        'edit_item'             => __('Edit Project', 'cedricph'),
        'update_item'           => __('Update Project', 'cedricph'),
        'view_item'             => __('View Project', 'cedricph'),
        'view_items'            => __('View Projects', 'cedricph'),
        'search_items'          => __('Search Project', 'cedricph'),
        'not_found'             => __('Not found', 'cedricph'),
        'not_found_in_trash'    => __('Not found in Trash', 'cedricph'),
        'featured_image'        => __('Featured Image', 'cedricph'),
        'set_featured_image'    => __('Set featured image', 'cedricph'),
        'remove_featured_image' => __('Remove featured image', 'cedricph'),
        'use_featured_image'    => __('Use as featured image', 'cedricph'),
        'insert_into_item'      => __('Insert into project', 'cedricph'),
        'uploaded_to_this_item' => __('Uploaded to this project', 'cedricph'),
        'items_list'            => __('Projects list', 'cedricph'),
        'items_list_navigation' => __('Projects list navigation', 'cedricph'),
        'filter_items_list'     => __('Filter projects list', 'cedricph'),
    );

    $args = array(
        'label'                 => __('Project', 'cedricph'),
        'description'           => __('Photography projects', 'cedricph'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'revisions'),
        'taxonomies'            => array('project_type'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-camera',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'rewrite'               => array('slug' => 'project', 'with_front' => false),
        'capability_type'       => 'post',
        'show_in_rest'          => false,
    );

    register_post_type('project', $args);
}
add_action('init', 'cedricph_register_project_cpt', 0);

/**
 * Registers the Project Type taxonomy for projects.
 *
 * @return void
 */
function cedricph_register_project_type_taxonomy(): void {
    $labels = array(
        'name'                       => _x('Project Types', 'Taxonomy General Name', 'cedricph'),
        'singular_name'              => _x('Project Type', 'Taxonomy Singular Name', 'cedricph'),
        'menu_name'                  => __('Project Types', 'cedricph'),
        'all_items'                  => __('All Types', 'cedricph'),
        'parent_item'                => __('Parent Type', 'cedricph'),
        'parent_item_colon'          => __('Parent Type:', 'cedricph'),
        'new_item_name'              => __('New Type Name', 'cedricph'),
        'add_new_item'               => __('Add New Type', 'cedricph'),
        'edit_item'                  => __('Edit Type', 'cedricph'),
        'update_item'                => __('Update Type', 'cedricph'),
        'view_item'                  => __('View Type', 'cedricph'),
        'separate_items_with_commas' => __('Separate types with commas', 'cedricph'),
        'add_or_remove_items'        => __('Add or remove types', 'cedricph'),
        'choose_from_most_used'      => __('Choose from the most used', 'cedricph'),
        'popular_items'              => __('Popular Types', 'cedricph'),
        'search_items'               => __('Search Types', 'cedricph'),
        'not_found'                  => __('Not Found', 'cedricph'),
        'no_terms'                   => __('No types', 'cedricph'),
        'items_list'                 => __('Types list', 'cedricph'),
        'items_list_navigation'      => __('Types list navigation', 'cedricph'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'rewrite'                    => array('slug' => 'project-type'),
        'show_in_rest'               => false,
        'meta_box_cb'                => 'cedricph_project_type_radio_meta_box',
    );

    register_taxonomy('project_type', array('project'), $args);
}
add_action('init', 'cedricph_register_project_type_taxonomy', 0);

/**
 * Extracts gallery images from project post content (gallery shortcode or img tags).
 * Returns an array of items with 'url' and 'alt' keys, with duplicates removed.
 *
 * @param int $postId Post ID (project).
 * @return array<int, array{url: string, alt: string}>
 */
function cedricph_get_project_gallery_images(int $postId): array {
    $content = get_post_field('post_content', $postId);
    $gallery = array();

    if (preg_match('/\[gallery ids=["\']([^"\']+)["\']\]/i', $content, $gallery_match)) {
        $attachment_ids = explode(',', $gallery_match[1]);
        $post_title = get_the_title($postId);
        foreach ($attachment_ids as $attachment_id) {
            $attachment_id = (int) trim($attachment_id);
            if ($attachment_id <= 0) {
                continue;
            }
            $full_url = wp_get_attachment_image_url($attachment_id, 'full');
            if ($full_url) {
                $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                $gallery[] = array(
                    'url' => $full_url,
                    'alt' => $alt ?: $post_title,
                );
            }
        }
    }

    if (empty($gallery)) {
        preg_match_all('/<img[^>]+>/i', $content, $img_tags);
        $post_title = get_the_title($postId);
        if (!empty($img_tags[0])) {
            foreach ($img_tags[0] as $img_tag) {
                $image_added = false;
                if (preg_match('/wp-image-(\d+)/i', $img_tag, $class_id)) {
                    $attachment_id = (int) $class_id[1];
                    $full_url = wp_get_attachment_image_url($attachment_id, 'full');
                    if ($full_url) {
                        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                        $gallery[] = array(
                            'url' => $full_url,
                            'alt' => $alt ?: $post_title,
                        );
                        $image_added = true;
                    }
                }
                if (!$image_added && preg_match('/src=["\']([^"\']+)["\']/i', $img_tag, $src_match)) {
                    $alt = '';
                    if (preg_match('/alt=["\']([^"\']*)["\']/i', $img_tag, $alt_match)) {
                        $alt = $alt_match[1];
                    }
                    $gallery[] = array(
                        'url' => $src_match[1],
                        'alt' => $alt ?: $post_title,
                    );
                }
            }
        }
    }

    $seen_urls = array();
    $unique = array();
    foreach ($gallery as $image) {
        if (!in_array($image['url'], $seen_urls, true)) {
            $seen_urls[] = $image['url'];
            $unique[] = $image;
        }
    }
    return $unique;
}

/**
 * Renders the custom meta box for Project Type with checkboxes.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function cedricph_project_type_radio_meta_box(WP_Post $post): void {
    $terms = get_terms(array(
        'taxonomy'   => 'project_type',
        'hide_empty' => false,
    ));

    $current_terms = wp_get_post_terms($post->ID, 'project_type', array('fields' => 'ids'));
    ?>
    <div id="taxonomy-project_type" class="categorydiv">
        <div id="project-type-all" class="tabs-panel">
            <ul id="project-typechecklist" class="categorychecklist form-no-clear">
                <?php foreach ($terms as $term): ?>
                    <li id="project-type-<?php echo esc_attr($term->term_id); ?>">
                        <label class="selectit">
                            <input
                                type="checkbox"
                                name="tax_input[project_type][]"
                                id="in-project-type-<?php echo esc_attr($term->term_id); ?>"
                                value="<?php echo esc_attr($term->term_id); ?>"
                                <?php checked(in_array($term->term_id, $current_terms, true)); ?>
                            >
                            <?php echo esc_html($term->name); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Adds the instruction meta box for uploading project images.
 *
 * @return void
 */
function cedricph_project_images_instructions(): void {
    add_meta_box(
        'project_images_instructions',
        '📸 How to Add Project Images',
        'cedricph_render_images_instructions',
        'project',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cedricph_project_images_instructions');

/**
 * Renders the instructions meta box content for project images.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function cedricph_render_images_instructions(WP_Post $post): void {
    $content = $post->post_content;
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*/i', $content, $matches);
    $count = !empty($matches[1]) ? count($matches[1]) : 0;
    ?>
    <div style="padding: 15px; background: #f8f9fa; border-left: 4px solid #D4A574; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #D4A574;">📷 Upload Images for the Carousel</h4>
        <ol style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
            <li><strong>Click "Add Media"</strong> button below</li>
            <li><strong>Select or upload</strong> all images you want in the carousel</li>
            <li><strong>Click "Insert into post"</strong> - the images will appear in the editor</li>
            <li><strong>Save/Update</strong> the project when done</li>
        </ol>
        <p style="margin: 10px 0 0 0; font-size: 13px; color: #666;">
            <?php if ($count > 0): ?>
                ✅ <strong style="color: #28a745;"><?php echo esc_html((string) $count); ?> image<?php echo esc_html($count > 1 ? 's' : ''); ?> uploaded</strong> - <?php echo esc_html__('These will appear in the carousel', 'cedricph'); ?>
            <?php else: ?>
                <?php echo esc_html__('⚠️ No images uploaded yet - Click "Add Media" below to get started', 'cedricph'); ?>
            <?php endif; ?>
        </p>
    </div>
    <?php
}

/**
 * Adds default placeholder text to the project editor when empty.
 *
 * @param string  $content Default content.
 * @param WP_Post $post    Current post object.
 * @return string Filtered content.
 */
function cedricph_project_default_content(string $content, WP_Post $post): string {
    if ($post->post_type !== 'project' || !empty($content)) {
        return $content;
    }

    return '📸 UPLOAD YOUR IMAGES HERE

Click the "Add Media" button above to upload all the photos for this project.

After uploading, you can delete this text - it\'s just here to guide you!';
}
add_filter('default_content', 'cedricph_project_default_content', 10, 2);

/**
 * Creates default project type terms (analog, digital) on first run.
 *
 * @return void
 */
function cedricph_create_default_project_types(): void {
    // Only run once
    if (get_option('cedricph_project_types_created')) {
        return;
    }

    // Ensure taxonomy is registered
    if (!taxonomy_exists('project_type')) {
        return;
    }

    // Create terms
    $terms = array('analog', 'digital');

    foreach ($terms as $term) {
        if (!term_exists($term, 'project_type')) {
            wp_insert_term(
                ucfirst($term),
                'project_type',
                array('slug' => $term)
            );
        }
    }

    // Mark as created
    update_option('cedricph_project_types_created', true);
}
add_action('init', 'cedricph_create_default_project_types', 11);

/**
 * Enqueues theme styles and scripts.
 *
 * @return void
 */
function mytheme_scripts(): void {
    // Main CSS
    wp_enqueue_style(
        'main-style',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        '2.0.0'
    );

    // Main JS
    wp_enqueue_script(
        'main-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        '2.0.0',
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
 * Adds custom classes to navigation menu items for hash links (section-link).
 * Rewrites Analog/Digital menu item URLs to the correct portfolio page permalinks.
 *
 * @param array   $atts  HTML attributes for the menu item.
 * @param WP_Post $item  Menu item object.
 * @param stdClass $args  Nav menu arguments.
 * @return array Modified attributes.
 */
function mytheme_nav_menu_link_attributes(array $atts, $item, stdClass $args): array {
    if ($args->theme_location !== 'primary') {
        return $atts;
    }

    $title = trim((string) $item->title);
    if (strtolower($title) === 'analog') {
        $atts['href'] = cedricph_get_portfolio_page_url('analog');
    } elseif (strtolower($title) === 'digital') {
        $atts['href'] = cedricph_get_portfolio_page_url('digital');
    }

    if (isset($atts['href']) && strpos($atts['href'], '#') !== false) {
        $atts['class'] = isset($atts['class']) ? $atts['class'] . ' section-link' : 'section-link';
    }

    return $atts;
}
add_filter('nav_menu_link_attributes', 'mytheme_nav_menu_link_attributes', 10, 3);

/**
 * Removes active classes from menu items that link to hash sections.
 * Active states for hash links are handled via JavaScript.
 *
 * @param string[]  $classes CSS classes applied to the menu item.
 * @param WP_Post   $item    Menu item object.
 * @param stdClass  $args    Nav menu arguments.
 * @return string[] Modified classes.
 */
function mytheme_nav_menu_css_class(array $classes, $item, stdClass $args): array {
    if ($args->theme_location !== 'primary') {
        return $classes;
    }

    $url = $item->url;
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $current_url = home_url($request_uri);
    $has_hash = strpos($url, '#') !== false;

    if ($has_hash) {
        $classes = array_diff($classes, array('current-menu-item', 'current_page_item'));
        return $classes;
    }

    $is_home_link = ($url === home_url('/') || $url === home_url());
    if ($is_home_link) {
        $current_has_hash = strpos($current_url, '#') !== false;
        if (!is_front_page() || $current_has_hash) {
            $classes = array_diff($classes, array('current-menu-item', 'current_page_item'));
        }
    }

    return $classes;
}
add_filter('nav_menu_css_class', 'mytheme_nav_menu_css_class', 10, 3);

/**
 * Custom walker: renders parent items with URL "#" as a span (dropdown trigger only), not a link.
 */
class Cedricph_Dropdown_Nav_Walker extends Walker_Nav_Menu {

    /**
     * Starts the element output.
     *
     * @param string   $output Used to append additional content.
     * @param WP_Post  $item   Menu item data object.
     * @param int      $depth  Depth of menu item.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     * @param int      $id     Current item ID.
     * @return void
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0): void {
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

    /**
     * Checks whether the URL is hash-only (no path, e.g. "#" or "#section").
     *
     * @param string $url Menu item URL.
     * @return bool True if URL is hash-only.
     */
    private function is_hash_only_url(string $url): bool {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return true;
        }
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        return $path === '' && ($fragment !== false || strpos($url, '#') !== false);
    }
}

/**
 * Adds body class for front page to help with styling.
 *
 * @param string[] $classes Existing body classes.
 * @return string[] Modified classes.
 */
function mytheme_body_classes(array $classes): array {
    if (is_front_page()) {
        $classes[] = 'is-front-page';
    }
    return $classes;
}
add_filter('body_class', 'mytheme_body_classes');

/**
 * Returns the permalink for the Analog or Digital portfolio page (by template).
 * Use when building nav or back links so the correct page URL is used regardless of slug.
 *
 * @param string $type Either 'analog' or 'digital'.
 * @return string URL for the page, or home_url('/analog'|'/digital') if no page found.
 */
function cedricph_get_portfolio_page_url(string $type): string {
    $templates = array(
        'analog'  => 'page-analog.php',
        'digital' => 'page-digital.php',
    );
    if (!isset($templates[$type])) {
        return home_url('/');
    }
    $pages = get_pages(array(
        'meta_key'   => '_wp_page_template',
        'meta_value' => $templates[$type],
        'number'     => 1,
    ));
    if (!empty($pages)) {
        return get_permalink($pages[0]->ID);
    }
    return home_url('/' . $type);
}

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
                'key' => 'field_about_quote',
                'label' => 'Quote',
                'name' => 'about_quote',
                'type' => 'textarea',
                'instructions' => 'Enter a featured quote about your photography philosophy.',
                'required' => 0,
                'rows' => 3,
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
            array(
                'key' => 'field_about_based_in',
                'label' => 'Based In',
                'name' => 'about_based_in',
                'type' => 'text',
                'instructions' => 'Enter your location (e.g., Brussels, Belgium)',
                'required' => 0,
                'default_value' => '',
            ),
            array(
                'key' => 'field_about_focus',
                'label' => 'Focus',
                'name' => 'about_focus',
                'type' => 'text',
                'instructions' => 'Enter your photography focus (e.g., Street & Portrait)',
                'required' => 0,
                'default_value' => '',
            ),
            array(
                'key' => 'field_about_specialization',
                'label' => 'Specialization',
                'name' => 'about_specialization',
                'type' => 'text',
                'instructions' => 'Enter your specialization (e.g., Analog & Digital)',
                'required' => 0,
                'default_value' => '',
            ),
            array(
                'key' => 'field_about_experience',
                'label' => 'Experience',
                'name' => 'about_experience',
                'type' => 'text',
                'instructions' => 'Enter your experience (e.g., 5+ Years)',
                'required' => 0,
                'default_value' => '',
            ),
            array(
                'key' => 'field_about_cta_text',
                'label' => 'CTA Button Text',
                'name' => 'about_cta_text',
                'type' => 'text',
                'instructions' => 'Enter the call-to-action button text (e.g., Get In Touch)',
                'required' => 0,
                'default_value' => 'Get In Touch',
            ),
            array(
                'key' => 'field_about_cta_link',
                'label' => 'CTA Button Link',
                'name' => 'about_cta_link',
                'type' => 'link',
                'instructions' => 'Choose where the CTA button should link to.',
                'required' => 0,
                'return_format' => 'array',
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

    // Featured Section Fields for Front Page
    acf_add_local_field_group(array(
        'key' => 'group_featured_section',
        'title' => 'Featured Section',
        'fields' => array(
            array(
                'key' => 'field_featured_project_1',
                'label' => 'Featured Project 1',
                'name' => 'featured_project_1',
                'type' => 'post_object',
                'instructions' => 'Select the first featured project',
                'required' => 0,
                'post_type' => array('project'),
                'return_format' => 'object',
                'allow_null' => 1,
            ),
            array(
                'key' => 'field_featured_project_2',
                'label' => 'Featured Project 2',
                'name' => 'featured_project_2',
                'type' => 'post_object',
                'instructions' => 'Select the second featured project',
                'required' => 0,
                'post_type' => array('project'),
                'return_format' => 'object',
                'allow_null' => 1,
            ),
            array(
                'key' => 'field_featured_project_3',
                'label' => 'Featured Project 3',
                'name' => 'featured_project_3',
                'type' => 'post_object',
                'instructions' => 'Select the third featured project',
                'required' => 0,
                'post_type' => array('project'),
                'return_format' => 'object',
                'allow_null' => 1,
            ),
            array(
                'key' => 'field_instagram_embed',
                'label' => 'Instagram Embed Code',
                'name' => 'instagram_embed',
                'type' => 'wysiwyg',
                'instructions' => 'Paste your Smash Balloon Instagram embed shortcode or code here',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
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
        'menu_order' => 2,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => 'Featured section fields for the front page',
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

    // Project Details Field Group
    acf_add_local_field_group(array(
        'key' => 'group_project_details',
        'title' => 'Project Details',
        'fields' => array(
            array(
                'key' => 'field_project_description',
                'label' => 'Project Description',
                'name' => 'project_description',
                'type' => 'wysiwyg',
                'instructions' => 'Detailed description of the project, photography approach, concept, etc.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'delay' => 0,
            ),
            array(
                'key' => 'field_project_location',
                'label' => 'Location',
                'name' => 'project_location',
                'type' => 'text',
                'instructions' => 'Where was this project shot? (e.g., "Brussels, Belgium")',
                'required' => 0,
                'placeholder' => 'Brussels, Belgium',
            ),
            array(
                'key' => 'field_project_people',
                'label' => 'People Involved',
                'name' => 'project_people',
                'type' => 'text',
                'instructions' => 'Credits for people involved (e.g., models, assistants, clients)',
                'required' => 0,
                'placeholder' => 'Model: John Doe, Assistant: Jane Smith',
            ),
            array(
                'key' => 'field_project_instagram_link',
                'label' => 'Instagram Post Link',
                'name' => 'project_instagram_link',
                'type' => 'url',
                'instructions' => 'Link to the Instagram post for this project (if applicable)',
                'required' => 0,
                'placeholder' => 'https://www.instagram.com/p/...',
            ),
            array(
                'key' => 'field_project_border_radius',
                'label' => 'Image Border Radius',
                'name' => 'project_border_radius',
                'type' => 'number',
                'instructions' => 'Border radius for project images in pixels (0 = square corners, 8 = default rounded, 50 = very rounded)',
                'required' => 0,
                'default_value' => 8,
                'min' => 0,
                'max' => 50,
                'step' => 1,
                'append' => 'px',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'project',
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
        'description' => 'Fields for project posts',
    ));
}

// ===================================
// PRIVATE PROJECT GALLERY SYSTEM
// ===================================

/**
 * Creates the "Private" term in project_type taxonomy.
 * Called on theme activation/init.
 *
 * @return void
 */
function cedricph_create_private_project_type(): void {
    // Check if "private" term already exists
    if (!term_exists('private', 'project_type')) {
        wp_insert_term(
            'Private',
            'project_type',
            array(
                'slug' => 'private',
                'description' => 'Private projects for customer delivery only',
            )
        );
    }
}
add_action('init', 'cedricph_create_private_project_type', 11);

/**
 * Generates a cryptographically secure access token for a private project.
 * Token expires 30 days from creation by default.
 *
 * @param int $post_id The project post ID.
 * @return string|false The generated token, or false on failure.
 */
function cedricph_generate_access_token(int $post_id) {
    if (get_post_type($post_id) !== 'project') {
        return false;
    }

    // Generate 32-character cryptographically secure token
    $token = bin2hex(random_bytes(16));

    // Hash token for validation (timing-attack resistant)
    $hashed_token = wp_hash_password($token);

    // Set expiration date (30 days from now)
    $expires = time() + (30 * DAY_IN_SECONDS);

    // Store token data
    // Store hashed version for validation
    update_post_meta($post_id, '_private_access_token', $hashed_token);
    // Store plain version for link generation (trade-off: usability vs security)
    update_post_meta($post_id, '_private_access_token_plain', $token);
    update_post_meta($post_id, '_private_token_created', time());
    update_post_meta($post_id, '_private_token_expires', $expires);

    // Return plain token
    return $token;
}

/**
 * Validates an access token for a private project.
 * Checks token match and expiration date.
 *
 * @param int $post_id The project post ID.
 * @param string $token The token to validate.
 * @return bool|WP_Error True if valid, WP_Error on failure.
 */
function cedricph_validate_access_token(int $post_id, string $token) {
    if (empty($token)) {
        return new WP_Error('missing_token', __('Access token is required to view this private gallery.', 'cedricph'));
    }

    // Get stored hashed token
    $hashed_token = get_post_meta($post_id, '_private_access_token', true);

    if (empty($hashed_token)) {
        return new WP_Error('no_token_set', __('This project does not have an access token configured.', 'cedricph'));
    }

    // Verify token using timing-attack-safe comparison
    if (!wp_check_password($token, $hashed_token)) {
        return new WP_Error('invalid_token', __('Invalid access token. Please check your link and try again.', 'cedricph'));
    }

    // Check expiration
    $expires = get_post_meta($post_id, '_private_token_expires', true);

    if (!empty($expires) && time() > intval($expires)) {
        return new WP_Error('expired_token', __('This access link has expired. Please request a new link from the photographer.', 'cedricph'));
    }

    return true;
}

/**
 * Checks if a project is marked as private.
 *
 * @param int $post_id The project post ID.
 * @return bool True if private, false otherwise.
 */
function cedricph_is_private_project(int $post_id): bool {
    if (get_post_type($post_id) !== 'project') {
        return false;
    }

    return has_term('private', 'project_type', $post_id);
}

/**
 * Generates the full shareable link for a private project.
 *
 * @param int $post_id The project post ID.
 * @return string|false The shareable URL with token, or false on failure.
 */
function cedricph_get_private_project_link(int $post_id) {
    if (!cedricph_is_private_project($post_id)) {
        return false;
    }

    // Get the token (need to retrieve plain text from meta - stored during generation)
    $plain_token = get_post_meta($post_id, '_private_access_token_plain', true);

    if (empty($plain_token)) {
        return false;
    }

    $permalink = get_permalink($post_id);

    if (!$permalink) {
        return false;
    }

    return add_query_arg('access_token', $plain_token, $permalink);
}

/**
 * Regenerates the access token for a private project.
 * Invalidates the old token.
 *
 * @param int $post_id The project post ID.
 * @return string|false The new token, or false on failure.
 */
function cedricph_regenerate_access_token(int $post_id) {
    if (!cedricph_is_private_project($post_id)) {
        return false;
    }

    // Delete old token data
    delete_post_meta($post_id, '_private_access_token');
    delete_post_meta($post_id, '_private_access_token_plain');
    delete_post_meta($post_id, '_private_token_created');
    delete_post_meta($post_id, '_private_token_expires');

    // Generate new token
    return cedricph_generate_access_token($post_id);
}

/**
 * Excludes private projects from public archive queries.
 *
 * @param WP_Query $query The WordPress query object.
 * @return void
 */
function cedricph_exclude_private_from_archives(WP_Query $query): void {
    // Only modify public-facing queries, not admin
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Only modify project queries
    if ($query->get('post_type') !== 'project') {
        return;
    }

    // Get existing tax_query
    $tax_query = $query->get('tax_query');

    if (!is_array($tax_query)) {
        $tax_query = array();
    }

    // Add exclusion for private projects
    $tax_query[] = array(
        'taxonomy' => 'project_type',
        'field'    => 'slug',
        'terms'    => 'private',
        'operator' => 'NOT IN',
    );

    $query->set('tax_query', $tax_query);
}
add_action('pre_get_posts', 'cedricph_exclude_private_from_archives');

/**
 * Registers the private project meta box in the admin.
 *
 * @return void
 */
function cedricph_add_private_project_meta_box(): void {
    add_meta_box(
        'cedricph_private_project_meta_box',
        __('Private Gallery Settings', 'cedricph'),
        'cedricph_render_private_project_meta_box',
        'project',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'cedricph_add_private_project_meta_box');

/**
 * Renders the private project meta box.
 *
 * @param WP_Post $post The current post object.
 * @return void
 */
function cedricph_render_private_project_meta_box(WP_Post $post): void {
    // Add nonce for security
    wp_nonce_field('cedricph_save_private_project_settings', 'cedricph_private_project_nonce');

    $is_private = cedricph_is_private_project($post->ID);
    $token = get_post_meta($post->ID, '_private_access_token_plain', true);
    $expires = get_post_meta($post->ID, '_private_token_expires', true);
    $created = get_post_meta($post->ID, '_private_token_created', true);

    ?>
    <div class="cedricph-private-project-settings">
        <?php if (!$is_private): ?>
            <p class="description">
                <?php esc_html_e('Mark this project as "Private" in the Project Type taxonomy to enable private gallery features.', 'cedricph'); ?>
            </p>
        <?php else: ?>
            <?php if (empty($token)): ?>
                <p class="description">
                    <?php esc_html_e('Generate an access token to create a shareable private link for this gallery.', 'cedricph'); ?>
                </p>
                <p>
                    <button type="button" id="cedricph-generate-token" class="button button-primary">
                        <?php esc_html_e('Generate Access Token', 'cedricph'); ?>
                    </button>
                </p>
            <?php else: ?>
                <div class="cedricph-token-info">
                    <p><strong><?php esc_html_e('Shareable Link:', 'cedricph'); ?></strong></p>
                    <div class="cedricph-token-display">
                        <input type="text" readonly value="<?php echo esc_attr(cedricph_get_private_project_link($post->ID)); ?>" id="cedricph-private-link" style="width: 100%; margin-bottom: 8px;" />
                        <button type="button" id="cedricph-copy-link" class="button button-secondary" style="width: 100%; margin-bottom: 8px;">
                            <?php esc_html_e('Copy Link', 'cedricph'); ?>
                        </button>
                    </div>

                    <?php if ($created): ?>
                        <p class="description">
                            <?php
                            /* translators: %s: date the token was created */
                            printf(esc_html__('Created: %s', 'cedricph'), esc_html(date_i18n(get_option('date_format'), $created)));
                            ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($expires): ?>
                        <p class="description" style="<?php echo time() > $expires ? 'color: #d63638;' : ''; ?>">
                            <?php
                            /* translators: %s: date the token expires */
                            printf(
                                esc_html__('Expires: %s', 'cedricph'),
                                esc_html(date_i18n(get_option('date_format'), $expires))
                            );
                            ?>
                            <?php if (time() > $expires): ?>
                                <strong><?php esc_html_e('(Expired)', 'cedricph'); ?></strong>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <p style="margin-top: 12px;">
                        <label for="cedricph-custom-expiration">
                            <?php esc_html_e('Custom Expiration Date:', 'cedricph'); ?>
                        </label>
                        <input type="date"
                               id="cedricph-custom-expiration"
                               name="cedricph_custom_expiration"
                               value="<?php echo $expires ? esc_attr(date('Y-m-d', $expires)) : ''; ?>"
                               min="<?php echo esc_attr(date('Y-m-d')); ?>"
                               style="width: 100%;" />
                        <span class="description">
                            <?php esc_html_e('Leave empty for 30-day default', 'cedricph'); ?>
                        </span>
                    </p>

                    <p style="margin-top: 12px;">
                        <button type="button" id="cedricph-regenerate-token" class="button">
                            <?php esc_html_e('Regenerate Token', 'cedricph'); ?>
                        </button>
                        <span class="description" style="display: block; margin-top: 4px;">
                            <?php esc_html_e('This will invalidate the old link', 'cedricph'); ?>
                        </span>
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div id="cedricph-token-message" style="display: none; margin-top: 12px;"></div>
    </div>

    <style>
        .cedricph-token-display input[readonly] {
            background: #f6f7f7;
            font-family: monospace;
            font-size: 11px;
            padding: 6px;
        }
        #cedricph-token-message {
            padding: 8px 12px;
            border-left: 4px solid #00a32a;
            background: #f0f6fc;
        }
        #cedricph-token-message.error {
            border-left-color: #d63638;
            background: #fcf0f1;
        }
    </style>
    <?php
}

/**
 * Saves the private project settings from the meta box.
 *
 * @param int $post_id The post ID.
 * @return void
 */
function cedricph_save_private_project_settings(int $post_id): void {
    // Check nonce
    if (!isset($_POST['cedricph_private_project_nonce']) ||
        !wp_verify_nonce($_POST['cedricph_private_project_nonce'], 'cedricph_save_private_project_settings')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save custom expiration date if provided
    if (isset($_POST['cedricph_custom_expiration']) && !empty($_POST['cedricph_custom_expiration'])) {
        $custom_date = sanitize_text_field($_POST['cedricph_custom_expiration']);
        $expires_timestamp = strtotime($custom_date . ' 23:59:59');

        if ($expires_timestamp !== false && $expires_timestamp > time()) {
            update_post_meta($post_id, '_private_token_expires', $expires_timestamp);
        }
    }
}
add_action('save_post_project', 'cedricph_save_private_project_settings');

/**
 * AJAX handler for generating/regenerating access token.
 *
 * @return void
 */
function cedricph_ajax_generate_token(): void {
    // Check nonce
    check_ajax_referer('cedricph_generate_token', 'nonce');

    // Get post ID
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (!$post_id || get_post_type($post_id) !== 'project') {
        wp_send_json_error(array('message' => __('Invalid project ID.', 'cedricph')));
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(array('message' => __('You do not have permission to edit this project.', 'cedricph')));
    }

    // Check if project is private
    if (!cedricph_is_private_project($post_id)) {
        wp_send_json_error(array('message' => __('Project must be marked as Private first.', 'cedricph')));
    }

    // Check if regenerating or generating new
    $is_regenerate = isset($_POST['regenerate']) && $_POST['regenerate'] === 'true';

    if ($is_regenerate) {
        $token = cedricph_regenerate_access_token($post_id);
    } else {
        $token = cedricph_generate_access_token($post_id);
    }

    if (!$token) {
        wp_send_json_error(array('message' => __('Failed to generate token.', 'cedricph')));
    }

    $link = cedricph_get_private_project_link($post_id);
    $expires = get_post_meta($post_id, '_private_token_expires', true);

    wp_send_json_success(array(
        'message' => $is_regenerate ? __('Token regenerated successfully!', 'cedricph') : __('Token generated successfully!', 'cedricph'),
        'token' => $token,
        'link' => $link,
        'expires' => $expires ? date_i18n(get_option('date_format'), $expires) : '',
    ));
}
add_action('wp_ajax_cedricph_generate_token', 'cedricph_ajax_generate_token');

/**
 * AJAX handler for downloading a single image from a private project.
 *
 * @return void
 */
function cedricph_download_single_image(): void {
    // Get parameters
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    $image_id = isset($_GET['image_id']) ? intval($_GET['image_id']) : 0;
    $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

    // Validate project
    if (!$post_id || get_post_type($post_id) !== 'project') {
        wp_die(__('Invalid project.', 'cedricph'), 403);
    }

    // Validate token
    $validation = cedricph_validate_access_token($post_id, $token);
    if (is_wp_error($validation)) {
        wp_die($validation->get_error_message(), 403);
    }

    // Validate image exists and get file path
    $file_path = get_attached_file($image_id);
    if (!$file_path || !file_exists($file_path)) {
        wp_die(__('Image not found.', 'cedricph'), 404);
    }

    // Verify MIME type
    $mime_type = wp_check_filetype($file_path);
    if (!str_starts_with($mime_type['type'], 'image/')) {
        wp_die(__('Invalid file type.', 'cedricph'), 403);
    }

    // Get original filename
    $filename = basename($file_path);

    // Set headers for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    // Clear output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Read and output file
    readfile($file_path);
    exit;
}
add_action('wp_ajax_nopriv_cedricph_download_image', 'cedricph_download_single_image');
add_action('wp_ajax_cedricph_download_image', 'cedricph_download_single_image');

/**
 * AJAX handler for downloading all gallery images as ZIP.
 *
 * @return void
 */
function cedricph_download_gallery_zip(): void {
    // Get parameters
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

    // Validate project
    if (!$post_id || get_post_type($post_id) !== 'project') {
        wp_die(__('Invalid project.', 'cedricph'), 403);
    }

    // Validate token
    $validation = cedricph_validate_access_token($post_id, $token);
    if (is_wp_error($validation)) {
        wp_die($validation->get_error_message(), 403);
    }

    // Check if ZipArchive is available
    if (!class_exists('ZipArchive')) {
        wp_die(__('ZIP functionality is not available on this server.', 'cedricph'), 500);
    }

    // Get gallery images
    $gallery = cedricph_get_project_gallery_images($post_id);

    if (empty($gallery)) {
        wp_die(__('No images found in this gallery.', 'cedricph'), 404);
    }

    // Create temporary ZIP file
    $upload_dir = wp_upload_dir();
    $temp_zip = $upload_dir['basedir'] . '/private-gallery-' . $post_id . '-' . time() . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($temp_zip, ZipArchive::CREATE) !== true) {
        wp_die(__('Failed to create ZIP file.', 'cedricph'), 500);
    }

    // Add each image to ZIP
    $counter = 1;
    foreach ($gallery as $image) {
        // Get attachment ID from URL
        $attachment_id = attachment_url_to_postid($image['url']);

        if ($attachment_id) {
            $file_path = get_attached_file($attachment_id);

            if ($file_path && file_exists($file_path)) {
                $extension = pathinfo($file_path, PATHINFO_EXTENSION);
                $zip_filename = sprintf('image-%03d.%s', $counter, $extension);
                $zip->addFile($file_path, $zip_filename);
                $counter++;
            }
        }
    }

    $zip->close();

    // Check if ZIP was created successfully
    if (!file_exists($temp_zip)) {
        wp_die(__('Failed to create ZIP file.', 'cedricph'), 500);
    }

    // Set headers for download
    $project_title = get_the_title($post_id);
    $safe_title = sanitize_file_name($project_title);
    $filename = $safe_title . '-gallery.zip';

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($temp_zip));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    // Clear output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Read and output file
    readfile($temp_zip);

    // Delete temporary file
    unlink($temp_zip);

    exit;
}
add_action('wp_ajax_nopriv_cedricph_download_gallery', 'cedricph_download_gallery_zip');
add_action('wp_ajax_cedricph_download_gallery', 'cedricph_download_gallery_zip');

/**
 * Enqueue admin scripts for private project management.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function cedricph_enqueue_private_project_admin_scripts(string $hook): void {
    // Only load on project edit screen
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    global $post;
    if (!$post || get_post_type($post) !== 'project') {
        return;
    }

    // Enqueue admin JavaScript
    wp_enqueue_script(
        'cedricph-private-projects-admin',
        get_template_directory_uri() . '/assets/js/admin-private-projects.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize script with AJAX data
    wp_localize_script('cedricph-private-projects-admin', 'cedricphPrivateProject', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'post_id' => $post->ID,
        'nonce' => wp_create_nonce('cedricph_generate_token'),
        'strings' => array(
            'copied' => __('Link copied to clipboard!', 'cedricph'),
            'copy_failed' => __('Failed to copy. Please copy manually.', 'cedricph'),
            'confirm_regenerate' => __('Are you sure? This will invalidate the old link.', 'cedricph'),
        ),
    ));

    // Enqueue admin CSS
    wp_enqueue_style(
        'cedricph-private-projects-admin',
        get_template_directory_uri() . '/assets/css/admin-private-projects.css',
        array(),
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'cedricph_enqueue_private_project_admin_scripts');

/**
 * Register ACF field group for private project settings.
 * Only displayed when project has "Private" taxonomy term.
 *
 * @return void
 */
function cedricph_register_private_project_acf_fields(): void {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key' => 'group_private_project_settings',
        'title' => 'Private Gallery Download Settings',
        'fields' => array(
            array(
                'key' => 'field_private_enable_downloads',
                'label' => 'Enable Downloads',
                'name' => 'private_enable_downloads',
                'type' => 'true_false',
                'instructions' => 'Allow customers to download images from this private gallery',
                'required' => 0,
                'default_value' => 1,
                'ui' => 1,
                'ui_on_text' => 'Enabled',
                'ui_off_text' => 'Disabled',
            ),
            array(
                'key' => 'field_private_show_bulk_download',
                'label' => 'Show Bulk Download Button',
                'name' => 'private_show_bulk_download',
                'type' => 'true_false',
                'instructions' => 'Show button to download all images as a ZIP file',
                'required' => 0,
                'default_value' => 1,
                'ui' => 1,
                'ui_on_text' => 'Show',
                'ui_off_text' => 'Hide',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_private_enable_downloads',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_private_customer_email',
                'label' => 'Customer Email',
                'name' => 'private_customer_email',
                'type' => 'email',
                'instructions' => 'Optional: Email address of the customer for this gallery',
                'required' => 0,
                'placeholder' => 'customer@example.com',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'project',
                ),
                array(
                    'param' => 'post_taxonomy',
                    'operator' => '==',
                    'value' => 'project_type:private',
                ),
            ),
        ),
        'menu_order' => 1,
        'position' => 'side',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => 'Download settings for private galleries',
    ));
}
add_action('acf/init', 'cedricph_register_private_project_acf_fields');