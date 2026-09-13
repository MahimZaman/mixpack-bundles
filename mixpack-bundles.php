<?php

/**
 * Plugin Name:       MixPack Bundles
 * Description:       Create flexible mix-and-match product packs and bundles with a guided builder for WooCommerce.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * Author:            Mahim Zaman
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mixpack-bundles
 * Domain Path:       /languages
 *
 * WC requires at least: 8.5
 * WC tested up to:      11.1.0
 *
 * @package MixPackBundles
 */

defined('ABSPATH') || exit;

/**
 * Plugin version.
 */
define('MIXPACK_BUNDLES_VERSION', '1.0.0');

/**
 * Main plugin file.
 */
define('MIXPACK_BUNDLES_FILE', __FILE__);

/**
 * Absolute path to the plugin directory.
 */
define('MIXPACK_BUNDLES_PATH', plugin_dir_path(__FILE__));

/**
 * URL to the plugin directory.
 */
define('MIXPACK_BUNDLES_URL', plugin_dir_url(__FILE__));

/**
 * Plugin basename.
 */
define('MIXPACK_BUNDLES_BASENAME', plugin_basename(__FILE__));

/**
 * Load the internal autoloader.
 */
require_once MIXPACK_BUNDLES_PATH . 'src/Autoloader.php';

\MixPack\Bundles\Autoloader::register();

/**
 * Register plugin lifecycle hooks.
 *
 * Activation and deactivation hooks must be registered while the main
 * plugin file is loading rather than from a later WordPress hook.
 */
register_activation_hook(
    MIXPACK_BUNDLES_FILE,
    array(
        \MixPack\Bundles\Lifecycle\Activator::class,
        'activate',
    )
);

register_deactivation_hook(
    MIXPACK_BUNDLES_FILE,
    array(
        \MixPack\Bundles\Lifecycle\Deactivator::class,
        'deactivate',
    )
);

/**
 * Register MixPack Bundles with WordPress.
 *
 * Feature modules register their hooks during this stage while actual
 * WooCommerce-dependent execution occurs at the appropriate WordPress
 * and WooCommerce lifecycle hooks.
 */
\MixPack\Bundles\Plugin::instance()->register();
