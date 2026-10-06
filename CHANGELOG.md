# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/mariliacamara/buy-again-woo/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/mariliacamara/buy-again-woo/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/mariliacamara/buy-again-woo/releases/tag/v1.0.0
