<?php
/**
 * Template Name: Digital Portfolio
 * Displays all digital photography projects in a masonry grid
 */

get_header();
?>

<main class="portfolio-archive digital-archive" role="main">
    <div class="container">
        <header class="archive-header">
            <h1 class="archive-title"><?php echo esc_html__('Digital Photography', 'cedricph'); ?></h1>
            <p class="archive-description"><?php echo esc_html__('Digital photography projects captured with modern equipment.', 'cedricph'); ?></p>
        </header>

        <?php get_template_part('template-parts/portfolio-filters'); ?>

        <div id="portfolio-grid" class="projects-grid masonry-grid">
            <?php
            get_template_part('template-parts/portfolio-grid', null, array(
                'term'  => 'digital',
                'label' => __('Digital', 'cedricph'),
            ));
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
