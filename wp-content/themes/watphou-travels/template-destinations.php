<?php
defined( 'ABSPATH' ) || exit;
get_header();

$img  = WATPHOU_THEME_URI . '/assets/images';
$hubs = array(
	array(
		'slug'  => 'bolaven-plateau',
		'title' => __( 'Bolaven Plateau', 'watphou-travels' ),
		'img'   => 'bolaven.jpg',
		'text'  => __( 'Waterfalls, coffee farms, and cool highland villages.', 'watphou-travels' ),
	),
	array(
		'slug'  => '4000-islands',
		'title' => __( '4000 Islands (Si Phan Don)', 'watphou-travels' ),
		'img'   => 'liphi.jpg',
		'text'  => __( 'Si Phan Don: islands, waterfalls, and slow river life.', 'watphou-travels' ),
	),
	array(
		'slug'  => 'champasak',
		'title' => __( 'Vat Phou & Champasak', 'watphou-travels' ),
		'img'   => 'vatphou.jpg',
		'text'  => __( 'Vat Phou temple and the historic Mekong towns.', 'watphou-travels' ),
	),
	array(
		'slug'  => 'pakse',
		'title' => __( 'Pakse & Surroundings', 'watphou-travels' ),
		'img'   => 'donkhone.jpg',
		'text'  => __( 'The riverside hub where private journeys begin.', 'watphou-travels' ),
	),
);
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
			<p><?php echo esc_html( get_the_excerpt() ?: __( 'Bolaven Plateau, 4000 Islands, Vat Phou & Champasak, Pakse & Surroundings.', 'watphou-travels' ) ); ?></p>
		</div>
	</section>
	<div class="wpt-container wpt-dest-hub">
		<?php foreach ( $hubs as $hub ) : ?>
			<?php
			$url = function_exists( 'watphou_term_url' )
				? watphou_term_url( 'destination', $hub['slug'] )
				: home_url( '/destinations/' . $hub['slug'] . '/' );
			?>
			<article class="wpt-dest-card">
				<a href="<?php echo esc_url( $url ); ?>">
					<img src="<?php echo esc_url( $img . '/' . $hub['img'] ); ?>" alt="<?php echo esc_attr( $hub['title'] ); ?>" loading="lazy" decoding="async">
					<div class="wpt-dest-card__body">
						<h2><?php echo esc_html( $hub['title'] ); ?></h2>
						<p><?php echo esc_html( $hub['text'] ); ?></p>
						<span><?php esc_html_e( 'View tours', 'watphou-travels' ); ?></span>
					</div>
				</a>
			</article>
		<?php endforeach; ?>
	</div>
</main>
<?php
get_footer();
