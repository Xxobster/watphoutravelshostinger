<?php
defined( 'ABSPATH' ) || exit;
get_header();
$img = WATPHOU_THEME_URI . '/assets/images';
$pid = (int) get_queried_object_id();
if ( $pid && function_exists( 'pll_get_post' ) && function_exists( 'watphou_core_current_lang_slug' ) ) {
	$want = (int) pll_get_post( $pid, watphou_core_current_lang_slug() );
	if ( $want ) {
		$pid = $want;
	}
}
$tour_post = get_post( $pid );
if ( $tour_post instanceof WP_Post ) {
	$GLOBALS['post'] = $tour_post;
	setup_postdata( $tour_post );
}
$thumb = function_exists( 'watphou_core_tour_featured_url' )
	? watphou_core_tour_featured_url( $pid, 'tour-hero' )
	: get_the_post_thumbnail_url( $pid, 'tour-hero' );
if ( ! $thumb ) {
	$thumb = $img . '/bolaven.jpg';
}
$duration = get_post_meta( $pid, 'tour_duration', true );
if ( $duration && function_exists( 'watphou_core_translate_public_string' ) ) {
	$duration = watphou_core_translate_public_string( (string) $duration );
}
$gallery  = function_exists( 'watphou_core_tour_gallery_urls' ) ? watphou_core_tour_gallery_urls( $pid ) : array();
$thumb_id = (int) get_post_thumbnail_id( $pid );

