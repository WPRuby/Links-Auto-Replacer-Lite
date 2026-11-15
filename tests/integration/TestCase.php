<?php
/**
 * Base test case with helpers to create auto links and render posts.
 */

namespace Links_Auto_Replacer\Tests;

use WP_UnitTestCase;

abstract class TestCase extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		$this->set_permalink_structure( '/%postname%/' );
		// CMB2 keeps the options in memory: forget them so every test reads its own database.
		if ( class_exists( 'CMB2_Options' ) ) {
			( new \ReflectionClass( 'CMB2_Options' ) )->setStaticPropertyValue( 'option_sets', array() );
		}
	}

	/**
	 * Create a published auto link.
	 *
	 * @param array $keywords The keywords of the link.
	 * @param array $meta     Link meta without the `_lar_links_` prefix, e.g. `[ 'url' => 'https://example.com' ]`.
	 * @param array $post     Extra post fields.
	 *
	 * @return int The link ID.
	 */
	protected function create_link( array $keywords, array $meta = array(), array $post = array() ) {
		$link_id = self::factory()->post->create(
			array_merge(
				array(
					'post_type'   => 'lar_link',
					'post_status' => 'publish',
					'post_title'  => implode( ', ', $keywords ),
				),
				$post
			)
		);

		$meta = array_merge(
			array(
				'keywords'  => $keywords,
				'link_type' => 'external',
				'url'       => 'https://example.com/product',
				'open_in'   => '_self',
			),
			$meta
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $link_id, LAR_LITE_PLUGIN_PREFIX . $key, $value );
		}

		return $link_id;
	}

	/**
	 * Create a published post.
	 *
	 * @param string $content The post content.
	 * @param array  $post    Extra post fields.
	 *
	 * @return int The post ID.
	 */
	protected function create_post( $content, array $post = array() ) {
		return self::factory()->post->create(
			array_merge(
				array(
					'post_status'  => 'publish',
					'post_content' => $content,
				),
				$post
			)
		);
	}

	/**
	 * Render the content of a post through `the_content`, the way a theme does.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string The rendered content.
	 */
	protected function render( $post_id ) {
		$this->go_to( get_permalink( $post_id ) );
		$GLOBALS['post'] = get_post( $post_id );
		setup_postdata( $GLOBALS['post'] );

		return apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
	}

	/**
	 * Render a post again in the same request. Unlike render() it keeps the object cache,
	 * which go_to() flushes, so the output cache is used.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string The rendered content.
	 */
	protected function render_again( $post_id ) {
		$GLOBALS['post'] = get_post( $post_id );
		setup_postdata( $GLOBALS['post'] );

		return apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
	}

	/**
	 * The text of each anchor.
	 *
	 * @param \DOMElement[] $links The anchors.
	 *
	 * @return string[]
	 */
	protected function texts( array $links ) {
		return array_map(
			function ( $a ) {
				return $a->textContent;
			},
			$links
		);
	}

	/**
	 * Save the plugin settings (the `lar_settings` option).
	 *
	 *
	 * @param array $settings Settings without the `_lar_links_` prefix.
	 */
	protected function save_settings( array $settings ) {
		$values = array();
		foreach ( $settings as $key => $value ) {
			$values[ LAR_LITE_PLUGIN_PREFIX . $key ] = $value;
		}
		update_option( 'lar_settings', $values );
	}

	/**
	 * Get the anchors that the plugin added to some HTML.
	 *
	 * @param string $html The rendered HTML.
	 *
	 * @return \DOMElement[]
	 */
	protected function plugin_links( $html ) {
		$doc = new \DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<?xml encoding="UTF-8"><body>' . $html . '</body>' );
		libxml_clear_errors();

		$links = array();
		foreach ( $doc->getElementsByTagName( 'a' ) as $a ) {
			if ( strpos( (string) $a->getAttribute( 'class' ), 'lar-automated-link' ) !== false ) {
				$links[] = $a;
			}
		}
		return $links;
	}
}
