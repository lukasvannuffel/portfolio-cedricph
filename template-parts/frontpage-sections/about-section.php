<?php
/**
 * About Section Template
 * Displays an about section with profile image and editable content
 */

// Get ACF fields
$profile_image = get_field('about_profile_image');
$about_text = get_field('about_text');
?>

<section id="about" class="about-section">
    <div class="container">
        <div class="about-content">
            <?php if ($profile_image): ?>
                <div class="about-image-wrapper">
                    <?php
                    $image_url = '';
                    $image_alt = 'Profile picture';
                    
                    if (is_array($profile_image)) {
                        $image_url = $profile_image['url'];
                        $image_alt = $profile_image['alt'] ? $profile_image['alt'] : 'Profile picture';
                    } elseif (is_numeric($profile_image)) {
                        $image_url = wp_get_attachment_image_url($profile_image, 'large');
                        $image_alt = get_post_meta($profile_image, '_wp_attachment_image_alt', true) ?: 'Profile picture';
                    } elseif (is_string($profile_image)) {
                        $image_url = $profile_image;
                    }
                    ?>
                    <div class="about-image">
                        <img 
                            src="<?php echo esc_url($image_url); ?>" 
                            alt="<?php echo esc_attr($image_alt); ?>"
                        >
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="about-text-wrapper">
                <h2 class="about-title">About me</h2>
                
                <?php if ($about_text): ?>
                    <div class="about-text">
                        <?php echo wp_kses_post($about_text); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
