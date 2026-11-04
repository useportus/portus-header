<?php
/**
 * Template base do cabeçalho.
 *
 * Variáveis disponíveis: $settings, $desktop_logo, $mobile_logo,
 * $desktop_menu, $mobile_menu e $classes.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$portus_header_cta_visible        = ! empty( $settings['cta_enabled'] ) && ! empty( $settings['cta_text'] ) && ! empty( $settings['cta_url'] );
$portus_header_is_pro_template    = ! empty( $is_pro );
$portus_header_has_drawer_content = ! empty( $mobile_menu ) || $portus_header_cta_visible || ( $portus_header_is_pro_template && ( $show_account || $show_cart ) );
$portus_header_cta_target         = ! empty( $settings['cta_new_tab'] ) ? '_blank' : '_self';
$portus_header_cta_rel            = ! empty( $settings['cta_new_tab'] ) ? 'noopener noreferrer' : '';
?>
<header id="portus-header" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-portus-header data-portus-smart="<?php echo esc_attr( $header_data['smart'] ); ?>" data-portus-hide-scroll="<?php echo esc_attr( $header_data['hide_scroll'] ); ?>" data-portus-shrink-scroll="<?php echo esc_attr( $header_data['shrink_scroll'] ); ?>" data-portus-scroll-threshold="<?php echo esc_attr( $header_data['threshold'] ); ?>">
	<div class="portus-header__inner">
		<a class="portus-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( $desktop_logo ) : ?>
				<img class="portus-header__logo portus-header__logo--desktop" src="<?php echo esc_url( $desktop_logo['url'] ); ?>" alt="<?php echo esc_attr( $desktop_logo['alt'] ); ?>" fetchpriority="high">
			<?php else : ?>
				<span class="portus-header__site-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			<?php endif; ?>

			<?php if ( $mobile_logo ) : ?>
				<img class="portus-header__logo portus-header__logo--mobile" src="<?php echo esc_url( $mobile_logo['url'] ); ?>" alt="<?php echo esc_attr( $mobile_logo['alt'] ); ?>" fetchpriority="high">
			<?php endif; ?>
		</a>

		<?php if ( $desktop_menu ) : ?>
			<nav class="portus-header__navigation" aria-label="<?php esc_attr_e( 'Menu principal', 'portus-header' ); ?>">
				<?php echo $desktop_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</nav>
		<?php endif; ?>

		<div class="portus-header__actions">
			<?php if ( $portus_header_is_pro_template ) : ?>
				<?php include PORTUS_HEADER_PATH . 'templates/pro/header-actions.php'; ?>
			<?php endif; ?>

			<?php if ( $portus_header_cta_visible ) : ?>
				<a class="portus-header__cta portus-header__cta--desktop" href="<?php echo esc_url( $settings['cta_url'] ); ?>" target="<?php echo esc_attr( $portus_header_cta_target ); ?>"<?php echo $portus_header_cta_rel ? ' rel="' . esc_attr( $portus_header_cta_rel ) . '"' : ''; ?>>
					<?php echo esc_html( $settings['cta_text'] ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $portus_header_has_drawer_content ) : ?>
				<button class="portus-header__menu-toggle" type="button" aria-expanded="false" aria-controls="portus-header-drawer" aria-label="<?php esc_attr_e( 'Abrir menu', 'portus-header' ); ?>" data-portus-menu-open>
					<img class="portus-header__icon portus-header__icon--menu" src="<?php echo esc_attr( $menu_icon_src ); ?>" alt="" aria-hidden="true" width="30" height="30">
				</button>
			<?php endif; ?>
		</div>
	</div>
</header>

<?php if ( $portus_header_has_drawer_content ) : ?>
	<?php include PORTUS_HEADER_PATH . 'templates/free/mobile-drawer.php'; ?>
<?php endif; ?>

<?php if ( $portus_header_is_pro_template && $show_search ) : ?>
	<?php include PORTUS_HEADER_PATH . 'templates/pro/search-panel.php'; ?>
<?php endif; ?>
