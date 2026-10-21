<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'watphou_core_configure_yoast_defaults', 20 );
add_action( 'init', 'watphou_core_configure_yoast_defaults', 25 );
add_action( 'init', 'watphou_core_maybe_fix_quote_yoast_titles', 54 );
add_action( 'save_post', 'watphou_core_sync_yoast_for_post', 30, 2 );
add_filter( 'pre_get_document_title', 'watphou_core_quote_document_title', 99 );
add_filter( 'wpseo_title', 'watphou_core_quote_document_title', 99 );
add_filter( 'wpseo_opengraph_title', 'watphou_core_quote_document_title', 99 );
add_filter( 'wpseo_twitter_title', 'watphou_core_quote_document_title', 99 );
add_filter( 'pre_get_document_title', 'watphou_core_tour_document_title', 100 );
add_filter( 'wpseo_title', 'watphou_core_tour_document_title', 100 );
add_filter( 'wpseo_opengraph_title', 'watphou_core_tour_document_title', 100 );
add_filter( 'wpseo_twitter_title', 'watphou_core_tour_document_title', 100 );
add_filter( 'wp_get_attachment_image_attributes', 'watphou_core_default_image_alt', 10, 2 );
add_action( 'wp_head', 'watphou_core_tracking_head', 2 );
add_action( 'init', 'watphou_core_complete_seo_settings', 30 );
add_action( 'wp_head', 'watphou_core_extra_seo_meta', 3 );
add_filter( 'wpseo_opengraph_image', 'watphou_core_default_og_image', 20 );
add_filter( 'wpseo_twitter_image', 'watphou_core_default_og_image', 20 );
add_filter( 'wpseo_metadesc', 'watphou_core_fallback_metadesc', 20 );
add_filter( 'wpseo_canonical', 'watphou_core_https_canonical', 20 );

function watphou_core_yoast_set( string $option_group, string $key, $value ): void {
	if ( class_exists( 'WPSEO_Options' ) ) {
		WPSEO_Options::set( $key, $value );
		return;
	}
	$all = get_option( $option_group, array() );
	if ( ! is_array( $all ) ) {
		$all = array();
	}
	$all[ $key ] = $value;
	update_option( $option_group, $all );
}

