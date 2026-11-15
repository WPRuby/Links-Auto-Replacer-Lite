<?php
/**
 * Tests for the REST API (links-auto-replacer/v1).
 */

namespace Links_Auto_Replacer\Tests;

use WP_REST_Request;
use WP_REST_Server;

class RestTest extends TestCase {

	public function set_up() {
		parent::set_up();
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	public function test_create_a_link_saves_the_meta_the_engine_reads() {
		$response = $this->request(
			'POST',
			'/links',
			array(
				'keywords'       => array( ' amazon ', 'kindle', '' ),
				'target_url'     => 'https://example.com/product',
				'slug'           => 'Amz Deal',
				'nofollow'       => false,
				'sponsored'      => true,
				'open_in'        => '_blank',
				'case_sensitive' => true,
			)
		);

		$this->assertSame( 201, $response->get_status() );
		$link = $response->get_data();
		$id   = $link['id'];
		$this->assertSame( array( 'amazon', 'kindle' ), $link['keywords'] );
		$this->assertSame( home_url( '/go/amz-deal' ), $link['short_link'] );
		$this->assertSame( 0, $link['clicks'] );

		$this->assertSame( array( 'amazon', 'kindle' ), get_post_meta( $id, '_lar_links_keywords', true ) );
		$this->assertSame( 'external', get_post_meta( $id, '_lar_links_link_type', true ) );
		$this->assertSame( 'amz-deal', get_post_meta( $id, '_lar_links_slug', true ) );
		$this->assertSame( 'on', get_post_meta( $id, '_lar_links_shrink', true ) );
		$this->assertSame( 'on', get_post_meta( $id, '_lar_links_do_follow', true ) );
		$this->assertSame( 'on', get_post_meta( $id, '_lar_links_sponsored', true ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'Get a kindle.' ) ) );
		$this->assertSame( home_url( '/go/amz-deal' ), $links[0]->getAttribute( 'href' ) );
		$this->assertSame( 'sponsored noopener', $links[0]->getAttribute( 'rel' ) );
	}

	public function test_new_links_use_the_default_settings() {
		$this->save_settings( array( 'do_follow' => 'on', 'open_in' => '_blank', 'is_sensitive' => 'on' ) );

		$link = $this->request( 'POST', '/links', array( 'keywords' => array( 'amazon' ), 'target_url' => 'https://example.com' ) )->get_data();

		$this->assertFalse( $link['nofollow'] );
		$this->assertSame( '_blank', $link['open_in'] );
		$this->assertTrue( $link['case_sensitive'] );
		$this->assertSame( 'publish', $link['status'] );
	}

	public function test_a_short_link_slug_is_generated_when_asked() {
		$first  = $this->request( 'POST', '/links', array( 'keywords' => array( 'a' ), 'target_url' => 'https://example.com/a', 'short_link_enabled' => true ) )->get_data();
		$second = $this->request( 'POST', '/links', array( 'keywords' => array( 'b' ), 'target_url' => 'https://example.com/b', 'short_link_enabled' => true ) )->get_data();

		$this->assertNotSame( '', $first['slug'] );
		$this->assertNotSame( $first['slug'], $second['slug'] );
		$this->assertMatchesRegularExpression( '/^[0-9a-z]+$/', $first['slug'] );
	}

	public function test_validation_errors() {
		$this->create_link( array( 'amazon' ), array( 'url' => 'https://example.com/taken', 'slug' => 'taken' ) );

		$errors = $this->request(
			'POST',
			'/links',
			array(
				'keywords'   => array( 'amazon' ),
				'target_url' => 'https://example.com/taken',
				'slug'       => 'taken',
			)
		)->get_data()['data']['errors'];
		$this->assertSame( array( 'keywords', 'target_url', 'slug' ), array_keys( $errors ) );

		$errors = $this->request( 'POST', '/links', array( 'keywords' => array(), 'target_url' => 'javascript:alert(1)' ) )->get_data()['data']['errors'];
		$this->assertSame( array( 'keywords', 'target_url' ), array_keys( $errors ) );
	}

