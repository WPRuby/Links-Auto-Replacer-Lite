<?php
/**
 * Tests for uninstall.php.
 */

namespace Links_Auto_Replacer\Tests;

class UninstallTest extends TestCase {

	public function test_uninstall_removes_the_settings_and_keeps_the_links() {
		$link_id = $this->create_link( array( 'amazon' ), array( 'clicks' => 3 ) );
		update_option( 'lar_settings', array( '_lar_links_enable' => 'on' ) );
		update_option( 'lar_version', LAR_VERSION );
		update_option( 'lar_cache_version', 4 );
		set_transient( 'lar_keyword_map', array( 'format' => 1 ) );

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'links-auto-replacer/links-auto-replacer.php' );
		}
		include LAR_DIR . 'uninstall.php';

		$this->assertFalse( get_option( 'lar_settings' ) );
		$this->assertFalse( get_option( 'lar_version' ) );
		$this->assertFalse( get_option( 'lar_cache_version' ) );
		$this->assertFalse( get_transient( 'lar_keyword_map' ) );
		$this->assertSame( 'publish', get_post_status( $link_id ), 'Affiliate Butler Pro uses the same links.' );
		$this->assertSame( '3', get_post_meta( $link_id, '_lar_links_clicks', true ) );
	}
}
