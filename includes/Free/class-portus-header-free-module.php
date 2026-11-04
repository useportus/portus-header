<?php
/**
 * Módulo base compartilhado entre Free e Pro.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header_Free_Module {
	/**
	 * Serviços públicos essenciais que formarão a base da futura edição Free.
	 *
	 * O painel administrativo também vive aqui, pois ele já respeita a edição
	 * ativa e consegue ocultar seções premium na futura Free.
	 *
	 * @var array<int, object>
	 */
	private $services = array();

	/**
	 * Inicializa recursos compartilhados.
	 */
	public function register() {
		$this->services[] = new Portus_Header_Assets();
		$this->services[] = new Portus_Header_Theme_Compatibility();
		$this->services[] = new Portus_Header_Renderer();

		if ( is_admin() ) {
			$this->services[] = new Portus_Header_Admin();
		}
	}

	/**
	 * Retorna serviços registrados.
	 *
	 * @return array<int, object>
	 */
	public function services() {
		return $this->services;
	}
}
