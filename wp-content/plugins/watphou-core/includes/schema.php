<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_output_schema(): void {
	if ( is_front_page() ) {
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => array( 'TravelAgency', 'Organization' ),
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
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}
