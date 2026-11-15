<?php
/**
 * Boots the plugin: registers every hook.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer;

use LinksAutoReplacer\Admin\AdminPage;
use LinksAutoReplacer\Admin\Branding;
use LinksAutoReplacer\Admin\DisableOption;
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
	 * @param string $file The plugin's main file.
	 */
	public function __construct( string $file ) {
		$this->file = $file;
	}

	/**
	 * Whether Affiliate Butler Pro is active. It links the same `lar_link` posts, so Lite
	 * pauses instead of linking every keyword twice.
	 *
	 * @return bool
	 */
	public static function pro_is_active(): bool {
		/**
		 * Filters whether Affiliate Butler Pro is active, which pauses this plugin.
		 *
		 * @param bool $active Whether Affiliate Butler Pro is loaded.
		 */
		return (bool) apply_filters( 'lar_pro_active', defined( 'ABUTLERPRO_VERSION' ) || function_exists( 'abutler_pro' ) );
	}

	/**
	 * Register the hooks. Runs on plugins_loaded, when Affiliate Butler Pro is loaded if active.
	 */
	public function boot(): void {
		if ( self::pro_is_active() ) {
			add_action( 'admin_notices', array( $this, 'paused_notice' ) );
			return;
		}

		add_action( 'init', array( PostType::class, 'register' ) );
		add_action( 'init', array( $this, 'maybe_upgrade' ), 99 );
		add_action( 'rest_api_init', array( new Controller(), 'register_routes' ) );

		ContentFilter::register_hooks();
		ShortLinks::register_hooks();
		( new DisableOption( $this->file ) )->register_hooks();

		if ( is_admin() ) {
			$this->boot_admin();
		}
	}

	/**
	 * The admin screens.
	 */
	private function boot_admin(): void {
		( new AdminPage( $this->file ) )->register_hooks();
		( new Branding( $this->file ) )->register_hooks();
	}

	/**
	 * Tell the admins on the Plugins screen that Affiliate Butler Pro does the linking now.
	 */
	public function paused_notice(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->id !== 'plugins' || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p>%s</p></div>',
			esc_html__( 'Affiliate Butler Pro is active and links your keywords, with the same links and settings, so Keywords to Links Converter is paused. You can deactivate Keywords to Links Converter.', 'links-auto-replacer' )
		);
	}

	/**
	 * Run the steps of a new version once.
	 */
	public function maybe_upgrade(): void {
		$previous = (string) get_option( self::VERSION_OPTION );
		if ( $previous === LAR_VERSION ) {
			return;
		}

		// The post type has no public pages or screens since 3.0.0, and the short links rule may be new.
		flush_rewrite_rules( false );
		// The cached links may miss what the new version adds to them.
		KeywordMap::flush();
		update_option( self::VERSION_OPTION, LAR_VERSION );
	}
}
