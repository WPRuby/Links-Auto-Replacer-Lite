<?php
/**
 * How keywords are linked in the content today (2.2.4). Keep these passing when the
 * engine is replaced, and change a test only when the change in behaviour is intended.
 */

namespace Links_Auto_Replacer\Tests;

class ReplacerTest extends TestCase {

	public function test_keyword_is_linked_to_the_url() {
		$this->create_link( array( 'amazon' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'Buy it on amazon today.' ) ) );

		$this->assertCount( 1, $links );
		$this->assertSame( 'amazon', $links[0]->textContent );
		$this->assertSame( 'https://example.com/product', $links[0]->getAttribute( 'href' ) );
		$this->assertSame( '_self', $links[0]->getAttribute( 'target' ) );
	}

	public function test_links_are_nofollow_unless_dofollow_is_on() {
		$this->create_link( array( 'amazon' ) );
		$this->create_link( array( 'kindle' ), array( 'url' => 'https://example.com/kindle', 'do_follow' => 'on' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon and kindle' ) ) );

		$this->assertSame( 'nofollow', $links[0]->getAttribute( 'rel' ) );
		$this->assertSame( '', $links[1]->getAttribute( 'rel' ) );
	}

	public function test_open_in_new_window() {
		$this->create_link( array( 'amazon' ), array( 'open_in' => '_blank' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$this->assertSame( '_blank', $links[0]->getAttribute( 'target' ) );
	}

	public function test_keywords_are_case_insensitive_by_default() {
		$this->create_link( array( 'kindle' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'My Kindle, my KINDLE and my kindle.' ) ) );

		$this->assertCount( 3, $links );
		$this->assertSame( 'Kindle', $links[0]->textContent, 'The original case is kept.' );
	}

	public function test_case_insensitive_keywords_without_the_exact_case_in_the_post() {
		$this->markTestIncomplete( 'Known bug in 2.2.4: a case-sensitive pre-check skips the post when the keyword is not also written in its exact case. Fixed by the engine rewrite.' );

		$this->create_link( array( 'kindle' ) );

		$this->assertCount( 1, $this->plugin_links( $this->render( $this->create_post( 'My Kindle.' ) ) ) );
	}

	public function test_case_sensitive_keywords() {
		$this->create_link( array( 'Kindle' ), array( 'is_sensitive' => 'on' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'My Kindle, not my kindle.' ) ) );

		$this->assertCount( 1, $links );
		$this->assertSame( 'Kindle', $links[0]->textContent );
	}

	public function test_every_keyword_of_a_link_is_linked() {
		$this->create_link( array( 'amazon', 'kindle' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon sells the kindle' ) ) );

		$this->assertSame( array( 'amazon', 'kindle' ), array_map( function ( $a ) {
			return $a->textContent;
		}, $links ) );
	}

	public function test_only_whole_words_are_linked() {
		$this->create_link( array( 'amazon' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'The amazonian forest and amazon-like shops.' ) ) );

		$this->assertCount( 0, $links );
	}

	public function test_keywords_inside_links_are_not_linked() {
		$this->create_link( array( 'amazon' ) );

		$html = $this->render( $this->create_post( 'Visit <a href="https://shop.example">the amazon shop</a>.' ) );

		$this->assertCount( 0, $this->plugin_links( $html ) );
		$this->assertStringContainsString( 'href="https://shop.example"', $html );
	}

	public function test_excluded_html_tags() {
		$this->save_settings( array( 'excluded_html_tags' => 'h2,strong' ) );
		$this->create_link( array( 'amazon' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( '<h2>amazon</h2><p><strong>amazon</strong> and amazon</p>' ) ) );

		$this->assertCount( 1, $links );
	}

	public function test_per_post_limit() {
		$this->save_settings( array( 'per_post_limit' => 1 ) );
		$this->create_link( array( 'amazon' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( '<p>amazon</p><p>amazon</p><p>amazon</p>' ) ) );

		$this->assertCount( 1, $links );
	}

	public function test_draft_links_are_not_used() {
		$this->create_link( array( 'amazon' ), array(), array( 'post_status' => 'draft' ) );

		$this->assertCount( 0, $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) ) );
	}

	public function test_auto_links_can_be_disabled_per_post() {
		$this->create_link( array( 'amazon' ) );
		$post_id = $this->create_post( 'amazon' );
		update_post_meta( $post_id, 'lar_disabled', 'on' );

		$this->assertCount( 0, $this->plugin_links( $this->render( $post_id ) ) );
	}

	public function test_auto_links_can_be_disabled_globally() {
		$this->save_settings( array( 'enable' => 'off' ) );
		$this->create_link( array( 'amazon' ) );

		$this->assertCount( 0, $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) ) );
	}

	public function test_short_links() {
		$this->create_link( array( 'amazon' ), array( 'shrink' => 'on', 'slug' => 'amz' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$this->assertSame( site_url() . '/go/amz', $links[0]->getAttribute( 'href' ) );
	}

	public function test_short_links_with_plain_permalinks() {
		$this->set_permalink_structure( '' );
		$this->create_link( array( 'amazon' ), array( 'shrink' => 'on', 'slug' => 'amz' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$this->assertSame( site_url() . '/index.php?go=amz', $links[0]->getAttribute( 'href' ) );
	}

	public function test_content_without_keywords_keeps_its_text() {
		$this->create_link( array( 'amazon' ) );

		$html = $this->render( $this->create_post( 'Nothing to link here.' ) );

		$this->assertCount( 0, $this->plugin_links( $html ) );
		$this->assertStringContainsString( 'Nothing to link here.', $html );
	}

	public function test_multibyte_content() {
		$this->create_link( array( 'café' ) );

		$html  = $this->render( $this->create_post( 'Un café à Paris, déjà vu.' ) );
		$links = $this->plugin_links( $html );

		$this->assertCount( 1, $links );
		// Non-ASCII characters come back as HTML entities, which display the same.
		$this->assertStringContainsString( 'd&eacute;j&agrave; vu', $html );
	}
}
