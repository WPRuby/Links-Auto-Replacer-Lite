<?php
/**
 * The short links: `/go/{slug}` (or `?go={slug}` with plain permalinks) redirects to the URL
 * of the link and counts the click.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Links;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ShortLinks {

	const QUERY_VAR = 'go';

	/**
	 * The meta that counts the clicks of a link.
	 */
	const CLICKS_META = '_lar_links_clicks';

	/**
	 * User agents of bots, crawlers and link previews, their visits are not counted.
	 */
	const BOTS = '/bot\b|bot\/|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|preview|headless|lighthouse|curl\/|wget\/|python-requests|go-http-client|okhttp|java\//i';

	/**
	 * Register the hooks.
	 */
	public static function register_hooks(): void {
		add_filter( 'rewrite_rules_array', array( __CLASS__, 'rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		// Before redirect_canonical(), which would add a trailing slash redirect first.
		add_action( 'template_redirect', array( __CLASS__, 'redirect' ), 1 );
	}

	/**
	 * Add the short links rule.
	 *
	 * @param array $rules The rewrite rules.
	 *
	 * @return array
	 */
	public static function rewrite_rules( $rules ) {
		return array( '^' . self::QUERY_VAR . '/([^/]*)/?$' => 'index.php?' . self::QUERY_VAR . '=$matches[1]' ) + (array) $rules;
	}

	/**
	 * Add the short links query var.
	 *
	 * @param array $vars The query vars.
	 *
	 * @return array
	 */
	public static function query_vars( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	/**
	 * Redirect a short link to the URL of its link.
	 */
	public static function redirect(): void {
		$slug = get_query_var( self::QUERY_VAR );
		if ( ! is_string( $slug ) || $slug === '' ) {
			return;
		}

		$links = get_posts(
			array(
				'post_type'      => PostType::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_key'       => LAR_LITE_PLUGIN_PREFIX . 'slug', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => sanitize_text_field( $slug ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( empty( $links ) ) {
			return;
		}

		$link_id = (int) $links[0]->ID;
		$url     = esc_url_raw( (string) get_post_meta( $link_id, LAR_LITE_PLUGIN_PREFIX . 'url', true ), array( 'http', 'https' ) );
		if ( $url === '' ) {
			return;
		}

		self::count_click( $link_id );
		/**
		 * Fires before a short link redirects.
		 *
		 * @param int $link_id The link ID.
		 */
		do_action( 'lar_link_redirected', $link_id );

		// Every visit reaches the site, and the short links stay out of search results.
		nocache_headers();
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow' );
		}
		wp_redirect( $url, 302, 'Keywords to Links Converter' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- short links redirect to external URLs by design.
		exit;
	}

	/**
	 * Count a click on a short link, unless the visitor is a bot.
	 *
	 * @param int $link_id The link ID.
	 */
	public static function count_click( int $link_id ): void {
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		/**
		 * Filters whether the click is counted, e.g. to follow a consent plugin.
		 *
		 * @param bool $count   Whether to count it, false for bots.
		 * @param int  $link_id The link ID.
		 */
		if ( ! apply_filters( 'lar_count_click', ! preg_match( self::BOTS, $user_agent ), $link_id ) ) {
			return;
		}
		update_post_meta( $link_id, self::CLICKS_META, self::clicks( $link_id ) + 1 );
	}

	/**
	 * The clicks of a link.
	 *
	 * @param int $link_id The link ID.
	 *
	 * @return int
	 */
	public static function clicks( int $link_id ): int {
		return (int) get_post_meta( $link_id, self::CLICKS_META, true );
	}
}
