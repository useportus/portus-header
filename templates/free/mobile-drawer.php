<?php
/**
 * Menu lateral mobile base.
 *
 * @package Portus_Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="portus-header-overlay" data-portus-menu-overlay hidden></div>
<aside id="portus-header-drawer" class="portus-header-drawer" aria-label="<?php esc_attr_e( 'Menu mobile', 'portus-header' ); ?>" aria-hidden="true" data-portus-menu-drawer hidden>
	<div class="portus-header-drawer__top">
		<a class="portus-header-drawer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( $mobile_logo ) : ?>
				<img class="portus-header-drawer__logo" src="<?php echo esc_url( $mobile_logo['url'] ); ?>" alt="<?php echo esc_attr( $mobile_logo['alt'] ); ?>">
			<?php else : ?>
				<span class="portus-header-drawer__site-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			<?php endif; ?>
		</a>
		<button class="portus-header-drawer__close" type="button" aria-label="<?php esc_attr_e( 'Fechar menu', 'portus-header' ); ?>" data-portus-menu-close>
			<img class="portus-header__icon portus-header__icon--close" src="<?php echo esc_attr( $close_icon_src ); ?>" alt="" aria-hidden="true" width="24" height="24">
		</button>
	</div>

	<?php if ( $mobile_menu ) : ?>
		<nav class="portus-header-drawer__navigation" aria-label="<?php esc_attr_e( 'Menu principal mobile', 'portus-header' ); ?>">
			<?php echo $mobile_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</nav>
	<?php endif; ?>

	<?php if ( ! empty( $is_pro ) && ( $show_account || $show_cart ) ) : ?>
		<?php include PORTUS_HEADER_PATH . 'templates/pro/mobile-utilities.php'; ?>
	<?php endif; ?>

	<?php if ( $portus_header_cta_visible ) : ?>
		<a class="portus-header__cta portus-header-drawer__cta" href="<?php echo esc_url( $settings['cta_url'] ); ?>" target="<?php echo esc_attr( $portus_header_cta_target ); ?>"<?php echo $portus_header_cta_rel ? ' rel="' . esc_attr( $portus_header_cta_rel ) . '"' : ''; ?>>
			<?php echo esc_html( $settings['cta_text'] ); ?>
		</a>
	<?php endif; ?>
</aside>
