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
 * Version:           1.1.0
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
	if ( version_compare( GFForms::$version, '1.7', '<=' ) ) {
		add_action( 'gform_after_submission', 'gfdse_remove_form_entry_fallback' );
	} else {
		add_action( 'gform_after_submission', 'gfdse_remove_form_entry' );
	}
}
add_action( 'plugins_loaded', 'gfdse_hook_to_gform_after_submission' );

/**
 * Delete Gravity Form Entry Data after Submission.
 * 
 * @param object $entry The entry that was just created.
 *
 * @since 1.0.0
 */
function gfdse_remove_form_entry( $entry ) {
	GFAPI::delete_entry( $entry['id'] );
}

/**
 * Delete Gravity Form Entry Data after Submission for Gravity Forms 1.7 and earlier.
 * 
 * @param object $entry The entry that was just created.
 *
 * @since 1.1.0
 */
function gfdse_remove_form_entry_fallback( $entry ) {
    global $wpdb;
 
    $lead_id = $entry['id'];
    $lead_table = RGFormsModel::get_lead_table_name();
    $lead_notes_table = RGFormsModel::get_lead_notes_table_name();
    $lead_detail_table = RGFormsModel::get_lead_details_table_name();
    $lead_detail_long_table = RGFormsModel::get_lead_details_long_table_name();
 
    //Delete from detail long
    $sql = $wpdb->prepare( "DELETE FROM $lead_detail_long_table WHERE lead_detail_id IN(SELECT id FROM $lead_detail_table WHERE lead_id=%d)", $lead_id );
    $wpdb->query( $sql );
 
    //Delete from lead details
    $sql = $wpdb->prepare( "DELETE FROM $lead_detail_table WHERE lead_id=%d", $lead_id );
    $wpdb->query( $sql );
 
    //Delete from lead notes
    $sql = $wpdb->prepare( "DELETE FROM $lead_notes_table WHERE lead_id=%d", $lead_id );
    $wpdb->query( $sql );
 
    //Delete from lead
    $sql = $wpdb->prepare( "DELETE FROM $lead_table WHERE id=%d", $lead_id );
    $wpdb->query( $sql );
}
