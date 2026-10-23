<?php
/**
 * French / Thai front-end text fixes: SEO titles and descriptions, schema, image alt
 * texts, review dates and a few strings stored in page content.
 * Strings live in data/ui_extras_i18n.json (English text => fr/th); tour descriptions
 * live in data/fr_tour_meta.json. A missing translation always falls back to English.
 */
defined( 'ABSPATH' ) || exit;

function watphou_core_fx_lang(): string {
	return function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
}

function watphou_core_fx_active(): bool {
	return ! is_admin() && in_array( watphou_core_fx_lang(), array( 'fr', 'th' ), true );
}

/**
 * Destination wording used in SEO titles and descriptions (French only).
 *
 * @return array<string, array{0: string, 1: string}> English term name => [ title phrase, description phrase ].
 */
function watphou_core_fx_destination_phrases(): array {
	return array(
		'4000 Islands (Si Phan Don)' => array( 'aux 4 000 îles (Si Phan Don)', 'aux 4 000 îles (Si Phan Don)' ),
		'Bolaven Plateau'            => array( 'sur le plateau des Bolovens', 'sur le plateau des Bolovens' ),
		'Vat Phou & Champasak'       => array( 'à Vat Phou et Champasak', 'à Vat Phou et Champasak' ),
		'Pakse & Surroundings'       => array( 'à Pakse et dans ses environs', 'à Pakse et dans ses environs' ),
	);
}

/**
 * Translate one SEO / schema string. Exact matches come from the shared map.
 */
function watphou_core_fx_tr( $text ) {
	if ( ! is_string( $text ) || '' === $text || ! watphou_core_fx_active() ) {
		return $text;
	}
	$plain = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
	if ( function_exists( 'watphou_core_translate_public_string' ) ) {
		$out = watphou_core_translate_public_string( $plain );
		if ( $out !== trim( $plain ) ) {
			return $out;
		}
	}
	if ( 'fr' === watphou_core_fx_lang() ) {
		$phrases = watphou_core_fx_destination_phrases();
		if ( preg_match( '/^(.+) tours - Watphou Travels$/u', $plain, $m ) && isset( $phrases[ $m[1] ] ) ) {
			return 'Circuits ' . $phrases[ $m[1] ][0] . ' - Watphou Travels';
		}
		if ( preg_match( '/^Private tours to (.+) in Southern Laos, organised from Pakse by Watphou Travels\.$/u', $plain, $m ) && isset( $phrases[ $m[1] ] ) ) {
			return 'Circuits privés ' . $phrases[ $m[1] ][1] . ' dans le sud du Laos, organisés depuis Pakse par Watphou Travels.';
		}
	}
	return $text;
}

/**
 * French description of the tour being viewed, or '' when there is none.
 */
function watphou_core_fx_tour_meta(): string {
	static $map = null;
	if ( 'fr' !== watphou_core_fx_lang() || ! is_singular( 'tour' ) ) {
		return '';
	}
	if ( null === $map ) {
		$map  = array();
		$path = WATPHOU_CORE_PATH . 'data/fr_tour_meta.json';
		if ( is_readable( $path ) ) {
			$raw = json_decode( (string) file_get_contents( $path ), true );
			if ( is_array( $raw ) && isset( $raw['fr'] ) && is_array( $raw['fr'] ) ) {
				$map = $raw['fr'];
			}
		}
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return '';
	}
	$slug = function_exists( 'watphou_core_canonical_content_slug' ) ? watphou_core_canonical_content_slug( (string) $post->post_name ) : (string) $post->post_name;
	return isset( $map[ $slug ] ) ? (string) $map[ $slug ] : '';
}

function watphou_core_fx_filter_title( $title ) {
	return watphou_core_fx_tr( $title );
}

function watphou_core_fx_filter_desc( $desc ) {
	$tour = watphou_core_fx_tour_meta();
	if ( '' !== $tour ) {
		return $tour;
	}
	return watphou_core_fx_tr( $desc );
}

foreach ( array( 'pre_get_document_title', 'wpseo_title', 'wpseo_opengraph_title', 'wpseo_twitter_title' ) as $watphou_fx_hook ) {
	add_filter( $watphou_fx_hook, 'watphou_core_fx_filter_title', 101 );
}
foreach ( array( 'wpseo_metadesc', 'wpseo_opengraph_desc', 'wpseo_twitter_description' ) as $watphou_fx_hook ) {
	add_filter( $watphou_fx_hook, 'watphou_core_fx_filter_desc', 101 );
}
unset( $watphou_fx_hook );

/**
 * Yoast JSON-LD: page name / description and breadcrumb names.
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function watphou_core_fx_schema_piece( $data ) {
	if ( ! is_array( $data ) || ! watphou_core_fx_active() ) {
		return $data;
	}
	foreach ( array( 'name', 'description' ) as $key ) {
		if ( isset( $data[ $key ] ) && is_string( $data[ $key ] ) ) {
			$data[ $key ] = watphou_core_fx_tr( $data[ $key ] );
		}
	}
	$tour = watphou_core_fx_tour_meta();
	if ( '' !== $tour ) {
		$data['description'] = $tour;
		$post                = get_queried_object();
		if ( $post instanceof WP_Post && isset( $data['name'] ) ) {
			$data['name'] = wp_strip_all_tags( $post->post_title ) . ' | Circuit privé | Watphou Travels';
		}
	}
	return $data;
}
add_filter( 'wpseo_schema_webpage', 'watphou_core_fx_schema_piece', 101 );
add_filter( 'wpseo_schema_website', 'watphou_core_fx_schema_piece', 101 );
add_filter( 'wpseo_schema_organization', 'watphou_core_fx_schema_piece', 101 );

function watphou_core_fx_schema_breadcrumb( $data ) {
	if ( ! is_array( $data ) || ! watphou_core_fx_active() || empty( $data['itemListElement'] ) || ! is_array( $data['itemListElement'] ) ) {
		return $data;
	}
	foreach ( $data['itemListElement'] as $i => $item ) {
		if ( is_array( $item ) && isset( $item['name'] ) && is_string( $item['name'] ) ) {
			$data['itemListElement'][ $i ]['name'] = watphou_core_fx_tr( $item['name'] );
		}
	}
	return $data;
}
add_filter( 'wpseo_schema_breadcrumb', 'watphou_core_fx_schema_breadcrumb', 101 );

/**
 * Page titles stored in English on the French copy (breadcrumb and heading).
 */
