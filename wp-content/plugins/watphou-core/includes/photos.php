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
	$candidates = array( $slug );
	$stripped   = preg_replace( '/-\d+$/', '', $slug );
	if ( is_string( $stripped ) && $stripped !== $slug && '' !== $stripped ) {
		$candidates[] = $stripped;
	}
	foreach ( $candidates as $try ) {
		$rel = 'assets/images/tours/' . $try;
		if ( is_readable( get_theme_file_path( $rel . '/featured.jpg' ) ) || is_readable( get_theme_file_path( $rel . '/g01.jpg' ) ) ) {
			return $rel;
		}
	}
	return 'assets/images/tours/' . $slug;
}

function watphou_core_photos_imported(): bool {
	return '1.7.1' === (string) get_option( 'watphou_september_photos' );
}

function watphou_core_is_legacy_tour_image( string $url ): bool {
	return (bool) preg_match( '#/(tad-fane|vatphou|hero-islands|donkhone|bolaven|liphi|coffee|waterfall)(?:-\d+x\d+)?\.(?:jpe?g|png|webp)#i', $url );
}

/**
 * Unique positive integers from a comma/space list (Quick Edit gallery field).
 *
 * @return int[]
 */
function watphou_core_unique_positive_ids( string $raw ): array {
	$out = array();
	foreach ( preg_split( '/[,\s]+/', trim( $raw ) ) as $part ) {
		$id = (int) $part;
		if ( $id > 0 && ! in_array( $id, $out, true ) ) {
			$out[] = $id;
		}
	}
	return $out;
}

/**
 * @return int[]
 */
function watphou_core_tour_gallery_ids( int $post_id ): array {
	$ids = watphou_core_unique_positive_ids( (string) get_post_meta( $post_id, 'tour_gallery', true ) );
	$ok  = array();
	foreach ( $ids as $id ) {
		if ( function_exists( 'wp_attachment_is_image' ) && ! wp_attachment_is_image( $id ) ) {
			continue;
		}
		$ok[] = $id;
	}
	return $ok;
}

function watphou_core_tour_featured_url( int $post_id, string $size = 'large' ): string {
	$tried = array();
	foreach ( array( $size, 'large', 'full' ) as $try ) {
		if ( isset( $tried[ $try ] ) ) {
			continue;
		}
		$tried[ $try ] = true;
		$native        = get_the_post_thumbnail_url( $post_id, $try );
		if ( $native ) {
			return $native;
		}
	}
	$rel  = watphou_core_tour_theme_dir( $post_id );
	$file = $rel ? get_theme_file_path( $rel . '/featured.jpg' ) : '';
	return ( $file && is_readable( $file ) ) ? get_theme_file_uri( $rel . '/featured.jpg' ) : '';
}

/**
 * @return array<int, array{url:string,alt:string}>
 */
