<?php
/**
 * Shows the business phone numbers as "+856 20 9949 5858" everywhere on public pages.
 *
 * Some page texts and SEO descriptions live in the database in the older "+85620 9949 5858" spelling.
 * Display text only: wa.me / tel: links are digits only and are never touched (they contain no space).
 */
defined( 'ABSPATH' ) || exit;

function watphou_core_format_phone_numbers( string $html ): string {
	return preg_replace( '/\+85620 (?=\d{4} \d{4})/', '+856 20 ', $html );
}

add_action(
	'template_redirect',
	static function (): void {
		if ( is_admin() || is_feed() || is_robots() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		ob_start( 'watphou_core_format_phone_numbers' );
	},
	1
);
