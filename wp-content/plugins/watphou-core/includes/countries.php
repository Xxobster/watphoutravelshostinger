<?php
/**
 * Country names for the Nationality field (Unicode CLDR names in English, French, Thai).
 */
defined( 'ABSPATH' ) || exit;

/**
 * @return array<int, array{0:string,1:string}> [ISO code, name] sorted for that language.
 */
function watphou_core_countries( string $lang = 'en' ): array {
	static $all = null;
	if ( null === $all ) {
		$path = WATPHOU_CORE_PATH . 'data/countries_i18n.json';
		$raw  = is_readable( $path ) ? json_decode( (string) file_get_contents( $path ), true ) : null;
		$all  = is_array( $raw ) ? $raw : array();
	}
	$lang = in_array( $lang, array( 'en', 'fr', 'th' ), true ) ? $lang : 'en';
	return isset( $all[ $lang ] ) && is_array( $all[ $lang ] ) ? $all[ $lang ] : array();
}

/**
 * English name for a typed nationality when it matches a list entry in any language, else ''.
 */
function watphou_core_country_english_name( string $typed ): string {
	$needle = mb_strtolower( trim( $typed ) );
	if ( '' === $needle ) {
		return '';
	}
	$code = '';
	foreach ( array( 'en', 'fr', 'th' ) as $lang ) {
		foreach ( watphou_core_countries( $lang ) as $row ) {
			if ( mb_strtolower( (string) $row[1] ) === $needle ) {
				$code = (string) $row[0];
				break 2;
			}
		}
	}
	if ( '' === $code ) {
		return '';
	}
	foreach ( watphou_core_countries( 'en' ) as $row ) {
		if ( $row[0] === $code ) {
			return (string) $row[1];
		}
	}
	return '';
}
