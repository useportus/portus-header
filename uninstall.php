<?php
/**
 * Limpeza ao desinstalar o plugin.
 *
 * @package Portus_Header
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'portus_header_settings' );
delete_option( 'portus_header_onboarding_pending' );
