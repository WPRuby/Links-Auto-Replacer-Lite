<?php
/**
 * Links the keywords of the post content (`the_content`).
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Links;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ContentFilter {

	/**
	 * Register the hooks.
	 */
	public static function register_hooks(): void {
		add_filter( 'the_content', array( __CLASS__, 'filter' ) );
		KeywordMap::register_hooks();
	}

	/**
	 * Link the keywords of the content.
	 *
	 * @param mixed $content The content.
	 *
	 * @return mixed
	 */
	public static function filter( $content ) {
		if ( ! is_string( $content ) || $content === '' || ! lar()->is_enabled() ) {
			return $content;
		}

		$post_id = get_the_ID() ? (int) get_the_ID() : 0;
		if ( $post_id && get_post_meta( $post_id, 'lar_disabled', true ) === 'on' ) {
			return $content;
		}

		// The same HTML gives the same result until a link, a setting or a post changes.
		$cache_key = md5( KeywordMap::version() . '|' . $post_id . '|' . get_locale() . '|' . $content );
		$replaced  = wp_cache_get( $cache_key, 'lar_content' );
		if ( ! is_string( $replaced ) ) {
			$replaced = ( new Replacer( KeywordMap::get_map(), $post_id ) )->replace( $content );
			wp_cache_set( $cache_key, $replaced, 'lar_content', DAY_IN_SECONDS );
		}

		/**
		 * Filters the content after the keywords were linked.
		 *
		 * @param string $content The content.
		 */
		return apply_filters( 'lar_replace_content', $replaced );
	}
}
