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
	$lang = watphou_core_current_lang_slug();
	if ( ! $term || is_wp_error( $term ) ) {
		$link = home_url( '/' . $base . '/' . $slug . '/' );
		return watphou_core_force_lang_url( $link, $lang );
	}
	$link = '';
	if ( function_exists( 'pll_get_term' ) ) {
		$translated = pll_get_term( $term->term_id, $lang );
		if ( $translated ) {
			$maybe = get_term_link( (int) $translated, $taxonomy );
			if ( ! is_wp_error( $maybe ) ) {
				$link = $maybe;
			}
		}
	}
	if ( '' === $link ) {
		$maybe = get_term_link( $term );
		$link  = is_wp_error( $maybe ) ? home_url( '/' . $base . '/' . $slug . '/' ) : $maybe;
	}
	return watphou_core_force_lang_url( $link, $lang );
}

function watphou_core_force_lang_url( string $url, string $lang ): string {
	if ( '' === $url ) {
		return $url;
	}
	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) ) {
		return $url;
	}
	$path  = isset( $parts['path'] ) ? (string) $parts['path'] : '/';
	$query = isset( $parts['query'] ) && '' !== $parts['query'] ? '?' . $parts['query'] : '';
	$path  = preg_replace( '#^/(?:fr|th)(?=/|$)#', '', $path ) ?: '/';
	$path  = preg_replace( '#^/(?:fr|th)(?=/|$)#', '', $path ) ?: '/';
	if ( '' === $path || '#' === $path[0] ) {
		$path = '/';
	}
	if ( 'en' === $lang ) {
		return untrailingslashit( home_url( '/' ) ) . $path . $query;
	}
	if ( ! in_array( $lang, array( 'fr', 'th' ), true ) ) {
		return $url;
	}
	$base = function_exists( 'pll_home_url' ) ? pll_home_url( $lang ) : home_url( '/' . $lang . '/' );
	return untrailingslashit( $base ) . $path . $query;
}

function watphou_localized_permalink( $post = 0 ): string {
	$link = get_permalink( $post );
	if ( ! is_string( $link ) || '' === $link ) {
		return '';
	}
	return watphou_core_force_lang_url( $link, watphou_core_current_lang_slug() );
}

function watphou_page_url( string $path ): string {
	$path = trim( $path, '/' );
	$lang = watphou_core_current_lang_slug();
	if ( '' === $path ) {
		$home = function_exists( 'pll_home_url' ) ? pll_home_url( $lang ) : home_url( '/' );
		return watphou_core_force_lang_url( (string) $home, $lang );
	}
	$id = watphou_core_find_content_id( $path, 'page' );
	$link = '';
	if ( $id && function_exists( 'pll_get_post' ) ) {
		$translated = (int) pll_get_post( $id, $lang );
		if ( $translated ) {
			$link = (string) get_permalink( $translated );
		}
	}
	if ( '' === $link && $id && 'en' === $lang ) {
		$link = (string) get_permalink( $id );
	}
	if ( '' === $link ) {
		$base = function_exists( 'pll_home_url' ) ? pll_home_url( $lang ) : home_url( '/' . ( 'en' === $lang ? '' : $lang . '/' ) );
		$link = trailingslashit( $base ) . $path . '/';
	}
	return watphou_core_force_lang_url( $link, $lang );
}

function watphou_tour_url( string $slug ): string {
	$id   = watphou_core_find_content_id( $slug, 'tour' );
	$lang = watphou_core_current_lang_slug();
	if ( ! $id ) {
		return watphou_core_force_lang_url( home_url( '/tours/' . $slug . '/' ), $lang );
	}
	$link = '';
	if ( function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $id, $lang );
		if ( $translated ) {
			$link = (string) get_permalink( $translated );
		}
	}
	if ( '' === $link ) {
		$link = (string) get_permalink( $id );
	}
	return watphou_core_force_lang_url( $link, $lang );
}

