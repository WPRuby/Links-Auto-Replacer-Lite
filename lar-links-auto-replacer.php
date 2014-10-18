<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   Links Auto Replacer
 * @author    Waseem Senjer <waseem.senjer@gmail.com>
 * @license   GPL-2.0+
 * @link      http://waseem-senjer.com
 * @copyright 2014 Waseem Senjer
 *
 * @wordpress-plugin
 * Plugin Name:       Links Auto Replacer
 * Plugin URI:        http://waseem-senjer.com/lar/
 * Description:       Auto replace your affiliate links and track them.
 * Version:           1.0.0
 * Author:            Waseem Senjer
 * Author URI:        http://waseem-senjer.com
 * Text Domain:       lar-links-auto-replacer
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/wsenjer/Links-Replacer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


add_action('init', 'lar_action_init');
function lar_action_init(){
	load_plugin_textdomain('lar-links-auto-replacer', false, basename( dirname( __FILE__ ) ) . '/languages' );

}

require_once(WP_PLUGIN_DIR . '/lar/admin/admin-functions.php');

require_once(WP_PLUGIN_DIR . '/lar/admin/admin-interface.php');

require_once(WP_PLUGIN_DIR . '/lar/admin/settings.php');





function lar_activate() {
			global $wpdb;
			    $sql = '

			CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'lar_links` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `keyword` text NOT NULL,
			  `keyword_url` text NOT NULL,
			  `dofollow` int(1) NOT NULL,
			  `open_in` varchar(255) NOT NULL,
			  `cloack` int(1) NOT NULL,
			  `slug` varchar(255) NOT NULL,
			  `created` int(11) NOT NULL,
			  `updated` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
			';

			$wpdb->query($sql);
}
register_activation_hook( __FILE__, 'lar_activate' );


/// Replace The links
add_filter('the_content','lar_auto_replace_links');

function lar_auto_replace_links($content){
	global $wpdb; 

	$links = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'lar_links');

	foreach ($links as $link) {
		$dofollow = '';
		if($link->dofollow != 1){
			$dofollow = 'rel="nofollow"';
		}
		$url = '<a href="'.$link->keyword_url.'" '.$dofollow.' target="'.$link->open_in.'">'.$link->keyword.'</a>';
		$content = preg_replace('/\b'.$link->keyword.'\b/u', $url, $content);

		
	}

	return $content;
}


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-name-admin.php` with the name of the plugin's admin file
 * - replace Plugin_Name_Admin with the name of the class defined in
 *   `class-plugin-name-admin.php`
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	//require_once( plugin_dir_path( __FILE__ ) . 'admin/class-plugin-name-admin.php' );
	//add_action( 'plugins_loaded', array( 'Plugin_Name_Admin', 'get_instance' ) );

}

if(is_admin()){



add_action( 'wp_ajax_delete_link', 'lar_delete_link_callback' );

function lar_delete_link_callback() {
	global $wpdb; // this is how you get access to the database

	$link_id = intval( $_POST['link_id'] );

	$wpdb->delete($wpdb->prefix.'lar_links',array('id'=>$link_id));

        

	die(); // this is required to terminate immediately and return a proper response
}
}
