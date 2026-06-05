<?php
/**
 * Admin settings page.
 *
 * @package BuyAgainWoo
 */

declare(strict_types=1);

namespace Marilia\BuyAgainWoo\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin settings page.
 */
class SettingsPage {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action(
			'admin_menu',
			array( $this, 'register_menu' )
		);

		add_action(
			'admin_init',
			array( $this, 'register_settings' )
		);
	}

	/**
	 * Register submenu page.
	 *
	 * @return void
	 */
	public function register_menu(): void {

		add_submenu_page(
			'woocommerce',
			__( 'Buy Again', 'buy-again-woo' ),
			__( 'Buy Again', 'buy-again-woo' ),
			'manage_woocommerce',
			'buy-again-woo',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {

		register_setting(
			'buy_again_woo_settings',
			'buy_again_woo_custom_css',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_css' ),
				'default'           => '',
			)
		);
	}

	/**
	 * Sanitize CSS input.
	 *
	 * @param string $css Raw CSS.
	 *
	 * @return string
	 */
	public function sanitize_css( string $css ): string {
		return wp_strip_all_tags( $css );
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$active_tab = isset( $_GET['tab'] )
			? sanitize_key( wp_unslash( $_GET['tab'] ) )
			: 'general';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		?>

		<div class="wrap">

			<h1>
				<?php esc_html_e( 'Buy Again', 'buy-again-woo' ); ?>
			</h1>

			<nav class="nav-tab-wrapper">

				<a
					href="<?php echo esc_url( admin_url( 'admin.php?page=buy-again-woo&tab=general' ) ); ?>"
					class="nav-tab <?php echo ( 'general' === $active_tab ) ? 'nav-tab-active' : ''; ?>"
				>
					<?php esc_html_e( 'General', 'buy-again-woo' ); ?>
				</a>

				<a
					href="<?php echo esc_url( admin_url( 'admin.php?page=buy-again-woo&tab=styles' ) ); ?>"
					class="nav-tab <?php echo ( 'styles' === $active_tab ) ? 'nav-tab-active' : ''; ?>"
				>
					<?php esc_html_e( 'Styles', 'buy-again-woo' ); ?>
				</a>

			</nav>

			<?php if ( 'general' === $active_tab ) : ?>

				<p>
					<?php esc_html_e( 'General settings coming soon.', 'buy-again-woo' ); ?>
				</p>

			<?php endif; ?>

			<?php if ( 'styles' === $active_tab ) : ?>

				<p>
					<?php esc_html_e( 'Styles settings coming soon.', 'buy-again-woo' ); ?>
				</p>

			<?php endif; ?>

		</div>

		<?php
	}
}