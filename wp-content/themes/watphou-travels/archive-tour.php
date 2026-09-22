<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="wpt-main">
	<section class="wpt-archive-hero">
		<div class="wpt-container">
			<h1><?php post_type_archive_title(); ?></h1>
			<p><?php esc_html_e( 'Private tours across Southern Laos — from one day to one week.', 'watphou-travels' ); ?></p>
		</div>
	</section>
	<div class="wpt-container wpt-adventures__list">
		<?php
		$posts = $GLOBALS['wp_query']->posts ?? array();
		if ( function_exists( 'watphou_core_sort_tours_by_catalog' ) && $posts ) {
			$posts = watphou_core_sort_tours_by_catalog( $posts );
		}
		if ( $posts ) {
			foreach ( $posts as $i => $tour ) {
				watphou_render_tour_row( $tour, (int) $i );
			}
		}
		?>
	</div>
</main>
<?php
get_footer();
