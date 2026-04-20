<?php
/**
 * Theme Functions
 */

/**
 * Registers theme support and navigation menus.
 *
 * @return void
 */
function cedricph_setup(): void {
    // Title tag support
    add_theme_support('title-tag');
    
    // Featured images
    add_theme_support('post-thumbnails');
    
    // Custom logo (flex dimensions allow SVG without cropping)
    add_theme_support('custom-logo', array(
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
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
add_action('after_setup_theme', 'cedricph_setup');

/**
 * Disables the block editor for the front page so ACF fields and the Featured Gallery meta box render correctly.
 *
 * @param bool $use_block_editor Whether to use the block editor.
 * @param WP_Post $post The post being edited.
 * @return bool
 */
function cedricph_disable_gutenberg_for_front_page(bool $use_block_editor, WP_Post $post): bool {
    $front_page_id = (int) get_option('page_on_front');

    if ($front_page_id && $post->ID === $front_page_id) {
        return false;
    }

    return $use_block_editor;
}
add_filter('use_block_editor_for_post', 'cedricph_disable_gutenberg_for_front_page', 10, 2);

/**
 * Allows SVG uploads in the Media Library and Customizer (for custom logo).
 *
 * @param array<string, string> $mimes Allowed MIME types.
 * @return array<string, string>
 */
function cedricph_allow_svg_upload(array $mimes): array {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'cedricph_allow_svg_upload');

/**
 * Sanitizes SVG file content on upload (removes scripts and event handlers).
 *
 * @param string $content Raw SVG file content.
 * @return string|null Sanitized content or null if invalid/dangerous.
 */
function cedricph_sanitize_svg_content(string $content): ?string {
    if (trim($content) === '') {
        return null;
    }

    // Remove script elements and their content
    $content = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/iu', '', $content);
    if ($content === null) {
        return null;
    }

    // Remove javascript: and data: URLs in attributes
    $content = preg_replace('/\s*(href|xlink:href)\s*=\s*["\']?\s*javascript:[^"\']*["\']?/iu', '', $content);
    if ($content === null) {
        return null;
    }
    $content = preg_replace('/\s*(href|xlink:href)\s*=\s*["\']?\s*data:text\/html[^"\']*["\']?/iu', '', $content);
    if ($content === null) {
        return null;
    }

    // Remove event handler attributes (onload, onerror, onclick, etc.)
    $content = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/iu', '', $content);
    if ($content === null) {
        return null;
    }

    // Remove <foreignObject> and content (can embed HTML/scripts)
    $content = preg_replace('/<foreignObject\b[^>]*>[\s\S]*?<\/foreignObject>/iu', '', $content);
    if ($content === null) {
        return null;
    }

    return $content;
}

/**
 * Sanitizes SVG uploads before WordPress saves the file.
 *
 * @param array<string, mixed> $file Upload file data.
 * @return array<string, mixed>
 */
function cedricph_sanitize_svg_upload(array $file): array {
    if (!isset($file['type']) || $file['type'] !== 'image/svg+xml') {
        return $file;
    }

    if (!isset($file['tmp_name']) || !is_readable($file['tmp_name'])) {
        return $file;
    }

    $content = file_get_contents($file['tmp_name']);
    if ($content === false) {
        return $file;
    }

    $sanitized = cedricph_sanitize_svg_content($content);
    if ($sanitized === null) {
        $file['error'] = __('This SVG file could not be sanitized and was rejected for security.', 'cedricph');
        return $file;
    }

    if (file_put_contents($file['tmp_name'], $sanitized) === false) {
        $file['error'] = __('Failed to save sanitized SVG.', 'cedricph');
        return $file;
    }

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'cedricph_sanitize_svg_upload');

/**
 * Returns full URL and dimensions for SVG attachments so wp_get_attachment_image works.
 *
 * @param array|false $out      Short-circuit return.
 * @param int         $id       Attachment ID.
 * @param string|int  $size     Requested size.
 * @return array|false [ src, width, height ] or false.
 */
function cedricph_image_downsize_svg($out, int $id, $size) {
    if ($out !== false) {
        return $out;
    }

    $post = get_post($id);
    if (!$post || $post->post_mime_type !== 'image/svg+xml') {
        return false;
    }

    $src = wp_get_attachment_url($id);
    if (!$src) {
        return false;
    }

    $width  = 1;
    $height = 1;
    $meta   = wp_get_attachment_metadata($id);
    if (is_array($meta) && isset($meta['width'], $meta['height'])) {
        $width  = (int) $meta['width'];
        $height = (int) $meta['height'];
    }

    return array($src, $width, $height, false);
}
add_filter('image_downsize', 'cedricph_image_downsize_svg', 10, 3);

/**
 * Removes width/height from custom logo img when logo is SVG so CSS can size it.
 *
 * @param array<string, string> $attr   Image attributes.
 * @param int                   $logoId Attachment ID.
 * @param int                   $blogId Blog ID.
 * @return array<string, string>
 */
function cedricph_custom_logo_svg_attributes(array $attr, int $logoId, int $blogId = 0): array {
    $url = wp_get_attachment_url($logoId);
    if (!$url || strtolower((string) pathinfo($url, PATHINFO_EXTENSION)) !== 'svg') {
        return $attr;
    }

    unset($attr['width'], $attr['height']);
    return $attr;
}
add_filter('get_custom_logo_image_attributes', 'cedricph_custom_logo_svg_attributes', 10, 3);

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
 * Returns an array of items with 'url', 'alt' and optionally 'id' (attachment ID), with duplicates removed.
 *
 * @param int $postId Post ID (project).
 * @return array<int, array{url: string, alt: string, id?: int}>
 */
function cedricph_get_project_gallery_images(int $postId): array {
    static $cache = array();

    if (isset($cache[$postId])) {
        return $cache[$postId];
    }

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
                    'id'  => $attachment_id,
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
                            'id'  => $attachment_id,
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

    $cache[$postId] = $unique;

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
 * Creates default project type terms (analog, digital, private) on first run.
 *
 * @return void
 */
function cedricph_create_default_project_types(): void {
    if (get_option('cedricph_project_types_created')) {
        return;
    }

    if (!taxonomy_exists('project_type')) {
        return;
    }

    $terms = array(
        'analog'  => array('slug' => 'analog'),
        'digital' => array('slug' => 'digital'),
        'private' => array(
            'slug'        => 'private',
            'description' => 'Private projects for customer delivery only',
        ),
    );

    foreach ($terms as $name => $args) {
        if (!term_exists($name, 'project_type')) {
            wp_insert_term(ucfirst($name), 'project_type', $args);
        }
    }

    update_option('cedricph_project_types_created', true);
}
add_action('init', 'cedricph_create_default_project_types', 11);

/**
 * Enqueues theme styles and scripts.
 *
 * @return void
 */
function cedricph_scripts(): void {
    $main_css_path = get_template_directory() . '/assets/css/main.css';
    $main_js_path  = get_template_directory() . '/assets/js/main.js';

    wp_enqueue_style(
        'cedricph-typekit',
        'https://use.typekit.net/yeb4yjj.css',
        array(),
        null
    );

    // Main CSS (version = file mtime for cache busting when file changes)
    wp_enqueue_style(
        'main-style',
        get_template_directory_uri() . '/assets/css/main.css',
        array('cedricph-typekit'),
        file_exists($main_css_path) ? (string) filemtime($main_css_path) : '1.0.0'
    );

    // Main JS (version = file mtime for cache busting when file changes)
    wp_enqueue_script(
        'main-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        file_exists($main_js_path) ? (string) filemtime($main_js_path) : '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'cedricph_scripts');

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
function cedricph_nav_menu_link_attributes(array $atts, $item, stdClass $args): array {
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
add_filter('nav_menu_link_attributes', 'cedricph_nav_menu_link_attributes', 10, 3);

/**
 * Removes active classes from menu items that link to hash sections.
 * Active states for hash links are handled via JavaScript.
 *
 * @param string[]  $classes CSS classes applied to the menu item.
 * @param WP_Post   $item    Menu item object.
 * @param stdClass  $args    Nav menu arguments.
 * @return string[] Modified classes.
 */
function cedricph_nav_menu_css_class(array $classes, $item, stdClass $args): array {
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
add_filter('nav_menu_css_class', 'cedricph_nav_menu_css_class', 10, 3);

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
function cedricph_body_classes(array $classes): array {
    if (is_front_page()) {
        $classes[] = 'is-front-page';
    }
    return $classes;
}
add_filter('body_class', 'cedricph_body_classes');

/**
 * Returns the permalink for the Analog or Digital portfolio page (by template).
 * Use when building nav or back links so the correct page URL is used regardless of slug.
 *
 * @param string $type Either 'analog' or 'digital'.
 * @return string URL for the page, or home_url('/analog'|'/digital') if no page found.
 */
function cedricph_get_portfolio_page_url(string $type): string {
    static $cache = array();

    if (isset($cache[$type])) {
        return $cache[$type];
    }

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
        $cache[$type] = get_permalink($pages[0]->ID);

        return $cache[$type];
    }

    $cache[$type] = home_url('/' . $type);

    return $cache[$type];
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
                'key' => 'field_hero_logo',
                'label' => 'Hero Logo',
                'name' => 'hero_logo',
                'type' => 'image',
                'instructions' => 'Logo shown instead of the text title. Leave empty to use the title below.',
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
                'default_value' => 'Get in touch',
                'placeholder' => 'e.g., Get in touch',
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
                'key' => 'field_hero_secondary_cta_text',
                'label' => 'Secondary CTA Button Text',
                'name' => 'hero_secondary_cta_text',
                'type' => 'text',
                'instructions' => 'Enter the text for the secondary hero button',
                'required' => 0,
                'default_value' => 'View my work',
                'placeholder' => 'e.g., View my work',
            ),
            array(
                'key' => 'field_hero_secondary_cta_link',
                'label' => 'Secondary CTA Button Link',
                'name' => 'hero_secondary_cta_link',
                'type' => 'link',
                'instructions' => 'Select where the secondary hero button should link to',
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

    // Featured Section — Instagram embed (gallery is handled by cedricph_register_featured_gallery_metabox)
    acf_add_local_field_group(array(
        'key' => 'group_featured_section',
        'title' => 'Featured Section — Instagram',
        'fields' => array(
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
        'description' => 'Instagram embed for the featured section',
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

    acf_add_local_field_group(array(
        'key'    => 'group_project_categories',
        'title'  => 'Project Categories',
        'fields' => array(
            array(
                'key'           => 'field_project_categories',
                'label'         => 'Categories',
                'name'          => 'project_categories',
                'type'          => 'checkbox',
                'instructions'  => 'Select the categories that best describe this project. Used for portfolio filtering.',
                'required'      => 0,
                'choices'       => array(
                    'portrait'   => 'Portrait',
                    'events'     => 'Events',
                    'commercial' => 'Commercial',
                ),
                'allow_custom'  => 0,
                'layout'        => 'horizontal',
                'toggle'        => 0,
                'return_format' => 'value',
                'save_custom'   => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'project',
                ),
            ),
        ),
        'menu_order'            => 1,
        'position'              => 'side',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'description'           => 'Category tags for portfolio filtering',
    ));
}

// ===================================
// PRIVATE PROJECT GALLERY SYSTEM
// ===================================


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

    // Block downloads when the project-level toggle is disabled.
    if (!get_field('private_enable_downloads', $post_id)) {
        wp_die(__('Downloads are disabled for this gallery.', 'cedricph'), 403);
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

    $filename = sanitize_file_name(basename($file_path));

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

    // Block downloads when the project-level toggle is disabled.
    if (!get_field('private_enable_downloads', $post_id)) {
        wp_die(__('Downloads are disabled for this gallery.', 'cedricph'), 403);
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

// ===================================
// SEO, STRUCTURED DATA & META
// ===================================

/**
 * Returns social profile URLs for schema (sameAs). Defaults from footer; filterable.
 *
 * @return array<int, string>
 */
function cedricph_get_social_same_as_urls(): array {
    $urls = array(
        'https://www.instagram.com',
        'https://www.linkedin.com',
    );
    return array_values(array_filter((array) apply_filters('cedricph_social_same_as_urls', $urls)));
}

/**
 * Returns meta description for current request (max 160 chars, no tags).
 *
 * @return string
 */
function cedricph_get_meta_description(): string {
    $description = '';

    if (is_front_page()) {
        $subtitle = function_exists('get_field') ? get_field('hero_subtitle') : null;
        $description = is_string($subtitle) ? $subtitle : get_bloginfo('description', 'display');
    } elseif (is_singular('project')) {
        $desc = function_exists('get_field') ? get_field('project_description') : null;
        if (is_string($desc)) {
            $description = wp_strip_all_tags($desc);
        }
        if ($description === '') {
            $description = get_the_excerpt();
        }
    } elseif (is_page()) {
        $template = get_page_template_slug();
        if ($template === 'page-analog.php') {
            $description = __('Film photography projects captured on analog cameras.', 'cedricph');
        } elseif ($template === 'page-digital.php') {
            $description = __('Digital photography projects captured with modern equipment.', 'cedricph');
        } else {
            $description = get_the_excerpt();
        }
    } else {
        $description = get_bloginfo('description', 'display');
    }

    $description = wp_strip_all_tags($description);
    if (mb_strlen($description) > 160) {
        $description = mb_substr($description, 0, 157) . '...';
    }
    return $description;
}

/**
 * Returns URL for OG/Twitter image for current request.
 *
 * @return string
 */
function cedricph_get_og_image_url(): string {
    if (is_front_page()) {
        $hero = function_exists('get_field') ? get_field('hero_background_image') : null;
        if (is_array($hero) && !empty($hero['url'])) {
            return $hero['url'];
        }
        if (is_numeric($hero)) {
            $url = wp_get_attachment_image_url((int) $hero, 'full');
            return $url ?: '';
        }
        if (is_string($hero)) {
            return $hero;
        }
    }

    if (is_singular('project')) {
        $thumb = get_the_post_thumbnail_url(get_queried_object_id(), 'large');
        if ($thumb) {
            return $thumb;
        }
        $gallery = cedricph_get_project_gallery_images(get_queried_object_id());
        if (!empty($gallery)) {
            return $gallery[0]['url'];
        }
    }

    if (is_page()) {
        $template = get_page_template_slug();
        if ($template === 'page-analog.php' || $template === 'page-digital.php') {
            $args = array(
                'post_type'      => 'project',
                'posts_per_page' => 1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'project_type',
                        'field'    => 'slug',
                        'terms'    => $template === 'page-analog.php' ? 'analog' : 'digital',
                    ),
                    array(
                        'taxonomy' => 'project_type',
                        'field'    => 'slug',
                        'terms'    => 'private',
                        'operator' => 'NOT IN',
                    ),
                ),
            );
            $q = new WP_Query($args);
            if ($q->have_posts()) {
                $q->the_post();
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');
                wp_reset_postdata();
                if ($thumb) {
                    return $thumb;
                }
            }
        }
    }

    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $url = wp_get_attachment_image_url((int) $custom_logo_id, 'full');
        if ($url) {
            return $url;
        }
    }
    return '';
}

/**
 * Outputs JSON-LD structured data in wp_head based on current context.
 *
 * @return void
 */
function cedricph_output_structured_data(): void {
    $site_url = home_url('/');
    $site_name = get_bloginfo('name', 'display');
    $site_description = get_bloginfo('description', 'display');
    $locale = get_bloginfo('language');
    $logo_id = get_theme_mod('custom_logo');
    $logo_url = $logo_id ? wp_get_attachment_image_url((int) $logo_id, 'full') : '';

    $graphs = array();

    // WebSite (sitewide)
    $website = array(
        '@type'       => 'WebSite',
        '@id'         => $site_url . '#website',
        'url'         => $site_url,
        'name'        => $site_name,
        'description' => $site_description ?: null,
        'publisher'   => array(
            '@id' => $site_url . '#organization',
        ),
        'inLanguage'  => $locale ?: null,
    );
    if (has_nav_menu('primary')) {
        $website['potentialAction'] = array(
            '@type'  => 'SearchAction',
            'target' => array(
                '@type'       => 'EntryPoint',
                'urlTemplate' => $site_url . '?s={search_term_string}',
            ),
            'query-input' => 'required name=search_term_string',
        );
    }
    $graphs[] = $website;

    // Organization (sitewide)
    $same_as = cedricph_get_social_same_as_urls();
    $organization = array(
        '@type' => 'Organization',
        '@id'   => $site_url . '#organization',
        'name'  => $site_name,
        'url'   => $site_url,
        'logo'  => $logo_url ? array(
            '@type' => 'ImageObject',
            'url'   => $logo_url,
        ) : null,
    );
    if (!empty($same_as)) {
        $organization['sameAs'] = $same_as;
    }
    $graphs[] = $organization;

    // Context-specific schema
    if (is_front_page()) {
        $based_in = function_exists('get_field') ? get_field('about_based_in') : null;
        $photographer = array(
            '@type'          => 'ProfessionalService',
            'additionalType' => 'https://schema.org/Photographer',
            'name'           => $site_name,
            'url'            => $site_url,
            'description'    => $site_description ?: (function_exists('get_field') ? wp_strip_all_tags((string) get_field('hero_subtitle')) : null),
            'logo'           => $logo_url ? array('@type' => 'ImageObject', 'url' => $logo_url) : null,
        );
        if (is_string($based_in) && $based_in !== '') {
            $photographer['address'] = array(
                '@type' => 'PostalAddress',
                'addressLocality' => $based_in,
            );
        }
        if (!empty($same_as)) {
            $photographer['sameAs'] = $same_as;
        }
        $graphs[] = $photographer;
    }

    if (is_singular('project')) {
        $post_id = get_queried_object_id();
        if (function_exists('cedricph_is_private_project') && cedricph_is_private_project($post_id)) {
            $token = isset($_GET['access_token']) ? sanitize_text_field(wp_unslash($_GET['access_token'])) : '';
            $valid = function_exists('cedricph_validate_access_token') ? cedricph_validate_access_token($post_id, $token) : false;
            if (!is_bool($valid) || !$valid) {
                // Private without valid token: skip project schema
            } else {
                $graphs[] = cedricph_build_project_schema($post_id);
            }
        } else {
            $graphs[] = cedricph_build_project_schema($post_id);
        }
    }

    if (is_page()) {
        $template = get_page_template_slug();
        if ($template === 'page-analog.php') {
            $graphs[] = array(
                '@type'       => 'CollectionPage',
                'name'       => __('Analog Photography', 'cedricph'),
                'description' => __('Film photography projects captured on analog cameras.', 'cedricph'),
                'url'        => get_permalink(),
            );
        } elseif ($template === 'page-digital.php') {
            $graphs[] = array(
                '@type'       => 'CollectionPage',
                'name'       => __('Digital Photography', 'cedricph'),
                'description' => __('Digital photography projects captured with modern equipment.', 'cedricph'),
                'url'        => get_permalink(),
            );
        }
    }

    $json = array(
        '@context' => 'https://schema.org',
        '@graph'   => $graphs,
    );
    echo '<script type="application/ld+json">' . "\n" . wp_json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n" . '</script>' . "\n";
}

/**
 * Builds ImageGallery/CreativeWork schema for a project.
 *
 * @param int $postId Project post ID.
 * @return array<string, mixed>
 */
function cedricph_build_project_schema(int $postId): array {
    $title = get_the_title($postId);
    $url = get_permalink($postId);
    $desc = function_exists('get_field') ? get_field('project_description', $postId) : null;
    $description = is_string($desc) ? wp_strip_all_tags($desc) : '';
    if (mb_strlen($description) > 160) {
        $description = mb_substr($description, 0, 157) . '...';
    }
    $gallery = cedricph_get_project_gallery_images($postId);
    $images = array();
    foreach ($gallery as $img) {
        $images[] = array('@type' => 'ImageObject', 'url' => $img['url']);
    }
    $location = function_exists('get_field') ? get_field('project_location', $postId) : null;
    $schema = array(
        '@type'         => count($images) > 1 ? 'ImageGallery' : 'CreativeWork',
        'name'          => $title,
        'url'           => $url,
        'description'   => $description ?: null,
        'image'         => !empty($images) ? $images : null,
        'author'        => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo('name', 'display'),
            'url'   => home_url('/'),
        ),
        'datePublished' => get_the_date('c', $postId),
    );
    if (is_string($location) && $location !== '') {
        $schema['locationCreated'] = array(
            '@type' => 'Place',
            'name'  => $location,
        );
    }
    return $schema;
}

/**
 * Outputs SEO meta tags (OG, Twitter, canonical, description, robots) in wp_head.
 *
 * @return void
 */
function cedricph_output_seo_meta_tags(): void {
    if (is_front_page()) {
        $url = home_url('/');
    } else {
        $url = get_permalink();
    }
    if (!$url || $url === '') {
        $url = home_url(add_query_arg(array()));
    }
    $title = wp_get_document_title();
    $description = cedricph_get_meta_description();
    $og_image = cedricph_get_og_image_url();
    $site_name = get_bloginfo('name', 'display');
    $locale = get_bloginfo('language');

    $robots = 'index, follow';
    if (is_singular('project')) {
        $post_id = get_queried_object_id();
        if (function_exists('cedricph_is_private_project') && cedricph_is_private_project($post_id)) {
            $token = isset($_GET['access_token']) ? sanitize_text_field(wp_unslash($_GET['access_token'])) : '';
            $valid = function_exists('cedricph_validate_access_token') ? cedricph_validate_access_token($post_id, $token) : false;
            if (!is_bool($valid) || !$valid) {
                $robots = 'noindex, nofollow';
            }
        }
    }
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";

    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr(is_front_page() ? 'website' : 'article') . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr($locale) . '">' . "\n";
    if ($og_image !== '') {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
    }

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    if ($og_image !== '') {
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
    }
}

/**
 * Document title separator: em dash.
 *
 * @param string $separator Current separator.
 * @return string
 */
function cedricph_filter_document_title_separator(string $separator): string {
    return ' — ';
}

/**
 * Homepage title format: "Cedric Ph — Photographer" (or site name + tagline).
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function cedricph_filter_document_title_parts(array $parts): array {
    if (!empty($parts['tagline']) && (is_front_page() && is_home())) {
        $parts['title'] = get_bloginfo('name', 'display');
        $parts['tagline'] = get_bloginfo('description', 'display') ?: __('Photographer', 'cedricph');
    }
    return $parts;
}

/**
 * Removes generator and unnecessary head links.
 *
 * @return void
 */
function cedricph_remove_head_clutter(): void {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
}
add_action('init', 'cedricph_remove_head_clutter');
add_filter('document_title_separator', 'cedricph_filter_document_title_separator');
add_filter('document_title_parts', 'cedricph_filter_document_title_parts', 10, 1);
add_action('wp_head', 'cedricph_output_structured_data', 5);
add_action('wp_head', 'cedricph_output_seo_meta_tags', 6);

// ===================================
// PERFORMANCE & CORE WEB VITALS
// ===================================

/**
 * Preloads hero image on front page (with srcset/sizes when available).
 *
 * @return void
 */
function cedricph_output_frontpage_hero_preload(): void {
    if (!is_front_page()) {
        return;
    }
    $hero = function_exists('get_field') ? get_field('hero_background_image') : null;
    $url = '';
    $id = 0;
    if (is_array($hero) && !empty($hero['ID'])) {
        $id = (int) $hero['ID'];
        $url = !empty($hero['url']) ? $hero['url'] : wp_get_attachment_image_url($id, 'full');
    } elseif (is_numeric($hero)) {
        $id = (int) $hero;
        $url = wp_get_attachment_image_url($id, 'full');
    } elseif (is_array($hero) && !empty($hero['url'])) {
        $url = $hero['url'];
    } elseif (is_string($hero)) {
        $url = $hero;
    }
    if ($url === '') {
        return;
    }
    $atts = array(
        'rel'  => 'preload',
        'as'   => 'image',
        'href' => $url,
    );
    if ($id && function_exists('wp_get_attachment_image_srcset')) {
        $srcset = wp_get_attachment_image_srcset($id, 'full');
        $sizes = wp_get_attachment_image_sizes($id, 'full');
        if ($srcset) {
            $atts['imagesrcset'] = $srcset;
        }
        if ($sizes) {
            $atts['imagesizes'] = $sizes;
        }
    }
    $line = '<link ';
    foreach ($atts as $k => $v) {
        $line .= esc_attr($k) . '="' . esc_attr($v) . '" ';
    }
    echo trim($line) . ">\n";
}
add_action('wp_head', 'cedricph_output_frontpage_hero_preload', 3);

/**
 * Adds dns-prefetch and preconnect for external origins.
 *
 * @param array<int, array<string, string>> $urls URLs and attributes.
 * @param string                            $relationType Relation type.
 * @return array<int, array<string, string>>
 */
function cedricph_add_resource_hints(array $urls, string $relationType): array {
    if ($relationType === 'dns-prefetch') {
        $urls[] = array('href' => 'https://www.instagram.com');
        $urls[] = array('href' => 'https://www.linkedin.com');
        $urls[] = array('href' => 'https://fonts.googleapis.com');
        $urls[] = array('href' => 'https://fonts.gstatic.com');
    }
    if ($relationType === 'preconnect') {
        $urls[] = array(
            'href'        => 'https://fonts.googleapis.com',
            'crossorigin' => 'anonymous',
        );
        $urls[] = array(
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }
    return $urls;
}
add_filter('wp_resource_hints', 'cedricph_add_resource_hints', 10, 2);

/**
 * Adds loading="lazy", decoding="async" to attachment images; skip for above-the-fold context.
 *
 * @param array<string, string> $attr Image attributes.
 * @param WP_Post               $attachment Attachment post.
 * @param string|array<int,int>  $size Size.
 * @return array<string, string>
 */
function cedricph_optimize_attachment_image_attributes(array $attr, WP_Post $attachment, $size): array {
    $attr['decoding'] = 'async';
    if (empty($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'cedricph_optimize_attachment_image_attributes', 10, 3);

// ===================================
// SITEMAP & ROBOTS
// ===================================

/**
 * Excludes private projects from the WordPress sitemap.
 *
 * @param array<string, mixed> $args Query args for post type sitemap.
 * @param string               $postType Post type name.
 * @return array<string, mixed>
 */
function cedricph_exclude_private_projects_from_sitemaps(array $args, string $postType): array {
    if ($postType !== 'project') {
        return $args;
    }
    if (!isset($args['tax_query']) || !is_array($args['tax_query'])) {
        $args['tax_query'] = array();
    }
    $args['tax_query'][] = array(
        'taxonomy' => 'project_type',
        'field'    => 'slug',
        'terms'    => 'private',
        'operator' => 'NOT IN',
    );
    return $args;
}
add_filter('wp_sitemaps_posts_query_args', 'cedricph_exclude_private_projects_from_sitemaps', 10, 2);

/**
 * Ensures project post type is included in sitemaps (public CPTs are included by default).
 *
 * @param array<string, WP_Post_Type> $postTypes Post type objects for sitemap.
 * @return array<string, WP_Post_Type>
 */
function cedricph_ensure_project_in_sitemaps(array $postTypes): array {
    if (!isset($postTypes['project'])) {
        $obj = get_post_type_object('project');
        if ($obj) {
            $postTypes['project'] = $obj;
        }
    }
    return $postTypes;
}
add_filter('wp_sitemaps_post_types', 'cedricph_ensure_project_in_sitemaps');

// ===================================
// SECURITY HEADERS
// ===================================

/**
 * Sends security-related HTTP headers.
 *
 * @return void
 */
function cedricph_send_security_headers(): void {
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
add_action('send_headers', 'cedricph_send_security_headers');

// ===================================
// CONTACT FORM & EXTERNAL LINKS (REL)
// ===================================

/**
 * Adds aria-label to Contact Form 7 wrapper when present.
 *
 * @param string $output Shortcode output.
 * @param string $tag   Shortcode tag.
 * @return string
 */
function cedricph_wpcf7_form_aria_label(string $output, string $tag): string {
    if ($tag !== 'contact-form-7') {
        return $output;
    }
    if (strpos($output, 'aria-label=') !== false) {
        return $output;
    }
    return preg_replace('/<div\s+class="([^"]*wpcf7[^"]*)"/', '<div class="$1" aria-label="' . esc_attr__('Contact form', 'cedricph') . '"', $output, 1);
}
add_filter('do_shortcode_tag', 'cedricph_wpcf7_form_aria_label', 10, 2);

/**
 * Ensures external links in content have rel="noopener noreferrer".
 *
 * @param string $content Post content.
 * @return string
 */
function cedricph_rel_noopener_noreferrer_for_external(string $content): string {
    if (strpos($content, '<a ') === false) {
        return $content;
    }
    $home = home_url();
    return preg_replace_callback(
        '/<a\s+([^>]*href=["\']([^"\']+)["\'][^>]*)>/i',
        static function (array $m) use ($home): string {
            $full = $m[0];
            $url = $m[2];
            if (strpos($url, '#') === 0 || strpos($url, 'mailto:') === 0 || strpos($url, 'tel:') === 0) {
                return $full;
            }
            $is_external = (strpos($url, 'http') === 0 && strpos($url, $home) !== 0);
            if (!$is_external) {
                return $full;
            }
            if (preg_match('/\srel=["\']([^"\']*)["\']/i', $full, $relMatch)) {
                $rel = $relMatch[1];
                if (stripos($rel, 'noopener') !== false) {
                    return $full;
                }
                $newRel = trim($rel . ' noopener noreferrer');
                return preg_replace('/\srel=["\'][^"\']*["\']/i', ' rel="' . esc_attr($newRel) . '"', $full);
            }
            return str_replace('<a ', '<a rel="noopener noreferrer" ', $full);
        },
        $content
    );
}
add_filter('the_content', 'cedricph_rel_noopener_noreferrer_for_external', 20);

/**
 * Renders breadcrumb nav with BreadcrumbList schema.
 *
 * @param array<int, array{label: string, url: string}> $items Breadcrumb items (label, url).
 * @return void
 */
function cedricph_render_breadcrumb(array $items): void {
    if (empty($items)) {
        return;
    }
    $list = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => array(),
    );
    foreach ($items as $position => $item) {
        $list['itemListElement'][] = array(
            '@type'    => 'ListItem',
            'position' => $position + 1,
            'name'     => $item['label'],
            'item'     => $item['url'],
        );
    }
    ?>
    <nav class="breadcrumb-nav" aria-label="<?php esc_attr_e('Breadcrumb', 'cedricph'); ?>">
        <ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
            <?php foreach ($items as $position => $item): ?>
                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <?php if ($position < count($items) - 1): ?>
                        <a href="<?php echo esc_url($item['url']); ?>" itemprop="item"><span itemprop="name"><?php echo esc_html($item['label']); ?></span></a>
                        <meta itemprop="position" content="<?php echo esc_attr((string) ($position + 1)); ?>">
                    <?php else: ?>
                        <span itemprop="name"><?php echo esc_html($item['label']); ?></span>
                        <meta itemprop="position" content="<?php echo esc_attr((string) ($position + 1)); ?>">
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
        <script type="application/ld+json"><?php echo wp_json_encode($list); ?></script>
    </nav>
    <?php
}

/**
 * Returns adjacent project (prev/next) in same project_type.
 *
 * @param int  $postId Current project ID.
 * @param bool $next   True for next, false for previous.
 * @return WP_Post|null
 */
function cedricph_get_adjacent_project(int $postId, bool $next): ?WP_Post {
    $terms = get_the_terms($postId, 'project_type');
    if (!$terms || is_wp_error($terms)) {
        return null;
    }
    $term_ids = array();
    foreach ($terms as $t) {
        if ($t->slug !== 'private') {
            $term_ids[] = $t->term_id;
        }
    }
    if (empty($term_ids)) {
        return null;
    }
    $order = $next ? 'ASC' : 'DESC';
    $compare = $next ? '>' : '<';
    $current_post = get_post($postId);
    if (!$current_post) {
        return null;
    }
    $query = new WP_Query(array(
        'post_type'      => 'project',
        'post__not_in'   => array($postId),
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => $order,
        'date_query'     => array(
            array(
                $compare => $current_post->post_date,
            ),
        ),
        'tax_query'      => array(
            array(
                'taxonomy' => 'project_type',
                'field'   => 'term_id',
                'terms'   => $term_ids,
            ),
            array(
                'taxonomy' => 'project_type',
                'field'   => 'slug',
                'terms'   => 'private',
                'operator' => 'NOT IN',
            ),
        ),
    ));
    if (!$query->have_posts()) {
        return null;
    }
    $query->the_post();
    $post = get_post();
    wp_reset_postdata();
    return $post;
}

/* ==========================================================================
   Featured Gallery Meta Box (native WP — no ACF Pro required)
   ========================================================================== */

/**
 * Registers the Featured Gallery meta box on the front page editor.
 *
 * @return void
 */
function cedricph_register_featured_gallery_metabox(): void {
    $front_page_id = (int) get_option('page_on_front');

    if (!$front_page_id) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'page') {
        return;
    }

    global $post;
    if (!$post || (int) $post->ID !== $front_page_id) {
        return;
    }

    add_meta_box(
        'cedricph_featured_gallery',
        __('Featured Gallery', 'cedricph'),
        'cedricph_featured_gallery_render',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cedricph_register_featured_gallery_metabox');

/**
 * Renders the Featured Gallery meta box HTML.
 *
 * @param WP_Post $post The current post.
 * @return void
 */
function cedricph_featured_gallery_render(WP_Post $post): void {
    wp_nonce_field('cedricph_featured_gallery', 'cedricph_featured_gallery_nonce');

    $image_ids = get_post_meta($post->ID, '_cedricph_featured_gallery', true);
    $image_ids = is_array($image_ids) ? $image_ids : array();
    ?>
    <p class="description"><?php esc_html_e('Upload up to 15 images. Drag to reorder.', 'cedricph'); ?></p>

    <div id="cedricph-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin:12px 0;min-height:60px;">
        <?php foreach ($image_ids as $attachment_id):
            $thumb = wp_get_attachment_image_url((int) $attachment_id, 'thumbnail');
            if (!$thumb) {
                continue;
            }
            ?>
            <div class="cedricph-gallery-thumb" data-id="<?php echo esc_attr((string) $attachment_id); ?>" style="position:relative;width:100px;height:100px;border-radius:4px;overflow:hidden;cursor:grab;border:2px solid #ddd;">
                <img src="<?php echo esc_url($thumb); ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
                <button type="button" class="cedricph-gallery-remove" style="position:absolute;top:2px;right:2px;background:#d63638;color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:14px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;" aria-label="<?php esc_attr_e('Remove image', 'cedricph'); ?>">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>

    <input type="hidden" id="cedricph-gallery-ids" name="cedricph_featured_gallery" value="<?php echo esc_attr(implode(',', $image_ids)); ?>">

    <button type="button" id="cedricph-gallery-add" class="button button-primary">
        <?php esc_html_e('Add Images', 'cedricph'); ?>
    </button>
    <span id="cedricph-gallery-count" style="margin-left:12px;color:#666;">
        <?php printf(esc_html__('%d / 15 images', 'cedricph'), count($image_ids)); ?>
    </span>

    <script>
    (function($) {
        var MAX_IMAGES = 15;
        var $preview = $('#cedricph-gallery-preview');
        var $input   = $('#cedricph-gallery-ids');
        var $addBtn  = $('#cedricph-gallery-add');
        var $count   = $('#cedricph-gallery-count');

        function getIds() {
            var val = $input.val().trim();
            return val ? val.split(',').map(Number) : [];
        }

        function setIds(ids) {
            $input.val(ids.join(','));
            $count.text(ids.length + ' / ' + MAX_IMAGES + ' images');
        }

        /* Drag-to-reorder via jQuery UI Sortable (bundled with WP) */
        $preview.sortable({
            tolerance: 'pointer',
            cursor: 'grabbing',
            update: function() {
                var ids = [];
                $preview.children('.cedricph-gallery-thumb').each(function() {
                    ids.push($(this).data('id'));
                });
                setIds(ids);
            }
        });

        /* Remove image */
        $preview.on('click', '.cedricph-gallery-remove', function(e) {
            e.preventDefault();
            var $thumb = $(this).closest('.cedricph-gallery-thumb');
            var removeId = $thumb.data('id');
            $thumb.remove();
            var ids = getIds().filter(function(id) { return id !== removeId; });
            setIds(ids);
        });

        /* Add images via WP Media modal */
        $addBtn.on('click', function(e) {
            e.preventDefault();
            var currentIds = getIds();
            var remaining  = MAX_IMAGES - currentIds.length;

            if (remaining <= 0) {
                alert('<?php echo esc_js(__('Maximum 15 images reached. Remove some to add new ones.', 'cedricph')); ?>');
                return;
            }

            var frame = wp.media({
                title: '<?php echo esc_js(__('Select Featured Gallery Images', 'cedricph')); ?>',
                button: { text: '<?php echo esc_js(__('Add to Gallery', 'cedricph')); ?>' },
                library: { type: 'image' },
                multiple: true
            });

            frame.on('select', function() {
                var selection = frame.state().get('selection').toArray();
                var ids = getIds();

                selection.forEach(function(attachment) {
                    if (ids.length >= MAX_IMAGES) {
                        return;
                    }
                    var data = attachment.toJSON();
                    if (ids.indexOf(data.id) !== -1) {
                        return;
                    }
                    ids.push(data.id);
                    var thumb = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
                    $preview.append(
                        '<div class="cedricph-gallery-thumb" data-id="' + data.id + '" style="position:relative;width:100px;height:100px;border-radius:4px;overflow:hidden;cursor:grab;border:2px solid #ddd;">' +
                            '<img src="' + thumb + '" style="width:100%;height:100%;object-fit:cover;display:block;">' +
                            '<button type="button" class="cedricph-gallery-remove" style="position:absolute;top:2px;right:2px;background:#d63638;color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:14px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;" aria-label="Remove image">&times;</button>' +
                        '</div>'
                    );
                });

                setIds(ids);
            });

            frame.open();
        });
    })(jQuery);
    </script>
    <?php
}

/**
 * Saves the Featured Gallery meta box data.
 *
 * @param int $post_id The post ID being saved.
 * @return void
 */
function cedricph_featured_gallery_save(int $post_id): void {
    if (!isset($_POST['cedricph_featured_gallery_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['cedricph_featured_gallery_nonce'], 'cedricph_featured_gallery')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    $raw = isset($_POST['cedricph_featured_gallery']) ? sanitize_text_field($_POST['cedricph_featured_gallery']) : '';
    $ids = array_filter(array_map('intval', explode(',', $raw)));

    /* Enforce max 15 */
    $ids = array_slice($ids, 0, 15);

    update_post_meta($post_id, '_cedricph_featured_gallery', $ids);
}
add_action('save_post_page', 'cedricph_featured_gallery_save');

/**
 * Retrieves the featured gallery images for the front page.
 *
 * @return array<int, array{id: int, url: string, medium: string, alt: string}> Array of image data.
 */
function cedricph_get_featured_gallery(): array {
    $front_page_id = (int) get_option('page_on_front');

    if (!$front_page_id) {
        return array();
    }

    $ids = get_post_meta($front_page_id, '_cedricph_featured_gallery', true);

    if (!is_array($ids) || empty($ids)) {
        return array();
    }

    $images = array();
    foreach ($ids as $attachment_id) {
        $attachment_id = (int) $attachment_id;
        $full_url = wp_get_attachment_image_url($attachment_id, 'full');

        if (!$full_url) {
            continue;
        }

        $medium_url = wp_get_attachment_image_url($attachment_id, 'medium_large') ?: $full_url;
        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: '';

        $images[] = array(
            'id' => $attachment_id,
            'url' => $full_url,
            'medium' => $medium_url,
            'alt' => $alt,
        );
    }

    return $images;
}

/**
 * Enqueues WP media uploader scripts on the front page editor.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function cedricph_enqueue_gallery_admin_scripts(string $hook): void {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    global $post;
    $front_page_id = (int) get_option('page_on_front');

    if (!$post || (int) $post->ID !== $front_page_id) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts', 'cedricph_enqueue_gallery_admin_scripts');