function watphou_lang_url( string $slug ): string {
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	if ( function_exists( 'pll_get_post' ) && is_singular() ) {
		$translated = pll_get_post( get_the_ID(), $slug );
		if ( $translated ) {
			$raw = get_permalink( $translated );
			if ( is_string( $raw ) && '' !== $raw ) {
				$path = (string) wp_parse_url( $raw, PHP_URL_PATH );
			}
		}
	} elseif ( function_exists( 'pll_get_term' ) && ( is_tax() || is_category() || is_tag() ) ) {
		$translated = pll_get_term( get_queried_object_id(), $slug );
		if ( $translated ) {
			$link = get_term_link( (int) $translated );
			if ( ! is_wp_error( $link ) ) {
				$path = (string) wp_parse_url( $link, PHP_URL_PATH );
			}
		}
	}
	return watphou_core_force_lang_url( home_url( $path ?: '/' ), $slug );
}

function watphou_language_switcher(): void {
	if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_home_url' ) ) {
		echo '<span>EN</span>';
		return;
	}
	$current = watphou_core_current_lang_slug();
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

add_filter( 'pll_preferred_language', 'watphou_core_preferred_language_from_url', 0 );
add_filter( 'pll_check_canonical_url', 'watphou_core_keep_url_language', 10, 2 );
add_action( 'pll_language_defined', 'watphou_core_lock_language_to_url', 0, 2 );
add_action( 'parse_query', 'watphou_core_parse_query_language', 0 );
add_action( 'pre_get_posts', 'watphou_core_pre_get_posts_language', 0 );
add_action( 'wp', 'watphou_core_redirect_language_mismatch', 0 );
add_action( 'template_redirect', 'watphou_core_redirect_known_slug_aliases', 0 );
add_action( 'template_redirect', 'watphou_core_redirect_language_mismatch', 1 );
add_filter( 'wpseo_breadcrumb_links', 'watphou_core_breadcrumb_links_current_lang', 20 );

function watphou_core_url_has_lang_prefix( string $url, string $lang ): bool {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	return (bool) preg_match( '#^/' . preg_quote( $lang, '#' ) . '(/|$)#', $path );
}

function watphou_core_is_public_front_request(): bool {
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( str_contains( $path, '/wp-admin/' ) || str_contains( $path, 'wp-login.php' ) || str_contains( $path, 'wp-cron.php' ) || str_contains( $path, '/wp-json/' ) ) {
		return false;
	}
	return true;
}

function watphou_core_lang_from_request(): string {
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( preg_match( '#^/(fr|th)(/|$)#', $path, $m ) ) {
		return $m[1];
	}
	return 'en';
}

function watphou_core_preferred_language_from_url( $slug ) {
	if ( ! watphou_core_is_public_front_request() ) {
		return $slug;
	}
	return watphou_core_lang_from_request();
}

function watphou_core_lock_language_to_url( $slug, $lang = null ): void {
	unset( $lang );
	if ( ! watphou_core_is_public_front_request() ) {
		return;
	}
	$want = watphou_core_lang_from_request();
	if ( is_string( $slug ) && $want === $slug ) {
		return;
	}
	watphou_core_set_request_language( $want );
}

function watphou_core_parse_query_language( $query ): void {
	if ( ! $query instanceof WP_Query || is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! watphou_core_is_public_front_request() ) {
		return;
	}
	$query->set( 'lang', watphou_core_lang_from_request() );
}

function watphou_core_pre_get_posts_language( $query ): void {
	watphou_core_parse_query_language( $query );
}

function watphou_core_redirect_known_slug_aliases(): void {
	if ( is_admin() || wp_doing_ajax() || ! watphou_core_is_public_front_request() ) {
		return;
	}
	$path    = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$aliases = array(
		'contact' => 'contact-us',
	);
	if ( ! preg_match( '#^/(fr|th)/([^/]+)/?$#', $path, $m ) ) {
		return;
	}
	$target = $aliases[ $m[2] ] ?? '';
	if ( '' === $target ) {
		return;
	}
	wp_safe_redirect( home_url( '/' . $m[1] . '/' . $target . '/' ), 301 );
	exit;
}

