<?php
/**
 * One-shot public-copy fixes from the 19 Sep 2026 text review.
 * Only our template/legal/placeholder lines — not customer package copy.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'watphou_core_maybe_trim_terms_placeholders', 48 );
add_action( 'init', 'watphou_core_maybe_apply_copy_review', 50 );
add_action( 'init', 'watphou_core_maybe_apply_menu_quote_copy', 52 );

/**
 * Remove staging-only price and Banque Pour Le Commerce Exterieur Lao (BCEL) notes from Terms.
 * Runs before the reviewed-translation apply so those sentences are not written back.
 */
function watphou_core_maybe_trim_terms_placeholders(): void {
	if ( '1.6.6' === (string) get_option( 'watphou_terms_trim' ) ) {
		return;
	}
	if ( wp_installing() ) {
		return;
	}
	$en_id = function_exists( 'watphou_core_find_page_id' ) ? watphou_core_find_page_id( 'terms' ) : 0;
	if ( ! $en_id ) {
		$page  = get_page_by_path( 'terms' );
		$en_id = $page ? (int) $page->ID : 0;
	}
	if ( ! $en_id ) {
		return;
	}
	$ids = array( $en_id );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$tr = pll_get_post_translations( $en_id );
		if ( is_array( $tr ) && $tr ) {
			$ids = array_values( array_unique( array_map( 'intval', $tr ) ) );
		}
	}
	foreach ( $ids as $id ) {
		$post = get_post( $id );
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$next = watphou_core_strip_terms_placeholder_paragraphs( (string) $post->post_content );
		if ( $next === (string) $post->post_content ) {
			continue;
		}
		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => $next,
				'post_excerpt' => wp_trim_words( wp_strip_all_tags( $next ), 28 ),
			)
		);
	}
	if ( function_exists( 'watphou_core_reviewed_json_path' ) ) {
		$path = watphou_core_reviewed_json_path();
		if ( is_readable( $path ) ) {
			$hash = md5_file( $path );
			if ( is_string( $hash ) ) {
				update_option( 'watphou_reviewed_i18n_hash', $hash, false );
				delete_option( 'watphou_reviewed_i18n_pending_hash' );
				delete_option( 'watphou_reviewed_i18n_step' );
			}
		}
	}
	update_option( 'watphou_terms_trim', '1.6.6', false );
}

function watphou_core_strip_terms_placeholder_paragraphs( string $html ): string {
	$needles = array(
		'Do not treat placeholder prices as a payable amount',
		'BCEL) online payment is not live yet',
		'prix indicatifs ne constituent pas un montant',
		'paiement en ligne par Banque Pour Le Commerce',
		'Le paiement en ligne Banque Pour Le Commerce Exterieur Lao (BCEL) n\'est pas encore en ligne',
		'ราคาเหล่านี้เป็นเพียงตัวอย่างและไม่ใช่จำนวนเงินที่ต้องชำระ',
		'ระบบชำระเงินออนไลน์ของ Banque Pour Le Commerce',
		'อย่าถือว่าราคาตัวยึดตำแหน่งเป็นจำนวนเงินที่ต้องชำระ',
		'Starting prices on the website are indications',
		'Send a request by the form or WhatsApp. We reply with a quotation',
		'the price for your dates, and the terms that apply if you accept',
		'Payment is due as stated on that quotation, after you accept',
	);
	$pattern = '/(?:<!--\s*wp:paragraph\b[^>]*-->\s*)?<p\b[^>]*>.*?<\/p>(?:\s*<!--\s*\/wp:paragraph\s*-->)?/siu';
	$out     = preg_replace_callback(
		$pattern,
		static function ( array $m ) use ( $needles ): string {
			$plain = wp_strip_all_tags( $m[0] );
			$plain = preg_replace( '/\s+/u', ' ', $plain ) ?? $plain;
			foreach ( $needles as $needle ) {
				if ( false !== stripos( $plain, $needle ) ) {
					return '';
				}
			}
			return $m[0];
		},
		$html
	);
	if ( ! is_string( $out ) ) {
		$out = $html;
	}
	$sentence_patterns = array(
		'/Public prices currently show as.{0,180}placeholder prices as a payable amount\.?/isu',
		'/Bookings are requested by form or WhatsApp\..{0,160}online payment is not live yet\.?/isu',
		'/Les prix publics sont provisoirement.{0,220}montant à régler\.?/isu',
		'/Les réservations se demandent.{0,220}n’est pas encore disponible\.?/isu',
		'/ขณะนี้ราคาบนเว็บไซต์แสดงเป็น.{0,220}ไม่ใช่จำนวนเงินที่ต้องชำระ/isu',
		'/กรุณาส่งคำขอจองผ่านแบบฟอร์มหรือ WhatsApp.{0,220}ยังไม่เปิดให้บริการ/isu',
	);
	foreach ( $sentence_patterns as $sentence_pattern ) {
		$stripped = preg_replace( $sentence_pattern, '', $out );
		if ( is_string( $stripped ) ) {
			$out = $stripped;
		}
	}
	$out = preg_replace( "/[ \t]*\n{3,}/", "\n\n", $out ) ?? $out;
	$out = preg_replace( '/(?:<!--\s*wp:paragraph\b[^>]*-->\s*<!--\s*\/wp:paragraph\s*-->\s*)+/iu', '', $out ) ?? $out;
	return trim( $out ) . "\n";
}

