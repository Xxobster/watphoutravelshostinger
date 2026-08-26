<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_handle_redirects(): void {
	$path = untrailingslashit( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '' );
	if ( ! $path ) {
		return;
	}

	$map = watphou_core_get_redirect_map();
	if ( isset( $map[ $path ] ) ) {
		$target = $map[ $path ];
		if ( '410' === $target ) {
			status_header( 410 );
			nocache_headers();
			include get_query_template( '404' );
			exit;
		}
		wp_safe_redirect( home_url( $target ), 301 );
		exit;
	}
}

function watphou_core_get_redirect_map(): array {
	$cache_key = 'watphou_redirect_map';
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$map  = array();
	$file = ABSPATH . '../content/redirects.csv';
	// Fallback: option stored on import.
	$stored = get_option( 'watphou_redirect_map', array() );
	if ( is_array( $stored ) && $stored ) {
		set_transient( $cache_key, $stored, DAY_IN_SECONDS );
		return $stored;
	}

	set_transient( $cache_key, $map, HOUR_IN_SECONDS );
	return $map;
}

function watphou_core_set_redirect_map( array $map ): void {
	update_option( 'watphou_redirect_map', $map, false );
	delete_transient( 'watphou_redirect_map' );
}
