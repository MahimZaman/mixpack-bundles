<?php

/**
 * Plugin requirement checks.
 *
 * @package MixPackBundles
 */

namespace MixPack\Bundles;

defined('ABSPATH') || exit;

/**
 * Handles runtime plugin requirement checks.
 */
final class Requirements
{

    /**
     * Minimum supported WordPress version.
     *
     * @var string
     */
    private const MINIMUM_WORDPRESS = '6.5';

    /**
     * Minimum supported PHP version.
     *
     * @var string
     */
    private const MINIMUM_PHP = '8.0';

    /**
     * Minimum supported WooCommerce version.
     *
     * @var string
     */
    private const MINIMUM_WOOCOMMERCE = '8.5';

    /**
     * Determine whether all runtime requirements are satisfied.
     *
     * @return bool
     */
    public static function is_satisfied()
    {
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP, '<')) {
            return false;
        }

        if (version_compare(get_bloginfo('version'), self::MINIMUM_WORDPRESS, '<')) {
            return false;
        }

        if (! class_exists('WooCommerce')) {
            return false;
        }

        if (
            defined('WC_VERSION') &&
            version_compare(WC_VERSION, self::MINIMUM_WOOCOMMERCE, '<')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Register the admin requirement notice.
     *
     * @return void
     */
    public static function register_admin_notice()
    {
        if (! is_admin()) {
            return;
        }

        add_action('admin_notices', array(__CLASS__, 'render_admin_notice'));
    }

    /**
     * Render requirement errors.
     *
     * @return void
     */
    public static function render_admin_notice()
    {
        if (! current_user_can('activate_plugins')) {
            return;
        }

        $message = self::get_error_message();

        if ('' === $message) {
            return;
        }
?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e('MixPack Bundles:', 'mixpack-bundles'); ?></strong>
                <?php echo esc_html($message); ?>
            </p>
        </div>
<?php
    }

    /**
     * Return the most relevant requirement error.
     *
     * @return string
     */
    private static function get_error_message()
    {
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP, '<')) {
            return sprintf(
                /* translators: %s: Minimum required PHP version. */
                __('PHP %s or newer is required.', 'mixpack-bundles'),
                self::MINIMUM_PHP
            );
        }

        if (version_compare(get_bloginfo('version'), self::MINIMUM_WORDPRESS, '<')) {
            return sprintf(
                /* translators: %s: Minimum required WordPress version. */
                __('WordPress %s or newer is required.', 'mixpack-bundles'),
                self::MINIMUM_WORDPRESS
            );
        }

        if (! class_exists('WooCommerce')) {
            return __(
                'WooCommerce must be installed and activated before this plugin can run.',
                'mixpack-bundles'
            );
        }

        if (
            defined('WC_VERSION') &&
            version_compare(WC_VERSION, self::MINIMUM_WOOCOMMERCE, '<')
        ) {
            return sprintf(
                /* translators: %s: Minimum required WooCommerce version. */
                __('WooCommerce %s or newer is required.', 'mixpack-bundles'),
                self::MINIMUM_WOOCOMMERCE
            );
        }

        return '';
    }
}
