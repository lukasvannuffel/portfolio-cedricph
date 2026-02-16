/**
 * Admin JavaScript for Private Project Management
 *
 * Handles token generation, regeneration, and clipboard copying
 * for private project galleries.
 */

(function($) {
    'use strict';

    // Wait for DOM to be ready
    $(document).ready(function() {
        /**
         * Handle token generation/regeneration
         */
        function generateToken(isRegenerate) {
            const button = isRegenerate ? $('#cedricph-regenerate-token') : $('#cedricph-generate-token');
            const messageBox = $('#cedricph-token-message');

            // Confirm regeneration
            if (isRegenerate && !confirm(cedricphPrivateProject.strings.confirm_regenerate)) {
                return;
            }

            // Disable button and show loading state
            button.prop('disabled', true).text(isRegenerate ? 'Regenerating...' : 'Generating...');
            messageBox.hide().removeClass('error');

            // AJAX request
            $.ajax({
                url: cedricphPrivateProject.ajax_url,
                type: 'POST',
                data: {
                    action: 'cedricph_generate_token',
                    post_id: cedricphPrivateProject.post_id,
                    nonce: cedricphPrivateProject.nonce,
                    regenerate: isRegenerate ? 'true' : 'false'
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        messageBox
                            .removeClass('error')
                            .html('<strong>Success!</strong> ' + response.data.message)
                            .show();

                        // Reload page to show updated UI
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        // Show error message
                        messageBox
                            .addClass('error')
                            .html('<strong>Error:</strong> ' + (response.data.message || 'Unknown error occurred'))
                            .show();

                        button.prop('disabled', false).text(isRegenerate ? 'Regenerate Token' : 'Generate Access Token');
                    }
                },
                error: function(xhr, status, error) {
                    messageBox
                        .addClass('error')
                        .html('<strong>Error:</strong> Failed to communicate with server. ' + error)
                        .show();

                    button.prop('disabled', false).text(isRegenerate ? 'Regenerate Token' : 'Generate Access Token');
                }
            });
        }

        /**
         * Copy link to clipboard
         */
        function copyLinkToClipboard() {
            const linkInput = $('#cedricph-private-link');
            const copyButton = $('#cedricph-copy-link');
            const messageBox = $('#cedricph-token-message');

            if (!linkInput.length) {
                return;
            }

            const link = linkInput.val();

            // Modern clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(link).then(function() {
                    // Success
                    messageBox
                        .removeClass('error')
                        .html('<strong>Success!</strong> ' + cedricphPrivateProject.strings.copied)
                        .show();

                    // Update button text temporarily
                    const originalText = copyButton.text();
                    copyButton.text('✓ Copied!');

                    setTimeout(function() {
                        copyButton.text(originalText);
                        messageBox.fadeOut();
                    }, 2000);
                }).catch(function(err) {
                    // Fallback to old method
                    fallbackCopyToClipboard(link);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyToClipboard(link);
            }
        }

        /**
         * Fallback clipboard copy for older browsers
         */
        function fallbackCopyToClipboard(text) {
            const linkInput = $('#cedricph-private-link');
            const messageBox = $('#cedricph-token-message');
            const copyButton = $('#cedricph-copy-link');

            try {
                linkInput.select();
                linkInput[0].setSelectionRange(0, 99999); // For mobile devices

                const successful = document.execCommand('copy');

                if (successful) {
                    messageBox
                        .removeClass('error')
                        .html('<strong>Success!</strong> ' + cedricphPrivateProject.strings.copied)
                        .show();

                    const originalText = copyButton.text();
                    copyButton.text('✓ Copied!');

                    setTimeout(function() {
                        copyButton.text(originalText);
                        messageBox.fadeOut();
                    }, 2000);
                } else {
                    throw new Error('Copy command failed');
                }
            } catch (err) {
                messageBox
                    .addClass('error')
                    .html('<strong>Note:</strong> ' + cedricphPrivateProject.strings.copy_failed)
                    .show();

                // Select the text for manual copying
                linkInput.select();
            }
        }

        // Event listeners
        $('#cedricph-generate-token').on('click', function(e) {
            e.preventDefault();
            generateToken(false);
        });

        $('#cedricph-regenerate-token').on('click', function(e) {
            e.preventDefault();
            generateToken(true);
        });

        $('#cedricph-copy-link').on('click', function(e) {
            e.preventDefault();
            copyLinkToClipboard();
        });

        // Auto-select link on click
        $('#cedricph-private-link').on('click', function() {
            $(this).select();
        });
    });

})(jQuery);
