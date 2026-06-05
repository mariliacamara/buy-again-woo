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

require_once plugin_dir_path(__FILE__) . 'includes/class-buy-again.php';

new Buy_Again();