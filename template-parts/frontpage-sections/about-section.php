<?php
/**
 * About Section Template
 *
 * Displays an about section with profile image and editable content.
 * ACF fields: about_profile_image, about_quote, about_text, about_based_in, about_focus,
 * about_specialization, about_experience, about_cta_text, about_cta_link.
 */

$profile_image = get_field('about_profile_image');
$about_quote = get_field('about_quote');
$about_text = get_field('about_text');
$based_in = get_field('about_based_in');
$focus = get_field('about_focus');
$specialization = get_field('about_specialization');
$experience = get_field('about_experience');
$cta_text = get_field('about_cta_text');
$cta_link = get_field('about_cta_link');
?>

<section id="about" class="about-section">
    <div class="container">
        <div class="about-content">
            <?php if ($profile_image): ?>
                <div class="about-image-container">
                    <div class="section-label">
                        <?php echo esc_html__('About me', 'cedricph'); ?>
                    </div>

                    <?php
                    $image_url = '';
                    $image_alt = __('Profile picture', 'cedricph');
                    if (is_array($profile_image)) {
                        $image_url = $profile_image['url'];
                        $image_alt = $profile_image['alt'] ?: $image_alt;
                    } elseif (is_numeric($profile_image)) {
                        $image_url = wp_get_attachment_image_url($profile_image, 'large');
                        $image_alt = get_post_meta($profile_image, '_wp_attachment_image_alt', true) ?: $image_alt;
                    } elseif (is_string($profile_image)) {
                        $image_url = $profile_image;
                    }
                    ?>
                    <div class="about-image-wrapper">
                        <img
                            src="<?php echo esc_url($image_url); ?>"
                            alt="<?php echo esc_attr($image_alt); ?>"
                            class="about-image"
                        >
                    </div>
                </div>
            <?php endif; ?>

            <div class="about-text">
                <?php if ($about_quote): ?>
                    <blockquote class="about-quote">
                        <span class="quote-mark">"</span>
                        <?php echo esc_html($about_quote); ?>
                    </blockquote>
                <?php endif; ?>

                <?php if ($about_text): ?>
                    <div class="about-description">
                        <?php echo wp_kses_post($about_text); ?>
                    </div>
                <?php endif; ?>

                <?php if ($based_in || $focus || $specialization || $experience): ?>
                    <div class="about-details">
                        <?php if ($based_in): ?>
                            <div class="detail-item">
                                <span class="detail-label"><?php echo esc_html__('Based In', 'cedricph'); ?></span>
                                <span class="detail-value"><?php echo esc_html($based_in); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($specialization): ?>
                            <div class="detail-item">
                                <span class="detail-label"><?php echo esc_html__('Specialization', 'cedricph'); ?></span>
                                <span class="detail-value"><?php echo esc_html($specialization); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($focus): ?>
                            <div class="detail-item">
                                <span class="detail-label"><?php echo esc_html__('Focus', 'cedricph'); ?></span>
                                <span class="detail-value"><?php echo esc_html($focus); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($experience): ?>
                            <div class="detail-item">
                                <span class="detail-label"><?php echo esc_html__('Experience', 'cedricph'); ?></span>
                                <span class="detail-value"><?php echo esc_html($experience); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (is_array($cta_link) && !empty($cta_link['url']) && $cta_text): ?>
                    <div class="about-cta">
                        <a
                            href="<?php echo esc_url($cta_link['url']); ?>"
                            class="about-cta-btn"
                            <?php if (!empty($cta_link['target'])): ?>target="<?php echo esc_attr($cta_link['target']); ?>"<?php endif; ?>
                        >
                            <?php echo esc_html($cta_text); ?>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
