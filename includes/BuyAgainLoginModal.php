<?php
/**
 * Login modal functionality.
 *
 * @package BuyAgainWoo
 */

namespace Marilia\BuyAgainWoo;

use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles login modal functionality.
 */
class BuyAgainLoginModal {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action(
			'wp_enqueue_scripts',
			array( $this, 'enqueue_assets' )
		);

		add_action(
			'wp_login',
			array( $this, 'mark_login' ),
			999,
			2
		);

		add_action(
			'wp_footer',
			array( $this, 'render_modal' )
		);
	}

	/**
	 * Mark modal to display after login.
	 *
	 * @param string  $user_login User login.
	 * @param WP_User $user       User object.
	 *
	 * @return void
	 */
	public function mark_login( $user_login, $user ) {
		update_user_meta(
			$user->ID,
			'_buy_again_show_modal',
			1
		);
	}

	/**
	 * Enqueue modal assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_style(
			'buy-again-login-modal',
			BUY_AGAIN_WOO_URL . 'assets/css/login-modal.css',
			array(),
			BUY_AGAIN_WOO_VERSION
		);

		wp_enqueue_script(
			'buy-again-login-modal',
			BUY_AGAIN_WOO_URL . 'assets/js/login-modal.js',
			array(),
			BUY_AGAIN_WOO_VERSION,
			true
		);
	}

	/**
	 * Render login modal.
	 *
	 * @return void
	 */
	public function render_modal() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$show_modal = get_user_meta(
			get_current_user_id(),
			'_buy_again_show_modal',
			true
		);

		if ( ! $show_modal ) {
			return;
		}

		delete_user_meta(
			get_current_user_id(),
			'_buy_again_show_modal'
		);

		$user = wp_get_current_user();

		?>
		<div id="buy-again-modal" class="buy-again-modal">

			<div
				class="buy-again-modal__overlay"
				data-close-buy-again-modal
			></div>

			<div class="buy-again-modal__content">

				<button
					type="button"
					class="buy-again-modal__close"
					data-close-buy-again-modal
				>
					×
				</button>

				<h3>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: customer display name. */
							__( 'Olá, %s', 'buy-again-woo' ),
							$user->display_name
						)
					);
					?>
				</h3>

				<p class="buy-again-modal__message">
					<?php esc_html_e( 'Deseja aceder ao seu histórico de encomendas', 'buy-again-woo' ); ?><br>
					<?php esc_html_e( 'e refazer uma compra?', 'buy-again-woo' ); ?>
				</p>

				<div class="buy-again-modal__actions">

					<a
						href="
						<?php
						echo esc_url(
							wc_get_account_endpoint_url( 'orders' )
						);
						?>
						"
						class="button buy-again-modal__history"
					>
						<?php esc_html_e( 'Histórico de encomendas', 'buy-again-woo' ); ?>
					</a>

					<a
						href="<?php echo esc_url( home_url( '/' ) ); ?>"
						class="button buy-again-modal__continue"
					>
						<?php esc_html_e( 'Continuar a compra', 'buy-again-woo' ); ?>
					</a>

				</div>

			</div>

		</div>
		<?php
	}
}