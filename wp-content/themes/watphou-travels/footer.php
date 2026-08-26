<?php
defined( 'ABSPATH' ) || exit;
?>
<footer class="wpt-footer">
	<div class="wpt-container wpt-footer__grid">
		<div>
			<img class="wpt-footer__logo" src="<?php echo esc_url( WATPHOU_THEME_URI . '/assets/images/logo-white.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="160">
			<p><?php esc_html_e( 'Your local expert in Southern Laos — private tours with European standards.', 'watphou-travels' ); ?></p>
		</div>
		<div>
			<h3><?php esc_html_e( 'Contact', 'watphou-travels' ); ?></h3>
			<p>Phone/WhatsApp: <a href="<?php echo esc_url( function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858' ); ?>">+85620 9949 5858</a></p>
			<p>Email: <a href="mailto:sales.watphoutravel@gmail.com">sales.watphoutravel@gmail.com</a></p>
			<p>Street N°5, Ban Vat Luang, Pakse, Laos</p>
		</div>
		<div>
			<h3><?php esc_html_e( 'Explore', 'watphou-travels' ); ?></h3>
			<ul>
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'tour' ) ); ?>"><?php esc_html_e( 'All tours', 'watphou-travels' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/tailor-made-tours/' ) ); ?>"><?php esc_html_e( 'Tailor-made tours', 'watphou-travels' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'About us', 'watphou-travels' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Contact', 'watphou-travels' ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="wpt-footer__copy">
		<div class="wpt-container">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Watphou Travels</div>
	</div>
</footer>

<a class="watphou-whatsapp-float" href="<?php echo esc_url( function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858' ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'WhatsApp us', 'watphou-travels' ); ?>">
	<img src="<?php echo esc_url( WATPHOU_THEME_URI . '/assets/images/whatsapp.svg' ); ?>" width="32" height="32" alt="">
</a>
<?php wp_footer(); ?>
</body>
</html>
