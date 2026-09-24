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
		'tour_price_note'            => 'string',
		'tour_code'                  => 'string',
		'tour_gallery'               => 'string',
		'tour_gallery_managed'       => 'string',
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
		'tour_whatsapp_message', 'tour_legacy_slug', 'tour_price_note', 'tour_code',
	);
	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	update_post_meta( $post_id, 'tour_bestseller', isset( $_POST['tour_bestseller'] ) ? 1 : 0 );
	update_post_meta( $post_id, 'tour_priority', isset( $_POST['tour_priority'] ) ? (int) $_POST['tour_priority'] : 0 );
}

function watphou_tour_is_day_only( int $post_id ): bool {
	$terms = wp_get_post_terms( $post_id, 'duration', array( 'fields' => 'slugs' ) );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( (array) $terms as $slug ) {
			$base = preg_replace( '/-(fr|th)$/', '', (string) $slug );
			if ( '1-day' === $base ) {
				return true;
			}
		}
	}
	$duration = strtolower( (string) get_post_meta( $post_id, 'tour_duration', true ) );
	return (bool) preg_match( '/^(1 day|1-day|full-?day|half-?day)/', $duration );
}

/**
 * English post plus Polylang translations (same tour).
 *
 * @return int[]
 */
function watphou_core_tour_translation_ids( int $post_id ): array {
	$ids = array( $post_id );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$tr = pll_get_post_translations( $post_id );
		if ( $tr ) {
			$ids = array_map( 'intval', $tr );
		}
	}
	$out = array();
	foreach ( $ids as $id ) {
		if ( $id && 'tour' === get_post_type( $id ) ) {
			$out[] = $id;
		}
	}
	return array_values( array_unique( $out ) );
}

function watphou_normalize_price_from( string $price ): string {
	$price = trim( $price );
	$price = ltrim( $price, '$' );
	return ( '' === $price ) ? 'XX' : $price;
}

function watphou_get_tour_price_amount( int $post_id ): string {
	return watphou_normalize_price_from( (string) get_post_meta( $post_id, 'tour_price_from', true ) );
}

/**
 * Put the live From-$ amount into a stored price sentence.
 */
function watphou_apply_amount_to_price_note( string $note, string $amount ): string {
	$note   = trim( $note );
	$amount = watphou_normalize_price_from( $amount );
	if ( '' === $note ) {
		return '';
	}
	// An earlier preg_replace used "$120" as the replacement; PHP treated $12 as a
	// capture group and stored "From 0 per person…". Restore those sentences.
	$note = preg_replace_callback(
		'/From 0 per person/i',
		static function () use ( $amount ) {
			return 'From $' . $amount . ' per person';
		},
		$note,
		1
	);
	if ( ! is_string( $note ) ) {
		return '';
	}
	$replaced = preg_replace_callback(
		'/\$(\d+(?:[.,]\d+)?|XX)\b/i',
		static function () use ( $amount ) {
			return '$' . $amount;
		},
		$note,
		1
	);
	if ( ! is_string( $replaced ) ) {
		return $note;
	}
	if ( 0 !== strcasecmp( $amount, 'XX' ) ) {
		$bare = preg_replace( '/\bXX\b/', $amount, $replaced, 1 );
		if ( is_string( $bare ) ) {
			$replaced = $bare;
		}
	}
	return $replaced;
}

function watphou_core_set_tour_price( int $post_id, string $price ): void {
	$price = watphou_normalize_price_from( $price );
	update_post_meta( $post_id, 'tour_price_from', $price );
	$note = trim( (string) get_post_meta( $post_id, 'tour_price_note', true ) );
	if ( '' !== $note ) {
		update_post_meta( $post_id, 'tour_price_note', watphou_apply_amount_to_price_note( $note, $price ) );
	}
}

function watphou_core_set_tour_duration( int $post_id, string $duration ): void {
	update_post_meta( $post_id, 'tour_duration', $duration );
	if ( '' === $duration || ! taxonomy_exists( 'duration' ) ) {
		return;
	}
	$slug = function_exists( 'watphou_core_map_duration_term' ) ? watphou_core_map_duration_term( $duration ) : '1-day';
	$term = get_term_by( 'slug', $slug, 'duration' );
	$tid  = ( $term && ! is_wp_error( $term ) ) ? (int) $term->term_id : 0;
	$lang = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $post_id ) : '';
	if ( $tid && $lang && function_exists( 'pll_get_term' ) ) {
		$translated = pll_get_term( $tid, $lang );
		if ( $translated ) {
			$tid = (int) $translated;
		}
	}
	if ( $tid ) {
		wp_set_object_terms( $post_id, $tid, 'duration', false );
	} else {
		wp_set_object_terms( $post_id, $slug, 'duration', false );
	}
}

function watphou_get_tour_price_display( int $post_id ): string {
	$amount = watphou_get_tour_price_amount( $post_id );
	$note   = trim( (string) get_post_meta( $post_id, 'tour_price_note', true ) );
	if ( '' !== $note && function_exists( 'watphou_core_translate_public_string' ) ) {
		$note = watphou_core_translate_public_string( $note );
	}
	if ( '' !== $note ) {
		return watphou_apply_amount_to_price_note( $note, $amount );
	}
	if ( watphou_tour_is_day_only( $post_id ) ) {
		return sprintf(
			/* translators: %s is a price amount or XX */
			__( 'From $%s per person — Standard.', 'watphou-core' ),
			$amount
		);
	}
	return sprintf(
		/* translators: %s is a price amount or XX */
		__( 'From $%s per person — Standard accommodation. Comfort upgrades available.', 'watphou-core' ),
		$amount
	);
}

/**
 * Copy English Quick Edit prices onto stored notes and French/Thai copies once.
 */
function watphou_core_maybe_sync_live_prices(): void {
	if ( get_option( 'watphou_sync_live_prices' ) === '1.6.8' ) {
		return;
	}
	$args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$args['lang'] = pll_default_language() ?: 'en';
	}
	$posts = get_posts( $args );
	foreach ( $posts as $post ) {
		$id    = (int) $post->ID;
		$price = (string) get_post_meta( $id, 'tour_price_from', true );
		foreach ( watphou_core_tour_translation_ids( $id ) as $tid ) {
			watphou_core_set_tour_price( $tid, $price );
		}
	}
	update_option( 'watphou_sync_live_prices', '1.6.8' );
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
