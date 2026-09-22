<?php
/**
 * Plugin Name: Watphou public hosts
 * Description: Serve the site on watphoutravels.site and the Hostinger preview hostname. Do not send visitors to a hostname their DNS cannot resolve.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hostnames that may serve this WordPress install.
 *
 * The Hostinger preview name (*.hostingersite.com) does not resolve on some
 * visitor networks (NXDOMAIN). The registered staging domain is the public URL.
 *
 * @return list<string>
 */
function watphou_allowed_public_hosts(): array {
	return array(
		'watphoutravels.site',
		'www.watphoutravels.site',
		'darkslategray-snake-182151.hostingersite.com',
	);
}

/**
 * HTTP Host of the current request, lowercase, without port.
 */
function watphou_request_host(): string {
	$host = strtolower( (string) ( $_SERVER['HTTP_HOST'] ?? '' ) );
	return (string) preg_replace( '/:\d+$/', '', $host );
}

/**
 * Prefer the hostname the visitor actually used.
 *
 * @param mixed $url Existing home or siteurl value.
 * @return mixed
 */
function watphou_filter_public_url( $url ) {
	$host = watphou_request_host();
	if ( in_array( $host, watphou_allowed_public_hosts(), true ) ) {
		return 'https://' . $host;
	}
	return $url;
}

/**
 * Rewrite absolute URLs that point at another allowed host onto the current host.
 */
function watphou_rewire_absolute_urls( string $value ): string {
	$host = watphou_request_host();
	if ( ! in_array( $host, watphou_allowed_public_hosts(), true ) ) {
		return $value;
	}
	$current = 'https://' . $host;
	foreach ( watphou_allowed_public_hosts() as $old_host ) {
		if ( $old_host === $host ) {
			continue;
		}
		$value = str_replace( 'https://' . $old_host, $current, $value );
		$value = str_replace( 'http://' . $old_host, $current, $value );
		$value = str_replace( 'https:\\/\\/' . $old_host, 'https:\\/\\/' . $host, $value );
		$value = str_replace( 'http:\\/\\/' . $old_host, 'https:\\/\\/' . $host, $value );
		$value = str_replace( '//' . $old_host, '//' . $host, $value );
	}
	return $value;
}

add_filter( 'pre_option_home', 'watphou_filter_public_url', 20 );
add_filter( 'pre_option_siteurl', 'watphou_filter_public_url', 20 );

add_action(
	'template_redirect',
	static function () {
		if ( is_ssl() ) {
			return;
		}
		$host = watphou_request_host();
		if ( ! in_array( $host, watphou_allowed_public_hosts(), true ) ) {
			return;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '/';
		wp_safe_redirect( 'https://' . $host . $uri, 301 );
		exit;
	},
	0
);

add_filter( 'pll_check_canonical_url', '__return_false' );

add_filter(
	'wp_redirect',
	static function ( $location ) {
		if ( ! is_string( $location ) || '' === $location ) {
			return $location;
		}
		$from = watphou_request_host();
		$to   = strtolower( (string) wp_parse_url( $location, PHP_URL_HOST ) );
		if (
			$from && $to && $from !== $to
			&& in_array( $from, watphou_allowed_public_hosts(), true )
			&& in_array( $to, watphou_allowed_public_hosts(), true )
		) {
			return false;
		}
		return $location;
	},
	1
);

add_filter(
	'allowed_redirect_hosts',
	static function ( $hosts ) {
		return array_values( array_unique( array_merge( (array) $hosts, watphou_allowed_public_hosts() ) ) );
	}
);

add_filter(
	'redirect_canonical',
	static function ( $redirect_url, $requested_url ) {
		if ( ! is_string( $redirect_url ) || ! is_string( $requested_url ) ) {
			return $redirect_url;
		}
		$from = wp_parse_url( $requested_url, PHP_URL_HOST );
		$to   = wp_parse_url( $redirect_url, PHP_URL_HOST );
		if ( $from && $to && $from !== $to && in_array( strtolower( (string) $from ), watphou_allowed_public_hosts(), true ) ) {
			return false;
		}
		return $redirect_url;
	},
	20,
	2
);

add_filter(
	'script_loader_src',
	static function ( $src ) {
		return is_string( $src ) ? watphou_rewire_absolute_urls( $src ) : $src;
	}
);
add_filter(
	'style_loader_src',
	static function ( $src ) {
		return is_string( $src ) ? watphou_rewire_absolute_urls( $src ) : $src;
	}
);
add_filter(
	'wp_get_attachment_url',
	static function ( $url ) {
		return is_string( $url ) ? watphou_rewire_absolute_urls( $url ) : $url;
	}
);

add_action(
	'template_redirect',
	static function () {
		if ( is_admin() ) {
			return;
		}
		ob_start( 'watphou_rewire_absolute_urls' );
	},
	0
);
