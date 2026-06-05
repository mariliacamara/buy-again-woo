# Buy Again for WooCommerce

Adds a **Buy Again** feature to WooCommerce, allowing customers to quickly reorder products from previous orders.

## Features

* Adds a **Buy Again** button to the customer order history.
* Restores products from a previous order into the cart.
* Supports simple and variable products.
* Displays a login welcome modal after authentication.
* Includes a quick link to the customer's order history.
* Follows modern PHP development practices:

  * Composer
  * PSR-4 Autoloading
  * PHPStan
  * WordPress Coding Standards (WPCS)
  * GitHub Actions CI/CD

## Requirements

* PHP 8.0+
* WordPress 6.x+
* WooCommerce 8.x+

## Installation

### Development

Clone the repository:

```bash
git clone <repository-url>
cd buy-again-woo
```

Install dependencies:

```bash
composer install
```

### Production

Install the generated release ZIP through the WordPress Plugin Installer.

## Development

### Coding Standards

Run PHPCS:

```bash
composer lint
```

Automatically fix coding style issues:

```bash
composer lint:fix
```

### Static Analysis

Run PHPStan:

```bash
composer analyse
```

### Run All Checks

```bash
composer check
```

## Architecture

```text
buy-again-woo/
├── assets/
│   ├── css/
│   └── js/
├── includes/
│   ├── BuyAgain.php
│   └── BuyAgainLoginModal.php
├── .github/
│   └── workflows/
├── vendor/
├── composer.json
└── buy-again-woo.php
```

## Release Process

Releases are generated automatically through GitHub Actions.

Create a new release tag:

```bash
git tag v1.0.0
git push origin v1.0.0
```

The workflow will:

1. Run PHPCS
2. Run PHPStan
3. Install production dependencies
4. Generate the plugin ZIP package
5. Publish a GitHub Release

## License

MIT License.
