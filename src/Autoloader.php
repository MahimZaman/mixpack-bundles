<?php

/**
 * Internal class autoloader.
 *
 * @package MixPackBundles
 */

namespace MixPack\Bundles;

defined('ABSPATH') || exit;

/**
 * Loads MixPack Bundles classes from the src directory.
 */
final class Autoloader
{

    /**
     * Namespace prefix handled by this autoloader.
     *
     * @var string
     */
    private const PREFIX = 'MixPack\\Bundles\\';

    /**
     * Register the autoloader.
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Load a class belonging to the plugin namespace.
     *
     * @param string $class Fully qualified class name.
     *
     * @return void
     */
    private static function autoload($class)
    {
        if (0 !== strpos($class, self::PREFIX)) {
            return;
        }

        $relative_class = substr($class, strlen(self::PREFIX));

        if (false === $relative_class || '' === $relative_class) {
            return;
        }

        $relative_path = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);

        $file = __DIR__ . DIRECTORY_SEPARATOR . $relative_path . '.php';

        if (is_readable($file)) {
            require_once $file;
        }
    }
}
