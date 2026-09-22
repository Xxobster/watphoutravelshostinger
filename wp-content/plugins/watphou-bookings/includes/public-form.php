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
	if ( $tour_id < 1 || 'tour' !== get_post_type( $tour_id ) ) {
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
		'code'         => (string) get_post_meta( $tour_id, 'tour_code', true ),
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
	if ( $ctx['price'] ) {
		$lines[] = 'Price: ' . $ctx['price'];
	}
	if ( $ctx['headline'] ) {
		$lines[] = 'Headline: ' . $ctx['headline'];
	}
	return implode( "\n", $lines );
}

function watphou_booking_form_shortcode( $atts ): string {
	$atts    = shortcode_atts( array( 'tour_id' => 0 ), $atts, 'watphou_booking_form' );
	$tour_id = watphou_booking_resolve_tour_id( (int) $atts['tour_id'] );
	$ctx     = watphou_booking_tour_context( $tour_id );
	$locked  = $tour_id > 0;
	$tours   = $locked ? array() : watphou_booking_published_tours();
	$privacy   = function_exists( 'watphou_page_url' ) ? watphou_page_url( 'privacy-policy' ) : home_url( '/privacy-policy/' );
	$ui_lang   = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	$form_lang = array(
		'fr' => 'fr-FR',
		'th' => 'th-TH',
	)[ $ui_lang ] ?? 'en-GB';
	ob_start();
	?>
	<form class="watphou-booking-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" lang="<?php echo esc_attr( $form_lang ); ?>">
		<input type="hidden" name="action" value="watphou_submit_booking"/>
		<?php wp_nonce_field( 'watphou_booking_submit', 'watphou_booking_nonce' ); ?>
		<fieldset class="watphou-booking-form__tour">
			<legend><?php esc_html_e( 'Your tour', 'watphou-bookings' ); ?></legend>
			<?php if ( $locked ) : ?>
				<input type="hidden" name="tour_id" value="<?php echo esc_attr( (string) $ctx['id'] ); ?>"/>
			<?php else : ?>
				<p><label><?php esc_html_e( 'Tour', 'watphou-bookings' ); ?>
					<select name="tour_id" class="watphou-booking-tour-select">
						<option value="0" data-name="" data-code="" data-duration="" data-destinations="" data-price="" data-headline="" data-departure=""><?php esc_html_e( 'Select a tour (or describe it in the message)', 'watphou-bookings' ); ?></option>
						<?php foreach ( $tours as $tour ) : ?>
							<?php
							$row = watphou_booking_tour_context( (int) $tour->ID );
							?>
							<option
								value="<?php echo esc_attr( (string) $row['id'] ); ?>"
								data-name="<?php echo esc_attr( $row['name'] ); ?>"
								data-code="<?php echo esc_attr( $row['code'] ); ?>"
								data-duration="<?php echo esc_attr( $row['duration'] ); ?>"
								data-destinations="<?php echo esc_attr( $row['destinations'] ); ?>"
								data-price="<?php echo esc_attr( $row['price'] ); ?>"
								data-headline="<?php echo esc_attr( $row['headline'] ); ?>"
								data-departure="<?php echo esc_attr( $row['departure'] ); ?>"
								<?php selected( $ctx['id'], $row['id'] ); ?>
							><?php echo esc_html( trim( ( $row['code'] ? $row['code'] . ' — ' : '' ) . $row['name'] ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label></p>
			<?php endif; ?>
			<p><label><?php esc_html_e( 'Tour name', 'watphou-bookings' ); ?> <input class="watphou-bf-name" name="tour_name" type="text" readonly value="<?php echo esc_attr( $ctx['name'] ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Package code', 'watphou-bookings' ); ?> <input class="watphou-bf-code" name="tour_code" type="text" readonly value="<?php echo esc_attr( $ctx['code'] ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Duration', 'watphou-bookings' ); ?> <input class="watphou-bf-duration" name="tour_duration" type="text" readonly value="<?php echo esc_attr( $ctx['duration'] ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Tour destinations', 'watphou-bookings' ); ?> <input class="watphou-bf-destinations" name="tour_destinations" type="text" readonly value="<?php echo esc_attr( $ctx['destinations'] ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Departure', 'watphou-bookings' ); ?> <input class="watphou-bf-departure" name="tour_departure" type="text" readonly value="<?php echo esc_attr( $ctx['departure'] ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Starting price', 'watphou-bookings' ); ?> <input class="watphou-bf-price" name="tour_price" type="text" readonly value="<?php echo esc_attr( $ctx['price'] ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Tour summary', 'watphou-bookings' ); ?> <textarea class="watphou-bf-headline" name="tour_headline" rows="2" readonly><?php echo esc_textarea( $ctx['headline'] ); ?></textarea></label></p>
		</fieldset>
		<fieldset class="watphou-booking-form__guest">
			<legend><?php esc_html_e( 'Your details', 'watphou-bookings' ); ?></legend>
			<p><label><?php esc_html_e( 'Name', 'watphou-bookings' ); ?> <span class="watphou-booking-form__req"><?php esc_html_e( '(required)', 'watphou-bookings' ); ?></span>
				<input required name="customer_name" id="wpt-bf-name" type="text" autocomplete="name" data-label="<?php esc_attr_e( 'Name', 'watphou-bookings' ); ?>"/>
				<span class="watphou-booking-form__field-error" id="wpt-bf-name-error" hidden></span>
			</label></p>
			<p><label><?php esc_html_e( 'Email', 'watphou-bookings' ); ?> <span class="watphou-booking-form__req"><?php esc_html_e( '(required)', 'watphou-bookings' ); ?></span>
				<input required name="email" id="wpt-bf-email" type="email" autocomplete="email" data-label="<?php esc_attr_e( 'Email', 'watphou-bookings' ); ?>"/>
				<span class="watphou-booking-form__field-error" id="wpt-bf-email-error" hidden></span>
			</label></p>
			<p><label><?php esc_html_e( 'Phone', 'watphou-bookings' ); ?> <input name="phone" type="tel" autocomplete="tel"/></label></p>
			<p><label><?php esc_html_e( 'WhatsApp', 'watphou-bookings' ); ?> <input name="whatsapp" type="tel"/></label></p>
			<p><label><?php esc_html_e( 'Preferred date', 'watphou-bookings' ); ?> <span class="watphou-booking-form__req"><?php esc_html_e( '(required)', 'watphou-bookings' ); ?></span>
				<input required name="preferred_date" id="wpt-bf-date" type="date" lang="<?php echo esc_attr( $form_lang ); ?>" data-label="<?php esc_attr_e( 'Preferred date', 'watphou-bookings' ); ?>"/>
				<span class="watphou-booking-form__field-error" id="wpt-bf-date-error" hidden></span>
			</label></p>
			<p><label><?php esc_html_e( 'Alternative date', 'watphou-bookings' ); ?> <input name="alt_date" type="date" lang="<?php echo esc_attr( $form_lang ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Adults', 'watphou-bookings' ); ?> <input name="adults" type="number" min="1" value="2"/></label></p>
			<p><label><?php esc_html_e( 'Children', 'watphou-bookings' ); ?> <input name="children" type="number" min="0" value="0"/></label></p>
			<p><label><?php esc_html_e( 'Country', 'watphou-bookings' ); ?> <input name="country" type="text" autocomplete="country-name"/></label></p>
			<p><label><?php esc_html_e( 'Pickup / hotel', 'watphou-bookings' ); ?> <input name="pickup_location" type="text" placeholder="<?php esc_attr_e( 'Hotel name in Pakse', 'watphou-bookings' ); ?>"/></label></p>
			<p><label><?php esc_html_e( 'Message', 'watphou-bookings' ); ?> <textarea name="customer_message" rows="4" placeholder="<?php esc_attr_e( 'Dates, pace, dietary needs, or questions', 'watphou-bookings' ); ?>"></textarea></label></p>
			<p><label>
				<input required name="privacy_consent" id="wpt-bf-privacy" type="checkbox" value="1" data-label="<?php esc_attr_e( 'Agreement to the privacy policy', 'watphou-bookings' ); ?>"/>
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
				<span class="watphou-booking-form__field-error" id="wpt-bf-privacy-error" hidden></span>
			</label></p>
			<div class="watphou-booking-form__alert" hidden role="alert"></div>
			<p><button type="submit" class="wp-block-button__link"><?php esc_html_e( 'Request a quote', 'watphou-bookings' ); ?></button></p>
			<p class="watphou-booking-form__hint"><?php esc_html_e( 'This saves your request for the Pakse office and emails them. You will see a confirmation on this page. We reply by email or WhatsApp.', 'watphou-bookings' ); ?></p>
		</fieldset>
	</form>
	<?php if ( ! $locked ) : ?>
	<script>
	(function () {
		var form = document.currentScript && document.currentScript.previousElementSibling;
		if (!form || !form.classList.contains('watphou-booking-form')) {
			form = document.querySelector('.watphou-booking-form');
		}
		if (!form) { return; }
		var sel = form.querySelector('.watphou-booking-tour-select');
		if (!sel) { return; }
		function apply() {
			var o = sel.options[sel.selectedIndex];
			if (!o) { return; }
			var map = { name: '.watphou-bf-name', code: '.watphou-bf-code', duration: '.watphou-bf-duration', destinations: '.watphou-bf-destinations', departure: '.watphou-bf-departure', price: '.watphou-bf-price', headline: '.watphou-bf-headline' };
			Object.keys(map).forEach(function (key) {
				var el = form.querySelector(map[key]);
				if (el) { el.value = o.getAttribute('data-' + key) || ''; }
			});
		}
		sel.addEventListener('change', apply);
		apply();
	})();
	</script>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}

function watphou_booking_field_errors(): array {
	$errors = array();
	$name   = sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) );
	$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$pref   = sanitize_text_field( wp_unslash( $_POST['preferred_date'] ?? '' ) );
	if ( '' === $name ) {
		$errors['customer_name'] = __( 'Please enter your name.', 'watphou-bookings' );
	}
	$raw_email = sanitize_text_field( wp_unslash( $_POST['email'] ?? '' ) );
	if ( '' === $raw_email ) {
		$errors['email'] = __( 'Please enter your email address.', 'watphou-bookings' );
	} elseif ( '' === $email || ! is_email( $email ) ) {
		$errors['email'] = __( 'Please enter a valid email address (for example name@example.com).', 'watphou-bookings' );
	}
	if ( '' === $pref ) {
		$errors['preferred_date'] = __( 'Please choose a preferred date.', 'watphou-bookings' );
	}
	if ( empty( $_POST['privacy_consent'] ) ) {
		$errors['privacy_consent'] = __( 'Please agree to the privacy policy.', 'watphou-bookings' );
	}
	return $errors;
}

