<?php
/**
 * Boots the plugin: registers every hook.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer;

use LinksAutoReplacer\Links\ContentFilter;
use LinksAutoReplacer\Links\KeywordMap;
use LinksAutoReplacer\Links\PostType;
use LinksAutoReplacer\Links\ShortLinks;
use LinksAutoReplacer\Rest\Controller;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {

	/**
	 * The option that holds the version the site last ran.
	 */
	const VERSION_OPTION = 'lar_version';

	/**
	 * The plugin's main file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * The admin screens, in the admin only.
	 *
	 * @var \Links_Auto_Replacer_Admin|null
	 */
	private $admin = null;

	/**
	 * @param string $file The plugin's main file.
	 */
	public function __construct( string $file ) {
		$this->file = $file;
	}

	/**
	 * Register the hooks.
	 */
	public function boot(): void {
		add_action( 'init', array( PostType::class, 'register' ) );
		add_action( 'init', array( $this, 'maybe_upgrade' ), 99 );
		add_action( 'rest_api_init', array( new Controller(), 'register_routes' ) );
		add_action( 'cmb2_save_options-page_fields', array( lar(), 'remember_settings_saved' ), 10, 2 );

		ContentFilter::register_hooks();
		ShortLinks::register_hooks();

		if ( is_admin() ) {
			$this->boot_admin();
		}
	}

	/**
	 * The admin screens, still built with CMB2 until the new admin.
	 * Public for the tests, which don't run in the admin.
	 */
	public function boot_admin(): void {
		$dir = plugin_dir_path( $this->file );
		require_once $dir . 'admin/cmb/init.php';
		require_once $dir . 'admin/classes/class.base62.php';
		require_once $dir . 'admin/classes/class.link.php';
		require_once $dir . 'admin/classes/class-settings.php';
		require_once $dir . 'admin/class-lar-admin.php';

		// The admin classes translate strings when they are created, which needs init.
		add_action( 'init', array( $this, 'init_admin' ), 1 );
	}

	/**
	 * Create the admin screens and register their hooks.
	 */
	public function init_admin(): void {
		if ( $this->admin ) {
			return;
		}
		new \Lar_Lite_Settings();
		$admin       = new \Links_Auto_Replacer_Admin( 'links-auto-replacer', LAR_VERSION );
		$this->admin = $admin;

		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_styles' ) );
		add_action( 'cmb2_init', array( $admin, 'lar_links_register_metabox' ) );
		add_action( 'wp_ajax_my_pre_submit_validation', array( $admin, 'pre_submit_link_validation' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue_validation' ) );
		add_action( 'add_meta_boxes', array( $admin, 'disable_for_single_post' ) );
		add_action( 'save_post', array( $admin, 'lar_meta_save' ) );
		add_filter( 'manage_lar_link_posts_columns', array( $admin, 'lar_columns_head' ) );
		add_action( 'manage_lar_link_posts_custom_column', array( $admin, 'lar_columns_content' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $admin, 'add_upgrade_to_pro_box' ) );
		add_filter( 'post_row_actions', array( $admin, 'remove_row_actions' ), 10, 2 );
	}

	/**
	 * The link validation script, on the link screens only.
	 */
	public function maybe_enqueue_validation(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $this->admin && $screen && $screen->post_type === PostType::POST_TYPE && $screen->base === 'post' ) {
			$this->admin->insert_validation_nonce();
		}
	}

	/**
	 * Run the steps of a new version once.
	 */
	public function maybe_upgrade(): void {
		$previous = (string) get_option( self::VERSION_OPTION );
		if ( $previous === LAR_VERSION ) {
			return;
		}

		// The post type has no public pages since 3.0.0, and the short links rule may be new.
		flush_rewrite_rules( false );
		// The cached links may miss what the new version adds to them.
		KeywordMap::flush();
		update_option( self::VERSION_OPTION, LAR_VERSION );
	}
}
