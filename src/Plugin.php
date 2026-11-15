<?php
/**
 * Boots the plugin: registers every hook.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer;

use LinksAutoReplacer\Admin\AdminPage;
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
	 * Register the hooks.
	 */
	public function boot(): void {
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
