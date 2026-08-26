<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="wpt-main wpt-container">
	<h1><?php esc_html_e( 'Search', 'watphou-travels' ); ?></h1>
	<?php get_search_form(); ?>
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			echo '<h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
			the_excerpt();
		}
	} else {
		echo '<p>' . esc_html__( 'No results.', 'watphou-travels' ) . '</p>';
	}
	?>
</main>
<?php
get_footer();
