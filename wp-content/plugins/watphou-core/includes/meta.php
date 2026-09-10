<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_register_meta(): void {
	$fields = array(
		'tour_headline'              => 'string',
		'tour_duration'              => 'string',
		'tour_price_from'            => 'string',
		'tour_currency'              => 'string',
		'tour_price_2pax'            => 'string',
		'tour_price_3_4pax'          => 'string',
		'tour_price_5_6pax'          => 'string',
		'tour_guide_supplement'      => 'string',
		'tour_accommodation_standard'=> 'string',
		'tour_comfort_upgrades'      => 'string',
		'tour_bestseller'            => 'boolean',
		'tour_priority'              => 'integer',
		'tour_whatsapp_message'      => 'string',
		'tour_legacy_slug'           => 'string',
		'testimonial_rating'         => 'number',
		'testimonial_reviewer'       => 'string',
		'testimonial_date'           => 'string',
		'testimonial_source'         => 'string',
		'testimonial_source_url'     => 'string',
	);

	foreach ( $fields as $key => $type ) {
		$object_type = str_starts_with( $key, 'testimonial_' ) ? 'testimonial' : 'tour';
		register_post_meta(
			$object_type,
			$key,
			array(
				'single'       => true,
				'type'         => $type,
				'show_in_rest' => true,
				'auth_callback'=> fn() => current_user_can( 'edit_posts' ),
			)
		);
	}
}

add_action( 'add_meta_boxes', 'watphou_core_add_meta_boxes' );

function watphou_core_add_meta_boxes(): void {
	add_meta_box(
		'watphou_tour_details',
		__( 'Tour Details (price, duration, photo notes)', 'watphou-core' ),
		'watphou_core_render_tour_meta_box',
		'tour',
		'side',
		'high'
	);
}

function watphou_core_render_tour_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'watphou_tour_meta', 'watphou_tour_meta_nonce' );
	$keys = array(
		'tour_headline'               => 'Short sales headline',
		'tour_duration'               => 'Duration (e.g. 1 Day, 3D/2N)',
		'tour_price_from'             => 'Starting price (use XX placeholder)',
		'tour_currency'               => 'Currency (USD)',
		'tour_price_2pax'             => 'Price for 2 people',
		'tour_price_3_4pax'           => 'Price for 3-4 people',
		'tour_price_5_6pax'           => 'Price for 5-6+ people',
		'tour_guide_supplement'       => 'Guide supplement',
		'tour_accommodation_standard' => 'Standard accommodation',
		'tour_comfort_upgrades'       => 'Comfort upgrades',
		'tour_whatsapp_message'       => 'WhatsApp prefill message',
		'tour_legacy_slug'            => 'Old Wix slug (for redirects)',
	);
	echo '<table class="form-table"><tbody>';
	foreach ( $keys as $key => $label ) {
		$val = get_post_meta( $post->ID, $key, true );
		printf(
			'<tr><th><label for="%1$s">%2$s</label></th><td><input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s"/></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( (string) $val )
		);
	}
	$bestseller = (bool) get_post_meta( $post->ID, 'tour_bestseller', true );
	$priority   = (int) get_post_meta( $post->ID, 'tour_priority', true );
	printf(
		'<tr><th>%s</th><td><label><input type="checkbox" name="tour_bestseller" value="1" %s/> Best seller</label></td></tr>',
		esc_html__( 'Featured', 'watphou-core' ),
		checked( $bestseller, true, false )
	);
	printf(
		'<tr><th><label for="tour_priority">%s</label></th><td><input type="number" id="tour_priority" name="tour_priority" value="%d" min="0" max="100"/></td></tr>',
		esc_html__( 'Priority order', 'watphou-core' ),
		$priority
	);
	echo '</tbody></table>';
}

add_action( 'save_post_tour', 'watphou_core_save_tour_meta' );

function watphou_core_save_tour_meta( int $post_id ): void {
	if ( ! isset( $_POST['watphou_tour_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['watphou_tour_meta_nonce'] ) ), 'watphou_tour_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$text_keys = array(
		'tour_headline', 'tour_duration', 'tour_price_from', 'tour_currency',
		'tour_price_2pax', 'tour_price_3_4pax', 'tour_price_5_6pax',
		'tour_guide_supplement', 'tour_accommodation_standard', 'tour_comfort_upgrades',
		'tour_whatsapp_message', 'tour_legacy_slug',
	);
	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	update_post_meta( $post_id, 'tour_bestseller', isset( $_POST['tour_bestseller'] ) ? 1 : 0 );
	update_post_meta( $post_id, 'tour_priority', isset( $_POST['tour_priority'] ) ? (int) $_POST['tour_priority'] : 0 );
}

function watphou_get_tour_price_display( int $post_id ): string {
	$from = get_post_meta( $post_id, 'tour_price_from', true );
	if ( ! $from || 'XX' === $from ) {
		return __( 'From $XX per person — Standard accommodation. Comfort upgrades available', 'watphou-core' );
	}
	return sprintf(
		__( 'From $%1$s per person — Standard accommodation. Comfort upgrades available', 'watphou-core' ),
		esc_html( (string) $from )
	);
}

function watphou_get_whatsapp_url( string $message = '' ): string {
	$phone = get_option( 'watphou_whatsapp', '+8562099495858' );
	$phone = preg_replace( '/[^0-9]/', '', $phone );
	$text  = rawurlencode( $message ?: 'Hello Watphou Travels, I would like to enquire about a tour.' );
	return "https://wa.me/{$phone}?text={$text}";
}

add_shortcode( 'watphou_tour_price', function () {
	if ( ! is_singular( 'tour' ) ) {
		return '';
	}
	return '<p class="watphou-tour-price-bar">' . esc_html( watphou_get_tour_price_display( get_the_ID() ) ) . '</p>';
} );
