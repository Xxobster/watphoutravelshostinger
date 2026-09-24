<?php
/**
 * Shared tour upsert used by Excel import and JSON package import.
 */
defined( 'ABSPATH' ) || exit;

function watphou_core_ensure_package_terms(): void {
	$terms = array(
		'1-day'   => 'Day Tours',
		'2-day'   => '2-Day Tours',
		'3-day'   => '3-Day Tours',
		'4-6-day' => '4-6 Day Tours',
	);
	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'duration' ) ) {
			wp_insert_term( $name, 'duration', array( 'slug' => $slug ) );
		}
	}
	$dest_labels = array(
		'bolaven-plateau' => 'Bolaven Plateau',
		'4000-islands'    => '4000 Islands (Si Phan Don)',
		'champasak'       => 'Vat Phou & Champasak',
		'pakse'           => 'Pakse & Surroundings',
	);
	foreach ( $dest_labels as $slug => $name ) {
		if ( ! term_exists( $slug, 'destination' ) ) {
			wp_insert_term( $name, 'destination', array( 'slug' => $slug ) );
		}
	}
}

function watphou_core_build_tour_content( array $data ): string {
	$blocks = array();
	if ( ! empty( $data['dream'] ) ) {
		$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $data['dream'] ) . '</p><!-- /wp:paragraph -->';
	}
	if ( ! empty( $data['highlights'] ) && is_array( $data['highlights'] ) ) {
		$blocks[] = '<!-- wp:watphou/highlights -->' . "\n" . esc_html( implode( "\n", $data['highlights'] ) ) . "\n" . '<!-- /wp:watphou/highlights -->';
	}
	if ( ! empty( $data['itinerary'] ) && is_array( $data['itinerary'] ) ) {
		foreach ( $data['itinerary'] as $day ) {
			$day_num  = (int) ( $day['day'] ?? 1 );
			$title    = esc_attr( $day['title'] ?? '' );
			$body     = esc_html( $day['body'] ?? '' );
			$blocks[] = sprintf(
				'<!-- wp:watphou/itinerary-day {"day":%1$d,"title":"%2$s"} --><p>%3$s</p><!-- /wp:watphou/itinerary-day -->',
				$day_num,
				$title,
				$body
			);
		}
	}
	foreach ( array( 'included' => 'included', 'excluded' => 'excluded', 'upgrades' => 'upgrades' ) as $key => $type ) {
		if ( empty( $data[ $key ] ) || ! is_array( $data[ $key ] ) ) {
			continue;
		}
		$blocks[] = sprintf(
			'<!-- wp:watphou/inclusions {"type":"%1$s"} -->%2$s<!-- /wp:watphou/inclusions -->',
			esc_attr( $type ),
			esc_html( implode( "\n", $data[ $key ] ) )
		);
	}
	if ( ! empty( $data['cta'] ) ) {
		$blocks[] = '<!-- wp:paragraph --><p><strong>' . esc_html( $data['cta'] ) . '</strong></p><!-- /wp:paragraph -->';
	}
	return implode( "\n\n", $blocks );
}

/**
 * @return int Tour post ID in the default (English) language, or 0.
 */
function watphou_core_find_tour_id( string $slug, string $code = '' ): int {
	$slug = sanitize_title( $slug );
	if ( $slug ) {
		$names = array( $slug );
		if ( function_exists( 'watphou_core_canonical_tour_slug' ) ) {
			$canonical = watphou_core_canonical_tour_slug( $slug );
			if ( $canonical && $canonical !== $slug ) {
				array_unshift( $names, $canonical );
			} elseif ( $canonical ) {
				$names[] = $canonical . '-2';
				$names[] = $canonical . '-3';
			}
		} elseif ( ! preg_match( '/-\d+$/', $slug ) ) {
			$names[] = $slug . '-2';
			$names[] = $slug . '-3';
		}
		$names = array_values( array_unique( array_filter( $names ) ) );
		foreach ( $names as $name ) {
			$by_name = get_posts(
				array(
					'post_type'      => 'tour',
					'name'           => $name,
					'posts_per_page' => 5,
					'post_status'    => 'any',
					'fields'         => 'ids',
					'lang'           => '',
				)
			);
			$id = watphou_core_prefer_english_tour( $by_name );
			if ( $id ) {
				return $id;
			}
		}
		$by_legacy = get_posts(
			array(
				'post_type'      => 'tour',
				'posts_per_page' => 5,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'lang'           => '',
				'meta_key'       => 'tour_legacy_slug',
				'meta_value'     => $slug,
			)
		);
		$id        = watphou_core_prefer_english_tour( $by_legacy );
		if ( $id ) {
			return $id;
		}
	}
	if ( $code ) {
		$by_code = get_posts(
			array(
				'post_type'      => 'tour',
				'posts_per_page' => 5,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'lang'           => '',
				'meta_key'       => 'tour_code',
				'meta_value'     => $code,
			)
		);
		$id      = watphou_core_prefer_english_tour( $by_code );
		if ( $id ) {
			return $id;
		}
	}
	return 0;
}

