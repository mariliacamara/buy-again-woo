<?php
/**
 * Plugin Name: Buy Again for WooCommerce
 * Description: Buy Again functionality for WooCommerce.
 * Version: 1.0.0
 * Author: Marilia Camara
 * Text Domain: buy-again-woo
 *
 * @package BuyAgainWoo
 */

use Marilia\BuyAgainWoo\Admin\SettingsPage;
use Marilia\BuyAgainWoo\BuyAgain;
use Marilia\BuyAgainWoo\BuyAgainLoginModal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 */
define( 'BUY_AGAIN_WOO_VERSION', '1.0.0' );

/**
 * Plugin path.
 */
define( 'BUY_AGAIN_WOO_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin URL.
 */
define( 'BUY_AGAIN_WOO_URL', plugin_dir_url( __FILE__ ) );


require_once __DIR__ . '/vendor/autoload.php';

new BuyAgain();
new BuyAgainLoginModal();
new SettingsPage();

add_action(
	'wp_enqueue_scripts',
	function () {

		if ( ! is_account_page() ) {
			return;
		}

		wp_enqueue_style(
			'buy-again-woo',
			plugin_dir_url( __FILE__ ) . 'assets/css/buy-again.css',
			array(),
			'1.0.0'
		);
	}
);
