<?php
/**
 * One-time: publish English placeholders for French/Thai, legal pages, Yoast meta.
 * Polylang Free does not auto-translate. Thai is never machine-translated.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'watphou_core_run_i18n_seo_setup', 40 );
add_action( 'admin_init', 'watphou_core_dismiss_yoast_notices', 30 );

function watphou_core_run_i18n_seo_setup(): void {
	if ( get_option( 'watphou_i18n_seo_setup' ) === '1.0' ) {
		return;
	}
	if ( wp_installing() ) {
		return;
	}
	if ( ! function_exists( 'PLL' ) || ! isset( $GLOBALS['polylang'] ) ) {
		return;
	}

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 );
	}

	delete_option( 'watphou_yoast_configured' );
	watphou_core_configure_yoast_defaults();
	watphou_core_sideload_yoast_logo();
	watphou_core_seed_destination_descriptions();
	watphou_core_ensure_term_translations();
	watphou_core_seed_legal_and_guide_pages();
	watphou_core_publish_language_placeholders();
	watphou_core_backfill_yoast_meta();
	flush_rewrite_rules( false );
	update_option( 'watphou_i18n_seo_setup', '1.0' );
}

function watphou_core_pll_model() {
	if ( ! function_exists( 'PLL' ) ) {
		return null;
	}
	$pll = PLL();
	return isset( $pll->model ) ? $pll->model : null;
}

function watphou_core_seed_destination_descriptions(): void {
	$copy = array(
		'bolaven-plateau' => 'Private tours on the Bolaven Plateau from Pakse: Tad Fane and Tad Yuang waterfalls, highland coffee farms, and villages. 100% private departures with Watphou Travels.',
		'4000-islands'    => 'Private tours to Si Phan Don (the 4000 Islands) on the Mekong: Don Khone, Don Det, and the Liphi waterfalls, organised from Pakse.',
		'champasak'       => 'Private tours to UNESCO-listed Vat Phou and Champasak town, south of Pakse, with a local driver-guide.',
		'pakse'           => 'Pakse is our home and the starting point for private journeys in Southern Laos: riverside walks, nearby villages, and easy links to the Bolaven Plateau and Vat Phou.',
	);
	foreach ( $copy as $slug => $desc ) {
		$term = get_term_by( 'slug', $slug, 'destination' );
		if ( $term && ! is_wp_error( $term ) && '' === trim( (string) $term->description ) ) {
			wp_update_term( (int) $term->term_id, 'destination', array( 'description' => $desc ) );
		}
	}
}

function watphou_core_ensure_term_translations(): void {
	$model = watphou_core_pll_model();
	if ( ! $model ) {
		return;
	}
	foreach ( array( 'destination', 'duration' ) as $tax ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
				'lang'       => 'en',
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
		}
		if ( is_wp_error( $terms ) || ! $terms ) {
			continue;
		}
		foreach ( $terms as $term ) {
			$lang = $model->term->get_language( $term->term_id );
			if ( $lang && 'en' !== $lang->slug ) {
				continue;
			}
			if ( ! $lang ) {
				$model->term->set_language( $term->term_id, 'en' );
			}
			$translations = array( 'en' => (int) $term->term_id );
			foreach ( array( 'fr', 'th' ) as $code ) {
				$existing = (int) $model->term->get_translation( $term->term_id, $code );
				if ( $existing ) {
					wp_update_term(
						$existing,
						$tax,
						array(
							'name'        => $term->name,
							'description' => $term->description,
						)
					);
					$translations[ $code ] = $existing;
					continue;
				}
				$inserted = wp_insert_term(
					$term->name,
					$tax,
					array(
						'slug'        => $term->slug . '-' . $code,
						'description' => $term->description,
					)
				);
				if ( is_wp_error( $inserted ) ) {
					continue;
				}
				$new_id = (int) $inserted['term_id'];
				$model->term->set_language( $new_id, $code );
				$translations[ $code ] = $new_id;
			}
			if ( function_exists( 'pll_save_term_translations' ) ) {
				pll_save_term_translations( $translations );
			} elseif ( method_exists( $model->term, 'save_translations' ) ) {
				$model->term->save_translations( $term->term_id, $translations );
			}
		}
	}
}

function watphou_core_seed_legal_and_guide_pages(): void {
	$phone = get_option( 'watphou_phone', '+85620 9949 5858' );
	$email = get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' );
	$addr  = get_option( 'watphou_address', 'Street N°5, Ban Vat Luang, Pakse, Laos' );

	$pages = array(
		'privacy-policy' => array(
			'title'   => 'Privacy Policy',
			'content' => '<p>Watphou Travels (Pakse, Laos) collects only the information you send through our enquiry form, email, or WhatsApp so we can answer your tour request.</p>'
				. '<p>Typical fields: name, email, phone, travel dates, group size, and your message. We do not sell this information. We do not invent or publish traveller reviews.</p>'
				. '<p>Contact: <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a> · ' . esc_html( $phone ) . '<br>' . esc_html( $addr ) . '</p>'
				. '<p>This page will be reviewed with a lawyer before the public domain launch if you need extra clauses (cookies, analytics identifiers).</p>',
		),
		'terms'          => array(
			'title'   => 'Terms of Use',
			'content' => '<p>Watphou Travels offers 100% private tours in Southern Laos, departing from Pakse. Information on this website describes itineraries; your confirmed quote is the booking contract.</p>'
				. '<p>Public prices currently show as <strong>From $XX</strong> until the company confirms real rates. Do not treat placeholder prices as a payable amount.</p>'
				. '<p>Bookings are requested by form or WhatsApp. Banque Pour Le Commerce Exterieur Lao (BCEL) online payment is not live yet.</p>'
				. '<p>Contact: <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a> · ' . esc_html( $phone ) . '<br>' . esc_html( $addr ) . '</p>',
		),
		'cancellation'   => array(
			'title'   => 'Cancellation',
			'content' => '<p>Cancellation and payment terms are written on your personal quote. We do not publish a generic percentage here until the company confirms the official policy.</p>'
				. '<p>To change or cancel a request, write to <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a> or WhatsApp ' . esc_html( $phone ) . '.</p>'
				. '<p>' . esc_html( $addr ) . '</p>',
		),
		'travel-guide'   => array(
			'title'   => 'Southern Laos Travel Guide',
			'content' => '<p>A short, factual guide to the places we operate from Pakse. Use it to choose a private day tour or a multi-day journey. We do not pad this page with invented history or prices.</p>'
				. '<h2>Pakse</h2><p>Pakse is the gateway to Southern Laos and the office of Watphou Travels (Street N°5, Ban Vat Luang). Most private tours start here.</p>'
				. '<p><a href="' . esc_url( home_url( '/destinations/pakse/' ) ) . '">Pakse tours</a></p>'
				. '<h2>Bolaven Plateau</h2><p>Highlands east of Pakse known for Tad Fane and Tad Yuang waterfalls and coffee farms. A classic full-day private tour from Pakse.</p>'
				. '<p><a href="' . esc_url( home_url( '/destinations/bolaven-plateau/' ) ) . '">Bolaven Plateau tours</a></p>'
				. '<h2>Vat Phou and Champasak</h2><p>UNESCO-listed Vat Phou sits near Champasak town, south of Pakse, on the Mekong. Visit as a day trip or combined with the 4000 Islands.</p>'
				. '<p><a href="' . esc_url( home_url( '/destinations/champasak/' ) ) . '">Vat Phou and Champasak tours</a></p>'
				. '<h2>4000 Islands (Si Phan Don)</h2><p>A Mekong archipelago near the Cambodian border: Don Khone, Don Det, and the Liphi waterfalls. Often a two-day private journey from Pakse.</p>'
				. '<p><a href="' . esc_url( home_url( '/destinations/4000-islands/' ) ) . '">4000 Islands tours</a></p>',
		),
	);

	foreach ( $pages as $slug => $data ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			if ( '' === trim( wp_strip_all_tags( $existing->post_content ) ) ) {
				wp_update_post(
					array(
						'ID'           => $existing->ID,
						'post_content' => $data['content'],
						'post_status'  => 'publish',
					)
				);
			}
			continue;
		}
		$new_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $data['title'],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_content' => $data['content'],
			)
		);
		$model = watphou_core_pll_model();
		if ( $new_id && ! is_wp_error( $new_id ) && $model ) {
			$model->post->set_language( (int) $new_id, 'en' );
		}
	}
}

function watphou_core_publish_language_placeholders(): void {
	$model = watphou_core_pll_model();
	if ( ! $model ) {
		return;
	}

	$sources = get_posts(
		array(
			'post_type'      => array( 'tour', 'page' ),
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'lang'           => '',
		)
	);

	foreach ( $sources as $source ) {
		$slang = $model->post->get_language( $source->ID );
		if ( $slang && 'en' !== $slang->slug ) {
			continue;
		}
		if ( 0 === strpos( $source->post_title, '[DRAFT ' ) ) {
			continue;
		}
		if ( 'publish' !== $source->post_status ) {
			continue;
		}
		if ( ! $slang ) {
			$model->post->set_language( $source->ID, 'en' );
		}

		$translations = array( 'en' => (int) $source->ID );
		foreach ( array( 'fr', 'th' ) as $code ) {
			$translations[ $code ] = watphou_core_copy_post_to_language( $source, $code, $model );
		}
		$translations = array_filter( $translations );
		if ( count( $translations ) > 1 && function_exists( 'pll_save_post_translations' ) ) {
			pll_save_post_translations( $translations );
		}
	}
}

function watphou_core_copy_post_to_language( WP_Post $source, string $code, $model ): int {
	$existing = (int) $model->post->get_translation( $source->ID, $code );
	$title    = preg_replace( '/^\[DRAFT (FR|TH)\]\s*/i', '', $source->post_title );
	$payload  = array(
		'post_type'    => $source->post_type,
		'post_title'   => $title,
		'post_name'    => $source->post_name,
		'post_status'  => 'publish',
		'post_content' => $source->post_content,
		'post_excerpt' => $source->post_excerpt,
		'menu_order'   => $source->menu_order,
		'post_parent'  => 0,
	);

	if ( $existing ) {
		$payload['ID'] = $existing;
		wp_update_post( $payload );
		$id = $existing;
	} else {
		$inserted = wp_insert_post( $payload, true );
		if ( is_wp_error( $inserted ) || ! $inserted ) {
			return 0;
		}
		$id = (int) $inserted;
		$model->post->set_language( $id, $code );
	}

	$thumb = get_post_thumbnail_id( $source->ID );
	if ( $thumb ) {
		set_post_thumbnail( $id, $thumb );
	}

	$skip = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_trash_meta_status', '_wp_trash_meta_time' );
	foreach ( get_post_meta( $source->ID ) as $key => $values ) {
		if ( 0 === strpos( $key, '_pll' ) || in_array( $key, $skip, true ) ) {
			continue;
		}
		delete_post_meta( $id, $key );
		foreach ( $values as $value ) {
			add_post_meta( $id, $key, maybe_unserialize( $value ) );
		}
	}
	update_post_meta( $id, '_watphou_translation_status', 'english-placeholder' );

	foreach ( array( 'destination', 'duration', 'tour_type' ) as $tax ) {
		if ( ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$terms = wp_get_object_terms( $source->ID, $tax, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $terms ) || ! $terms ) {
			continue;
		}
		$mapped = array();
		foreach ( $terms as $tid ) {
			$tr = (int) $model->term->get_translation( (int) $tid, $code );
			$mapped[] = $tr ? $tr : (int) $tid;
		}
		wp_set_object_terms( $id, $mapped, $tax, false );
	}

	return (int) $id;
}