/**
 * @param int[] $ids
 */
function watphou_core_prefer_english_tour( array $ids ): int {
	$ids = array_values( array_filter( array_map( 'intval', $ids ) ) );
	if ( ! $ids ) {
		return 0;
	}
	if ( function_exists( 'pll_get_post' ) ) {
		foreach ( $ids as $id ) {
			$en = (int) pll_get_post( $id, 'en' );
			if ( $en ) {
				return $en;
			}
		}
		foreach ( $ids as $id ) {
			if ( function_exists( 'pll_get_post_language' ) && 'en' === pll_get_post_language( $id ) ) {
				return $id;
			}
		}
	}
	return $ids[0];
}

/**
 * Create or update one English tour. Does not change the featured photo.
 *
 * @return array{id:int,created:bool,title:string}
 */
function watphou_core_upsert_tour_from_data( array $data ): array {
	$slug = sanitize_title( (string) ( $data['slug'] ?? '' ) );
	if ( '' === $slug && ! empty( $data['title'] ) ) {
		$slug = sanitize_title( (string) $data['title'] );
	}
	if ( '' === $slug ) {
		throw new InvalidArgumentException( 'Each tour needs a slug (web address name) or a title.' );
	}

	watphou_core_ensure_package_terms();

	$title    = (string) ( $data['title'] ?? $slug );
	$code     = (string) ( $data['code'] ?? '' );
	$existing = watphou_core_find_tour_id( $slug, $code );
	if ( ! empty( $data['id'] ) ) {
		$by_id = (int) $data['id'];
		if ( $by_id && 'tour' === get_post_type( $by_id ) ) {
			$existing = $by_id;
		}
	}
	$status = sanitize_key( (string) ( $data['post_status'] ?? 'publish' ) );
	if ( ! in_array( $status, array( 'publish', 'draft', 'pending', 'future', 'private' ), true ) ) {
		$status = 'publish';
	}
	$postarr  = array(
		'post_type'    => 'tour',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => $status,
		'post_content' => watphou_core_build_tour_content( $data ),
		'post_excerpt' => $data['dream'] ?? ( $data['headline'] ?? '' ),
	);

	$created = false;
	if ( $existing ) {
		$postarr['ID'] = $existing;
		$old_name      = (string) get_post_field( 'post_name', $existing );
		if ( ! empty( $data['allow_slug_change'] ) && $slug ) {
			$postarr['post_name'] = $slug;
		} else {
			$postarr['post_name'] = $old_name ?: $slug;
		}
		$post_id = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
		$created = true;
	}
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		throw new RuntimeException( 'Could not save tour: ' . $slug );
	}
	$post_id = (int) $post_id;

	if ( $created && function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $post_id, 'en' );
	}

	$price = trim( (string) ( $data['price_from'] ?? 'XX' ) );
	if ( '' === $price ) {
		$price = 'XX';
	}

	update_post_meta( $post_id, 'tour_legacy_slug', $slug );
	update_post_meta( $post_id, 'tour_headline', $data['headline'] ?? '' );
	update_post_meta( $post_id, 'tour_duration', $data['duration'] ?? '' );
	update_post_meta( $post_id, 'tour_price_from', $price );
	if ( array_key_exists( 'price_note', $data ) ) {
		update_post_meta( $post_id, 'tour_price_note', (string) $data['price_note'] );
	}
	if ( function_exists( 'watphou_apply_amount_to_price_note' ) ) {
		$note = trim( (string) get_post_meta( $post_id, 'tour_price_note', true ) );
		if ( '' !== $note ) {
			update_post_meta( $post_id, 'tour_price_note', watphou_apply_amount_to_price_note( $note, $price ) );
		}
	}
	update_post_meta( $post_id, 'tour_currency', 'USD' );
	update_post_meta( $post_id, 'tour_bestseller', ! empty( $data['bestseller'] ) ? '1' : '0' );
	update_post_meta( $post_id, 'tour_priority', (string) (int) ( $data['priority'] ?? 0 ) );
	update_post_meta( $post_id, 'tour_whatsapp_message', 'Hello Watphou Travels, I would like to enquire about: ' . $title );
	update_post_meta( $post_id, 'tour_code', $code );
	if ( ! empty( $data['image'] ) ) {
		update_post_meta( $post_id, 'tour_image_file', $data['image'] );
	}

	$duration_term = (string) ( $data['duration_term'] ?? '' );
	if ( '' === $duration_term && ! empty( $data['duration'] ) && function_exists( 'watphou_core_map_duration_term' ) ) {
		$duration_term = watphou_core_map_duration_term( (string) $data['duration'] );
	}
	if ( $duration_term && taxonomy_exists( 'duration' ) ) {
		wp_set_object_terms( $post_id, $duration_term, 'duration', false );
	}
	if ( ! empty( $data['destinations'] ) && is_array( $data['destinations'] ) && taxonomy_exists( 'destination' ) ) {
		wp_set_object_terms( $post_id, $data['destinations'], 'destination', false );
	}

	watphou_core_sync_tour_placeholders( $post_id, $created );

	return array(
		'id'      => $post_id,
		'created' => $created,
		'title'   => $title,
	);
}

