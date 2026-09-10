<?php
/**
 * Tours list columns: photo, price, duration. Hide Customizer for logged-in managers on the front.
 */
defined( 'ABSPATH' ) || exit;

add_filter( 'manage_tour_posts_columns', 'watphou_core_tour_columns' );
add_action( 'manage_tour_posts_custom_column', 'watphou_core_tour_column_content', 10, 2 );
add_filter( 'manage_edit-tour_sortable_columns', 'watphou_core_tour_sortable_columns' );
add_action( 'restrict_manage_posts', 'watphou_core_tour_list_help', 20 );

function watphou_core_tour_columns( array $columns ): array {
	unset(
		$columns['taxonomy-tour_type'],
		$columns['taxonomy-duration_cat'],
		$columns['taxonomy-duration'],
		$columns['wpseo-score'],
		$columns['wpseo-score-readability'],
		$columns['wpseo-title'],
		$columns['wpseo-metadesc'],
		$columns['wpseo-focuskw'],
		$columns['wpseo-links'],
		$columns['wpseo-linked']
	);
	$out = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$out['watphou_thumb'] = __( 'Photo', 'watphou-core' );
			$out[ $key ]          = $label;
			$out['watphou_price'] = __( 'From $', 'watphou-core' );
			$out['watphou_dur']   = __( 'Duration', 'watphou-core' );
			continue;
		}
		$out[ $key ] = $label;
	}
	return $out;
}

function watphou_core_tour_column_content( string $column, int $post_id ): void {
	if ( 'watphou_thumb' === $column ) {
		$thumb = get_the_post_thumbnail( $post_id, array( 60, 60 ) );
		echo $thumb ? $thumb : '&mdash;';
		return;
	}
	if ( 'watphou_price' === $column ) {
		$price = (string) get_post_meta( $post_id, 'tour_price_from', true );
		echo esc_html( $price !== '' ? $price : 'XX' );
		return;
	}
	if ( 'watphou_dur' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, 'tour_duration', true ) );
	}
}

function watphou_core_tour_sortable_columns( array $columns ): array {
	$columns['watphou_price'] = 'tour_price_from';
	return $columns;
}

function watphou_core_tour_list_help(): void {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-tour' !== $screen->id ) {
		return;
	}
	$url = admin_url( 'admin.php?page=watphou-tour-desk' );
	echo '<a class="button button-primary" style="margin:0 8px 4px 0;" href="' . esc_url( $url ) . '">' . esc_html__( 'Quick edit prices &amp; photos', 'watphou-core' ) . '</a>';
}

add_action( 'admin_head', 'watphou_core_tour_list_css' );

function watphou_core_tour_list_css(): void {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-tour' !== $screen->id ) {
		return;
	}
	echo '<style>.column-watphou_thumb{width:70px}.column-watphou_thumb img{width:60px;height:60px;object-fit:cover;border-radius:4px}.column-watphou_price,.column-watphou_dur{width:110px}</style>';
}

add_action( 'wp_before_admin_bar_render', 'watphou_core_hide_customize_bar' );

function watphou_core_hide_customize_bar(): void {
	global $wp_admin_bar;
	if ( ! $wp_admin_bar ) {
		return;
	}
	$wp_admin_bar->remove_node( 'customize' );
	$wp_admin_bar->remove_node( 'customize-wp-core' );
}

add_action( 'add_meta_boxes_tour', 'watphou_core_tour_editor_help_box' );

function watphou_core_tour_editor_help_box(): void {
	add_meta_box(
		'watphou_tour_how',
		__( 'How to edit this tour', 'watphou-core' ),
		static function () {
			$desk = admin_url( 'admin.php?page=watphou-tour-desk' );
			echo '<p>' . esc_html__( 'Price and the main photo are easiest on the Quick edit tours screen. This page is for the long text (dream paragraph, itinerary, included).', 'watphou-core' ) . '</p>';
			echo '<p><a class="button button-primary" href="' . esc_url( $desk ) . '">' . esc_html__( 'Quick edit prices &amp; photos', 'watphou-core' ) . '</a></p>';
			echo '<p>' . esc_html__( 'Main photo: use the Featured image box in the right sidebar on this screen, or Quick edit tours.', 'watphou-core' ) . '</p>';
		},
		'tour',
		'side',
		'high'
	);
}
