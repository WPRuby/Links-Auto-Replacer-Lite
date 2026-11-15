<?php
/**
 * The auto links post type.
 *
 * Links are only edited in the admin: they have no public pages or archives.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Links;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PostType {

	const POST_TYPE = 'lar_link';

	/**
	 * Register the post type.
	 */
	public static function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => _x( 'Auto Links', 'post type general name', 'links-auto-replacer' ),
					'singular_name'      => _x( 'Auto Link', 'post type singular name', 'links-auto-replacer' ),
					'menu_name'          => _x( 'Auto Links', 'admin menu', 'links-auto-replacer' ),
					'name_admin_bar'     => _x( 'Auto Link', 'add new on admin bar', 'links-auto-replacer' ),
					'add_new'            => __( 'Add New Auto Link', 'links-auto-replacer' ),
					'add_new_item'       => __( 'Add New Auto Link', 'links-auto-replacer' ),
					'new_item'           => __( 'New Auto Link', 'links-auto-replacer' ),
					'edit_item'          => __( 'Edit Auto Link', 'links-auto-replacer' ),
					'view_item'          => __( 'View Auto Link', 'links-auto-replacer' ),
					'all_items'          => __( 'All Auto Links', 'links-auto-replacer' ),
					'search_items'       => __( 'Search Auto Links', 'links-auto-replacer' ),
					'not_found'          => __( 'No links found.', 'links-auto-replacer' ),
					'not_found_in_trash' => __( 'No links found in Trash.', 'links-auto-replacer' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'supports'            => array( 'author' ),
				'menu_icon'           => 'dashicons-editor-unlink',
			)
		);
	}
}
