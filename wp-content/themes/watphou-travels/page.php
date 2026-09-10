<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="wpt-main wpt-container">
	<?php
	if ( function_exists( 'watphou_core_breadcrumbs' ) ) {
		watphou_core_breadcrumbs();
	}
	while ( have_posts() ) {
		the_post();
		the_title( '<h1 class="wpt-page-title">', '</h1>' );
		echo '<div class="wpt-prose">';
		the_content();
		echo '</div>';
	}
	?>
</main>
<?php
get_footer();
