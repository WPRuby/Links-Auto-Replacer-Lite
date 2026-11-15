<?php
/**
 * The keyword map: every keyword that should be linked and the link it points to.
 *
 * Building it means loading every auto link and its meta, so it is cached in a transient
 * and rebuilt only when links, link meta or the settings change.
 *
 * @since   3.0.0
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Links;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class KeywordMap {

	/**
	 * The transient that holds the map.
	 */
	const CACHE_KEY = 'lar_keyword_map';

	/**
	 * The option that holds the cache version, it changes whenever cached output may be stale.
	 */
	const VERSION_OPTION = 'lar_cache_version';

	/**
	 * Bump this when the map format changes.
	 */
	const FORMAT = 1;

	/**
	 * The link meta the replacer needs (without the prefix).
	 */
	const LINK_FIELDS = array( 'link_type', 'url', 'slug', 'open_in', 'do_follow', 'sponsored', 'is_sensitive' );

	/**
	 * The map for this request.
	 *
	 * @var array|null
	 */
	private static $map = null;

	/**
	 * Register the hooks that invalidate the caches.
	 */
	public static function register_hooks() {
		// Any post: a post's own content and permalink are part of the cached output.
		add_action( 'save_post', array( __CLASS__, 'flush' ) );
		add_action( 'deleted_post', array( __CLASS__, 'flush' ) );
		add_action( 'trashed_post', array( __CLASS__, 'flush' ) );
		add_action( 'untrashed_post', array( __CLASS__, 'flush' ) );

		// CMB2 saves the link fields after save_post.
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'flush_for_link_meta' ), 10, 2 );
		}

		foreach ( array( 'lar_settings', 'permalink_structure', 'home', 'siteurl' ) as $option ) {
			add_action( "update_option_{$option}", array( __CLASS__, 'flush' ) );
			add_action( "add_option_{$option}", array( __CLASS__, 'flush' ) );
			add_action( "delete_option_{$option}", array( __CLASS__, 'flush' ) );
		}
	}

	/**
	 * Invalidate the keyword map and the cached output.
	 */
	public static function flush() {
		self::$map = null;
		delete_transient( self::CACHE_KEY );
		update_option( self::VERSION_OPTION, self::version() + 1, false );
	}

	/**
	 * Invalidate the caches when the meta of an auto link changes.
	 *
	 * @param int|int[] $meta_ids  The meta IDs.
	 * @param int       $object_id The post ID.
	 */
	public static function flush_for_link_meta( $meta_ids, $object_id ) {
		if ( get_post_type( $object_id ) === 'lar_link' ) {
			self::flush();
		}
	}

	/**
	 * The current cache version.
	 *
	 * @return int
	 */
	public static function version() {
		return (int) get_option( self::VERSION_OPTION, 0 );
	}

	/**
	 * Get the keyword map.
	 *
	 * `keywords` is the list of keywords in priority order, each one has `keyword`,
	 * `case_sensitive` and `link` (the link ID). `links` holds the link meta by link ID.
	 *
	 * @return array
	 */
	public static function get_map() {
		if ( self::$map !== null ) {
			return self::$map;
		}

		$map = get_transient( self::CACHE_KEY );
		if ( ! is_array( $map ) || ! isset( $map['format'] ) || $map['format'] !== self::FORMAT ) {
			$map = self::build();
			set_transient( self::CACHE_KEY, $map, WEEK_IN_SECONDS );
		}

		self::$map = $map;
		return $map;
	}

	/**
	 * Build the keyword map from the published auto links, the newest first.
	 *
	 * @return array
	 */
	private static function build() {
		$map = array(
			'format'   => self::FORMAT,
			'keywords' => array(),
			'links'    => array(),
		);

		$links = get_posts(
			array(
				'post_type'      => 'lar_link',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		foreach ( $links as $link ) {
			$meta = array();
			foreach ( self::LINK_FIELDS as $field ) {
				$meta[ $field ] = (string) get_post_meta( $link->ID, LAR_LITE_PLUGIN_PREFIX . $field, true );
			}
			// Lite links are external links. Other types come from Affiliate Butler Pro.
			if ( $meta['link_type'] !== 'external' && $meta['link_type'] !== '' ) {
				continue;
			}

			$keywords = get_post_meta( $link->ID, LAR_LITE_PLUGIN_PREFIX . 'keywords', true );
			if ( ! is_array( $keywords ) ) {
				continue;
			}

			$map['links'][ $link->ID ] = $meta;
			foreach ( $keywords as $keyword ) {
				self::add_keyword( $map, $keyword, $meta['is_sensitive'] === 'on', (int) $link->ID );
			}
		}

		return $map;
	}

	/**
	 * Add a keyword to the map.
	 *
	 * @param array $map            The map.
	 * @param mixed $keyword        The keyword as saved.
	 * @param bool  $case_sensitive Whether the keyword matches only its exact case.
	 * @param int   $link_id        The link ID.
	 */
	private static function add_keyword( array &$map, $keyword, $case_sensitive, $link_id ) {
		if ( ! is_string( $keyword ) ) {
			return;
		}
		$keyword = html_entity_decode( stripslashes( wptexturize( $keyword ) ), ENT_QUOTES, 'UTF-8' );
		if ( trim( $keyword ) === '' ) {
			return;
		}

		$map['keywords'][] = array(
			'keyword'        => $keyword,
			'case_sensitive' => $case_sensitive,
			'link'           => $link_id,
		);
	}
}
