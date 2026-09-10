<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_output_schema(): void {
	if ( is_front_page() ) {
		$same = array_filter(
			array(
				get_option( 'watphou_facebook', '' ),
				get_option( 'watphou_instagram', '' ),
				get_option( 'watphou_tripadvisor', '' ),
			)
		);
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => array( 'TravelAgency', 'LocalBusiness', 'Organization' ),
			'name'        => 'Watphou Travels',
			'url'         => home_url( '/' ),
			'telephone'   => get_option( 'watphou_phone', '+85620 9949 5858' ),
			'email'       => get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ),
			'address'     => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => get_option( 'watphou_address', 'Street N°5, Ban Vat Luang, Pakse, Laos' ),
				'addressLocality' => 'Pakse',
				'addressCountry'  => 'LA',
			),
		);
		if ( $same ) {
			$schema['sameAs'] = array_values( $same );
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		return;
	}

	if ( is_singular( 'tour' ) ) {
		$pid   = get_the_ID();
		$price = get_post_meta( $pid, 'tour_price_from', true );
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'TouristTrip',
			'name'        => get_the_title( $pid ),
			'description' => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $pid ) ?: get_post_field( 'post_content', $pid ) ), 40 ),
			'url'         => get_permalink( $pid ),
			'provider'    => array(
				'@type' => 'TravelAgency',
				'name'  => 'Watphou Travels',
				'url'   => home_url( '/' ),
			),
			'itinerary'   => get_post_meta( $pid, 'tour_duration', true ),
		);
		$image = get_the_post_thumbnail_url( $pid, 'large' );
		if ( $image ) {
			$schema['image'] = $image;
		}
		if ( $price && 'XX' !== $price ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'priceCurrency' => 'USD',
				'price'         => $price,
				'url'           => get_permalink( $pid ),
			);
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}
