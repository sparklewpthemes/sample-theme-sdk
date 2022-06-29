<?php
/**
 * Sparkle WEDL Sample Theme Updater functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Sparkle_WEDL_Sample_Theme_Updater
 */

/**
 * Load theme updater functions.
 * Action is used so that child themes can easily disable.
 */

function sparkle_wedl_theme_updater() {
	require( get_template_directory() . '/theme-updater/sparkle-wedl-sample-theme-updater.php' );
}
add_action( 'after_setup_theme', 'sparkle_wedl_theme_updater' );