function watphou_core_fx_the_title( $title ) {
	if ( ! is_string( $title ) || '' === $title || ! watphou_core_fx_active() ) {
		return $title;
	}
	static $exact = array( 'Contact Us', 'About Us' );
	return in_array( $title, $exact, true ) ? watphou_core_fx_tr( $title ) : $title;
}
add_filter( 'the_title', 'watphou_core_fx_the_title', 20 );

/**
 * English sentences stored in the French copy of the Contact / Cancellation pages.
 */
function watphou_core_fx_the_content( $content ) {
	if ( ! is_string( $content ) || is_admin() || 'fr' !== watphou_core_fx_lang() || ! in_the_loop() ) {
		return $content;
	}
	$pairs = array(
		'Planning a trip in Southern Laos? Contact our Pakse team by WhatsApp, phone or email and tell us your dates and travel plans.' => null,
		'Phone/WhatsApp:'                                  => null,
		'Email:'                                           => null,
		'Address: Street N°5, Ban Vat Luang, Pakse, Laos'  => null,
	);
	foreach ( $pairs as $en => $unused ) {
		$fr = watphou_core_fx_tr( $en );
		if ( $fr !== $en ) {
			$content = str_replace( $en, $fr, $content );
		}
	}
	// "a@x.com or b@x.com" between two mail links.
	$content = preg_replace( '#(</a>)\s+or\s+(<a )#', '$1 ou $2', $content ) ?? $content;
	return $content;
}
add_filter( 'the_content', 'watphou_core_fx_the_content', 20 );

/**
 * Image alt texts saved in English in the media library.
 */
function watphou_core_fx_image_alt( $value, $object_id, $meta_key, $single ) {
	static $busy = false;
	if ( $busy || '_wp_attachment_image_alt' !== $meta_key || ! watphou_core_fx_active() ) {
		return $value;
	}
	$busy = true;
	$alt  = get_post_meta( $object_id, '_wp_attachment_image_alt', true );
	$busy = false;
	if ( ! is_string( $alt ) || '' === $alt ) {
		return $value;
	}
	$fr = watphou_core_fx_tr( $alt );
	if ( $fr === $alt ) {
		return $value;
	}
	return $single ? $fr : array( $fr );
}
add_filter( 'get_post_metadata', 'watphou_core_fx_image_alt', 10, 4 );

/**
 * Google review dates arrive in English ("5 months ago", "Jun 9, 2026").
 */
function watphou_core_fx_review_label( string $label ): string {
	if ( 'fr' !== watphou_core_fx_lang() || '' === $label ) {
		return $label;
	}
	$units = array(
		'year'   => array( 'an', 'ans' ),
		'month'  => array( 'mois', 'mois' ),
		'week'   => array( 'semaine', 'semaines' ),
		'day'    => array( 'jour', 'jours' ),
		'hour'   => array( 'heure', 'heures' ),
		'minute' => array( 'minute', 'minutes' ),
	);
	$l = strtolower( trim( $label ) );
	if ( preg_match( '/^(a|an|\d+)\s+(year|month|week|day|hour|minute)s?\s+ago$/', $l, $m ) ) {
		$n = ( 'a' === $m[1] || 'an' === $m[1] ) ? 1 : (int) $m[1];
		return 'il y a ' . $n . ' ' . $units[ $m[2] ][ $n > 1 ? 1 : 0 ];
	}
	if ( 'yesterday' === $l ) {
		return 'hier';
	}
	if ( 'today' === $l ) {
		return 'aujourd’hui';
	}
	if ( preg_match( '/^([A-Z][a-z]{2})[a-z]*\.? (\d{1,2}), (\d{4})$/', trim( $label ), $m ) ) {
		$months = array( 'Jan' => 'janvier', 'Feb' => 'février', 'Mar' => 'mars', 'Apr' => 'avril', 'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juillet', 'Aug' => 'août', 'Sep' => 'septembre', 'Oct' => 'octobre', 'Nov' => 'novembre', 'Dec' => 'décembre' );
		if ( isset( $months[ $m[1] ] ) ) {
			$day = (int) $m[2];
			return ( 1 === $day ? '1er' : (string) $day ) . ' ' . $months[ $m[1] ] . ' ' . $m[3];
		}
	}
	return $label;
}

/**
 * Thai destination names: the terms are stored in English, so translate them when read on the public site.
 */
function watphou_core_fx_term_name( $term, $taxonomy = '' ) {
	if ( ! $term instanceof WP_Term || 'destination' !== $term->taxonomy || 'th' !== watphou_core_fx_lang() || ! watphou_core_fx_active() ) {
		return $term;
	}
	$th = watphou_core_fx_tr( $term->name );
	if ( $th === $term->name ) {
		return $term;
	}
	$copy       = clone $term;
	$copy->name = $th;
	return $copy;
}
add_filter( 'get_term', 'watphou_core_fx_term_name', 20, 2 );