function watphou_core_configure_yoast_defaults(): void {
	if ( get_option( 'watphou_yoast_configured' ) ) {
		return;
	}
	if ( ! class_exists( 'WPSEO_Options' ) ) {
		return;
	}

	WPSEO_Options::set( 'company_or_person', 'company' );
	WPSEO_Options::set( 'company_name', 'Watphou Travels' );
	WPSEO_Options::set( 'company_or_person_user_id', false );
	WPSEO_Options::set( 'website_name', 'Watphou Travels' );
	WPSEO_Options::set( 'alternate_website_name', 'Watphou Travels — Southern Laos' );
	WPSEO_Options::set( 'company_alternate_name', 'Watphou Travels Pakse' );
	WPSEO_Options::set( 'org-description', 'Local tour agency in Pakse, Laos. Private tours of the Bolaven Plateau, Vat Phou, Champasak and the 4000 Islands.' );
	WPSEO_Options::set( 'org-email', get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ) );
	WPSEO_Options::set( 'org-phone', get_option( 'watphou_phone', '+85620 9949 5858' ) );
	WPSEO_Options::set( 'org-legal-name', 'Watphou Travels' );

	WPSEO_Options::set( 'title-home-wpseo', 'Watphou Travels %%sep%% Private tours in Southern Laos' );
	WPSEO_Options::set( 'metadesc-home-wpseo', 'Private tours from Pakse: Bolaven Plateau waterfalls, UNESCO Vat Phou, Champasak and the 4000 Islands. Local team, European standards.' );
	WPSEO_Options::set( 'open_graph_frontpage_title', 'Watphou Travels %%sep%% Private tours in Southern Laos' );
	WPSEO_Options::set( 'open_graph_frontpage_desc', 'Private tours from Pakse across the Bolaven Plateau, Vat Phou, Champasak and the 4000 Islands.' );

	WPSEO_Options::set( 'title-tour', '%%title%% %%sep%% Private tour %%sep%% Watphou Travels' );
	WPSEO_Options::set( 'metadesc-tour', 'Private %%title%% from Pakse with Watphou Travels. Local team, flexible dates. WhatsApp +85620 9949 5858.' );
	WPSEO_Options::set( 'title-ptarchive-tour', 'Private tours in Southern Laos %%sep%% Watphou Travels' );
	WPSEO_Options::set( 'metadesc-ptarchive-tour', 'Browse private day tours and multi-day journeys in Southern Laos: Bolaven Plateau, Vat Phou, Champasak and the 4000 Islands.' );

	WPSEO_Options::set( 'title-page', '%%title%% %%sep%% Watphou Travels' );
	WPSEO_Options::set( 'metadesc-page', '%%excerpt%%' );

	WPSEO_Options::set( 'title-tax-destination', '%%term_title%% tours %%sep%% Watphou Travels' );
	WPSEO_Options::set( 'metadesc-tax-destination', 'Private tours to %%term_title%% in Southern Laos, organised from Pakse by Watphou Travels.' );

	WPSEO_Options::set( 'disable-author', true );
	WPSEO_Options::set( 'disable-date', true );
	WPSEO_Options::set( 'disable-post_format', true );
	WPSEO_Options::set( 'noindex-archive-wpseo', true );
	WPSEO_Options::set( 'breadcrumbs-enable', true );
	WPSEO_Options::set( 'breadcrumbs-home', 'Home' );
	WPSEO_Options::set( 'breadcrumbs-sep', '»' );
	WPSEO_Options::set( 'separator', 'sc-dash' );

	WPSEO_Options::set( 'enable_xml_sitemap', true );
	WPSEO_Options::set( 'keyword_analysis_active', true );
	WPSEO_Options::set( 'content_analysis_active', true );
	WPSEO_Options::set( 'enable_admin_bar_menu', true );
	WPSEO_Options::set( 'tracking', false );
	WPSEO_Options::set( 'toggled_tracking', true );
	WPSEO_Options::set( 'environment_type', 'staging' );
	WPSEO_Options::set( 'site_type', 'smallBusiness' );
	WPSEO_Options::set( 'has_multiple_authors', false );
	WPSEO_Options::set( 'first_time_install', false );
	WPSEO_Options::set( 'show_onboarding_notice', false );
	WPSEO_Options::set( 'dismiss_configuration_workout_notice', true );
	WPSEO_Options::set( 'ignore_search_engines_discouraged_notice', true );
	WPSEO_Options::set(
		'configuration_finished_steps',
		array( 'siteRepresentation', 'socialProfiles', 'personalPreferences' )
	);
	WPSEO_Options::set(
		'workouts_data',
		array(
			'configuration' => array(
				'finishedSteps' => array( 'siteRepresentation', 'socialProfiles', 'personalPreferences' ),
			),
		)
	);

	$social = get_option( 'wpseo_social', array() );
	if ( ! is_array( $social ) ) {
		$social = array();
	}
	$hero960 = get_theme_file_uri( 'assets/images/home-hero/01-tad-fan-hero-960w.jpg' );
	$social['og_default_image']     = $hero960 ?: get_theme_file_uri( 'assets/images/home-hero/01-tad-fan-hero.jpg' );
	$social['og_default_image_id']  = 0;
	$social['opengraph']            = true;
	$social['twitter']              = true;
	$social['facebook_site']        = get_option( 'watphou_facebook', '' );
	$social['instagram_url']        = get_option( 'watphou_instagram', '' );
	update_option( 'wpseo_social', $social );

	$gsc = get_option( 'watphou_gsc_verification', '' );
	if ( $gsc ) {
		WPSEO_Options::set( 'googleverify', $gsc );
	}

	update_option( 'watphou_yoast_configured', 1 );
}

function watphou_core_tracking_head(): void {
	$gsc = trim( (string) get_option( 'watphou_gsc_verification', '' ) );
	if ( $gsc && ! class_exists( 'WPSEO_Options' ) ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $gsc ) . '">' . "\n";
	}
	// Live domain only: keep the Search Console property of watphou-travels.com verified after the
	// move from Wix (same token the Wix site publishes), unless another code was entered.
	$yoast_gsc = class_exists( 'WPSEO_Options' ) ? (string) WPSEO_Options::get( 'googleverify', '' ) : '';
	if ( '' === $gsc && '' === $yoast_gsc && function_exists( 'watphou_is_non_production' ) && ! watphou_is_non_production() ) {
		echo '<meta name="google-site-verification" content="QH3GAjV28KcT9PFKjQVheavnOXt5p6_bO2GVAwN84jY">' . "\n";
	}

	if ( function_exists( 'watphou_is_non_production' ) && watphou_is_non_production() ) {
		return;
	}
	$ga4 = strtoupper( trim( (string) get_option( 'watphou_ga4_id', '' ) ) );
	if ( ! preg_match( '/^G-[A-Z0-9]+$/', $ga4 ) ) {
		return;
	}
	echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $ga4 ) . '"></script>' . "\n";
	echo '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . esc_js( $ga4 ) . '");'
		// The quote form fires this event after a request is saved: counted as a GA4 lead (conversion).
		. 'document.addEventListener("watphou:booking-sent",function(e){var d=e.detail||{};gtag("event","generate_lead",{tour_name:String(d.tour||"").slice(0,100),group_size:String(d.group||"").slice(0,20)});});'
		. '</script>' . "\n";
}

