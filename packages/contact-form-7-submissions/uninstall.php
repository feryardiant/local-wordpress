<?php
/**
 * Contact Form 7 Submissions Uninstaller
 *
 * Uninstalling Contact Form 7 Submissions deletes submissions and options.
 *
 * @package feryardiant/contact-form-7-submissions
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Clear any cached data that has been removed.
wp_cache_flush();