function watphou_core_tour_gallery_urls( int $post_id ): array {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return array();
	}
	$ids = watphou_core_tour_gallery_ids( $post_id );
	if ( $ids ) {
		$out = array();
		foreach ( $ids as $aid ) {
			$full = wp_get_attachment_image_url( $aid, 'full' );
			$url  = wp_get_attachment_image_url( $aid, 'medium_large' );
			if ( ! $url ) {
				$url = wp_get_attachment_image_url( $aid, 'large' );
			}
			if ( ! $url ) {
				continue;
			}
			$alt = (string) get_post_meta( $aid, '_wp_attachment_image_alt', true );
			$out[] = array(
				'url'  => $url,
				'full' => $full ? $full : $url,
				'alt'  => '' !== $alt ? $alt : (string) get_the_title( $post_id ),
			);
		}
		return $out;
	}
	if ( '1' === (string) get_post_meta( $post_id, 'tour_gallery_managed', true ) ) {
		return array();
	}
	$slug     = function_exists( 'watphou_core_tour_catalog_slug' ) ? watphou_core_tour_catalog_slug( $post ) : '';
	$rel      = watphou_core_tour_theme_dir( $post_id );
	$manifest = watphou_core_photo_manifest();
	$row      = array();
	$base     = $rel ? basename( $rel ) : '';
	if ( $base && isset( $manifest['tours'][ $base ] ) && is_array( $manifest['tours'][ $base ] ) ) {
		$row = $manifest['tours'][ $base ];
	} elseif ( $slug && isset( $manifest['tours'][ $slug ] ) && is_array( $manifest['tours'][ $slug ] ) ) {
		$row = $manifest['tours'][ $slug ];
	}
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
		$full_url = get_theme_file_uri( $rel . '/' . $file );
		$small    = preg_replace( '/\.(jpe?g)$/i', '-800w.$1', $file );
		$display  = $full_url;
		if ( is_string( $small ) && $small !== $file && is_readable( get_theme_file_path( $rel . '/' . $small ) ) ) {
			$display = get_theme_file_uri( $rel . '/' . $small );
		}
		$out[] = array(
			'url'  => $display,
			'full' => $full_url,
			'alt'  => (string) ( $item['alt'] ?? get_the_title( $post_id ) ),
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

/**
 * Existing child image whose attached file basename matches (avoid duplicate sideloads).
 */
function watphou_core_find_child_attachment( int $parent_id, string $basename ): int {
	$basename = sanitize_file_name( $basename );
	if ( ! $parent_id || '' === $basename ) {
		return 0;
	}
	$children = get_children(
		array(
			'post_parent'    => $parent_id,
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => 80,
		)
	);
	if ( ! $children ) {
		return 0;
	}
	foreach ( $children as $att ) {
		$file = get_attached_file( (int) $att->ID );
		if ( $file && strtolower( basename( $file ) ) === strtolower( $basename ) ) {
			return (int) $att->ID;
		}
	}
	return 0;
}

/**
 * @return array<int, array{id:int,url:string,large:string}>
 */
function watphou_core_tour_gallery_preview_items( int $post_id ): array {
	$ids = watphou_core_tour_gallery_ids( $post_id );
	$out = array();
	if ( $ids ) {
		foreach ( $ids as $aid ) {
			$url = wp_get_attachment_image_url( $aid, 'thumbnail' );
			if ( ! $url ) {
				$url = wp_get_attachment_image_url( $aid, 'medium' );
			}
			if ( ! $url ) {
				continue;
			}
			$large = wp_get_attachment_image_url( $aid, 'medium_large' );
			if ( ! $large ) {
				$large = wp_get_attachment_image_url( $aid, 'large' );
			}
			if ( ! $large ) {
				$large = $url;
			}
			$out[] = array(
				'id'    => $aid,
				'url'   => $url,
				'large' => $large,
			);
		}
		return $out;
	}
	foreach ( watphou_core_tour_gallery_urls( $post_id ) as $shot ) {
		$out[] = array(
			'id'    => 0,
			'url'   => (string) $shot['url'],
			'large' => (string) ( $shot['full'] ?? $shot['url'] ),
		);
	}
	return $out;
}

function watphou_core_apply_tour_gallery( int $post_id, array $ids ): void {
	$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
	$csv = implode( ',', $ids );
	$tr  = function_exists( 'watphou_core_tour_translation_ids' ) ? watphou_core_tour_translation_ids( $post_id ) : array( $post_id );
	foreach ( $tr as $tid ) {
		update_post_meta( $tid, 'tour_gallery', $csv );
		update_post_meta( $tid, 'tour_gallery_managed', '1' );
	}
}

function watphou_core_gallery_seed_complete(): bool {
	return '1.7.5' === (string) get_option( 'watphou_tour_gallery_seed' );
}

/**
 * Copy theme gallery JPEGs into the Media Library (one tour per call).
 */
function watphou_core_seed_one_tour_gallery( string $slug, array $row ): void {
	if ( ! function_exists( 'watphou_core_find_tour_id' ) ) {
		return;
	}
	$id = watphou_core_find_tour_id( $slug, (string) ( $row['code'] ?? '' ) );
	if ( ! $id ) {
		return;
	}
	if ( watphou_core_tour_gallery_ids( $id ) ) {
		return;
	}
	if ( '1' === (string) get_post_meta( $id, 'tour_gallery_managed', true ) ) {
		return;
	}
	$gids = array();
	$rel  = 'assets/images/tours/' . $slug;
	foreach ( $row['gallery_meta'] ?? array() as $item ) {
		$file = sanitize_file_name( (string) ( $item['file'] ?? '' ) );
		if ( '' === $file ) {
			continue;
		}
		$path = get_theme_file_path( $rel . '/' . $file );
		if ( ! is_readable( $path ) ) {
			continue;
		}
		$found = watphou_core_find_child_attachment( $id, $file );
		if ( $found ) {
			$gids[] = $found;
			continue;
		}
		$attach = watphou_core_sideload_theme_image( $path, $id, (string) ( $item['alt'] ?? $file ) );
		if ( $attach ) {
			$alt = sanitize_text_field( (string) ( $item['alt'] ?? '' ) );
			if ( '' !== $alt ) {
				update_post_meta( $attach, '_wp_attachment_image_alt', $alt );
			}
			$gids[] = $attach;
		}
	}
	if ( ! $gids ) {
		return;
	}
	$csv = implode( ',', $gids );
	$tr  = function_exists( 'watphou_core_tour_translation_ids' ) ? watphou_core_tour_translation_ids( $id ) : array( $id );
	foreach ( $tr as $tid ) {
		update_post_meta( $tid, 'tour_gallery', $csv );
	}
}

/**
 * @return int Remaining tours not yet visited by the seed cursor.
 */
function watphou_core_run_gallery_seed_batch( int $max_tours = 1 ): int {
	if ( watphou_core_gallery_seed_complete() ) {
		return 0;
	}
	if ( ! watphou_core_photos_imported() ) {
		return 0;
	}
	$manifest = watphou_core_photo_manifest();
	$tours    = $manifest['tours'] ?? array();
	if ( ! is_array( $tours ) || ! $tours ) {
		return 0;
	}
	$slugs  = array_keys( $tours );
	$cursor = (int) get_option( 'watphou_tour_gallery_seed_cursor', 0 );
	$total  = count( $slugs );
	if ( $cursor >= $total ) {
		update_option( 'watphou_tour_gallery_seed', '1.7.5', false );
		return 0;
	}
	$done = 0;
	while ( $cursor < $total && $done < $max_tours ) {
		$slug = (string) $slugs[ $cursor ];
		watphou_core_seed_one_tour_gallery( $slug, $tours[ $slug ] );
		++$cursor;
		++$done;
	}
	update_option( 'watphou_tour_gallery_seed_cursor', $cursor, false );
	if ( $cursor >= $total ) {
		update_option( 'watphou_tour_gallery_seed', '1.7.5', false );
		return 0;
	}
	return $total - $cursor;
}

add_action( 'admin_init', 'watphou_core_maybe_seed_tour_galleries', 90 );

function watphou_core_maybe_seed_tour_galleries(): void {
	if ( watphou_core_gallery_seed_complete() ) {
		return;
	}
	if ( wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( ! current_user_can( 'upload_files' ) ) {
		return;
	}
	if ( get_transient( 'watphou_gallery_seed_lock' ) ) {
		return;
	}
	set_transient( 'watphou_gallery_seed_lock', 1, 120 );
	watphou_core_run_gallery_seed_batch( 1 );
	delete_transient( 'watphou_gallery_seed_lock' );
}
