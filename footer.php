<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Logo -->
            <div class="footer-branding">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-site-title">
                        <?php bloginfo('name'); ?>
                    </a>
                    <?php
                }
                ?>
            </div>

            <!-- Social Media Icons -->
            <div class="footer-social">
                <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="footer-social-link" aria-label="Visit Instagram">
                    <svg class="footer-social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                    </svg>
                </a>
                <a href="https://www.linkedin.com" target="_blank" rel="noopener noreferrer" class="footer-social-link" aria-label="Visit LinkedIn">
                    <svg class="footer-social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                        <rect x="2" y="9" width="4" height="12"/>
                        <circle cx="4" cy="4" r="2"/>
                    </svg>
                </a>
                <a href="mailto:contact@example.com" class="footer-social-link" aria-label="Send email">
                    <svg class="footer-social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </a>
            </div>
            
            <!-- Separator Line -->
            <div class="footer-separator">
                <div class="separator-line"></div>
            </div>

            <!-- Copyright -->
            <div class="footer-copyright">
                <p>&copy;<?php echo date('Y'); ?> Cedric Ph. All rights reserved | Website by <a href="https://lukasvannuffel.vercel.app" target="_blank" rel="noopener noreferrer" class="footer-link">Codelux.be</a></p>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>