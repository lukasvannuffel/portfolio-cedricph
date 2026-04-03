<?php
/**
 * Template Name: Analog Portfolio
 * Displays all analog photography projects in a masonry grid
 */

get_header();
?>

<main class="portfolio-archive analog-archive" role="main">
    <div class="container">
        <header class="archive-header">
            <h1 class="archive-title"><?php echo esc_html__('Analog Photography', 'cedricph'); ?></h1>
            <p class="archive-description"><?php echo esc_html__('Film photography projects captured on analog cameras.', 'cedricph'); ?></p>
        </header>

        <?php get_template_part('template-parts/portfolio-filters'); ?>

        <div id="portfolio-grid" class="projects-grid masonry-grid">
            <?php
            get_template_part('template-parts/portfolio-grid', null, array(
                'term'  => 'analog',
                'label' => __('Analog', 'cedricph'),
            ));
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