/**
 * Keep Yoast crumbs on the post we actually render (not a same-slug translation).
 *
 * @param array<int, array<string, mixed>> $links Breadcrumb links.
 * @return array<int, array<string, mixed>>
 */
function watphou_core_breadcrumb_links_current_lang( $links ) {
	if ( ! is_array( $links ) || ! $links || ! is_singular() ) {
		return $links;
	}
	$title = get_the_title();
	if ( ! is_string( $title ) || '' === $title ) {
		return $links;
	}
	$last = array_key_last( $links );
	if ( null === $last ) {
		return $links;
	}
	$links[ $last ]['text'] = $title;
	return $links;
}

function watphou_core_keep_url_language( $redirect_url, $language = null ) {
	unset( $language );
	if ( ! watphou_core_is_public_front_request() || ! is_string( $redirect_url ) || '' === $redirect_url ) {
		return $redirect_url;
	}
	$want = watphou_core_lang_from_request();
	$path = (string) wp_parse_url( $redirect_url, PHP_URL_PATH );
	if ( 'en' === $want && preg_match( '#^/(fr|th)(/|$)#', $path ) ) {
		return false;
	}
	if ( in_array( $want, array( 'fr', 'th' ), true ) && ! preg_match( '#^/' . preg_quote( $want, '#' ) . '(/|$)#', $path ) ) {
		return false;
	}
	return $redirect_url;
}

function watphou_core_canonical_content_slug( string $name ): string {
	$clean = preg_replace( '/-(?:2|3|fr|th)$/', '', $name );
	return is_string( $clean ) && '' !== $clean ? $clean : $name;
}

function watphou_core_set_request_language( string $lang ): void {
	if ( ! function_exists( 'PLL' ) ) {
		return;
	}
	$obj = PLL()->model->get_language( $lang );
	if ( $obj ) {
		PLL()->curlang = $obj;
	}
}

function watphou_core_swap_queried_post( int $id ): void {
	global $wp_query, $post;
	$obj = get_post( $id );
	if ( ! $obj instanceof WP_Post ) {
		return;
	}
	$post = $obj;
	if ( $wp_query instanceof WP_Query ) {
		$wp_query->post              = $obj;
		$wp_query->posts             = array( $obj );
		$wp_query->queried_object    = $obj;
		$wp_query->queried_object_id = (int) $obj->ID;
		$wp_query->found_posts       = 1;
		$wp_query->post_count        = 1;
		$wp_query->is_404            = false;
		$wp_query->is_singular       = true;
		$wp_query->is_single         = ( 'page' !== $obj->post_type );
		$wp_query->is_page           = ( 'page' === $obj->post_type );
	}
	setup_postdata( $post );
	status_header( 200 );
}

function watphou_core_redirect_language_mismatch(): void {
	if ( is_admin() || wp_doing_ajax() || ! function_exists( 'pll_get_post' ) ) {
		return;
	}
	if ( ! watphou_core_is_public_front_request() ) {
		return;
	}
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$lang = watphou_core_lang_from_request();
	watphou_core_set_request_language( $lang );
	if ( ! is_singular() ) {
		return;
	}
	$id = (int) get_queried_object_id();
	if ( $id < 1 ) {
		return;
	}
	$have = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : '';
	if ( ! is_string( $have ) || '' === $have || $have === $lang ) {
		return;
	}
	$tr = (int) pll_get_post( $id, $lang );
	if ( $tr < 1 || $tr === $id ) {
		return;
	}
	$url  = get_permalink( $tr );
	$want = $url ? (string) wp_parse_url( $url, PHP_URL_PATH ) : '';
	if ( $url && 'en' !== $lang && watphou_core_url_has_lang_prefix( $url, $lang ) && untrailingslashit( $want ) !== untrailingslashit( $path ) ) {
		wp_safe_redirect( $url, 301 );
		exit;
	}
	watphou_core_swap_queried_post( $tr );
}

add_action( 'template_redirect', 'watphou_core_redirect_term_language_mismatch', 1 );

