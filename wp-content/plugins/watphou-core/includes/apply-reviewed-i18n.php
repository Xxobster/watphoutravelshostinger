<?php
/**
 * Apply reviewed French / Thai texts from data/reviewed_en_fr_th.json.
 * Re-runs when the JSON file hash changes (future Excel updates).
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'watphou_core_maybe_apply_reviewed_i18n', 55 );
add_filter( 'gettext', 'watphou_core_filter_reviewed_gettext', 20, 3 );
add_filter( 'gettext_with_context', 'watphou_core_filter_reviewed_gettext_ctx', 20, 4 );

function watphou_core_find_page_id( string $slug ): int {
	$ids = get_posts(
		array(
			'post_type'      => 'page',
			'name'           => $slug,
			'posts_per_page' => 5,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'lang'           => '',
		)
	);
	return watphou_core_prefer_english_tour( $ids );
}

function watphou_core_reviewed_json_path(): string {
	return WATPHOU_CORE_PATH . 'data/reviewed_en_fr_th.json';
}

function watphou_core_maybe_apply_reviewed_i18n(): void {
	if ( wp_installing() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	$path = watphou_core_reviewed_json_path();
	if ( ! is_readable( $path ) || ! function_exists( 'PLL' ) ) {
		return;
	}
	$hash = md5_file( $path );
	if ( ! is_string( $hash ) || $hash === (string) get_option( 'watphou_reviewed_i18n_hash', '' ) ) {
		return;
	}
	if ( get_transient( 'watphou_applying_reviewed_i18n' ) ) {
		return;
	}
	set_transient( 'watphou_applying_reviewed_i18n', 1, 90 );
	if ( function_exists( 'ignore_user_abort' ) ) {
		ignore_user_abort( true );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 );
	}

	$pending = (string) get_option( 'watphou_reviewed_i18n_pending_hash', '' );
	if ( $pending !== $hash ) {
		update_option( 'watphou_reviewed_i18n_pending_hash', $hash, false );
		update_option( 'watphou_reviewed_i18n_step', 'ui', false );
		delete_option( 'watphou_reviewed_i18n_pkg_offset' );
	}

	$rows = watphou_core_reviewed_rows();
	$step = (string) get_option( 'watphou_reviewed_i18n_step', 'ui' );
	if ( 'ui' === $step ) {
		watphou_core_store_ui_map( $rows );
		watphou_core_apply_reviewed_duration_terms();
		watphou_core_apply_reviewed_destinations( $rows );
		update_option( 'watphou_reviewed_i18n_step', 'pages', false );
	} elseif ( 'pages' === $step ) {
		watphou_core_apply_reviewed_pages( $rows );
		update_option( 'watphou_reviewed_i18n_step', 'packages', false );
	} else {
		$slugs  = watphou_core_reviewed_package_slugs( $rows );
		$offset = (int) get_option( 'watphou_reviewed_i18n_pkg_offset', 0 );
		$chunk  = array_slice( $slugs, $offset, 4 );
		foreach ( $chunk as $slug ) {
			watphou_core_apply_reviewed_package_slug( $rows, $slug );
		}
		$offset += count( $chunk );
		if ( $offset >= count( $slugs ) ) {
			update_option( 'watphou_reviewed_i18n_hash', $hash, false );
			delete_option( 'watphou_reviewed_i18n_pending_hash' );
			delete_option( 'watphou_reviewed_i18n_step' );
			delete_option( 'watphou_reviewed_i18n_pkg_offset' );
		} else {
			update_option( 'watphou_reviewed_i18n_pkg_offset', $offset, false );
		}
	}
	delete_transient( 'watphou_applying_reviewed_i18n' );
}

/**
 * @return array<int, array<string, mixed>>
 */
function watphou_core_reviewed_rows(): array {
	static $rows = null;
	if ( null !== $rows ) {
		return $rows;
	}
	$path = watphou_core_reviewed_json_path();
	if ( ! is_readable( $path ) ) {
		$rows = array();
		return $rows;
	}
	$decoded = json_decode( (string) file_get_contents( $path ), true );
	$rows    = is_array( $decoded ) ? $decoded : array();
	return $rows;
}

