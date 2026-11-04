<?php
/**
 * Schema de configurações da edição Free/base.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header_Free_Settings {
	/**
	 * Defaults permitidos na edição Free.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'display_mode'        => 'shortcode',
			'smart_scope'         => 'all',
			'smart_page_ids'      => '',
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
	 * @return array<int, string>
	 */
	public static function keys() {
		return array_keys( self::defaults() );
	}

	/**
	 * @return array<int, string>
	 */
	public static function checkbox_keys() {
		return array(
			'sticky',
			'cta_enabled',
			'cta_new_tab',
			'load_assets_globally',
			'hide_theme_header',
			'disable_astra_header',
		);
	}

	/**
	 * @return array<int, string>
	 */
	public static function color_keys() {
		return array(
			'color_header_bg',
			'color_text',
			'color_hover',
			'color_submenu_bg',
			'color_submenu_text',
			'color_submenu_hover',
			'color_cta_bg',
			'color_cta_text',
			'color_cta_hover_bg',
			'color_cta_hover_text',
			'color_mobile_header_bg',
			'color_mobile_bg',
			'color_mobile_text',
			'color_mobile_hover',
			'color_mobile_icon',
			'color_mobile_toggle_bg',
			'color_mobile_submenu_bg',
			'color_mobile_submenu_text',
			'color_mobile_submenu_hover',
			'color_mobile_submenu_icon',
			'color_mobile_cta_bg',
			'color_mobile_cta_text',
			'color_mobile_cta_hover_bg',
			'color_mobile_cta_hover_text',
		);
	}

	/**
	 * @return array<string, array<int, int>>
	 */
	public static function number_ranges() {
		return array(
			'container_max_width' => array( 800, 1920 ),
			'desktop_height'      => array( 56, 160 ),
			'mobile_height'       => array( 52, 120 ),
			'desktop_logo_width'  => array( 80, 420 ),
			'desktop_logo_height' => array( 24, 120 ),
			'mobile_logo_width'   => array( 70, 320 ),
			'mobile_logo_height'  => array( 24, 100 ),
			'menu_gap'            => array( 8, 80 ),
			'cta_radius'          => array( 0, 40 ),
			'submenu_width'       => array( 160, 420 ),
			'drawer_width'        => array( 260, 600 ),
			'drawer_padding'      => array( 12, 60 ),
			'menu_icon_size'      => array( 20, 48 ),
			'close_icon_size'     => array( 18, 40 ),
			'toggle_button_size'  => array( 40, 64 ),
			'desktop_menu_font_size' => array( 12, 24 ),
			'mobile_menu_font_size' => array( 13, 24 ),
			'submenu_font_size'   => array( 12, 22 ),
			'cta_font_size'       => array( 12, 22 ),
			'menu_font_weight'    => array( 400, 800 ),
			'desktop_menu_font_weight' => array( 400, 900 ),
			'mobile_menu_font_weight' => array( 400, 900 ),
			'desktop_menu_letter_spacing' => array( 0, 3 ),
			'mobile_menu_letter_spacing' => array( 0, 3 ),
			'submenu_icon_size'   => array( 16, 36 ),
			'submenu_animation_duration' => array( 120, 600 ),
			'drawer_animation_duration' => array( 120, 600 ),
		);
	}
}
