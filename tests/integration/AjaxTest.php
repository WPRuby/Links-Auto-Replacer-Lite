<?php
/**
 * Tests for the link validation AJAX action.
 */

namespace Links_Auto_Replacer\Tests;

use WP_Ajax_UnitTestCase;
use WPAjaxDieContinueException;
use WPAjaxDieStopException;

/**
 * @group ajax
 */
class AjaxTest extends WP_Ajax_UnitTestCase {

	public function set_up() {
		parent::set_up();
		( new \ReflectionClass( 'CMB2_Options' ) )->setStaticPropertyValue( 'option_sets', array() );
	}

	public function test_subscribers_cannot_validate_links() {
		$this->_setRole( 'subscriber' );

		$this->assertSame( '-1', $this->validate( array( 'post_ID' => 1 ) ) );
	}

	public function test_post_id_is_an_integer() {
		$this->_setRole( 'editor' );
		$other = self::factory()->post->create( array( 'post_type' => 'lar_link', 'post_status' => 'publish' ) );
		update_post_meta( $other, LAR_LITE_PLUGIN_PREFIX . 'keywords', array( 'amazon' ) );

		$output = $this->validate(
			array(
				'post_ID'                                 => '0 OR 1=1',
				LAR_LITE_PLUGIN_PREFIX . 'keywords'  => array( 'amazon' ),
				LAR_LITE_PLUGIN_PREFIX . 'link_type' => 'external',
				LAR_LITE_PLUGIN_PREFIX . 'url'       => 'https://example.com',
			)
		);

		$this->assertStringContainsString( 'amazon', $output, 'The duplicate check still runs.' );
		$this->assertSame( '', $GLOBALS['wpdb']->last_error );
	}

	public function test_errors_are_escaped() {
		$this->_setRole( 'editor' );

		$output = $this->validate(
			array(
				'post_ID'                                 => 0,
				LAR_LITE_PLUGIN_PREFIX . 'keywords'  => array( 'a', 'a' ),
				LAR_LITE_PLUGIN_PREFIX . 'link_type' => 'external',
				LAR_LITE_PLUGIN_PREFIX . 'url'       => '"><img src=x onerror=alert(1)>',
			)
		);

		$this->assertStringContainsString( 'lar_errors', $output );
		$this->assertStringNotContainsString( '<img', $output );
	}

	public function test_valid_links_pass() {
		$this->_setRole( 'editor' );

		$output = $this->validate(
			array(
				'post_ID'                                 => 0,
				LAR_LITE_PLUGIN_PREFIX . 'keywords'  => array( 'kindle' ),
				LAR_LITE_PLUGIN_PREFIX . 'link_type' => 'external',
				LAR_LITE_PLUGIN_PREFIX . 'url'       => 'https://example.com/kindle',
			)
		);

		$this->assertSame( '1', $output );
	}

	/**
	 * Call the validation action.
	 *
	 * @param array $link The link form fields.
	 *
	 * @return string The response.
	 */
	private function validate( array $link ) {
		$_POST['security']  = wp_create_nonce( 'my_pre_submit_validation' );
		$_POST['form_data'] = http_build_query( $link );
		try {
			$this->_handleAjax( 'my_pre_submit_validation' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		} catch ( WPAjaxDieStopException $e ) {
			return $e->getMessage();
		}
		return $this->_last_response;
	}
}
