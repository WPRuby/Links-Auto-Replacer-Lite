<?php
/**
 * Plugin Name:       Keywords to Links Converter
 * Plugin URI:        https://wpruby.com/plugin/affiliate-butler-pro/
 * Description:       Link your keywords to URLs automatically, with short links and click counts.
 * Version:           3.0.0
 * Author:            Waseem Senjer
 * Author URI:        https://wpruby.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       links-auto-replacer
 * Domain Path:       /languages
 * Requires at least: 6.6
 * Requires PHP:      7.4
 *
 * @package Links_Auto_Replacer
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LAR_VERSION', '3.0.0' );
define( 'LAR_URL', plugin_dir_url( __FILE__ ) );
define( 'LAR_DIR', plugin_dir_path( __FILE__ ) );
define( 'LAR_LITE_PLUGIN_PREFIX', '_lar_links_' );

require_once LAR_DIR . 'src/autoload.php';

/**
 * The plugin settings.
 *
 * @return \LinksAutoReplacer\Settings
 */
function lar() {
	static $settings = null;
	if ( $settings === null ) {
		$settings = new \LinksAutoReplacer\Settings();
	}
	return $settings;
}

register_activation_hook( __FILE__, array( \LinksAutoReplacer\Activation::class, 'activate' ) );

// On plugins_loaded, so the plugin can tell whether Affiliate Butler Pro is active.
add_action( 'plugins_loaded', array( new \LinksAutoReplacer\Plugin( __FILE__ ), 'boot' ) );

$GLOBALS['lar_name'] = 'Keywords to Links Converter';
