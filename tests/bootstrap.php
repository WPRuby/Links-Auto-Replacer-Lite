<?php
/**
 * PHPUnit bootstrap: boots the WordPress test suite with the plugin loaded.
 */

require dirname( __DIR__ ) . '/vendor/autoload.php';

putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' );

$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () {
		require dirname( __DIR__ ) . '/links-auto-replacer.php';
	}
);

require $_tests_dir . '/includes/bootstrap.php';

require __DIR__ . '/integration/TestCase.php';
