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

The plugin follows [Semantic Versioning](https://semver.org/):

* **MAJOR** (`2.0.0`): breaking changes.
* **MINOR** (`1.1.0`): new backwards-compatible features.
* **PATCH** (`1.0.1`): backwards-compatible bug fixes.

To publish a new version:

1. Move the entries under `## [Unreleased]` in `CHANGELOG.md` to a new `## [x.y.z] - YYYY-MM-DD` section and update the compare links at the bottom.
2. Bump the version in `buy-again-woo.php`, in both the `Version:` header and the `BUY_AGAIN_WOO_VERSION` constant.
3. Merge to the production branch, then tag the release:

```bash
git tag v1.1.0
git push origin v1.1.0
```

The workflow will:

1. Check that the tag matches the plugin version and has a `CHANGELOG.md` entry
2. Run PHPCS
3. Run PHPStan
4. Install production dependencies
5. Generate the plugin ZIP package
6. Publish a GitHub Release using the `CHANGELOG.md` section as release notes

## Author

Developed by [Camorim Tech](https://camorim.dev.br).

## License

MIT License.
