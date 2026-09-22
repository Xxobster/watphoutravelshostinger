<?php
/**
 * Extra public French/Thai chrome. The map lives in data/ui_extras_i18n.json
 * so a syntax error here cannot take the whole site down.
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'watphou_core_load_ui_extras_json' ) ) {
	/**
	 * @return array<string, array{fr?: string, th?: string}>
	 */
	function watphou_core_load_ui_extras_json(): array {
		static $map = null;
		if ( null !== $map ) {
			return $map;
		}
		$map  = array();
		$path = dirname( __DIR__ ) . '/data/ui_extras_i18n.json';
		if ( ! is_readable( $path ) ) {
			return $map;
		}
		$raw = json_decode( (string) file_get_contents( $path ), true );
		return $map = is_array( $raw ) ? $raw : array();
	}
}

if ( ! function_exists( 'watphou_core_extra_ui_i18n' ) ) {
	/**
	 * @return array<string, array{fr?: string, th?: string}>
	 */
	function watphou_core_extra_ui_i18n(): array {
		return watphou_core_load_ui_extras_json();
	}
}
