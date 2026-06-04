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

    $order_id = absint($_GET['buy_again']);

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $items = $order->get_items();

    $first_item = reset($items);

    if (!$first_item) {
        wc_add_notice('Nenhum produto encontrado na encomenda.', 'error');
        return;
    }

    $product_id = $first_item->get_product_id();

    WC()->cart->add_to_cart($product_id, 1);

    wc_add_notice(
        sprintf(
            'Produto %d adicionado ao carrinho.',
            $product_id
        ),
        'success'
    );
});