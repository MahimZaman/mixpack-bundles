<?php

namespace MahimZaman\BuildABundle\Admin;

use MahimZaman\BuildABundle\Contracts\Module;

defined('ABSPATH') || exit;

final class SettingsModule implements Module
{

    const OPTION_KEY = 'mahimzaman_bab_appearance';

    public function register()
    {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action(
            'admin_post_mahimzaman_bab_reset_appearance',
            array($this, 'reset_settings')
        );
    }

    public static function defaults()
    {
        return array(
            'primary'     => '#2271b1',
            'button_bg'   => '#2271b1',
            'button_text' => '#ffffff',
            'builder_bg'  => '#ffffff',
            'card_bg'     => '#ffffff',
            'text'        => '#1d2327',
            'muted'       => '#646970',
            'border'      => '#dcdcde',
        );
    }

    public static function get_values()
    {
        $saved = get_option(self::OPTION_KEY, array());

        if (! is_array($saved)) {
            $saved = array();
        }

        return array_merge(self::defaults(), $saved);
    }

    public function add_menu()
    {
        add_menu_page(
            __('Build-a-Bundle Settings', 'mahimzaman-build-a-bundle-for-woocommerce'),
            __('Build-a-Bundle', 'mahimzaman-build-a-bundle-for-woocommerce'),
            'manage_woocommerce',
            'mahimzaman-build-a-bundle-for-woocommerce',
            array($this, 'render_page'),
            'dashicons-products',
            56
        );
    }

    public function register_settings()
    {
        register_setting(
            'mahimzaman_bab_appearance',
            self::OPTION_KEY,
            array(
                'type'              => 'array',
                'sanitize_callback' => array($this, 'sanitize'),
                'default'           => self::defaults(),
            )
        );
    }

    public function sanitize($input)
    {
        $defaults = self::defaults();
        $output   = array();

        foreach ($defaults as $key => $default) {
            $value = isset($input[$key])
                ? sanitize_hex_color($input[$key])
                : '';

            $output[$key] = $value ? $value : $default;
        }

        return $output;
    }

    public function enqueue_assets($hook)
    {
        if ('toplevel_page_mahimzaman-build-a-bundle-for-woocommerce' !== $hook) {
            return;
        }

        wp_enqueue_style('wp-color-picker');

        wp_enqueue_style(
            'mahimzaman-build-a-bundle-for-woocommerce-settings',
            MAHIMZAMAN_BAB_URL . 'assets/css/settings.css',
            array('wp-color-picker'),
            MAHIMZAMAN_BAB_VERSION
        );

        wp_enqueue_script(
            'mahimzaman-build-a-bundle-for-woocommerce-settings',
            MAHIMZAMAN_BAB_URL . 'assets/js/settings.js',
            array('jquery', 'wp-color-picker'),
            MAHIMZAMAN_BAB_VERSION,
            true
        );
    }

    public function reset_settings()
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(
                esc_html__('You are not allowed to perform this action.', 'mahimzaman-build-a-bundle-for-woocommerce')
            );
        }

        check_admin_referer('mahimzaman_bab_reset_appearance');

        update_option(
            self::OPTION_KEY,
            self::defaults(),
            false
        );

        wp_safe_redirect(
            admin_url('admin.php?page=mahimzaman-build-a-bundle-for-woocommerce')
        );

        exit;
    }

    public function render_page()
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $settings = self::get_values();
?>
        <div class="wrap mahimzaman-bab-settings">

            <div class="mahimzaman-bab-settings-header">
                <div>
                    <h1><?php esc_html_e('Build-a-Bundle Settings', 'mahimzaman-build-a-bundle-for-woocommerce'); ?></h1>
                    <p>
                        <?php esc_html_e('Customize the global appearance of your Build-a-Bundle bundle builder.', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                    </p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('mahimzaman_bab_appearance'); ?>

                <div class="mahimzaman-bab-settings-grid">

                    <div class="mahimzaman-bab-settings-card">
                        <h2><?php esc_html_e('Brand Colors', 'mahimzaman-build-a-bundle-for-woocommerce'); ?></h2>

                        <?php
                        $this->color_field(
                            'primary',
                            __('Accent color', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            $settings
                        );

                        $this->color_field(
                            'button_bg',
                            __('Button background', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            $settings
                        );

                        $this->color_field(
                            'button_text',
                            __('Button text', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            $settings
                        );
                        ?>
                    </div>

                    <div class="mahimzaman-bab-settings-card">
                        <h2><?php esc_html_e('Builder Colors', 'mahimzaman-build-a-bundle-for-woocommerce'); ?></h2>

                        <?php
                        $this->color_field(
                            'builder_bg',
                            __('Builder background', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            $settings
                        );

                        $this->color_field(
                            'card_bg',
                            __('Product card background', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            $settings
                        );

                        $this->color_field(
                            'text',
                            __('Text color', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            $settings
                        );

                        $this->color_field(
                            'muted',
                            __('Muted text color', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            $settings
                        );

                        $this->color_field(
                            'border',
                            __('Border color', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            $settings
                        );
                        ?>
                    </div>

                </div>

                <div class="mahimzaman-bab-settings-actions">
                    <?php submit_button(__('Save Changes', 'mahimzaman-build-a-bundle-for-woocommerce'), 'primary', 'submit', false); ?>
                </div>

            </form>

            <form
                method="post"
                action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                class="mahimzaman-bab-reset-form">
                <input
                    type="hidden"
                    name="action"
                    value="mahimzaman_bab_reset_appearance">

                <?php wp_nonce_field('mahimzaman_bab_reset_appearance'); ?>

                <button type="submit" class="button">
                    <?php esc_html_e('Reset to Defaults', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                </button>
            </form>

        </div>
    <?php
    }

    private function color_field($key, $label, $settings)
    {
        $defaults = self::defaults();
    ?>
        <div class="mahimzaman-bab-setting-row">
            <div class="mahimzaman-bab-setting-label">
                <label for="mahimzaman_bab_<?php echo esc_attr($key); ?>">
                    <?php echo esc_html($label); ?>
                </label>
            </div>

            <div class="mahimzaman-bab-setting-control">
                <input
                    id="mahimzaman_bab_<?php echo esc_attr($key); ?>"
                    type="text"
                    class="mahimzaman-bab-color-field"
                    name="<?php echo esc_attr(self::OPTION_KEY); ?>[<?php echo esc_attr($key); ?>]"
                    value="<?php echo esc_attr($settings[$key]); ?>"
                    data-default-color="<?php echo esc_attr($defaults[$key]); ?>">
            </div>
        </div>
<?php
    }
}
