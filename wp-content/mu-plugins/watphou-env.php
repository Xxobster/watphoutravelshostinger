<?php
/**
 * Plugin Name: Watphou Environment
 * Description: Staging/production hardening, noindex, and environment flags.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hostinger (and similar hosts) terminate HTTPS in front of PHP. Without this,
 * WordPress thinks the request is HTTP and prints insecure http:// links.
 */
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
	$watphou_fwd_proto = strtolower( (string) $_SERVER['HTTP_X_FORWARDED_PROTO'] );
	if ( str_starts_with( $watphou_fwd_proto, 'https' ) ) {
		$_SERVER['HTTPS'] = 'on';
	}
}
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && 'on' === strtolower( (string) $_SERVER['HTTP_X_FORWARDED_SSL'] ) ) {
	$_SERVER['HTTPS'] = 'on';
}
if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
	define( 'FORCE_SSL_ADMIN', true );
}

/**
 * Language follows the Uniform Resource Locator (URL) only.
 * A previous visit to /fr/ must not keep a cookie that then shows French
 * on unprefixed English pages that share the same slug.
 */
if ( ! defined( 'PLL_COOKIE' ) ) {
	define( 'PLL_COOKIE', false );
}

/**
 * Set in wp-config.php on the server (never commit):
 *   define( 'WATPHOU_ENV', 'staging' ); // or 'production'
 * Legacy: WATPHOU_DEMO true forces staging behaviour.
 */
if ( ! defined( 'WATPHOU_ENV' ) ) {
	if ( defined( 'WATPHOU_DEMO' ) && WATPHOU_DEMO ) {
		define( 'WATPHOU_ENV', 'staging' );
	} else {
		define( 'WATPHOU_ENV', 'staging' );
	}
}

if ( ! defined( 'WATPHOU_DEMO' ) ) {
	define( 'WATPHOU_DEMO', 'production' !== WATPHOU_ENV );
}

if ( ! defined( 'WATPHOU_MOCK_CALLBACK_SECRET' ) ) {
	define( 'WATPHOU_MOCK_CALLBACK_SECRET', 'demo-mock-secret-change-on-server' );
}

function watphou_is_non_production(): bool {
	return 'production' !== WATPHOU_ENV;
}

add_action( 'send_headers', function () {
	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains', false );
		header( 'Content-Security-Policy: upgrade-insecure-requests', false );
	}
	if ( watphou_is_non_production() ) {
		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
		header( 'Pragma: no-cache', true );
	}
} );

add_filter( 'pre_option_blog_public', function ( $value ) {
	if ( watphou_is_non_production() ) {
		return '0';
	}
	return $value;
} );

add_action( 'wp_body_open', function () {
	if ( ! watphou_is_non_production() ) {
		return;
	}
	$label = 'staging' === WATPHOU_ENV
		? 'Staging website — not indexed, mock payments only. Do not share publicly.'
		: 'Demonstration website — not indexed, mock payments only';
	echo '<div class="watphou-demo-banner" role="status">' . esc_html( $label ) . '</div>';
}, 1 );

add_filter( 'xmlrpc_enabled', '__return_false' );

add_filter( 'authenticate', function ( $user, $username, $password ) {
	if ( '' === (string) $username ) {
		return $user;
	}
	$ip       = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$key      = 'watphou_login_' . md5( $ip . $username );
	$attempts = (int) get_transient( $key );
	if ( $attempts >= 20 ) {
		return new WP_Error( 'too_many_attempts', __( 'Too many login attempts. Try again later.', 'watphou-travels' ) );
	}
	if ( is_wp_error( $user ) && $password ) {
		set_transient( $key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
	}
	if ( $user instanceof WP_User ) {
		delete_transient( $key );
	}
	return $user;
}, 30, 3 );
