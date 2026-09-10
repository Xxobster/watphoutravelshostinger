<?php
defined( 'ABSPATH' ) || exit;

define( 'WATPHOU_THEME_VERSION', '2.1.0' );
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
	$url = WATPHOU_THEME_URI . '/assets/images/tad-fane.jpg';
	echo '<link rel="preload" as="image" href="' . esc_url( $url ) . '">' . "\n";
}

require_once WATPHOU_THEME_PATH . '/inc/template-tags.php';
