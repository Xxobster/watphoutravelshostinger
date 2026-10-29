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

/**
 * Bump when data/redirects.csv changes: the stored map is rebuilt from the CSV once.
 */
const WATPHOU_REDIRECTS_VERSION = '2026-10-29';

// Old Wix paths must not be "guessed" onto a same-slug post in another language (Thai).
add_filter( 'do_redirect_guess_404_permalink', '__return_false' );

function watphou_core_get_redirect_map(): array {
	$cache_key = 'watphou_redirect_map';
	if ( WATPHOU_REDIRECTS_VERSION !== get_option( 'watphou_redirect_map_version' ) ) {
		delete_option( 'watphou_redirect_map' );
		delete_transient( $cache_key );
		update_option( 'watphou_redirect_map_version', WATPHOU_REDIRECTS_VERSION, false );
	}
	$cached = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$stored = get_option( 'watphou_redirect_map', array() );
	if ( is_array( $stored ) && $stored ) {
		set_transient( $cache_key, $stored, DAY_IN_SECONDS );
		return $stored;
	}

	$map   = array();
	// Plugin copy first: it ships with every deploy, so it is never stale.
	$files = array(
		WATPHOU_CORE_PATH . 'data/redirects.csv',
		WP_CONTENT_DIR . '/../content/redirects.csv',
		dirname( ABSPATH ) . '/content/redirects.csv',
	);
	foreach ( $files as $file ) {
		$file = wp_normalize_path( $file );
		if ( ! is_file( $file ) || ! ( $fh = fopen( $file, 'r' ) ) ) {
			continue;
		}
		fgetcsv( $fh );
		while ( ( $row = fgetcsv( $fh ) ) ) {
			if ( count( $row ) < 3 ) {
				continue;
			}
			$old  = untrailingslashit( $row[0] );
			$new  = $row[1];
			$code = $row[2];
			if ( ! $old || '200' === $code || '/' === $old ) {
				continue;
			}
			$map[ $old ] = ( '410' === $code ) ? '410' : $new;
		}
		fclose( $fh );
		break;
	}

	set_transient( $cache_key, $map, HOUR_IN_SECONDS );
	return $map;
}

function watphou_core_set_redirect_map( array $map ): void {
	update_option( 'watphou_redirect_map', $map, false );
	delete_transient( 'watphou_redirect_map' );
}
