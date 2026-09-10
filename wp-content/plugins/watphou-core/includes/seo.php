<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'watphou_core_configure_yoast_defaults', 20 );
add_action( 'init', 'watphou_core_configure_yoast_defaults', 25 );
add_action( 'save_post', 'watphou_core_sync_yoast_for_post', 30, 2 );
add_filter( 'wp_get_attachment_image_attributes', 'watphou_core_default_image_alt', 10, 2 );
add_action( 'wp_head', 'watphou_core_tracking_head', 2 );

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
	$social['og_default_image']     = get_theme_file_uri( 'assets/images/tad-fane.jpg' );
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

	if ( function_exists( 'watphou_is_non_production' ) && watphou_is_non_production() ) {
		return;
	}
	$ga4 = strtoupper( trim( (string) get_option( 'watphou_ga4_id', '' ) ) );
	if ( ! preg_match( '/^G-[A-Z0-9]+$/', $ga4 ) ) {
		return;
	}
	echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $ga4 ) . '"></script>' . "\n";
	echo '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . esc_js( $ga4 ) . '");</script>' . "\n";
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
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="wpt-breadcrumbs" aria-label="Breadcrumb">', '</nav>' );
	}
}
