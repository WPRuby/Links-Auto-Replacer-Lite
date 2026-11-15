<?php
/**
 * Reads and saves auto links.
 *
 * A link is a `lar_link` post, its fields are post meta with the `_lar_links_` prefix, saved
 * the way the CMB2 editor saved them (checkboxes are `on` or empty). Lite links are external
 * links; the other types belong to Affiliate Butler Pro and are left as they are.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Links;

use WP_Error;
use WP_Post;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Repository {

	const STATUSES = array( 'publish', 'draft' );

	/**
	 * A link as an array, for the REST API.
	 *
	 * @param WP_Post $post The link post.
	 *
	 * @return array
	 */
	public static function to_array( WP_Post $post ): array {
		$meta = function ( string $field ) use ( $post ): string {
			$value = get_post_meta( $post->ID, LAR_LITE_PLUGIN_PREFIX . $field, true );
			return is_scalar( $value ) ? (string) $value : '';
		};

		$keywords = get_post_meta( $post->ID, LAR_LITE_PLUGIN_PREFIX . 'keywords', true );
		$slug     = $meta( 'slug' );
		$type     = $meta( 'link_type' );

		return array(
			'id'             => $post->ID,
			'status'         => $post->post_status,
			'date'           => mysql_to_rfc3339( $post->post_date_gmt === '0000-00-00 00:00:00' ? $post->post_date : $post->post_date_gmt ),
			'keywords'       => array_values( array_filter( (array) $keywords, 'is_string' ) ),
			// Links of other types were made with Affiliate Butler Pro.
			'pro_type'       => $type !== '' && $type !== 'external' ? $type : '',
			'target_url'     => $meta( 'url' ),
			'slug'           => $slug,
			'short_link'     => $slug !== '' ? Replacer::short_link( $slug ) : null,
			'nofollow'       => $meta( 'do_follow' ) !== 'on',
			'sponsored'      => $meta( 'sponsored' ) === 'on',
			'open_in'        => $meta( 'open_in' ) === '_blank' ? '_blank' : '_self',
			'case_sensitive' => $meta( 'is_sensitive' ) === 'on',
			'clicks'         => ShortLinks::clicks( $post->ID ),
		);
	}

	/**
	 * Create or update a link.
	 *
	 * @param array    $data    The fields to save, named like to_array(). Fields that are left out
	 *                          keep their value. `short_link_enabled` gets a slug when it has none.
	 * @param int|null $link_id The link to update, null to create one.
	 *
	 * @return int|WP_Error The link ID, or the validation errors by field in `errors`.
	 */
	public static function save( array $data, ?int $link_id = null ) {
		$link = self::prepare( $data, $link_id );
		if ( is_wp_error( $link ) ) {
			return $link;
		}

		if ( ! empty( $data['short_link_enabled'] ) && $link['slug'] === '' ) {
			$link['slug'] = self::new_slug();
		}

		$post = array(
			'post_type'   => PostType::POST_TYPE,
			'post_status' => $link['status'],
			'post_title'  => implode( ', ', $link['keywords'] ),
		);
		if ( $link_id ) {
			$post['ID'] = $link_id;
			$result     = wp_update_post( wp_slash( $post ), true );
		} else {
			$result = wp_insert_post( wp_slash( $post ), true );
		}
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$link_id = (int) $result;

		$meta = array(
			'keywords'     => $link['keywords'],
			'link_type'    => 'external',
			'url'          => $link['target_url'],
			'slug'         => $link['slug'],
			'shrink'       => $link['slug'] !== '' ? 'on' : '',
			'do_follow'    => $link['nofollow'] ? '' : 'on',
			'sponsored'    => $link['sponsored'] ? 'on' : '',
			'open_in'      => $link['open_in'],
			'is_sensitive' => $link['case_sensitive'] ? 'on' : '',
		);
		foreach ( $meta as $field => $value ) {
			if ( $value === '' || $value === array() ) {
				delete_post_meta( $link_id, LAR_LITE_PLUGIN_PREFIX . $field );
			} else {
				update_post_meta( $link_id, LAR_LITE_PLUGIN_PREFIX . $field, wp_slash( $value ) );
			}
		}

		return $link_id;
	}

	/**
	 * The fields a save would store, without saving them.
	 *
	 * @param array    $data    The fields to save, see save().
	 * @param int|null $link_id The link to update, null for a new one.
	 *
	 * @return array|WP_Error The sanitized fields, or the validation errors by field in `errors`.
	 */
	public static function prepare( array $data, ?int $link_id = null ) {
		$current = $link_id ? self::to_array( get_post( $link_id ) ) : self::defaults();
		if ( $link_id && $current['pro_type'] !== '' ) {
			return new WP_Error(
				'lar_pro_link',
				__( 'This link was made with Affiliate Butler Pro. Edit it with Affiliate Butler Pro.', 'links-auto-replacer' ),
				array( 'status' => 400 )
			);
		}

		$link   = self::sanitize( array_merge( $current, array_intersect_key( $data, $current ) ) );
		$errors = self::validate( $link, (int) $link_id );
		if ( ! empty( $errors ) ) {
			return new WP_Error(
				'lar_invalid_link',
				__( 'Please correct the link fields.', 'links-auto-replacer' ),
				array(
					'status' => 400,
					'errors' => $errors,
				)
			);
		}
		return $link;
	}

	/**
	 * The fields of a new link, from the defaults of the settings.
	 *
	 * @return array
	 */
	public static function defaults(): array {
		$settings = lar()->to_array();
		return array(
			'status'         => 'publish',
			'keywords'       => array(),
			'pro_type'       => '',
			'target_url'     => '',
			'slug'           => '',
			'nofollow'       => ! $settings['do_follow'],
			'sponsored'      => $settings['sponsored'],
			'open_in'        => $settings['open_in'],
			'case_sensitive' => $settings['is_sensitive'],
		);
	}

	/**
	 * Sanitize the fields.
	 *
	 * @param array $link The fields.
	 *
	 * @return array
	 */
	private static function sanitize( array $link ): array {
		$keywords = array();
		foreach ( (array) $link['keywords'] as $keyword ) {
			$keyword = is_string( $keyword ) ? trim( sanitize_text_field( $keyword ) ) : '';
			if ( $keyword !== '' ) {
				$keywords[] = $keyword;
			}
		}

		return array(
			'status'         => in_array( $link['status'], self::STATUSES, true ) ? $link['status'] : 'draft',
			'keywords'       => $keywords,
			'pro_type'       => '',
			'target_url'     => is_string( $link['target_url'] ) ? trim( $link['target_url'] ) : '',
			'slug'           => is_string( $link['slug'] ) ? sanitize_title( $link['slug'] ) : '',
			'nofollow'       => (bool) $link['nofollow'],
			'sponsored'      => (bool) $link['sponsored'],
			'open_in'        => $link['open_in'] === '_blank' ? '_blank' : '_self',
			'case_sensitive' => (bool) $link['case_sensitive'],
		);
	}

	/**
	 * Check the fields.
	 *
	 * @param array $link    The sanitized fields.
	 * @param int   $link_id The ID of the link being updated, 0 for a new one.
	 *
	 * @return string[] The error messages by field.
	 */
	private static function validate( array $link, int $link_id ): array {
		$errors = array();

		if ( empty( $link['keywords'] ) ) {
			$errors['keywords'] = __( 'Please add at least one keyword.', 'links-auto-replacer' );
		} elseif ( count( array_unique( $link['keywords'] ) ) < count( $link['keywords'] ) ) {
			$errors['keywords'] = __( 'Please remove the repeated keywords.', 'links-auto-replacer' );
		} else {
			$used = array_intersect( $link['keywords'], self::other_links_values( 'keywords', $link_id ) );
			if ( ! empty( $used ) ) {
				/* translators: %s: the keywords. */
				$errors['keywords'] = sprintf( __( 'These keywords are used by another link: %s', 'links-auto-replacer' ), implode( ', ', $used ) );
			}
		}

		$scheme = wp_parse_url( $link['target_url'], PHP_URL_SCHEME );
		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) || ! wp_parse_url( $link['target_url'], PHP_URL_HOST ) ) {
			$errors['target_url'] = __( 'Please enter a valid URL.', 'links-auto-replacer' );
		} elseif ( in_array( $link['target_url'], self::other_links_values( 'url', $link_id ), true ) ) {
			$errors['target_url'] = __( 'Another link already uses this URL.', 'links-auto-replacer' );
		}

		if ( $link['slug'] !== '' && in_array( $link['slug'], self::other_links_values( 'slug', $link_id ), true ) ) {
			/* translators: %s: the short link slug. */
			$errors['slug'] = sprintf( __( 'Another link already uses the slug %s.', 'links-auto-replacer' ), $link['slug'] );
		}

		return $errors;
	}

	/**
	 * The values of a field in the other published links.
	 *
	 * @param string $field   The field, without the prefix.
	 * @param int    $link_id The link to leave out.
	 *
	 * @return array The values, the arrays of the keywords are merged.
	 */
	private static function other_links_values( string $field, int $link_id ): array {
		global $wpdb;

		$values = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT pm.meta_value FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish' AND p.ID <> %d",
				LAR_LITE_PLUGIN_PREFIX . $field,
				PostType::POST_TYPE,
				$link_id
			)
		);

		$result = array();
		foreach ( $values as $value ) {
			$value  = maybe_unserialize( $value );
			$result = array_merge( $result, is_array( $value ) ? array_filter( $value, 'is_string' ) : array( (string) $value ) );
		}
		return $result;
	}

	/**
	 * A new unique short link slug, from the `last_lar_link_id` counter of the old editor.
	 *
	 * @return string
	 */
	private static function new_slug(): string {
		$used = self::other_links_values( 'slug', 0 );
		$next = (int) get_option( 'last_lar_link_id', 0 ) + 100;
		do {
			$slug = strtolower( self::base62( $next++ ) );
		} while ( in_array( $slug, $used, true ) );
		update_option( 'last_lar_link_id', $next - 100, false );

		return $slug;
	}

	/**
	 * A number in base 62, like the old editor's slugs.
	 *
	 * @param int $number The number.
	 *
	 * @return string
	 */
	private static function base62( int $number ): string {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$encoded    = '';
		do {
			$encoded = $characters[ $number % 62 ] . $encoded;
			$number  = intdiv( $number, 62 );
		} while ( $number > 0 );
		return $encoded;
	}
}
