<?php
/**
 * Tests for the single pass engine (3.0.0, ported from Affiliate Butler Pro): overlapping
 * keywords, fixes over 2.2, and the keyword map and output caches.
 */

namespace Links_Auto_Replacer\Tests;

use LinksAutoReplacer\Links\KeywordMap;
use LinksAutoReplacer\Links\Replacer;

class EngineTest extends TestCase {

	public function test_overlapping_keywords_the_leftmost_match_wins() {
		$this->create_link( array( 'Amazon Prime' ), array( 'url' => 'https://example.com/prime' ), array( 'post_date' => '2020-01-01 00:00:00' ) );
		$this->create_link( array( 'Prime Video' ), array( 'url' => 'https://example.com/video' ), array( 'post_date' => '2021-01-01 00:00:00' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'Watch Amazon Prime Video now.' ) ) );

		$this->assertSame( array( 'Amazon Prime' ), $this->texts( $links ) );
	}

	public function test_overlapping_keywords_at_the_same_place_the_longest_wins() {
		$this->create_link( array( 'Amazon Prime' ), array( 'url' => 'https://example.com/prime' ), array( 'post_date' => '2020-01-01 00:00:00' ) );
		$this->create_link( array( 'Amazon' ), array( 'url' => 'https://example.com/amazon' ), array( 'post_date' => '2021-01-01 00:00:00' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'Amazon Prime, or just Amazon.' ) ) );

		$this->assertSame( array( 'Amazon Prime', 'Amazon' ), $this->texts( $links ) );
		$this->assertSame( 'https://example.com/prime', $links[0]->getAttribute( 'href' ) );
		$this->assertSame( 'https://example.com/amazon', $links[1]->getAttribute( 'href' ) );
	}

	public function test_the_same_keyword_in_two_links_uses_the_newest_link() {
		$this->create_link( array( 'amazon' ), array( 'url' => 'https://example.com/old' ), array( 'post_date' => '2020-01-01 00:00:00' ) );
		$this->create_link( array( 'amazon' ), array( 'url' => 'https://example.com/new' ), array( 'post_date' => '2021-01-01 00:00:00' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$this->assertSame( 'https://example.com/new', $links[0]->getAttribute( 'href' ) );
	}

	public function test_no_space_is_added_before_links() {
		$this->create_link( array( 'amazon' ) );

		$html = $this->render( $this->create_post( '(amazon)' ) );

		$this->assertMatchesRegularExpression( '#\(<a [^>]+>amazon</a>\)#', $html );
	}

	public function test_text_of_scripts_styles_and_textareas_is_not_linked() {
		$this->create_link( array( 'amazon' ) );

		$html = ( new Replacer( KeywordMap::get_map(), 0 ) )->replace(
			'<script>var amazon = 1;</script><style>.amazon{}</style><textarea>amazon</textarea><p>amazon</p>'
		);

		$this->assertCount( 1, $this->plugin_links( $html ) );
		$this->assertStringContainsString( '<script>var amazon = 1;</script>', $html );
		$this->assertStringContainsString( '<textarea>amazon</textarea>', $html );
	}

	public function test_excluded_html_tags_can_have_spaces_and_any_case() {
		$this->save_settings( array( 'excluded_html_tags' => 'H2, strong ' ) );
		$this->create_link( array( 'amazon' ) );

		$html = $this->render( $this->create_post( '<h2>amazon</h2><p><strong>amazon</strong> amazon</p>' ) );

		$this->assertCount( 1, $this->plugin_links( $html ) );
	}

	public function test_content_without_keywords_is_returned_unchanged() {
		$this->create_link( array( 'amazon' ) );
		$content = "<p>Hello<br>world &amp; <b>friends</b></p>\n<!-- a comment -->";

		$this->assertSame( $content, ( new Replacer( KeywordMap::get_map(), 0 ) )->replace( $content ) );
	}

	public function test_content_where_every_keyword_is_excluded_is_returned_unchanged() {
		$this->create_link( array( 'amazon' ) );
		$content = '<a href="/x">amazon</a><pre>amazon</pre>';

		$this->assertSame( $content, ( new Replacer( KeywordMap::get_map(), 0 ) )->replace( $content ) );
	}

	public function test_case_sensitive_limit_counts_only_the_links() {
		$this->save_settings( array( 'per_post_limit' => 2 ) );
		$this->create_link( array( 'Apple' ), array( 'is_sensitive' => 'on' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'apple apple apple Apple Apple Apple' ) ) );

		$this->assertSame( array( 'Apple', 'Apple' ), $this->texts( $links ) );
	}

	public function test_link_without_a_type_is_an_external_link() {
		$this->create_link( array( 'amazon' ), array( 'link_type' => '' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$this->assertSame( 'https://example.com/product', $links[0]->getAttribute( 'href' ) );
		$this->assertSame( 'nofollow', $links[0]->getAttribute( 'rel' ) );
	}

	public function test_sponsored_and_new_window_links() {
		$this->create_link( array( 'amazon' ), array( 'sponsored' => 'on', 'open_in' => '_blank' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$this->assertSame( 'nofollow sponsored noopener', $links[0]->getAttribute( 'rel' ) );
		$this->assertSame( '_blank', $links[0]->getAttribute( 'target' ) );
	}

	public function test_the_extra_attributes_filter_cannot_replace_the_link() {
		$this->create_link( array( 'amazon' ) );
		add_filter(
			'lar_add_extra_atts',
			function () {
				return 'data-x="1" href="https://evil.example" onclick="alert(1)" class="theirs"';
			}
		);

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$this->assertSame( '1', $links[0]->getAttribute( 'data-x' ) );
		$this->assertSame( 'https://example.com/product', $links[0]->getAttribute( 'href' ) );
		$this->assertSame( '', $links[0]->getAttribute( 'onclick' ) );
		$this->assertSame( 'lar-automated-link', $links[0]->getAttribute( 'class' ) );
	}

	public function test_keyword_map_is_cached() {
		$this->create_link( array( 'amazon' ) );
		$this->render( $this->create_post( 'amazon' ) );

		$map = get_transient( KeywordMap::CACHE_KEY );
		$this->assertSame( 'amazon', $map['keywords'][0]['keyword'] );

		KeywordMap::flush();
		$this->assertFalse( get_transient( KeywordMap::CACHE_KEY ) );
	}

	public function test_output_is_cached_until_something_changes() {
		$this->create_link( array( 'amazon' ) );
		$post_id = $this->create_post( 'amazon' );
		$calls   = 0;
		add_filter(
			'lar_add_extra_atts',
			function ( $attributes ) use ( &$calls ) {
				$calls++;
				return $attributes;
			},
			20
		);

		$first = $this->render( $post_id );
		$this->assertSame( $first, $this->render_again( $post_id ) );
		$this->assertSame( 1, $calls );

		KeywordMap::flush();
		$this->render_again( $post_id );
		$this->assertSame( 2, $calls );
	}

	public function test_changing_a_link_url_updates_the_output() {
		$link_id = $this->create_link( array( 'amazon' ) );
		$post_id = $this->create_post( 'amazon' );
		$this->render( $post_id );

		update_post_meta( $link_id, LAR_LITE_PLUGIN_PREFIX . 'url', 'https://example.com/changed' );

		$links = $this->plugin_links( $this->render_again( $post_id ) );
		$this->assertSame( 'https://example.com/changed', $links[0]->getAttribute( 'href' ) );
	}

	public function test_changing_link_keywords_updates_the_output() {
		$link_id = $this->create_link( array( 'amazon' ) );
		$post_id = $this->create_post( 'amazon ebay' );
		$this->render( $post_id );

		update_post_meta( $link_id, LAR_LITE_PLUGIN_PREFIX . 'keywords', array( 'ebay' ) );

		$this->assertSame( array( 'ebay' ), $this->texts( $this->plugin_links( $this->render_again( $post_id ) ) ) );
	}

	public function test_new_unpublished_and_deleted_links_update_the_output() {
		$post_id = $this->create_post( 'amazon' );
		$this->assertCount( 0, $this->plugin_links( $this->render( $post_id ) ) );

		$link_id = $this->create_link( array( 'amazon' ) );
		$this->assertCount( 1, $this->plugin_links( $this->render_again( $post_id ) ) );

		wp_update_post( array( 'ID' => $link_id, 'post_status' => 'draft' ) );
		$this->assertCount( 0, $this->plugin_links( $this->render_again( $post_id ) ) );

		wp_update_post( array( 'ID' => $link_id, 'post_status' => 'publish' ) );
		$this->assertCount( 1, $this->plugin_links( $this->render_again( $post_id ) ) );

		wp_delete_post( $link_id, true );
		$this->assertCount( 0, $this->plugin_links( $this->render_again( $post_id ) ) );
	}

	public function test_changing_the_settings_updates_the_output() {
		$this->create_link( array( 'amazon' ) );
		$post_id = $this->create_post( '<h2>amazon</h2>' );
		$this->assertCount( 1, $this->plugin_links( $this->render( $post_id ) ) );

		$this->save_settings( array( 'excluded_html_tags' => 'h2' ) );

		$this->assertCount( 0, $this->plugin_links( $this->render_again( $post_id ) ) );
	}

	public function test_turning_the_plugin_off_updates_the_output() {
		$this->create_link( array( 'amazon' ) );
		$post_id = $this->create_post( 'amazon' );
		$this->assertCount( 1, $this->plugin_links( $this->render( $post_id ) ) );

		$this->save_settings( array( 'settings_saved' => 'yes' ) );

		$this->assertCount( 0, $this->plugin_links( $this->render_again( $post_id ) ) );
	}

	public function test_links_are_loaded_once_per_request() {
		for ( $i = 0; $i < 20; $i++ ) {
			$this->create_link( array( 'keyword' . $i ), array( 'url' => 'https://example.com/' . $i ) );
		}
		$posts = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$posts[] = $this->create_post( 'keyword' . $i );
		}
		$this->render( $posts[0] );

		$queries = $GLOBALS['wpdb']->num_queries;
		foreach ( $posts as $post_id ) {
			$this->render_again( $post_id );
		}
		// 2.2 loaded every link and its meta for every post.
		$this->assertLessThan( 20, $GLOBALS['wpdb']->num_queries - $queries );
	}
}
