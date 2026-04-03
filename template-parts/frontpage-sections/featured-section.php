<?php
/**
 * Featured Section Template
 *
 * Displays up to 15 gallery images in a mosaic grid with lightbox.
 * Gallery uses custom meta box (no ACF Pro required).
 * Instagram embed uses ACF free wysiwyg field.
 */

$gallery = cedricph_get_featured_gallery();
$instagram_embed = get_field('instagram_embed');
?>

<section id="featured" class="featured-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo esc_html__('Featured Work', 'cedricph'); ?></h2>
        </div>

        <?php if (!empty($gallery)): ?>
            <div class="featured-grid">
                <?php foreach ($gallery as $index => $image):
                    $medium_url = $image['medium'];
                    $full_url = $image['url'];
                    $alt = $image['alt'] ?: sprintf(__('Featured image %d', 'cedricph'), $index + 1);
                    ?>
                    <figure class="featured-grid__item">
                        <button
                            type="button"
                            class="featured-grid__trigger"
                            data-full-src="<?php echo esc_url($full_url); ?>"
                            data-index="<?php echo esc_attr((string) $index); ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('View %s full size', 'cedricph'), $alt)); ?>"
                        >
                            <img
                                src="<?php echo esc_url($medium_url); ?>"
                                alt="<?php echo esc_attr($alt); ?>"
                                class="featured-grid__img"
                                loading="lazy"
                                decoding="async"
                            >
                        </button>
                    </figure>
                <?php endforeach; ?>
            </div>

            <!-- Lightbox -->
            <div
                class="featured-lightbox"
                id="featuredLightbox"
                role="dialog"
                aria-modal="true"
                aria-label="<?php esc_attr_e('Image viewer', 'cedricph'); ?>"
                hidden
            >
                <div class="featured-lightbox__backdrop"></div>

                <button class="featured-lightbox__close" aria-label="<?php esc_attr_e('Close', 'cedricph'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>

                <button class="featured-lightbox__prev" aria-label="<?php esc_attr_e('Previous image', 'cedricph'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18L9 12L15 6"/>
                    </svg>
                </button>

                <button class="featured-lightbox__next" aria-label="<?php esc_attr_e('Next image', 'cedricph'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18L15 12L9 6"/>
                    </svg>
                </button>

                <img class="featured-lightbox__img" src="" alt="">

                <span class="featured-lightbox__counter">
                    <span class="featured-lightbox__current">1</span>
                    /
                    <span class="featured-lightbox__total"><?php echo esc_html((string) count($gallery)); ?></span>
                </span>
            </div>
        <?php endif; ?>

        <?php if ($instagram_embed): ?>
            <div class="instagram-section">
                <div class="instagram-header">
                    <h3 class="instagram-title"><?php echo esc_html__('Follow My Journey', 'cedricph'); ?></h3>
                    <p class="instagram-subtitle"><?php echo esc_html__('Daily inspiration and behind-the-scenes moments', 'cedricph'); ?></p>
                </div>
                <div class="instagram-embed">
                    <?php
                    // Output unfiltered: ACF admin-only content. Smash Balloon outputs <style> and other markup that wp_kses would strip, leaving raw CSS visible.
                    echo $instagram_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted ACF WYSIWYG embed code
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