function watphou_core_backfill_yoast_meta(): void {
	$page_seo = array(
		'about-us'         => array( 'About Watphou Travels | Local team in Pakse', 'Local tour agency in Pakse, Laos. Private tours of the Bolaven Plateau, Vat Phou, Champasak and the 4000 Islands.' ),
		'contact-us'       => array( 'Contact Watphou Travels | Pakse, Laos', 'WhatsApp +85620 9949 5858 · sales.watphoutravel@gmail.com · Street N°5, Ban Vat Luang, Pakse. Ask for a private tour in Southern Laos.' ),
		'book-online'      => array( 'Request a private tour | Watphou Travels', 'Send dates and group size for a private tour from Pakse. We reply by email or WhatsApp. Prices show as From $XX until confirmed.' ),
		'day-tours'        => array( 'Day tours from Pakse | Watphou Travels', 'Private one-day tours from Pakse: Bolaven Plateau waterfalls, Vat Phou, Champasak and Pakse riverside.' ),
		'2-day-tours'      => array( '2-day private tours | Watphou Travels', 'Two-day private journeys from Pakse: Bolaven Plateau or 4000 Islands and Vat Phou.' ),
		'3-day-tours'      => array( '3-day Southern Laos tours | Watphou Travels', 'Three-day private itineraries in Southern Laos, departing from Pakse with a local team.' ),
		'4-6-day-tours'    => array( '4–6 day Southern Laos journeys | Watphou Travels', 'Longer private journeys from Pakse across the Bolaven Plateau, Vat Phou, Champasak and the 4000 Islands.' ),
		'destinations'     => array( 'Destinations in Southern Laos | Watphou Travels', 'Pakse, Bolaven Plateau, Vat Phou and Champasak, and the 4000 Islands — private tours with a local Pakse team.' ),
		'tailor-made-tours'=> array( 'Tailor-made tours in Southern Laos | Watphou Travels', 'Build a private itinerary around your dates and pace. Watphou Travels is based in Pakse.' ),
		'travel-guide'     => array( 'Southern Laos travel guide | Watphou Travels', 'Short guide to Pakse, the Bolaven Plateau, Vat Phou and the 4000 Islands, written from our local tours.' ),
		'privacy-policy'   => array( 'Privacy Policy | Watphou Travels', 'How Watphou Travels in Pakse uses the contact details you send by form, email, or WhatsApp.' ),
		'terms'            => array( 'Terms of Use | Watphou Travels', 'How private tours, placeholder prices, and booking requests work at Watphou Travels in Pakse.' ),
		'cancellation'     => array( 'Cancellation | Watphou Travels', 'How to change or cancel a Watphou Travels request. Final terms are on your personal quote.' ),
	);

	$ids = get_posts(
		array(
			'post_type'      => array( 'tour', 'page' ),
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'lang'           => '',
		)
	);

	foreach ( $ids as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			continue;
		}
		$slug  = $post->post_name;
		$title = wp_strip_all_tags( preg_replace( '/^\[DRAFT (FR|TH)\]\s*/i', '', $post->post_title ) );
		if ( 'page' === $post->post_type && isset( $page_seo[ $slug ] ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_title', $page_seo[ $slug ][0] );
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', $page_seo[ $slug ][1] );
			update_post_meta( $post_id, '_yoast_wpseo_focuskw', $title );
			continue;
		}
		if ( 'tour' === $post->post_type ) {
			$excerpt = wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 28 );
			if ( '' === $excerpt ) {
				$excerpt = 'Private tour from Pakse with Watphou Travels. Local team, flexible dates. WhatsApp +85620 9949 5858.';
			}
			update_post_meta( $post_id, '_yoast_wpseo_title', $title . ' | Private tour | Watphou Travels' );
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', $excerpt );
			update_post_meta( $post_id, '_yoast_wpseo_focuskw', $title );
			update_post_meta( $post_id, '_yoast_wpseo_opengraph-title', $title . ' | Watphou Travels' );
		} elseif ( ! get_post_meta( $post_id, '_yoast_wpseo_title', true ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_title', $title . ' | Watphou Travels' );
		}
	}

	$term_kw = array(
		'bolaven-plateau' => array( 'Bolaven Plateau tours from Pakse | Watphou Travels', 'Private Bolaven Plateau tours from Pakse: waterfalls, coffee farms, and highland villages. 100% private.' ),
		'4000-islands'    => array( '4000 Islands tours | Watphou Travels', 'Private Si Phan Don (4000 Islands) tours from Pakse: Don Khone, Don Det, and Liphi waterfalls.' ),
		'champasak'       => array( 'Vat Phou and Champasak tours | Watphou Travels', 'Private tours to UNESCO Vat Phou and Champasak town, organised from Pakse.' ),
		'pakse'           => array( 'Pakse tours | Watphou Travels', 'Private tours around Pakse and the Mekong, the starting point for Southern Laos journeys.' ),
	);
	foreach ( $term_kw as $slug => $pair ) {
		$term = get_term_by( 'slug', $slug, 'destination' );
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}
		update_term_meta( $term->term_id, '_yoast_wpseo_title', $pair[0] );
		update_term_meta( $term->term_id, '_yoast_wpseo_metadesc', $pair[1] );
		$model = watphou_core_pll_model();
		if ( ! $model ) {
			continue;
		}
		foreach ( array( 'fr', 'th' ) as $code ) {
			$tr = (int) $model->term->get_translation( $term->term_id, $code );
			if ( $tr ) {
				update_term_meta( $tr, '_yoast_wpseo_title', $pair[0] );
				update_term_meta( $tr, '_yoast_wpseo_metadesc', $pair[1] );
			}
		}
	}
}

