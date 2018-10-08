<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/sofyansitorus
 * @since             1.0.0
 * @package           Gfdse
 *
 * @wordpress-plugin
 * Plugin Name:       Gravity Form Disable Storing Entry
 * Plugin URI:        https://github.com/sofyansitorus/Gravity-Form-Disable-Storing-Entry
 * Description:       By default, Gravity Forms was designed to record all data submitted into the database, so this plugin will delete the entry data upon submission.
 * Version:           1.0.0
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if plugin is active
 *
 * @param string $plugin_file Plugin file name.
 */
function gfdse_is_plugin_active( $plugin_file ) {
	$active_plugins = (array) apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, (array) get_site_option( 'active_sitewide_plugins', array() ) );
	}

	return in_array( $plugin_file, $active_plugins, true ) || array_key_exists( $plugin_file, $active_plugins );
}

/**
 * Check if Gravity Form plugin is active
 */
if ( ! gfdse_is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
	return;
}

/**
 * Hook to gform_after_submission actions.
 *
 * @since 1.0.0
 */
function gfdse_hook_to_gform_after_submission(){
	// Hook to gform_after_submission actions.
	add_action( 'gform_after_submission', 'gfdse_remove_form_entry' );
}
add_action( 'plugins_loaded', 'gfdse_hook_to_gform_after_submission' );

/**
 * Delete Gravity Form Entry Data after Submission.
 *
 * @since 1.0.0
 */
function gfdse_remove_form_entry( $entry ) {
    GFAPI::delete_entry( $entry['id'] );
}
