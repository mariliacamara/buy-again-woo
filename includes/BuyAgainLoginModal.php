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
	 * Marca o cliente para ver a janela no próximo carregamento, se a janela estiver ligada.
	 *
	 * @param string  $user_login User login.
	 * @param WP_User $user       User object.
	 *
	 * @return void
	 */
	public function mark_login( $user_login, $user ) {
		if ( ! Settings::get()['modal_enabled'] ) {
			return;
		}
		update_user_meta( $user->ID, '_buy_again_show_modal', 1 );
	}

	/**
	 * Se a janela deve aparecer a este cliente.
	 *
	 * Ligada e, com «só a clientes com encomendas», com pelo menos uma encomenda.
	 *
	 * @param int $user_id ID do cliente.
	 * @return bool
	 */
	public function should_show( int $user_id ): bool {
		$s = Settings::get();
		if ( ! $user_id || ! $s['modal_enabled'] ) {
			return false;
		}
		if ( ! $s['modal_only_with_orders'] ) {
			return true;
		}
		$orders = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'limit'       => 1,
				'return'      => 'ids',
				'status'      => array_keys( wc_get_order_statuses() ),
			)
		);
		return ! empty( $orders );
	}

	/**
	 * Se a janela está pendente para o cliente atual.
	 *
	 * @return bool
	 */
	private function pending(): bool {
		$user_id = get_current_user_id();
		return $user_id && get_user_meta( $user_id, '_buy_again_show_modal', true ) && $this->should_show( $user_id );
	}

	/**
	 * Carrega o CSS e o JS da janela só quando ela vai aparecer.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! $this->pending() ) {
			return;
		}

		wp_enqueue_style(
			'buy-again-login-modal',
			BUY_AGAIN_WOO_URL . 'assets/css/login-modal.css',
			array(),
			BUY_AGAIN_WOO_VERSION
		);
		wp_add_inline_style( 'buy-again-login-modal', Assets::css_variables( Settings::get() ) );

		wp_enqueue_script(
			'buy-again-login-modal',
			BUY_AGAIN_WOO_URL . 'assets/js/login-modal.js',
			array(),
			BUY_AGAIN_WOO_VERSION,
			true
		);
	}

	/**
	 * Mostra a janela uma vez e limpa a marca.
	 *
	 * @return void
	 */
	public function render_modal() {
		$user_id = get_current_user_id();
		if ( ! $user_id || ! get_user_meta( $user_id, '_buy_again_show_modal', true ) ) {
			return;
		}
		delete_user_meta( $user_id, '_buy_again_show_modal' );
		if ( ! $this->should_show( $user_id ) ) {
			return;
		}

		$s     = Settings::get();
		$user  = wp_get_current_user();
		$title = str_replace( '{nome}', $user->display_name, $s['modal_title'] );
		$next  = Settings::destination( $s['modal_secondary_type'], (int) $s['modal_secondary_page'], $s['modal_secondary_url'] );
		?>
		<div id="buy-again-modal" class="buy-again-modal">
			<div class="buy-again-modal__overlay" data-close-buy-again-modal></div>
			<div class="buy-again-modal__content" role="dialog" aria-modal="true" aria-labelledby="buy-again-modal-title">
				<button type="button" class="buy-again-modal__close" data-close-buy-again-modal aria-label="<?php esc_attr_e( 'Fechar', 'buy-again-woo' ); ?>">×</button>
				<h3 id="buy-again-modal-title"><?php echo esc_html( $title ); ?></h3>
				<p class="buy-again-modal__message"><?php echo esc_html( $s['modal_message'] ); ?></p>
				<div class="buy-again-modal__actions">
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="button buy-again-modal__history"><?php echo esc_html( $s['modal_primary_text'] ); ?></a>
					<a href="<?php echo esc_url( $next ); ?>" class="button buy-again-modal__continue"><?php echo esc_html( $s['modal_secondary_text'] ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}
}
