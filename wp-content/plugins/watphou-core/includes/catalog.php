<?php
/**
 * Package order and destination membership from
 * Website structures for Micah 2026-09-01.xlsx
 */
defined( 'ABSPATH' ) || exit;

/**
 * Excel codes in sheet order (Day Tour → 2-DAY → 3-DAY → 4–6 Day).
 *
 * @return array<int, array{code:string,slug:string}>
 */
function watphou_core_catalog_packages(): array {
	return array(
		array( 'code' => '1.1', 'slug' => 'bolaven-plateau-classic-full-day-tour' ),
		array( 'code' => '1.2', 'slug' => 'vatphu-riverside-gems-full-day' ),
		array( 'code' => '1.3', 'slug' => '4000islands-full-day' ),
		array( 'code' => '1.4', 'slug' => 'pakse-cultural-riverside-exploration-full-day' ),
		array( 'code' => '1.5', 'slug' => 'dan-sinxai-eco-adventure-trek-full-day' ),
		array( 'code' => '1.6', 'slug' => 'champasak-cycling-experience' ),
		array( 'code' => '1.7', 'slug' => 'full-day-trekking-at-dan-yai-tiger-falls' ),
		array( 'code' => '2.1', 'slug' => 'bolaven-plateau-classic-2-day' ),
		array( 'code' => '2.2', 'slug' => '4000islands-vatphu-temple-2-day-1-night' ),
		array( 'code' => '2.3', 'slug' => 'vatphu-champasak-discovery-2days-1night' ),
		array( 'code' => '3.1', 'slug' => '3-day-classic-experience-in-southern-laos' ),
		array( 'code' => '3.2', 'slug' => '3-day-highlights-of-southern-laos' ),
		array( 'code' => '4.1', 'slug' => '4-day-southern-laos-escape' ),
		array( 'code' => '5.1', 'slug' => '5-day-exploring-southern-laos' ),
		array( 'code' => '6.1', 'slug' => '6-day-journey-to-the-heart-of-southern-laos' ),
	);
}

/**
 * Home sheet “Our Best Sellers” — exact four packages, in that order.
 *
 * @return string[]
 */
function watphou_core_catalog_bestseller_slugs(): array {
	return array(
		'bolaven-plateau-classic-full-day-tour',
		'4000islands-vatphu-temple-2-day-1-night',
		'3-day-classic-experience-in-southern-laos',
		'4-day-southern-laos-escape',
	);
}

/**
 * Published tours marked Homepage in Quick edit, catalog order, current language.
 *
 * @return int[]
 */
