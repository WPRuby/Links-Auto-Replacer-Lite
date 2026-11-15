<?php
/**
 * Tests for what the plugin registers.
 */

namespace Links_Auto_Replacer\Tests;

use LinksAutoReplacer\Admin\AdminPage;
use LinksAutoReplacer\Admin\DisableOption;

class AdminTest extends TestCase {

	public function test_the_post_type_is_registered() {
		$this->assertTrue( post_type_exists( 'lar_link' ) );
		$this->assertFalse( get_post_type_object( 'lar_link' )->show_ui, 'The links are edited in the plugin\'s own screens.' );
	}

	public function test_the_content_filter_is_hooked() {
		$this->assertNotFalse( has_filter( 'the_content' ) );
		$this->assertContains( 'go', apply_filters( 'query_vars', array() ) );
	}

	public function test_settings_are_read_from_the_lar_settings_option() {
		$this->save_settings( array( 'per_post_limit' => 3 ) );

		$this->assertSame( 3, lar()->get_option( LAR_LITE_PLUGIN_PREFIX . 'per_post_limit' ) );
	}

	public function test_the_menu() {
		global $submenu, $menu;
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'dashboard' );
		$page = new AdminPage( LAR_DIR . 'links-auto-replacer.php' );

		$page->add_menu();
		$page->add_upgrade_link();

		$this->assertContains( AdminPage::SLUG, wp_list_pluck( $menu, 2 ) );
		$this->assertSame(
			array( 'links-auto-replacer', 'links-auto-replacer-new', 'links-auto-replacer-settings', AdminPage::PRO_URL ),
			wp_list_pluck( $submenu[ AdminPage::SLUG ], 2 )
		);
	}

	/**
	 * @dataProvider old_screens
	 *
	 * @param string $pagenow The admin file.
	 * @param array  $get     The query.
	 * @param string $view    The view it opens.
	 */
	public function test_the_old_screens_open_the_app( $pagenow, array $get, $view ) {
		$link_id = $this->create_link( array( 'amazon' ) );
		$get     = array_map(
			function ( $value ) use ( $link_id ) {
				return $value === 'LINK' ? (string) $link_id : $value;
			},
			$get
		);
		$GLOBALS['pagenow'] = $pagenow;
		$_GET               = $get;
		$location           = '';
		add_filter(
			'wp_redirect',
			function ( $url ) use ( &$location ) {
				$location = $url;
				throw new \Exception( 'redirect' );
			}
		);

		try {
			( new AdminPage( LAR_DIR . 'links-auto-replacer.php' ) )->redirect_old_screens();
		} catch ( \Exception $e ) {
			unset( $e );
		}
		$_GET = array();

		$expected = $view === 'edit-link' ? AdminPage::url( 'edit', $link_id ) : AdminPage::url( $view );
		$this->assertSame( $expected, $location );
	}

	public function old_screens() {
		return array(
			'list'          => array( 'edit.php', array( 'post_type' => 'lar_link' ), 'links' ),
			'new link'      => array( 'post-new.php', array( 'post_type' => 'lar_link' ), 'edit' ),
			'edit link'     => array( 'post.php', array( 'post' => 'LINK', 'action' => 'edit' ), 'edit-link' ),
			'settings page' => array( 'edit.php', array( 'post_type' => 'lar_link', 'page' => 'lar_settings' ), 'settings' ),
			'about page'    => array( 'edit.php', array( 'post_type' => 'lar_link', 'page' => 'lar_about_settings' ), 'settings' ),
		);
	}

	public function test_the_disable_switch_is_in_the_rest_api() {
		// The test suite resets the registered meta between tests.
		( new DisableOption( LAR_DIR . 'links-auto-replacer.php' ) )->register_meta();
		$post_id = $this->create_post( 'amazon' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$request = new \WP_REST_Request( 'POST', '/wp/v2/posts/' . $post_id );
		$request->set_param( 'meta', array( DisableOption::META => 'on' ) );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'on', get_post_meta( $post_id, DisableOption::META, true ) );
	}
}