function watphou_core_sideload_yoast_logo(): void {
	if ( ! class_exists( 'WPSEO_Options' ) ) {
		return;
	}
	$existing = (int) WPSEO_Options::get( 'company_logo_id', 0 );
	if ( $existing && wp_attachment_is_image( $existing ) ) {
		return;
	}
	$path = get_theme_file_path( 'assets/images/logo-wpt.jpg' );
	if ( ! is_readable( $path ) ) {
		$path = get_theme_file_path( 'assets/images/favicon-source.jpg' );
	}
	if ( ! is_readable( $path ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( 'watphou-logo.jpg' );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		return;
	}
	$file_array = array(
		'name'     => 'watphou-travels-logo.jpg',
		'tmp_name' => $tmp,
	);
	$attach_id = media_handle_sideload( $file_array, 0, 'Watphou Travels logo' );
	if ( is_wp_error( $attach_id ) ) {
		@unlink( $tmp );
		return;
	}
	$url = wp_get_attachment_url( $attach_id );
	WPSEO_Options::set( 'company_logo_id', (int) $attach_id );
	WPSEO_Options::set( 'company_logo', $url );
	$social = get_option( 'wpseo_social', array() );
	if ( ! is_array( $social ) ) {
		$social = array();
	}
	$social['og_default_image']    = $url;
	$social['og_default_image_id'] = (int) $attach_id;
	update_option( 'wpseo_social', $social );
}

function watphou_core_dismiss_yoast_notices(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$user_id = get_current_user_id();
	update_user_meta( $user_id, 'wpseo_dismiss_configuration_workout_notice', '1' );
	update_user_meta( $user_id, '_yoast_wpseo_dismissed_notices', array( 'first-time-configuration' => true ) );
	if ( ! class_exists( 'WPSEO_Options' ) ) {
		return;
	}
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
	$wpseo = get_option( 'wpseo', array() );
	if ( is_array( $wpseo ) ) {
		$wpseo['first_time_install']                       = false;
		$wpseo['show_onboarding_notice']                   = false;
		$wpseo['dismiss_configuration_workout_notice']     = true;
		$wpseo['ignore_search_engines_discouraged_notice'] = true;
		$wpseo['configuration_finished_steps']             = array( 'siteRepresentation', 'socialProfiles', 'personalPreferences' );
		update_option( 'wpseo', $wpseo );
	}
	if ( class_exists( 'PLL_Admin_Notices' ) ) {
		PLL_Admin_Notices::dismiss( 'wizard' );
	}
}