function watphou_core_redirect_term_language_mismatch(): void {
	if ( is_admin() || wp_doing_ajax() || ! function_exists( 'pll_get_term' ) ) {
		return;
	}
	if ( ! is_tax() && ! is_category() && ! is_tag() ) {
		return;
	}
	if ( ! watphou_core_is_public_front_request() ) {
		return;
	}
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$lang = watphou_core_lang_from_request();
	watphou_core_set_request_language( $lang );
	$id = (int) get_queried_object_id();
	if ( $id < 1 ) {
		return;
	}
	$have = function_exists( 'pll_get_term_language' ) ? pll_get_term_language( $id ) : '';
	if ( ! is_string( $have ) || '' === $have || $have === $lang ) {
		return;
	}
	$tr = (int) pll_get_term( $id, $lang );
	if ( $tr < 1 || $tr === $id ) {
		return;
	}
	$link = get_term_link( $tr );
	if ( is_wp_error( $link ) ) {
		return;
	}
	$want = (string) wp_parse_url( $link, PHP_URL_PATH );
	if ( 'en' !== $lang && watphou_core_url_has_lang_prefix( $link, $lang ) && untrailingslashit( $want ) !== untrailingslashit( $path ) ) {
		wp_safe_redirect( $link, 301 );
		exit;
	}
	global $wp_query;
	$term = get_term( $tr );
	if ( $term && ! is_wp_error( $term ) ) {
		$wp_query->queried_object    = $term;
		$wp_query->queried_object_id = $tr;
	}
}

function watphou_core_current_lang_slug(): string {
	if ( watphou_core_is_public_front_request() ) {
		return watphou_core_lang_from_request();
	}
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( is_string( $lang ) && '' !== $lang ) {
			return $lang;
		}
	}
	if ( function_exists( 'PLL' ) && isset( PLL()->curlang ) && PLL()->curlang ) {
		return (string) PLL()->curlang->slug;
	}
	return watphou_core_lang_from_request();
}

add_action( 'wp_body_open', 'watphou_core_language_placeholder_banner', 5 );

function watphou_core_language_placeholder_banner(): void {
	$lang = watphou_core_current_lang_slug();
	if ( 'en' === $lang ) {
		return;
	}
	if ( get_option( 'watphou_reviewed_i18n_hash' ) ) {
		return;
	}
	$map = function_exists( 'watphou_core_ui_map' ) ? watphou_core_ui_map() : array();
	if ( ! empty( $map[ $lang ] ) ) {
		return;
	}
	if ( 'fr' === $lang ) {
		$text = 'La version française est en cours de rédaction. Cette page s’affiche pour l’instant en anglais. / French translation is in progress. This page is currently in English.';
	} else {
		$text = 'Thai translation is in progress. This page is currently in English. We do not use machine translation.';
	}
	echo '<div class="watphou-lang-banner" role="status">' . esc_html( $text ) . '</div>';
}

add_action( 'init', 'watphou_core_maybe_flush_language_rewrites', 20 );
add_action( 'init', 'watphou_core_maybe_align_translation_slugs', 30 );
add_action( 'template_redirect', 'watphou_core_fix_language_prefix_404', 0 );

