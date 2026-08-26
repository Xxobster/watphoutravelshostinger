<?php
/**
 * Plugin Name: Watphou Core
 * Description: Tours, destinations, settings, and business logic for Watphou Travels.
 * Version: 1.0.0
 * Author: Watphou Travels
 * Text Domain: watphou-core
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WATPHOU_CORE_VERSION', '1.0.0' );
define( 'WATPHOU_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WATPHOU_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once WATPHOU_CORE_PATH . 'includes/post-types.php';
require_once WATPHOU_CORE_PATH . 'includes/taxonomies.php';
require_once WATPHOU_CORE_PATH . 'includes/meta.php';
require_once WATPHOU_CORE_PATH . 'includes/settings.php';
require_once WATPHOU_CORE_PATH . 'includes/roles.php';
require_once WATPHOU_CORE_PATH . 'includes/blocks.php';
require_once WATPHOU_CORE_PATH . 'includes/redirects.php';
require_once WATPHOU_CORE_PATH . 'includes/schema.php';
require_once WATPHOU_CORE_PATH . 'includes/admin-dashboard.php';
require_once WATPHOU_CORE_PATH . 'includes/admin-notices.php';
require_once WATPHOU_CORE_PATH . 'includes/import-cli.php';
require_once WATPHOU_CORE_PATH . 'includes/shortcodes.php';

add_action( 'init', 'watphou_core_register_post_types' );
add_action( 'init', 'watphou_core_register_taxonomies' );
add_action( 'init', 'watphou_core_register_meta' );
add_action( 'init', 'watphou_core_register_blocks' );
add_action( 'admin_init', 'watphou_core_register_settings' );
add_action( 'admin_menu', 'watphou_core_admin_menu' );
add_action( 'admin_notices', 'watphou_core_admin_notices' );
add_action( 'init', 'watphou_core_register_roles', 5 );
add_action( 'template_redirect', 'watphou_core_handle_redirects' );
add_action( 'wp_head', 'watphou_core_output_schema', 5 );

register_activation_hook( __FILE__, 'watphou_core_activate' );

function watphou_core_activate(): void {
	watphou_core_register_post_types();
	watphou_core_register_taxonomies();
	watphou_core_register_roles();
	flush_rewrite_rules();
}
