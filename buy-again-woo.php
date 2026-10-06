<?php
/**
 * Plugin Name: Buy Again for WooCommerce
 * Description: Buy Again functionality for WooCommerce.
 * Version: 1.1.0
 * Author: Camorim Tech
 * Author URI: https://camorim.dev.br
 * License: MIT
 * Text Domain: buy-again-woo
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * WC requires at least: 8.0
 *
 * @package BuyAgainWoo
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Marilia\BuyAgainWoo\Admin\SettingsPage;
use Marilia\BuyAgainWoo\BuyAgain;
use Marilia\BuyAgainWoo\BuyAgainLoginModal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 */
define( 'BUY_AGAIN_WOO_VERSION', '1.1.0' );

/**
 * Plugin path.
 */
define( 'BUY_AGAIN_WOO_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin URL.
 */
define( 'BUY_AGAIN_WOO_URL', plugin_dir_url( __FILE__ ) );


require_once __DIR__ . '/vendor/autoload.php';

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Boot the plugin once all plugins are loaded, only if WooCommerce is active.
 */
add_action(
	'plugins_loaded',
	function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		new BuyAgain();
		new BuyAgainLoginModal();
		new SettingsPage();

		add_action(
			'init',
			function () {
				load_plugin_textdomain(
					'buy-again-woo',
					false,
					dirname( plugin_basename( __FILE__ ) ) . '/languages'
				);
			}
		);

		add_action(
			'wp_enqueue_scripts',
			function () {

				if ( ! is_account_page() && ! is_cart() ) {
					return;
				}

				wp_enqueue_style(
					'buy-again-woo',
					BUY_AGAIN_WOO_URL . 'assets/css/buy-again.css',
					array(),
					BUY_AGAIN_WOO_VERSION
				);
			}
		);
	}
);
