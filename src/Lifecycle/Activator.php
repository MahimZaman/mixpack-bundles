<?php

/**
 * Plugin activation handler.
 *
 * @package MahimZamanBuildABundle
 */

namespace MahimZaman\BuildABundle\Lifecycle;

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
    private const VERSION_OPTION = 'mahimzaman_bab_version';

    /**
     * Installation timestamp option.
     *
     * @var string
     */
    private const INSTALLED_AT_OPTION = 'mahimzaman_bab_installed_at';

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
         * Fires after MahimZaman Build-a-Bundle for WooCommerce activation tasks have completed.
         */
        do_action('mahimzaman_bab_activated');
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
            MAHIMZAMAN_BAB_VERSION,
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