function watphou_core_sync_tour_placeholders( int $post_id, bool $created ): void {
	if ( ! function_exists( 'PLL' ) || ! function_exists( 'watphou_core_copy_post_to_language' ) ) {
		return;
	}
	$source = get_post( $post_id );
	if ( ! $source instanceof WP_Post ) {
		return;
	}
	$model = PLL()->model;
	if ( $created ) {
		$model->post->set_language( $post_id, 'en' );
	}
	$translations = array( 'en' => $post_id );
	foreach ( array( 'fr', 'th' ) as $code ) {
		$existing = (int) $model->post->get_translation( $post_id, $code );
		if ( $existing ) {
			$status = (string) get_post_meta( $existing, '_watphou_translation_status', true );
			if ( 'english-placeholder' !== $status ) {
				$translations[ $code ] = $existing;
				continue;
			}
		} elseif ( ! $created ) {
			continue;
		}
		$translations[ $code ] = watphou_core_copy_post_to_language( $source, $code, $model );
	}
	$translations = array_filter( $translations );
	if ( count( $translations ) > 1 && function_exists( 'pll_save_post_translations' ) ) {
		pll_save_post_translations( $translations );
	}
}

/**
 * @param array<int, array<string, string>> $tour_rows
 * @param array<int, array<string, string>> $itinerary_rows
 * @return array<int, array<string, mixed>>
 */
