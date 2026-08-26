<?php
defined( 'ABSPATH' ) || exit;

define( 'WATPHOU_THEME_VERSION', '1.0.0' );
define( 'WATPHOU_THEME_PATH', get_template_directory() );
define( 'WATPHOU_THEME_URI', get_template_directory_uri() );

add_action( 'after_setup_theme', 'watphou_theme_setup' );
add_action( 'wp_enqueue_scripts', 'watphou_theme_assets' );
add_action( 'init', 'watphou_register_patterns' );

function watphou_theme_setup(): void {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
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
			'whatsapp' => function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858',
		)
	);
}

function watphou_register_patterns(): void {
	register_block_pattern_category( 'watphou', array( 'label' => __( 'Watphou Travels', 'watphou-travels' ) ) );
}

require_once WATPHOU_THEME_PATH . '/inc/template-tags.php';
