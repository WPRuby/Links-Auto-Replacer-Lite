<?php
/**
 * Tests for the fixes of the 2.2.5 security release.
 */

namespace Links_Auto_Replacer\Tests;

use Exception;

class SecurityTest extends TestCase {

	public function test_escaped_text_in_the_content_stays_escaped() {
		$this->create_link( array( 'amazon' ) );

		$html = $this->render( $this->create_post( '&lt;script&gt;alert(1)&lt;/script&gt; Buy on amazon.' ) );

		$this->assertCount( 1, $this->plugin_links( $html ) );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '&lt;script&gt;', $html );
	}

	public function test_links_have_only_their_own_attributes() {
		$this->create_link( array( 'amazon' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$names = array();
		foreach ( $links[0]->attributes as $attribute ) {
			$names[] = $attribute->name;
		}
		$this->assertSame( array( 'class', 'href', 'rel', 'target' ), $names, '2.2.4 added the link ID as an attribute.' );
	}

	public function test_the_extra_attributes_filter() {
		$this->create_link( array( 'amazon' ) );
		add_filter(
			'lar_add_extra_atts',
			function () {
				return 'data-campaign="spring"';
			}
		);

		$links = $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) );

		$this->assertSame( 'spring', $links[0]->getAttribute( 'data-campaign' ) );
	}

	public function test_unsafe_urls_and_targets() {
		$this->create_link( array( 'amazon' ), array( 'url' => 'javascript:alert(1)' ) );
		$this->create_link( array( 'kindle' ), array( 'url' => 'https://example.com/"onmouseover="alert(1)', 'open_in' => '" onclick="alert(1)' ) );

		$html  = $this->render( $this->create_post( 'amazon and kindle' ) );
		$links = $this->plugin_links( $html );

		$this->assertCount( 1, $links, 'A javascript: URL is not linked.' );
		$this->assertSame( '', $links[0]->getAttribute( 'onmouseover' ) );
		$this->assertSame( '', $links[0]->getAttribute( 'onclick' ) );
		$this->assertSame( '_self', $links[0]->getAttribute( 'target' ) );
	}

	public function test_code_blocks_are_not_linked() {
		$this->create_link( array( 'amazon' ) );

		$links = $this->plugin_links( $this->render( $this->create_post( '<pre>amazon</pre><p>amazon</p>' ) ) );

		$this->assertCount( 1, $links );
	}

	public function test_pro_link_types_are_skipped() {
		$this->create_link( array( 'amazon' ), array( 'link_type' => 'popup' ) );
		$this->create_link( array( 'kindle' ), array( 'link_type' => 'internal_pro' ) );

		$this->assertCount( 0, $this->plugin_links( $this->render( $this->create_post( 'amazon and kindle' ) ) ) );
	}

	/**
	 * @dataProvider enable_settings
	 *
	 * @param array $settings The saved settings, without the prefix.
	 * @param bool  $enabled  Whether keywords are linked.
	 */
	public function test_the_enable_setting( array $settings, $enabled ) {
		$this->save_settings( $settings );
		$this->create_link( array( 'amazon' ) );

		$this->assertSame( $enabled, lar()->is_enabled() );
		$this->assertCount( $enabled ? 1 : 0, $this->plugin_links( $this->render( $this->create_post( 'amazon' ) ) ) );
	}

	public function enable_settings() {
		return array(
			'never saved'                  => array( array(), true ),
			'saved before 2.2.5, on'       => array( array( 'enable' => 'on' ), true ),
			'saved before 2.2.5, off'      => array( array( 'enable' => 'off' ), false ),
			'saved before 2.2.5, no value' => array( array( 'do_follow' => 'on' ), true ),
			'saved, on'                    => array( array( 'enable' => 'on', 'settings_saved' => 'yes' ), true ),
			'saved, unchecked'             => array( array( 'settings_saved' => 'yes' ), false ),
		);
	}

	public function test_settings_are_sanitized() {
		$p     = LAR_LITE_PLUGIN_PREFIX;
		$clean = lar()->sanitize(
			array(
				$p . 'enable'             => 'on',
				$p . 'do_follow'          => '',
				$p . 'open_in'            => '"><script>',
				$p . 'per_post_limit'     => '-3',
				$p . 'excluded_html_tags' => 'h1,<script>h2',
			)
		);

		$this->assertSame( 'on', $clean[ $p . 'enable' ] );
		$this->assertSame( '', $clean[ $p . 'do_follow' ] );
		$this->assertSame( '_self', $clean[ $p . 'open_in' ] );
		$this->assertSame( 3, $clean[ $p . 'per_post_limit' ] );
		$this->assertSame( 'h1,h2', $clean[ $p . 'excluded_html_tags' ] );
		$this->assertSame( array(), lar()->sanitize( 'nope' ) );
	}

	/**
	 * @dataProvider redirects_that_do_not_happen
	 *
	 * @param string $slug        The slug visited.
	 * @param array  $link_meta   The meta of the link.
	 * @param string $post_status The status of the link.
	 */
	public function test_redirects_that_do_not_happen( $slug, array $link_meta, $post_status ) {
		$this->create_link( array( 'amazon' ), array_merge( array( 'shrink' => 'on', 'slug' => 'amz' ), $link_meta ), array( 'post_status' => $post_status ) );
		add_filter(
			'wp_redirect',
			function ( $location ) {
				throw new Exception( 'redirect:' . $location );
			}
		);
		$this->set_permalink_structure( '' );
		$this->go_to( home_url( '/?go=' . $slug ) );

		try {
			do_action( 'template_redirect' );
			$result = '';
		} catch ( Exception $e ) {
			$result = $e->getMessage();
		}
		$this->assertSame( '', $result );
	}

	public function redirects_that_do_not_happen() {
		return array(
			'unknown slug'      => array( 'nope', array(), 'publish' ),
			'draft link'        => array( 'amz', array(), 'draft' ),
			'query injection'   => array( rawurlencode( 'amz&post_status=draft' ), array(), 'draft' ),
			'empty url'         => array( 'amz', array( 'url' => '' ), 'publish' ),
			'javascript url'    => array( 'amz', array( 'url' => 'javascript:alert(1)' ), 'publish' ),
		);
	}

	public function test_the_disable_box_needs_a_valid_nonce_and_the_capability() {
		$post_id = $this->create_post( 'amazon' );
		$admin   = new \LinksAutoReplacer\Admin\DisableOption( LAR_DIR . 'links-auto-replacer.php' );
		$nonce   = function () {
			return wp_create_nonce( \LinksAutoReplacer\Admin\DisableOption::NONCE );
		};

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$_POST = array( 'lar_disabled' => 'on' );
		$admin->save_meta_box( $post_id );
		$this->assertSame( '', get_post_meta( $post_id, 'lar_disabled', true ), 'No nonce.' );

		$_POST[ \LinksAutoReplacer\Admin\DisableOption::NONCE ] = 'wrong';
		$admin->save_meta_box( $post_id );
		$this->assertSame( '', get_post_meta( $post_id, 'lar_disabled', true ), 'A wrong nonce (2.2.4 accepted it).' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$_POST[ \LinksAutoReplacer\Admin\DisableOption::NONCE ] = $nonce();
		$admin->save_meta_box( $post_id );
		$this->assertSame( '', get_post_meta( $post_id, 'lar_disabled', true ), 'A user who cannot edit the post.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$_POST[ \LinksAutoReplacer\Admin\DisableOption::NONCE ] = $nonce();
		$admin->save_meta_box( $post_id );
		$this->assertSame( 'on', get_post_meta( $post_id, 'lar_disabled', true ) );
		$_POST = array();
	}
}
