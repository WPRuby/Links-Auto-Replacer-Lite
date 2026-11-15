<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * The plugin's settings and caches are removed. The auto links themselves (the `lar_link`
 * posts and their click counts) are kept: Affiliate Butler Pro uses the same links, and
 * they come back if the plugin is installed again.
 *
 * @package Links_Auto_Replacer
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

foreach ( array( 'lar_settings', 'lar_version', 'lar_cache_version', '_lar_links_old_data_imported' ) as $lar_option ) {
	delete_option( $lar_option );
}
delete_transient( 'lar_keyword_map' );
