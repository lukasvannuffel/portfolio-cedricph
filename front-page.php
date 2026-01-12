<?php get_header(); ?>

<main class="front-page">
    <?php
    // Include hero section
    get_template_part('template-parts/frontpage-sections/hero-section', 'hero');
    
    // Include about section
    get_template_part('template-parts/frontpage-sections/about-section', 'about');
    
    // Include services section
    get_template_part('template-parts/frontpage-sections/previews-section', 'previews');
    
    // Include contact section
    get_template_part('template-parts/frontpage-sections/contact-section', 'contact');
    ?>
</main>

<?php get_footer(); ?>