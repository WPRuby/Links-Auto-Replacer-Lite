<?php
/**
 * Tests for Lite pausing itself when Affiliate Butler Pro is active.
 */

namespace Links_Auto_Replacer\Tests;

use LinksAutoReplacer\Plugin;

class ProTest extends TestCase {

	public function test_pro_is_not_active_by_default() {
		$this->assertFalse( Plugin::pro_is_active() );
	}

	public function test_with_pro_active_nothing_is_registered_but_the_notice() {
		add_filter( 'lar_pro_active', '__return_true' );
		$hooks = array( 'the_content', 'template_redirect', 'rest_api_init', 'init', 'admin_menu' );
		$count = function () use ( $hooks ) {
			$counts = array();
			foreach ( $hooks as $hook ) {
				$counts[ $hook ] = isset( $GLOBALS['wp_filter'][ $hook ] ) ? count( $GLOBALS['wp_filter'][ $hook ]->callbacks, COUNT_RECURSIVE ) : 0;
			}
			return $counts;
		};
		$before = $count();
		$plugin = new Plugin( LAR_DIR . 'links-auto-replacer.php' );

		$plugin->boot();

		$this->assertSame( $before, $count() );
		$this->assertSame( 10, has_action( 'admin_notices', array( $plugin, 'paused_notice' ) ) );
	}

	public function test_the_notice_is_shown_to_admins_on_the_plugins_screen_only() {
		$plugin = new Plugin( LAR_DIR . 'links-auto-replacer.php' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		set_current_screen( 'dashboard' );
		$this->assertSame( '', $this->output( array( $plugin, 'paused_notice' ) ) );

		set_current_screen( 'plugins' );
		$this->assertStringContainsString( 'Affiliate Butler Pro is active', $this->output( array( $plugin, 'paused_notice' ) ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertSame( '', $this->output( array( $plugin, 'paused_notice' ) ) );
		set_current_screen( 'front' );
	}

	/**
	 * What a callback prints.
	 *
	 * @param callable $callback The callback.
	 *
	 * @return string
	 */
	private function output( $callback ) {
		ob_start();
		call_user_func( $callback );
		return (string) ob_get_clean();
	}
}
