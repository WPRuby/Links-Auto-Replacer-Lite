<?php
/**
 * Tests for the click counts of the short links, and the post type.
 */

namespace Links_Auto_Replacer\Tests;

use Exception;
use LinksAutoReplacer\Links\ShortLinks;

class ClicksTest extends TestCase {

	public function set_up() {
		parent::set_up();
		add_filter(
			'wp_redirect',
			function ( $location ) {
				throw new Exception( 'redirect:' . $location );
			}
		);
		flush_rewrite_rules();
	}

	public function tear_down() {
		unset( $_SERVER['HTTP_USER_AGENT'] );
		parent::tear_down();
	}

	public function test_visits_of_a_short_link_are_counted() {
		$link_id = $this->create_link( array( 'amazon' ), array( 'shrink' => 'on', 'slug' => 'amz' ) );
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh) Firefox/120.0';

		$this->visit( '/go/amz' );
		$this->visit( '/go/amz' );

		$this->assertSame( 2, ShortLinks::clicks( $link_id ) );
	}

	public function test_bots_are_not_counted() {
		$link_id = $this->create_link( array( 'amazon' ), array( 'shrink' => 'on', 'slug' => 'amz' ) );
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

		$this->assertSame( 'redirect:https://example.com/product', $this->visit( '/go/amz' ), 'Bots are still redirected.' );
		$this->assertSame( 0, ShortLinks::clicks( $link_id ) );
	}

	public function test_counting_can_be_turned_off_with_a_filter() {
		$link_id = $this->create_link( array( 'amazon' ), array( 'shrink' => 'on', 'slug' => 'amz' ) );
		add_filter( 'lar_count_click', '__return_false' );

		$this->visit( '/go/amz' );

		$this->assertSame( 0, ShortLinks::clicks( $link_id ) );
	}

	public function test_links_have_no_public_pages() {
		$type = get_post_type_object( 'lar_link' );

		$this->assertFalse( $type->public );
		$this->assertFalse( $type->publicly_queryable );
		$this->assertFalse( $type->has_archive );
		$this->assertTrue( $type->show_ui, 'The CMB2 screens need it until the new admin.' );
	}

	public function test_the_upgrade_routine_runs_once_per_version() {
		update_option( 'lar_version', '2.2.4' );
		$before = \LinksAutoReplacer\Links\KeywordMap::version();

		( new \LinksAutoReplacer\Plugin( LAR_DIR . 'links-auto-replacer.php' ) )->maybe_upgrade();
		$this->assertSame( LAR_VERSION, get_option( 'lar_version' ) );
		$this->assertSame( $before + 1, \LinksAutoReplacer\Links\KeywordMap::version() );

		( new \LinksAutoReplacer\Plugin( LAR_DIR . 'links-auto-replacer.php' ) )->maybe_upgrade();
		$this->assertSame( $before + 1, \LinksAutoReplacer\Links\KeywordMap::version() );
	}

	/**
	 * Visit a URL and run the redirect.
	 *
	 * @param string $path The path.
	 *
	 * @return string `redirect:{location}`, or empty.
	 */
	private function visit( $path ) {
		$this->go_to( home_url( $path ) );
		try {
			ShortLinks::redirect();
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
		return '';
	}
}
