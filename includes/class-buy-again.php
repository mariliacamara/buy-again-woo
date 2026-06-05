<?php
/**
 * Buy Again functionality.
 *
 * @package BuyAgainWoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles buy again functionality.
 */
class Buy_Again {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter(
			'woocommerce_my_account_my_orders_actions',
			array( $this, 'add_button' ),
			10,
			2
		);

		add_action(
			'template_redirect',
			array( $this, 'process_reorder' )
		);

		add_action(
			'woocommerce_cart_actions',
			array( $this, 'render_continue_shopping_button' )
		);
	}

	/**
	 * Add buy again button to orders list.
	 *
	 * @param array    $actions Order actions.
	 * @param WC_Order $order   Order object.
	 *
	 * @return array
	 */
	public function add_button( $actions, $order ) {
		$actions['buy_again'] = array(
			'url'  => wp_nonce_url(
				add_query_arg(
					array(
						'buy_again' => $order->get_id(),
					),
					wc_get_cart_url()
				),
				'buy_again_' . $order->get_id()
			),
			'name' => __( 'Comprar Novamente', 'buy-again-woo' ),
		);

		return $actions;
	}

	/**
	 * Process reorder request.
	 *
	 * @return void
	 */
	public function process_reorder() {
		if ( ! isset( $_GET['buy_again'] ) ) {
			return;
		}

		$order_id = absint( $_GET['buy_again'] );

		if (
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ),
				'buy_again_' . $order_id
			)
		) {
			wc_add_notice(
				'Pedido inválido.',
				'error'
			);

			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			wc_add_notice(
				'Encomenda não encontrada.',
				'error'
			);

			return;
		}

		if ( (int) $order->get_user_id() !== get_current_user_id() ) {
			wc_add_notice(
				'Não tem permissão para esta encomenda.',
				'error'
			);

			return;
		}

		WC()->cart->empty_cart();

		$added  = 0;
		$failed = array();

		foreach ( $order->get_items() as $item ) {

			$product_id   = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$quantity     = $item->get_quantity();

			if ( ! $product_id ) {
				continue;
			}

			$variation = array();

			if ( $variation_id ) {
				$variation_product = wc_get_product( $variation_id );

				if (
					$variation_product &&
					$variation_product->is_type( 'variation' )
				) {
					$variation = $variation_product->get_variation_attributes();
				}
			}

			$result = WC()->cart->add_to_cart(
				$product_id,
				$quantity,
				$variation_id,
				$variation
			);

			if ( $result ) {
				++$added;
			} else {
				$failed[] = $item->get_name();
			}
		}

		if ( $added > 0 ) {
			wc_add_notice(
				sprintf(
					'%d produto(s) adicionados ao carrinho.',
					$added
				),
				'success'
			);
		}

		if ( ! empty( $failed ) ) {
			wc_add_notice(
				sprintf(
					'Os seguintes produtos não puderam ser adicionados: %s',
					implode( ', ', $failed )
				),
				'notice'
			);
		}

		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	/**
	 * Render continue shopping button.
	 *
	 * @return void
	 */
	public function render_continue_shopping_button() {
		?>
		<a
			href="<?php echo esc_url( home_url( '/' ) ); ?>"
			class="button buy-again-continue-shopping"
		>
			Continuar a Comprar
		</a>
		<?php
	}
}