<?php
/**
 * Guide / FAQ — practical guide for visitors (EN / FR / TH).
 * Layout follows the PDF "Practical Guide to Southern Laos": title block, four numbered sections,
 * season table, tips, packing list and FAQ. Copy: watphou-core data/guide_content.json.
 */
defined( 'ABSPATH' ) || exit;

get_header();

$content  = function_exists( 'watphou_core_guide_content' ) ? watphou_core_guide_content() : array();
$html     = static function ( string $text ): string {
	return function_exists( 'watphou_core_guide_html' ) ? watphou_core_guide_html( $text ) : esc_html( $text );
};
$hero     = (array) ( $content['hero'] ?? array() );
$sections = (array) ( $content['sections'] ?? array() );
$wa_url   = function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858';
$quote    = function_exists( 'watphou_page_url' ) ? watphou_page_url( 'book-online' ) : home_url( '/book-online/' );
$schema   = ( $content && function_exists( 'watphou_core_guide_faq_schema' ) ) ? watphou_core_guide_faq_schema( $content ) : '';
?>
<main class="wpt-main">
	<article class="wpt-guide">
		<div class="wpt-container">
			<?php
			if ( function_exists( 'watphou_core_breadcrumbs' ) ) {
				watphou_core_breadcrumbs();
			}
			?>

			<header class="wpt-guide__hero">
				<h1><?php echo esc_html( (string) ( $hero['title'] ?? get_the_title() ) ); ?></h1>
				<p class="wpt-guide__subtitle"><?php echo esc_html( (string) ( $hero['subtitle'] ?? '' ) ); ?></p>
				<p class="wpt-guide__tagline"><?php echo esc_html( (string) ( $hero['tagline'] ?? '' ) ); ?></p>
				<p class="wpt-guide__note"><?php echo esc_html( (string) ( $hero['note'] ?? '' ) ); ?></p>
			</header>

			<nav class="wpt-guide__toc" aria-label="<?php echo esc_attr( (string) ( $content['toc'] ?? 'Guide' ) ); ?>">
				<span class="wpt-guide__toc-label"><?php echo esc_html( (string) ( $content['toc'] ?? '' ) ); ?></span>
				<ol>
					<?php foreach ( $sections as $section ) : ?>
						<li><a href="#<?php echo esc_attr( (string) ( $section['id'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $section['title'] ?? '' ) ); ?></a></li>
					<?php endforeach; ?>
				</ol>
			</nav>

			<?php
			foreach ( $sections as $i => $section ) :
				$sid = (string) ( $section['id'] ?? '' );
				?>
				<section class="wpt-guide__section" id="<?php echo esc_attr( $sid ); ?>" aria-labelledby="<?php echo esc_attr( $sid ); ?>-h">
					<h2 class="wpt-guide__banner" id="<?php echo esc_attr( $sid ); ?>-h">
						<span class="wpt-guide__num" aria-hidden="true"><?php echo (int) ( $i + 1 ); ?></span>
						<?php echo esc_html( (string) ( $section['title'] ?? '' ) ); ?>
					</h2>
					<?php
					foreach ( (array) ( $section['blocks'] ?? array() ) as $block ) {
						$type = (string) ( $block['t'] ?? '' );
						if ( 'p' === $type ) {
							echo '<p>' . $html( (string) ( $block['html'] ?? '' ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_kses in helper.
						} elseif ( 'h3' === $type ) {
							echo '<h3>' . esc_html( (string) ( $block['text'] ?? '' ) ) . '</h3>';
						} elseif ( 'ul' === $type ) {
							echo '<ul class="wpt-guide__list">';
							foreach ( (array) ( $block['items'] ?? array() ) as $item ) {
								echo '<li>' . $html( (string) $item ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput
							}
							echo '</ul>';
						} elseif ( 'table' === $type ) {
							echo '<h3>' . esc_html( (string) ( $block['caption'] ?? '' ) ) . '</h3>';
							echo '<div class="wpt-guide__table-wrap"><table class="wpt-guide__table"><thead><tr>';
							foreach ( (array) ( $block['head'] ?? array() ) as $th ) {
								echo '<th scope="col">' . esc_html( (string) $th ) . '</th>';
							}
							echo '</tr></thead><tbody>';
							foreach ( (array) ( $block['rows'] ?? array() ) as $row ) {
								echo '<tr>';
								foreach ( (array) $row as $cell ) {
									echo '<td>' . esc_html( (string) $cell ) . '</td>';
								}
								echo '</tr>';
							}
							echo '</tbody></table></div>';
						} elseif ( 'tips' === $type ) {
							echo '<dl class="wpt-guide__tips">';
							foreach ( (array) ( $block['items'] ?? array() ) as $tip ) {
								echo '<div><dt>' . esc_html( (string) ( $tip[0] ?? '' ) ) . '</dt><dd>' . esc_html( (string) ( $tip[1] ?? '' ) ) . '</dd></div>';
							}
							echo '</dl>';
						}
					}
					if ( ! empty( $section['faq'] ) ) {
						echo '<div class="wpt-guide__faq">';
						foreach ( (array) $section['faq'] as $qa ) {
							echo '<div class="wpt-guide__qa"><h3>' . esc_html( (string) ( $qa[0] ?? '' ) ) . '</h3><p>' . $html( (string) ( $qa[1] ?? '' ) ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
						}
						echo '</div>';
					}
					?>
				</section>
			<?php endforeach; ?>

			<aside class="wpt-guide__cta">
				<h2><?php echo esc_html( (string) ( $content['cta']['title'] ?? '' ) ); ?></h2>
				<p><?php echo esc_html( (string) ( $content['cta']['text'] ?? '' ) ); ?></p>
				<p>
					<a class="wpt-btn-whatsapp" href="<?php echo esc_url( $wa_url ); ?>"><?php esc_html_e( 'WhatsApp us', 'watphou-travels' ); ?></a>
					<a class="wpt-btn-primary" href="<?php echo esc_url( $quote ); ?>"><?php esc_html_e( 'Request a quote', 'watphou-travels' ); ?></a>
				</p>
			</aside>
		</div>
	</article>
</main>
<?php if ( '' !== $schema ) : ?>
<script type="application/ld+json"><?php echo $schema; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_json_encode with HEX flags. ?></script>
<?php endif; ?>
<?php
get_footer();