function watphou_core_maybe_flush_language_rewrites(): void {
	if ( wp_installing() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( ! function_exists( 'PLL' ) ) {
		return;
	}
	if ( get_option( 'watphou_pll_rewrites' ) === '1.7.1' ) {
		return;
	}
	$opt = get_option( 'polylang' );
	if ( is_array( $opt ) ) {
		$opt['force_lang']    = 1;
		$opt['hide_default']  = 1;
		$opt['rewrite']       = 1;
		$opt['redirect_lang'] = 1;
		$opt['browser']       = 0;
		update_option( 'polylang', $opt );
	}
	flush_rewrite_rules( false );
	update_option( 'watphou_pll_rewrites', '1.7.1', false );
}

/**
 * English post ID for a page or tour slug (any language copy).
 */
function watphou_core_find_content_id( string $slug, string $post_type ): int {
	$slug = sanitize_title( $slug );
	if ( '' === $slug ) {
		return 0;
	}
	$args = array(
		'name'           => $slug,
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$args['lang'] = pll_default_language() ?: 'en';
	}
	$ids = get_posts( $args );
	if ( $ids ) {
		return (int) $ids[0];
	}
	$args['lang'] = '';
	$ids          = get_posts( $args );
	if ( $ids ) {
		return (int) $ids[0];
	}
	foreach ( array( $slug . '-2', $slug . '-3' ) as $alt ) {
		$args['name'] = $alt;
		$args['lang'] = function_exists( 'pll_default_language' ) ? ( pll_default_language() ?: 'en' ) : '';
		$ids          = get_posts( $args );
		if ( $ids ) {
			return (int) $ids[0];
		}
	}
	return 0;
}

/**
 * WordPress uniquifies slugs across languages. Force the English slug onto a translation.
 */
function watphou_core_force_post_slug( int $id, string $slug ): void {
	global $wpdb;
	$slug = sanitize_title( $slug );
	if ( $id < 1 || '' === $slug ) {
		return;
	}
	$wpdb->update(
		$wpdb->posts,
		array(
			'post_name'   => $slug,
			'post_status' => 'publish',
		),
		array( 'ID' => $id )
	);
	clean_post_cache( $id );
}

/**
 * Give French/Thai copies the same slug as English so /fr/day-tours/ works.
 */
function watphou_core_maybe_align_translation_slugs(): void {
	if ( wp_installing() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( ! function_exists( 'pll_get_post_translations' ) ) {
		return;
	}
	if ( get_option( 'watphou_align_i18n_slugs' ) === '1.6.13' ) {
		return;
	}
	$posts = get_posts(
		array(
			'post_type'      => array( 'page', 'tour' ),
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'draft' ),
			'lang'           => function_exists( 'pll_default_language' ) ? ( pll_default_language() ?: 'en' ) : '',
		)
	);
	foreach ( $posts as $post ) {
		$en_slug = watphou_core_canonical_content_slug( (string) $post->post_name );
		if ( '' === $en_slug ) {
			continue;
		}
		if ( $en_slug !== (string) $post->post_name ) {
			watphou_core_force_post_slug( (int) $post->ID, $en_slug );
		}
		$tr = pll_get_post_translations( (int) $post->ID );
		if ( ! is_array( $tr ) ) {
			continue;
		}
		foreach ( $tr as $lang => $tid ) {
			unset( $lang );
			$tid = (int) $tid;
			if ( ! $tid || $tid === (int) $post->ID ) {
				continue;
			}
			$current = (string) get_post_field( 'post_name', $tid );
			if ( $current === $en_slug ) {
				continue;
			}
			watphou_core_force_post_slug( $tid, $en_slug );
		}
	}
	flush_rewrite_rules( false );
	update_option( 'watphou_align_i18n_slugs', '1.6.13', false );
}

/**
 * Serve /fr/ and /th/ (and their child paths) when Polylang rewrite rules miss them.
 */
function watphou_core_fix_language_prefix_404(): void {
	if ( is_admin() || wp_doing_ajax() || ! is_404() ) {
		return;
	}
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( ! preg_match( '#^/(fr|th)(/.*)?$#', $path, $m ) ) {
		return;
	}
	$lang = $m[1];
	$rest = isset( $m[2] ) ? trim( $m[2], '/' ) : '';
	if ( function_exists( 'PLL' ) ) {
		$obj = PLL()->model->get_language( $lang );
		if ( $obj ) {
			PLL()->curlang = $obj;
		}
	}

	if ( '' === $rest ) {
		watphou_core_render_language_home();
		return;
	}

	$id = watphou_core_content_id_from_language_path( $lang, $rest );
	if ( $id ) {
		$url  = get_permalink( $id );
		$want = $url ? (string) wp_parse_url( $url, PHP_URL_PATH ) : '';
		if ( $url && watphou_core_url_has_lang_prefix( $url, $lang ) && untrailingslashit( $want ) !== untrailingslashit( $path ) ) {
			wp_safe_redirect( $url, 301 );
			exit;
		}
		watphou_core_swap_queried_post( $id );
		return;
	}
	if ( preg_match( '#^destinations/([^/]+)$#', $rest, $dm ) ) {
		$term_id = watphou_core_term_id_from_language_path( 'destination', $lang, $dm[1] );
		if ( $term_id ) {
			watphou_core_render_language_term( 'destination', $term_id );
		}
	}
}

function watphou_core_render_language_home(): void {
	global $wp_query;
	$wp_query->is_404        = false;
	$wp_query->is_home       = true;
	$wp_query->is_front_page = true;
	$wp_query->is_page       = false;
	$wp_query->is_singular   = false;
	status_header( 200 );
	nocache_headers();
	$tpl = get_front_page_template();
	if ( ! $tpl ) {
		$tpl = get_home_template();
	}
	if ( $tpl ) {
		include $tpl;
		exit;
	}
}

function watphou_core_content_id_from_language_path( string $lang, string $rest ): int {
	$rest = trim( $rest, '/' );
	$type = 'page';
	$slug = $rest;
	if ( preg_match( '#^tours/([^/]+)$#', $rest, $m ) ) {
		$type = 'tour';
		$slug = $m[1];
	} elseif ( preg_match( '#^destinations/([^/]+)$#', $rest ) ) {
		return 0;
	}
	$slug = preg_replace( '/-(?:2|3|fr|th)$/', '', $slug ) ?: $slug;
	$en   = watphou_core_find_content_id( $slug, $type );
	if ( ! $en ) {
		$en = watphou_core_find_content_id( $rest, $type );
	}
	if ( ! $en ) {
		return 0;
	}
	if ( function_exists( 'pll_get_post' ) ) {
		$tr = (int) pll_get_post( $en, $lang );
		if ( $tr ) {
			return $tr;
		}
	}
	return $en;
}

function watphou_core_term_id_from_language_path( string $taxonomy, string $lang, string $slug ): int {
	$slug = preg_replace( '/-(?:2|3|fr|th)$/', '', $slug ) ?: $slug;
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( ( ! $term || is_wp_error( $term ) ) && function_exists( 'pll_languages_list' ) ) {
		foreach ( pll_languages_list() as $code ) {
			$maybe = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'slug'       => $slug,
					'lang'       => $code,
				)
			);
			if ( $maybe && ! is_wp_error( $maybe ) ) {
				$term = $maybe[0];
				break;
			}
		}
	}
	if ( ! $term || is_wp_error( $term ) ) {
		return 0;
	}
	$id = (int) $term->term_id;
	if ( function_exists( 'pll_get_term' ) ) {
		$tr = (int) pll_get_term( $id, $lang );
		if ( $tr ) {
			return $tr;
		}
	}
	return $id;
}

function watphou_core_render_language_term( string $taxonomy, int $term_id ): void {
	global $wp_query;
	$term = get_term( $term_id, $taxonomy );
	if ( ! $term || is_wp_error( $term ) ) {
		return;
	}
	$query = new WP_Query(
		array(
			'post_type'      => 'tour',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'lang'           => watphou_core_lang_from_request(),
			'tax_query'      => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
		)
	);
	$wp_query                        = $query;
	$wp_query->is_404                = false;
	$wp_query->is_tax                = true;
	$wp_query->is_archive            = true;
	$wp_query->is_home               = false;
	$wp_query->is_singular           = false;
	$wp_query->queried_object        = $term;
	$wp_query->queried_object_id     = $term_id;
	status_header( 200 );
	nocache_headers();
	$tpl = get_query_template( 'taxonomy-' . $taxonomy );
	if ( ! $tpl ) {
		$tpl = get_taxonomy_template();
	}
	if ( ! $tpl ) {
		$tpl = get_archive_template();
	}
	if ( $tpl ) {
		include $tpl;
		exit;
	}
}

