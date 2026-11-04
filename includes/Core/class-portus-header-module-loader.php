<?php
/**
 * Carregador dos módulos compartilhados e Pro.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header_Module_Loader {
	/**
	 * Edição ativa.
	 *
	 * @var string
	 */
	private $edition;

	/**
	 * Instâncias registradas para manter referência durante a requisição.
	 *
	 * @var array<int, object>
	 */
	private $modules = array();

	/**
	 * @param string $edition Edição ativa.
	 */
	public function __construct( $edition ) {
		$this->edition = function_exists( 'sanitize_key' )
			? sanitize_key( $edition )
			: preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $edition ) );
	}

	/**
	 * Inicializa os módulos da edição.
	 */
	public function register() {
		$this->register_module( new Portus_Header_Free_Module() );

		if ( 'pro' === $this->edition && class_exists( 'Portus_Header_Pro_Module' ) ) {
			$this->register_module( new Portus_Header_Pro_Module() );
		}
	}

	/**
	 * Registra e guarda uma instância de módulo.
	 *
	 * @param object $module Módulo com método register().
	 */
	private function register_module( $module ) {
		if ( is_callable( array( $module, 'register' ) ) ) {
			$module->register();
		}

		$this->modules[] = $module;
	}

	/**
	 * Retorna os módulos carregados.
	 *
	 * @return array<int, object>
	 */
	public function modules() {
		return $this->modules;
	}
}
