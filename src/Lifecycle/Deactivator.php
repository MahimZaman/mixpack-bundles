<?php

/**
 * Plugin deactivation handler.
 *
 * @package MixPackBundles
 */

namespace MixPack\Bundles\Lifecycle;

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
         * Fires when MixPack Bundles is deactivated.
         *
         * Persistent bundle and historical order data must not be removed
         * during deactivation.
         */
        do_action('mixpack_bundles_deactivated');
    }
}
