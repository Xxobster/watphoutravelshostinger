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
		<?php the_content(); ?>
	</div>
	<div class="wpt-container" id="booking">
		<h2><?php esc_html_e( 'Request this tour', 'watphou-travels' ); ?></h2>
		<?php echo do_shortcode( '[watphou_booking_form tour_id="' . (int) $pid . '"]' ); ?>
	</div>
</article>
<?php
get_footer();
