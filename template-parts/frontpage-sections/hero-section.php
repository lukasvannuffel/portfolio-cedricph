<?php
/**
 * Hero Section Template
 *
 * Displays a full-height hero section with background image, title, subtitle, and CTA button.
 * ACF fields: hero_background_image, hero_logo, hero_title, hero_subtitle, hero_cta_text, hero_cta_link, hero_secondary_cta_text, hero_secondary_cta_link, hero_show_scroll_indicator.
 */

$background_image = get_field('hero_background_image');
$logo = get_field('hero_logo');
$title = get_field('hero_title') ?: __('Capturing Moments That Matter', 'cedricph');
$subtitle = get_field('hero_subtitle') ?: __('Event & portrait photography that tells your story through authentic, cinematic imagery', 'cedricph');
$cta_text = get_field('hero_cta_text') ?: __('Get in touch', 'cedricph');
$secondary_cta_text = get_field('hero_secondary_cta_text') ?: __('View my work', 'cedricph');
$secondary_cta_link = get_field('hero_secondary_cta_link');
$show_scroll_indicator = get_field('hero_show_scroll_indicator');

if ($secondary_cta_link === null || $secondary_cta_link === false) {
    $secondary_cta_link = array(
        'url'    => '#featured',
        'title'  => __('View my work', 'cedricph'),
        'target' => '',
    );
}

$bg_image_url = '';
$bg_image_id = 0;
if ($background_image && is_array($background_image)) {
    $bg_image_url = $background_image['url'] ?? '';
    $bg_image_id = isset($background_image['ID']) ? (int) $background_image['ID'] : 0;
} elseif (is_numeric($background_image)) {
    $bg_image_id = (int) $background_image;
    $bg_image_url = wp_get_attachment_image_url($bg_image_id, 'full');
} elseif ($background_image && is_string($background_image)) {
    $bg_image_url = $background_image;
}

$logo_url = '';
$logo_id = 0;
if ($logo && is_array($logo) && !empty($logo['url'])) {
    $logo_url = $logo['url'];
    $logo_id = isset($logo['ID']) ? (int) $logo['ID'] : 0;
} elseif ($logo && is_numeric($logo)) {
    $logo_id = (int) $logo;
    $logo_url = wp_get_attachment_image_url($logo_id, 'full');
} elseif ($logo && is_string($logo)) {
    $logo_url = $logo;
}

$hero_alt = $title ?: get_bloginfo('name', 'display');

$secondary_cta_url = '#featured';
$secondary_cta_target = '';
if (is_array($secondary_cta_link)) {
    $secondary_cta_url = $secondary_cta_link['url'] ?: '#featured';
    $secondary_cta_target = $secondary_cta_link['target'] ? ' target="' . esc_attr($secondary_cta_link['target']) . '"' : '';
} elseif (is_string($secondary_cta_link)) {
    $secondary_cta_url = $secondary_cta_link;
}

?>

<section id="hero" class="hero-section" role="banner">
    <?php if ($bg_image_url): ?>
        <div class="hero-background">
            <?php
            $hero_img_attrs = array(
                'class'          => 'hero-bg-image',
                'alt'            => $hero_alt,
                'loading'        => 'eager',
                'decoding'       => 'async',
                'fetchpriority'  => 'high',
            );
            if ($bg_image_id): ?>
                <?php echo wp_get_attachment_image($bg_image_id, 'full', false, $hero_img_attrs); ?>
            <?php else: ?>
                <img
                    src="<?php echo esc_url($bg_image_url); ?>"
                    alt="<?php echo esc_attr($hero_alt); ?>"
                    class="hero-bg-image"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >
            <?php endif; ?>
            <div class="hero-overlay"></div>
        </div>
    <?php endif; ?>
    
    <div class="hero-content">
        <div class="container">
            <div class="hero-text">
                <?php if ($logo_url): ?>
                    <h1 class="hero-title hero-title--logo">
                        <?php if ($logo_id): ?>
                            <?php echo wp_get_attachment_image($logo_id, 'full', false, array('class' => 'hero-logo', 'alt' => $hero_alt, 'loading' => 'eager', 'decoding' => 'async')); ?>
                        <?php else: ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($hero_alt); ?>" class="hero-logo" loading="eager" decoding="async">
                        <?php endif; ?>
                    </h1>
                <?php endif; ?>
                
                <?php if ($subtitle): ?>
                    <p class="hero-subtitle">
                        <?php echo esc_html($subtitle); ?>
                    </p>
                <?php endif; ?>
                
                <div class="hero-cta">
                    <?php if ($cta_text): ?>
                        <a
                            href="#contact"
                            class="btn btn--filled"
                            aria-label="<?php echo esc_attr($cta_text); ?> — <?php esc_attr_e('open contact section', 'cedricph'); ?>"
                        >
                            <?php echo esc_html($cta_text); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($secondary_cta_text): ?>
                        <a
                            href="<?php echo esc_url($secondary_cta_url); ?>"
                            class="btn btn-primary btn--secondary"
                            aria-label="<?php echo esc_attr($secondary_cta_text); ?> — <?php esc_attr_e('open featured work section', 'cedricph'); ?>"
                            <?php echo $secondary_cta_target; ?>
                        >
                            <?php echo esc_html($secondary_cta_text); ?>
                        </a>
                    <?php endif; ?>
                </div>
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
