<?php
/**
 * Easy Digital Downloads Theme Updater
 *
 * @package Sparkle_WEDL_Sample_Theme_Updater
 */

// Includes the files needed for the theme updater
if ( !class_exists( 'Sparkle_WE_DP_Theme_Updater_Admin' ) ) {
	include( dirname( __FILE__ ) . '/wedl-theme-updater-admin.php' );
}

// Loads the updater classes
$updater = new Sparkle_WE_DP_Theme_Updater_Admin(
	// Config settings
	$config = array(
		'remote_api_url' => 'http://yourshop.url', //  Site where EDD is hosted
		'item_name'      => 'Theme Name', // Name of theme
		'theme_slug'     => 'sparkle-edd-sample-theme', // Theme slug
		'version'        => '1.0.0', // The current version of this theme
		'author'         => 'Sparkle WP Themes', // The author of this theme
		'download_id'    => '', // Optional, used for generating a license renewal link
		'renew_url'      => '', // Optional, allows for a custom license renewal link
		'product_id'     => 'PRODUCT_ID', // set the product id here *important
	)
);