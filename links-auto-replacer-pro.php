<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://waseem-senjer.com/product/links-auto-replacer-pro/
 * @since             1.0
 * @package           Links_Auto_Replacer_Pro
 *
 * @wordpress-plugin
 * Plugin Name:       Links Auto Replacer PRO
 * Plugin URI:        http://waseem-senjer.com/product/links-auto-replacer-pro/
 * Description:       Auto replace your affiliate links and track them.
 * Version:           2.0.0
 * Author:            Waseem Senjer
 * Author URI:        http://waseem-senjer.com/product/links-auto-replacer-pro//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       links-auto-replacer-pro
 * Domain Path:       /languages
 */

define('LAR_VERSION','2.0.0');
define('LAR_URL',plugin_dir_url(__FILE__));
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('PLUGIN_PREFIX','_lar_links_');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lar-pro-activator.php
 */
function activate_Links_Auto_Replacer_Pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-lar-pro-activator.php';
	Links_Auto_Replacer_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lar-pro-deactivator.php
 */
function deactivate_Links_Auto_Replacer_Pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-lar-pro-deactivator.php';
	Links_Auto_Replacer_Pro_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Links_Auto_Replacer_Pro' );
register_deactivation_hook( __FILE__, 'deactivate_Links_Auto_Replacer_Pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-lar-pro.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_Links_Auto_Replacer_Pro() {

	$plugin = new Links_Auto_Replacer_Pro();
	$plugin->run();

}
run_Links_Auto_Replacer_Pro();


/**
 * Check if there is a new update
 *
 * The plugin will contact the plugin repository to check 
 * if there is a new update has been released
 * using this library: https://github.com/YahnisElsts/plugin-update-checker
 *
 * @since    2.0.0
 */
require_once plugin_dir_path( __FILE__ ) .'admin/libs/update_checker/plugin-update-checker.php';
$larUpdateChecker = PucFactory::buildUpdateChecker(
    'http://www.waseem-senjer.com/plugins/lar-pro/metadata.json',
    __FILE__);
