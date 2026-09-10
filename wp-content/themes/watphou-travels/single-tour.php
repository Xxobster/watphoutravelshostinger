<?php
defined( 'ABSPATH' ) || exit;
get_header();
$img   = WATPHOU_THEME_URI . '/assets/images';
$pid   = get_the_ID();
$thumb = get_the_post_thumbnail_url( $pid, 'tour-hero' );
if ( ! $thumb ) {
	$thumb = $img . '/bolaven.jpg';
}
$duration = get_post_meta( $pid, 'tour_duration', true );
?>
<article class="wpt-tour-single">
	<section class="wpt-hero wpt-hero--tour" style="background-image:url('<?php echo esc_url( $thumb ); ?>')">
		<div class="wpt-hero__overlay"></div>
		<div class="wpt-container wpt-hero__content">
			<h1><?php the_title(); ?></h1>
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
		the_content();
		$dest_ids = wp_get_post_terms( $pid, 'destination', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $dest_ids ) && $dest_ids ) {
			$related = new WP_Query(
				array(
					'post_type'      => 'tour',
					'posts_per_page' => 3,
					'post__not_in'   => array( $pid ),
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
					echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
				}
				echo '</ul></section>';
				wp_reset_postdata();
			}
		}
		?>
	</div>
	<div class="wpt-container" id="booking">
		<h2><?php esc_html_e( 'Request this tour', 'watphou-travels' ); ?></h2>
		<?php echo do_shortcode( '[watphou_booking_form tour_id="' . (int) $pid . '"]' ); ?>
	</div>
</article>
<?php
get_footer();
