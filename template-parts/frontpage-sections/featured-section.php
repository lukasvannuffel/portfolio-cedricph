<?php
/**
 * Featured Section Template
 *
 * Displays 3 featured projects and optional Instagram embed.
 * ACF fields: featured_project_1, featured_project_2, featured_project_3, instagram_embed.
 */

$project_1 = get_field('featured_project_1');
$project_2 = get_field('featured_project_2');
$project_3 = get_field('featured_project_3');
$instagram_embed = get_field('instagram_embed');
$featured_projects = array_filter(array($project_1, $project_2, $project_3));
?>

<section id="featured" class="featured-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo esc_html__('Featured Work', 'cedricph'); ?></h2>
        </div>

        <?php if (!empty($featured_projects)): ?>
            <div class="projects-grid">
                <?php foreach ($featured_projects as $project):
                    $project_id = $project->ID;
                    $project_title = get_the_title($project_id);
                    $project_permalink = get_permalink($project_id);
                    $project_type = get_field('project_type', $project_id);
                    $thumbnail_id = get_post_thumbnail_id($project_id);
                    $image_url = wp_get_attachment_image_url($thumbnail_id, 'large');
                    $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) ?: $project_title;
                    ?>
                    <article class="project-card">
                        <a href="<?php echo esc_url($project_permalink); ?>" class="project-card-link">
                            <?php if ($image_url): ?>
                                <img
                                    src="<?php echo esc_url($image_url); ?>"
                                    alt="<?php echo esc_attr($image_alt); ?>"
                                    class="project-image"
                                    loading="lazy"
                                >
                            <?php endif; ?>
                            <div class="project-overlay">
                                <h3 class="project-title"><?php echo esc_html($project_title); ?></h3>
                                <?php if ($project_type): ?>
                                    <span class="project-category"><?php echo esc_html($project_type); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
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
