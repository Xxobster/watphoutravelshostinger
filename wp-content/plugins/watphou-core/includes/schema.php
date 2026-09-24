<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_schema_logo_url(): string {
	if ( function_exists( 'get_theme_file_uri' ) ) {
		return get_theme_file_uri( 'assets/images/logo-wpt.jpg' );
	}
	return '';
}

function watphou_core_schema_agency_base(): array {
	$reviews = function_exists( 'watphou_core_google_reviews_data' ) ? watphou_core_google_reviews_data() : array();
	$geo     = is_array( $reviews['geo'] ?? null ) ? $reviews['geo'] : array();
	$lat     = isset( $geo['lat'] ) ? (float) $geo['lat'] : 15.1200197;
	$lng     = isset( $geo['lng'] ) ? (float) $geo['lng'] : 105.7988082;
	$same    = array_values(
		array_filter(
			array(
				get_option( 'watphou_facebook', '' ),
				get_option( 'watphou_instagram', '' ),
				get_option( 'watphou_tripadvisor', '' ),
				(string) ( $reviews['maps_url'] ?? 'https://maps.app.goo.gl/QUTghqefjS9LCZSA8' ),
				'https://www.watphou-travels.com/',
			)
		)
	);
	$schema = array(
		'@type'           => array( 'TravelAgency', 'LocalBusiness', 'Organization', 'TouristInformationCenter' ),
		'@id'             => home_url( '/#organization' ),
		'name'            => 'Watphou Travels',
		'legalName'       => 'Watphou Travels',
		'url'             => home_url( '/' ),
		'telephone'       => get_option( 'watphou_phone', '+85620 9949 5858' ),
		'email'           => get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ),
		'image'           => watphou_core_schema_logo_url(),
		'logo'            => watphou_core_schema_logo_url(),
		'priceRange'      => '$$',
		'currenciesAccepted' => 'USD, LAK',
		'paymentAccepted' => 'Cash, Bank transfer',
		'areaServed'      => array(
			array(
				'@type' => 'AdministrativeArea',
				'name'  => 'Champasak Province, Laos',
			),
			array(
				'@type' => 'City',
				'name'  => 'Pakse',
			),
		),
		'knowsLanguage'   => array( 'en', 'fr', 'lo' ),
		'address'         => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => get_option( 'watphou_address', 'Street N°5, Ban Vat Luang, Pakse, Laos' ),
			'addressLocality' => 'Pakse',
			'addressRegion'   => 'Champasak',
			'addressCountry'  => 'LA',
		),
		'geo'             => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => $lat,
			'longitude' => $lng,
		),
		'hasMap'          => (string) ( $reviews['maps_place_url'] ?? $reviews['maps_url'] ?? 'https://maps.app.goo.gl/QUTghqefjS9LCZSA8' ),
		'openingHoursSpecification' => array(
			array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
				'opens'     => '07:30',
				'closes'    => '16:00',
			),
		),
		'sameAs'          => $same,
		'description'     => 'Local tour agency in Pakse, Laos. Private tours of the Bolaven Plateau, UNESCO Vat Phou, Champasak and the 4000 Islands.',
	);
	$rating = (float) ( $reviews['rating'] ?? 0 );
	$count  = (int) ( $reviews['review_count'] ?? 0 );
	if ( $rating > 0 && $count > 0 ) {
		$schema['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $rating,
			'reviewCount' => $count,
			'bestRating'  => 5,
			'worstRating' => 1,
		);
	}
	$review_nodes = array();
	foreach ( (array) ( $reviews['reviews'] ?? array() ) as $row ) {
		if ( empty( $row['author'] ) || empty( $row['text'] ) ) {
			continue;
		}
		$node = array(
			'@type'        => 'Review',
			'author'       => array(
				'@type' => 'Person',
				'name'  => $row['author'],
			),
			'reviewBody'   => $row['text'],
			'reviewRating' => array(
				'@type'       => 'Rating',
				'ratingValue' => (int) ( $row['rating'] ?? 5 ),
				'bestRating'  => 5,
				'worstRating' => 1,
			),
			'url'          => (string) ( $row['maps_url'] ?? '' ),
		);
		if ( ! empty( $row['date'] ) ) {
			$node['datePublished'] = $row['date'];
		}
		$review_nodes[] = $node;
	}
	if ( $review_nodes ) {
		$schema['review'] = $review_nodes;
	}
	return $schema;
}

function watphou_core_output_schema(): void {
	if ( is_front_page() ) {
		$schema            = watphou_core_schema_agency_base();
		$schema['@context'] = 'https://schema.org';
		$search_url         = home_url( '/?s={search_term_string}' );
		$graph              = array(
			$schema,
			array(
				'@type'           => 'WebSite',
				'@id'             => home_url( '/#website' ),
				'url'             => home_url( '/' ),
				'name'            => 'Watphou Travels',
				'inLanguage'      => function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en',
				'publisher'       => array( '@id' => home_url( '/#organization' ) ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => $search_url,
					'query-input' => 'required name=search_term_string',
				),
			),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		return;
	}

	if ( is_singular( 'tour' ) ) {
		$pid   = get_the_ID();
		$price = get_post_meta( $pid, 'tour_price_from', true );
		$code  = function_exists( 'watphou_core_tour_package_code' )
			? watphou_core_tour_package_code( $pid )
			: (string) get_post_meta( $pid, 'tour_code', true );
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'TouristTrip',
			'@id'         => get_permalink( $pid ) . '#trip',
			'name'        => get_the_title( $pid ),
			'description' => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $pid ) ?: get_post_field( 'post_content', $pid ) ), 40 ),
			'url'         => get_permalink( $pid ),
			'touristType' => 'Private tour',
			'provider'    => array(
				'@type' => 'TravelAgency',
				'@id'   => home_url( '/#organization' ),
				'name'  => 'Watphou Travels',
				'url'   => home_url( '/' ),
			),
			'itinerary'   => get_post_meta( $pid, 'tour_duration', true ),
			'areaServed'  => 'Southern Laos',
		);
		if ( $code ) {
			$schema['identifier'] = $code;
		}
		$image = function_exists( 'watphou_core_tour_featured_url' )
			? watphou_core_tour_featured_url( $pid, 'large' )
			: get_the_post_thumbnail_url( $pid, 'large' );
		if ( $image ) {
			$schema['image'] = $image;
		}
		if ( $price && 'XX' !== $price ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'priceCurrency' => 'USD',
				'price'         => $price,
				'url'           => get_permalink( $pid ),
				'availability'  => 'https://schema.org/InStock',
			);
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
