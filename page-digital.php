<?php
/**
 * Template Name: Digital Portfolio
 * Displays all digital photography projects in a masonry grid
 */

get_header();
?>

<main class="portfolio-archive digital-archive">
    <div class="container">
        <!-- Page header -->
        <header class="archive-header">
            <h1 class="archive-title"><?php echo esc_html__('Digital Photography', 'cedricph'); ?></h1>
            <p class="archive-description"><?php echo esc_html__('Digital photography projects captured with modern equipment.', 'cedricph'); ?></p>
        </header>

        <!-- Projects grid -->
        <div class="projects-grid masonry-grid">
            <?php
            $args = array(
                'post_type'      => 'project',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'tax_query'      => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'project_type',
                        'field'    => 'slug',
                        'terms'    => 'digital',
                    ),
                    array(
                        'taxonomy' => 'project_type',
                        'field'    => 'slug',
                        'terms'    => 'private',
                        'operator' => 'NOT IN',
                    ),
                ),
            );

            $digital_query = new WP_Query($args);

            if (!$digital_query->have_posts()) {
                ?>
                <div class="no-projects">
                    <p><?php esc_html_e('No digital projects found yet. Check back soon!', 'cedricph'); ?></p>
                </div>
                <?php
            } else {
                while ($digital_query->have_posts()) {
                    $digital_query->the_post();
                    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
                    if (!$featured_image) {
                        $content = get_the_content();
                        preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*/i', $content, $match);
                        if (!empty($match[1])) {
                            $featured_image = $match[1];
                        }
                    }
                    ?>
                    <article class="project-card">
                        <a href="<?php the_permalink(); ?>" class="project-card-link">
                            <?php if ($featured_image): ?>
                                <img
                                    src="<?php echo esc_url($featured_image); ?>"
                                    alt="<?php the_title_attribute(); ?>"
                                    class="project-image"
                                    loading="lazy"
                                >
                            <?php endif; ?>
                            <div class="project-overlay">
                                <h3 class="project-title"><?php echo esc_html(get_the_title()); ?></h3>
                                <span class="project-category"><?php echo esc_html__('Digital', 'cedricph'); ?></span>
                            </div>
                        </a>
                    </article>
                    <?php
                }
                wp_reset_postdata();
            }
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
