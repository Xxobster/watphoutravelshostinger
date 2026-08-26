<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="wpt-main wpt-container">
	<h1><?php esc_html_e( 'Page not found', 'watphou-travels' ); ?></h1>
	<p><?php esc_html_e( 'Sorry, we could not find that page.', 'watphou-travels' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return home', 'watphou-travels' ); ?></a></p>
</main>
<?php
get_footer();