function watphou_core_sync_yoast_for_post( int $post_id, WP_Post $post ): void {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( ! in_array( $post->post_type, array( 'tour', 'page' ), true ) ) {
		return;
	}
	if ( get_post_meta( $post_id, '_yoast_wpseo_title', true ) ) {
		return;
	}
	$title = wp_strip_all_tags( $post->post_title );
	if ( 'tour' === $post->post_type ) {
		update_post_meta( $post_id, '_yoast_wpseo_title', $title . ' | Private tour | Watphou Travels' );
		$excerpt = wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 28 );
		if ( '' === $excerpt ) {
			$excerpt = 'Private tour from Pakse with Watphou Travels. Local team, flexible dates.';
		}
		update_post_meta( $post_id, '_yoast_wpseo_metadesc', $excerpt );
		update_post_meta( $post_id, '_yoast_wpseo_focuskw', $title );
	} else {
		update_post_meta( $post_id, '_yoast_wpseo_title', $title . ' | Watphou Travels' );
		$excerpt = wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 28 );
		if ( $excerpt ) {
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', $excerpt );
		}
	}
}

function watphou_core_default_image_alt( array $attr, $attachment ): array {
	if ( ! empty( $attr['alt'] ) ) {
		return $attr;
	}
	if ( is_singular( 'tour' ) ) {
		$attr['alt'] = get_the_title();
	} elseif ( $attachment instanceof WP_Post && $attachment->post_title ) {
		$attr['alt'] = $attachment->post_title;
	}
	return $attr;
}

function watphou_core_breadcrumbs(): void {
	$home = function_exists( 'watphou_page_url' ) ? watphou_page_url( '/' ) : home_url( '/' );
	$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	$parts = array(
		'<a href="' . esc_url( $home ) . '">' . esc_html__( 'Home', 'watphou-travels' ) . '</a>',
	);
	if ( is_singular( 'tour' ) ) {
		$tours = (string) get_post_type_archive_link( 'tour' );
		if ( function_exists( 'watphou_core_force_lang_url' ) ) {
			$tours = watphou_core_force_lang_url( $tours, $lang );
		}
		$parts[] = '<a href="' . esc_url( $tours ) . '">' . esc_html__( 'Tours', 'watphou-travels' ) . '</a>';
		$parts[] = esc_html( get_the_title( get_queried_object_id() ) );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$name = $term->name;
			if ( function_exists( 'watphou_core_localize_destination_list' ) ) {
				$name = watphou_core_localize_destination_list( $name );
			}
			$parts[] = esc_html( $name );
		}
	} elseif ( is_singular() || is_page() ) {
		$parts[] = esc_html( get_the_title( get_queried_object_id() ) );
	} elseif ( is_post_type_archive() || is_archive() ) {
		$parts[] = esc_html( wp_strip_all_tags( get_the_archive_title() ) );
	} else {
		return;
	}
	echo '<nav class="wpt-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'watphou-travels' ) . '">' . implode( ' » ', $parts ) . '</nav>';
}

add_filter( 'wpseo_schema_graph', 'watphou_core_schema_graph_current_lang', 20 );

/**
 * @param array<int, array<string, mixed>> $graph Schema graph.
 * @return array<int, array<string, mixed>>
 */
function watphou_core_schema_graph_current_lang( $graph ) {
	if ( ! is_array( $graph ) || ! is_singular() ) {
		return $graph;
	}
	$title = get_the_title( get_queried_object_id() );
	if ( ! is_string( $title ) || '' === $title ) {
		return $graph;
	}
	foreach ( $graph as &$piece ) {
		if ( ! is_array( $piece ) || ( $piece['@type'] ?? '' ) !== 'BreadcrumbList' ) {
			continue;
		}
		$items = $piece['itemListElement'] ?? array();
		if ( ! is_array( $items ) || ! $items ) {
			continue;
		}
		$last = array_key_last( $items );
		$items[ $last ]['name'] = $title;
		$piece['itemListElement'] = $items;
	}
	unset( $piece );
	return $graph;
}

