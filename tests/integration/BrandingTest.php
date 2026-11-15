<?php
/**
 * Tests for the WPRuby branding.
 */

namespace Links_Auto_Replacer\Tests;

use LinksAutoReplacer\Admin\Branding;

class BrandingTest extends TestCase {

	private function branding() {
		return new Branding( LAR_DIR . 'links-auto-replacer.php' );
	}

	public function tear_down() {
		unset( $GLOBALS['plugin_page'] );
		parent::tear_down();
	}

	public function test_the_header_is_on_the_plugin_screens_only() {
		$GLOBALS['plugin_page'] = 'links-auto-replacer-settings';
		ob_start();
		$this->branding()->render_header();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'lar-brand-header', $html );
		$this->assertStringContainsString( 'wpruby-logo.svg', $html );
		$this->assertMatchesRegularExpression( '#by <a href="https://wpruby.com"[^>]*>WPRuby<span class="screen-reader-text">[^<]*</span></a> · v' . preg_quote( LAR_VERSION, '#' ) . '#', $html );
		$this->assertStringContainsString( Branding::DOCS_URL, $html );
		$this->assertStringContainsString( Branding::SUPPORT_URL, $html );

		$GLOBALS['plugin_page'] = 'another-plugin';
		ob_start();
		$this->branding()->render_header();
		$this->assertSame( '', ob_get_clean() );
	}

	public function test_plugins_screen_links() {
		$basename = plugin_basename( LAR_DIR . 'links-auto-replacer.php' );

		$meta = $this->branding()->row_meta( array( 'By WPRuby' ), $basename );
		$this->assertStringContainsString( Branding::DOCS_URL, $meta['docs'] );
		$this->assertStringContainsString( Branding::SUPPORT_URL, $meta['support'] );

		$this->assertSame( array( 'By someone' ), $this->branding()->row_meta( array( 'By someone' ), 'hello.php' ), 'Other plugins are left alone.' );
	}

	public function test_logo_is_bundled() {
		$this->assertFileExists( LAR_DIR . 'assets/images/wpruby-logo.svg' );
		$this->assertStringEndsWith( 'assets/images/wpruby-logo.svg', $this->branding()->logo_url() );
	}
}
