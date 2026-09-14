<?php

/**
 * Plugin deactivation handler.
 *
 * @package MahimZamanBuildABundle
 */

namespace MahimZaman\BuildABundle\Lifecycle;

defined('ABSPATH') || exit;

/**
 * Handles plugin deactivation.
 */
final class Deactivator
{

    /**
     * Run plugin deactivation tasks.
     *
     * @param bool $network_wide Whether the plugin is being network deactivated.
     *
     * @return void
     */
    public static function deactivate($network_wide = false)
    {
        unset($network_wide);

        /**
         * Fires when MahimZaman Build-a-Bundle for WooCommerce is deactivated.
         *
         * Persistent bundle and historical order data must not be removed
         * during deactivation.
         */
        do_action('mahimzaman_bab_deactivated');
    }
}