/**
 * Browser-tab titles for the quote page (English / French / Thai).
 *
 * @return array<string, string>
 */
function watphou_core_quote_seo_titles(): array {
	return array(
		'en' => 'Request a quote | Watphou Travels',
		'fr' => 'Demander un devis | Watphou Travels',
		'th' => 'ขอใบเสนอราคา | Watphou Travels',
	);
}

function watphou_core_is_quote_request(): bool {
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( preg_match( '#/(?:fr|th)/book-online(?:-\d+)?(/|$)#', $path ) || preg_match( '#^/book-online(?:-\d+)?(/|$)#', $path ) ) {
		return true;
	}
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$name = (string) get_post_field( 'post_name', get_queried_object_id() );
	$slug = function_exists( 'watphou_core_canonical_content_slug' )
		? watphou_core_canonical_content_slug( $name )
		: $name;
	return 'book-online' === $slug;
}

/**
 * @param string $title Current document title.
 */
function watphou_core_quote_document_title( $title ) {
	if ( is_admin() || ! watphou_core_is_quote_request() ) {
		return $title;
	}
	$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	$map  = watphou_core_quote_seo_titles();
	return $map[ $lang ] ?? ( is_string( $title ) ? $title : $map['en'] );
}

function watphou_core_tour_document_title( $title ) {
	if ( is_admin() || ! is_singular( 'tour' ) ) {
		return $title;
	}
	$id = (int) get_queried_object_id();
	if ( $id < 1 ) {
		return $title;
	}
	$name = get_the_title( $id );
	if ( '' === $name ) {
		return $title;
	}
	$lang    = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	$private = array(
		'en' => 'Private tour',
		'fr' => 'Circuit privé',
		'th' => 'ทัวร์ส่วนตัว',
	);
	$suffix = $private[ $lang ] ?? $private['en'];
	return $name . ' | ' . $suffix . ' | Watphou Travels';
}

function watphou_core_maybe_fix_quote_yoast_titles(): void {
	if ( '1.6.13' === (string) get_option( 'watphou_quote_yoast_titles' ) ) {
		return;
	}
	if ( wp_installing() ) {
		return;
	}
	$en_id = function_exists( 'watphou_core_find_page_id' ) ? watphou_core_find_page_id( 'book-online' ) : 0;
	if ( ! $en_id && function_exists( 'watphou_core_find_content_id' ) ) {
		$en_id = watphou_core_find_content_id( 'book-online', 'page' );
	}
	if ( ! $en_id ) {
		return;
	}
	$map = watphou_core_quote_seo_titles();
	update_post_meta( $en_id, '_yoast_wpseo_title', $map['en'] );
	update_post_meta( $en_id, '_yoast_wpseo_opengraph-title', $map['en'] );
	foreach ( array( 'fr', 'th' ) as $lang ) {
		$tid = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $en_id, $lang ) : 0;
		if ( $tid < 1 ) {
			continue;
		}
		update_post_meta( $tid, '_yoast_wpseo_title', $map[ $lang ] );
		update_post_meta( $tid, '_yoast_wpseo_opengraph-title', $map[ $lang ] );
	}
	update_option( 'watphou_quote_yoast_titles', '1.6.13', false );
}