function watphou_core_maybe_apply_copy_review(): void {
	if ( '1.6.4' === (string) get_option( 'watphou_copy_review_apply' ) ) {
		return;
	}
	if ( wp_installing() ) {
		return;
	}
	watphou_core_apply_copy_review_price_notes();
	watphou_core_apply_copy_review_pages();
	if ( function_exists( 'watphou_core_reviewed_json_path' ) ) {
		$path = watphou_core_reviewed_json_path();
		if ( is_readable( $path ) ) {
			$hash = md5_file( $path );
			if ( is_string( $hash ) ) {
				update_option( 'watphou_reviewed_i18n_hash', $hash, false );
				delete_option( 'watphou_reviewed_i18n_pending_hash' );
				delete_option( 'watphou_reviewed_i18n_step' );
			}
		}
	}
	update_option( 'watphou_copy_review_apply', '1.6.4', false );
}

function watphou_core_apply_copy_review_price_notes(): void {
	if ( ! function_exists( 'watphou_core_catalog_packages' ) || ! function_exists( 'watphou_core_find_tour_id' ) ) {
		return;
	}
	foreach ( watphou_core_catalog_packages() as $row ) {
		$file = WATPHOU_CORE_PATH . 'data/packages/' . $row['slug'] . '.json';
		if ( ! is_readable( $file ) ) {
			continue;
		}
		$pkg = json_decode( (string) file_get_contents( $file ), true );
		if ( ! is_array( $pkg ) ) {
			continue;
		}
		$note = trim( (string) ( $pkg['price_note'] ?? '' ) );
		if ( '' === $note ) {
			continue;
		}
		$id = watphou_core_find_tour_id( $row['slug'], $row['code'] );
		if ( ! $id ) {
			continue;
		}
		$ids = array( $id );
		if ( function_exists( 'pll_get_post_translations' ) ) {
			$tr = pll_get_post_translations( $id );
			if ( $tr ) {
				$ids = array_map( 'intval', $tr );
			}
		}
		foreach ( $ids as $tid ) {
			if ( $tid && 'tour' === get_post_type( $tid ) ) {
				update_post_meta( $tid, 'tour_price_note', $note );
			}
		}
	}
}

