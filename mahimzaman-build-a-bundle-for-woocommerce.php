<?php

/**
 * Plugin Name:       MahimZaman Build-a-Bundle for WooCommerce
 * Description:       Create flexible mix-and-match product packs and bundles with a guided builder for WooCommerce.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * Author:            Mahim Zaman
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mahimzaman-build-a-bundle-for-woocommerce
 * Domain Path:       /languages
 *
 * WC requires at least: 8.5
 * WC tested up to:      11.1.0
 *
 * @package MahimZamanBuildABundle
 */

defined('ABSPATH') || exit;

/**
 * Plugin version.
 */
define('MAHIMZAMAN_BAB_VERSION', '1.0.0');

/**
 * Main plugin file.
 */
define('MAHIMZAMAN_BAB_FILE', __FILE__);

/**
 * Absolute path to the plugin directory.
 */
define('MAHIMZAMAN_BAB_PATH', plugin_dir_path(__FILE__));

/**
 * URL to the plugin directory.
 */
define('MAHIMZAMAN_BAB_URL', plugin_dir_url(__FILE__));

/**
 * Plugin basename.
 */
define('MAHIMZAMAN_BAB_BASENAME', plugin_basename(__FILE__));

/**
 * Load the internal autoloader.
 */
require_once MAHIMZAMAN_BAB_PATH . 'src/Autoloader.php';

\MahimZaman\BuildABundle\Autoloader::register();

/**
 * Register plugin lifecycle hooks.
 *
 * Activation and deactivation hooks must be registered while the main
 * plugin file is loading rather than from a later WordPress hook.
 */
register_activation_hook(
    MAHIMZAMAN_BAB_FILE,
    array(
        \MahimZaman\BuildABundle\Lifecycle\Activator::class,
        'activate',
    )
);

register_deactivation_hook(
    MAHIMZAMAN_BAB_FILE,
    array(
        \MahimZaman\BuildABundle\Lifecycle\Deactivator::class,
        'deactivate',
    )
);

/**
 * Register MahimZaman Build-a-Bundle for WooCommerce with WordPress.
 *
 * Feature modules register their hooks during this stage while actual
 * WooCommerce-dependent execution occurs at the appropriate WordPress
 * and WooCommerce lifecycle hooks.
 */
\MahimZaman\BuildABundle\Plugin::instance()->register();
