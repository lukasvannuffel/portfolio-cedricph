<?php
/**
 * Single Project Template
 *
 * Displays an individual project with carousel gallery and metadata.
 * Must be used within the main WordPress loop (have_posts/the_post).
 */

// ACCESS CONTROL FOR PRIVATE PROJECTS
// Check if this project is private and validate access token
if (have_posts()) {
    the_post();
    $post_id = get_the_ID();
    $is_private = cedricph_is_private_project($post_id);

    if ($is_private) {
        // Get token from URL
        $token = isset($_GET['access_token']) ? sanitize_text_field($_GET['access_token']) : '';

        // Validate token
        $validation = cedricph_validate_access_token($post_id, $token);

        if (is_wp_error($validation)) {
            // Show access gate and exit
            get_template_part('template-parts/project-access-gate', null, array(
                'error' => $validation->get_error_message(),
                'post_id' => $post_id,
            ));
            exit;
        }
    }

    // Rewind posts so the loop can run normally
    rewind_posts();
}

get_header();

$back_link_svg = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M15 10H5M5 10L10 5M5 10L10 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

while (have_posts()) {
    the_post();

    $post_id = get_the_ID();
    $gallery = cedricph_get_project_gallery_images($post_id);
    $description = get_field('project_description');
    $location = get_field('project_location');
    $people = get_field('project_people');
    $instagram_link = get_field('project_instagram_link');
    $border_radius_raw = get_field('project_border_radius');
    $border_radius = ($border_radius_raw !== '' && $border_radius_raw !== null) ? (int) $border_radius_raw : 8;

    $project_types = get_the_terms($post_id, 'project_type');
    $project_type_name = '';
    if ($project_types && !is_wp_error($project_types)) {
        $project_type_name = $project_types[0]->name;
    }

    $back_url = '';
    $back_label = '';
    if ($project_type_name === 'Analog') {
        $back_url = cedricph_get_portfolio_page_url('analog');
        $back_label = __('Back to Analog', 'cedricph');
    } elseif ($project_type_name === 'Digital') {
        $back_url = cedricph_get_portfolio_page_url('digital');
        $back_label = __('Back to Digital', 'cedricph');
    }

    $featured_image = get_the_post_thumbnail_url($post_id, 'large');
    $gallery_count = count($gallery);
    ?>

    <main class="single-project">
        <article class="project-detail">
            <div class="container">
                <div class="project-back-nav">
                    <?php if ($back_url && $back_label): ?>
                        <a href="<?php echo esc_url($back_url); ?>" class="back-link">
                            <?php echo $back_link_svg; ?>
                            <span><?php echo esc_html($back_label); ?></span>
                        </a>
                    <?php endif; ?>
                </div>

                <header class="project-header">
                    <h1 class="project-title"><?php echo esc_html(get_the_title()); ?></h1>
                    <?php if ($project_type_name): ?>
                        <span class="project-type-badge"><?php echo esc_html($project_type_name); ?></span>
                    <?php endif; ?>
                </header>

                <div class="project-content-grid">
                    <div class="project-gallery-section">
                        <?php if ($gallery_count > 0): ?>
                            <div class="project-carousel" data-carousel style="border-radius: <?php echo esc_attr((string) $border_radius); ?>px;">
                                <div class="carousel-stage">
                                    <?php foreach ($gallery as $index => $image): ?>
                                        <div class="carousel-slide <?php echo esc_attr($index === 0 ? 'active' : ''); ?>" data-slide="<?php echo esc_attr((string) $index); ?>">
                                            <img
                                                src="<?php echo esc_url($image['url']); ?>"
                                                alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>"
                                                loading="<?php echo esc_attr($index === 0 ? 'eager' : 'lazy'); ?>"
                                            >
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($gallery_count > 1): ?>
                                    <div class="carousel-controls">
                                        <button class="carousel-btn carousel-prev" aria-label="<?php esc_attr_e('Previous image', 'cedricph'); ?>">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="carousel-btn carousel-next" aria-label="<?php esc_attr_e('Next image', 'cedricph'); ?>">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="carousel-indicators">
                                        <?php foreach ($gallery as $index => $image): ?>
                                            <button
                                                class="carousel-indicator <?php echo esc_attr($index === 0 ? 'active' : ''); ?>"
                                                data-slide-to="<?php echo esc_attr((string) $index); ?>"
                                                aria-label="<?php echo esc_attr(sprintf(__('Go to image %d', 'cedricph'), $index + 1)); ?>"
                                            ></button>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="carousel-counter">
                                        <span class="current-slide">1</span> / <span class="total-slides"><?php echo esc_html((string) $gallery_count); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($featured_image): ?>
                            <div class="project-single-image" style="border-radius: <?php echo esc_attr((string) $border_radius); ?>px;">
                                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="project-info-section">
                        <?php if ($description): ?>
                            <div class="project-description">
                                <h2 class="info-heading"><?php esc_html_e('About This Project', 'cedricph'); ?></h2>
                                <div class="info-content">
                                    <?php echo wp_kses_post($description); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="project-metadata">
                            <?php if ($location): ?>
                                <div class="metadata-item">
                                    <h3 class="metadata-label"><?php esc_html_e('Location', 'cedricph'); ?></h3>
                                    <p class="metadata-value"><?php echo esc_html($location); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($people): ?>
                                <div class="metadata-item">
                                    <h3 class="metadata-label"><?php esc_html_e('Credits', 'cedricph'); ?></h3>
                                    <p class="metadata-value"><?php echo esc_html($people); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($instagram_link): ?>
                                <div class="metadata-item">
                                    <a href="<?php echo esc_url($instagram_link); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary instagram-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                        </svg>
                                        <span><?php esc_html_e('View on Instagram', 'cedricph'); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php
                // Show download section for private projects
                if ($is_private && get_field('private_enable_downloads')) {
                    get_template_part('template-parts/project-download-section', null, array(
                        'post_id' => $post_id,
                        'token' => $token,
                        'gallery' => $gallery,
                    ));
                }
                ?>
            </div>
        </article>
    </main>
    <?php
}

get_footer();
?>