<?php
/**
 * Configurações e sanitização.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Portus_Header_Free_Settings' ) && defined( 'PORTUS_HEADER_PATH' ) ) {
	require_once PORTUS_HEADER_PATH . 'includes/Free/class-portus-header-free-settings.php';
}

if ( ! class_exists( 'Portus_Header_Pro_Settings' ) && defined( 'PORTUS_HEADER_PATH' ) ) {
	$portus_header_pro_settings = PORTUS_HEADER_PATH . 'includes/Pro/Admin/class-portus-header-pro-settings.php';

	if ( is_readable( $portus_header_pro_settings ) ) {
		require_once $portus_header_pro_settings;
	}
}

class Portus_Header_Settings {
	const OPTION_NAME = 'portus_header_settings';
	const LICENSE_STATE_OPTION = 'portus_header_license_state';

	/**
	 * Retorna os valores oficiais da Portus.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		$defaults = self::free_defaults();

		if ( class_exists( 'Portus_Header_Pro_Settings' ) ) {
			$defaults = array_merge( $defaults, Portus_Header_Pro_Settings::defaults() );
		}

		return $defaults;
	}

	/**
	 * Retorna somente os padrões permitidos para a futura edição Free.
	 *
	 * @return array<string, mixed>
	 */
	public static function free_defaults() {
		return class_exists( 'Portus_Header_Free_Settings' )
			? Portus_Header_Free_Settings::defaults()
			: self::minimum_free_defaults();
	}

	/**
	 * Retorna somente os padrões exclusivos da edição Pro.
	 *
	 * @return array<string, mixed>
	 */
	public static function pro_defaults() {
		return class_exists( 'Portus_Header_Pro_Settings' )
			? Portus_Header_Pro_Settings::defaults()
			: array();
	}

	/**
	 * Retorna padrões por edição sem mudar o comportamento Pro atual.
	 *
	 * @param string $edition Edição.
	 * @return array<string, mixed>
	 */
	public static function defaults_for_edition( $edition ) {
		return 'free' === sanitize_key( $edition ) ? self::free_defaults() : self::defaults();
	}

	/**
	 * Chaves base permitidas na futura Free.
	 *
	 * @return array<int, string>
	 */
	public static function free_keys() {
		if ( class_exists( 'Portus_Header_Free_Settings' ) ) {
			return Portus_Header_Free_Settings::keys();
		}

		return array_keys( self::minimum_free_defaults() );
	}

	/**
	 * Chaves exclusivas da edição Pro.
	 *
	 * @return array<int, string>
	 */
	public static function pro_keys() {
		return class_exists( 'Portus_Header_Pro_Settings' )
			? Portus_Header_Pro_Settings::keys()
			: array();
	}

	/**
	 * Fallback mínimo da edição Free caso o schema físico ainda não tenha sido carregado.
	 *
	 * Este método não pode conter chaves premium. A edição Pro deve depender
	 * exclusivamente de Portus_Header_Pro_Settings.
	 *
	 * @return array<string, mixed>
	 */
	private static function minimum_free_defaults() {
		return array(
			'display_mode'        => 'shortcode',
			'sticky'              => 0,
			'desktop_logo_id'     => 0,
			'mobile_logo_id'      => 0,
			'cta_enabled'         => 1,
			'cta_text'            => 'Fale conosco',
			'cta_url'             => '',
			'cta_new_tab'         => 0,
			'font_family'         => '',
			'color_header_bg'     => '#00053A',
			'color_text'          => '#FFFFFF',
			'color_hover'         => '#C0A821',
			'color_submenu_bg'    => '#FFFFFF',
			'color_submenu_text'  => '#00053A',
			'color_submenu_hover' => '#C0A821',
			'color_cta_bg'        => '#C0A821',
			'color_cta_text'      => '#00053A',
			'color_cta_hover_bg'  => '#D3C45A',
			'color_cta_hover_text'=> '#00053A',
			'color_mobile_header_bg' => '#00053A',
			'color_mobile_bg'     => '#FFFFFF',
			'color_mobile_text'   => '#00053A',
			'color_mobile_hover'  => '#C0A821',
			'color_mobile_icon'   => '#C0A821',
			'color_mobile_toggle_bg' => '#FFFFFF',
			'color_mobile_submenu_bg' => '#FFFFFF',
			'color_mobile_submenu_text' => '#00053A',
			'color_mobile_submenu_hover' => '#C0A821',
			'color_mobile_submenu_icon' => '#C0A821',
			'color_mobile_cta_bg' => '#00053A',
			'color_mobile_cta_text' => '#FFFFFF',
			'color_mobile_cta_hover_bg' => '#C0A821',
			'color_mobile_cta_hover_text' => '#00053A',
			'container_max_width' => 1240,
			'desktop_height'      => 82,
			'mobile_height'       => 72,
			'desktop_logo_width'  => 220,
			'desktop_logo_height' => 52,
			'mobile_logo_width'   => 180,
			'mobile_logo_height'  => 46,
			'menu_gap'            => 30,
			'cta_radius'          => 6,
			'submenu_width'       => 210,
			'drawer_width'        => 390,
			'drawer_padding'      => 24,
			'menu_icon_size'      => 30,
			'close_icon_size'     => 24,
			'toggle_button_size'  => 44,
			'desktop_menu_font_size' => 15,
			'mobile_menu_font_size' => 16,
			'submenu_font_size'   => 14,
			'cta_font_size'       => 15,
			'menu_font_weight'    => 600,
			'desktop_menu_font_weight' => 600,
			'mobile_menu_font_weight' => 700,
			'desktop_menu_letter_spacing' => 0,
			'mobile_menu_letter_spacing' => 0,
			'submenu_icon_size'   => 22,
			'submenu_animation_duration' => 280,
			'drawer_animation_duration' => 280,
			'load_assets_globally'=> 0,
			'hide_theme_header'   => 0,
			'theme_header_selector' => '',
			'disable_astra_header' => 0,
		);
	}

	/**
	 * Retorna configurações mescladas aos padrões.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_all() {
		$saved = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		// Migra silenciosamente o esquema visual 0.1.x para os novos padrões 0.2.0.
		if ( ! array_key_exists( 'color_mobile_text', $saved ) ) {
			$saved['color_mobile_bg']    = '#FFFFFF';
			$saved['color_cta_hover_bg'] = '#D3C45A';
		}

		$settings = wp_parse_args( $saved, self::defaults() );

		if ( class_exists( 'Portus_Header_Pro_Settings' ) ) {
			$license_state = get_option( self::LICENSE_STATE_OPTION, array() );

			if ( is_array( $license_state ) ) {
				foreach ( array( 'license_status', 'license_domain', 'license_last_checked', 'license_message' ) as $license_state_key ) {
					if ( array_key_exists( $license_state_key, $license_state ) ) {
						$settings[ $license_state_key ] = $license_state[ $license_state_key ];
					}
				}

				if ( isset( $settings['license_status'] ) ) {
					$settings['license_status'] = self::sanitize_license_status( $settings['license_status'] );
				}
			}
		}

		if ( array_key_exists( 'mini_cart_enabled', $settings ) ) {
			$settings['mini_cart_enabled'] = 0;
		}

		return $settings;
	}

	/**
	 * Retorna uma configuração.
	 *
	 * @param string $key Chave da configuração.
	 * @return mixed|null
	 */
	public static function get( $key ) {
		$settings = self::get_all();

		return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	}

	/**
	 * Sanitiza todos os dados enviados pelo painel.
	 *
	 * @param mixed $input Dados recebidos.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$input    = is_array( $input ) ? $input : array();
		$output   = array();
		$is_pro   = self::is_pro_edition() && class_exists( 'Portus_Header_Pro_Settings' );

		$output['display_mode'] = isset( $input['display_mode'] ) && in_array( $input['display_mode'], array( 'automatic', 'shortcode' ), true )
			? $input['display_mode']
			: $defaults['display_mode'];
		if ( $is_pro ) {
			$output['layout_preset'] = isset( $input['layout_preset'] ) && in_array( $input['layout_preset'], array( 'standard', 'compact', 'centered', 'cta' ), true )
				? $input['layout_preset']
				: $defaults['layout_preset'];
		}

		$checkboxes = class_exists( 'Portus_Header_Free_Settings' ) ? Portus_Header_Free_Settings::checkbox_keys() : array( 'sticky', 'cta_enabled', 'cta_new_tab', 'load_assets_globally', 'hide_theme_header', 'disable_astra_header' );

		if ( $is_pro ) {
			$checkboxes = array_merge( $checkboxes, Portus_Header_Pro_Settings::checkbox_keys() );
		}

		foreach ( $checkboxes as $checkbox ) {
			$output[ $checkbox ] = empty( $input[ $checkbox ] ) ? 0 : 1;
		}

		$output['desktop_logo_id'] = isset( $input['desktop_logo_id'] ) ? absint( $input['desktop_logo_id'] ) : 0;
		$output['mobile_logo_id']  = isset( $input['mobile_logo_id'] ) ? absint( $input['mobile_logo_id'] ) : 0;
		$output['cta_text']        = isset( $input['cta_text'] ) ? sanitize_text_field( $input['cta_text'] ) : $defaults['cta_text'];
		$output['cta_url']         = isset( $input['cta_url'] ) ? esc_url_raw( $input['cta_url'] ) : '';
		$output['smart_scope'] = isset( $input['smart_scope'] ) && in_array( $input['smart_scope'], array( 'all', 'include', 'exclude' ), true )
			? $input['smart_scope']
			: $defaults['smart_scope'];
		$output['smart_page_ids'] = isset( $input['smart_page_ids'] ) ? self::sanitize_id_list( $input['smart_page_ids'] ) : '';
		if ( $is_pro ) {
			$output['search_placeholder'] = isset( $input['search_placeholder'] )
				? sanitize_text_field( $input['search_placeholder'] )
				: $defaults['search_placeholder'];

			foreach ( Portus_Header_Pro_Settings::text_keys() as $text_key ) {
				$value = isset( $input[ $text_key ] ) ? sanitize_text_field( $input[ $text_key ] ) : '';
				$output[ $text_key ] = '' !== $value ? $value : $defaults[ $text_key ];
			}

			$output['mega_menu_preset'] = isset( $input['mega_menu_preset'] ) && in_array( $input['mega_menu_preset'], array( 'cards', 'editorial', 'compact' ), true )
				? $input['mega_menu_preset']
				: $defaults['mega_menu_preset'];
			$saved_license_key         = '';
			$saved_settings            = get_option( self::OPTION_NAME, array() );
			if ( is_array( $saved_settings ) && isset( $saved_settings['license_key'] ) ) {
				$saved_license_key = (string) $saved_settings['license_key'];
			}
			$submitted_license_key = isset( $input['license_key'] ) ? substr( sanitize_text_field( $input['license_key'] ), 0, 120 ) : '';
			$submitted_is_masked   = '' !== $submitted_license_key && 0 === preg_match( '/[A-Za-z0-9]/', $submitted_license_key );
			$output['license_key'] = $submitted_is_masked ? $saved_license_key : $submitted_license_key;
			$output['enable_remote_updates'] = empty( $input['enable_remote_updates'] ) ? 0 : 1;
			$output['update_server_url'] = isset( $input['update_server_url'] ) ? esc_url_raw( $input['update_server_url'] ) : '';
			$output['update_channel']  = isset( $input['update_channel'] ) && 'beta' === $input['update_channel'] ? 'beta' : 'stable';

			$saved_license_state = get_option( self::LICENSE_STATE_OPTION, array() );

			if ( is_array( $saved_settings ) && $output['license_key'] === $saved_license_key ) {
				$output['license_status']       = isset( $saved_license_state['license_status'] ) ? self::sanitize_license_status( $saved_license_state['license_status'] ) : ( isset( $saved_settings['license_status'] ) ? self::sanitize_license_status( $saved_settings['license_status'] ) : $defaults['license_status'] );
				$output['license_domain']       = isset( $saved_license_state['license_domain'] ) ? sanitize_text_field( $saved_license_state['license_domain'] ) : ( isset( $saved_settings['license_domain'] ) ? sanitize_text_field( $saved_settings['license_domain'] ) : '' );
				$output['license_last_checked'] = isset( $saved_license_state['license_last_checked'] ) ? sanitize_text_field( $saved_license_state['license_last_checked'] ) : ( isset( $saved_settings['license_last_checked'] ) ? sanitize_text_field( $saved_settings['license_last_checked'] ) : '' );
				$output['license_message']      = isset( $saved_license_state['license_message'] ) ? sanitize_text_field( $saved_license_state['license_message'] ) : ( isset( $saved_settings['license_message'] ) ? sanitize_text_field( $saved_settings['license_message'] ) : '' );
			} else {
				$output['license_status']       = 'inactive';
				$output['license_domain']       = '';
				$output['license_last_checked'] = '';
				$output['license_message']      = '';
				delete_option( self::LICENSE_STATE_OPTION );
			}
		}
		$output['font_family']     = isset( $input['font_family'] ) ? self::sanitize_font_stack( $input['font_family'] ) : '';

		$color_keys = class_exists( 'Portus_Header_Free_Settings' ) ? Portus_Header_Free_Settings::color_keys() : array();

		if ( $is_pro ) {
			$color_keys = array_merge( $color_keys, Portus_Header_Pro_Settings::color_keys() );
		}

		foreach ( $color_keys as $color_key ) {
			$color = isset( $input[ $color_key ] ) ? sanitize_hex_color( $input[ $color_key ] ) : '';
			$output[ $color_key ] = $color ? strtoupper( $color ) : $defaults[ $color_key ];
		}

		$number_ranges = class_exists( 'Portus_Header_Free_Settings' ) ? Portus_Header_Free_Settings::number_ranges() : array();

		if ( $is_pro ) {
			$number_ranges = array_merge( $number_ranges, Portus_Header_Pro_Settings::number_ranges() );
		}

		foreach ( $number_ranges as $number_key => $range ) {
			$value = isset( $input[ $number_key ] ) ? absint( $input[ $number_key ] ) : $defaults[ $number_key ];
			$output[ $number_key ] = min( $range[1], max( $range[0], $value ) );
		}

		$selector = isset( $input['theme_header_selector'] )
			? self::sanitize_css_selector( $input['theme_header_selector'] )
			: '';

		$output['theme_header_selector'] = $selector;

		if ( $output['hide_theme_header'] && '' === $selector ) {
			$output['hide_theme_header'] = 0;
			add_settings_error(
				self::OPTION_NAME,
				'portus_header_missing_selector',
				esc_html__( 'O cabeçalho do tema não foi ocultado porque nenhum seletor CSS válido foi informado.', 'portus-header' ),
				'warning'
			);
		}

		if ( ! self::is_pro_edition() ) {
			$output = array_intersect_key( $output, array_flip( self::free_keys() ) );
		}

		return $output;
	}

	/**
	 * Sanitiza listas de IDs separadas por vírgula.
	 *
	 * @param mixed $value Valor.
	 * @return string
	 */
	private static function sanitize_id_list( $value ) {
		$ids = preg_split( '/[\s,]+/', (string) $value );
		$ids = array_filter( array_map( 'absint', $ids ) );

		return implode( ',', array_values( array_unique( $ids ) ) );
	}

	/**
	 * Sanitiza o status da licença Pro.
	 *
	 * @param mixed $status Status informado.
	 * @return string
	 */
	private static function sanitize_license_status( $status ) {
		$status = sanitize_key( (string) $status );

		return in_array( $status, array( 'active', 'pending', 'invalid', 'expired', 'inactive' ), true ) ? $status : 'inactive';
	}

	/**
	 * Permite somente seletores simples, sem declarações CSS.
	 *
	 * @param string $selector Seletor informado.
	 * @return string
	 */
	private static function sanitize_css_selector( $selector ) {
		$selector = trim( wp_strip_all_tags( wp_unslash( $selector ) ) );
		$selector = substr( $selector, 0, 250 );

		if ( '' === $selector || preg_match( '/[{};@\\\\]/', $selector ) ) {
			return '';
		}

		if ( ! preg_match( '/^[a-zA-Z0-9_#.,:>+~*\\-\\[\\]="\'()\\s]+$/', $selector ) ) {
			return '';
		}

		return $selector;
	}

	/**
	 * Sanitiza uma pilha simples de fontes sem permitir CSS arbitrário.
	 *
	 * @param mixed $value Valor.
	 * @return string
	 */
	private static function sanitize_font_stack( $value ) {
		$value = trim( wp_strip_all_tags( wp_unslash( (string) $value ) ) );
		$value = substr( $value, 0, 160 );

		if ( '' === $value || preg_match( '/[{};:@\\\\]/', $value ) ) {
			return '';
		}

		if ( ! preg_match( '/^[a-zA-Z0-9_,"\'\\-\\s]+$/', $value ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Informa se a edição ativa deve aceitar configurações Pro.
	 *
	 * @return bool
	 */
	private static function is_pro_edition() {
		return ! class_exists( 'Portus_Header_Edition_Manager' ) || Portus_Header_Edition_Manager::is_pro();
	}
}
