<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="wpt-main wpt-container">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_title( '<h1>', '</h1>' );
			the_content();
		}
	} else {
		echo '<p>' . esc_html__( 'No content found.', 'watphou-travels' ) . '</p>';
	}
	?>
</main>
<?php
get_footer();