function watphou_core_complete_seo_settings(): void {
	if ( '1.8.0' === (string) get_option( 'watphou_seo_complete' ) ) {
		return;
	}
	if ( ! class_exists( 'WPSEO_Options' ) ) {
		return;
	}
	WPSEO_Options::set( 'twitter_card_type', 'summary_large_image' );
	WPSEO_Options::set( 'company_or_person', 'company' );
	WPSEO_Options::set( 'company_name', 'Watphou Travels' );
	WPSEO_Options::set( 'website_name', 'Watphou Travels' );
	WPSEO_Options::set( 'org-description', 'Local tour agency in Pakse, Laos. Private tours of the Bolaven Plateau, Vat Phou, Champasak and the 4000 Islands.' );
	WPSEO_Options::set( 'org-email', get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ) );
	WPSEO_Options::set( 'org-phone', get_option( 'watphou_phone', '+85620 9949 5858' ) );
	WPSEO_Options::set( 'breadcrumbs-enable', true );
	WPSEO_Options::set( 'enable_xml_sitemap', true );
	WPSEO_Options::set( 'disable-author', true );
	WPSEO_Options::set( 'disable-date', true );
	WPSEO_Options::set( 'keyword_analysis_active', true );
	WPSEO_Options::set( 'content_analysis_active', true );
	if ( function_exists( 'get_theme_file_uri' ) ) {
		$logo = get_theme_file_uri( 'assets/images/logo-wpt.jpg' );
		$hero = get_theme_file_uri( 'assets/images/home-hero/01-tad-fan-hero-960w.jpg' );
		$social = get_option( 'wpseo_social', array() );
		if ( ! is_array( $social ) ) {
			$social = array();
		}
		$social['opengraph'] = true;
		$social['twitter']   = true;
		if ( empty( $social['og_default_image'] ) && $hero ) {
			$social['og_default_image'] = $hero;
		}
		update_option( 'wpseo_social', $social );
		if ( $logo && ! WPSEO_Options::get( 'company_logo' ) ) {
			WPSEO_Options::set( 'company_logo', $logo );
		}
	}
	update_option( 'watphou_seo_complete', '1.8.0', false );
}

function watphou_core_default_og_image( $image ) {
	if ( is_string( $image ) && '' !== $image ) {
		return $image;
	}
	if ( is_singular( 'tour' ) && function_exists( 'watphou_core_tour_featured_url' ) ) {
		$tour_img = watphou_core_tour_featured_url( get_queried_object_id(), 'large' );
		if ( $tour_img ) {
			return $tour_img;
		}
	}
	return get_theme_file_uri( 'assets/images/home-hero/01-tad-fan-hero-960w.jpg' );
}

function watphou_core_fallback_metadesc( $desc ) {
	if ( is_string( $desc ) && '' !== trim( wp_strip_all_tags( $desc ) ) ) {
		return $desc;
	}
	if ( is_singular( 'tour' ) ) {
		$id      = get_queried_object_id();
		$excerpt = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $id ) ?: get_post_field( 'post_content', $id ) ), 28 );
		if ( $excerpt ) {
			return $excerpt;
		}
		return 'Private tour from Pakse with Watphou Travels. Local team, flexible dates. WhatsApp +85620 9949 5858.';
	}
	if ( is_front_page() ) {
		return 'Private tours from Pakse: Bolaven Plateau waterfalls, UNESCO Vat Phou, Champasak and the 4000 Islands. Local team, European standards.';
	}
	if ( is_post_type_archive( 'tour' ) ) {
		return 'Browse private day tours and multi-day journeys in Southern Laos: Bolaven Plateau, Vat Phou, Champasak and the 4000 Islands.';
	}
	$title = wp_strip_all_tags( wp_get_document_title() );
	if ( $title ) {
		return wp_trim_words( $title . '. Watphou Travels — private tours in Southern Laos from Pakse.', 28 );
	}
	return $desc;
}

function watphou_core_https_canonical( $url ) {
	$ours = watphou_core_current_canonical_url();
	if ( '' !== $ours ) {
		return $ours;
	}
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}
	return set_url_scheme( $url, 'https' );
}

/**
 * Absolute HTTPS canonical for the current public page (language-aware).
 */
function watphou_core_current_canonical_url(): string {
	$url = '';
	if ( is_singular() ) {
		$url = (string) get_permalink( get_queried_object_id() );
	} elseif ( is_post_type_archive() ) {
		$pt = get_query_var( 'post_type' );
		if ( is_array( $pt ) ) {
			$pt = reset( $pt );
		}
		$archive = get_post_type_archive_link( is_string( $pt ) ? $pt : 'tour' );
		$url     = is_string( $archive ) ? $archive : '';
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$maybe = get_term_link( $term );
			$url   = is_wp_error( $maybe ) ? '' : (string) $maybe;
		}
	} elseif ( is_front_page() || is_home() ) {
		$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
		$url  = function_exists( 'pll_home_url' ) ? (string) pll_home_url( $lang ) : home_url( '/' );
	}
	if ( '' === $url ) {
		$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
		$url  = home_url( $path ?: '/' );
	}
	$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	if ( function_exists( 'watphou_core_force_lang_url' ) ) {
		$url = watphou_core_force_lang_url( $url, $lang );
	}
	return set_url_scheme( $url, 'https' );
}

/**
 * English Uniform Resource Locator (URL) for hreflang x-default.
 */
