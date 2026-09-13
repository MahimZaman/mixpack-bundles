<?php

/**
 * Module contract.
 *
 * @package MixPackBundles
 */

namespace MixPack\Bundles\Contracts;

defined('ABSPATH') || exit;

/**
 * Defines the contract implemented by plugin modules.
 *
 * A module should register its WordPress and WooCommerce hooks inside
 * the register() method. Expensive work should not be performed during
 * registration.
 */
interface Module
{

    /**
     * Register the module's hooks and integrations.
     *
     * @return void
     */
    public function register();
}
