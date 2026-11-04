<?php
/**
 * Controle da edição ativa e leitura do manifesto Free/Pro.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header_Edition_Manager {
	/**
	 * Edição padrão enquanto o produto privado atual é o Pro.
	 */
	const DEFAULT_EDITION = 'pro';

	/**
	 * Cache do manifesto.
	 *
	 * @var array<string, mixed>|null
	 */
	private static $manifest = null;

	/**
	 * Retorna a edição ativa.
	 *
	 * Nesta fase a edição Pro continua fixa para não alterar o comportamento
	 * público. O filtro existe apenas para testes e preparação do build futuro.
	 *
	 * @return string
	 */
	public static function current() {
		$manifest = self::manifest();
		$edition  = isset( $manifest['current_edition'] ) ? self::sanitize_edition_key( (string) $manifest['current_edition'] ) : self::DEFAULT_EDITION;

		if ( ! self::exists( $edition ) ) {
			$edition = self::DEFAULT_EDITION;
		}

		/**
		 * Permite simular outra edição em testes sem mudar o produto instalado.
		 *
		 * @param string $edition Edição ativa.
		 */
		if ( function_exists( 'apply_filters' ) ) {
			$edition = (string) apply_filters( 'portus_header_current_edition', $edition );
		}

		$edition = self::sanitize_edition_key( $edition );

		return self::exists( $edition ) ? $edition : self::DEFAULT_EDITION;
	}

	/**
	 * Verifica se a edição existe no manifesto.
	 *
	 * @param string $edition Edição.
	 * @return bool
	 */
	public static function exists( $edition ) {
		$manifest = self::manifest();

		return isset( $manifest['editions'][ $edition ] ) && is_array( $manifest['editions'][ $edition ] );
	}

	/**
	 * Informa se a edição ativa deve carregar recursos Pro.
	 *
	 * @return bool
	 */
	public static function is_pro() {
		return 'pro' === self::current();
	}

	/**
	 * Retorna a definição da edição.
	 *
	 * @param string|null $edition Edição opcional.
	 * @return array<string, mixed>
	 */
	public static function edition( $edition = null ) {
		$edition  = $edition ? self::sanitize_edition_key( $edition ) : self::current();
		$manifest = self::manifest();

		return self::exists( $edition ) ? $manifest['editions'][ $edition ] : array();
	}

	/**
	 * Lê o manifesto de edições com fallback seguro.
	 *
	 * @return array<string, mixed>
	 */
	public static function manifest() {
		if ( null !== self::$manifest ) {
			return self::$manifest;
		}

		$path = PORTUS_HEADER_PATH . 'config/editions.json';

		if ( is_readable( $path ) ) {
			$json = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$data = json_decode( (string) $json, true );

			if ( is_array( $data ) && isset( $data['editions'] ) && is_array( $data['editions'] ) ) {
				self::$manifest = $data;
				return self::$manifest;
			}
		}

		self::$manifest = array(
			'current_edition' => self::DEFAULT_EDITION,
			'editions'        => array(
				'pro' => array(
					'status'    => 'current',
					'buildable' => true,
				),
			),
		);

		return self::$manifest;
	}

	/**
	 * Normaliza uma chave de edição sem depender do bootstrap completo do WP.
	 *
	 * @param string $edition Edição.
	 * @return string
	 */
	private static function sanitize_edition_key( $edition ) {
		if ( function_exists( 'sanitize_key' ) ) {
			return sanitize_key( $edition );
		}

		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $edition ) );
	}
}