function watphou_core_english_alternate_url(): string {
	if ( is_singular() && function_exists( 'pll_get_post' ) ) {
		$en_id = (int) pll_get_post( get_queried_object_id(), 'en' );
		if ( $en_id > 0 ) {
			return set_url_scheme( (string) get_permalink( $en_id ), 'https' );
		}
	}
	if ( is_tax() && function_exists( 'pll_get_term' ) ) {
		$en_term = (int) pll_get_term( get_queried_object_id(), 'en' );
		if ( $en_term > 0 ) {
			$en_link = get_term_link( $en_term );
			if ( ! is_wp_error( $en_link ) ) {
				return set_url_scheme( (string) $en_link, 'https' );
			}
		}
	}
	if ( function_exists( 'watphou_core_force_lang_url' ) ) {
		return watphou_core_force_lang_url( watphou_core_current_canonical_url(), 'en' );
	}
	if ( function_exists( 'pll_home_url' ) ) {
		return set_url_scheme( (string) pll_home_url( 'en' ), 'https' );
	}
	return set_url_scheme( home_url( '/' ), 'https' );
}

function watphou_core_extra_seo_meta(): void {
	$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	$og   = array(
		'en' => 'en_US',
		'fr' => 'fr_FR',
		'th' => 'th_TH',
	);
	$canon = watphou_core_current_canonical_url();
	if ( '' !== $canon ) {
		echo '<link rel="canonical" href="' . esc_url( $canon ) . '">' . "\n";
	}
	$en = watphou_core_english_alternate_url();
	if ( '' !== $en ) {
		echo '<link rel="alternate" href="' . esc_url( $en ) . '" hreflang="x-default">' . "\n";
	}
	echo '<meta property="og:locale" content="' . esc_attr( $og[ $lang ] ?? 'en_US' ) . '">' . "\n";
	echo '<meta name="geo.region" content="LA-CH">' . "\n";
	echo '<meta name="geo.placename" content="Pakse">' . "\n";
	echo '<meta name="geo.position" content="15.1200197;105.7988082">' . "\n";
	echo '<meta name="ICBM" content="15.1200197, 105.7988082">' . "\n";
	if ( is_singular( 'tour' ) && function_exists( 'watphou_core_tour_package_code' ) ) {
		$code = watphou_core_tour_package_code( get_queried_object_id() );
		if ( $code ) {
			echo '<meta name="sku" content="' . esc_attr( $code ) . '">' . "\n";
		}
	}
}

/*
 * Keyword pass (October 2026 audit), English pages only.
 * French and Thai keep their reviewed wording until the translations of these
 * lines are approved (drafts: docs/translations/seo_keywords_fr_th_drafts.csv).
 * Filters only: nothing is written to the database, so removing this block undoes it.
 */
add_filter( 'gettext', 'watphou_core_keyword_headings', 30, 3 );
add_filter( 'pre_get_document_title', 'watphou_core_keyword_document_title', 101 );
add_filter( 'wpseo_title', 'watphou_core_keyword_document_title', 101 );
add_filter( 'wpseo_opengraph_title', 'watphou_core_keyword_document_title', 101 );
add_filter( 'wpseo_twitter_title', 'watphou_core_keyword_document_title', 101 );
add_filter( 'wpseo_metadesc', 'watphou_core_keyword_metadesc', 30 );
add_filter( 'wpseo_opengraph_desc', 'watphou_core_keyword_metadesc', 30 );
add_filter( 'get_the_archive_description', 'watphou_core_keyword_destination_intro', 20 );

function watphou_core_keyword_is_english(): bool {
	if ( is_admin() ) {
		return false;
	}
	$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	return 'en' === $lang;
}

/**
 * Homepage headings with the search words people type.
 */
function watphou_core_keyword_headings( $translated, $text, $domain ) {
	if ( 'watphou-travels' !== $domain || ! watphou_core_keyword_is_english() ) {
		return $translated;
	}
	static $map = array(
		'Discover Southern Laos Your Way'        => 'Private Tours in Southern Laos from Pakse',
		'Popular Private Tours'                  => 'Popular Private Tours in Southern Laos',
		'Why Us!'                                => 'Why Travel with a Local Pakse Tour Agency',
		'Ready to plan your Southern Laos journey?' => 'Ready to plan your private tour in Laos?',
	);
	return $map[ $text ] ?? $translated;
}

/**
 * Current English page key: front, listing page slug, or destination term slug.
 */
