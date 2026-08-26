<?php
defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="wpt-header">
	<div class="wpt-topbar">
		<div class="wpt-container wpt-topbar__inner">
			<div class="wpt-topbar__left">
				<a class="wpt-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( WATPHOU_THEME_URI . '/assets/images/logo-wpt.jpg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="140" height="40">
				</a>
				<div class="wpt-location">
					<span class="wpt-location__pin" aria-hidden="true">📍</span>
					<span><?php esc_html_e( 'Southern Laos', 'watphou-travels' ); ?></span>
				</div>
			</div>
			<div class="wpt-topbar__right">
				<form class="wpt-search d-desktop" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
					<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'watphou-travels' ); ?>">⌕</button>
					<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search', 'watphou-travels' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
				</form>
				<a class="wpt-whatsapp-phone" href="<?php echo esc_url( function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858' ); ?>">
					<img class="wpt-wa-icon" src="<?php echo esc_url( WATPHOU_THEME_URI . '/assets/images/whatsapp.svg' ); ?>" width="24" height="24" alt="">
					<strong>+85620 9949 5858</strong>
				</a>
				<nav class="wpt-langs" aria-label="<?php esc_attr_e( 'Language', 'watphou-travels' ); ?>">
					<?php
					if ( function_exists( 'pll_the_languages' ) ) {
						pll_the_languages( array( 'show_flags' => 0, 'show_names' => 1, 'display_names_as' => 'slug' ) );
					} else {
						echo '<a href="' . esc_url( home_url( '/en/' ) ) . '">EN</a> <a href="' . esc_url( home_url( '/fr/' ) ) . '">FR</a> <a href="' . esc_url( home_url( '/th/' ) ) . '">TH</a>';
					}
					?>
				</nav>
				<?php if ( is_user_logged_in() ) : ?>
					<a class="wpt-login-link" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Edit website', 'watphou-travels' ); ?></a>
				<?php else : ?>
					<a class="wpt-login-link" href="<?php echo esc_url( wp_login_url( admin_url() ) ); ?>"><?php esc_html_e( 'Log in to edit', 'watphou-travels' ); ?></a>
				<?php endif; ?>
				<button class="wpt-nav-toggle" type="button" aria-expanded="false" aria-controls="wpt-nav" aria-label="<?php esc_attr_e( 'Open menu', 'watphou-travels' ); ?>">☰</button>
			</div>
		</div>
	</div>

	<nav class="wpt-nav" id="wpt-nav" aria-label="<?php esc_attr_e( 'Primary', 'watphou-travels' ); ?>">
		<div class="wpt-container wpt-nav__inner">
			<ul class="wpt-menu">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'watphou-travels' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'tour' ) ); ?>"><?php esc_html_e( 'Day Tours', 'watphou-travels' ); ?></a></li>
				<li class="has-children">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'tour' ) ); ?>"><?php esc_html_e( 'Tour Packages', 'watphou-travels' ); ?> <span>▾</span></a>
					<ul class="wpt-dropdown">
						<li><a href="<?php echo esc_url( home_url( '/tours/bolaven-plateau-classic-full-day-tour/' ) ); ?>">Bolaven Plateau Classic Full-Day</a></li>
						<li><a href="<?php echo esc_url( home_url( '/tours/bolaven-plateau-classic-2-day/' ) ); ?>">Bolaven Plateau 2 Days 1 Night</a></li>
						<li><a href="<?php echo esc_url( home_url( '/tours/4000islands-vatphu-temple-2-day-1-night/' ) ); ?>">4000 Islands &amp; Vat Phou 2D1N</a></li>
						<li><a href="<?php echo esc_url( home_url( '/tours/3-day-classic-experience-in-southern-laos/' ) ); ?>">3-Day Classic Experience</a></li>
						<li><a href="<?php echo esc_url( home_url( '/tours/4-day-southern-laos-escape/' ) ); ?>">4-Day Southern Laos Escape</a></li>
						<li><a href="<?php echo esc_url( home_url( '/tours/5-day-exploring-southern-laos/' ) ); ?>">5-Day Exploring Southern Laos</a></li>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'tour' ) ); ?>"><?php esc_html_e( 'View all tours', 'watphou-travels' ); ?></a></li>
					</ul>
				</li>
				<li class="has-children">
					<a href="<?php echo esc_url( home_url( '/destinations/' ) ); ?>"><?php esc_html_e( 'Destinations', 'watphou-travels' ); ?> <span>▾</span></a>
					<ul class="wpt-dropdown">
						<li><a href="<?php echo esc_url( home_url( '/destinations/bolaven-plateau/' ) ); ?>">Bolaven Plateau</a></li>
						<li><a href="<?php echo esc_url( home_url( '/destinations/4000-islands/' ) ); ?>">4000 Islands (Si Phan Don)</a></li>
						<li><a href="<?php echo esc_url( home_url( '/destinations/champasak/' ) ); ?>">Vat Phou &amp; Champasak</a></li>
						<li><a href="<?php echo esc_url( home_url( '/destinations/pakse/' ) ); ?>">Pakse &amp; Surroundings</a></li>
					</ul>
				</li>
				<li><a href="<?php echo esc_url( home_url( '/tailor-made-tours/' ) ); ?>"><?php esc_html_e( 'Tailor-made', 'watphou-travels' ); ?></a></li>
				<li class="has-children">
					<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'About Us', 'watphou-travels' ); ?> <span>▾</span></a>
					<ul class="wpt-dropdown">
						<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'About us', 'watphou-travels' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/#why-us' ) ); ?>"><?php esc_html_e( 'Why travel with us', 'watphou-travels' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/#reviews' ) ); ?>"><?php esc_html_e( 'Reviews', 'watphou-travels' ); ?></a></li>
					</ul>
				</li>
				<li><a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Contact', 'watphou-travels' ); ?></a></li>
			</ul>
			<a class="wpt-cta-outline" href="<?php echo esc_url( home_url( '/booking-request/' ) ); ?>"><?php esc_html_e( 'Customize trip', 'watphou-travels' ); ?></a>
		</div>
	</nav>
</header>