function watphou_core_apply_copy_review_pages(): void {
	$phone = get_option( 'watphou_phone', '+85620 9949 5858' );
	$email = get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' );
	$addr  = get_option( 'watphou_address', 'Street N°5, Ban Vat Luang, Pakse, Laos' );

	$pages = array(
		'privacy-policy' => array(
			'title'   => 'Privacy Policy',
			'content' => '<p>Watphou Travels (Pakse, Laos) collects only the information you send through our enquiry form, email, or WhatsApp so we can answer your tour request.</p>'
				. '<p>Typical fields: name, email, phone, travel dates, group size, and your message. We do not sell this information.</p>'
				. '<p>Contact: <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a> · ' . esc_html( $phone ) . '<br>' . esc_html( $addr ) . '</p>',
		),
		'terms'          => array(
			'title'   => 'Terms of Use',
			'content' => '<p>Watphou Travels offers private tours in Southern Laos, departing from Pakse. Pages on this website describe itineraries. Your written quotation is the contract once you accept it.</p>'
				. '<p>Contact: <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a> · ' . esc_html( $phone ) . '<br>' . esc_html( $addr ) . '</p>',
		),
		'cancellation'   => array(
			'title'   => 'Cancellation',
			'content' => '<p>Cancellation and payment conditions are provided with your quotation and clearly confirmed before booking.</p>'
				. '<p>To change or cancel a request, write to <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a> or WhatsApp ' . esc_html( $phone ) . '.</p>'
				. '<p>' . esc_html( $addr ) . '</p>',
		),
		'travel-guide'   => array(
			'title'   => 'Southern Laos Travel Guide',
			'content' => '<p>A short, factual guide to the places we operate from Pakse. Use it to choose a private day tour or a multi-day journey.</p>'
				. '<h2>Pakse</h2><p>Pakse is the gateway to Southern Laos and the office of Watphou Travels (Street N°5, Ban Vat Luang). Most private tours start here.</p>'
				. '<p><a href="' . esc_url( home_url( '/destinations/pakse/' ) ) . '">Pakse tours</a></p>'
				. '<h2>Bolaven Plateau</h2><p>Highlands east of Pakse known for Tad Fane and Tad Yuang waterfalls and coffee farms. A classic full-day private tour from Pakse.</p>'
				. '<p><a href="' . esc_url( home_url( '/destinations/bolaven-plateau/' ) ) . '">Bolaven Plateau tours</a></p>'
				. '<h2>Vat Phou and Champasak</h2><p>UNESCO-listed Vat Phou sits near Champasak town, south of Pakse, on the Mekong. Visit as a day trip or combined with the 4000 Islands.</p>'
				. '<p><a href="' . esc_url( home_url( '/destinations/champasak/' ) ) . '">Vat Phou and Champasak tours</a></p>'
				. '<h2>4000 Islands (Si Phan Don)</h2><p>A Mekong archipelago near the Cambodian border: Don Khone, Don Det, and the Liphi waterfalls. Often a two-day private journey from Pakse.</p>'
				. '<p><a href="' . esc_url( home_url( '/destinations/4000-islands/' ) ) . '">4000 Islands tours</a></p>',
		),
		'contact-us'     => array(
			'title'   => 'Contact Us',
			'content' => '<p>Planning a trip in Southern Laos? Contact our Pakse team by WhatsApp, phone or email and tell us your dates and travel plans.</p>'
				. '<p>Phone/WhatsApp: <a href="' . esc_url( function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858' ) . '">' . esc_html( $phone ) . '</a></p>'
				. '<p>Email: <a href="mailto:sales.watphoutravel@gmail.com">sales.watphoutravel@gmail.com</a> or <a href="mailto:watphoutravel.of@gmail.com">watphoutravel.of@gmail.com</a></p>'
				. '<p>Address: ' . esc_html( $addr ) . '</p>',
		),
		'book-online'    => array(
			'title'   => 'Request a quote',
			'content' => '<p>You are requesting a private tour. We reply with a clear quote for your dates.</p>' . "\n\n" . '<!-- wp:shortcode -->[watphou_booking_form]<!-- /wp:shortcode -->',
		),
	);

	foreach ( $pages as $slug => $data ) {
		watphou_core_replace_public_page( $slug, $data['title'], $data['content'] );
	}
}

function watphou_core_replace_public_page( string $slug, string $title, string $content ): void {
	$en_id = function_exists( 'watphou_core_find_page_id' ) ? watphou_core_find_page_id( $slug ) : 0;
	if ( ! $en_id ) {
		$page = get_page_by_path( $slug );
		$en_id = $page ? (int) $page->ID : 0;
	}
	if ( ! $en_id ) {
		return;
	}
	$excerpt = wp_trim_words( wp_strip_all_tags( $content ), 28 );
	wp_update_post(
		array(
			'ID'           => $en_id,
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'post_status'  => 'publish',
		)
	);
	if ( function_exists( 'watphou_core_update_translated_post' ) ) {
		foreach ( array( 'fr', 'th' ) as $lang ) {
			watphou_core_update_translated_post( $en_id, $lang, $title, $content, $excerpt );
		}
	}
}

/**
 * Menu capitalization (Day Tours / 2-Day Tours / …) and Request a quote page.
 */
function watphou_core_maybe_apply_menu_quote_copy(): void {
	if ( '1.6.12' === (string) get_option( 'watphou_menu_quote_copy' ) ) {
		return;
	}
	if ( wp_installing() ) {
		return;
	}
	$pages = array(
		'day-tours'       => array(
			'en' => 'Day Tours',
			'fr' => 'Excursions à la journée',
			'th' => 'ทัวร์แบบไปเช้าเย็นกลับ',
		),
		'2-day-tours'     => array(
			'en' => '2-Day Tours',
			'fr' => 'Circuits de 2 jours',
			'th' => 'ทัวร์ 2 วัน',
		),
		'3-day-tours'     => array(
			'en' => '3-Day Tours',
			'fr' => 'Circuits de 3 jours',
			'th' => 'ทัวร์ 3 วัน',
		),
		'4-6-day-tours'   => array(
			'en' => '4-6 Day Tours',
			'fr' => 'Circuits de 4 à 6 jours',
			'th' => 'ทัวร์ 4–6 วัน',
		),
		'book-online'     => array(
			'en' => 'Request a quote',
			'fr' => 'Demander un devis',
			'th' => 'ขอใบเสนอราคา',
		),
	);
	$intros = array(
		'en' => 'You are requesting a private tour. We reply with a clear quote for your dates.',
		'fr' => 'Vous demandez un circuit privé. Nous vous répondons avec un devis clair pour vos dates.',
		'th' => 'คุณกำลังขอทัวร์ส่วนตัว เราจะตอบกลับด้วยใบเสนอราคาที่ชัดเจนตามวันเดินทางของคุณ',
	);
	$form = "\n\n<!-- wp:shortcode -->[watphou_booking_form]<!-- /wp:shortcode -->";
	foreach ( $pages as $slug => $titles ) {
		$en_id = function_exists( 'watphou_core_find_page_id' ) ? watphou_core_find_page_id( $slug ) : 0;
		if ( ! $en_id ) {
			continue;
		}
		$is_quote = ( 'book-online' === $slug );
		$en_body  = $is_quote ? ( '<p>' . esc_html( $intros['en'] ) . '</p>' . $form ) : (string) get_post_field( 'post_content', $en_id );
		wp_update_post(
			array(
				'ID'           => $en_id,
				'post_title'   => $titles['en'],
				'post_content' => $en_body,
				'post_excerpt' => $is_quote ? $intros['en'] : wp_trim_words( wp_strip_all_tags( $en_body ), 28 ),
				'post_status'  => 'publish',
			)
		);
		if ( function_exists( 'watphou_core_force_post_slug' ) ) {
			watphou_core_force_post_slug( $en_id, $slug );
		}
		if ( $is_quote ) {
			update_post_meta( $en_id, '_yoast_wpseo_title', 'Request a quote | Watphou Travels' );
			update_post_meta( $en_id, '_yoast_wpseo_metadesc', $intros['en'] );
		}
		if ( ! function_exists( 'watphou_core_update_translated_post' ) ) {
			continue;
		}
		foreach ( array( 'fr', 'th' ) as $lang ) {
			$tid = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $en_id, $lang ) : 0;
			$body = $is_quote
				? ( '<p>' . esc_html( $intros[ $lang ] ) . '</p>' . $form )
				: ( $tid ? (string) get_post_field( 'post_content', $tid ) : $en_body );
			watphou_core_update_translated_post( $en_id, $lang, $titles[ $lang ], $body, $titles[ $lang ] );
			if ( $tid && function_exists( 'watphou_core_force_post_slug' ) ) {
				watphou_core_force_post_slug( $tid, $slug );
			}
			if ( $is_quote && $tid ) {
				update_post_meta( $tid, '_yoast_wpseo_title', $titles[ $lang ] . ' | Watphou Travels' );
			}
		}
	}
	update_option( 'watphou_menu_quote_copy', '1.6.12', false );
}
