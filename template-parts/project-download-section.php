<?php
/**
 * Template Part: Project Download Section
 *
 * Displays download options for private project galleries.
 * Shows bulk ZIP download and optional individual image downloads.
 *
 * Available variables from get_template_part args:
 * @var int $post_id The project post ID
 * @var string $token The access token
 * @var array $gallery Array of gallery images
 */

$post_id = $args['post_id'] ?? 0;
$token = $args['token'] ?? '';
$gallery = $args['gallery'] ?? array();

if (empty($gallery) || empty($token)) {
    return;
}

$show_bulk_download = get_field('private_show_bulk_download') !== false;
$gallery_count = count($gallery);

// Generate download URLs
$bulk_download_url = add_query_arg(array(
    'action' => 'cedricph_download_gallery',
    'post_id' => $post_id,
    'token' => $token,
), admin_url('admin-ajax.php'));

?>

<div class="private-download-section">
    <div class="download-header">
        <h3 class="download-title">
            <svg class="download-icon" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            <?php esc_html_e('Download Gallery', 'cedricph'); ?>
        </h3>
        <p class="download-description">
            <?php
            /* translators: %d: number of images in the gallery */
            printf(
                esc_html(_n(
                    'Download %d photo from this gallery',
                    'Download %d photos from this gallery',
                    $gallery_count,
                    'cedricph'
                )),
                $gallery_count
            );
            ?>
        </p>
    </div>

    <div class="download-actions">
        <?php if ($show_bulk_download): ?>
            <div class="download-option download-bulk">
                <a href="<?php echo esc_url($bulk_download_url); ?>"
                   class="btn-download btn-download-primary"
                   id="bulk-download-btn"
                   data-post-id="<?php echo esc_attr($post_id); ?>">
                    <svg class="btn-download-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="btn-download-text">
                        <?php
                        /* translators: %d: number of images */
                        printf(esc_html__('Download All (%d images)', 'cedricph'), $gallery_count);
                        ?>
                    </span>
                </a>
                <p class="download-help">
                    <?php esc_html_e('Downloads all photos as a single ZIP file', 'cedricph'); ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="download-option download-individual">
            <button type="button"
                    class="btn-download btn-download-secondary"
                    id="toggle-individual-downloads">
                <svg class="btn-download-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="btn-download-text">
                    <?php esc_html_e('Download Individual Photos', 'cedricph'); ?>
                </span>
                <svg class="btn-download-chevron" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div class="individual-downloads-list" id="individual-downloads-list" style="display: none;">
                <p class="download-help" style="margin-bottom: 16px;">
                    <?php esc_html_e('Click on any photo below to download it individually:', 'cedricph'); ?>
                </p>
                <div class="individual-downloads-grid">
                    <?php
                    $counter = 1;
                    foreach ($gallery as $image):
                        $attachment_id = attachment_url_to_postid($image['url']);
                        if (!$attachment_id) {
                            continue;
                        }

                        $download_url = add_query_arg(array(
                            'action' => 'cedricph_download_image',
                            'post_id' => $post_id,
                            'image_id' => $attachment_id,
                            'token' => $token,
                        ), admin_url('admin-ajax.php'));

                        $thumbnail_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
                        ?>
                        <div class="individual-download-item">
                            <a href="<?php echo esc_url($download_url); ?>"
                               class="individual-download-link"
                               download
                               data-image-id="<?php echo esc_attr($attachment_id); ?>">
                                <div class="individual-download-thumb">
                                    <img src="<?php echo esc_url($thumbnail_url ?: $image['url']); ?>"
                                         alt="<?php echo esc_attr($image['alt'] ?: sprintf(__('Photo %d', 'cedricph'), $counter)); ?>"
                                         loading="lazy" />
                                    <div class="individual-download-overlay">
                                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <span class="individual-download-number">
                                    <?php printf(__('Photo %d', 'cedricph'), $counter); ?>
                                </span>
                            </a>
                        </div>
                        <?php
                        $counter++;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Progress Indicator -->
    <div class="download-progress" id="download-progress" style="display: none;">
        <div class="download-progress-bar">
            <div class="download-progress-fill" id="download-progress-fill"></div>
        </div>
        <p class="download-progress-text" id="download-progress-text">
            <?php esc_html_e('Preparing download...', 'cedricph'); ?>
        </p>
    </div>
</div>

<script>
(function() {
    // Toggle individual downloads list
    const toggleBtn = document.getElementById('toggle-individual-downloads');
    const downloadsList = document.getElementById('individual-downloads-list');

    if (toggleBtn && downloadsList) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = downloadsList.style.display !== 'none';
            downloadsList.style.display = isVisible ? 'none' : 'block';
            toggleBtn.classList.toggle('expanded', !isVisible);
        });
    }

    // Optional: Show progress for bulk download
    const bulkDownloadBtn = document.getElementById('bulk-download-btn');
    const progressIndicator = document.getElementById('download-progress');

    if (bulkDownloadBtn && progressIndicator) {
        bulkDownloadBtn.addEventListener('click', function() {
            progressIndicator.style.display = 'block';
            setTimeout(function() {
                progressIndicator.style.display = 'none';
            }, 3000);
        });
    }
})();
</script>
