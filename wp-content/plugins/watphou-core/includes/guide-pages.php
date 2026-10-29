<?php
/**
 * Guide / FAQ page: content loader, one-time page creation (EN, FR, TH) and FAQ schema.
 * Copy lives in data/guide_content.json so a typo cannot break the site.
 */
defined( 'ABSPATH' ) || exit;

const WATPHOU_GUIDE_SLUG = 'guide-faq';

/**
 * @return array<string, mixed> Guide content for one language (falls back to English).
 */
function watphou_core_guide_content( string $lang = '' ): array {
	static $all = null;
	if ( null === $all ) {
		$path = dirname( __DIR__ ) . '/data/guide_content.json';
		$raw  = is_readable( $path ) ? json_decode( (string) file_get_contents( $path ), true ) : null;
		$all  = is_array( $raw ) ? $raw : array();
	}
	if ( '' === $lang ) {
		$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : 'en';
	}
	if ( isset( $all[ $lang ] ) && is_array( $all[ $lang ] ) ) {
		return $all[ $lang ];
	}
	return isset( $all['en'] ) && is_array( $all['en'] ) ? $all['en'] : array();
}

/**
 * Inline HTML allowed in guide copy (bold, italics, WhatsApp link). {whatsapp} is replaced by the real URL.
 */
function watphou_core_guide_html( string $html ): string {
	$wa   = function_exists( 'watphou_get_whatsapp_url' ) ? (string) watphou_get_whatsapp_url() : 'https://wa.me/8562099495858';
	$html = str_replace( '{whatsapp}', esc_url( $wa ), $html );
	return wp_kses(
		$html,
		array(
			'strong' => array(),
			'em'     => array(),
			'a'      => array( 'href' => array() ),
		)
	);
}

/**
 * FAQPage structured data (Google rich results) for the Guide / FAQ page.
 */
function watphou_core_guide_faq_schema( array $content ): string {
	$entities = array();
	foreach ( (array) ( $content['sections'] ?? array() ) as $section ) {
		foreach ( (array) ( $section['faq'] ?? array() ) as $qa ) {
			if ( ! is_array( $qa ) || count( $qa ) < 2 ) {
				continue;
			}
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( (string) $qa[0] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( str_replace( '{whatsapp}', '#', (string) $qa[1] ) ),
				),
			);
		}
	}
	if ( ! $entities ) {
		return '';
	}
	return (string) wp_json_encode(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
	);
}

add_action( 'init', 'watphou_core_ensure_guide_pages', 45 );

/**
 * Create the Guide / FAQ page in English, French and Thai once, and link them as translations.
 * Revert: delete the three pages listed in the option "watphou_guide_page_ids" (see deploy backup).
 */
function watphou_core_ensure_guide_pages(): void {
	if ( wp_installing() || wp_doing_ajax() || wp_doing_cron() || ! function_exists( 'pll_save_post_translations' ) ) {
		return;
	}
	if ( get_option( 'watphou_guide_pages_version' ) === '4' ) {
		return;
	}
	$model = function_exists( 'watphou_core_pll_model' ) ? watphou_core_pll_model() : null;
	if ( ! $model ) {
		return;
	}
	$titles = array(
		'en' => 'Guide / FAQ',
		'fr' => 'Guide / FAQ',
		'th' => 'คู่มือ / คำถามที่พบบ่อย',
	);
	$ids   = array();
	$found = array();
	foreach ( get_posts(
		array(
			'name'           => WATPHOU_GUIDE_SLUG,
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'lang'           => '',
		)
	) as $pid ) {
		$plang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( (int) $pid ) : '';
		if ( is_string( $plang ) && '' !== $plang && ! isset( $found[ $plang ] ) ) {
			$found[ $plang ] = (int) $pid;
		}
	}
	foreach ( $titles as $code => $title ) {
		if ( isset( $found[ $code ] ) ) {
			$ids[ $code ] = $found[ $code ];
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $title,
				'post_name'    => WATPHOU_GUIDE_SLUG,
				'post_status'  => 'publish',
				'post_content' => '',
			),
			true
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return;
		}
		$id = (int) $id;
		$model->post->set_language( $id, $code );
		watphou_core_force_post_slug( $id, WATPHOU_GUIDE_SLUG );
		$ids[ $code ] = $id;
	}
	if ( count( $ids ) > 1 ) {
		pll_save_post_translations( $ids );
	}
	// Polylang keeps Yoast meta identical across translations: store English; the FR/TH text comes from ui_extras_i18n.json.
	$en = watphou_core_guide_content( 'en' );
	foreach ( $ids as $id ) {
		update_post_meta( $id, '_yoast_wpseo_title', (string) ( $en['seo_title'] ?? '' ) );
		update_post_meta( $id, '_yoast_wpseo_metadesc', (string) ( $en['seo_desc'] ?? '' ) );
	}
	update_option( 'watphou_guide_page_ids', $ids, false );
	update_option( 'watphou_guide_pages_version', '4', false );
	flush_rewrite_rules( false );
}

/**
 * One-time retirement of the old "Travel guide" page (EN, FR, TH): it is replaced by Guide / FAQ.
 * The pages go to the Trash (restorable from wp-admin > Pages > Trash); /travel-guide URLs 301 via data/redirects.csv.
 */
add_action( 'init', 'watphou_core_retire_travel_guide', 50 );

function watphou_core_retire_travel_guide(): void {
	if ( get_option( 'watphou_travel_guide_retired' ) === '1' || wp_installing() ) {
		return;
	}
	$ids = get_posts(
		array(
			'post_type'      => 'page',
			'name'           => 'travel-guide',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'lang'           => '',
		)
	);
	foreach ( $ids as $id ) {
		wp_trash_post( (int) $id );
	}
	// Polylang names the FR/TH copies travel-guide-fr / travel-guide-th.
	foreach ( array( 'travel-guide-fr', 'travel-guide-th' ) as $slug ) {
		foreach ( get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids', 'lang' => '' ) ) as $id ) {
			wp_trash_post( (int) $id );
		}
	}
	update_option( 'watphou_travel_guide_retired', '1', false );
}
