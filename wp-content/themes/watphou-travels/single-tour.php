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
?>
<article class="wpt-tour-single">
	<section class="wpt-hero wpt-hero--tour" style="background-image:url('<?php echo esc_url( $thumb ); ?>')">
		<div class="wpt-hero__overlay"></div>
		<div class="wpt-container wpt-hero__content">
			<h1><?php echo esc_html( get_the_title( $pid ) ); ?></h1>
			<?php if ( $duration ) : ?>
				<p class="wpt-hero__sub"><?php echo esc_html( $duration ); ?></p>
			<?php endif; ?>
			<p class="wpt-price-bar"><?php echo esc_html( function_exists( 'watphou_get_tour_price_display' ) ? watphou_get_tour_price_display( $pid ) : '' ); ?></p>
			<a class="wpt-btn-primary" href="#booking"><?php esc_html_e( 'Request this tour', 'watphou-travels' ); ?></a>
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
					'<figure><img src="%1$s" alt="%2$s" loading="lazy" decoding="async"></figure>',
					esc_url( $shot['url'] ),
					esc_attr( $shot['alt'] )
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
		<h2><?php esc_html_e( 'Request this tour', 'watphou-travels' ); ?></h2>
		<p class="wpt-tour-booking__note"><?php esc_html_e( 'Tour details are filled in. Add your dates and contact details.', 'watphou-travels' ); ?></p>
		<?php echo do_shortcode( '[watphou_booking_form tour_id="' . $pid . '"]' ); ?>
	</div>
</article>
<?php
get_footer();
