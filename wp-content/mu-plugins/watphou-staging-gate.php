<?php
/**
 * Plugin Name: Watphou Staging Gate
 * Description: HTTP Basic Auth for non-production when credentials are defined in wp-config.php.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WATPHOU_ENV' ) || 'production' === WATPHOU_ENV ) {
	return;
}

if ( ! defined( 'WATPHOU_STAGING_USER' ) || ! defined( 'WATPHOU_STAGING_PASS' ) ) {
	return;
}

if ( '' === (string) WATPHOU_STAGING_USER || '' === (string) WATPHOU_STAGING_PASS ) {
	return;
}

add_action(
	'init',
	static function () {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}
		$user = $_SERVER['PHP_AUTH_USER'] ?? '';
		$pass = $_SERVER['PHP_AUTH_PW'] ?? '';
		if ( hash_equals( (string) WATPHOU_STAGING_USER, (string) $user ) && hash_equals( (string) WATPHOU_STAGING_PASS, (string) $pass ) ) {
			return;
		}
		header( 'WWW-Authenticate: Basic realm="Watphou Travels Staging"' );
		header( 'HTTP/1.0 401 Unauthorized' );
		header( 'X-Robots-Tag: noindex, nofollow', true );
		echo 'Staging site — authentication required.';
		exit;
	},
	0
);
