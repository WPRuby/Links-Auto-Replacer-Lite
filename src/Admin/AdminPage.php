<?php
/**
 * The "Auto Links" admin menu, its pages host the React admin app (assets/src/admin).
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Admin;

use LinksAutoReplacer\Links\PostType;
use LinksAutoReplacer\Links\Replacer;
use LinksAutoReplacer\Links\Repository;
use LinksAutoReplacer\Rest\Controller;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AdminPage {

	const SLUG = 'links-auto-replacer';

	/**
	 * The pages: slug => the app view it opens.
	 */
	const PAGES = array(
		self::SLUG               => 'links',
		self::SLUG . '-new'      => 'edit',
		self::SLUG . '-settings' => 'settings',
	);

	const PRO_URL = 'https://wpruby.com/plugin/affiliate-butler-pro/';

	/**
	 * The plugin's main file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * The hook suffixes of the pages.
	 *
	 * @var string[]
	 */
	private $hooks = array();

	/**
	 * @param string $file The plugin's main file.
	 */
	public function __construct( string $file ) {
		$this->file = $file;
	}

	/**
	 * Register the hooks.
	 */
	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_upgrade_link' ), 100 );
		// Before WordPress checks the page exists, which it does while it builds the menu.
		add_action( 'admin_menu', array( $this, 'redirect_old_screens' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( $this->file ), array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Add the menu and its pages.
	 */
	public function add_menu(): void {
		$this->hooks[] = add_menu_page( __( 'Auto Links', 'links-auto-replacer' ), __( 'Auto Links', 'links-auto-replacer' ), 'edit_posts', self::SLUG, array( $this, 'render' ), 'dashicons-editor-unlink', 26 );
		$this->hooks[] = add_submenu_page( self::SLUG, __( 'All Auto Links', 'links-auto-replacer' ), __( 'All Auto Links', 'links-auto-replacer' ), 'edit_posts', self::SLUG, array( $this, 'render' ) );
		$this->hooks[] = add_submenu_page( self::SLUG, __( 'Add New Auto Link', 'links-auto-replacer' ), __( 'Add New', 'links-auto-replacer' ), 'edit_posts', self::SLUG . '-new', array( $this, 'render' ) );
		$this->hooks[] = add_submenu_page( self::SLUG, __( 'Settings', 'links-auto-replacer' ), __( 'Settings', 'links-auto-replacer' ), 'manage_options', self::SLUG . '-settings', array( $this, 'render' ) );
	}

	/**
	 * The link to Affiliate Butler Pro at the end of the menu.
	 */
	public function add_upgrade_link(): void {
		global $submenu;
		if ( ! isset( $submenu[ self::SLUG ] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$submenu[ self::SLUG ][] = array( __( 'Upgrade to Pro', 'links-auto-replacer' ), 'manage_options', self::PRO_URL ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * The "Settings" link on the plugins screen.
	 *
	 * @param string[] $links The action links.
	 *
	 * @return string[]
	 */
	public function plugin_action_links( array $links ): array {
		array_unshift( $links, '<a href="' . esc_url( self::url( 'settings' ) ) . '">' . esc_html__( 'Settings', 'links-auto-replacer' ) . '</a>' );
		return $links;
	}

	/**
	 * The URL of a view of the app.
	 *
	 * @param string $view    `links`, `edit` or `settings`.
	 * @param int    $link_id The link, for `edit`.
	 *
	 * @return string
	 */
	public static function url( string $view = 'links', int $link_id = 0 ): string {
		if ( $view === 'settings' ) {
			return admin_url( 'admin.php?page=' . self::SLUG . '-settings' );
		}
		if ( $view === 'edit' && ! $link_id ) {
			return admin_url( 'admin.php?page=' . self::SLUG . '-new' );
		}
		$args = array( 'page' => self::SLUG );
		if ( $view !== 'links' ) {
			$args['view'] = $view;
			$args['id']   = $link_id;
		}
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Send the screens from before 3.0.0 (CMB2) to the app.
	 */
	public function redirect_old_screens(): void {
		global $pagenow;
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- only reads which screen is opened.
		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : '';
		$page      = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		$post_id   = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$url = null;
		if ( in_array( $page, array( 'lar_settings', 'lar_about_settings' ), true ) ) {
			$url = self::url( 'settings' );
		} elseif ( $pagenow === 'edit.php' && $post_type === PostType::POST_TYPE ) {
			$url = self::url();
		} elseif ( $pagenow === 'post-new.php' && $post_type === PostType::POST_TYPE ) {
			$url = self::url( 'edit' );
		} elseif ( $pagenow === 'post.php' && $post_id && get_post_type( $post_id ) === PostType::POST_TYPE ) {
			$url = self::url( 'edit', $post_id );
		}

		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Load the app on the plugin's pages.
	 *
	 * @param string $hook The hook suffix of the page.
	 */
	public function enqueue( string $hook ): void {
		if ( ! in_array( $hook, $this->hooks, true ) ) {
			return;
		}

		$asset_file = plugin_dir_path( $this->file ) . 'build/admin.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;

		wp_enqueue_script( 'lar-admin', plugins_url( 'build/admin.js', $this->file ), $asset['dependencies'], $asset['version'], true );
		wp_enqueue_style( 'lar-admin', plugins_url( 'build/style-admin.css', $this->file ), array( 'wp-components' ), $asset['version'] );
		wp_set_script_translations( 'lar-admin', 'links-auto-replacer' );

		wp_add_inline_script( 'lar-admin', 'window.larAdmin = ' . wp_json_encode( $this->app_data() ) . ';', 'before' );
	}

	/**
	 * Render a page: the app mounts into it.
	 */
	public function render(): void {
		echo '<div class="wrap lar-admin-wrap"><div id="lar-admin"></div>';
		if ( ! file_exists( plugin_dir_path( $this->file ) . 'build/admin.js' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'The admin app is not built. Run `npm install && npm run build` in the plugin folder.', 'links-auto-replacer' ) . '</p></div>';
		}
		echo '</div>';
	}

	/**
	 * The data the app starts with.
	 *
	 * @return array
	 */
	private function app_data(): array {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- only reads which view is opened.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : self::SLUG;
		$view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : ( isset( self::PAGES[ $page ] ) ? self::PAGES[ $page ] : 'links' );
		$id   = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return array(
			'view'             => $view,
			'linkId'           => $id,
			'restPath'         => '/' . Controller::NAMESPACE,
			'adminUrl'         => admin_url( 'admin.php' ),
			'slug'             => self::SLUG,
			'shortLinkUrl'     => Replacer::short_link( '' ),
			'defaults'         => Repository::defaults(),
			'defaultShortLink' => lar()->to_array()['shrink'],
			'canSettings'      => current_user_can( 'manage_options' ),
			'proUrl'           => self::PRO_URL,
			'version'          => LAR_VERSION,
		);
	}
}
