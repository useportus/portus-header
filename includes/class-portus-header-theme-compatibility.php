<?php
/**
 * Compatibilidades controladas com temas.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header_Theme_Compatibility {
	/**
	 * Registra hooks de compatibilidade.
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'maybe_disable_astra_header' ), 20 );
	}

	/**
	 * Remove o cabeçalho padrão do Astra quando o usuário ativa explicitamente a opção.
	 */
	public function maybe_disable_astra_header() {
		$settings = Portus_Header_Settings::get_all();

		if ( empty( $settings['disable_astra_header'] ) || ! self::is_astra_active() ) {
			return;
		}

		remove_action( 'astra_header', 'astra_header_markup' );
	}

	/**
	 * Detecta se o tema ativo é Astra ou um child theme do Astra.
	 *
	 * @return bool
	 */
	public static function is_astra_active() {
		if ( defined( 'ASTRA_THEME_VERSION' ) || function_exists( 'astra_header' ) ) {
			return true;
		}

		if ( ! function_exists( 'wp_get_theme' ) ) {
			return false;
		}

		$theme = wp_get_theme();

		if ( ! is_object( $theme ) ) {
			return false;
		}

		$template   = method_exists( $theme, 'get_template' ) ? strtolower( (string) $theme->get_template() ) : '';
		$stylesheet = method_exists( $theme, 'get_stylesheet' ) ? strtolower( (string) $theme->get_stylesheet() ) : '';
		$name       = method_exists( $theme, 'get' ) ? strtolower( (string) $theme->get( 'Name' ) ) : '';

		return 'astra' === $template || 'astra' === $stylesheet || false !== strpos( $name, 'astra' );
	}
}