function watphou_core_reviewed_slug( string $location ): string {
	$location = trim( $location );
	if ( preg_match( '/\s([a-z0-9-]+)$/i', $location, $m ) ) {
		return sanitize_title( $m[1] );
	}
	return sanitize_title( $location );
}

function watphou_core_apply_reviewed_i18n(): void {
	$rows = watphou_core_reviewed_rows();
	if ( ! $rows ) {
		return;
	}
	watphou_core_store_ui_map( $rows );
	watphou_core_apply_reviewed_pages( $rows );
	watphou_core_apply_reviewed_destinations( $rows );
	watphou_core_apply_reviewed_duration_terms();
	watphou_core_apply_reviewed_packages( $rows );
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function watphou_core_store_ui_map( array $rows ): void {
	$map = array( 'fr' => array(), 'th' => array() );
	foreach ( $rows as $row ) {
		if ( ( $row['kind'] ?? '' ) !== 'ui' ) {
			continue;
		}
		$en = (string) ( $row['en'] ?? '' );
		if ( '' === $en ) {
			continue;
		}
		foreach ( array( 'fr', 'th' ) as $lang ) {
			$val = trim( (string) ( $row[ $lang ] ?? '' ) );
			if ( '' !== $val ) {
				$map[ $lang ][ $en ] = $val;
			}
		}
	}
	update_option( 'watphou_ui_i18n', $map, false );
}

function watphou_core_ui_map(): array {
	$map = get_option( 'watphou_ui_i18n', array() );
	return is_array( $map ) ? $map : array();
}

function watphou_core_filter_reviewed_gettext( $translated, $text, $domain ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $translated;
	}
	if ( ! in_array( $domain, array( 'watphou-travels', 'watphou-core', 'watphou-bookings' ), true ) ) {
		return $translated;
	}
	$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	if ( 'en' === $lang ) {
		return $translated;
	}
	$map = watphou_core_ui_map();
	if ( ! empty( $map[ $lang ][ $text ] ) ) {
		return (string) $map[ $lang ][ $text ];
	}
	return $translated;
}

