<?php
/**
 * WordPress test suite configuration.
 *
 * The database settings come from environment variables (used by CI). For a local
 * setup, copy them into tests/wp-tests-config-local.php, which is git-ignored.
 */

if ( file_exists( __DIR__ . '/wp-tests-config-local.php' ) ) {
	require __DIR__ . '/wp-tests-config-local.php';
}

defined( 'ABSPATH' ) || define( 'ABSPATH', dirname( __DIR__ ) . '/vendor/roots/wordpress-no-content/' );

defined( 'DB_NAME' ) || define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'wordpress_tests' );
defined( 'DB_USER' ) || define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'root' );
defined( 'DB_PASSWORD' ) || define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ?: 'root' );
defined( 'DB_HOST' ) || define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: '127.0.0.1' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// The test suite drops and recreates every table with this prefix.
$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
define( 'WP_DEBUG', true );
