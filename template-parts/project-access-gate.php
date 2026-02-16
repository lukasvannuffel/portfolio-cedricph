<?php
/**
 * Template Part: Project Access Gate
 *
 * Displays access denied message for private projects.
 * This is shown when a valid access token is not provided.
 *
 * Available variables from get_template_part args:
 * @var string $error Error message to display
 * @var int $post_id The project post ID
 */

$error = $args['error'] ?? __('Access token is required to view this private gallery.', 'cedricph');
$post_id = $args['post_id'] ?? 0;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo esc_html(get_the_title($post_id)); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            background: linear-gradient(135deg, #1C1C1C 0%, #303030 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .private-project-gate {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 12px;
            padding: 48px 32px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .gate-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            color: #D4AF37;
            opacity: 0.8;
        }

        .gate-title {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 600;
            color: #D4AF37;
            margin: 0 0 16px;
            letter-spacing: -0.02em;
        }

        .gate-message {
            color: #E5E5E5;
            font-size: 1rem;
            line-height: 1.6;
            margin: 0 0 32px;
        }

        .gate-error {
            background: rgba(214, 54, 56, 0.1);
            border: 1px solid rgba(214, 54, 56, 0.3);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            color: #FF6B6B;
            font-size: 0.9375rem;
        }

        .gate-instructions {
            background: rgba(212, 175, 55, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 8px;
            padding: 20px;
            margin-top: 24px;
            text-align: left;
        }

        .gate-instructions p {
            color: #B8B8B8;
            font-size: 0.875rem;
            line-height: 1.6;
            margin: 0 0 12px;
        }

        .gate-instructions p:last-child {
            margin-bottom: 0;
        }

        .gate-instructions strong {
            color: #D4AF37;
        }

        .gate-home-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: transparent;
            border: 1px solid #D4AF37;
            color: #D4AF37;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .gate-home-link:hover {
            background: #D4AF37;
            color: #1C1C1C;
            transform: translateY(-2px);
        }

        @media (max-width: 640px) {
            .private-project-gate {
                padding: 32px 24px;
            }

            .gate-icon {
                width: 48px;
                height: 48px;
            }
        }
    </style>
</head>
<body class="private-project-access-denied">
    <div class="private-project-gate">
        <svg class="gate-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>

        <h1 class="gate-title">
            <?php esc_html_e('Private Gallery', 'cedricph'); ?>
        </h1>

        <p class="gate-message">
            <?php esc_html_e('This is a private photo gallery that requires a special access link to view.', 'cedricph'); ?>
        </p>

        <div class="gate-error">
            <strong><?php esc_html_e('Access Denied:', 'cedricph'); ?></strong>
            <?php echo esc_html($error); ?>
        </div>

        <div class="gate-instructions">
            <p>
                <strong><?php esc_html_e('Need access?', 'cedricph'); ?></strong>
            </p>
            <p>
                <?php esc_html_e('If you received a link from the photographer, please make sure you copied the entire URL including the access code.', 'cedricph'); ?>
            </p>
            <p>
                <?php esc_html_e('If your link has expired or you need a new one, please contact the photographer directly.', 'cedricph'); ?>
            </p>
        </div>

        <div style="margin-top: 32px;">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="gate-home-link">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span><?php esc_html_e('Return to Homepage', 'cedricph'); ?></span>
            </a>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
