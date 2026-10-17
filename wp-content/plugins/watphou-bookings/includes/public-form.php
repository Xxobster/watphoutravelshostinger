<?php
defined( 'ABSPATH' ) || exit;

add_shortcode( 'watphou_booking_form', 'watphou_booking_form_shortcode' );
add_action( 'wp_ajax_watphou_submit_booking', 'watphou_handle_booking_submit' );
add_action( 'wp_ajax_nopriv_watphou_submit_booking', 'watphou_handle_booking_submit' );

/**
 * Resolve which tour this form is for: shortcode, current tour page, or ?tour= slug.
 */
function watphou_booking_resolve_tour_id( int $from_atts ): int {
	if ( $from_atts > 0 && 'tour' === get_post_type( $from_atts ) ) {
		return $from_atts;
	}
	if ( is_singular( 'tour' ) ) {
		return (int) get_the_ID();
	}
	$q = isset( $_GET['tour'] ) ? sanitize_text_field( wp_unslash( $_GET['tour'] ) ) : '';
	if ( '' === $q ) {
		return 0;
	}
	if ( ctype_digit( $q ) ) {
		$id = (int) $q;
		return ( 'tour' === get_post_type( $id ) ) ? $id : 0;
	}
	if ( function_exists( 'watphou_core_find_tour_id' ) ) {
		$id = watphou_core_find_tour_id( sanitize_title( $q ) );
		if ( $id && function_exists( 'pll_get_post' ) ) {
			$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : '';
			$tid  = $lang ? (int) pll_get_post( $id, $lang ) : 0;
			if ( $tid ) {
				$id = $tid;
			}
		}
		return $id;
	}
	$page = get_page_by_path( sanitize_title( $q ), OBJECT, 'tour' );
	return ( $page instanceof WP_Post ) ? (int) $page->ID : 0;
}

/**
 * Published tours in Excel catalog order.
 *
 * @return WP_Post[]
 */
