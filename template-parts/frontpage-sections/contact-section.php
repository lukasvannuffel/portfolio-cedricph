<?php
/**
 * Contact Section Template
 * Displays a contact form section with customizable description text
 */

// Get ACF fields
$contact_description = get_field('contact_description');
?>

<section id="contact" class="contact-section">
    <div class="container">
        <div class="contact-content">
            <h2 class="contact-title">Let's work together</h2>
            
            <?php if ($contact_description): ?>
                <div class="contact-description">
                    <?php echo wp_kses_post($contact_description); ?>
                </div>
            <?php else: ?>
                <div class="contact-description">
                    <p>Interested in working together? Fill out the form below and I'll get back to you within 24 hours.</p>
                </div>
            <?php endif; ?>
            
            <div class="contact-form-container">
                <?php echo do_shortcode('[contact-form-7 id="7a66514" title="Contact"]'); ?>
            </div>
        </div>
    </div>
</section>
