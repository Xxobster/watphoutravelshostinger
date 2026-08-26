<?php
defined( 'ABSPATH' ) || exit;

add_shortcode( 'watphou_featured_tours', function ( $atts ) {
	$atts = shortcode_atts( array( 'count' => 4 ), $atts, 'watphou_featured_tours' );
	$query = new WP_Query(
		array(
			'post_type'      => 'tour',
			'posts_per_page' => (int) $atts['count'],
			'meta_key'       => 'tour_priority',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'   => 'tour_bestseller',
					'value' => '1',
				),
			),
		)
	);
	if ( ! $query->have_posts() ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'tour',
				'posts_per_page' => (int) $atts['count'],
				'meta_key'       => 'tour_priority',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
			)
		);
	}
	ob_start();
	echo '<div class="watphou-grid-tours">';
	while ( $query->have_posts() ) {
		$query->the_post();
		watphou_render_tour_card( get_the_ID() );
	}
	wp_reset_postdata();
	echo '</div>';
	return ob_get_clean();
} );

add_filter( 'shortcode_atts_watphou_booking_form', function ( $out, $pairs, $atts ) {
	if ( empty( $out['tour_id'] ) && is_singular( 'tour' ) ) {
		$out['tour_id'] = get_the_ID();
	}
	return $out;
}, 10, 3 );
