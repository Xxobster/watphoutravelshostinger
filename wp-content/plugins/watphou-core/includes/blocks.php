<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_register_blocks(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type(
		WATPHOU_CORE_PATH . 'blocks/itinerary-day',
		array(
			'render_callback' => 'watphou_render_itinerary_day',
		)
	);
	register_block_type(
		WATPHOU_CORE_PATH . 'blocks/highlights',
		array(
			'render_callback' => 'watphou_render_highlights',
		)
	);
	register_block_type(
		WATPHOU_CORE_PATH . 'blocks/inclusions',
		array(
			'render_callback' => 'watphou_render_inclusions',
		)
	);
}

function watphou_render_itinerary_day( array $attrs, string $content ): string {
	$day   = isset( $attrs['day'] ) ? (int) $attrs['day'] : 1;
	$title = isset( $attrs['title'] ) ? esc_html( $attrs['title'] ) : '';
	$body  = wp_kses_post( $content );
	return sprintf(
		'<section class="watphou-itinerary-day"><h3 class="watphou-itinerary-day__title"><span class="watphou-itinerary-day__num">%1$s</span> %2$s</h3><div class="watphou-itinerary-day__body">%3$s</div></section>',
		esc_html( sprintf( __( 'Day %d', 'watphou-core' ), $day ) ),
		$title,
		$body
	);
}

function watphou_render_highlights( array $attrs, string $content ): string {
	$items = array_filter( array_map( 'trim', explode( "\n", wp_strip_all_tags( $content ) ) ) );
	if ( empty( $items ) ) {
		return '';
	}
	$html = '<ul class="watphou-highlights">';
	foreach ( $items as $item ) {
		$html .= '<li>' . esc_html( $item ) . '</li>';
	}
	$html .= '</ul>';
	return $html;
}

function watphou_render_inclusions( array $attrs, string $content ): string {
	$type  = isset( $attrs['type'] ) ? $attrs['type'] : 'included';
	$title = match ( $type ) {
		'excluded' => __( "What's not included", 'watphou-core' ),
		'upgrades' => __( 'Optional upgrades', 'watphou-core' ),
		default    => __( "What's included", 'watphou-core' ),
	};
	$items = array_filter( array_map( 'trim', explode( "\n", wp_strip_all_tags( $content ) ) ) );
	if ( empty( $items ) ) {
		return '';
	}
	$html = '<section class="watphou-inclusions watphou-inclusions--' . esc_attr( $type ) . '"><h3>' . esc_html( $title ) . '</h3><ul>';
	foreach ( $items as $item ) {
		$html .= '<li>' . esc_html( $item ) . '</li>';
	}
	$html .= '</ul></section>';
	return $html;
}
