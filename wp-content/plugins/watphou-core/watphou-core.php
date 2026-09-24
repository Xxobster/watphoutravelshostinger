<?php
/**
 * Plugin Name: Watphou Core
 * Description: Tours, destinations, settings, and business logic for Watphou Travels.
 * Version: 1.8.4
 * Author: Watphou Travels
 * Text Domain: watphou-core
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WATPHOU_CORE_VERSION', '1.8.4' );
define( 'WATPHOU_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WATPHOU_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once WATPHOU_CORE_PATH . 'includes/post-types.php';
require_once WATPHOU_CORE_PATH . 'includes/taxonomies.php';
require_once WATPHOU_CORE_PATH . 'includes/meta.php';
require_once WATPHOU_CORE_PATH . 'includes/settings.php';
require_once WATPHOU_CORE_PATH . 'includes/mail.php';
require_once WATPHOU_CORE_PATH . 'includes/roles.php';
require_once WATPHOU_CORE_PATH . 'includes/blocks.php';
require_once WATPHOU_CORE_PATH . 'includes/redirects.php';
require_once WATPHOU_CORE_PATH . 'includes/reviews.php';
require_once WATPHOU_CORE_PATH . 'includes/schema.php';
require_once WATPHOU_CORE_PATH . 'includes/seo.php';
require_once WATPHOU_CORE_PATH . 'includes/i18n.php';
require_once WATPHOU_CORE_PATH . 'includes/setup-i18n-seo.php';
require_once WATPHOU_CORE_PATH . 'includes/admin-dashboard.php';
require_once WATPHOU_CORE_PATH . 'includes/admin-tour-desk.php';
require_once WATPHOU_CORE_PATH . 'includes/admin-tour-list.php';
require_once WATPHOU_CORE_PATH . 'includes/admin-notices.php';
require_once WATPHOU_CORE_PATH . 'includes/xlsx-reader.php';
require_once WATPHOU_CORE_PATH . 'includes/import-tour.php';
require_once WATPHOU_CORE_PATH . 'includes/catalog.php';
require_once WATPHOU_CORE_PATH . 'includes/photos.php';
require_once WATPHOU_CORE_PATH . 'includes/admin-tour-manager.php';
require_once WATPHOU_CORE_PATH . 'includes/apply-reviewed-i18n.php';
require_once WATPHOU_CORE_PATH . 'includes/copy-review.php';
require_once WATPHOU_CORE_PATH . 'includes/import-cli.php';
require_once WATPHOU_CORE_PATH . 'includes/import-admin.php';
require_once WATPHOU_CORE_PATH . 'includes/shortcodes.php';

add_action( 'init', 'watphou_core_register_post_types' );
add_action( 'init', 'watphou_core_register_taxonomies' );
add_action( 'init', 'watphou_core_register_meta' );
add_action( 'init', 'watphou_core_register_blocks' );
add_action( 'init', 'watphou_core_maybe_sync_live_prices', 30 );
add_action( 'admin_init', 'watphou_core_register_settings' );
add_action( 'admin_menu', 'watphou_core_admin_menu' );
add_action( 'admin_notices', 'watphou_core_admin_notices' );
add_action( 'init', 'watphou_core_register_roles', 5 );
add_action( 'template_redirect', 'watphou_core_handle_redirects' );
add_action( 'wp_head', 'watphou_core_output_schema', 5 );
add_filter( 'pll_get_post_types', 'watphou_core_pll_post_types', 10, 2 );
add_filter( 'pll_get_taxonomies', 'watphou_core_pll_taxonomies', 10, 2 );

/**
 * Let Polylang manage tours (English published; French/Thai English placeholders until translated).
 *
 * @param string[] $types Post types Polylang already knows.
 * @return string[]
 */
function watphou_core_pll_post_types( array $types, $is_settings = false ): array {
	unset( $is_settings );
	$types['tour'] = 'tour';
	return $types;
}

/**
 * Let Polylang manage duration and destination taxonomies.
 *
 * @param string[] $taxonomies Taxonomies Polylang already knows.
 * @return string[]
 */
function watphou_core_pll_taxonomies( array $taxonomies, $is_settings = false ): array {
	unset( $is_settings );
	$taxonomies['duration']    = 'duration';
	$taxonomies['destination'] = 'destination';
	return $taxonomies;
}

register_activation_hook( __FILE__, 'watphou_core_activate' );
register_deactivation_hook( __FILE__, 'watphou_core_deactivate' );

function watphou_core_activate(): void {
	watphou_core_register_post_types();
	watphou_core_register_taxonomies();
	watphou_core_register_roles();
	watphou_core_ensure_google_reviews_cron();
	flush_rewrite_rules();
}

function watphou_core_deactivate(): void {
	wp_clear_scheduled_hook( WATPHOU_CORE_REVIEWS_CRON );
}
