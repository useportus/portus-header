<?php
/**
 * Assets públicos e variáveis visuais.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header_Assets {
	/**
	 * Registra hooks.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Carrega os assets em todas as páginas públicas para garantir o shortcode
	 * também em widgets e templates que não expõem o conteúdo antecipadamente.
	 */
	public function enqueue() {
		$settings = Portus_Header_Settings::get_all();
		$is_pro   = $this->is_pro_edition();

		if ( ! $this->should_enqueue( $settings ) ) {
			return;
		}

		wp_enqueue_style(
			'portus-header',
			PORTUS_HEADER_URL . ( $is_pro ? 'assets/css/portus-header-pro.css' : 'assets/css/portus-header.css' ),
			array(),
			PORTUS_HEADER_VERSION
		);

		wp_add_inline_style( 'portus-header', $this->get_dynamic_css() );

		wp_enqueue_script(
			'portus-header',
			PORTUS_HEADER_URL . ( $is_pro ? 'assets/js/portus-header-pro.js' : 'assets/js/portus-header.js' ),
			array(),
			PORTUS_HEADER_VERSION,
			true
		);

		wp_localize_script(
			'portus-header',
			'PortusHeaderFrontend',
			$this->get_script_data( $settings, $is_pro )
		);
	}

	/**
	 * Monta os dados JavaScript conforme a edição ativa.
	 *
	 * @param array<string, mixed> $settings Configurações.
	 * @param bool                 $is_pro   Informa se a edição Pro está ativa.
	 * @return array<string, mixed>
	 */
	private function get_script_data( $settings, $is_pro ) {
		$data = array(
			'openMenu'        => esc_html__( 'Abrir menu', 'portus-header' ),
			'closeMenu'       => esc_html__( 'Fechar menu', 'portus-header' ),
			'expandSubmenu'   => esc_html__( 'Expandir submenu:', 'portus-header' ),
			'collapseSubmenu' => esc_html__( 'Recolher submenu:', 'portus-header' ),
			'submenuIcon'     => $this->get_icon_src( 'chevron-down.svg', $settings['color_mobile_submenu_icon'] ),
		);

		if ( ! $is_pro ) {
			return $data;
		}

		return array_merge(
			$data,
			array(
				'searchEndpoint'          => esc_url_raw( rest_url( 'portus-header/v1/products' ) ),
				'searchMinChars'          => absint( $settings['search_min_chars'] ),
				'searchDelay'             => absint( $settings['search_debounce'] ),
				'searchLimit'             => absint( $settings['search_results_limit'] ),
				'searchRecentEnabled'     => ! empty( $settings['search_recent_enabled'] ),
				'searchRecentLimit'       => absint( $settings['search_recent_limit'] ),
				'searchHistoryEnabled'    => ! empty( $settings['search_history_enabled'] ),
				'searchHistoryLimit'      => absint( $settings['search_history_limit'] ),
				'searchHighlightEnabled'  => ! empty( $settings['search_highlight_enabled'] ),
				'searchCategoriesEnabled' => ! empty( $settings['search_categories_enabled'] ),
				'searchShowImage'         => ! empty( $settings['search_show_image'] ),
				'searchShowPrice'         => ! empty( $settings['search_show_price'] ),
				'searchShowStock'         => ! empty( $settings['search_show_stock'] ),
				'searchViewAllEnabled'    => ! empty( $settings['search_view_all_enabled'] ),
				'searchResultsUrl'        => esc_url_raw( home_url( '/' ) ),
				'searchI18n'              => array(
					'loading'      => esc_html__( 'Buscando produtos…', 'portus-header' ),
					'empty'        => esc_html__( 'Nenhum produto encontrado.', 'portus-header' ),
					'error'        => esc_html__( 'Não foi possível carregar os resultados.', 'portus-header' ),
					'minChars'     => esc_html__( 'Digite mais caracteres para pesquisar.', 'portus-header' ),
					'resultHint'   => esc_html__( 'Use as setas para navegar e Enter para abrir.', 'portus-header' ),
					'recentLabel'  => sanitize_text_field( $settings['search_recent_label'] ),
					'historyLabel' => sanitize_text_field( $settings['search_history_label'] ),
					'resultsLabel' => sanitize_text_field( $settings['search_results_label'] ),
					'viewAllLabel' => sanitize_text_field( $settings['search_view_all_label'] ),
					'clearHistory' => esc_html__( 'Limpar', 'portus-header' ),
				),
			)
		);
	}

	/**
	 * Gera somente variáveis CSS validadas e a regra opcional do tema.
	 *
	 * @return string
	 */
	private function get_dynamic_css() {
		$settings = Portus_Header_Settings::get_all();
		$defaults = Portus_Header_Settings::defaults();
		$map      = array(
			'--portus-header-bg'          => 'color_header_bg',
			'--portus-header-text'        => 'color_text',
			'--portus-header-hover'       => 'color_hover',
			'--portus-header-submenu-bg'  => 'color_submenu_bg',
			'--portus-header-submenu-text'=> 'color_submenu_text',
			'--portus-header-submenu-hover' => 'color_submenu_hover',
			'--portus-header-cta-bg'      => 'color_cta_bg',
			'--portus-header-cta-text'    => 'color_cta_text',
			'--portus-header-cta-hover-bg'=> 'color_cta_hover_bg',
			'--portus-header-cta-hover-text' => 'color_cta_hover_text',
			'--portus-header-mobile-bar-bg' => 'color_mobile_header_bg',
			'--portus-header-mobile-bg'   => 'color_mobile_bg',
			'--portus-header-mobile-text' => 'color_mobile_text',
			'--portus-header-mobile-hover'=> 'color_mobile_hover',
			'--portus-header-mobile-icon' => 'color_mobile_icon',
			'--portus-header-mobile-toggle-bg' => 'color_mobile_toggle_bg',
			'--portus-header-mobile-submenu-bg' => 'color_mobile_submenu_bg',
			'--portus-header-mobile-submenu-text' => 'color_mobile_submenu_text',
			'--portus-header-mobile-submenu-hover' => 'color_mobile_submenu_hover',
			'--portus-header-mobile-submenu-icon' => 'color_mobile_submenu_icon',
			'--portus-header-mobile-cta-bg' => 'color_mobile_cta_bg',
			'--portus-header-mobile-cta-text' => 'color_mobile_cta_text',
			'--portus-header-mobile-cta-hover-bg' => 'color_mobile_cta_hover_bg',
			'--portus-header-mobile-cta-hover-text' => 'color_mobile_cta_hover_text',
			'--portus-header-search-icon' => 'color_search_icon',
			'--portus-header-account-icon' => 'color_account_icon',
			'--portus-header-cart-icon' => 'color_cart_icon',
			'--portus-header-search-panel-bg' => 'color_search_panel_bg',
			'--portus-header-search-panel-text' => 'color_search_panel_text',
			'--portus-header-search-panel-border' => 'color_search_panel_border',
			'--portus-header-search-submit-bg' => 'color_search_submit_bg',
			'--portus-header-search-submit-text' => 'color_search_submit_text',
			'--portus-header-search-submit-hover-bg' => 'color_search_submit_hover_bg',
			'--portus-header-search-submit-hover-text' => 'color_search_submit_hover_text',
			'--portus-header-search-result-bg' => 'color_search_result_bg',
			'--portus-header-search-result-hover-bg' => 'color_search_result_hover_bg',
			'--portus-header-search-result-name' => 'color_search_result_name',
			'--portus-header-search-result-category' => 'color_search_result_category',
			'--portus-header-search-result-price' => 'color_search_result_price',
			'--portus-header-search-stock-in' => 'color_search_stock_in',
			'--portus-header-search-stock-out' => 'color_search_stock_out',
			'--portus-header-search-highlight-bg' => 'color_search_highlight_bg',
			'--portus-header-search-highlight-text' => 'color_search_highlight_text',
			'--portus-header-search-view-all-bg' => 'color_search_view_all_bg',
			'--portus-header-search-view-all-text' => 'color_search_view_all_text',
			'--portus-header-search-view-all-border' => 'color_search_view_all_border',
			'--portus-header-search-view-all-hover-bg' => 'color_search_view_all_hover_bg',
			'--portus-header-search-view-all-hover-text' => 'color_search_view_all_hover_text',
			'--portus-header-mega-bg' => 'color_mega_bg',
			'--portus-header-mega-text' => 'color_mega_text',
			'--portus-header-mega-hover' => 'color_mega_hover',
			'--portus-header-mega-border' => 'color_mega_border',
			'--portus-header-mega-badge-text' => 'color_mega_badge_text',
			'--portus-header-account-dropdown-bg' => 'color_account_dropdown_bg',
			'--portus-header-account-dropdown-text' => 'color_account_dropdown_text',
			'--portus-header-account-dropdown-hover' => 'color_account_dropdown_hover',
			'--portus-header-account-dropdown-border' => 'color_account_dropdown_border',
			'--portus-header-mini-cart-bg' => 'color_mini_cart_bg',
			'--portus-header-mini-cart-text' => 'color_mini_cart_text',
			'--portus-header-mini-cart-accent' => 'color_mini_cart_accent',
			'--portus-header-mini-cart-border' => 'color_mini_cart_border',
			'--portus-header-transparent-bg' => 'color_transparent_bg',
			'--portus-header-scrolled-bg' => 'color_scrolled_bg',
			'--portus-header-scrolled-text' => 'color_scrolled_text',
		);
		$variables = array();

		foreach ( $map as $property => $key ) {
			if ( ! array_key_exists( $key, $settings ) || ! array_key_exists( $key, $defaults ) ) {
				continue;
			}

			$value = sanitize_hex_color( $settings[ $key ] );
			$variables[] = $property . ':' . ( $value ? $value : $defaults[ $key ] );
		}

		$size_map = array(
			'--portus-header-container-width' => 'container_max_width',
			'--portus-header-desktop-height'  => 'desktop_height',
			'--portus-header-mobile-height'   => 'mobile_height',
			'--portus-header-desktop-logo-width' => 'desktop_logo_width',
			'--portus-header-desktop-logo-height' => 'desktop_logo_height',
			'--portus-header-mobile-logo-width' => 'mobile_logo_width',
			'--portus-header-mobile-logo-height' => 'mobile_logo_height',
			'--portus-header-menu-gap'       => 'menu_gap',
			'--portus-header-cta-radius'     => 'cta_radius',
			'--portus-header-submenu-width'  => 'submenu_width',
			'--portus-header-drawer-width'   => 'drawer_width',
			'--portus-header-drawer-padding' => 'drawer_padding',
			'--portus-header-menu-icon-size' => 'menu_icon_size',
			'--portus-header-close-icon-size' => 'close_icon_size',
			'--portus-header-toggle-button-size' => 'toggle_button_size',
			'--portus-header-desktop-menu-font-size' => 'desktop_menu_font_size',
			'--portus-header-mobile-menu-font-size' => 'mobile_menu_font_size',
			'--portus-header-submenu-font-size' => 'submenu_font_size',
			'--portus-header-cta-font-size' => 'cta_font_size',
			'--portus-header-submenu-icon-size' => 'submenu_icon_size',
			'--portus-header-search-icon-size' => 'search_icon_size',
			'--portus-header-account-icon-size' => 'account_icon_size',
			'--portus-header-cart-icon-size' => 'cart_icon_size',
			'--portus-header-action-gap' => 'header_action_gap',
			'--portus-header-search-panel-width' => 'search_panel_width',
			'--portus-header-search-result-image-size' => 'search_result_image_size',
			'--portus-header-search-result-gap' => 'search_result_gap',
			'--portus-header-search-result-radius' => 'search_result_radius',
			'--portus-header-search-result-max-height' => 'search_result_max_height',
			'--portus-header-search-result-name-size' => 'search_result_name_size',
			'--portus-header-search-result-meta-size' => 'search_result_meta_size',
			'--portus-header-search-view-all-radius' => 'search_view_all_radius',
			'--portus-header-mega-width' => 'mega_menu_width',
			'--portus-header-mega-gap' => 'mega_menu_gap',
			'--portus-header-mega-padding' => 'mega_menu_padding',
			'--portus-header-mega-radius' => 'mega_menu_radius',
			'--portus-header-mega-image-height' => 'mega_menu_image_height',
			'--portus-header-account-dropdown-width' => 'account_dropdown_width',
			'--portus-header-mini-cart-width' => 'mini_cart_width',
			'--portus-header-panel-radius' => 'panel_radius',
			'--portus-header-scroll-threshold' => 'scroll_threshold',
			'--portus-header-scrolled-height' => 'scrolled_height',
		);

		foreach ( $size_map as $property => $key ) {
			if ( ! array_key_exists( $key, $settings ) && ! array_key_exists( $key, $defaults ) ) {
				continue;
			}

			$value = isset( $settings[ $key ] ) ? absint( $settings[ $key ] ) : absint( $defaults[ $key ] );
			$variables[] = $property . ':' . $value . 'px';
		}

		$font_family = isset( $settings['font_family'] ) ? trim( (string) $settings['font_family'] ) : '';
		if ( '' !== $font_family ) {
			$variables[] = '--portus-header-font-family:' . $font_family;
		}
		$variables[] = '--portus-header-menu-font-weight:' . absint( $settings['menu_font_weight'] );
		$variables[] = '--portus-header-desktop-menu-font-weight:' . absint( $settings['desktop_menu_font_weight'] );
		$variables[] = '--portus-header-mobile-menu-font-weight:' . absint( $settings['mobile_menu_font_weight'] );
		$variables[] = '--portus-header-desktop-menu-letter-spacing:' . absint( $settings['desktop_menu_letter_spacing'] ) / 10 . 'px';
		$variables[] = '--portus-header-mobile-menu-letter-spacing:' . absint( $settings['mobile_menu_letter_spacing'] ) / 10 . 'px';
		$variables[] = '--portus-header-submenu-animation-duration:' . absint( $settings['submenu_animation_duration'] ) . 'ms';
		$variables[] = '--portus-header-drawer-animation-duration:' . absint( $settings['drawer_animation_duration'] ) . 'ms';

		if ( isset( $settings['search_button_animation_duration'] ) ) {
			$variables[] = '--portus-header-search-button-animation-duration:' . absint( $settings['search_button_animation_duration'] ) . 'ms';
		}

		if ( isset( $settings['mega_menu_animation_duration'] ) ) {
			$variables[] = '--portus-header-mega-animation-duration:' . absint( $settings['mega_menu_animation_duration'] ) . 'ms';
		}

		if ( isset( $settings['transparent_opacity'] ) ) {
			$variables[] = '--portus-header-transparent-opacity:' . absint( $settings['transparent_opacity'] ) . '%';
		}

		$css = '#portus-header,.portus-header-drawer,.portus-header-overlay,.portus-header-search-panel,.portus-header-search-overlay{' . implode( ';', $variables ) . '}';

		if ( ! empty( $settings['hide_theme_header'] ) && ! empty( $settings['theme_header_selector'] ) ) {
			$selectors = array_filter( array_map( 'trim', explode( ',', $settings['theme_header_selector'] ) ) );

			foreach ( $selectors as $selector ) {
				$css .= 'body.portus-header-is-rendered ' . $selector . '{display:none!important;}';
			}
		}

		return $css;
	}

	/**
	 * Evita carregar CSS e JS onde o cabeçalho não pode aparecer.
	 *
	 * @param array<string, mixed> $settings Configurações.
	 * @return bool
	 */
	private function should_enqueue( $settings ) {
		if ( ! empty( $settings['load_assets_globally'] ) || 'automatic' === $settings['display_mode'] ) {
			return true;
		}

		$detected = false;

		if ( function_exists( 'is_singular' ) && is_singular() && function_exists( 'get_queried_object' ) ) {
			$post = get_queried_object();

			if ( $post instanceof WP_Post ) {
				$detected = has_shortcode( $post->post_content, 'portus_header' );
			}
		}

		return (bool) apply_filters( 'portus_header_should_enqueue_assets', $detected, $settings );
	}

	/**
	 * Retorna um SVG local incorporado com cor personalizável.
	 *
	 * @param string $filename Arquivo permitido.
	 * @param string $color    Cor hexadecimal.
	 * @return string
	 */
	private function get_icon_src( $filename, $color ) {
		if ( 'chevron-down.svg' !== $filename ) {
			return '';
		}

		$path  = PORTUS_HEADER_PATH . 'assets/svg/' . $filename;
		$color = sanitize_hex_color( $color );

		if ( ! $color || ! is_readable( $path ) ) {
			return PORTUS_HEADER_URL . 'assets/svg/' . $filename;
		}

		$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( ! is_string( $svg ) || '' === $svg ) {
			return PORTUS_HEADER_URL . 'assets/svg/' . $filename;
		}

		$svg = preg_replace( '/fill="#[A-Fa-f0-9]{6}"/', 'fill="' . strtoupper( $color ) . '"', $svg, 1 );

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Informa se a edição ativa deve carregar assets Pro.
	 *
	 * @return bool
	 */
	private function is_pro_edition() {
		return ! class_exists( 'Portus_Header_Edition_Manager' ) || Portus_Header_Edition_Manager::is_pro();
	}
}
