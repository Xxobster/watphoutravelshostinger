<?php
defined( 'ABSPATH' ) || exit;

define( 'WATPHOU_THEME_VERSION', '2.5.5' );
define( 'WATPHOU_THEME_PATH', get_template_directory() );
define( 'WATPHOU_THEME_URI', get_template_directory_uri() );

add_action( 'after_setup_theme', 'watphou_theme_setup' );
add_action( 'wp_enqueue_scripts', 'watphou_theme_assets' );

function watphou_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo' );
	add_image_size( 'tour-card', 600, 400, true );
	add_image_size( 'tour-hero', 1600, 900, true );
	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'watphou-travels' ),
			'footer'  => __( 'Footer Menu', 'watphou-travels' ),
		)
	);
}

function watphou_theme_assets(): void {
	wp_enqueue_style( 'watphou-theme', get_stylesheet_uri(), array(), WATPHOU_THEME_VERSION );
	wp_enqueue_style( 'watphou-theme-extra', WATPHOU_THEME_URI . '/assets/css/theme.css', array(), WATPHOU_THEME_VERSION );
	wp_enqueue_script( 'watphou-theme', WATPHOU_THEME_URI . '/assets/js/theme.js', array(), WATPHOU_THEME_VERSION, true );
	wp_localize_script(
		'watphou-theme',
		'watphouTheme',
		array(
			'i18n' => array(
				'viewer' => __( 'Image viewer', 'watphou-travels' ),
				'close'  => __( 'Close', 'watphou-travels' ),
				'prev'   => __( 'Previous image', 'watphou-travels' ),
				'next'   => __( 'Next image', 'watphou-travels' ),
			),
		)
	);
}

add_filter( 'wp_get_attachment_image_attributes', 'watphou_lazy_images', 20 );

function watphou_lazy_images( array $attr ): array {
	if ( empty( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}
	$attr['decoding'] = 'async';
	return $attr;
}

add_action( 'wp_head', 'watphou_favicons', 1 );
function watphou_favicons(): void {
	$base = WATPHOU_THEME_URI . '/assets/images';
	echo '<link rel="icon" href="' . esc_url( $base . '/favicon-32.jpg' ) . '" sizes="32x32" type="image/jpeg">' . "\n";
	echo '<link rel="icon" href="' . esc_url( $base . '/favicon-192.jpg' ) . '" sizes="192x192" type="image/jpeg">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $base . '/favicon-192.jpg' ) . '" sizes="180x180">' . "\n";
	echo '<link rel="shortcut icon" href="' . esc_url( $base . '/favicon-32.jpg' ) . '" type="image/jpeg">' . "\n";
}

add_action( 'wp_head', 'watphou_preload_hero', 1 );
function watphou_preload_hero(): void {
	if ( ! is_front_page() ) {
		return;
	}
	$slides = function_exists( 'watphou_home_hero_slides' ) ? watphou_home_hero_slides() : array();
	if ( ! $slides ) {
		return;
	}
	echo '<link rel="preload" as="image" href="' . esc_url( $slides[0]['url'] ) . '" fetchpriority="high"';
	$srcset = function_exists( 'watphou_hero_srcset' ) ? watphou_hero_srcset( $slides[0]['url'] ) : '';
	if ( $srcset ) {
		echo ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="100vw"';
	}
	echo '>' . "\n";
}

require_once WATPHOU_THEME_PATH . '/inc/template-tags.php';

add_filter( 'script_loader_tag', 'watphou_defer_third_party_scripts', 20, 3 );

/**
 * Hostinger Reach marketing scripts stay available, but they must not compete
 * with the first paint. defer keeps their order and runs them after parsing.
 *
 * @param string $tag    Script tag.
 * @param string $handle WordPress handle.
 * @param string $src    Script URL.
 */
function watphou_defer_third_party_scripts( string $tag, string $handle, string $src ): string {
	unset( $handle );
	if ( is_admin() || '' === $src ) {
		return $tag;
	}
	$delay = str_contains( $src, 'cdn-reach.hostinger.com' ) || str_contains( $src, '/hostinger-reach/' );
	if ( ! $delay || str_contains( $tag, ' defer' ) || str_contains( $tag, ' async' ) ) {
		return $tag;
	}
	return str_replace( '<script ', '<script defer ', $tag );
}

add_filter( 'template_include', 'watphou_theme_template_include' );

/**
 * Duration listing pages, Destinations hub, and About Us use dedicated templates (not empty page content).
 */
function watphou_theme_template_include( string $template ): string {
	if ( ! is_page() ) {
		return $template;
	}
	$id = get_queried_object_id();
	$en = $id;
	if ( function_exists( 'pll_get_post' ) ) {
		$maybe = pll_get_post( $id, 'en' );
		if ( $maybe ) {
			$en = (int) $maybe;
		}
	}
	$slug = (string) get_post_field( 'post_name', $en );
	$slug = preg_replace( '/-(?:2|3|fr|th)$/', '', $slug ) ?: $slug;
	$map  = array(
		'day-tours'       => '1-day',
		'2-day-tours'     => '2-day',
		'3-day-tours'     => '3-day',
		'4-6-day-tours'   => '4-6-day',
	);
	if ( isset( $map[ $slug ] ) ) {
		$GLOBALS['watphou_listing_duration'] = $map[ $slug ];
		$custom = WATPHOU_THEME_PATH . '/template-tour-listing.php';
		if ( is_readable( $custom ) ) {
			return $custom;
		}
	}
	if ( 'destinations' === $slug ) {
		$custom = WATPHOU_THEME_PATH . '/template-destinations.php';
		if ( is_readable( $custom ) ) {
			return $custom;
		}
	}
	if ( 'about-us' === $slug ) {
		$custom = WATPHOU_THEME_PATH . '/template-about.php';
		if ( is_readable( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}

add_action( 'pre_get_posts', 'watphou_theme_catalog_queries' );

function watphou_theme_catalog_queries( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'tour' ) || $query->is_tax( 'destination' ) || $query->is_tax( 'duration' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
		if ( function_exists( 'watphou_core_current_lang_slug' ) ) {
			$query->set( 'lang', watphou_core_current_lang_slug() );
		}
	}
}
