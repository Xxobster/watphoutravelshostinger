<?php
defined( 'ABSPATH' ) || exit;
get_header();

$duration_slug = (string) ( $GLOBALS['watphou_listing_duration'] ?? '1-day' );
$tours         = function_exists( 'watphou_get_tours_for_duration' ) ? watphou_get_tours_for_duration( $duration_slug ) : array();
$intro         = get_the_excerpt() ?: wp_strip_all_tags( get_the_content() );
$fallbacks     = array(
	'1-day'   => __( 'Private full-day journeys from Pakse.', 'watphou-travels' ),
	'2-day'   => __( 'Two-day private journeys (one night) from Pakse.', 'watphou-travels' ),
	'3-day'   => __( 'Three-day private journeys (two nights) from Pakse.', 'watphou-travels' ),
	'4-6-day' => __( 'Four- to six-day private journeys across Southern Laos.', 'watphou-travels' ),
);
if ( '' === trim( (string) $intro ) && isset( $fallbacks[ $duration_slug ] ) ) {
	$intro = $fallbacks[ $duration_slug ];
}
?>
<main class="wpt-main">
	<section class="wpt-archive-hero">
		<div class="wpt-container">
			<?php
			if ( function_exists( 'watphou_core_breadcrumbs' ) ) {
				watphou_core_breadcrumbs();
			}
			?>
			<h1><?php the_title(); ?></h1>
			<?php if ( $intro ) : ?>
				<p><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<div class="wpt-container wpt-adventures__list">
		<?php
		if ( '4-6-day' === $duration_slug && $tours && function_exists( 'watphou_core_catalog_code_map' ) ) {
			$code_map = watphou_core_catalog_code_map();
			$groups   = array(
				'4.1' => __( '4-DAY TOURS (3 nights)', 'watphou-travels' ),
				'5.1' => __( '5-DAY TOURS (4 nights)', 'watphou-travels' ),
				'6.1' => __( '6-DAY TOURS (5 nights)', 'watphou-travels' ),
			);
			$i = 0;
			foreach ( $tours as $tour ) {
				$slug = function_exists( 'watphou_core_tour_catalog_slug' ) ? watphou_core_tour_catalog_slug( $tour ) : $tour->post_name;
				$code = $code_map[ $slug ] ?? '';
				if ( isset( $groups[ $code ] ) ) {
					echo '<h2 class="wpt-listing-group">' . esc_html( $groups[ $code ] ) . '</h2>';
					unset( $groups[ $code ] );
				}
				watphou_render_tour_row( $tour, $i );
				++$i;
			}
		} elseif ( $tours ) {
			foreach ( $tours as $i => $tour ) {
				watphou_render_tour_row( $tour, (int) $i );
			}
		} else {
			echo '<p>' . esc_html__( 'No tours in this category yet. They appear here as soon as they are published.', 'watphou-travels' ) . '</p>';
		}
		?>
	</div>
</main>
<?php
get_footer();
