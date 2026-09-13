<?php

/**
 * Plugin activation handler.
 *
 * @package MixPackBundles
 */

namespace MixPack\Bundles\Lifecycle;

defined('ABSPATH') || exit;

/**
 * Handles plugin activation.
 */
final class Activator
{

    /**
     * Plugin version option.
     *
     * @var string
     */
    private const VERSION_OPTION = 'mixpack_bundles_version';

    /**
     * Installation timestamp option.
     *
     * @var string
     */
    private const INSTALLED_AT_OPTION = 'mixpack_bundles_installed_at';

    /**
     * Run plugin activation tasks.
     *
     * @param bool $network_wide Whether the plugin is being network activated.
     *
     * @return void
     */
    public static function activate($network_wide = false)
    {
        unset($network_wide);

        self::store_installation_metadata();

        /**
         * Fires after MixPack Bundles activation tasks have completed.
         */
        do_action('mixpack_bundles_activated');
    }

    /**
     * Store lightweight installation metadata.
     *
     * This does not create bundle settings, database tables, or user-facing
     * configuration. It exists only to support future upgrade routines and
     * installation diagnostics.
     *
     * @return void
     */
    private static function store_installation_metadata()
    {
        update_option(
            self::VERSION_OPTION,
            MIXPACK_BUNDLES_VERSION,
            false
        );

        if (false === get_option(self::INSTALLED_AT_OPTION, false)) {
            add_option(
                self::INSTALLED_AT_OPTION,
                time(),
                '',
                false
            );
        }
    }
}
