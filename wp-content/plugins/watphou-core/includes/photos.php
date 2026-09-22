<?php
/**
 * September 2026 tour photos and homepage hero slides (theme files + optional media import).
 */
defined( 'ABSPATH' ) || exit;

function watphou_core_photo_manifest(): array {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$file = WATPHOU_CORE_PATH . 'data/september_photos.json';
	if ( ! is_readable( $file ) ) {
		$data = array();
		return $data;
	}
	$raw = json_decode( (string) file_get_contents( $file ), true );
	$data = is_array( $raw ) ? $raw : array();
	return $data;
}

/**
 * @return array<int, array{url:string,alt:string}>
 */
function watphou_core_home_hero_slides(): array {
	$manifest = watphou_core_photo_manifest();
	$rows     = $manifest['home_hero'] ?? array();
	$slides   = array();
	foreach ( $rows as $row ) {
		$file = sanitize_file_name( (string) ( $row['file'] ?? '' ) );
		if ( '' === $file ) {
			continue;
		}
		$rel  = 'assets/images/home-hero/' . $file;
		$path = get_theme_file_path( $rel );
		if ( ! is_readable( $path ) ) {
			continue;
		}
		$slides[] = array(
			'url' => get_theme_file_uri( $rel ),
			'alt' => (string) ( $row['alt'] ?? '' ),
		);
	}
	return $slides;
}

function watphou_core_tour_theme_dir( int $post_id ): string {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || ! function_exists( 'watphou_core_tour_catalog_slug' ) ) {
		return '';
	}
	$slug = watphou_core_tour_catalog_slug( $post );
	if ( '' === $slug ) {
		return '';
	}
	return 'assets/images/tours/' . $slug;
}

function watphou_core_photos_imported(): bool {
	return '1.7.1' === (string) get_option( 'watphou_september_photos' );
}

function watphou_core_is_legacy_tour_image( string $url ): bool {
	return (bool) preg_match( '#/(tad-fane|vatphou|hero-islands|donkhone|bolaven|liphi|coffee|waterfall)(?:-\d+x\d+)?\.(?:jpe?g|png|webp)#i', $url );
}

function watphou_core_tour_featured_url( int $post_id, string $size = 'large' ): string {
	$rel       = watphou_core_tour_theme_dir( $post_id );
	$file      = $rel ? get_theme_file_path( $rel . '/featured.jpg' ) : '';
	$theme_url = ( $file && is_readable( $file ) ) ? get_theme_file_uri( $rel . '/featured.jpg' ) : '';
	$native    = get_the_post_thumbnail_url( $post_id, $size );
	if ( $native && ! watphou_core_is_legacy_tour_image( $native ) && ( ! $theme_url || watphou_core_photos_imported() ) ) {
		return $native;
	}
	if ( $theme_url ) {
		return $theme_url;
	}
	return $native ? $native : '';
}

/**
 * @return array<int, array{url:string,alt:string}>
 */
function watphou_core_tour_gallery_urls( int $post_id ): array {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return array();
	}
	$slug = function_exists( 'watphou_core_tour_catalog_slug' ) ? watphou_core_tour_catalog_slug( $post ) : '';
	$manifest = watphou_core_photo_manifest();
	$row      = ( $slug && isset( $manifest['tours'][ $slug ] ) && is_array( $manifest['tours'][ $slug ] ) )
		? $manifest['tours'][ $slug ]
		: array();
	$rel = watphou_core_tour_theme_dir( $post_id );
	$out = array();
	foreach ( $row['gallery_meta'] ?? array() as $item ) {
		$file = sanitize_file_name( (string) ( $item['file'] ?? '' ) );
		if ( '' === $file || '' === $rel ) {
			continue;
		}
		$path = get_theme_file_path( $rel . '/' . $file );
		if ( ! is_readable( $path ) ) {
			continue;
		}
		$out[] = array(
			'url' => get_theme_file_uri( $rel . '/' . $file ),
			'alt' => (string) ( $item['alt'] ?? get_the_title( $post_id ) ),
		);
	}
	return $out;
}

add_action( 'admin_init', 'watphou_core_maybe_import_september_photos', 80 );

function watphou_core_maybe_import_september_photos(): void {
	if ( '1.7.0' === (string) get_option( 'watphou_september_photos' ) ) {
		delete_option( 'watphou_september_photos' );
		delete_option( 'watphou_september_photos_cursor' );
	}
	if ( watphou_core_photos_imported() ) {
		return;
	}
	if ( wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( get_transient( 'watphou_photo_import_lock' ) ) {
		return;
	}
	$manifest = watphou_core_photo_manifest();
	if ( empty( $manifest['tours'] ) || ! is_array( $manifest['tours'] ) ) {
		return;
	}
	set_transient( 'watphou_photo_import_lock', 1, 90 );
	$cursor = (int) get_option( 'watphou_september_photos_cursor', 0 );
	$slugs  = array_keys( $manifest['tours'] );
	$done   = 0;
	while ( $cursor < count( $slugs ) && $done < 2 ) {
		$slug = $slugs[ $cursor ];
		watphou_core_import_tour_photos( $slug, $manifest['tours'][ $slug ] );
		++$cursor;
		++$done;
	}
	update_option( 'watphou_september_photos_cursor', $cursor, false );
	if ( $cursor >= count( $slugs ) ) {
		update_option( 'watphou_september_photos', '1.7.1', false );
	}
	delete_transient( 'watphou_photo_import_lock' );
}

function watphou_core_import_tour_photos( string $slug, array $row ): void {
	if ( ! function_exists( 'watphou_core_find_tour_id' ) ) {
		return;
	}
	$id = watphou_core_find_tour_id( $slug, (string) ( $row['code'] ?? '' ) );
	if ( ! $id ) {
		return;
	}
	$rel = 'assets/images/tours/' . $slug . '/featured.jpg';
	$path = get_theme_file_path( $rel );
	if ( ! is_readable( $path ) ) {
		return;
	}
	$attach = watphou_core_sideload_theme_image( $path, $id, (string) ( $row['featured_alt'] ?? $slug ) );
	if ( $attach ) {
		$ids = function_exists( 'watphou_core_tour_translation_ids' ) ? watphou_core_tour_translation_ids( $id ) : array( $id );
		foreach ( $ids as $tid ) {
			set_post_thumbnail( $tid, $attach );
		}
	}
}

function watphou_core_sideload_theme_image( string $path, int $parent_id, string $title ): int {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( basename( $path ) );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		return 0;
	}
	$file_array = array(
		'name'     => sanitize_file_name( basename( $path ) ),
		'tmp_name' => $tmp,
	);
	$attach_id = media_handle_sideload( $file_array, $parent_id, $title );
	if ( is_wp_error( $attach_id ) ) {
		@unlink( $tmp );
		return 0;
	}
	return (int) $attach_id;
}
