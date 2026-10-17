<?php
/**
 * Plugin Name: Watphou Hardening
 * Description: Hides the admin username from anonymous visitors, removes the WordPress version tag, and adds basic browser security headers.
 *
 * Added by the October 2026 audit. Safe to remove: delete this file to undo.
 */

defined( 'ABSPATH' ) || exit;

// Anonymous visitors cannot list users through the REST API (it exposed "wptadmin").
add_filter(
	'rest_endpoints',
	static function ( $endpoints ) {
		if ( is_user_logged_in() ) {
			return $endpoints;
		}
		foreach ( array_keys( $endpoints ) as $route ) {
			if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
		return $endpoints;
	}
);

// /?author=1 must not reveal the username through a redirect to /author/<name>/.
add_action(
	'template_redirect',
	static function () {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}
		if ( isset( $_GET['author'] ) || is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);

// Do not advertise the WordPress version.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

add_action(
	'send_headers',
	static function () {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	}
);
