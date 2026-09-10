<?php
/**
 * Language-aware URLs and a public notice when French/Thai still show English.
 * Polylang Free does not translate text. We publish English copies so /fr/ and /th/ work.
 */
defined( 'ABSPATH' ) || exit;

function watphou_term_url( string $taxonomy, string $slug ): string {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	$rewrite = array(
		'destination' => 'destinations',
		'duration'    => 'duration',
		'tour_type'   => 'tour-type',
	);
	$base = $rewrite[ $taxonomy ] ?? $taxonomy;
	if ( ! $term || is_wp_error( $term ) ) {
		return home_url( '/' . $base . '/' . $slug . '/' );
	}
	if ( function_exists( 'pll_get_term' ) ) {
		$translated = pll_get_term( $term->term_id );
		if ( $translated ) {
			$link = get_term_link( (int) $translated, $taxonomy );
			if ( ! is_wp_error( $link ) ) {
				return $link;
			}
		}
	}
	$link = get_term_link( $term );
	return is_wp_error( $link ) ? home_url( '/' ) : $link;
}

function watphou_page_url( string $path ): string {
	$path = trim( $path, '/' );
	if ( '' === $path ) {
		return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
	}
	if ( function_exists( 'pll_get_post' ) ) {
		$page = get_page_by_path( $path );
		if ( $page ) {
			$translated = pll_get_post( $page->ID );
			if ( $translated ) {
				return get_permalink( $translated );
			}
		}
	}
	return home_url( '/' . $path . '/' );
}

function watphou_tour_url( string $slug ): string {
	$posts = get_posts(
		array(
			'name'           => $slug,
			'post_type'      => 'tour',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'lang'           => '',
		)
	);
	if ( ! $posts ) {
		return home_url( '/tours/' . $slug . '/' );
	}
	$id = (int) $posts[0]->ID;
	if ( function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $id );
		if ( $translated ) {
			return get_permalink( $translated );
		}
	}
	return get_permalink( $id );
}

function watphou_lang_url( string $slug ): string {
	$url = function_exists( 'pll_home_url' ) ? pll_home_url( $slug ) : home_url( '/' );
	if ( function_exists( 'pll_get_post' ) && is_singular() ) {
		$translated = pll_get_post( get_the_ID(), $slug );
		if ( $translated ) {
			return get_permalink( $translated );
		}
	}
	if ( function_exists( 'pll_get_term' ) && is_tax() ) {
		$translated = pll_get_term( get_queried_object_id(), $slug );
		if ( $translated ) {
			$link = get_term_link( (int) $translated );
			if ( ! is_wp_error( $link ) ) {
				return $link;
			}
		}
	}
	return $url;
}

function watphou_language_switcher(): void {
	if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_home_url' ) ) {
		echo '<span>EN</span>';
		return;
	}
	$current = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
	echo '<ul>';
	foreach ( pll_languages_list() as $slug ) {
		printf(
			'<li class="%1$s"><a lang="%2$s" hreflang="%2$s" href="%3$s">%4$s</a></li>',
			( $slug === $current ) ? 'current-lang' : 'lang-item',
			esc_attr( $slug ),
			esc_url( watphou_lang_url( $slug ) ),
			esc_html( strtoupper( $slug ) )
		);
	}
	echo '</ul>';
}

add_action( 'template_redirect', 'watphou_core_redirect_language_mismatch', 1 );

function watphou_core_redirect_language_mismatch(): void {
	if ( is_admin() || wp_doing_ajax() || ! function_exists( 'pll_get_post' ) ) {
		return;
	}
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( ! preg_match( '#^/(fr|th)/#', $path, $m ) ) {
		return;
	}
	if ( ! is_singular() ) {
		return;
	}
	$id = (int) get_queried_object_id();
	if ( $id < 1 ) {
		return;
	}
	$have = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : '';
	if ( ! is_string( $have ) || '' === $have || $have === $m[1] ) {
		return;
	}
	$tr = (int) pll_get_post( $id, $m[1] );
	if ( $tr < 1 || $tr === $id ) {
		return;
	}
	$url = get_permalink( $tr );
	if ( ! $url || wp_parse_url( $url, PHP_URL_PATH ) === $path ) {
		return;
	}
	wp_safe_redirect( $url, 301 );
	exit;
}

add_action( 'template_redirect', 'watphou_core_redirect_term_language_mismatch', 1 );

function watphou_core_redirect_term_language_mismatch(): void {
	if ( is_admin() || wp_doing_ajax() || ! function_exists( 'pll_get_term' ) ) {
		return;
	}
	if ( ! is_tax() && ! is_category() && ! is_tag() ) {
		return;
	}
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( ! preg_match( '#^/(fr|th)/#', $path, $m ) ) {
		return;
	}
	$id = (int) get_queried_object_id();
	if ( $id < 1 ) {
		return;
	}
	$have = function_exists( 'pll_get_term_language' ) ? pll_get_term_language( $id ) : '';
	if ( ! is_string( $have ) || '' === $have || $have === $m[1] ) {
		return;
	}
	$tr = (int) pll_get_term( $id, $m[1] );
	if ( $tr < 1 || $tr === $id ) {
		return;
	}
	$link = get_term_link( $tr );
	if ( is_wp_error( $link ) ) {
		return;
	}
	wp_safe_redirect( $link, 301 );
	exit;
}

function watphou_core_current_lang_slug(): string {
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( is_string( $lang ) && '' !== $lang ) {
			return $lang;
		}
	}
	if ( function_exists( 'PLL' ) && isset( PLL()->curlang ) && PLL()->curlang ) {
		return (string) PLL()->curlang->slug;
	}
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( preg_match( '#^/(fr|th)(/|$)#', $path, $m ) ) {
		return $m[1];
	}
	return 'en';
}

add_action( 'wp_body_open', 'watphou_core_language_placeholder_banner', 5 );

function watphou_core_language_placeholder_banner(): void {
	$lang = watphou_core_current_lang_slug();
	if ( 'en' === $lang ) {
		return;
	}
	if ( 'fr' === $lang ) {
		$text = 'La version française est en cours de rédaction. Cette page s’affiche pour l’instant en anglais. / French translation is in progress. This page is currently in English.';
	} else {
		$text = 'Thai translation is in progress. This page is currently in English. We do not use machine translation.';
	}
	echo '<div class="watphou-lang-banner" role="status">' . esc_html( $text ) . '</div>';
}
