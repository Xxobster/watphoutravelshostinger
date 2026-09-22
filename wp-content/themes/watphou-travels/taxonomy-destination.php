<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="wpt-main">
	<section class="wpt-archive-hero">
		<div class="wpt-container">
			<?php
			if ( function_exists( 'watphou_core_breadcrumbs' ) ) {
				watphou_core_breadcrumbs();
			}
			?>
			<h1><?php
			$term_title = single_term_title( '', false );
			if ( function_exists( 'watphou_core_localize_destination_list' ) ) {
				$term_title = watphou_core_localize_destination_list( (string) $term_title );
			}
			echo esc_html( $term_title );
			?></h1>
			<?php the_archive_description( '<p>', '</p>' ); ?>
		</div>
	</section>
	<div class="wpt-container wpt-adventures__list">
		<?php
		$posts = $GLOBALS['wp_query']->posts ?? array();
		$term  = get_queried_object();
		$slug  = ( $term && isset( $term->slug ) ) ? (string) $term->slug : '';
		if ( function_exists( 'pll_get_term' ) && $term && isset( $term->term_id ) ) {
			$en = pll_get_term( (int) $term->term_id, 'en' );
			if ( $en ) {
				$en_term = get_term( (int) $en, 'destination' );
				if ( $en_term && ! is_wp_error( $en_term ) ) {
					$slug = (string) $en_term->slug;
				}
			}
		}
		if ( function_exists( 'watphou_core_sort_tours_for_destination' ) && $posts && $slug ) {
			$posts = watphou_core_sort_tours_for_destination( $posts, $slug );
		} elseif ( function_exists( 'watphou_core_sort_tours_by_catalog' ) && $posts ) {
			$posts = watphou_core_sort_tours_by_catalog( $posts );
		}
		if ( $posts ) {
			foreach ( $posts as $i => $tour ) {
				watphou_render_tour_row( $tour, (int) $i );
			}
		} else {
			echo '<p>' . esc_html__( 'No tours for this destination yet.', 'watphou-travels' ) . '</p>';
		}
		if ( 'pakse' === $slug ) {
			$tailor = function_exists( 'watphou_page_url' ) ? watphou_page_url( 'tailor-made-tours' ) : home_url( '/tailor-made-tours/' );
			echo '<p class="wpt-listing-note"><a href="' . esc_url( $tailor ) . '">' . esc_html__( 'Tailor-made tours from Pakse (T.1)', 'watphou-travels' ) . '</a></p>';
		}
		?>
	</div>
</main>
<?php
get_footer();
