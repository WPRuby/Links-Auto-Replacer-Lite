<?php
/**
 * Plugin activation.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer;

use LinksAutoReplacer\Links\PostType;
use LinksAutoReplacer\Links\ShortLinks;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activation {

	/**
	 * Import the links of version 1 and add the short links rule.
	 */
	public static function activate(): void {
		self::import_version_1_links();

		PostType::register();
		add_filter( 'rewrite_rules_array', array( ShortLinks::class, 'rewrite_rules' ) );
		flush_rewrite_rules( false );
	}

	/**
	 * Version 1 kept the links in its own table, they become auto links once.
	 */
	private static function import_version_1_links(): void {
		if ( get_option( LAR_LITE_PLUGIN_PREFIX . 'old_data_imported' ) === 'yes' ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'lar_links';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) !== $table ) {
			add_option( LAR_LITE_PLUGIN_PREFIX . 'old_data_imported', 'yes' );
			return;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery -- the table name only, read once.
		$old_links = $wpdb->get_results( 'SELECT * FROM ' . $table );
		foreach ( (array) $old_links as $link ) {
			$link_id = wp_insert_post(
				array(
					'post_title'  => $link->keyword,
					'post_status' => 'publish',
					'post_type'   => PostType::POST_TYPE,
					'post_date'   => gmdate( 'Y-m-d H:i:s', (int) $link->created ),
				)
			);
			if ( ! $link_id ) {
				continue;
			}
			$meta = array(
				'keywords'     => explode( ',', $link->keyword ),
				'url'          => $link->keyword_url,
				'do_follow'    => $link->dofollow,
				'open_in'      => $link->open_in,
				'shrink'       => $link->cloack,
				'slug'         => $link->slug,
				'link_type'    => 'external',
				'is_sensitive' => isset( $link->is_sensitive ) ? $link->is_sensitive : '',
			);
			foreach ( $meta as $key => $value ) {
				add_post_meta( $link_id, LAR_LITE_PLUGIN_PREFIX . $key, $value );
			}
		}
		add_option( LAR_LITE_PLUGIN_PREFIX . 'old_data_imported', 'yes' );
	}
}
