<?php

/**
 * Plugin module registry.
 *
 * @package MixPackBundles
 */

namespace MixPack\Bundles\Support;

use MixPack\Bundles\Contracts\Module;

defined('ABSPATH') || exit;

/**
 * Stores and registers MixPack Bundles modules.
 */
final class ModuleRegistry
{

    /**
     * Registered modules.
     *
     * The fully-qualified class name is used as the array key to prevent
     * the same module from being registered more than once.
     *
     * @var array<string, Module>
     */
    private $modules = array();

    /**
     * Whether module registration has already occurred.
     *
     * @var bool
     */
    private $registered = false;

    /**
     * Add a module to the registry.
     *
     * Duplicate module classes are ignored.
     *
     * @param Module $module Module instance.
     *
     * @return void
     */
    public function add(Module $module)
    {
        $class_name = get_class($module);

        if (isset($this->modules[$class_name])) {
            return;
        }

        $this->modules[$class_name] = $module;

        /*
		 * If registration has already occurred, register late-added
		 * modules immediately.
		 */
        if ($this->registered) {
            $module->register();
        }
    }

    /**
     * Register every module.
     *
     * @return void
     */
    public function register_all()
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        foreach ($this->modules as $module) {
            $module->register();
        }
    }

    /**
     * Determine whether a module exists in the registry.
     *
     * @param string $class_name Fully-qualified module class name.
     *
     * @return bool
     */
    public function has($class_name)
    {
        return isset($this->modules[ltrim($class_name, '\\')]);
    }

    /**
     * Get the number of registered module instances.
     *
     * @return int
     */
    public function count()
    {
        return count($this->modules);
    }
}
