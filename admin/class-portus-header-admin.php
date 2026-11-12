<?php
/**
 * Painel administrativo do Portus Header.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portus_Header_Admin {
	/**
	 * Registra hooks administrativos.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'onboarding_notice' ) );
		add_action( 'admin_notices', array( $this, 'license_notice' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( PORTUS_HEADER_FILE ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Orientação inicial após a ativação.
	 */
	public function onboarding_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! get_option( 'portus_header_onboarding_pending' ) ) {
			return;
		}

		$url = admin_url( 'themes.php?page=portus-header' );
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Portus Header está pronto para configurar.', 'portus-header' ); ?></strong>
				<a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Abrir configuração guiada', 'portus-header' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Exibe retorno das ações de licença.
	 */
	public function license_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$message_key = get_transient( 'portus_header_admin_notice_' . get_current_user_id() );

		if ( ! $message_key ) {
			return;
		}

		delete_transient( 'portus_header_admin_notice_' . get_current_user_id() );
		$message_key = sanitize_key( $message_key );
		$messages    = array(
			'license_saved'       => array( 'success', esc_html__( 'Configurações de licença salvas.', 'portus-header' ) ),
			'license_activated'   => array( 'success', esc_html__( 'Licença ativa para este domínio.', 'portus-header' ) ),
			'license_deactivated' => array( 'success', esc_html__( 'Licença desativada neste domínio.', 'portus-header' ) ),
			'license_checked'     => array( 'success', esc_html__( 'Consulta de atualização concluída.', 'portus-header' ) ),
			'license_failed'      => array( 'error', esc_html__( 'Não foi possível validar a licença no servidor Portus.', 'portus-header' ) ),
			'license_missing'     => array( 'warning', esc_html__( 'Informe a chave de licença antes de ativar ou verificar atualizações.', 'portus-header' ) ),
		);

		if ( empty( $messages[ $message_key ] ) ) {
			return;
		}

		list( $type, $message ) = $messages[ $message_key ];
		?>
		<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Registra a opção única do plugin.
	 */
	public function register_settings() {
		register_setting(
			'portus_header_settings_group',
			Portus_Header_Settings::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'Portus_Header_Settings', 'sanitize' ),
				'default'           => Portus_Header_Settings::defaults(),
			)
		);
	}

	/**
	 * Adiciona a página em Aparência.
	 */
	public function add_page() {
		add_theme_page(
			esc_html__( 'Portus Header', 'portus-header' ),
			esc_html__( 'Portus Header', 'portus-header' ),
			'manage_options',
			'portus-header',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Carrega somente os assets do painel do plugin.
	 *
	 * @param string $hook_suffix Identificador da tela.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( 'appearance_page_portus-header' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style(
			'portus-header-admin',
			PORTUS_HEADER_URL . 'assets/css/portus-header-admin.css',
			array(),
			PORTUS_HEADER_VERSION
		);
		wp_enqueue_script(
			'portus-header-admin',
			PORTUS_HEADER_URL . 'assets/js/portus-header-admin.js',
			array( 'media-editor' ),
			PORTUS_HEADER_VERSION,
			true
		);
		wp_localize_script(
			'portus-header-admin',
			'PortusHeaderAdmin',
			array(
				'defaults' => Portus_Header_Settings::defaults(),
				'presets'  => $this->get_visual_presets(),
				'i18n'     => array(
					'mediaTitle'  => esc_html__( 'Selecionar logo', 'portus-header' ),
					'mediaButton' => esc_html__( 'Usar esta imagem', 'portus-header' ),
					'restoreColors' => esc_html__( 'Restaurar somente as cores oficiais da Portus?', 'portus-header' ),
					'restoreSizes' => esc_html__( 'Restaurar somente medidas, fontes e espaçamentos oficiais da Portus?', 'portus-header' ),
					'restoreVisual' => esc_html__( 'Restaurar todo o visual oficial da Portus?', 'portus-header' ),
					'preset'      => esc_html__( 'Aplicar este preset visual? Revise o preview e clique em Salvar configurações para publicar.', 'portus-header' ),
					'importError' => esc_html__( 'O arquivo não contém configurações válidas do Portus Header.', 'portus-header' ),
					'importDone'  => esc_html__( 'Configurações importadas. Revise e clique em Salvar configurações.', 'portus-header' ),
				),
			)
		);
	}

	/**
	 * Adiciona atalho na lista de plugins.
	 *
	 * @param array<int, string> $links Links atuais.
	 * @return array<int, string>
	 */
	public function add_settings_link( $links ) {
		array_unshift(
			$links,
			'<a href="' . esc_url( admin_url( 'themes.php?page=portus-header' ) ) . '">' . esc_html__( 'Configurações', 'portus-header' ) . '</a>'
		);

		return $links;
	}

	/**
	 * Presets visuais aplicados no painel antes de salvar.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_visual_presets() {
		return array(
			'portus' => array(
				'label'       => esc_html__( 'Portus padrão', 'portus-header' ),
				'description' => esc_html__( 'Identidade oficial: azul Portus, dourado no CTA e dimensões equilibradas.', 'portus-header' ),
				'settings'    => array(
					'layout_preset'       => 'standard',
					'color_header_bg'     => '#00053A',
					'color_text'          => '#FFFFFF',
					'color_hover'         => '#C0A821',
					'color_cta_bg'        => '#C0A821',
					'color_cta_text'      => '#00053A',
					'color_cta_hover_bg'  => '#D3C45A',
					'desktop_height'      => 82,
					'mobile_height'       => 72,
					'menu_gap'            => 30,
					'cta_radius'          => 6,
					'desktop_menu_font_size' => 15,
					'desktop_menu_font_weight' => 600,
					'header_action_gap'   => 10,
				),
			),
			'compact' => array(
				'label'       => esc_html__( 'Compacto', 'portus-header' ),
				'description' => esc_html__( 'Header mais baixo e denso para lojas com muitos links.', 'portus-header' ),
				'settings'    => array(
					'layout_preset'       => 'compact',
					'desktop_height'      => 68,
					'mobile_height'       => 64,
					'desktop_logo_width'  => 180,
					'desktop_logo_height' => 44,
					'mobile_logo_width'   => 150,
					'mobile_logo_height'  => 40,
					'menu_gap'            => 20,
					'cta_radius'          => 6,
					'desktop_menu_font_size' => 14,
					'cta_font_size'       => 14,
					'header_action_gap'   => 6,
					'search_icon_size'    => 22,
					'account_icon_size'   => 21,
					'cart_icon_size'      => 22,
				),
			),
			'centered' => array(
				'label'       => esc_html__( 'Centralizado', 'portus-header' ),
				'description' => esc_html__( 'Logo à esquerda, menu perfeitamente central e ações alinhadas à direita.', 'portus-header' ),
				'settings'    => array(
					'layout_preset'       => 'centered',
					'desktop_height'      => 86,
					'container_max_width' => 1280,
					'menu_gap'            => 34,
					'desktop_menu_font_size' => 15,
					'desktop_menu_font_weight' => 700,
					'header_action_gap'   => 12,
					'mega_menu_width'     => 1160,
				),
			),
			'cta' => array(
				'label'       => esc_html__( 'CTA forte', 'portus-header' ),
				'description' => esc_html__( 'Mais destaque comercial para o botão principal e ações do topo.', 'portus-header' ),
				'settings'    => array(
					'layout_preset'       => 'cta',
					'cta_enabled'         => 1,
					'color_cta_bg'        => '#C0A821',
					'color_cta_text'      => '#00053A',
					'color_cta_hover_bg'  => '#FFFFFF',
					'color_cta_hover_text'=> '#00053A',
					'desktop_height'      => 84,
					'cta_radius'          => 12,
					'cta_font_size'       => 16,
					'header_action_gap'   => 14,
					'menu_gap'            => 28,
				),
			),
		);
	}

	/**
	 * Renderiza o painel.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = Portus_Header_Settings::get_all();
		$option   = Portus_Header_Settings::OPTION_NAME;
		$presets  = $this->get_visual_presets();
		$is_pro   = $this->is_pro_edition();
		$pro_tabs = $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ? Portus_Header_Pro_Admin::tabs() : array();
		$product_title = $is_pro ? __( 'Portus Header Pro', 'portus-header' ) : __( 'Portus Header', 'portus-header' );
			$preview_logo_id  = ! empty( $settings['desktop_logo_id'] ) ? absint( $settings['desktop_logo_id'] ) : 0;
			$preview_logo_id  = ! $preview_logo_id && ! empty( $settings['mobile_logo_id'] ) ? absint( $settings['mobile_logo_id'] ) : $preview_logo_id;
			$preview_logo_id  = ! $preview_logo_id ? absint( get_theme_mod( 'custom_logo' ) ) : $preview_logo_id;
			$preview_logo_url = $preview_logo_id ? wp_get_attachment_image_url( $preview_logo_id, 'medium' ) : '';
		update_option( 'portus_header_onboarding_pending', 0, false );
		?>
		<div class="wrap portus-header-admin portus-header-admin--<?php echo esc_attr( $is_pro ? 'pro' : 'free' ); ?>" data-portus-admin-edition="<?php echo esc_attr( $is_pro ? 'pro' : 'free' ); ?>">
			<header class="portus-header-admin__brand">
				<img class="portus-header-admin__brand-icon" src="<?php echo esc_url( PORTUS_HEADER_URL . 'assets/images/portus-header/icon-256x256.png' ); ?>" alt="<?php echo esc_attr__( 'Ícone do Portus Header', 'portus-header' ); ?>">
				<div class="portus-header-admin__brand-content">
					<div class="portus-header-admin__brand-title-row">
						<h1><?php echo esc_html( $product_title ); ?></h1>
					</div>
					<p class="portus-header-admin__intro">
						<?php esc_html_e( 'Configure a exibição, identidade visual e comportamento do cabeçalho.', 'portus-header' ); ?>
					</p>
				</div>
			</header>

			<?php settings_errors(); ?>

			<?php if ( 'automatic' === $settings['display_mode'] && ! $this->theme_appears_to_support_body_open() ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<strong><?php esc_html_e( 'Não encontramos wp_body_open no tema ativo.', 'portus-header' ); ?></strong>
						<?php esc_html_e( 'A inserção automática pode não funcionar. Se o cabeçalho não aparecer, selecione o modo Shortcode e adicione [portus_header] ao conteúdo ou template.', 'portus-header' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<form action="options.php" method="post">
				<?php settings_fields( 'portus_header_settings_group' ); ?>

				<div class="portus-header-admin__toolbar">
					<nav class="portus-header-admin__tabs" aria-label="<?php esc_attr_e( 'Seções de configuração', 'portus-header' ); ?>">
						<button type="button" class="is-active" data-portus-admin-tab="general"><?php esc_html_e( 'Geral', 'portus-header' ); ?></button>
						<?php foreach ( $pro_tabs as $tab_key => $tab_label ) : ?>
							<?php if ( 'commerce' === $tab_key ) : ?>
								<button type="button" data-portus-admin-tab="<?php echo esc_attr( $tab_key ); ?>"><?php echo esc_html( $tab_label ); ?></button>
							<?php endif; ?>
						<?php endforeach; ?>
						<button type="button" data-portus-admin-tab="appearance"><?php esc_html_e( 'Visual', 'portus-header' ); ?></button>
					<?php foreach ( $pro_tabs as $tab_key => $tab_label ) : ?>
							<?php if ( 'commerce' !== $tab_key ) : ?>
								<button type="button" data-portus-admin-tab="<?php echo esc_attr( $tab_key ); ?>"><?php echo esc_html( $tab_label ); ?></button>
							<?php endif; ?>
						<?php endforeach; ?>
						<button type="button" data-portus-admin-tab="advanced"><?php esc_html_e( 'Avançado', 'portus-header' ); ?></button>
					</nav>
					<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) && Portus_Header_Pro_Admin::supports_transfer() ) : ?>
						<?php Portus_Header_Pro_Admin::render_transfer_controls(); ?>
					<?php endif; ?>
				</div>

				<div class="portus-header-admin__live-preview" data-portus-live-preview>
					<div class="portus-header-admin__preview-top">
						<span><?php esc_html_e( 'Pré-visualização ao vivo', 'portus-header' ); ?></span>
						<code data-portus-preview-layout-label><?php esc_html_e( 'Portus padrão', 'portus-header' ); ?></code>
					</div>
					<div class="portus-header-admin__preview-stage">
						<div class="portus-header-admin__preview-header" data-portus-preview-header>
							<span class="portus-header-admin__preview-logo" data-portus-preview-logo>
								<?php if ( $preview_logo_url ) : ?>
									<img src="<?php echo esc_url( $preview_logo_url ); ?>" alt="">
									<?php else : ?>
										<span class="portus-header-admin__preview-logo-fallback">
											<b><?php esc_html_e( 'Portus', 'portus-header' ); ?></b>
										</span>
									<?php endif; ?>
							</span>
							<nav class="portus-header-admin__preview-nav" aria-label="<?php esc_attr_e( 'Menu de exemplo', 'portus-header' ); ?>">
								<span><?php esc_html_e( 'Início', 'portus-header' ); ?></span>
								<span><?php esc_html_e( 'Loja', 'portus-header' ); ?></span>
								<span class="has-children"><?php esc_html_e( 'Plugins', 'portus-header' ); ?></span>
								<span class="has-children"><?php esc_html_e( 'Docs', 'portus-header' ); ?></span>
								<span><?php esc_html_e( 'Suporte', 'portus-header' ); ?></span>
							</nav>
							<div class="portus-header-admin__preview-actions">
								<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
									<?php Portus_Header_Pro_Admin::render_preview_actions(); ?>
								<?php endif; ?>
								<strong data-portus-preview-cta><?php echo esc_html( $settings['cta_text'] ); ?></strong>
							</div>
						</div>
						<div class="portus-header-admin__preview-panels" aria-hidden="true">
							<div>
								<b><?php esc_html_e( 'Submenu', 'portus-header' ); ?></b>
								<span><?php esc_html_e( 'Cards, links e hover da marca', 'portus-header' ); ?></span>
							</div>
							<div>
								<b><?php esc_html_e( 'Mobile', 'portus-header' ); ?></b>
								<span><?php esc_html_e( 'Drawer branco, ícones dourados', 'portus-header' ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<section class="portus-header-admin__card" data-portus-admin-section="general">
					<h2><?php esc_html_e( 'Exibição', 'portus-header' ); ?></h2>
					<div class="portus-header-admin__grid">
						<div class="portus-header-admin__field">
							<label for="portus-display-mode"><?php esc_html_e( 'Modo de exibição', 'portus-header' ); ?></label>
							<select id="portus-display-mode" name="<?php echo esc_attr( $option ); ?>[display_mode]">
								<option value="automatic" <?php selected( $settings['display_mode'], 'automatic' ); ?>><?php esc_html_e( 'Automático — wp_body_open', 'portus-header' ); ?></option>
								<option value="shortcode" <?php selected( $settings['display_mode'], 'shortcode' ); ?>><?php esc_html_e( 'Shortcode', 'portus-header' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'No modo automático, a presença de [portus_header] no conteúdo impede a inserção pelo wp_body_open.', 'portus-header' ); ?></p>
						</div>

						<div class="portus-header-admin__field">
							<label><?php esc_html_e( 'Shortcode', 'portus-header' ); ?></label>
							<code>[portus_header]</code>
							<p class="description"><?php esc_html_e( 'Use como alternativa em páginas, widgets ou templates.', 'portus-header' ); ?></p>
						</div>
					</div>

					<label class="portus-header-admin__check">
						<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[sticky]" value="1" <?php checked( $settings['sticky'], 1 ); ?>>
						<span><?php esc_html_e( 'Manter o cabeçalho fixo durante a rolagem', 'portus-header' ); ?></span>
					</label>

					<?php if ( ! $is_pro ) : ?>
						<div class="portus-header-admin__grid portus-header-admin__subheading">
							<div class="portus-header-admin__field">
								<label for="portus-smart-scope"><?php esc_html_e( 'Exibir em', 'portus-header' ); ?></label>
								<select id="portus-smart-scope" name="<?php echo esc_attr( $option ); ?>[smart_scope]">
									<option value="all" <?php selected( $settings['smart_scope'], 'all' ); ?>><?php esc_html_e( 'Todas as páginas', 'portus-header' ); ?></option>
									<option value="include" <?php selected( $settings['smart_scope'], 'include' ); ?>><?php esc_html_e( 'Somente IDs informados', 'portus-header' ); ?></option>
									<option value="exclude" <?php selected( $settings['smart_scope'], 'exclude' ); ?>><?php esc_html_e( 'Exceto IDs informados', 'portus-header' ); ?></option>
								</select>
							</div>
							<div class="portus-header-admin__field">
								<label for="portus-smart-page-ids"><?php esc_html_e( 'IDs de páginas', 'portus-header' ); ?></label>
								<input id="portus-smart-page-ids" type="text" name="<?php echo esc_attr( $option ); ?>[smart_page_ids]" value="<?php echo esc_attr( $settings['smart_page_ids'] ); ?>" placeholder="12, 34">
								<p class="description"><?php esc_html_e( 'Separe os IDs por vírgula. Esta regra também vale no modo automático.', 'portus-header' ); ?></p>
							</div>
						</div>
					<?php endif; ?>
				</section>

				<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
					<?php Portus_Header_Pro_Admin::render_commerce_section( $this, $settings, $option ); ?>
				<?php endif; ?>

				<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
					<?php Portus_Header_Pro_Admin::render_presets_section( $presets, $settings, $option ); ?>
				<?php endif; ?>

				<section class="portus-header-admin__card" data-portus-admin-section="appearance" hidden>
					<h2><?php esc_html_e( 'Logos', 'portus-header' ); ?></h2>
					<div class="portus-header-admin__grid">
						<?php $this->render_media_field( 'desktop_logo_id', esc_html__( 'Logo desktop', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_media_field( 'mobile_logo_id', esc_html__( 'Logo mobile', 'portus-header' ), $settings, $option ); ?>
					</div>
					<p class="description"><?php esc_html_e( 'Sem logo desktop, será usado o logo personalizado do tema. Sem logo mobile, será usado o logo desktop.', 'portus-header' ); ?></p>
				</section>

				<section class="portus-header-admin__card" data-portus-admin-section="appearance" hidden>
					<h2><?php esc_html_e( 'Botão CTA', 'portus-header' ); ?></h2>
					<label class="portus-header-admin__check">
						<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[cta_enabled]" value="1" <?php checked( $settings['cta_enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Exibir botão CTA', 'portus-header' ); ?></span>
					</label>
					<div class="portus-header-admin__grid">
						<div class="portus-header-admin__field">
							<label for="portus-cta-text"><?php esc_html_e( 'Texto', 'portus-header' ); ?></label>
							<input id="portus-cta-text" type="text" class="regular-text" name="<?php echo esc_attr( $option ); ?>[cta_text]" value="<?php echo esc_attr( $settings['cta_text'] ); ?>">
						</div>
						<div class="portus-header-admin__field">
							<label for="portus-cta-url"><?php esc_html_e( 'URL', 'portus-header' ); ?></label>
							<input id="portus-cta-url" type="url" class="regular-text" name="<?php echo esc_attr( $option ); ?>[cta_url]" value="<?php echo esc_attr( $settings['cta_url'] ); ?>" placeholder="https://">
						</div>
					</div>
					<label class="portus-header-admin__check">
						<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[cta_new_tab]" value="1" <?php checked( $settings['cta_new_tab'], 1 ); ?>>
						<span><?php esc_html_e( 'Abrir em nova aba', 'portus-header' ); ?></span>
					</label>
				</section>

				<section class="portus-header-admin__card" data-portus-admin-section="appearance" hidden>
					<div class="portus-header-admin__heading-row">
						<h2><?php esc_html_e( 'Aparência avançada', 'portus-header' ); ?></h2>
						<div class="portus-header-admin__restore-actions">
							<button type="button" class="button" data-portus-restore-group="colors"><?php esc_html_e( 'Restaurar cores Portus', 'portus-header' ); ?></button>
							<button type="button" class="button" data-portus-restore-group="sizes"><?php esc_html_e( 'Restaurar medidas Portus', 'portus-header' ); ?></button>
							<button type="button" class="button button-primary" data-portus-restore-group="visual"><?php esc_html_e( 'Restaurar visual Portus', 'portus-header' ); ?></button>
						</div>
					</div>

					<h3><?php esc_html_e( 'Desktop', 'portus-header' ); ?></h3>
					<div class="portus-header-admin__colors">
						<?php $this->render_color_field( 'color_header_bg', esc_html__( 'Fundo do cabeçalho', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_text', esc_html__( 'Texto principal', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_hover', esc_html__( 'Hover e item ativo', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_submenu_bg', esc_html__( 'Fundo do submenu', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_submenu_text', esc_html__( 'Texto do submenu', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_submenu_hover', esc_html__( 'Hover do submenu', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_cta_bg', esc_html__( 'Fundo do CTA', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_cta_text', esc_html__( 'Texto do CTA', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_cta_hover_bg', esc_html__( 'Fundo hover do CTA', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_cta_hover_text', esc_html__( 'Texto hover do CTA', 'portus-header' ), $settings, $option ); ?>
					</div>

					<h3 class="portus-header-admin__subheading"><?php esc_html_e( 'Mobile', 'portus-header' ); ?></h3>
					<div class="portus-header-admin__colors">
						<?php $this->render_color_field( 'color_mobile_header_bg', esc_html__( 'Fundo do header mobile', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_toggle_bg', esc_html__( 'Fundo do botão menu', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_icon', esc_html__( 'Ícones', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_bg', esc_html__( 'Fundo do drawer', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_text', esc_html__( 'Texto da navegação', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_hover', esc_html__( 'Hover e item ativo', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_submenu_bg', esc_html__( 'Fundo do submenu', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_submenu_text', esc_html__( 'Texto do submenu', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_submenu_hover', esc_html__( 'Hover do submenu', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_submenu_icon', esc_html__( 'Setas dos submenus', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_cta_bg', esc_html__( 'Fundo do CTA', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_cta_text', esc_html__( 'Texto do CTA', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_cta_hover_bg', esc_html__( 'Fundo hover do CTA', 'portus-header' ), $settings, $option ); ?>
						<?php $this->render_color_field( 'color_mobile_cta_hover_text', esc_html__( 'Texto hover do CTA', 'portus-header' ), $settings, $option ); ?>
					</div>

					<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
						<?php Portus_Header_Pro_Admin::render_search_color_fields( $this, $settings, $option ); ?>
					<?php endif; ?>
					<p class="description"><?php esc_html_e( 'Todas as cores são validadas pelo WordPress e aplicadas como variáveis CSS isoladas no plugin.', 'portus-header' ); ?></p>
				</section>

				<section class="portus-header-admin__card" data-portus-admin-section="appearance" hidden>
					<h2><?php esc_html_e( 'Layout e dimensões', 'portus-header' ); ?></h2>
					<div class="portus-header-admin__field portus-header-admin__subheading">
						<label for="portus-font-family"><?php esc_html_e( 'Fonte do header', 'portus-header' ); ?></label>
						<input id="portus-font-family" type="text" class="regular-text" name="<?php echo esc_attr( $option ); ?>[font_family]" value="<?php echo esc_attr( $settings['font_family'] ); ?>" placeholder="inherit">
						<p class="description"><?php esc_html_e( 'Use inherit para herdar do tema ou informe uma pilha segura, por exemplo: Inter, Arial, sans-serif.', 'portus-header' ); ?></p>
					</div>
					<div class="portus-header-admin__numbers">
						<?php $this->render_number_field( 'container_max_width', esc_html__( 'Largura máxima do conteúdo', 'portus-header' ), 800, 1920, $settings, $option ); ?>
						<?php $this->render_number_field( 'desktop_height', esc_html__( 'Altura desktop', 'portus-header' ), 56, 160, $settings, $option ); ?>
						<?php $this->render_number_field( 'mobile_height', esc_html__( 'Altura mobile', 'portus-header' ), 52, 120, $settings, $option ); ?>
						<?php $this->render_number_field( 'desktop_logo_width', esc_html__( 'Largura máxima do logo desktop', 'portus-header' ), 80, 420, $settings, $option ); ?>
						<?php $this->render_number_field( 'desktop_logo_height', esc_html__( 'Altura máxima do logo desktop', 'portus-header' ), 24, 120, $settings, $option ); ?>
						<?php $this->render_number_field( 'mobile_logo_width', esc_html__( 'Largura máxima do logo mobile', 'portus-header' ), 70, 320, $settings, $option ); ?>
						<?php $this->render_number_field( 'mobile_logo_height', esc_html__( 'Altura máxima do logo mobile', 'portus-header' ), 24, 100, $settings, $option ); ?>
						<?php $this->render_number_field( 'menu_gap', esc_html__( 'Espaço entre links', 'portus-header' ), 8, 80, $settings, $option ); ?>
						<?php $this->render_number_field( 'cta_radius', esc_html__( 'Raio dos botões', 'portus-header' ), 0, 40, $settings, $option ); ?>
						<?php $this->render_number_field( 'submenu_width', esc_html__( 'Largura mínima do submenu', 'portus-header' ), 160, 420, $settings, $option ); ?>
						<?php $this->render_number_field( 'drawer_width', esc_html__( 'Largura do menu mobile', 'portus-header' ), 260, 600, $settings, $option ); ?>
						<?php $this->render_number_field( 'drawer_padding', esc_html__( 'Espaçamento interno mobile', 'portus-header' ), 12, 60, $settings, $option ); ?>
						<?php $this->render_number_field( 'menu_icon_size', esc_html__( 'Tamanho do ícone de menu', 'portus-header' ), 20, 48, $settings, $option ); ?>
						<?php $this->render_number_field( 'close_icon_size', esc_html__( 'Tamanho do ícone fechar', 'portus-header' ), 18, 40, $settings, $option ); ?>
						<?php $this->render_number_field( 'toggle_button_size', esc_html__( 'Tamanho do botão de menu', 'portus-header' ), 40, 64, $settings, $option ); ?>
						<?php $this->render_number_field( 'desktop_menu_font_size', esc_html__( 'Fonte do menu desktop', 'portus-header' ), 12, 24, $settings, $option ); ?>
						<?php $this->render_number_field( 'mobile_menu_font_size', esc_html__( 'Fonte do menu mobile', 'portus-header' ), 13, 24, $settings, $option ); ?>
						<?php $this->render_number_field( 'submenu_font_size', esc_html__( 'Fonte dos submenus', 'portus-header' ), 12, 22, $settings, $option ); ?>
						<?php $this->render_number_field( 'cta_font_size', esc_html__( 'Fonte dos botões CTA', 'portus-header' ), 12, 22, $settings, $option ); ?>
						<?php $this->render_number_field( 'menu_font_weight', esc_html__( 'Peso da fonte dos menus', 'portus-header' ), 400, 800, $settings, $option, '' ); ?>
						<?php $this->render_number_field( 'desktop_menu_font_weight', esc_html__( 'Peso do menu desktop', 'portus-header' ), 400, 900, $settings, $option, '' ); ?>
						<?php $this->render_number_field( 'mobile_menu_font_weight', esc_html__( 'Peso do menu mobile', 'portus-header' ), 400, 900, $settings, $option, '' ); ?>
						<?php $this->render_number_field( 'desktop_menu_letter_spacing', esc_html__( 'Espaçamento de letras desktop', 'portus-header' ), 0, 3, $settings, $option, '0.1px' ); ?>
						<?php $this->render_number_field( 'mobile_menu_letter_spacing', esc_html__( 'Espaçamento de letras mobile', 'portus-header' ), 0, 3, $settings, $option, '0.1px' ); ?>
						<?php $this->render_number_field( 'submenu_icon_size', esc_html__( 'Tamanho das setas mobile', 'portus-header' ), 16, 36, $settings, $option ); ?>
						<?php $this->render_number_field( 'submenu_animation_duration', esc_html__( 'Duração da animação dos submenus', 'portus-header' ), 120, 600, $settings, $option, 'ms' ); ?>
						<?php $this->render_number_field( 'drawer_animation_duration', esc_html__( 'Duração da animação do menu mobile', 'portus-header' ), 120, 600, $settings, $option, 'ms' ); ?>
						<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
							<?php Portus_Header_Pro_Admin::render_number_fields( $this, $settings, $option ); ?>
						<?php endif; ?>
					</div>
					<p class="description"><?php esc_html_e( 'Todas as medidas são em pixels e respeitam limites seguros para responsividade.', 'portus-header' ); ?></p>
				</section>

				<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
					<?php Portus_Header_Pro_Admin::render_mega_section( $this, $settings, $option ); ?>
				<?php endif; ?>

				<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
					<?php Portus_Header_Pro_Admin::render_smart_section( $this, $settings, $option ); ?>
				<?php endif; ?>

				<section class="portus-header-admin__card portus-header-admin__card--warning" data-portus-admin-section="advanced" hidden>
					<h2><?php esc_html_e( 'Cabeçalho original do tema', 'portus-header' ); ?></h2>
					<p><?php esc_html_e( 'O Portus Header nunca oculta o cabeçalho do tema automaticamente. Ative esta opção somente após confirmar o seletor CSS correto no site de teste. A regra só é aplicada quando o Portus Header estiver presente na página.', 'portus-header' ); ?></p>
						<h3><?php esc_html_e( 'Compatibilidade específica com Astra', 'portus-header' ); ?></h3>
					<label class="portus-header-admin__check">
						<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[disable_astra_header]" value="1" <?php checked( $settings['disable_astra_header'], 1 ); ?>>
						<span><?php esc_html_e( 'Desativar o header padrão do Astra pelo hook oficial', 'portus-header' ); ?></span>
					</label>
					<p class="description">
							<?php esc_html_e( 'Esta opção funciona somente quando o tema ativo é Astra ou um child theme do Astra, porque usa hooks oficiais do Astra. Ela não remove o tema, apenas impede a renderização do cabeçalho padrão do Astra.', 'portus-header' ); ?>
						<?php if ( class_exists( 'Portus_Header_Theme_Compatibility' ) && ! Portus_Header_Theme_Compatibility::is_astra_active() ) : ?>
							<?php esc_html_e( 'O tema ativo não parece ser Astra; nesse caso, use o seletor CSS abaixo se precisar ocultar outro header.', 'portus-header' ); ?>
						<?php endif; ?>
					</p>
					<hr>
					<label class="portus-header-admin__check">
						<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[hide_theme_header]" value="1" <?php checked( $settings['hide_theme_header'], 1 ); ?>>
						<span><?php esc_html_e( 'Ocultar explicitamente o cabeçalho original', 'portus-header' ); ?></span>
					</label>
					<div class="portus-header-admin__field">
						<label for="portus-theme-selector"><?php esc_html_e( 'Seletor CSS do cabeçalho original', 'portus-header' ); ?></label>
						<input id="portus-theme-selector" type="text" class="regular-text code" name="<?php echo esc_attr( $option ); ?>[theme_header_selector]" value="<?php echo esc_attr( $settings['theme_header_selector'] ); ?>" placeholder="#masthead">
					</div>
				</section>

				<section class="portus-header-admin__card" data-portus-admin-section="advanced" hidden>
					<h2><?php esc_html_e( 'Performance e distribuição privada', 'portus-header' ); ?></h2>
					<label class="portus-header-admin__check">
						<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[load_assets_globally]" value="1" <?php checked( $settings['load_assets_globally'], 1 ); ?>>
						<span><?php esc_html_e( 'Carregar assets em todas as páginas para builders e widgets', 'portus-header' ); ?></span>
					</label>
					<p class="description"><?php esc_html_e( 'Desativado, CSS e JavaScript são carregados somente quando o modo automático ou o shortcode forem detectados.', 'portus-header' ); ?></p>
				</section>

				<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
					<section class="portus-header-admin__card" data-portus-admin-section="license" hidden>
						<?php Portus_Header_Pro_Admin::render_license_fields( $settings, $option ); ?>
					</section>
				<?php endif; ?>

				<?php submit_button( esc_html__( 'Salvar configurações', 'portus-header' ) ); ?>
			</form>

					<?php if ( $is_pro && class_exists( 'Portus_Header_Pro_Admin' ) ) : ?>
						<?php Portus_Header_Pro_Admin::render_license_actions( $settings ); ?>
					<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Campo reutilizável da Biblioteca de Mídia.
	 *
	 * @param string               $key      Chave.
	 * @param string               $label    Rótulo.
	 * @param array<string, mixed> $settings Configurações.
	 * @param string               $option   Nome da opção.
	 */
	private function render_media_field( $key, $label, $settings, $option ) {
		$id  = absint( $settings[ $key ] );
		$url = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
		?>
		<div class="portus-header-admin__field portus-header-admin__media" data-media-field data-media-key="<?php echo esc_attr( $key ); ?>">
			<label><?php echo esc_html( $label ); ?></label>
			<div class="portus-header-admin__preview" data-media-preview <?php echo $url ? '' : 'hidden'; ?>>
				<?php if ( $url ) : ?>
					<img src="<?php echo esc_url( $url ); ?>" alt="">
				<?php endif; ?>
			</div>
			<input type="hidden" name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $id ); ?>" data-media-id>
			<button type="button" class="button" data-media-select><?php esc_html_e( 'Selecionar imagem', 'portus-header' ); ?></button>
			<button type="button" class="button-link-delete" data-media-remove <?php echo $id ? '' : 'hidden'; ?>><?php esc_html_e( 'Remover', 'portus-header' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Campo de cor nativo.
	 *
	 * @param string               $key      Chave.
	 * @param string               $label    Rótulo.
	 * @param array<string, mixed> $settings Configurações.
	 * @param string               $option   Nome da opção.
	 */
	public function render_color_field( $key, $label, $settings, $option ) {
		?>
		<div class="portus-header-admin__color-field">
			<label for="portus-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			<div>
				<input id="portus-<?php echo esc_attr( $key ); ?>" type="color" name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $settings[ $key ] ); ?>" data-portus-color="<?php echo esc_attr( $key ); ?>" data-portus-default="<?php echo esc_attr( $key ); ?>">
				<code data-color-value><?php echo esc_html( $settings[ $key ] ); ?></code>
			</div>
		</div>
		<?php
	}

	/**
	 * Campo numérico de layout.
	 *
	 * @param string               $key      Chave.
	 * @param string               $label    Rótulo.
	 * @param int                  $min      Mínimo.
	 * @param int                  $max      Máximo.
	 * @param array<string, mixed> $settings Configurações.
	 * @param string               $option   Nome da opção.
	 */
	public function render_number_field( $key, $label, $min, $max, $settings, $option, $unit = 'px' ) {
		?>
		<div class="portus-header-admin__number-field">
			<label for="portus-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			<div>
				<input id="portus-<?php echo esc_attr( $key ); ?>" type="number" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="1" name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $settings[ $key ] ); ?>" data-portus-default="<?php echo esc_attr( $key ); ?>">
				<?php if ( $unit ) : ?><span><?php echo esc_html( $unit ); ?></span><?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Procura wp_body_open nos arquivos PHP dos temas ativo e pai.
	 *
	 * @return bool
	 */
	private function theme_appears_to_support_body_open() {
		$theme     = wp_get_theme();
		$cache_key = 'portus_header_body_open_' . md5( get_stylesheet() . $theme->get( 'Version' ) );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$directories = array_unique( array( get_stylesheet_directory(), get_template_directory() ) );
		$found       = false;

		foreach ( $directories as $directory ) {
			if ( $this->directory_contains_body_open( $directory ) ) {
				$found = true;
				break;
			}
		}

		set_transient( $cache_key, $found ? 1 : 0, 12 * HOUR_IN_SECONDS );

		return $found;
	}

	/**
	 * Faz uma varredura limitada nos PHPs do tema.
	 *
	 * @param string $directory Diretório do tema.
	 * @return bool
	 */
	private function directory_contains_body_open( $directory ) {
		if ( ! is_dir( $directory ) || ! is_readable( $directory ) ) {
			return false;
		}

		$checked = 0;

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $directory, FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( $checked >= 500 ) {
					break;
				}

				if ( ! $file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) {
					continue;
				}

				$path = $file->getPathname();

				if ( preg_match( '#[\\\\/](vendor|node_modules|\.git)[\\\\/]#i', $path ) || $file->getSize() > 1048576 || ! is_readable( $path ) ) {
					continue;
				}

				++$checked;
				$content = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

				if ( is_string( $content ) && preg_match( '/(?:\\bwp_body_open\\s*\\(|do_action\\s*\\(\\s*[\'\"]wp_body_open[\'\"])/', $content ) ) {
					return true;
				}
			}
		} catch ( Exception $exception ) {
			return false;
		}

		return false;
	}

	/**
	 * Informa se o painel deve exibir recursos Pro.
	 *
	 * @return bool
	 */
	private function is_pro_edition() {
		return ! class_exists( 'Portus_Header_Edition_Manager' ) || Portus_Header_Edition_Manager::is_pro();
	}
}
