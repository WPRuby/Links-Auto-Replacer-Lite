<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wpruby.com/plugin/affiliate-butler-pro/
 * @since      2.0.0
 *
 * @package    Links_Auto_Replacer
 * @subpackage Links_Auto_Replacer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.0
 * @package    Links_Auto_Replacer
 * @subpackage Links_Auto_Replacer/includes
 * @author     Waseem Senjer <waseem.senjer@gmail.com>
 */
class Links_Auto_Replacer_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    2.0.0
	 */
	public static function activate() {
		self::import_old_data();
	}

	/**
	 * Before 2.0 version, the plugin was using a custom table to store links
	 * The new version is using custom post types, so the plugin must import
	 * the old data from the table on activation.
	 *
	 * 
	 *
	 * @since    2.0.0
	 */
	private static function import_old_data(){

		// we only need to import the data once.
		if(get_option(LAR_LITE_PLUGIN_PREFIX.'old_data_imported') === 'yes') return; 

		global $wpdb;
		$table_name = $wpdb->prefix .'lar_links';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) !== $table_name ) {
			add_option( LAR_LITE_PLUGIN_PREFIX . 'old_data_imported', 'yes' );
			return;
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- the table name only.
		$old_links = $wpdb->get_results( 'SELECT * FROM ' . $table_name );

		if ( empty( $old_links ) ) return;

		foreach ($old_links as $link){
			$args = array();
			$args['post_title'] = $link->keyword;
			$args['post_status'] = 'publish';
			$args['post_type'] = 'lar_link';
			$args['post_date'] = gmdate( 'Y-m-d H:i:s', (int) $link->created );
			$link_id = wp_insert_post($args);
			add_post_meta($link_id, LAR_LITE_PLUGIN_PREFIX . 'keywords', explode(',', $link->keyword));
			add_post_meta($link_id, LAR_LITE_PLUGIN_PREFIX . 'url', $link->keyword_url);
			add_post_meta($link_id, LAR_LITE_PLUGIN_PREFIX . 'do_follow', $link->dofollow);
			add_post_meta($link_id, LAR_LITE_PLUGIN_PREFIX . 'open_in', $link->open_in);
			add_post_meta($link_id, LAR_LITE_PLUGIN_PREFIX . 'shrink', $link->cloack);
			add_post_meta($link_id, LAR_LITE_PLUGIN_PREFIX . 'slug', $link->slug);
			add_post_meta($link_id, LAR_LITE_PLUGIN_PREFIX . 'link_type', 'external');
			@add_post_meta($link_id, LAR_LITE_PLUGIN_PREFIX . 'is_sensitive', $link->is_sensitive);

		}
		add_option(LAR_LITE_PLUGIN_PREFIX. 'old_data_imported','yes');
	}

}