function watphou_core_keyword_page_key(): string {
	if ( is_front_page() ) {
		return 'front';
	}
	if ( is_tax( 'destination' ) ) {
		$term = get_queried_object();
		return ( $term instanceof WP_Term ) ? 'dest:' . $term->slug : '';
	}
	if ( is_post_type_archive( 'tour' ) ) {
		return 'tours';
	}
	if ( is_page() ) {
		$name = (string) get_post_field( 'post_name', get_queried_object_id() );
		return function_exists( 'watphou_core_canonical_content_slug' ) ? watphou_core_canonical_content_slug( $name ) : $name;
	}
	return '';
}

/**
 * @return array<string, array{0:string,1:string}> key => [title, meta description]
 */
function watphou_core_keyword_seo_map(): array {
	return array(
		'front'                => array( 'Private Tours in Southern Laos from Pakse | Watphou Travels', 'Local Laos tour agency in Pakse since 2008. Private tours to the Bolaven Plateau waterfalls, UNESCO Vat Phou, Champasak and the 4000 Islands. Ask for a free quote.' ),
		'tours'                => array( 'All Private Tours in Southern Laos | Watphou Travels', 'Private day tours and 2- to 6-day trips from Pakse: Bolaven Plateau, Vat Phou, Champasak and the 4000 Islands. 100% private with a local Laos tour agency.' ),
		'day-tours'            => array( 'Day Tours from Pakse, Laos | Bolaven Plateau, Vat Phou | Watphou Travels', 'Private day tours from Pakse: Bolaven Plateau waterfalls and coffee, Vat Phou temple, 4000 Islands, trekking and cycling. Hotel pickup in Pakse.' ),
		'2-day-tours'          => array( '2-Day Tours from Pakse | Bolaven Plateau & 4000 Islands | Watphou Travels', 'Two-day private tours from Pakse with one night: Bolaven Plateau, Vat Phou and Champasak, or the 4000 Islands. Local Pakse team.' ),
		'3-day-tours'          => array( '3-Day Southern Laos Tours from Pakse | Watphou Travels', 'Three-day private tours of Southern Laos from Pakse: Bolaven Plateau waterfalls, Vat Phou, Champasak and the 4000 Islands.' ),
		'4-6-day-tours'        => array( '4 to 6-Day Southern Laos Tour Packages | Watphou Travels', 'Private 4, 5 and 6-day Southern Laos tours from Pakse: Bolaven Plateau, Vat Phou, Champasak, the 4000 Islands and Khone Phapheng Falls.' ),
		'destinations'         => array( 'Southern Laos Destinations: Bolaven, Vat Phou, 4000 Islands | Watphou Travels', 'Where to go in Southern Laos: Pakse, the Bolaven Plateau, UNESCO Vat Phou and Champasak, and the 4000 Islands. Private tours from Pakse.' ),
		'tailor-made-tours'    => array( 'Tailor-Made Laos Tours from Pakse | Watphou Travels', 'Custom private tours in Southern Laos built around your dates, pace and interests by a local tour agency in Pakse.' ),
		'about-us'             => array( 'About Watphou Travels | Local Tour Agency in Pakse, Laos', 'Watphou Travels is a local tour agency in Pakse, Laos, since 2008. Private tours with a driver and guide across Southern Laos.' ),
		'contact-us'           => array( 'Contact Watphou Travels | Tour Agency in Pakse, Laos', 'WhatsApp +85620 9949 5858 or email sales.watphoutravel@gmail.com. Our office is on Street N°5, Ban Vat Luang, Pakse. Private tours in Southern Laos.' ),
		'travel-guide'         => array( 'Southern Laos Travel Guide: Pakse, Bolaven, 4000 Islands | Watphou Travels', 'Practical tips for visiting Southern Laos: Pakse, the Bolaven Plateau, Vat Phou and the 4000 Islands, from a local Pakse tour agency.' ),
		'dest:bolaven-plateau' => array( 'Bolaven Plateau Tours from Pakse | Waterfalls & Coffee | Watphou Travels', 'Private Bolaven Plateau tours from Pakse: Tad Fane, Tad Yuang and Tad Champee waterfalls, coffee farms and highland villages. Day trips and overnight tours.' ),
		'dest:4000-islands'    => array( '4000 Islands Tours (Si Phan Don) from Pakse | Watphou Travels', 'Private 4000 Islands tours from Pakse: Don Khone, Don Det, Khone Phapheng and Li Phi waterfalls on the Mekong. Day trips and overnight tours.' ),
		'dest:champasak'       => array( 'Vat Phou & Champasak Tours from Pakse | UNESCO Temple | Watphou Travels', 'Private tours to Vat Phou, the UNESCO World Heritage Khmer temple, and Champasak town, about an hour south of Pakse. Day trips, cycling and overnight tours.' ),
		'dest:pakse'           => array( 'Pakse Tours & Things to Do in Pakse | Watphou Travels', 'Private Pakse city tours and day trips: temples, the Mekong riverside, Phou Salao Golden Buddha and nearby weaving villages. Local Pakse agency.' ),
	);
}

