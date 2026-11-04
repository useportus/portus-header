<?php
/**
 * Main plugin orchestrator.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header {
	/**
	 * Singleton instance.
	 *
	 * @var Portus_Header|null
	 */
	private static $instance = null;

	/**
	 * Active edition module loader.
	 *
	 * @var Portus_Header_Module_Loader|null
	 */
	private $module_loader = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return Portus_Header
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initializes modules and hooks.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_menu_location' ) );

		$this->module_loader = new Portus_Header_Module_Loader( Portus_Header_Edition_Manager::current() );
		$this->module_loader->register();
	}

	/**
	 * Registers the native menu location.
	 */
	public function register_menu_location() {
		register_nav_menu(
			'portus-header-primary',
			esc_html__( 'Portus Header — Primary menu', 'portus-header' )
		);
	}

	/**
	 * Returns the active plugin edition.
	 *
	 * @return string
	 */
	public function edition() {
		return Portus_Header_Edition_Manager::current();
	}
}
