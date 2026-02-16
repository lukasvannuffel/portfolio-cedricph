<?php
/**
 * Hero Section Template
 *
 * Displays a full-height hero section with background image, title, subtitle, and CTA button.
 * ACF fields: hero_background_image, hero_title, hero_subtitle, hero_cta_text, hero_cta_link, hero_show_scroll_indicator.
 */

$background_image = get_field('hero_background_image');
$title = get_field('hero_title') ?: __('Capturing Moments That Matter', 'cedricph');
$subtitle = get_field('hero_subtitle') ?: __('Event & portrait photography that tells your story through authentic, cinematic imagery', 'cedricph');
$cta_text = get_field('hero_cta_text') ?: __('View portfolio', 'cedricph');
$cta_link = get_field('hero_cta_link');
$show_scroll_indicator = get_field('hero_show_scroll_indicator');

if ($cta_link === null || $cta_link === false) {
    $cta_link = array(
        'url'    => '#portfolio',
        'title'  => __('View portfolio', 'cedricph'),
        'target' => '',
    );
}

$bg_image_url = '';
if ($background_image && is_array($background_image)) {
    $bg_image_url = $background_image['url'];
} elseif ($background_image && is_string($background_image)) {
    $bg_image_url = $background_image;
}

$cta_url = '#';
$cta_target = '';
if (is_array($cta_link)) {
    $cta_url = $cta_link['url'] ?: '#';
    $cta_target = $cta_link['target'] ? ' target="' . esc_attr($cta_link['target']) . '"' : '';
} elseif (is_string($cta_link)) {
    $cta_url = $cta_link;
}
?>

<section id="hero" class="hero-section" role="banner">
    <?php if ($bg_image_url): ?>
        <div class="hero-background">
            <img 
                src="<?php echo esc_url($bg_image_url); ?>" 
                alt="<?php echo esc_attr($title); ?>"
                class="hero-bg-image"
            >
            <div class="hero-overlay"></div>
        </div>
    <?php endif; ?>
    
    <div class="hero-content">
        <div class="container">
            <div class="hero-text">
                <?php if ($title): ?>
                    <h1 class="hero-title">
                        <?php echo esc_html($title); ?>
                    </h1>
                <?php endif; ?>
                
                <?php if ($subtitle): ?>
                    <p class="hero-subtitle">
                        <?php echo esc_html($subtitle); ?>
                    </p>
                <?php endif; ?>
                
                <?php if ($cta_text && $cta_url): ?>
                    <div class="hero-cta">
                        <a href="<?php echo esc_url($cta_url); ?>" class="btn btn-primary"<?php echo $cta_target; ?>>
                            <?php echo esc_html($cta_text); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($show_scroll_indicator): ?>
        <div class="hero-scroll-indicator" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    <?php endif; ?>
</section>