function watphou_core_excel_to_packages( array $tour_rows, array $itinerary_rows ): array {
	$days_by_slug = array();
	foreach ( $itinerary_rows as $row ) {
		$slug = sanitize_title( watphou_core_excel_val( $row, array( 'slug', 'tour_slug', 'web_address_name' ) ) );
		if ( '' === $slug ) {
			continue;
		}
		$days_by_slug[ $slug ][] = array(
			'day'   => (int) watphou_core_excel_val( $row, array( 'day', 'day_number' ) ),
			'title' => watphou_core_excel_val( $row, array( 'title', 'day_title' ) ),
			'body'  => watphou_core_excel_val( $row, array( 'body', 'day_body', 'description' ) ),
		);
	}

	$packages = array();
	foreach ( $tour_rows as $row ) {
		$title = watphou_core_excel_val( $row, array( 'title', 'tour_title', 'name' ) );
		$slug  = sanitize_title( watphou_core_excel_val( $row, array( 'slug', 'web_address_name' ) ) );
		if ( '' === $slug && $title ) {
			$slug = sanitize_title( $title );
		}
		if ( '' === $slug ) {
			continue;
		}
		if ( isset( $days_by_slug[ $slug ] ) ) {
			usort(
				$days_by_slug[ $slug ],
				static function ( $a, $b ) {
					return ( $a['day'] <=> $b['day'] );
				}
			);
		}
		$itinerary = $days_by_slug[ $slug ] ?? array();
		if ( ! $itinerary ) {
			$itinerary = watphou_core_excel_days_from_row( $row );
		}
		$packages[] = array(
			'slug'          => $slug,
			'title'         => $title ?: $slug,
			'headline'      => watphou_core_excel_val( $row, array( 'headline', 'short_headline' ) ),
			'duration'      => watphou_core_excel_val( $row, array( 'duration', 'duration_display' ) ),
			'duration_term' => watphou_core_map_excel_duration_term( watphou_core_excel_val( $row, array( 'duration_term', 'duration_menu' ) ), watphou_core_excel_val( $row, array( 'duration', 'duration_display' ) ) ),
			'departure'     => watphou_core_excel_val( $row, array( 'departure' ) ),
			'price_from'    => watphou_core_excel_val( $row, array( 'price_from', 'from_usd', 'price' ) ) ?: 'XX',
			'dream'         => watphou_core_excel_val( $row, array( 'dream', 'introduction', 'intro' ) ),
			'highlights'    => watphou_core_excel_split_list( watphou_core_excel_val( $row, array( 'highlights' ) ) ),
			'itinerary'     => $itinerary,
			'included'      => watphou_core_excel_split_list( watphou_core_excel_val( $row, array( 'included', 'whats_included' ) ) ),
			'excluded'      => watphou_core_excel_split_list( watphou_core_excel_val( $row, array( 'excluded', 'whats_not_included' ) ) ),
			'upgrades'      => watphou_core_excel_split_list( watphou_core_excel_val( $row, array( 'upgrades', 'optional_upgrades' ) ) ),
			'destinations'  => watphou_core_map_excel_destinations( watphou_core_excel_val( $row, array( 'destinations' ) ) ),
			'cta'           => watphou_core_excel_val( $row, array( 'cta', 'call_to_action' ) ),
			'code'          => watphou_core_excel_val( $row, array( 'code', 'package_code' ) ),
			'bestseller'    => watphou_core_excel_is_yes( watphou_core_excel_val( $row, array( 'bestseller', 'homepage' ) ) ),
			'priority'      => (int) watphou_core_excel_val( $row, array( 'priority' ) ),
			'page_type'     => 'tour',
		);
	}
	return $packages;
}

/**
 * @param array<string, string> $row
 * @return array<int, array{day:int,title:string,body:string}>
 */
function watphou_core_excel_days_from_row( array $row ): array {
	$days = array();
	foreach ( $row as $key => $val ) {
		if ( ! is_string( $key ) || ! preg_match( '/^day_?(\d+)_(title|body)$/', $key, $m ) ) {
			continue;
		}
		$n = (int) $m[1];
		if ( empty( $days[ $n ] ) ) {
			$days[ $n ] = array(
				'day'   => $n,
				'title' => '',
				'body'  => '',
			);
		}
		$days[ $n ][ $m[2] ] = trim( (string) $val );
	}
	ksort( $days );
	return array_values(
		array_filter(
			$days,
			static function ( $day ) {
				return '' !== trim( (string) ( $day['title'] ?? '' ) ) || '' !== trim( (string) ( $day['body'] ?? '' ) );
			}
		)
	);
}

/**
 * @param array<string, string> $row
 * @param string[]              $keys
 */
function watphou_core_excel_val( array $row, array $keys ): string {
	foreach ( $keys as $key ) {
		if ( isset( $row[ $key ] ) && '' !== trim( (string) $row[ $key ] ) ) {
			return trim( (string) $row[ $key ] );
		}
	}
	return '';
}

/**
 * @return string[]
 */
function watphou_core_excel_split_list( string $raw ): array {
	if ( '' === trim( $raw ) ) {
		return array();
	}
	$normalized = str_replace( array( "\r\n", "\r" ), "\n", $raw );
	$parts      = preg_split( '/\n+|;\s*/', $normalized ) ?: array();
	$out        = array();
	foreach ( $parts as $part ) {
		$part = trim( $part );
		$part = preg_replace( '/^[\-\*\x{2022}]\s*/u', '', $part );
		if ( '' !== $part ) {
			$out[] = $part;
		}
	}
	return $out;
}

function watphou_core_excel_is_yes( string $raw ): bool {
	$n = strtolower( trim( $raw ) );
	return in_array( $n, array( '1', 'yes', 'y', 'true', 'homepage', 'x' ), true );
}

function watphou_core_map_excel_duration_term( string $term, string $duration ): string {
	$blob = strtolower( $term . ' ' . $duration );
	if ( preg_match( '/4\s*[–-]\s*6|4-6|5\s*day|6\s*day|\b4\b.*day/', $blob ) ) {
		return '4-6-day';
	}
	if ( str_contains( $blob, '3-day' ) || preg_match( '/\b3\s*day/', $blob ) ) {
		return '3-day';
	}
	if ( str_contains( $blob, '2-day' ) || preg_match( '/\b2\s*day/', $blob ) ) {
		return '2-day';
	}
	if ( $term ) {
		$clean = sanitize_title( $term );
		if ( in_array( $clean, array( '1-day', '2-day', '3-day', '4-6-day' ), true ) ) {
			return $clean;
		}
	}
	return '1-day';
}