function watphou_booking_published_tours(): array {
	$posts = get_posts(
		array(
			'post_type'      => 'tour',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	if ( function_exists( 'watphou_core_sort_tours_by_catalog' ) && $posts ) {
		return watphou_core_sort_tours_by_catalog( $posts );
	}
	return $posts;
}

/**
 * Tour fields to show on the form and store with the request.
 *
 * @return array<string, string>
 */
function watphou_booking_tour_context( int $tour_id ): array {
	$empty = array(
		'id'           => 0,
		'name'         => '',
		'code'         => '',
		'duration'     => '',
		'destinations' => '',
		'price'        => '',
		'headline'     => '',
		'departure'    => '',
		'slug'         => '',
	);
	// Only published tours: a draft's name must not leak through ?tour=ID or a forged post.
	if ( $tour_id < 1 || 'tour' !== get_post_type( $tour_id ) || 'publish' !== get_post_status( $tour_id ) ) {
		return $empty;
	}
	$post = get_post( $tour_id );
	if ( ! $post instanceof WP_Post ) {
		return $empty;
	}
	$dests = wp_get_post_terms( $tour_id, 'destination', array( 'fields' => 'names' ) );
	$dest  = ( ! is_wp_error( $dests ) && $dests ) ? implode( ', ', $dests ) : '';
	if ( function_exists( 'watphou_core_localize_destination_list' ) ) {
		$dest = watphou_core_localize_destination_list( $dest );
	}
	$slug  = function_exists( 'watphou_core_tour_catalog_slug' )
		? watphou_core_tour_catalog_slug( $post )
		: (string) $post->post_name;
	$departure = '';
	if ( defined( 'WATPHOU_CORE_PATH' ) && $slug ) {
		$file = WATPHOU_CORE_PATH . 'data/packages/' . $slug . '.json';
		if ( is_readable( $file ) ) {
			$pkg = json_decode( (string) file_get_contents( $file ), true );
			if ( is_array( $pkg ) ) {
				$departure = (string) ( $pkg['departure'] ?? '' );
				if ( $departure && function_exists( 'watphou_core_translate_public_string' ) ) {
					$departure = watphou_core_translate_public_string( $departure );
				}
			}
		}
	}
	$price = function_exists( 'watphou_get_tour_price_display' )
		? watphou_get_tour_price_display( $tour_id )
		: __( 'From $XX per person — Standard.', 'watphou-bookings' );
	return array(
		'id'           => $tour_id,
		'name'         => (string) $post->post_title,
		'code'         => function_exists( 'watphou_core_tour_package_code' )
			? watphou_core_tour_package_code( $post )
			: (string) get_post_meta( $tour_id, 'tour_code', true ),
		'duration'     => function_exists( 'watphou_core_translate_public_string' )
			? watphou_core_translate_public_string( (string) get_post_meta( $tour_id, 'tour_duration', true ) )
			: (string) get_post_meta( $tour_id, 'tour_duration', true ),
		'destinations' => $dest,
		'price'        => $price,
		'headline'     => (string) get_post_meta( $tour_id, 'tour_headline', true ),
		'departure'    => $departure,
		'slug'         => $slug,
	);
}

/**
 * Language of the page the form was sent from (en, fr, th).
 */
function watphou_booking_request_lang(): string {
	$lang = isset( $_POST['lang'] ) ? sanitize_key( wp_unslash( $_POST['lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( '' === $lang && function_exists( 'watphou_core_current_lang_slug' ) ) {
		$lang = watphou_core_current_lang_slug();
	}
	return in_array( $lang, array( 'en', 'fr', 'th' ), true ) ? $lang : 'en';
}

/**
 * Translate a form message for the visitor's language. AJAX runs under /wp-admin/,
 * so the URL-based language switch does not apply there.
 */
function watphou_booking_tr( string $text, string $lang ): string {
	if ( 'en' === $lang || ! function_exists( 'watphou_core_load_ui_extras_json' ) ) {
		return $text;
	}
	$map = watphou_core_load_ui_extras_json();
	return isset( $map[ $text ][ $lang ] ) && '' !== $map[ $text ][ $lang ] ? (string) $map[ $text ][ $lang ] : $text;
}

/**
 * Signed render time. Lets the server drop forms sent back faster than a person can type.
 */
function watphou_booking_time_token(): string {
	$ts = (string) time();
	return $ts . '.' . substr( hash_hmac( 'sha256', $ts, wp_salt( 'nonce' ) ), 0, 20 );
}

/**
 * Seconds since the form was rendered, or -1 when the token is missing or forged.
 */
function watphou_booking_token_age( string $token ): int {
	if ( ! preg_match( '/^(\d{9,11})\.([a-f0-9]{20})$/', $token, $m ) ) {
		return -1;
	}
	$expected = substr( hash_hmac( 'sha256', $m[1], wp_salt( 'nonce' ) ), 0, 20 );
	if ( ! hash_equals( $expected, $m[2] ) ) {
		return -1;
	}
	return time() - (int) $m[1];
}

/**
 * At most 6 requests per visitor address every 15 minutes.
 */
function watphou_booking_rate_limited(): bool {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$key = 'wpt_bk_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 24 );
	$hits = (int) get_transient( $key );
	if ( $hits >= 6 ) {
		return true;
	}
	set_transient( $key, $hits + 1, 15 * MINUTE_IN_SECONDS );
	return false;
}

/**
 * Group the visitor clicked on the price table: a row index, 'more' (9+), or ''.
 */
function watphou_booking_requested_group(): string {
	$g = isset( $_GET['group'] ) ? sanitize_key( wp_unslash( $_GET['group'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'more' === $g || ( ctype_digit( $g ) && (int) $g < 50 ) ) {
		return $g;
	}
	return '';
}

/**
 * Price-table facts for a booking, recomputed on the server from the number of people.
 *
 * @return array{group:string, prices:string}
 */
function watphou_booking_price_for( int $tour_id, int $people ): array {
	$out = array(
		'group'  => '',
		'prices' => '',
	);
	if ( $tour_id < 1 || $people < 1 || ! function_exists( 'watphou_core_get_price_table' ) ) {
		return $out;
	}
	$table = watphou_core_get_price_table( $tour_id );
	$max   = watphou_core_price_table_max_people( $table );
	$idx   = watphou_core_price_table_row_for( $table, $people );
	if ( $idx < 0 ) {
		$out['group']  = $people > $max ? ( $max + 1 ) . '+ people' : $people . ' people (not in table)';
		$out['prices'] = 'On request';
		return $out;
	}
	$row = $table['rows'][ $idx ];
	$out['group'] = $row['min'] === $row['max'] ? $row['min'] . ' people' : $row['min'] . '/' . $row['max'] . ' people';
	$fmt = static function ( string $p ): string {
		return '' === $p ? 'on request' : watphou_core_format_usd( $p ) . ' per person';
	};
	// Total from the stored table and the server-checked number of people, never from the browser.
	$tot = static function ( string $p ) use ( $people ): string {
		$t = function_exists( 'watphou_core_price_total' ) ? watphou_core_price_total( $p, $people ) : '';
		return '' === $t ? 'on request' : $t;
	};
	if ( watphou_core_price_table_is_day( $tour_id, $table ) ) {
		$out['prices'] = $fmt( $row['a'] ) . '; total ' . $tot( $row['a'] );
	} else {
		$out['prices'] = 'Sharing Twin/Double Room ' . $fmt( $row['a'] ) . ' (total ' . $tot( $row['a'] ) . '); Private Single Room ' . $fmt( $row['b'] ) . ' (total ' . $tot( $row['b'] ) . ')';
	}
	return $out;
}

function watphou_booking_snapshot_text( array $ctx ): string {
	$lines = array();
	if ( $ctx['name'] ) {
		$lines[] = 'Tour: ' . $ctx['name'];
	}
	if ( $ctx['code'] ) {
		$lines[] = 'Code: ' . $ctx['code'];
	}
	if ( $ctx['duration'] ) {
		$lines[] = 'Duration: ' . $ctx['duration'];
	}
	if ( $ctx['destinations'] ) {
		$lines[] = 'Destinations: ' . $ctx['destinations'];
	}
	if ( $ctx['departure'] ) {
		$lines[] = 'Departure: ' . $ctx['departure'];
	}
	return implode( "\n", $lines );
}

function watphou_booking_form_shortcode( $atts ): string {
	$atts    = shortcode_atts(
		array(
			'tour_id' => 0,
			'group'   => '',
		),
		$atts,
		'watphou_booking_form'
	);
	$tour_id = watphou_booking_resolve_tour_id( (int) $atts['tour_id'] );
	$ctx     = watphou_booking_tour_context( $tour_id );
	$locked  = $ctx['id'] > 0;
	$tours   = $locked ? array() : watphou_booking_published_tours();
	$privacy   = function_exists( 'watphou_page_url' ) ? watphou_page_url( 'privacy-policy' ) : home_url( '/privacy-policy/' );
	$ui_lang   = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	$ui_lang   = in_array( $ui_lang, array( 'en', 'fr', 'th' ), true ) ? $ui_lang : 'en';
	$form_lang = array(
		'fr' => 'fr-FR',
		'th' => 'th-TH',
	)[ $ui_lang ] ?? 'en-GB';

	// Number of people: filled in for a single group size (2 pax, 4 pax); empty and
	// required for a range (6/8 pax) or for "Group size 9+".
	$group       = (string) $atts['group'];
	$group       = ( 'more' === $group || ctype_digit( $group ) ) ? $group : '';
	$people      = '';
	$placeholder = '';
	$people_min  = 1;
	if ( $locked && '' !== $group && function_exists( 'watphou_core_get_price_table' ) ) {
		$table = watphou_core_get_price_table( $ctx['id'] );
		if ( 'more' === $group ) {
			$people_min  = watphou_core_price_table_max_people( $table ) + 1;
			$placeholder = sprintf(
				/* translators: %d: smallest large-group size */
				__( '%d or more', 'watphou-bookings' ),
				$people_min
			);
		} elseif ( isset( $table['rows'][ (int) $group ] ) ) {
			$row = $table['rows'][ (int) $group ];
			if ( $row['min'] === $row['max'] ) {
				$people = (string) $row['min'];
			} else {
				$placeholder = $row['min'] . '–' . $row['max'];
			}
		}
	}
	$countries = function_exists( 'watphou_core_countries' ) ? watphou_core_countries( $ui_lang ) : array();
	$uid       = wp_unique_id( 'wpt-bf-' );
	ob_start();
	?>
	<form class="watphou-booking-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" lang="<?php echo esc_attr( $form_lang ); ?>">
		<input type="hidden" name="action" value="watphou_submit_booking"/>
		<input type="hidden" name="lang" value="<?php echo esc_attr( $ui_lang ); ?>"/>
		<input type="hidden" name="group" value="<?php echo esc_attr( $group ); ?>"/>
		<input type="hidden" name="wpt_ts" value="<?php echo esc_attr( watphou_booking_time_token() ); ?>"/>
		<?php wp_nonce_field( 'watphou_booking_submit', 'watphou_booking_nonce' ); ?>
		<div class="watphou-booking-form__hp" aria-hidden="true">
			<label>Website <input type="text" name="wpt_website" value="" tabindex="-1" autocomplete="off"/></label>
		</div>
		<fieldset class="watphou-booking-form__guest">
			<legend><?php esc_html_e( 'Contact details', 'watphou-bookings' ); ?></legend>
			<?php if ( $locked ) : ?>
				<input type="hidden" name="tour_id" value="<?php echo esc_attr( (string) $ctx['id'] ); ?>"/>
				<p><label><?php esc_html_e( 'Tour name', 'watphou-bookings' ); ?> <input class="watphou-bf-name" type="text" readonly value="<?php echo esc_attr( $ctx['name'] ); ?>"/></label></p>
			<?php else : ?>
				<p><label><?php esc_html_e( 'Tour name', 'watphou-bookings' ); ?>
					<select name="tour_id" class="watphou-booking-tour-select">
						<option value="0"><?php esc_html_e( 'Select a tour (or describe it in the message)', 'watphou-bookings' ); ?></option>
						<?php foreach ( $tours as $tour ) : ?>
							<?php $code = function_exists( 'watphou_core_tour_package_code' ) ? watphou_core_tour_package_code( $tour ) : ''; ?>
							<option value="<?php echo esc_attr( (string) $tour->ID ); ?>"><?php echo esc_html( trim( ( $code ? $code . ' — ' : '' ) . $tour->post_title ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label></p>
			<?php endif; ?>
			<p><label><?php esc_html_e( 'Name', 'watphou-bookings' ); ?> <span class="watphou-booking-form__req"><?php esc_html_e( '(required)', 'watphou-bookings' ); ?></span>
				<input required name="customer_name" type="text" maxlength="120" autocomplete="name" data-label="<?php esc_attr_e( 'Name', 'watphou-bookings' ); ?>"/>
				<span class="watphou-booking-form__field-error" hidden></span>
			</label></p>
			<p><label><?php esc_html_e( 'Email', 'watphou-bookings' ); ?> <span class="watphou-booking-form__req"><?php esc_html_e( '(required)', 'watphou-bookings' ); ?></span>
				<input required name="email" type="email" maxlength="120" autocomplete="email" data-label="<?php esc_attr_e( 'Email', 'watphou-bookings' ); ?>"/>
				<span class="watphou-booking-form__field-error" hidden></span>
			</label></p>
			<p><label><?php esc_html_e( 'Phone', 'watphou-bookings' ); ?> <input name="phone" type="tel" maxlength="40" autocomplete="tel"/></label></p>
			<p><label><?php esc_html_e( 'WhatsApp', 'watphou-bookings' ); ?> <input name="whatsapp" type="tel" maxlength="40"/></label></p>
			<p><label><?php esc_html_e( 'Preferred date', 'watphou-bookings' ); ?> <span class="watphou-booking-form__req"><?php esc_html_e( '(required)', 'watphou-bookings' ); ?></span>
				<input required name="preferred_date" type="date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" lang="<?php echo esc_attr( $form_lang ); ?>" data-label="<?php esc_attr_e( 'Preferred date', 'watphou-bookings' ); ?>"/>
				<span class="watphou-booking-form__field-error" hidden></span>
			</label></p>
			<p><label><?php esc_html_e( 'Alternative date', 'watphou-bookings' ); ?> <input name="alt_date" type="date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" lang="<?php echo esc_attr( $form_lang ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Number of people', 'watphou-bookings' ); ?> <span class="watphou-booking-form__req"><?php esc_html_e( '(required)', 'watphou-bookings' ); ?></span>
				<input required name="people" type="number" inputmode="numeric" min="1" max="99" step="1" value="<?php echo esc_attr( $people ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" data-min="<?php echo (int) $people_min; ?>" data-label="<?php esc_attr_e( 'Number of people', 'watphou-bookings' ); ?>"/>
				<span class="watphou-booking-form__field-error" hidden></span>
			</label></p>
			<p><label><?php esc_html_e( 'Nationality', 'watphou-bookings' ); ?>
				<input name="nationality" type="text" maxlength="64" list="<?php echo esc_attr( $uid ); ?>-countries" autocomplete="country-name" placeholder="<?php esc_attr_e( 'Type or choose from the list', 'watphou-bookings' ); ?>"/>
			</label></p>
			<datalist id="<?php echo esc_attr( $uid ); ?>-countries">
				<?php foreach ( $countries as $country ) : ?>
					<option value="<?php echo esc_attr( (string) $country[1] ); ?>"></option>
				<?php endforeach; ?>
			</datalist>
			<p><label><?php esc_html_e( 'Pickup / hotel', 'watphou-bookings' ); ?> <input name="pickup_location" type="text" maxlength="200" placeholder="<?php esc_attr_e( 'Hotel name in Pakse', 'watphou-bookings' ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Message', 'watphou-bookings' ); ?> <textarea name="customer_message" rows="4" maxlength="3000" placeholder="<?php esc_attr_e( 'Dates, pace, dietary needs, or questions', 'watphou-bookings' ); ?>"></textarea></label></p>
			<p><label>
				<input required name="privacy_consent" type="checkbox" value="1" data-label="<?php esc_attr_e( 'Agreement to the privacy policy', 'watphou-bookings' ); ?>"/>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %s: privacy policy URL */
						__( 'I agree to the <a href="%s">privacy policy</a>.', 'watphou-bookings' ),
						esc_url( $privacy )
					),
					array( 'a' => array( 'href' => array() ) )
				);
				?>
				<span class="watphou-booking-form__req"><?php esc_html_e( '(required)', 'watphou-bookings' ); ?></span>
				<span class="watphou-booking-form__field-error" hidden></span>
			</label></p>
			<div class="watphou-booking-form__alert" hidden role="alert"></div>
			<p><button type="submit" class="wp-block-button__link"><?php esc_html_e( 'Send Inquiry', 'watphou-bookings' ); ?></button></p>
			<p class="watphou-booking-form__hint"><?php esc_html_e( 'This saves your request for the Pakse office and emails them. You will see a confirmation on this page. We reply by email or WhatsApp.', 'watphou-bookings' ); ?></p>
		</fieldset>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Plain text for one field: no tags, no control characters, capped length.
 */
function watphou_booking_clean( string $key, int $max, bool $multiline = false ): string {
	$raw = isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$val = $multiline ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
	$val = (string) preg_replace( $multiline ? '/[^\P{C}\n\t]/u' : '/\p{C}/u', '', $val );
	return mb_substr( trim( $val ), 0, $max );
}

/**
 * A Y-m-d date from the form, or '' when missing or not a real date.
 */
function watphou_booking_clean_date( string $key ): string {
	$val = watphou_booking_clean( $key, 10 );
	$d   = DateTime::createFromFormat( '!Y-m-d', $val );
	return ( $d && $d->format( 'Y-m-d' ) === $val ) ? $val : '';
}

function watphou_booking_field_errors( string $lang ): array {
	$errors = array();
	if ( '' === watphou_booking_clean( 'customer_name', 120 ) ) {
		$errors['customer_name'] = watphou_booking_tr( 'Please enter your name.', $lang );
	}
	$raw_email = watphou_booking_clean( 'email', 120 );
	$email     = sanitize_email( $raw_email );
	if ( '' === $raw_email ) {
		$errors['email'] = watphou_booking_tr( 'Please enter your email address.', $lang );
	} elseif ( '' === $email || ! is_email( $email ) ) {
		$errors['email'] = watphou_booking_tr( 'Please enter a valid email address (for example name@example.com).', $lang );
	}
	$pref     = watphou_booking_clean_date( 'preferred_date' );
	$earliest = wp_date( 'Y-m-d', time() - DAY_IN_SECONDS );
	if ( '' === $pref ) {
		$errors['preferred_date'] = watphou_booking_tr( 'Please choose a preferred date.', $lang );
	} elseif ( $pref < $earliest ) {
		$errors['preferred_date'] = watphou_booking_tr( 'Please choose a date in the future.', $lang );
	}
	$people = watphou_booking_clean( 'people', 3 );
	if ( ! ctype_digit( $people ) || (int) $people < 1 || (int) $people > 99 ) {
		$errors['people'] = watphou_booking_tr( 'Please verify the number of people.', $lang );
	}
	if ( empty( $_POST['privacy_consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$errors['privacy_consent'] = watphou_booking_tr( 'Please agree to the privacy policy.', $lang );
	}
	return $errors;
}

/**
 * Inboxes that receive this tour request (primary + alt).
 *
 * @return string[]
 */
function watphou_booking_office_recipients(): array {
	if ( function_exists( 'watphou_office_email_recipients' ) ) {
		return watphou_office_email_recipients();
	}
	return array( 'sales.watphoutravel@gmail.com', 'watphoutravel.of@gmail.com' );
}

/**
 * Email both office inboxes. The booking row is already saved; log send or failure on that row.
 */
function watphou_booking_notify_office( int $booking_id, string $guest_name, string $guest_email, string $tour_name, string $body ): bool {
	$recipients = watphou_booking_office_recipients();
	$headers    = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $guest_email ) && ! preg_match( '/[\r\n]/', $guest_email ) ) {
		$safe_name = trim( str_replace( array( "\r", "\n", ':', '<', '>', '"', ',', ';' ), '', $guest_name ) );
		$headers[] = $safe_name
			? 'Reply-To: "' . $safe_name . '" <' . $guest_email . '>'
			: 'Reply-To: ' . $guest_email;
	}
	$subject    = 'New booking request' . ( $tour_name ? ': ' . str_replace( array( "\r", "\n" ), ' ', $tour_name ) : '' );
	$mail_error = '';
	$on_fail    = static function ( $err ) use ( &$mail_error ): void {
		if ( $err instanceof WP_Error ) {
			$mail_error = $err->get_error_message();
		}
	};
	add_action( 'wp_mail_failed', $on_fail );
	$sent = wp_mail( $recipients, $subject, $body, $headers );
	remove_action( 'wp_mail_failed', $on_fail );
	$note = $sent
		? 'Office email sent to ' . implode( ', ', $recipients )
		: 'Office email FAILED to ' . implode( ', ', $recipients ) . ( $mail_error ? ': ' . $mail_error : '' );
	Watphou_Booking_Repository::log_event( $booking_id, 'requested', 'requested', 0, $note );
	return (bool) $sent;
}

function watphou_handle_booking_submit(): void {
	$lang = watphou_booking_request_lang();
	if ( ! check_ajax_referer( 'watphou_booking_submit', 'watphou_booking_nonce', false ) ) {
		wp_send_json_error( array( 'message' => watphou_booking_tr( 'This page has expired. Please reload the page and send the form again.', $lang ) ), 403 );
	}
	$thanks = watphou_booking_tr( 'Thank you. We received your tour request. The Pakse office will reply by email or WhatsApp.', $lang );
	// Spam traps: a hidden field people never see, and a form sent back within 3 seconds.
	$age = watphou_booking_token_age( watphou_booking_clean( 'wpt_ts', 40 ) );
	if ( '' !== watphou_booking_clean( 'wpt_website', 200 ) || ( $age >= 0 && $age < 3 ) ) {
		wp_send_json_success( array( 'message' => $thanks ) );
	}
	if ( $age < 0 ) {
		wp_send_json_error( array( 'message' => watphou_booking_tr( 'This page has expired. Please reload the page and send the form again.', $lang ) ), 400 );
	}
	$errors = watphou_booking_field_errors( $lang );
	if ( $errors ) {
		wp_send_json_error(
			array(
				'message' => watphou_booking_tr( 'Please complete these required fields:', $lang ),
				'errors'  => $errors,
			)
		);
	}
	if ( watphou_booking_rate_limited() ) {
		wp_send_json_error( array( 'message' => watphou_booking_tr( 'Too many requests from this connection. Please wait a few minutes, or contact us on WhatsApp.', $lang ) ), 429 );
	}
	// Tour, group and price come from the database, never from the browser.
	$tour_id = absint( $_POST['tour_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$ctx     = watphou_booking_tour_context( $tour_id );
	$tour_id = (int) $ctx['id'];
	$people  = (int) watphou_booking_clean( 'people', 3 );
	$price   = watphou_booking_price_for( $tour_id, $people );
	$name    = watphou_booking_clean( 'customer_name', 120 );
	$email   = sanitize_email( watphou_booking_clean( 'email', 120 ) );
	$phone   = watphou_booking_clean( 'phone', 40 );
	$wa      = watphou_booking_clean( 'whatsapp', 40 );
	$pref    = watphou_booking_clean_date( 'preferred_date' );
	$alt     = watphou_booking_clean_date( 'alt_date' );
	$nation  = watphou_booking_clean( 'nationality', 64 );
	$nat_en  = function_exists( 'watphou_core_country_english_name' ) ? watphou_core_country_english_name( $nation ) : '';
	$nat_out = ( $nat_en && 0 !== strcasecmp( $nat_en, $nation ) ) ? $nation . ' (' . $nat_en . ')' : $nation;
	$pickup  = watphou_booking_clean( 'pickup_location', 200, true );
	$msg     = watphou_booking_clean( 'customer_message', 3000, true );
	$snap    = watphou_booking_snapshot_text( $ctx );
	if ( $price['group'] ) {
		$snap .= ( $snap ? "\n" : '' ) . 'Group size: ' . $price['group'] . "\nPrice (USD): " . $price['prices'];
	}
	$id = Watphou_Booking_Repository::create(
		array(
			'tour_id'          => $tour_id,
			'tour_name'        => $ctx['name'],
			'customer_name'    => $name,
			'email'            => $email,
			'phone'            => $phone,
			'whatsapp'         => $wa,
			'preferred_date'   => $pref,
			'alt_date'         => $alt ?: null,
			'adults'           => $people,
			'children'         => 0,
			'country'          => mb_substr( $nat_out, 0, 64 ),
			'pickup_location'  => $pickup,
			'customer_message' => $msg,
			'staff_notes'      => $snap,
			'privacy_consent'  => 1,
			'language'         => $lang,
		)
	);
	if ( $id < 1 ) {
		wp_send_json_error( array( 'message' => watphou_booking_tr( 'The request could not be sent. Check your connection and try again.', $lang ) ), 500 );
	}
	$body = implode(
		"\n",
		array_filter(
			array(
				'New booking #' . $id,
				$snap,
				'Number of people: ' . $people,
				'Name: ' . $name,
				'Email: ' . $email,
				$phone ? 'Phone: ' . $phone : '',
				$wa ? 'WhatsApp: ' . $wa : '',
				'Preferred date: ' . $pref,
				$alt ? 'Alternative date: ' . $alt : '',
				$nat_out ? 'Nationality: ' . $nat_out : '',
				$pickup ? 'Pickup / hotel: ' . $pickup : '',
				$msg ? "Message:\n" . $msg : '',
				'Language: ' . strtoupper( $lang ),
			)
		)
	);
	watphou_booking_notify_office( $id, $name, $email, $ctx['name'], $body );
	wp_send_json_success(
		array(
			'booking_id' => $id,
			'message'    => $thanks,
			'tour'       => $ctx['name'],
			'group'      => $price['group'],
		)
	);
}
