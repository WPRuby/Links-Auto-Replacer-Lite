<?php
/**
 * The plugin settings, saved in the `lar_settings` option.
 *
 * The option keeps the format of the CMB2 settings page: every key has the `_lar_links_`
 * prefix and a checked checkbox is `on`. An unchecked checkbox saves nothing, so the
 * "Enable" switch also relies on the `settings_saved` marker, see is_enabled().
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {

	const OPTION = 'lar_settings';

	/**
	 * The settings, without the prefix: name => type (`bool`, `int`, `string` or `open_in`).
	 */
	const FIELDS = array(
		'enable'             => 'bool',
		'do_follow'          => 'bool',
		'sponsored'          => 'bool',
		'open_in'            => 'open_in',
		'shrink'             => 'bool',
		'is_sensitive'       => 'bool',
		'per_post_limit'     => 'int',
		'excluded_html_tags' => 'string',
	);

	/**
	 * A saved setting, as the CMB2 settings page saved it: false when it isn't set.
	 *
	 * @param string $key The setting, with the prefix. Empty for all of them.
	 *
	 * @return mixed
	 */
	public function get_option( $key = '' ) {
		$options = get_option( self::OPTION, array() );
		$options = is_array( $options ) ? $options : array();

		if ( $key === '' ) {
			return $options;
		}
		return array_key_exists( $key, $options ) ? $options[ $key ] : false;
	}

	/**
	 * Whether keywords are linked.
	 *
	 * Once the settings were saved (2.2.5 and later), "Enable" is on only when checked. Before
	 * 2.2.5 the form didn't remember being saved: those sites stay enabled unless it's `off`.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		$enable = $this->get_option( LAR_LITE_PLUGIN_PREFIX . 'enable' );
		if ( $this->get_option( LAR_LITE_PLUGIN_PREFIX . 'settings_saved' ) === 'yes' ) {
			return $enable === 'on';
		}
		return $enable !== 'off';
	}

	/**
	 * The settings with their types, for the REST API.
	 *
	 * @return array
	 */
	public function to_array(): array {
		$values = array();
		foreach ( self::FIELDS as $name => $type ) {
			$value = $this->get_option( LAR_LITE_PLUGIN_PREFIX . $name );
			switch ( $type ) {
				case 'bool':
					$values[ $name ] = $value === 'on';
					break;
				case 'int':
					$values[ $name ] = (int) $value;
					break;
				case 'open_in':
					$values[ $name ] = $value === '_blank' ? '_blank' : '_self';
					break;
				default:
					$values[ $name ] = is_string( $value ) ? $value : '';
			}
		}
		$values['enable'] = $this->is_enabled();
		return $values;
	}

	/**
	 * Save some of the settings.
	 *
	 * @param array $changes The settings to change, without the prefix, typed like to_array().
	 *
	 * @return array The settings after the change, see to_array().
	 */
	public function update( array $changes ): array {
		$options = $this->get_option();
		// "Enable" is stored like the other checkboxes from now on.
		$options[ LAR_LITE_PLUGIN_PREFIX . 'enable' ] = $this->is_enabled() ? 'on' : '';

		foreach ( self::FIELDS as $name => $type ) {
			if ( ! array_key_exists( $name, $changes ) ) {
				continue;
			}
			$value = $changes[ $name ];
			if ( $type === 'bool' ) {
				$value = $value ? 'on' : '';
			}
			$options[ LAR_LITE_PLUGIN_PREFIX . $name ] = $value;
		}
		$options[ LAR_LITE_PLUGIN_PREFIX . 'settings_saved' ] = 'yes';

		update_option( self::OPTION, $this->sanitize( $options ) );
		return $this->to_array();
	}

	/**
	 * Sanitize the settings before they are saved.
	 *
	 * @param mixed $values The settings.
	 *
	 * @return array
	 */
	public function sanitize( $values ): array {
		if ( ! is_array( $values ) ) {
			return array();
		}
		$clean = array();
		foreach ( $values as $key => $value ) {
			$name = (string) preg_replace( '/^' . preg_quote( LAR_LITE_PLUGIN_PREFIX, '/' ) . '/', '', (string) $key );
			switch ( $name ) {
				case 'enable':
				case 'do_follow':
				case 'sponsored':
				case 'shrink':
				case 'is_sensitive':
					$clean[ $key ] = $value === 'off' ? 'off' : ( $value ? 'on' : '' );
					break;
				case 'open_in':
					$clean[ $key ] = $value === '_blank' ? '_blank' : '_self';
					break;
				case 'per_post_limit':
					$clean[ $key ] = absint( $value );
					break;
				case 'settings_saved':
					$clean[ $key ] = $value === 'yes' ? 'yes' : '';
					break;
				default:
					$clean[ $key ] = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
			}
		}
		return $clean;
	}

	/**
	 * Remember that the CMB2 settings form was saved, see is_enabled().
	 *
	 * @param string $object_id The option key.
	 * @param string $cmb_id    The metabox ID.
	 */
	public function remember_settings_saved( $object_id, $cmb_id ): void {
		if ( $object_id !== self::OPTION || $cmb_id !== 'lar_settings' ) {
			return;
		}
		$options = $this->get_option();
		$options[ LAR_LITE_PLUGIN_PREFIX . 'settings_saved' ] = 'yes';
		update_option( self::OPTION, $options );
		// The CMB2 form reads its own copy of the option.
		if ( function_exists( 'cmb2_options' ) ) {
			cmb2_options( self::OPTION )->update( LAR_LITE_PLUGIN_PREFIX . 'settings_saved', 'yes', false );
		}
	}
}
