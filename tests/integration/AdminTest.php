<?php
/**
 * Tests for what the plugin registers.
 */

namespace Links_Auto_Replacer\Tests;

class AdminTest extends TestCase {

	public function test_the_post_type_is_registered() {
		$this->assertTrue( post_type_exists( 'lar_link' ) );
		$this->assertFalse( get_post_type_object( 'lar_link' )->public === false && get_post_type_object( 'lar_link' )->show_ui === false );
	}

	public function test_the_content_filter_is_hooked() {
		$this->assertNotFalse( has_filter( 'the_content' ) );
		$this->assertContains( 'go', apply_filters( 'query_vars', array() ) );
	}

	public function test_settings_are_read_from_the_lar_settings_option() {
		$this->save_settings( array( 'per_post_limit' => 3 ) );

		$this->assertSame( 3, lar()->get_option( LAR_LITE_PLUGIN_PREFIX . 'per_post_limit' ) );
	}
}