function watphou_core_keyword_document_title( $title ) {
	if ( ! watphou_core_keyword_is_english() ) {
		return $title;
	}
	$map = watphou_core_keyword_seo_map();
	$key = watphou_core_keyword_page_key();
	return isset( $map[ $key ] ) ? $map[ $key ][0] : $title;
}

function watphou_core_keyword_metadesc( $desc ) {
	if ( ! watphou_core_keyword_is_english() ) {
		return $desc;
	}
	$map = watphou_core_keyword_seo_map();
	$key = watphou_core_keyword_page_key();
	return isset( $map[ $key ] ) ? $map[ $key ][1] : $desc;
}

/**
 * Longer English intro on destination pages (they were one sentence long).
 * Escaped paragraphs joined with </p><p>; the template wraps the result in <p>…</p>.
 */
function watphou_core_keyword_destination_intro( $description ) {
	if ( ! is_tax( 'destination' ) || ! watphou_core_keyword_is_english() ) {
		return $description;
	}
	$term = get_queried_object();
	if ( ! $term instanceof WP_Term ) {
		return $description;
	}
	$copy = array(
		'bolaven-plateau' => array(
			'The Bolaven Plateau rises about 1,000 to 1,300 metres above the Mekong, roughly an hour and a half east of Pakse. Its cool air, red volcanic soil and dozens of waterfalls make it the most popular day trip in Southern Laos.',
			'On a private Bolaven Plateau tour from Pakse you can see Tad Fane, the twin waterfall dropping into a deep jungle gorge, swim near Tad Yuang and Tad Champee, walk through a highland market and taste Lao coffee with the families who grow it around Paksong.',
			'Best time to visit: November to February is cool and dry; June to October brings the fullest waterfalls and the greenest scenery. Choose a full-day tour, or stay overnight to add villages, Tad Lo and quieter falls.',
		),
		'4000-islands'    => array(
			'Si Phan Don, the 4000 Islands, is where the Mekong spreads up to 14 kilometres wide just before the Cambodian border, about two and a half to three hours south of Pakse.',
			'A private 4000 Islands tour from Pakse visits Khone Phapheng, the largest waterfall by volume in Southeast Asia, the Li Phi (Somphamit) falls on Don Khone, the old French railway bridge between Don Khone and Don Det, and quiet island villages by boat or bicycle.',
			'It works as a long day trip, but most travellers prefer two days with one night on the islands, often combined with Vat Phou and Champasak on the way.',
		),
		'champasak'       => array(
			'Vat Phou (Wat Phu) is a Khmer temple complex at the foot of Phou Kao mountain, with origins older than Angkor Wat. It is a UNESCO World Heritage Site and lies near the small riverside town of Champasak, about an hour south of Pakse.',
			'A private Vat Phou and Champasak tour from Pakse climbs the ancient stairway to the hillside sanctuary for views over the Mekong plain, then continues to Champasak town, rice fields and riverside villages. You can add a cycling route or a ferry to Don Daeng island.',
			'Go early in the morning or late in the afternoon for softer light and cooler temperatures. Vat Phou combines easily with the 4000 Islands in a two-day tour.',
		),
		'pakse'           => array(
			'Pakse, the capital of Champasak Province, sits where the Xe Don river meets the Mekong. It is the main gateway to Southern Laos and the starting point of every Watphou Travels tour.',
			'Things to do in Pakse include the hilltop Golden Buddha at Wat Phou Salao, Wat Luang and other temples, the morning market, the Mekong riverside at sunset and nearby villages known for silk and cotton weaving.',
			'Our office is in Pakse, so we pick you up at your hotel. From here the Bolaven Plateau, Vat Phou and the 4000 Islands are all within easy reach on a private day tour or a longer trip.',
		),
	);
	$slug = $term->slug;
	if ( ! isset( $copy[ $slug ] ) ) {
		return $description;
	}
	return implode( '</p><p>', array_map( 'esc_html', $copy[ $slug ] ) );
}