/**
 * @return string[]
 */
function watphou_core_map_excel_destinations( string $raw ): array {
	$allowed = array(
		'bolaven-plateau' => 'bolaven-plateau',
		'bolaven'         => 'bolaven-plateau',
		'bolaven plateau' => 'bolaven-plateau',
		'4000-islands'    => '4000-islands',
		'4000 islands'    => '4000-islands',
		'si phan don'     => '4000-islands',
		'champasak'       => 'champasak',
		'vat phou'        => 'champasak',
		'vatphou'         => 'champasak',
		'pakse'           => 'pakse',
		'pakse surroundings' => 'pakse',
	);
	$out     = array();
	foreach ( preg_split( '/[,;]+/', $raw ) ?: array() as $part ) {
		$key = strtolower( trim( $part ) );
		$key = preg_replace( '/\s+/', ' ', $key );
		if ( isset( $allowed[ $key ] ) ) {
			$out[] = $allowed[ $key ];
			continue;
		}
		$slug = sanitize_title( $part );
		if ( in_array( $slug, array( 'bolaven-plateau', '4000-islands', 'champasak', 'pakse' ), true ) ) {
			$out[] = $slug;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * @return array<string, mixed>
 */
function watphou_core_parse_tour_blocks( string $html ): array {
	$out = array(
		'dream'      => '',
		'cta'        => '',
		'highlights' => array(),
		'included'   => array(),
		'excluded'   => array(),
		'upgrades'   => array(),
		'itinerary'  => array(),
	);
	if ( preg_match_all( '/<!-- wp:paragraph --><p>(.*?)<\/p><!-- \/wp:paragraph -->/s', $html, $paras ) ) {
		foreach ( $paras[1] as $i => $p ) {
			$text = trim( html_entity_decode( wp_strip_all_tags( $p ), ENT_QUOTES, 'UTF-8' ) );
			if ( '' === $text ) {
				continue;
			}
			if ( false !== strpos( $p, '<strong>' ) && $i === ( count( $paras[1] ) - 1 ) ) {
				$out['cta'] = $text;
			} elseif ( '' === $out['dream'] ) {
				$out['dream'] = $text;
			}
		}
	}
	if ( preg_match( '/<!-- wp:watphou\/highlights -->(.*?)<!-- \/wp:watphou\/highlights -->/s', $html, $m ) ) {
		$out['highlights'] = watphou_core_excel_split_list( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES, 'UTF-8' ) );
	}
	if ( preg_match_all( '/<!-- wp:watphou\/itinerary-day ({.*?}) -->(?:<p>)?(.*?)(?:<\/p>)?<!-- \/wp:watphou\/itinerary-day -->/s', $html, $days, PREG_SET_ORDER ) ) {
		foreach ( $days as $day ) {
			$attrs = json_decode( $day[1], true );
			$out['itinerary'][] = array(
				'day'   => (int) ( $attrs['day'] ?? count( $out['itinerary'] ) + 1 ),
				'title' => (string) ( $attrs['title'] ?? '' ),
				'body'  => trim( html_entity_decode( wp_strip_all_tags( $day[2] ), ENT_QUOTES, 'UTF-8' ) ),
			);
		}
	}
	if ( preg_match_all( '/<!-- wp:watphou\/inclusions ({.*?}) -->(.*?)<!-- \/wp:watphou\/inclusions -->/s', $html, $incs, PREG_SET_ORDER ) ) {
		foreach ( $incs as $inc ) {
			$attrs = json_decode( $inc[1], true );
			$type  = (string) ( $attrs['type'] ?? 'included' );
			$list  = watphou_core_excel_split_list( html_entity_decode( wp_strip_all_tags( $inc[2] ), ENT_QUOTES, 'UTF-8' ) );
			if ( isset( $out[ $type ] ) ) {
				$out[ $type ] = $list;
			}
		}
	}
	return $out;
}

/**
 * @return array<string, mixed>
 */
function watphou_core_get_tour_editor_fields( int $post_id ): array {
	$empty = array(
		'title'         => '',
		'slug'          => '',
		'status'        => 'draft',
		'headline'      => '',
		'duration'      => '',
		'duration_term' => '1-day',
		'destinations'  => array(),
		'price_from'    => 'XX',
		'bestseller'    => false,
		'thumb'         => 0,
		'dream'         => '',
		'cta'           => '',
		'highlights'    => array(),
		'included'      => array(),
		'excluded'      => array(),
		'upgrades'      => array(),
		'itinerary'     => array(),
	);
	if ( $post_id < 1 ) {
		return $empty;
	}
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return $empty;
	}
	$parsed = watphou_core_parse_tour_blocks( (string) $post->post_content );
	$terms  = wp_get_object_terms( $post_id, 'duration', array( 'fields' => 'slugs' ) );
	$dest   = wp_get_object_terms( $post_id, 'destination', array( 'fields' => 'slugs' ) );
	$dur_term = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? (string) $terms[0] : watphou_core_map_duration_term( (string) get_post_meta( $post_id, 'tour_duration', true ) );
	$highlights = get_post_meta( $post_id, '_watphou_highlights', true );
	$included   = get_post_meta( $post_id, '_watphou_included', true );
	$excluded   = get_post_meta( $post_id, '_watphou_excluded', true );
	$upgrades   = get_post_meta( $post_id, '_watphou_upgrades', true );
	$itinerary  = get_post_meta( $post_id, '_watphou_itinerary', true );
	return array(
		'title'         => $post->post_title,
		'slug'          => $post->post_name,
		'status'        => $post->post_status,
		'headline'      => (string) get_post_meta( $post_id, 'tour_headline', true ),
		'duration'      => (string) get_post_meta( $post_id, 'tour_duration', true ),
		'duration_term' => $dur_term,
		'destinations'  => is_wp_error( $dest ) ? array() : array_values( $dest ),
		'price_from'    => (string) get_post_meta( $post_id, 'tour_price_from', true ) ?: 'XX',
		'bestseller'    => in_array( (string) get_post_meta( $post_id, 'tour_bestseller', true ), array( '1', 'true' ), true ),
		'thumb'         => (int) get_post_thumbnail_id( $post_id ),
		'dream'         => $post->post_excerpt ?: (string) $parsed['dream'],
		'cta'           => (string) get_post_meta( $post_id, '_watphou_cta', true ) ?: (string) $parsed['cta'],
		'highlights'    => is_array( $highlights ) && $highlights ? $highlights : $parsed['highlights'],
		'included'      => is_array( $included ) && $included ? $included : $parsed['included'],
		'excluded'      => is_array( $excluded ) && $excluded ? $excluded : $parsed['excluded'],
		'upgrades'      => is_array( $upgrades ) && $upgrades ? $upgrades : $parsed['upgrades'],
		'itinerary'     => is_array( $itinerary ) && $itinerary ? $itinerary : $parsed['itinerary'],
	);
}

function watphou_core_store_tour_lists( int $post_id, array $data ): void {
	update_post_meta( $post_id, '_watphou_highlights', $data['highlights'] ?? array() );
	update_post_meta( $post_id, '_watphou_included', $data['included'] ?? array() );
	update_post_meta( $post_id, '_watphou_excluded', $data['excluded'] ?? array() );
	update_post_meta( $post_id, '_watphou_upgrades', $data['upgrades'] ?? array() );
	update_post_meta( $post_id, '_watphou_itinerary', $data['itinerary'] ?? array() );
	update_post_meta( $post_id, '_watphou_cta', $data['cta'] ?? '' );
}

add_action( 'init', 'watphou_core_maybe_backfill_duration_terms', 60 );

function watphou_core_maybe_backfill_duration_terms(): void {
	if ( '1.6.0' === (string) get_option( 'watphou_duration_backfill' ) ) {
		return;
	}
	if ( ! taxonomy_exists( 'duration' ) ) {
		return;
	}
	watphou_core_ensure_package_terms();
	$tours = get_posts(
		array(
			'post_type'      => 'tour',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'lang'           => '',
		)
	);
	foreach ( $tours as $tour ) {
		$have = wp_get_object_terms( $tour->ID, 'duration', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $have ) && ! empty( $have ) ) {
			continue;
		}
		$dur = (string) get_post_meta( $tour->ID, 'tour_duration', true );
		wp_set_object_terms( $tour->ID, watphou_core_map_duration_term( $dur ), 'duration', false );
	}
	update_option( 'watphou_duration_backfill', '1.6.0', false );
}
