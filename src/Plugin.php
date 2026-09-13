<?php

/**
 * Main plugin controller.
 *
 * @package MixPackBundles
 */

namespace MixPack\Bundles;

use MixPack\Bundles\Support\ModuleRegistry;

defined('ABSPATH') || exit;

/**
 * Main MixPack Bundles application controller.
 */
final class Plugin
{

    /**
     * Singleton instance.
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Plugin module registry.
     *
     * @var ModuleRegistry|null
     */
    private $modules = null;

    /**
     * Whether plugin registration has occurred.
     *
     * @var bool
     */
    private $registered = false;

    /**
     * Whether the plugin has successfully booted.
     *
     * @var bool
     */
    private $booted = false;

    /**
     * Prevent direct construction.
     */
    private function __construct() {}

    /**
     * Get the plugin instance.
     *
     * @return Plugin
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register the plugin with WordPress.
     *
     * This method is intentionally executed early. Modules should only
     * register hooks here and must not assume WooCommerce is fully loaded.
     *
     * @return void
     */
    public function register()
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;
        $this->modules    = new ModuleRegistry();

        $this->register_modules();

        $this->modules->register_all();

        add_action('plugins_loaded', array($this, 'boot'), 20);

        /**
         * Fires after MixPack Bundles has registered its modules.
         *
         * WooCommerce may not be fully initialized when this hook runs.
         *
         * @param Plugin $plugin Main plugin instance.
         */
        do_action('mixpack_bundles_registered', $this);
    }

    /**
     * Add plugin modules to the registry.
     *
     * Modules are registered here so the application's composition remains
     * centralized while individual feature implementations stay isolated.
     *
     * @return void
     */
    private function register_modules()
    {
        $this->modules->add(
            new Compatibility\CompatibilityModule()
        );

        $this->modules->add(
            new Product\ProductModule()
        );

        $this->modules->add(
            new Admin\AdminModule()
        );

        $this->modules->add(
            new Admin\SettingsModule()
        );

        $this->modules->add(
            new Frontend\FrontendModule()
        );

        $this->modules->add(
            new Cart\CartModule()
        );

        $this->modules->add(
            new Orders\OrderModule()
        );
    }

    /**
     * Boot the plugin after WordPress plugins have loaded.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        if (! Requirements::is_satisfied()) {
            Requirements::register_admin_notice();
            return;
        }

        $this->booted = true;

        /**
         * Fires once MixPack Bundles and its runtime requirements
         * are available.
         *
         * @param Plugin $plugin Main plugin instance.
         */
        do_action('mixpack_bundles_loaded', $this);
    }

    /**
     * Determine whether the plugin registration stage has completed.
     *
     * @return bool
     */
    public function is_registered()
    {
        return $this->registered;
    }

    /**
     * Determine whether the plugin successfully booted.
     *
     * @return bool
     */
    public function is_booted()
    {
        return $this->booted;
    }

    /**
     * Get the module registry.
     *
     * Primarily useful for internal diagnostics and automated tests.
     *
     * @return ModuleRegistry|null
     */
    public function modules()
    {
        return $this->modules;
    }
}
