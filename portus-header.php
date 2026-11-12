<?php
/**
 * Plugin Name:       Portus Header
 * Description:       Responsive WordPress header with native menus, logos, CTA, shortcode mode, automatic insertion, sticky option, and mobile drawer.
 * Version:           2.3.11
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Portus
 * Author URI:        https://useportus.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       portus-header
 * Domain Path:       /languages
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PORTUS_HEADER_VERSION', '2.3.11' );
define( 'PORTUS_HEADER_FILE', __FILE__ );
define( 'PORTUS_HEADER_PATH', plugin_dir_path( __FILE__ ) );
define( 'PORTUS_HEADER_URL', plugin_dir_url( __FILE__ ) );

/**
 * Verifica os requisitos mínimos antes da ativação.
 */
function portus_header_activate() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, '7.4', '<' ) || version_compare( $wp_version, '6.0', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );

		wp_die(
			esc_html__( 'O Portus Header requer WordPress 6.0 ou superior e PHP 7.4 ou superior.', 'portus-header' ),
			esc_html__( 'Não foi possível ativar o Portus Header', 'portus-header' ),
			array( 'back_link' => true )
		);
	}

	update_option( 'portus_header_onboarding_pending', 1, false );
}
register_activation_hook( __FILE__, 'portus_header_activate' );

$portus_header_pro_settings = PORTUS_HEADER_PATH . 'includes/Pro/Admin/class-portus-header-pro-settings.php';
$portus_header_pro_admin    = PORTUS_HEADER_PATH . 'includes/Pro/Admin/class-portus-header-pro-admin.php';

require_once PORTUS_HEADER_PATH . 'includes/Free/class-portus-header-free-settings.php';

if ( is_readable( $portus_header_pro_settings ) ) {
	require_once $portus_header_pro_settings;
}

if ( is_readable( $portus_header_pro_admin ) ) {
	require_once $portus_header_pro_admin;
}

require_once PORTUS_HEADER_PATH . 'includes/class-portus-header-settings.php';
require_once PORTUS_HEADER_PATH . 'includes/class-portus-header-assets.php';
require_once PORTUS_HEADER_PATH . 'includes/class-portus-header-theme-compatibility.php';
require_once PORTUS_HEADER_PATH . 'includes/class-portus-header-renderer.php';
require_once PORTUS_HEADER_PATH . 'admin/class-portus-header-admin.php';
require_once PORTUS_HEADER_PATH . 'includes/Core/class-portus-header-edition-manager.php';
require_once PORTUS_HEADER_PATH . 'includes/Core/class-portus-header-module-loader.php';
require_once PORTUS_HEADER_PATH . 'includes/Free/class-portus-header-free-module.php';

$portus_header_pro_module = PORTUS_HEADER_PATH . 'includes/Pro/class-portus-header-pro-module.php';
if ( is_readable( $portus_header_pro_module ) ) {
	require_once $portus_header_pro_module;
}

require_once PORTUS_HEADER_PATH . 'includes/class-portus-header.php';

/**
 * Inicializa o plugin.
 *
 * @return Portus_Header
 */
function portus_header() {
	return Portus_Header::instance();
}

portus_header();
