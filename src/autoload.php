<?php
/**
 * Autoloader for the LinksAutoReplacer namespace (PSR-4, src/).
 *
 * Composer isn't shipped with the plugin, so the classes are loaded here.
 *
 * @package Links_Auto_Replacer
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	function ( $class ) {
		$prefix = 'LinksAutoReplacer\\';
		if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
			return;
		}

		$file = __DIR__ . '/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);
