<?php
/**
 * Plugin Name: Buy Again for WooCommerce
 * Description: Buy Again functionality for WooCommerce.
 * Version: 1.0.0
 * Author: Marilia Camara
 * Text Domain: buy-again-woo
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Buy Again button
 */
add_filter(
    'woocommerce_my_account_my_orders_actions',
    function ($actions, $order) {

        $actions['buy_again'] = [
            'url' => wp_nonce_url(
                add_query_arg(
                    [
                        'buy_again' => $order->get_id(),
                    ],
                    wc_get_cart_url()
                ),
                'buy_again_' . $order->get_id()
            ),
            'name' => __('Comprar Novamente', 'buy-again-woo'),
        ];

        return $actions;
    },
    10,
    2
);

/**
 * Processing buy again
 */
add_action('template_redirect', function () {

    if (!isset($_GET['buy_again'])) {
        return;
    }

    $order_id = absint($_GET['buy_again']);

    if (
        !isset($_GET['_wpnonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_GET['_wpnonce'])),
            'buy_again_' . $order_id
        )
    ) {
        wc_add_notice(
            'Pedido inválido.',
            'error'
        );

        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        wc_add_notice(
            'Encomenda não encontrada.',
            'error'
        );

        return;
    }

    if ((int) $order->get_user_id() !== get_current_user_id()) {
        wc_add_notice(
            'Não tem permissão para esta encomenda.',
            'error'
        );

        return;
    }

    /**
     * Clear cart before re-creating the order
     */
    WC()->cart->empty_cart();

    $added = 0;

    foreach ($order->get_items() as $item) {

        $product_id = $item->get_product_id();

        if (!$product_id) {
            continue;
        }

        WC()->cart->add_to_cart(
            $product_id,
            $item->get_quantity()
        );

        $added++;
    }

    wc_add_notice(
        sprintf(
            '%d produto(s) adicionados ao carrinho.',
            $added
        ),
        'success'
    );
});