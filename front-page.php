<?php get_header(); ?>

<main class="front-page" role="main">
    <?php
    get_template_part('template-parts/frontpage-sections/hero-section', 'hero');    
    get_template_part('template-parts/frontpage-sections/featured-section', 'featured');
    get_template_part('template-parts/frontpage-sections/about-section', 'about');
    get_template_part('template-parts/frontpage-sections/contact-section', 'contact');
    ?>
</main>

<?php get_footer(); ?>
