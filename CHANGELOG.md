# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-11-24

### Added

- Settings page under the new shared **Camorim Tech** admin menu, with a live preview: every text, color, link and on/off switch of the order button, the post-login modal and the cart button can now be changed without code.
- Option to show the post-login modal only to customers who already have orders (on by default).
- Shared `includes/camorim-tech-menu.php`, meant to be copied unchanged into every Camorim Tech plugin.
- Automatic deploy to both stores' staging (`development`) and production (`production`) sites.
- Integration tests run on the staging sites (`bin/test-dev.sh`).

### Changed

- Default texts and colors are the ones used until 1.1.0, so existing stores look the same after updating.
- The modal assets now load only when the modal is about to show.
- The orders-table layout rule no longer applies to every WooCommerce table (it was squeezing the cart and totals tables on some themes).

### Removed

- The unused `buy_again_woo_custom_css` setting and the placeholder "General" and "Styles" tabs.

## [1.1.0] - 2026-10-06

### Added

- Declare compatibility with WooCommerce High-Performance Order Storage (HPOS).
- Plugin header requirements: `Requires Plugins`, `Requires PHP`, `Requires at least` and `WC requires at least`.
- Translation support: all customer-facing strings use the `buy-again-woo` text domain.
- Camorim Tech as plugin author.

### Fixed

- Fatal error when WooCommerce is inactive; the plugin now only boots when WooCommerce is loaded.
- Fatal error when installing from the repository: the committed autoloader required development-only files.
- Variations with "Any" attributes could not be added back to the cart.
- "Continue shopping" button on the cart page was unstyled.
- Typo in the login modal ("Histórico de encomendas").
- Asset cache busting now follows the plugin version.

## [1.0.0] - 2026-06-08

### Added

- "Buy again" button in the customer order history that restores order items to the cart.
- Support for simple and variable products, skipping unavailable products.
- "Continue shopping" button on the cart page.
- Welcome modal after customer login with a link to the order history.
- Buy Again settings page under WooCommerce.

[Unreleased]: https://github.com/mariliacamara/buy-again-woo/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/mariliacamara/buy-again-woo/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/mariliacamara/buy-again-woo/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/mariliacamara/buy-again-woo/releases/tag/v1.0.0