function watphou_handle_booking_submit(): void {
	check_ajax_referer( 'watphou_booking_submit', 'watphou_booking_nonce' );
	$errors = watphou_booking_field_errors();
	if ( $errors ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please complete these required fields:', 'watphou-bookings' ),
				'errors'  => $errors,
			)
		);
	}
	$tour_id = (int) ( $_POST['tour_id'] ?? 0 );
	$ctx     = watphou_booking_tour_context( $tour_id );
	$name    = sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$wa      = sanitize_text_field( wp_unslash( $_POST['whatsapp'] ?? '' ) );
	$pref    = sanitize_text_field( wp_unslash( $_POST['preferred_date'] ?? '' ) );
	$alt     = sanitize_text_field( wp_unslash( $_POST['alt_date'] ?? '' ) );
	$adults  = max( 1, (int) ( $_POST['adults'] ?? 1 ) );
	$kids    = max( 0, (int) ( $_POST['children'] ?? 0 ) );
	$country = sanitize_text_field( wp_unslash( $_POST['country'] ?? '' ) );
	$pickup  = sanitize_textarea_field( wp_unslash( $_POST['pickup_location'] ?? '' ) );
	$msg     = sanitize_textarea_field( wp_unslash( $_POST['customer_message'] ?? '' ) );
	$snap    = watphou_booking_snapshot_text( $ctx );
	$id      = Watphou_Booking_Repository::create(
		array(
			'tour_id'          => $tour_id,
			'tour_name'        => $ctx['name'] ?: sanitize_text_field( wp_unslash( $_POST['tour_name'] ?? '' ) ),
			'customer_name'    => $name,
			'email'            => $email,
			'phone'            => $phone,
			'whatsapp'         => $wa,
			'preferred_date'   => $pref,
			'alt_date'         => $alt,
			'adults'           => $adults,
			'children'         => $kids,
			'country'          => $country,
			'pickup_location'  => $pickup,
			'customer_message' => $msg,
			'staff_notes'      => $snap,
			'privacy_consent'  => 1,
			'language'         => function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : ( function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en' ),
		)
	);
	$body = implode(
		"\n",
		array_filter(
			array(
				'New booking #' . $id,
				$snap,
				'Name: ' . $name,
				'Email: ' . $email,
				'Phone: ' . $phone,
				'WhatsApp: ' . $wa,
				'Preferred date: ' . $pref,
				'Alternative date: ' . $alt,
				'Adults: ' . $adults,
				'Children: ' . $kids,
				'Country: ' . $country,
				'Pickup / hotel: ' . $pickup,
				'Message: ' . $msg,
			)
		)
	);
	wp_mail(
		get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ),
		'New booking request' . ( $ctx['name'] ? ': ' . $ctx['name'] : '' ),
		$body
	);
	wp_send_json_success(
		array(
			'booking_id' => $id,
			'message'    => __( 'Thank you. We received your tour request. The Pakse office will reply by email or WhatsApp.', 'watphou-bookings' ),
		)
	);
}
