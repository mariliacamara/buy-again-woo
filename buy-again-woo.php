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

add_filter(
    'woocommerce_my_account_my_orders_actions',
    function ($actions, $order) {

        $actions['buy_again'] = [
            'url'  => add_query_arg(
                [
                    'buy_again' => $order->get_id(),
                ],
                wc_get_cart_url()
            ),
            'name' => __('Comprar Novamente', 'buy-again-woo'),
        ];

        return $actions;
    },
    10,
    2
);

add_action('template_redirect', function () {

    if (!isset($_GET['buy_again'])) {
        return;
    }

    wc_add_notice(
        sprintf(
            'Pedido recebido: %d',
            absint($_GET['buy_again'])
        ),
        'success'
    );
});