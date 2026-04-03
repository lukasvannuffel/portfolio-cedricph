<?php
/**
 * Template Part: Portfolio Grid
 *
 * Shared project grid loop for analog and digital portfolio pages.
 * Accepts 'term' (taxonomy slug) and 'label' (display name) via $args.
 *
 * @var string $args['term']  Taxonomy slug (e.g. 'analog' or 'digital').
 * @var string $args['label'] Display label (e.g. 'Analog' or 'Digital').
 */

$term = $args['term'] ?? '';
$label = $args['label'] ?? '';

if (empty($term)) {
    return;
}

$query_args = array(
    'post_type'      => 'project',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'tax_query'      => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'project_type',
            'field'    => 'slug',
            'terms'    => $term,
        ),
        array(
            'taxonomy' => 'project_type',
            'field'    => 'slug',
            'terms'    => 'private',
            'operator' => 'NOT IN',
        ),
    ),
);

$portfolio_query = new WP_Query($query_args);

if (!$portfolio_query->have_posts()) {
    ?>
    <div class="no-projects">
        <p><?php esc_html_e('No projects found yet. Check back soon!', 'cedricph'); ?></p>
    </div>
    <?php
} else {
    while ($portfolio_query->have_posts()) {
        $portfolio_query->the_post();
        $pid = get_the_ID();
        $thumb_id = get_post_thumbnail_id($pid);
        $featured_image = $thumb_id ? null : get_the_post_thumbnail_url($pid, 'large');

        if (!$featured_image && !$thumb_id) {
            $content = get_the_content();
            preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*/i', $content, $match);
            if (!empty($match[1])) {
                $featured_image = $match[1];
            }
        }

        $raw_categories = get_field('project_categories', $pid);
        $categories_attr = '';
        if (!empty($raw_categories) && is_array($raw_categories)) {
            $categories_attr = implode(' ', array_map('sanitize_html_class', $raw_categories));
        }
        ?>
        <article
            class="project-card"
            data-title="<?php echo esc_attr(strtolower(get_the_title())); ?>"
            data-categories="<?php echo esc_attr($categories_attr); ?>"
        >
            <a href="<?php the_permalink(); ?>" class="project-card-link">
                <?php if ($thumb_id): ?>
                    <?php echo wp_get_attachment_image($thumb_id, 'large', false, array(
                        'class' => 'project-image',
                        'loading' => 'lazy',
                        'decoding' => 'async',
                    )); ?>
                <?php elseif ($featured_image): ?>
                    <img
                        src="<?php echo esc_url($featured_image); ?>"
                        alt="<?php the_title_attribute(); ?>"
                        class="project-image"
                        loading="lazy"
                        decoding="async"
                    >
                <?php endif; ?>
                <div class="project-overlay">
                    <h3 class="project-title"><?php echo esc_html(get_the_title()); ?></h3>
                    <span class="project-category"><?php echo esc_html($label); ?></span>
                </div>
            </a>
        </article>
        <?php
    }
    wp_reset_postdata();
}