// "Send Inquiry" on the price table opens this same tour URL with ?inquiry=1: a page with only the form.
if ( function_exists( 'watphou_core_is_inquiry_page' ) && watphou_core_is_inquiry_page() ) {
	$group   = function_exists( 'watphou_booking_requested_group' ) ? watphou_booking_requested_group() : '';
	$pdata   = function_exists( 'watphou_core_price_table_public_data' ) ? watphou_core_price_table_public_data( $pid ) : array( 'day' => true, 'max' => 8, 'rows' => array() );
	// The row the visitor clicked fills the summary until they type a number of people.
	$chosen = null;
	if ( 'more' === $group ) {
		$chosen = array( 'label' => ( (int) $pdata['max'] + 1 ) . '+', 'a' => '', 'b' => '' );
	} elseif ( '' !== $group && isset( $pdata['rows'][ (int) $group ] ) ) {
		$chosen = $pdata['rows'][ (int) $group ];
	}
	$pdata['chosen'] = $chosen;
	$on_request      = __( 'On request', 'watphou-core' );
	// Starting count, as the form fills it in: largest size of the clicked line (3-5 → 5),
	// 9+ on tours with nights starts at 9, and everyone starts in Sharing Twin/Double Room.
	$start = 0;
	if ( 'more' === $group ) {
		$start = $pdata['day'] ? 0 : (int) $pdata['max'] + 1;
	} elseif ( isset( $chosen['max'] ) ) {
		$start = (int) $chosen['max'];
	} elseif ( ! $pdata['day'] ) {
		$start = 2;
	}
	$table = function_exists( 'watphou_core_get_price_table' ) ? watphou_core_get_price_table( $pid ) : array( 'rows' => array() );
	$quote = function_exists( 'watphou_core_price_quote' ) ? watphou_core_price_quote( $table, (bool) $pdata['day'], $start, 0 ) : array( 'row' => -1, 'lines' => array(), 'total' => '' );
	$usd   = static function ( string $v ): string {
		return function_exists( 'watphou_core_format_usd' ) ? watphou_core_format_usd( $v ) : $v;
	};
	$count = static function ( int $n ): string {
		/* translators: %d: number of people */
		return 1 === $n ? __( '1 person', 'watphou-core' ) : sprintf( __( '%d people', 'watphou-core' ), $n );
	};
	$line_a = $quote['lines'][0] ?? null;
	$total  = '—';
	if ( $start > 0 ) {
		$total = $quote['row'] < 0 ? __( 'On request – we will send you a quotation', 'watphou-core' ) : ( '' !== $quote['total'] ? $usd( $quote['total'] ) : $on_request );
	}
	$back    = function_exists( 'watphou_localized_permalink' ) ? watphou_localized_permalink( $pid ) : get_permalink( $pid );
	$home    = function_exists( 'watphou_page_url' ) ? watphou_page_url( '/' ) : home_url( '/' );
	?>
	<div class="wpt-container wpt-inquiry">
		<nav class="wpt-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'watphou-travels' ); ?>">
			<a href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'Home', 'watphou-travels' ); ?></a>
			<span aria-hidden="true">»</span>
			<a href="<?php echo esc_url( $back ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a>
			<span aria-hidden="true">»</span>
			<span><?php esc_html_e( 'Contact details', 'watphou-bookings' ); ?></span>
		</nav>
		<div class="wpt-inquiry__grid">
			<div class="wpt-inquiry__form">
				<?php echo do_shortcode( '[watphou_booking_form tour_id="' . (int) $pid . '" group="' . esc_attr( $group ) . '"]' ); ?>
			</div>
			<aside class="wpt-inquiry-summary" data-prices="<?php echo esc_attr( wp_json_encode( $pdata ) ); ?>">
				<h2 class="wpt-inquiry-summary__head"><?php esc_html_e( 'Booking summary', 'watphou-travels' ); ?></h2>
				<div class="wpt-inquiry-summary__body">
					<p class="wpt-inquiry-summary__tour"><?php echo esc_html( get_the_title( $pid ) ); ?></p>
					<?php if ( $duration ) : ?>
						<p class="wpt-inquiry-summary__dur"><?php echo esc_html( $duration ); ?></p>
					<?php endif; ?>
					<dl>
						<div><dt><?php esc_html_e( 'Number of people', 'watphou-bookings' ); ?></dt><dd data-summary="people"><?php echo esc_html( $start > 0 ? (string) $start : '—' ); ?></dd></div>
						<?php if ( $pdata['day'] ) : ?>
							<div><dt><?php esc_html_e( 'Price per person', 'watphou-travels' ); ?></dt><dd data-summary="unit"><?php echo esc_html( $start > 0 ? ( $line_a && '' !== $line_a['unit'] ? $usd( $line_a['unit'] ) : $on_request ) : '—' ); ?></dd></div>
						<?php else : ?>
							<?php
							// Only room types with people in them are shown.
							foreach ( array( 'a' => __( 'Sharing Twin/Double Room', 'watphou-core' ), 'b' => __( 'Private Single Room', 'watphou-core' ) ) as $key => $room_name ) :
								$line = ( 'a' === $key && $line_a ) ? $line_a : null;
								$sub  = $line ? $count( (int) $line['people'] ) . ( '' !== $line['unit'] ? ' × ' . $usd( $line['unit'] ) : '' ) : '';
								?>
								<div data-summary="room-<?php echo esc_attr( $key ); ?>"<?php echo $line ? '' : ' hidden'; ?>>
									<dt><?php echo esc_html( $room_name ); ?><small data-summary="room-<?php echo esc_attr( $key ); ?>-sub"><?php echo esc_html( $sub ); ?></small></dt>
									<dd data-summary="room-<?php echo esc_attr( $key ); ?>-amount"><?php echo esc_html( $line && '' !== $line['amount'] ? $usd( $line['amount'] ) : $on_request ); ?></dd>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
						<div class="wpt-inquiry-summary__total"><dt><?php esc_html_e( 'Total price', 'watphou-core' ); ?></dt><dd data-summary="total"><?php echo esc_html( $total ); ?></dd></div>
					</dl>
					<p class="wpt-inquiry-summary__note"><?php esc_html_e( 'Prices in USD. Our team confirms availability and the final price by email or WhatsApp.', 'watphou-travels' ); ?></p>
				</div>
			</aside>
		</div>
	</div>
	<?php
	get_footer();
	return;
}
?>
<article class="wpt-tour-single">
	<section class="wpt-hero wpt-hero--tour">
		<div class="wpt-hero__slides">
			<?php
			if ( $thumb_id ) {
				echo wp_get_attachment_image(
					$thumb_id,
					'tour-hero',
					false,
					array(
						'class'         => 'wpt-hero__slide is-active',
						'fetchpriority' => 'high',
						'loading'       => 'eager',
						'decoding'      => 'async',
						'sizes'         => '100vw',
						'alt'           => get_the_title( $pid ),
					)
				);
			} elseif ( $thumb ) {
				printf(
					'<img class="wpt-hero__slide is-active" src="%1$s" alt="%2$s" fetchpriority="high" decoding="async" width="1600" height="900">',
					esc_url( $thumb ),
					esc_attr( get_the_title( $pid ) )
				);
			}
			?>
		</div>
		<div class="wpt-hero__overlay"></div>
		<div class="wpt-container wpt-hero__content">
			<h1><?php echo esc_html( get_the_title( $pid ) ); ?></h1>
			<?php if ( $duration ) : ?>
				<p class="wpt-hero__sub"><?php echo esc_html( $duration ); ?></p>
			<?php endif; ?>
			<p class="wpt-price-bar"><?php echo esc_html( function_exists( 'watphou_get_tour_price_display' ) ? watphou_get_tour_price_display( $pid ) : '' ); ?></p>
			<a class="wpt-btn-primary" href="#prices"><?php esc_html_e( 'View prices', 'watphou-travels' ); ?></a>
		</div>
	</section>
	<div class="wpt-container wpt-prose">
		<?php
		if ( function_exists( 'watphou_core_breadcrumbs' ) ) {
			watphou_core_breadcrumbs();
		}
		if ( $tour_post instanceof WP_Post ) {
			echo apply_filters( 'the_content', $tour_post->post_content );
		} else {
			the_content();
		}
		if ( $gallery ) {
			echo '<section class="wpt-tour-gallery" aria-label="' . esc_attr__( 'Tour photos', 'watphou-travels' ) . '">';
			foreach ( $gallery as $shot ) {
				printf(
					'<figure><img src="%1$s" data-full="%3$s" alt="%2$s" loading="lazy" decoding="async"></figure>',
					esc_url( $shot['url'] ),
					esc_attr( $shot['alt'] ),
					esc_url( $shot['full'] ?? $shot['url'] )
				);
			}
			echo '</section>';
		}
		$dest_ids = wp_get_post_terms( $pid, 'destination', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $dest_ids ) && $dest_ids ) {
			$related = new WP_Query(
				array(
					'post_type'      => 'tour',
					'posts_per_page' => 3,
					'post__not_in'   => array( $pid ),
					'lang'           => function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : '',
					'tax_query'      => array(
						array(
							'taxonomy' => 'destination',
							'field'    => 'term_id',
							'terms'    => $dest_ids,
						),
					),
				)
			);
			if ( $related->have_posts() ) {
				echo '<section class="wpt-related"><h2>' . esc_html__( 'Related tours', 'watphou-travels' ) . '</h2><ul>';
				while ( $related->have_posts() ) {
					$related->the_post();
					echo '<li><a href="' . esc_url( function_exists( 'watphou_localized_permalink' ) ? watphou_localized_permalink() : get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
				}
				echo '</ul></section>';
				wp_reset_postdata();
			}
		}
		?>
	</div>
	<div class="wpt-container wpt-tour-booking" id="booking">
		<?php echo function_exists( 'watphou_core_render_price_table' ) ? watphou_core_render_price_table( $pid ) : ''; // Escaped inside. ?>
	</div>
</article>
<?php
get_footer();
