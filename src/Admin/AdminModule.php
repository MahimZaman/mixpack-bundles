<?php

namespace MixPack\Bundles\Admin;

use MixPack\Bundles\Contracts\Module;
use MixPack\Bundles\Product\BundleProduct;

defined('ABSPATH') || exit;

final class AdminModule implements Module
{

    public function register()
    {
        add_filter('woocommerce_product_data_tabs', array($this, 'add_product_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'render_panel'));
        add_action('woocommerce_admin_process_product_object', array($this, 'save'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function add_product_tab($tabs)
    {
        $tabs['mixpack_bundle'] = array(
            'label'    => __('MixPack', 'mixpack-bundles'),
            'target'   => 'mixpack_bundle_product_data',
            'class'    => array('show_if_mixpack_bundle'),
            'priority' => 20,
        );

        foreach (array('general', 'inventory', 'shipping', 'linked_product', 'attribute', 'variations') as $key) {
            if (isset($tabs[$key])) {
                $tabs[$key]['class'][] = 'hide_if_mixpack_bundle';
            }
        }

        return $tabs;
    }

    public function render_panel()
    {
        global $post, $product_object;

        $config = $this->get_config($product_object);
        $group  = $config['groups'][0];

        wp_nonce_field('mixpack_bundles_save_config', 'mixpack_bundles_nonce');
?>
        <div id="mixpack_bundle_product_data" class="panel woocommerce_options_panel hidden">

            <div class="options_group">
                <?php
                woocommerce_wp_select(
                    array(
                        'id'          => 'mixpack_pricing_mode',
                        'label'       => __('Pricing method', 'mixpack-bundles'),
                        'description' => __('Choose how the bundle price is calculated.', 'mixpack-bundles'),
                        'desc_tip'    => true,
                        'value'       => $config['pricing_mode'],
                        'options'     => array(
                            'fixed'      => __('Fixed price per pack', 'mixpack-bundles'),
                            'calculated' => __('Calculate from selected products', 'mixpack-bundles'),
                        ),
                    )
                );
                ?>
            </div>

            <div class="options_group">
                <p class="form-field mixpack-pack-sizes-field">
                    <label>
                        <?php esc_html_e('Pack sizes', 'mixpack-bundles'); ?>
                        <?php
                        echo wp_kses_post(
                            wc_help_tip(
                                __('Add the pack quantities customers can choose from.', 'mixpack-bundles')
                            )
                        );
                        ?>
                    </label>

                    <span class="mixpack-field-content">
                        <span id="mixpack-pack-rows" class="mixpack-pack-rows">

                            <?php foreach ($config['pack_sizes'] as $pack) : ?>
                                <span class="mixpack-pack-row">

                                    <span class="mixpack-pack-input">
                                        <span class="mixpack-input-label">
                                            <?php esc_html_e('Quantity', 'mixpack-bundles'); ?>
                                        </span>

                                        <input
                                            type="number"
                                            name="mixpack_pack_quantity[]"
                                            min="1"
                                            step="1"
                                            value="<?php echo esc_attr($pack['quantity']); ?>">
                                    </span>

                                    <span class="mixpack-pack-input mixpack-price-field">
                                        <span class="mixpack-input-label">
                                            <?php esc_html_e('Price', 'mixpack-bundles'); ?>
                                        </span>

                                        <span class="mixpack-price-input">
                                            <span class="mixpack-currency">
                                                <?php echo esc_html(get_woocommerce_currency_symbol()); ?>
                                            </span>

                                            <input
                                                type="text"
                                                name="mixpack_pack_price[]"
                                                class="wc_input_price"
                                                value="<?php echo esc_attr($pack['price']); ?>">
                                        </span>
                                    </span>

                                    <button
                                        type="button"
                                        class="button-link-delete mixpack-remove-pack">
                                        <?php esc_html_e('Remove', 'mixpack-bundles'); ?>
                                    </button>

                                </span>
                            <?php endforeach; ?>

                        </span>

                        <button
                            type="button"
                            class="button mixpack-add-pack"
                            id="mixpack-add-pack">
                            <?php esc_html_e('Add pack size', 'mixpack-bundles'); ?>
                        </button>
                    </span>
                </p>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_select(
                    array(
                        'id'          => 'mixpack_product_source',
                        'label'       => __('Products from', 'mixpack-bundles'),
                        'description' => __('Choose individual products or use products from selected categories.', 'mixpack-bundles'),
                        'desc_tip'    => true,
                        'value'       => $group['source'],
                        'options'     => array(
                            'products'   => __('Selected products', 'mixpack-bundles'),
                            'categories' => __('Product categories', 'mixpack-bundles'),
                        ),
                    )
                );
                ?>

                <p class="form-field mixpack-source-products">
                    <label for="mixpack_product_ids">
                        <?php esc_html_e('Products', 'mixpack-bundles'); ?>
                        <?php
                        echo wc_help_tip(
                            __('Search for the simple products customers can add to this bundle.', 'mixpack-bundles')
                        );
                        ?>
                    </label>

                    <select
                        id="mixpack_product_ids"
                        name="mixpack_product_ids[]"
                        class="wc-product-search"
                        multiple="multiple"
                        data-placeholder="<?php esc_attr_e('Search for products…', 'mixpack-bundles'); ?>"
                        data-action="woocommerce_json_search_products"
                        data-exclude="<?php echo esc_attr($post->ID); ?>">
                        <?php foreach ($group['product_ids'] as $product_id) : ?>
                            <?php $product = wc_get_product($product_id); ?>

                            <?php if ($product) : ?>
                                <option value="<?php echo esc_attr($product_id); ?>" selected>
                                    <?php echo esc_html(wp_strip_all_tags($product->get_formatted_name())); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p class="form-field mixpack-source-categories">
                    <label for="mixpack_category_ids">
                        <?php esc_html_e('Categories', 'mixpack-bundles'); ?>
                        <?php
                        echo wc_help_tip(
                            __('Products from these categories will be available in the bundle.', 'mixpack-bundles')
                        );
                        ?>
                    </label>

                    <select
                        id="mixpack_category_ids"
                        name="mixpack_category_ids[]"
                        class="wc-enhanced-select"
                        multiple="multiple"
                        data-placeholder="<?php esc_attr_e('Choose categories…', 'mixpack-bundles'); ?>">
                        <?php
                        $categories = get_terms(
                            array(
                                'taxonomy'   => 'product_cat',
                                'hide_empty' => false,
                            )
                        );

                        if (! is_wp_error($categories)) :
                            foreach ($categories as $category) :
                        ?>
                                <option
                                    value="<?php echo esc_attr($category->term_id); ?>"
                                    <?php selected(in_array($category->term_id, $group['category_ids'], true)); ?>>
                                    <?php echo esc_html($category->name); ?>
                                </option>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </select>
                </p>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_checkbox(
                    array(
                        'id'          => 'mixpack_allow_duplicates',
                        'label'       => __('Multiple quantities', 'mixpack-bundles'),
                        'description' => __('Allow customers to choose more than one of the same product.', 'mixpack-bundles'),
                        'value'       => $group['allow_duplicates'] ? 'yes' : 'no',
                    )
                );
                ?>
            </div>

        </div>
<?php
    }

    public function save($product)
    {
        if (
            empty($_POST['mixpack_bundles_nonce']) ||
            ! wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['mixpack_bundles_nonce'])),
                'mixpack_bundles_save_config'
            )
        ) {
            return;
        }

        $product_type = isset($_POST['product-type'])
            ? sanitize_key(wp_unslash($_POST['product-type']))
            : '';

        if ('mixpack_bundle' !== $product_type) {
            return;
        }

        if (! current_user_can('edit_post', $product->get_id())) {
            return;
        }

        $pricing_mode = isset($_POST['mixpack_pricing_mode'])
            ? sanitize_key(wp_unslash($_POST['mixpack_pricing_mode']))
            : 'fixed';

        if (! in_array($pricing_mode, array('fixed', 'calculated'), true)) {
            $pricing_mode = 'fixed';
        }

        $pack_sizes = $this->get_pack_sizes_from_request();

        $source = isset($_POST['mixpack_product_source'])
            ? sanitize_key(wp_unslash($_POST['mixpack_product_source']))
            : 'products';

        if (! in_array($source, array('products', 'categories'), true)) {
            $source = 'products';
        }

        $config = array(
            'schema'       => 1,
            'pricing_mode' => $pricing_mode,
            'pack_sizes'   => $pack_sizes,
            'groups'       => array(
                array(
                    'id'               => 'default',
                    'label'            => __('Products', 'mixpack-bundles'),
                    'source'           => $source,
                    'product_ids'      => $this->get_product_ids_from_request($product->get_id()),
                    'category_ids'     => $this->get_category_ids_from_request(),
                    'allow_duplicates' => isset($_POST['mixpack_allow_duplicates']),
                ),
            ),
        );

        if (! $product instanceof BundleProduct) {
            return;
        }

        $product->set_bundle_config($config);

        $errors = $product->get_configuration_errors();

        foreach ($errors->get_error_messages() as $message) {
            \WC_Admin_Meta_Boxes::add_error($message);
        }
    }

    public function enqueue_assets()
    {
        $screen = get_current_screen();

        if (
            ! $screen ||
            'product' !== $screen->post_type ||
            ! in_array($screen->base, array('post', 'post-new'), true)
        ) {
            return;
        }

        wp_enqueue_style(
            'mixpack-bundles-admin',
            MIXPACK_BUNDLES_URL . 'assets/css/admin.css',
            array(),
            MIXPACK_BUNDLES_VERSION
        );

        wp_enqueue_script(
            'mixpack-bundles-admin',
            MIXPACK_BUNDLES_URL . 'assets/js/admin.js',
            array('jquery', 'wc-enhanced-select'),
            MIXPACK_BUNDLES_VERSION,
            true
        );
    }

    private function get_config($product)
    {
        if ($product instanceof BundleProduct) {
            $config = $product->get_bundle_config();
        } else {
            $config = $product
                ? $product->get_meta(BundleProduct::CONFIG_META_KEY, true)
                : array();
        }

        if (empty($config['pack_sizes'])) {
            $config['pack_sizes'] = array(
                array('quantity' => 3, 'price' => ''),
                array('quantity' => 6, 'price' => ''),
                array('quantity' => 12, 'price' => ''),
            );
        }

        $config['pricing_mode'] = $config['pricing_mode'] ?? 'fixed';

        if (empty($config['groups'][0])) {
            $config['groups'][0] = array(
                'id'               => 'default',
                'label'            => __('Products', 'mixpack-bundles'),
                'source'           => 'products',
                'product_ids'      => array(),
                'category_ids'     => array(),
                'allow_duplicates' => true,
            );
        }

        return $config;
    }

    private function get_pack_sizes_from_request()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in save().
        $quantities = isset($_POST['mixpack_pack_quantity'])
            ? array_map(
                'absint',
                (array) wp_unslash($_POST['mixpack_pack_quantity'])
            )
            : array();

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in save().
        $prices = isset($_POST['mixpack_pack_price'])
            ? array_map(
                'wc_format_decimal',
                (array) wp_unslash($_POST['mixpack_pack_price'])
            )
            : array();

        $packs = array();

        foreach ($quantities as $index => $quantity) {
            $quantity = (int) $quantity;

            if ($quantity < 1) {
                continue;
            }

            if (isset($packs[$quantity])) {
                \WC_Admin_Meta_Boxes::add_error(
                    __('Pack quantities must be unique.', 'mixpack-bundles')
                );

                continue;
            }

            $packs[$quantity] = array(
                'quantity' => $quantity,
                'price'    => isset($prices[$index])
                    ? wc_format_decimal($prices[$index])
                    : '',
            );
        }

        ksort($packs, SORT_NUMERIC);

        return array_values($packs);
    }

    private function get_product_ids_from_request($bundle_id)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in save().
        $ids = isset($_POST['mixpack_product_ids'])
            ? array_map('absint', (array) wp_unslash($_POST['mixpack_product_ids']))
            : array();

        $valid = array();

        foreach (array_unique($ids) as $id) {
            if (! $id || $id === $bundle_id) {
                continue;
            }

            $product = wc_get_product($id);

            if ($product && $product->is_type('simple')) {
                $valid[] = $id;
            }
        }

        return $valid;
    }

    private function get_category_ids_from_request()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in save().
        $ids = isset($_POST['mixpack_category_ids'])
            ? array_map('absint', (array) wp_unslash($_POST['mixpack_category_ids']))
            : array();

        return array_values(
            array_filter(
                array_unique($ids),
                static function ($id) {
                    return (bool) term_exists($id, 'product_cat');
                }
            )
        );
    }
}
