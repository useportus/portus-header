<?php
/**
 * Renderização automática e shortcode.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header_Renderer {
	/**
	 * Indica se o cabeçalho já foi produzido nesta requisição.
	 *
	 * @var bool
	 */
	private static $rendered = false;

	/**
	 * Registra hooks de exibição.
	 */
	public function __construct() {
		add_shortcode( 'portus_header', array( $this, 'shortcode' ) );
		add_action( 'wp_body_open', array( $this, 'render_automatic' ), 5 );

		if ( $this->is_pro_edition() ) {
			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ) );
		}
	}

	/**
	 * Renderiza no wp_body_open somente quando o modo e o conteúdo permitem.
	 */
	public function render_automatic() {
		if ( 'automatic' !== Portus_Header_Settings::get( 'display_mode' ) ) {
			return;
		}

		if ( $this->queried_content_has_shortcode() ) {
			return;
		}

		echo $this->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Callback do shortcode.
	 *
	 * @return string
	 */
	public function shortcode() {
		return $this->render();
	}

	/**
	 * Detecta o shortcode antes do wp_body_open em conteúdos singulares.
	 *
	 * @return bool
	 */
	private function queried_content_has_shortcode() {
		$has_shortcode = false;

		if ( is_singular() ) {
			$post = get_queried_object();

			if ( $post instanceof WP_Post ) {
				$has_shortcode = has_shortcode( $post->post_content, 'portus_header' );
			}
		}

		/**
		 * Permite integrar detectores de shortcodes usados por builders ou templates.
		 *
		 * @param bool $has_shortcode Resultado da detecção padrão.
		 */
		return (bool) apply_filters( 'portus_header_content_has_shortcode', $has_shortcode );
	}

	/**
	 * Monta o HTML do cabeçalho uma única vez.
	 *
	 * @return string
	 */
	private function render() {
		if ( self::$rendered ) {
			return '';
		}

		$settings       = Portus_Header_Settings::get_all();
		$is_pro         = $this->is_pro_edition();

		if ( ! $this->is_allowed_on_current_page( $settings ) ) {
			return '';
		}

		self::$rendered = true;
		$desktop_logo   = $this->get_logo( absint( $settings['desktop_logo_id'] ) );
		$mobile_logo    = $this->get_logo( absint( $settings['mobile_logo_id'] ) );
		$menu_icon_src  = $this->get_icon_src( 'menu.svg', $settings['color_mobile_icon'] );
		$close_icon_src = $this->get_icon_src( 'close.svg', $settings['color_mobile_icon'] );
		$search_icon_src = $is_pro ? $this->get_icon_src( 'search.svg', $settings['color_search_icon'] ) : '';
		$search_close_icon_src = $is_pro ? $this->get_icon_src( 'close.svg', $settings['color_search_icon'] ) : '';
		$account_icon_src = $is_pro ? $this->get_icon_src( 'person.svg', $settings['color_account_icon'] ) : '';
		$cart_icon_src  = $is_pro ? $this->get_icon_src( 'bag.svg', $settings['color_cart_icon'] ) : '';
		$products_available = $is_pro && function_exists( 'post_type_exists' ) && post_type_exists( 'product' );
		$woocommerce_active = $is_pro && ( class_exists( 'WooCommerce' ) || function_exists( 'WC' ) );
		$show_search    = $is_pro && ! empty( $settings['search_enabled'] ) && $products_available;
		$show_account   = $is_pro && ! empty( $settings['account_enabled'] ) && $woocommerce_active;
		$show_cart      = $is_pro && ! empty( $settings['cart_enabled'] ) && $woocommerce_active;
		$account_url    = $show_account && function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';
		$cart_url       = $show_cart && function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '';
		$cart_count     = $this->get_cart_count();
		$account_data   = $is_pro ? $this->get_account_data( $account_url, $settings ) : array();
		$account_dropdown_enabled = $show_account && ! empty( $settings['account_dropdown_enabled'] ) && $account_url;
		$mini_cart_enabled = false;

		if ( ! $desktop_logo ) {
			$desktop_logo = $this->get_logo( absint( get_theme_mod( 'custom_logo' ) ) );
		}

		if ( ! $mobile_logo ) {
			$mobile_logo = $desktop_logo;
		}

		$desktop_menu = $this->get_menu_markup( 'portus-header__menu', 'portus-header-desktop-menu' );
		$mobile_menu  = $this->get_menu_markup( 'portus-header-drawer__menu', 'portus-header-mobile-menu' );
		$classes      = array( 'portus-header' );

		if ( ! empty( $settings['sticky'] ) ) {
			$classes[] = 'portus-header--sticky';
		}

		if ( $is_pro && ! empty( $settings['smart_header_enabled'] ) ) {
			$classes[] = 'portus-header--smart';
		}

		if ( $is_pro && ! empty( $settings['mega_menu_preset'] ) ) {
			$classes[] = 'portus-header--mega-preset-' . sanitize_html_class( $settings['mega_menu_preset'] );
		}

		if ( ! empty( $settings['layout_preset'] ) ) {
			$classes[] = 'portus-header--layout-' . sanitize_html_class( $settings['layout_preset'] );
		}

		$transparent_active = $is_pro && ! empty( $settings['transparent_enabled'] )
			&& ( empty( $settings['transparent_home_only'] ) || ( function_exists( 'is_front_page' ) && is_front_page() ) );

		if ( $transparent_active ) {
			$classes[] = 'portus-header--transparent';
		}

		$header_data = array(
			'smart'        => $is_pro && ! empty( $settings['smart_header_enabled'] ) ? 'true' : 'false',
			'hide_scroll'  => $is_pro && ! empty( $settings['hide_on_scroll'] ) ? 'true' : 'false',
			'shrink_scroll'=> $is_pro && ! empty( $settings['shrink_on_scroll'] ) ? 'true' : 'false',
			'threshold'    => isset( $settings['scroll_threshold'] ) ? absint( $settings['scroll_threshold'] ) : 0,
		);

		ob_start();
		include PORTUS_HEADER_PATH . 'templates/free/header.php';

		return (string) ob_get_clean();
	}

	/**
	 * Aplica regras simples de inclusão e exclusão por página.
	 *
	 * @param array<string, mixed> $settings Configurações.
	 * @return bool
	 */
	private function is_allowed_on_current_page( $settings ) {
		$scope = isset( $settings['smart_scope'] ) ? $settings['smart_scope'] : 'all';

		if ( 'all' === $scope ) {
			return true;
		}

		$ids        = array_filter( array_map( 'absint', explode( ',', (string) $settings['smart_page_ids'] ) ) );
		$current_id = function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
		$matches    = $current_id && in_array( $current_id, $ids, true );

		return 'include' === $scope ? $matches : ! $matches;
	}

	/**
	 * Gera um SVG local como imagem incorporada com a cor configurada.
	 *
	 * @param string $filename Nome permitido do arquivo.
	 * @param string $color    Cor hexadecimal.
	 * @return string
	 */
	private function get_icon_src( $filename, $color ) {
		$allowed = array(
			'menu.svg',
			'close.svg',
			'search.svg',
			'person.svg',
			'bag.svg',
			'person-plus-fill.svg',
			'bag-check-fill.svg',
			'cloud-arrow-down-fill.svg',
			'geo-alt-fill.svg',
			'door-open-fill.svg',
		);

		if ( ! in_array( $filename, $allowed, true ) ) {
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
	 * Retorna a quantidade atual no carrinho.
	 *
	 * @return int
	 */
	private function get_cart_count() {
		if ( function_exists( 'WC' ) && WC() && WC()->cart ) {
			return absint( WC()->cart->get_cart_contents_count() );
		}

		return 0;
	}

	/**
	 * Retorna dados contextuais da conta WooCommerce.
	 *
	 * @param string               $account_url URL da página Minha conta.
	 * @param array<string, mixed> $settings    Configurações atuais.
	 * @return array<string, mixed>
	 */
	private function get_account_data( $account_url, $settings ) {
		$is_logged_in       = function_exists( 'is_user_logged_in' ) && is_user_logged_in();
		$name               = '';
		$avatar_url         = '';
		$links              = array();
		$account_icon_color = isset( $settings['color_account_icon'] ) ? $settings['color_account_icon'] : '#C0A821';
		$link_icon          = function ( $filename ) use ( $account_icon_color ) {
			return $this->get_icon_src( $filename, $account_icon_color );
		};

		if ( $is_logged_in && function_exists( 'wp_get_current_user' ) ) {
			$user = wp_get_current_user();
			if ( $user && ! empty( $user->display_name ) ) {
				$name = $user->display_name;
			}

			if ( $user && ! empty( $user->ID ) && function_exists( 'get_avatar_url' ) ) {
				$avatar_url = get_avatar_url(
					absint( $user->ID ),
					array(
						'size' => 96,
					)
				);
			}
		}

		if ( $is_logged_in ) {
			$links[] = array(
				'label'    => esc_html__( 'Minha conta', 'portus-header' ),
				'url'      => $account_url,
				'icon_src' => $link_icon( 'person.svg' ),
			);

			if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
				$links[] = array(
					'label'    => esc_html__( 'Pedidos', 'portus-header' ),
					'url'      => wc_get_account_endpoint_url( 'orders' ),
					'icon_src' => $link_icon( 'bag-check-fill.svg' ),
				);
				$links[] = array(
					'label'    => esc_html__( 'Downloads', 'portus-header' ),
					'url'      => wc_get_account_endpoint_url( 'downloads' ),
					'icon_src' => $link_icon( 'cloud-arrow-down-fill.svg' ),
				);
				$links[] = array(
					'label'    => esc_html__( 'Endereços', 'portus-header' ),
					'url'      => wc_get_account_endpoint_url( 'edit-address' ),
					'icon_src' => $link_icon( 'geo-alt-fill.svg' ),
				);
			}

			$links[] = array(
				'label'    => esc_html__( 'Sair', 'portus-header' ),
				'url'      => function_exists( 'wc_logout_url' ) ? wc_logout_url() : ( function_exists( 'wp_logout_url' ) ? wp_logout_url( home_url( '/' ) ) : $account_url ),
				'class'    => 'is-danger',
				'icon_src' => $link_icon( 'door-open-fill.svg' ),
			);
		} else {
			$links[] = array(
				'label'    => esc_html__( 'Entrar ou cadastrar', 'portus-header' ),
				'url'      => $account_url,
				'class'    => 'is-primary',
				'icon_src' => $link_icon( 'person-plus-fill.svg' ),
			);
		}

		return array(
			'logged_in'  => $is_logged_in,
			'name'       => $name,
			'avatar_url' => $avatar_url,
			'label'      => $is_logged_in ? esc_html__( 'Minha conta', 'portus-header' ) : esc_html__( 'Entrar', 'portus-header' ),
			/* translators: %s: customer display name. */
			'greeting'   => $is_logged_in && $name ? sprintf( esc_html__( 'Olá, %s', 'portus-header' ), $name ) : esc_html__( 'Acesse sua conta Portus', 'portus-header' ),
			'summary'    => $is_logged_in ? esc_html__( 'Gerencie pedidos, downloads e dados da conta.', 'portus-header' ) : esc_html__( 'Entre para acessar pedidos, downloads e suporte.', 'portus-header' ),
			'links'      => $links,
		);
	}

	/**
	 * Retorna o corpo atual do mini-cart.
	 *
	 * @return string
	 */
	private function get_mini_cart_body() {
		if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
			return '<p class="portus-header-mini-cart__empty">' . esc_html__( 'Seu carrinho está vazio.', 'portus-header' ) . '</p>';
		}

		ob_start();
		woocommerce_mini_cart();

		return (string) ob_get_clean();
	}

	/**
	 * Atualiza o contador junto aos fragments AJAX do WooCommerce.
	 *
	 * @param array<string, string> $fragments Fragments atuais.
	 * @return array<string, string>
	 */
	public function cart_fragments( $fragments ) {
		if ( ! $this->is_pro_edition() ) {
			return $fragments;
		}

		$count = $this->get_cart_count();
		$fragments['.portus-header__cart-count'] = '<span class="portus-header__cart-count" data-count="' . esc_attr( $count ) . '">' . esc_html( $count ) . '</span>';

		return $fragments;
	}

	/**
	 * Obtém dados seguros da imagem de logo.
	 *
	 * @param int $attachment_id ID do anexo.
	 * @return array<string, string>|null
	 */
	private function get_logo( $attachment_id ) {
		if ( ! $attachment_id ) {
			return null;
		}

		$url = wp_get_attachment_image_url( $attachment_id, 'full' );

		if ( ! $url ) {
			return null;
		}

		$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

		return array(
			'url' => $url,
			'alt' => $alt ? $alt : get_bloginfo( 'name' ),
		);
	}

	/**
	 * Gera duas instâncias do menu sem IDs HTML duplicados nos itens.
	 *
	 * @param string $menu_class Classe da lista.
	 * @param string $menu_id    ID único da lista.
	 * @return string
	 */
	private function get_menu_markup( $menu_class, $menu_id ) {
		if ( ! has_nav_menu( 'portus-header-primary' ) ) {
			return '';
		}

		add_filter( 'nav_menu_item_id', '__return_empty_string' );

		$args = array(
			'theme_location' => 'portus-header-primary',
			'container'      => false,
			'menu_class'     => $menu_class,
			'menu_id'        => $menu_id,
			'fallback_cb'    => false,
			'echo'           => false,
			'depth'          => 3,
		);

		if ( $this->is_pro_edition() && class_exists( 'Portus_Header_Walker_Nav_Menu' ) ) {
			$args['walker'] = new Portus_Header_Walker_Nav_Menu();
		}

		$markup = wp_nav_menu( $args );

		remove_filter( 'nav_menu_item_id', '__return_empty_string' );

		return is_string( $markup ) ? $markup : '';
	}

	/**
	 * Informa se a renderização deve carregar recursos Pro.
	 *
	 * @return bool
	 */
	private function is_pro_edition() {
		return ! class_exists( 'Portus_Header_Edition_Manager' ) || Portus_Header_Edition_Manager::is_pro();
	}
}