	public function test_update_keeps_the_fields_left_out() {
		$link = $this->request( 'POST', '/links', array( 'keywords' => array( 'amazon' ), 'target_url' => 'https://example.com', 'sponsored' => true ) )->get_data();

		$updated = $this->request( 'PUT', '/links/' . $link['id'], array( 'target_url' => 'https://example.com/new' ) )->get_data();

		$this->assertSame( 'https://example.com/new', $updated['target_url'] );
		$this->assertSame( array( 'amazon' ), $updated['keywords'] );
		$this->assertTrue( $updated['sponsored'] );
	}

	public function test_links_made_with_pro_are_read_only() {
		$id = $this->create_link( array( 'gallery' ), array( 'link_type' => 'popup_gallery' ) );

		$this->assertSame( 'popup_gallery', $this->request( 'GET', '/links/' . $id )->get_data()['pro_type'] );
		$response = $this->request( 'PUT', '/links/' . $id, array( 'keywords' => array( 'other' ) ) );
		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'popup_gallery', get_post_meta( $id, '_lar_links_link_type', true ) );
	}

	public function test_list_trash_and_delete() {
		$id = $this->request( 'POST', '/links', array( 'keywords' => array( 'amazon' ), 'target_url' => 'https://example.com' ) )->get_data()['id'];

		$response = $this->request( 'GET', '/links', array( 'search' => 'amazon' ) );
		$this->assertSame( '1', $response->get_headers()['X-WP-Total'] );

		$this->assertSame( 'trash', $this->request( 'DELETE', '/links/' . $id )->get_data()['status'] );
		$this->assertTrue( $this->request( 'DELETE', '/links/' . $id, array( 'force' => true ) )->get_data()['deleted'] );
		$this->assertNull( get_post( $id ) );
		$this->assertSame( 404, $this->request( 'GET', '/links/' . $id )->get_status() );
	}

	public function test_settings() {
		$values = $this->request( 'GET', '/settings' )->get_data();
		$this->assertTrue( $values['enable'] );

		$values = $this->request( 'POST', '/settings', array( 'enable' => false, 'per_post_limit' => 2, 'open_in' => '_blank', 'unknown' => 'x' ) )->get_data();
		$this->assertFalse( $values['enable'] );
		$this->assertSame( 2, $values['per_post_limit'] );
		$this->assertSame( '_blank', $values['open_in'] );
		$this->assertFalse( lar()->is_enabled() );
		$this->assertArrayNotHasKey( '_lar_links_unknown', get_option( 'lar_settings' ) );

		// Changing another setting keeps "Enable" as it is.
		$this->request( 'POST', '/settings', array( 'enable' => true ) );
		$values = $this->request( 'POST', '/settings', array( 'per_post_limit' => 0 ) )->get_data();
		$this->assertTrue( $values['enable'] );
	}

	public function test_permissions() {
		$link_id = $this->create_link( array( 'amazon' ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertSame( 403, $this->request( 'GET', '/links' )->get_status() );
		$this->assertSame( 403, $this->request( 'PUT', '/links/' . $link_id, array( 'keywords' => array( 'x' ) ) )->get_status() );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertSame( 200, $this->request( 'GET', '/links' )->get_status() );
		$this->assertSame( 403, $this->request( 'GET', '/settings' )->get_status() );
	}

	/**
	 * Call a route.
	 *
	 * @param string $method The method.
	 * @param string $route  The route, without the namespace.
	 * @param array  $params The parameters.
	 *
	 * @return \WP_REST_Response
	 */
	private function request( $method, $route, array $params = array() ) {
		$request = new WP_REST_Request( $method, '/links-auto-replacer/v1' . $route );
		if ( in_array( $method, array( 'POST', 'PUT' ), true ) ) {
			$request->set_header( 'Content-Type', 'application/json' );
			$request->set_body( wp_json_encode( $params ) );
		} else {
			foreach ( $params as $key => $value ) {
				$request->set_param( $key, $value );
			}
		}
		return rest_get_server()->dispatch( $request );
	}
}
