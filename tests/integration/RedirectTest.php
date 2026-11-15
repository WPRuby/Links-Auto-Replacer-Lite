<?php
/**
 * Tests for the short links redirect (`/go/{slug}`).
 */

namespace Links_Auto_Replacer\Tests;

use Exception;

class RedirectTest extends TestCase {

	public function set_up() {
		parent::set_up();
		// Stop at the redirect instead of sending headers and exiting.
		add_filter(
			'wp_redirect',
			function ( $location ) {
				throw new Exception( 'redirect:' . $location );
			}
		);
		flush_rewrite_rules();
	}

	public function test_short_link_redirects_to_the_url() {
		$this->create_link( array( 'amazon' ), array( 'shrink' => 'on', 'slug' => 'amz', 'url' => 'https://example.com/?a=1&b=2' ) );

		$this->assertSame( 'redirect:https://example.com/?a=1&b=2', $this->visit( '/go/amz' ) );
		$this->assertSame( 1, did_action( 'lar_link_redirected' ) );
	}

	public function test_short_link_with_plain_permalinks() {
		$this->set_permalink_structure( '' );
		$this->create_link( array( 'amazon' ), array( 'shrink' => 'on', 'slug' => 'amz' ) );

		$this->assertSame( 'redirect:https://example.com/product', $this->visit( '/?go=amz' ) );
	}

	public function test_pages_without_a_short_link_do_not_redirect() {
		$this->create_link( array( 'amazon' ), array( 'shrink' => 'on', 'slug' => 'amz' ) );

		$this->assertSame( '', $this->visit( '/' ) );
	}

	/**
	 * Visit a URL and run the plugin's redirect.
	 *
	 * @param string $path The path to visit.
	 *
	 * @return string `redirect:{location}`, or an empty string when there was no redirect.
	 */
	private function visit( $path ) {
		$this->go_to( home_url( $path ) );
		try {
			// The plugin's redirect. WordPress's canonical redirect would add a trailing slash first.
			\LinksAutoReplacer\Links\ShortLinks::redirect();
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
		return '';
	}
}