function watphou_core_homepage_bestseller_ids( int $limit = 4 ): array {
	$limit = max( 1, $limit );
	$lang  = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : '';
	$posts = get_posts(
		array(
			'post_type'      => 'tour',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_key'       => 'tour_bestseller',
			'meta_value'     => '1',
		)
	);
	if ( $posts ) {
		$posts = watphou_core_sort_tours_by_catalog( $posts );
	}
	$ids = array();
	foreach ( $posts as $post ) {
		$id = (int) $post->ID;
		if ( $lang && function_exists( 'pll_get_post' ) ) {
			$tid = (int) pll_get_post( $id, $lang );
			$id  = $tid ?: $id;
		}
		if ( $id && ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
		if ( count( $ids ) >= $limit ) {
			break;
		}
	}
	if ( $ids ) {
		return $ids;
	}
	if ( ! function_exists( 'watphou_core_find_tour_id' ) ) {
		return array();
	}
	foreach ( watphou_core_catalog_bestseller_slugs() as $best_slug ) {
		$bid = watphou_core_find_tour_id( $best_slug );
		if ( ! $bid ) {
			continue;
		}
		if ( $lang && function_exists( 'pll_get_post' ) ) {
			$tid = (int) pll_get_post( $bid, $lang );
			$bid = $tid ?: $bid;
		}
		if ( $bid && ! in_array( $bid, $ids, true ) ) {
			$ids[] = $bid;
		}
		if ( count( $ids ) >= $limit ) {
			break;
		}
	}
	return $ids;
}

/**
 * Destination pages — Excel section 5 (codes in listed order).
 *
 * @return array<string, string[]> destination slug => package codes
 */
function watphou_core_catalog_destination_codes(): array {
	return array(
		'bolaven-plateau' => array( '1.1', '1.5', '1.7', '2.1', '3.1', '3.2', '4.1', '5.1', '6.1' ),
		'4000-islands'    => array( '1.3', '2.2', '3.1', '3.2', '4.1', '5.1', '6.1' ),
		'champasak'       => array( '1.2', '1.6', '2.2', '2.3', '3.1', '3.2', '4.1', '5.1', '6.1' ),
		'pakse'           => array( '1.4' ),
	);
}

/**
 * @return array<string, int> english slug => 1-based order
 */
function watphou_core_catalog_order_map(): array {
	$map = array();
	foreach ( watphou_core_catalog_packages() as $i => $row ) {
		$map[ $row['slug'] ] = $i + 1;
	}
	return $map;
}

/**
 * @return array<string, string> english slug => code
 */
function watphou_core_catalog_code_map(): array {
	$map = array();
	foreach ( watphou_core_catalog_packages() as $row ) {
		$map[ $row['slug'] ] = $row['code'];
	}
	return $map;
}

function watphou_core_tour_catalog_slug( WP_Post $post ): string {
	$id = (int) $post->ID;
	if ( function_exists( 'pll_get_post' ) ) {
		$en = pll_get_post( $id, 'en' );
		if ( $en ) {
			$id = (int) $en;
		}
	}
	$legacy = (string) get_post_meta( $id, 'tour_legacy_slug', true );
	if ( '' !== $legacy ) {
		return $legacy;
	}
	return (string) get_post_field( 'post_name', $id );
}

/**
 * @param WP_Post[] $posts
 * @return WP_Post[]
 */
function watphou_core_sort_tours_by_catalog( array $posts ): array {
	$order = watphou_core_catalog_order_map();
	usort(
		$posts,
		static function ( WP_Post $a, WP_Post $b ) use ( $order ): int {
			$sa = watphou_core_tour_catalog_slug( $a );
			$sb = watphou_core_tour_catalog_slug( $b );
			$oa = $order[ $sa ] ?? 999;
			$ob = $order[ $sb ] ?? 999;
			if ( $oa === $ob ) {
				return strcasecmp( $a->post_title, $b->post_title );
			}
			return $oa <=> $ob;
		}
	);
	return $posts;
}

/**
 * Destination page order from Excel section 5 (package codes in listed order).
 *
 * @param WP_Post[] $posts
 * @return WP_Post[]
 */
function watphou_core_sort_tours_for_destination( array $posts, string $dest_slug ): array {
	$codes = watphou_core_catalog_destination_codes()[ $dest_slug ] ?? array();
	if ( ! $codes ) {
		return watphou_core_sort_tours_by_catalog( $posts );
	}
	$code_rank = array_flip( $codes );
	$code_map  = watphou_core_catalog_code_map();
	usort(
		$posts,
		static function ( WP_Post $a, WP_Post $b ) use ( $code_rank, $code_map ): int {
			$ca = $code_map[ watphou_core_tour_catalog_slug( $a ) ] ?? '';
			$cb = $code_map[ watphou_core_tour_catalog_slug( $b ) ] ?? '';
			$oa = $code_rank[ $ca ] ?? 999;
			$ob = $code_rank[ $cb ] ?? 999;
			if ( $oa === $ob ) {
				return strcasecmp( $a->post_title, $b->post_title );
			}
			return $oa <=> $ob;
		}
	);
	return $posts;
}

add_action( 'init', 'watphou_core_maybe_apply_catalog', 70 );

function watphou_core_maybe_apply_catalog(): void {
	if ( '1.6.2' === (string) get_option( 'watphou_catalog_apply' ) ) {
		return;
	}
	if ( ! post_type_exists( 'tour' ) ) {
		return;
	}
	watphou_core_apply_catalog();
	update_option( 'watphou_catalog_apply', '1.6.2', false );
}

function watphou_core_apply_catalog(): void {
	$code_map  = watphou_core_catalog_code_map();
	$order_map = watphou_core_catalog_order_map();
	$best      = watphou_core_catalog_bestseller_slugs();
	$dest_by_code = array();
	foreach ( watphou_core_catalog_destination_codes() as $dest => $codes ) {
		foreach ( $codes as $code ) {
			$dest_by_code[ $code ][] = $dest;
		}
	}

	foreach ( watphou_core_catalog_packages() as $row ) {
		$slug = $row['slug'];
		$code = $row['code'];
		$id   = function_exists( 'watphou_core_find_tour_id' ) ? watphou_core_find_tour_id( $slug, $code ) : 0;
		if ( ! $id ) {
			continue;
		}
		$ids = array( $id );
		if ( function_exists( 'pll_get_post_translations' ) ) {
			$tr = pll_get_post_translations( $id );
			if ( $tr ) {
				$ids = array_map( 'intval', $tr );
			}
		}
		$menu = (int) ( $order_map[ $slug ] ?? 99 ) * 10;
		$is_best = in_array( $slug, $best, true ) ? '1' : '0';
		$dests   = $dest_by_code[ $code ] ?? array();
		foreach ( $ids as $tid ) {
			if ( ! $tid || 'tour' !== get_post_type( $tid ) ) {
				continue;
			}
			wp_update_post(
				array(
					'ID'         => $tid,
					'menu_order' => $menu,
				)
			);
			update_post_meta( $tid, 'tour_code', $code );
			update_post_meta( $tid, 'tour_bestseller', $is_best );
			if ( $dests && taxonomy_exists( 'destination' ) ) {
				$lang     = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $tid ) : '';
				$term_ids = array();
				foreach ( $dests as $dest_slug ) {
					$term = get_term_by( 'slug', $dest_slug, 'destination' );
					if ( ! $term || is_wp_error( $term ) ) {
						continue;
					}
					$term_id = (int) $term->term_id;
					if ( $lang && function_exists( 'pll_get_term' ) ) {
						$translated = pll_get_term( $term_id, $lang );
						if ( $translated ) {
							$term_id = (int) $translated;
						}
					}
					$term_ids[] = $term_id;
				}
				if ( $term_ids ) {
					wp_set_object_terms( $tid, $term_ids, 'destination', false );
				}
			}
		}
	}
}
