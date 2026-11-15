<?php
/**
 * The REST API, under `links-auto-replacer/v1`.
 *
 * - `GET    /links`       The auto links (edit_posts).
 * - `POST   /links`       Creates a link (edit_posts).
 * - `GET    /links/{id}`  One link (edit_post).
 * - `PUT    /links/{id}`  Updates a link (edit_post).
 * - `DELETE /links/{id}`  Trashes a link, `force` deletes it (delete_post).
 * - `GET    /settings`, `POST /settings`  The settings (manage_options).
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Rest;

use LinksAutoReplacer\Links\PostType;
use LinksAutoReplacer\Links\Repository;
use LinksAutoReplacer\Settings;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Controller {

	const NAMESPACE = 'links-auto-replacer/v1';

	/**
	 * Register the routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/links',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_links' ),
					'permission_callback' => array( $this, 'can_edit_links' ),
					'args'                => array(
						'page'     => array(
							'type'    => 'integer',
							'default' => 1,
							'minimum' => 1,
						),
						'per_page' => array(
							'type'    => 'integer',
							'default' => 20,
							'minimum' => 1,
							'maximum' => 100,
						),
						'search'   => array(
							'type'    => 'string',
							'default' => '',
						),
						'status'   => array(
							'type'    => 'string',
							'default' => 'any',
							'enum'    => array( 'any', 'publish', 'draft', 'trash' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_link' ),
					'permission_callback' => array( $this, 'can_edit_links' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/links/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_link' ),
					'permission_callback' => array( $this, 'can_edit_link' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_link' ),
					'permission_callback' => array( $this, 'can_edit_link' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_link' ),
					'permission_callback' => array( $this, 'can_delete_link' ),
					'args'                => array(
						'force' => array(
							'type'    => 'boolean',
							'default' => false,
						),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'can_manage_settings' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'can_manage_settings' ),
				),
			)
		);
	}

	/**
	 * Whether the current user can list and create links.
	 *
	 * @return bool
	 */
	public function can_edit_links(): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Whether the current user can edit a link.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return bool
	 */
	public function can_edit_link( WP_REST_Request $request ): bool {
		$post = get_post( (int) $request['id'] );
		return $post && $post->post_type === PostType::POST_TYPE ? current_user_can( 'edit_post', $post->ID ) : current_user_can( 'edit_posts' );
	}

	/**
	 * Whether the current user can delete a link.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return bool
	 */
	public function can_delete_link( WP_REST_Request $request ): bool {
		$post = get_post( (int) $request['id'] );
		return $post && $post->post_type === PostType::POST_TYPE ? current_user_can( 'delete_post', $post->ID ) : current_user_can( 'edit_posts' );
	}

	/**
	 * Whether the current user can change the settings.
	 *
	 * @return bool
	 */
	public function can_manage_settings(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * List the links.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_links( WP_REST_Request $request ): WP_REST_Response {
		$status = (string) $request['status'];
		$query  = new WP_Query(
			array(
				'post_type'      => PostType::POST_TYPE,
				'post_status'    => $status === 'any' ? array( 'publish', 'draft', 'pending', 'private', 'future' ) : $status,
				'posts_per_page' => (int) $request['per_page'],
				'paged'          => (int) $request['page'],
				's'              => (string) $request['search'],
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$items = array();
		foreach ( $query->posts as $post ) {
			if ( $post instanceof WP_Post ) {
				$items[] = Repository::to_array( $post );
			}
		}

		$response = new WP_REST_Response( $items );
		$response->header( 'X-WP-Total', (string) $query->found_posts );
		$response->header( 'X-WP-TotalPages', (string) $query->max_num_pages );
		return $response;
	}

	/**
	 * Get one link.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_link( WP_REST_Request $request ) {
		$post = $this->find_link( (int) $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		return new WP_REST_Response( Repository::to_array( $post ) );
	}

	/**
	 * Create a link.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_link( WP_REST_Request $request ) {
		$link_id = Repository::save( $this->link_fields( $request ) );
		if ( is_wp_error( $link_id ) ) {
			return $link_id;
		}
		return new WP_REST_Response( Repository::to_array( get_post( $link_id ) ), 201 );
	}

	/**
	 * Update a link.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_link( WP_REST_Request $request ) {
		$post = $this->find_link( (int) $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$link_id = Repository::save( $this->link_fields( $request ), $post->ID );
		if ( is_wp_error( $link_id ) ) {
			return $link_id;
		}
		return new WP_REST_Response( Repository::to_array( get_post( $link_id ) ) );
	}

	/**
	 * Trash or delete a link.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_link( WP_REST_Request $request ) {
		$post = $this->find_link( (int) $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$link = Repository::to_array( $post );
		if ( $request['force'] ) {
			wp_delete_post( $post->ID, true );
			return new WP_REST_Response(
				array(
					'deleted'  => true,
					'previous' => $link,
				)
			);
		}

		wp_trash_post( $post->ID );
		return new WP_REST_Response( Repository::to_array( get_post( $post->ID ) ) );
	}

	/**
	 * Get the settings.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings(): WP_REST_Response {
		return new WP_REST_Response( lar()->to_array() );
	}

	/**
	 * Change the settings.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function update_settings( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response( lar()->update( array_intersect_key( $request->get_params(), Settings::FIELDS ) ) );
	}

	/**
	 * The link fields of a request.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return array
	 */
	private function link_fields( WP_REST_Request $request ): array {
		$fields = $request->get_params();
		unset( $fields['id'] );
		return $fields;
	}

	/**
	 * Get an auto link by ID.
	 *
	 * @param int $id The link ID.
	 *
	 * @return WP_Post|WP_Error
	 */
	private function find_link( int $id ) {
		$post = get_post( $id );
		if ( ! $post instanceof WP_Post || $post->post_type !== PostType::POST_TYPE ) {
			return new WP_Error( 'lar_link_not_found', __( 'Link not found.', 'links-auto-replacer' ), array( 'status' => 404 ) );
		}
		return $post;
	}
}
