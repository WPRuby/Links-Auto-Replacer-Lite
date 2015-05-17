<?php

/**
 * Fired during plugin activation
 *
 * @link       http://waseem-senjer.com/product/links-auto-replacer-pro/
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
 * @author     Your Name <waseem.senjer@gmail.com>
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
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    2.0.0
	 */
	private function import_old_data(){

		if(get_option('lar_old_data_imported') === 'yes') return; 

		global $wpdb;
		$table_name = $wpdb->prefix .'lar_links';

		$old_links = $wpdb->get_results('SELECT * FROM '.$table_name);

		if($old_links === false) return;

		foreach ($old_links as $link){
			$args = array();
			$args['post_title'] = $link->keyword;
			$args['post_status'] = 'publish';
			$args['post_type'] = 'lar_link';
			$args['post_date'] = date('Y-m-d H:i:s', $link->created);
			$link_id = wp_insert_post($args);
			add_post_meta($link_id, PLUGIN_PREFIX . 'keywords', explode(',', $link->keyword));
			add_post_meta($link_id, PLUGIN_PREFIX . 'url', $link->keyword_url);
			add_post_meta($link_id, PLUGIN_PREFIX . 'do_follow', $link->dofollow);
			add_post_meta($link_id, PLUGIN_PREFIX . 'open_in', $link->open_in);
			add_post_meta($link_id, PLUGIN_PREFIX . 'shrink', $link->cloack);
			add_post_meta($link_id, PLUGIN_PREFIX . 'slug', $link->slug);
			add_post_meta($link_id, PLUGIN_PREFIX . 'link_type', 'external');
			@add_post_meta($link_id, PLUGIN_PREFIX . 'is_sensitive', $link->is_sensitive);

		}
		add_option('lar_old_data_imported','yes');
	}

}
