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
            'url'  => '#',
            'name' => __('Comprar Novamente', 'buy-again-woo'),
        ];

        return $actions;
    },
    10,
    2
);