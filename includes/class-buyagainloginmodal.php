<?php
/**
 * Login modal functionality.
 *
 * @package BuyAgainWoo
 */

namespace Marilia\BuyAgainWoo;

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
			plugin_dir_url( __FILE__ ) . '../assets/css/login-modal.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'buy-again-login-modal',
			plugin_dir_url( __FILE__ ) . '../assets/js/login-modal.js',
			array(),
			'1.0.0',
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
					Olá, <?php echo esc_html( $user->display_name ); ?>
				</h3>

				<p class="buy-again-modal__message">
					Deseja aceder seu histórico de encomenda<br>
					e refazer uma compra?
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
						Histórico de Encomenda
					</a>

					<a
						href="<?php echo esc_url( home_url( '/' ) ); ?>"
						class="button buy-again-modal__continue"
					>
						Continuar a Compra
					</a>

				</div>

			</div>

		</div>
		<?php
	}
}