function watphou_core_filter_reviewed_gettext_ctx( $translated, $text, $context, $domain ) {
	unset( $context );
	return watphou_core_filter_reviewed_gettext( $translated, $text, $domain );
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function watphou_core_apply_reviewed_pages( array $rows ): void {
	$by_slug = array();
	foreach ( $rows as $row ) {
		if ( ( $row['kind'] ?? '' ) !== 'page' ) {
			continue;
		}
		$slug = sanitize_title( (string) ( $row['location'] ?? '' ) );
		if ( '' === $slug ) {
			continue;
		}
		$by_slug[ $slug ][] = $row;
	}
	foreach ( $by_slug as $slug => $parts ) {
		$en_id = watphou_core_find_page_id( $slug );
		if ( ! $en_id ) {
			continue;
		}
		foreach ( array( 'fr', 'th' ) as $lang ) {
			$copies = array();
			foreach ( $parts as $row ) {
				$val = trim( (string) ( $row[ $lang ] ?? '' ) );
				if ( '' !== $val ) {
					$copies[] = $val;
				}
			}
			if ( ! $copies ) {
				continue;
			}
			$title   = array_shift( $copies );
			$content = '';
			foreach ( $copies as $para ) {
				$content .= '<!-- wp:paragraph --><p>' . esc_html( $para ) . '</p><!-- /wp:paragraph -->' . "\n\n";
			}
			watphou_core_update_translated_post( $en_id, $lang, $title, $content, $copies[0] ?? $title );
		}
	}
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function watphou_core_apply_reviewed_destinations( array $rows ): void {
	$model = function_exists( 'watphou_core_pll_model' ) ? watphou_core_pll_model() : null;
	if ( ! $model ) {
		return;
	}
	foreach ( $rows as $row ) {
		if ( ( $row['kind'] ?? '' ) !== 'dest' ) {
			continue;
		}
		$slug = sanitize_title( (string) ( $row['location'] ?? '' ) );
		$en = watphou_core_find_en_term( 'destination', $slug );
		if ( ! $en ) {
			continue;
		}
		foreach ( array( 'fr', 'th' ) as $lang ) {
			$desc = trim( (string) ( $row[ $lang ] ?? '' ) );
			$tid  = (int) $model->term->get_translation( (int) $en->term_id, $lang );
			if ( $tid && '' !== $desc ) {
				wp_update_term( $tid, 'destination', array( 'description' => $desc ) );
			}
		}
	}
}

function watphou_core_apply_reviewed_duration_terms(): void {
	$model = function_exists( 'watphou_core_pll_model' ) ? watphou_core_pll_model() : null;
	if ( ! $model ) {
		return;
	}
	$map  = watphou_core_ui_map();
	$en_names = array(
		'1-day'   => 'Day Tours',
		'2-day'   => '2-Day Tours',
		'3-day'   => '3-Day Tours',
		'4-6-day' => '4–6 Day Tours',
	);
	foreach ( $en_names as $slug => $en_name ) {
		$term = watphou_core_find_en_term( 'duration', $slug );
		if ( ! $term ) {
			continue;
		}
		foreach ( array( 'fr', 'th' ) as $lang ) {
			$tid = (int) $model->term->get_translation( (int) $term->term_id, $lang );
			if ( ! $tid || empty( $map[ $lang ][ $en_name ] ) ) {
				continue;
			}
			wp_update_term( $tid, 'duration', array( 'name' => $map[ $lang ][ $en_name ] ) );
		}
	}
}

/**
 * @param array<int, array<string, mixed>> $rows
 * @return string[]
 */
function watphou_core_reviewed_package_slugs( array $rows ): array {
	$slugs = array();
	foreach ( $rows as $row ) {
		if ( ( $row['kind'] ?? '' ) !== 'package' ) {
			continue;
		}
		$slug = watphou_core_reviewed_slug( (string) ( $row['location'] ?? '' ) );
		if ( '' !== $slug ) {
			$slugs[ $slug ] = true;
		}
	}
	return array_keys( $slugs );
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function watphou_core_apply_reviewed_package_slug( array $rows, string $slug ): void {
	$parts = array();
	foreach ( $rows as $row ) {
		if ( ( $row['kind'] ?? '' ) !== 'package' ) {
			continue;
		}
		if ( watphou_core_reviewed_slug( (string) ( $row['location'] ?? '' ) ) !== $slug ) {
			continue;
		}
		$parts[] = $row;
	}
	if ( ! $parts ) {
		return;
	}
	foreach ( array( 'fr', 'th' ) as $lang ) {
		$data = watphou_core_package_lang_data( $parts, $lang );
		if ( empty( $data['title'] ) ) {
			continue;
		}
		$data['slug'] = $slug;
		if ( 'tailor-made-tours' === $slug ) {
			$page_id = watphou_core_find_page_id( $slug );
			if ( $page_id ) {
				watphou_core_update_translated_post(
					$page_id,
					$lang,
					$data['title'],
					watphou_core_build_tour_content( $data ),
					$data['dream'] ?? ''
				);
			}
			continue;
		}
		$en_id = watphou_core_find_tour_id( $slug, (string) ( $data['code'] ?? '' ) );
		if ( ! $en_id ) {
			continue;
		}
		watphou_core_update_translated_tour( $en_id, $lang, $data );
	}
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function watphou_core_apply_reviewed_packages( array $rows ): void {
	foreach ( watphou_core_reviewed_package_slugs( $rows ) as $slug ) {
		watphou_core_apply_reviewed_package_slug( $rows, $slug );
	}
}

function watphou_core_find_en_term( string $taxonomy, string $slug ) {
	$queries = array(
		array(
			'taxonomy'   => $taxonomy,
			'slug'       => $slug,
			'hide_empty' => false,
			'lang'       => 'en',
		),
		array(
			'taxonomy'   => $taxonomy,
			'slug'       => $slug,
			'hide_empty' => false,
			'lang'       => '',
		),
	);
	foreach ( $queries as $args ) {
		$found = get_terms( $args );
		if ( is_wp_error( $found ) || empty( $found ) ) {
			continue;
		}
		foreach ( $found as $term ) {
			if ( function_exists( 'pll_get_term_language' ) && 'en' === pll_get_term_language( $term->term_id ) ) {
				return $term;
			}
		}
		return $found[0];
	}
	return null;
}

/**
 * @param array<int, array<string, mixed>> $parts
 * @return array<string, mixed>
 */
function watphou_core_package_lang_data( array $parts, string $lang ): array {
	$data = array(
		'highlights' => array(),
		'included'   => array(),
		'excluded'   => array(),
		'upgrades'   => array(),
		'itinerary'  => array(),
	);
	foreach ( $parts as $row ) {
		$field = (string) ( $row['field'] ?? '' );
		$text  = trim( (string) ( $row[ $lang ] ?? '' ) );
		if ( '' === $field ) {
			continue;
		}
		if ( in_array( $field, array( 'title', 'headline', 'duration', 'departure', 'price_note', 'dream', 'cta', 'code' ), true ) ) {
			$data[ $field ] = $text;
			continue;
		}
		if ( preg_match( '/^(highlights|included|excluded|upgrades)\[(\d+)\]$/', $field, $m ) ) {
			$data[ $m[1] ][ (int) $m[2] ] = $text;
			continue;
		}
		if ( preg_match( '/^itinerary\.day(\d+)\.(title|body)$/', $field, $m ) ) {
			$day = (int) $m[1];
			if ( empty( $data['itinerary'][ $day ] ) ) {
				$data['itinerary'][ $day ] = array( 'day' => $day, 'title' => '', 'body' => '' );
			}
			$data['itinerary'][ $day ][ $m[2] ] = $text;
		}
	}
	foreach ( array( 'highlights', 'included', 'excluded', 'upgrades' ) as $key ) {
		ksort( $data[ $key ] );
		$data[ $key ] = array_values( array_filter( $data[ $key ], static fn( $v ) => '' !== trim( (string) $v ) ) );
	}
	ksort( $data['itinerary'] );
	$data['itinerary'] = array_values( $data['itinerary'] );
	return $data;
}

function watphou_core_update_translated_tour( int $en_id, string $lang, array $data ): void {
	$model = function_exists( 'watphou_core_pll_model' ) ? watphou_core_pll_model() : null;
	if ( ! $model ) {
		return;
	}
	$id = (int) $model->post->get_translation( $en_id, $lang );
	if ( ! $id ) {
		$source = get_post( $en_id );
		if ( ! $source instanceof WP_Post ) {
			return;
		}
		$id = watphou_core_copy_post_to_language( $source, $lang, $model );
	}
	if ( ! $id ) {
		return;
	}
	wp_update_post(
		array(
			'ID'           => $id,
			'post_title'   => $data['title'],
			'post_content' => watphou_core_build_tour_content( $data ),
			'post_excerpt' => $data['dream'] ?? '',
			'post_status'  => 'publish',
		)
	);
	update_post_meta( $id, 'tour_headline', $data['headline'] ?? '' );
	update_post_meta( $id, 'tour_duration', $data['duration'] ?? '' );
	update_post_meta( $id, '_watphou_translation_status', 'reviewed' );
}

function watphou_core_update_translated_post( int $en_id, string $lang, string $title, string $content, string $excerpt ): void {
	$model = function_exists( 'watphou_core_pll_model' ) ? watphou_core_pll_model() : null;
	if ( ! $model ) {
		return;
	}
	$id = (int) $model->post->get_translation( $en_id, $lang );
	if ( ! $id ) {
		$source = get_post( $en_id );
		if ( ! $source instanceof WP_Post ) {
			return;
		}
		$id = watphou_core_copy_post_to_language( $source, $lang, $model );
	}
	if ( ! $id ) {
		return;
	}
	wp_update_post(
		array(
			'ID'           => $id,
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'post_status'  => 'publish',
		)
	);
	update_post_meta( $id, '_watphou_translation_status', 'reviewed' );
}
