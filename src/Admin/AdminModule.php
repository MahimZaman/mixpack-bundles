<?php

namespace MahimZaman\BuildABundle\Admin;

use MahimZaman\BuildABundle\Contracts\Module;
use MahimZaman\BuildABundle\Product\BundleProduct;

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
        $tabs['mahimzaman_bundle'] = array(
            'label'    => __('Build-a-Bundle', 'mahimzaman-build-a-bundle-for-woocommerce'),
            'target'   => 'mahimzaman_bundle_product_data',
            'class'    => array('show_if_mahimzaman_bundle'),
            'priority' => 20,
        );

        foreach (array('general', 'inventory', 'shipping', 'linked_product', 'attribute', 'variations') as $key) {
            if (isset($tabs[$key])) {
                $tabs[$key]['class'][] = 'hide_if_mahimzaman_bundle';
            }
        }

        return $tabs;
    }

    public function render_panel()
    {
        global $post, $product_object;

        $config = $this->get_config($product_object);
        $group  = $config['groups'][0];

        wp_nonce_field('mahimzaman_bab_save_config', 'mahimzaman_bab_nonce');
?>
        <div id="mahimzaman_bundle_product_data" class="panel woocommerce_options_panel hidden">

            <div class="options_group">
                <?php
                woocommerce_wp_select(
                    array(
                        'id'          => 'mahimzaman_bab_pricing_mode',
                        'label'       => __('Pricing method', 'mahimzaman-build-a-bundle-for-woocommerce'),
                        'description' => __('Choose how the bundle price is calculated.', 'mahimzaman-build-a-bundle-for-woocommerce'),
                        'desc_tip'    => true,
                        'value'       => $config['pricing_mode'],
                        'options'     => array(
                            'fixed'      => __('Fixed price per pack', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            'calculated' => __('Calculate from selected products', 'mahimzaman-build-a-bundle-for-woocommerce'),
                        ),
                    )
                );
                ?>
            </div>

            <div class="options_group">
                <p class="form-field mahimzaman-bab-pack-sizes-field">
                    <label>
                        <?php esc_html_e('Pack sizes', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>

                        <?php
                        echo wp_kses_post(
                            wc_help_tip(
                                __('Add the pack quantities customers can choose from.', 'mahimzaman-build-a-bundle-for-woocommerce')
                            )
                        );
                        ?>
                    </label>

                    <span class="mahimzaman-bab-field-content">
                        <span id="mahimzaman-bab-pack-rows" class="mahimzaman-bab-pack-rows">

                            <?php foreach ($config['pack_sizes'] as $pack) : ?>
                                <span class="mahimzaman-bab-pack-row">

                                    <span class="mahimzaman-bab-pack-input">
                                        <span class="mahimzaman-bab-input-label">
                                            <?php esc_html_e('Quantity', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                                        </span>

                                        <input
                                            type="number"
                                            name="mahimzaman_bab_pack_quantity[]"
                                            min="1"
                                            step="1"
                                            value="<?php echo esc_attr($pack['quantity']); ?>">
                                    </span>

                                    <span class="mahimzaman-bab-pack-input mahimzaman-bab-price-field">
                                        <span class="mahimzaman-bab-input-label">
                                            <?php esc_html_e('Price', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                                        </span>

                                        <span class="mahimzaman-bab-price-input">
                                            <span class="mahimzaman-bab-currency">
                                                <?php echo esc_html(get_woocommerce_currency_symbol()); ?>
                                            </span>

                                            <input
                                                type="text"
                                                name="mahimzaman_bab_pack_price[]"
                                                class="wc_input_price"
                                                value="<?php echo esc_attr($pack['price']); ?>">
                                        </span>
                                    </span>

                                    <button
                                        type="button"
                                        class="button-link-delete mahimzaman-bab-remove-pack">
                                        <?php esc_html_e('Remove', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                                    </button>

                                </span>
                            <?php endforeach; ?>

                        </span>

                        <button
                            type="button"
                            class="button mahimzaman-bab-add-pack"
                            id="mahimzaman-bab-add-pack">
                            <?php esc_html_e('Add pack size', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                        </button>
                    </span>
                </p>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_select(
                    array(
                        'id'          => 'mahimzaman_bab_product_source',
                        'label'       => __('Products from', 'mahimzaman-build-a-bundle-for-woocommerce'),
                        'description' => __('Choose individual products or use products from selected categories.', 'mahimzaman-build-a-bundle-for-woocommerce'),
                        'desc_tip'    => true,
                        'value'       => $group['source'],
                        'options'     => array(
                            'products'   => __('Selected products', 'mahimzaman-build-a-bundle-for-woocommerce'),
                            'categories' => __('Product categories', 'mahimzaman-build-a-bundle-for-woocommerce'),
                        ),
                    )
                );
                ?>

                <p class="form-field mahimzaman-bab-source-products">
                    <label for="mahimzaman_bab_product_ids">
                        <?php esc_html_e('Products', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>

                        <?php
                        echo wp_kses_post(
                            wc_help_tip(
                                __('Search for the simple products customers can add to this bundle.', 'mahimzaman-build-a-bundle-for-woocommerce')
                            )
                        );
                        ?>
                    </label>

                    <select
                        id="mahimzaman_bab_product_ids"
                        name="mahimzaman_bab_product_ids[]"
                        class="wc-product-search"
                        multiple="multiple"
                        data-placeholder="<?php esc_attr_e('Search for products…', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>"
                        data-action="woocommerce_json_search_products"
                        data-exclude="<?php echo esc_attr($post->ID); ?>">
                        <?php foreach ($group['product_ids'] as $product_id) : ?>
                            <?php $product = wc_get_product($product_id); ?>

                            <?php if ($product) : ?>
                                <option
                                    value="<?php echo esc_attr($product_id); ?>"
                                    selected>
                                    <?php echo esc_html(wp_strip_all_tags($product->get_formatted_name())); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p class="form-field mahimzaman-bab-source-categories">
                    <label for="mahimzaman_bab_category_ids">
                        <?php esc_html_e('Categories', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>

                        <?php
                        echo wp_kses_post(
                            wc_help_tip(
                                __('Products from these categories will be available in the bundle.', 'mahimzaman-build-a-bundle-for-woocommerce')
                            )
                        );
                        ?>
                    </label>

                    <select
                        id="mahimzaman_bab_category_ids"
                        name="mahimzaman_bab_category_ids[]"
                        class="wc-enhanced-select"
                        multiple="multiple"
                        data-placeholder="<?php esc_attr_e('Choose categories…', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>">
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
                        'id'          => 'mahimzaman_bab_allow_duplicates',
                        'label'       => __('Multiple quantities', 'mahimzaman-build-a-bundle-for-woocommerce'),
                        'description' => __('Allow customers to choose more than one of the same product.', 'mahimzaman-build-a-bundle-for-woocommerce'),
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
        if (empty($_POST['mahimzaman_bab_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(
            wp_unslash($_POST['mahimzaman_bab_nonce'])
        );

        if (! wp_verify_nonce($nonce, 'mahimzaman_bab_save_config')) {
            return;
        }

        if (! current_user_can('edit_post', $product->get_id())) {
            return;
        }

        $product_type = isset($_POST['product-type'])
            ? sanitize_key(wp_unslash($_POST['product-type']))
            : '';

        if ('mahimzaman_bundle' !== $product_type) {
            return;
        }

        if (! $product instanceof BundleProduct) {
            return;
        }

        $pricing_mode = isset($_POST['mahimzaman_bab_pricing_mode'])
            ? sanitize_key(wp_unslash($_POST['mahimzaman_bab_pricing_mode']))
            : 'fixed';

        if (! in_array($pricing_mode, array('fixed', 'calculated'), true)) {
            $pricing_mode = 'fixed';
        }

        $source = isset($_POST['mahimzaman_bab_product_source'])
            ? sanitize_key(wp_unslash($_POST['mahimzaman_bab_product_source']))
            : 'products';

        if (! in_array($source, array('products', 'categories'), true)) {
            $source = 'products';
        }

        $pack_quantities = isset($_POST['mahimzaman_bab_pack_quantity'])
            ? array_map(
                'absint',
                (array) wp_unslash($_POST['mahimzaman_bab_pack_quantity'])
            )
            : array();

        $pack_prices = isset($_POST['mahimzaman_bab_pack_price'])
            ? array_map(
                'sanitize_text_field',
                (array) wp_unslash($_POST['mahimzaman_bab_pack_price'])
            )
            : array();

        $product_ids = isset($_POST['mahimzaman_bab_product_ids'])
            ? array_map(
                'absint',
                (array) wp_unslash($_POST['mahimzaman_bab_product_ids'])
            )
            : array();

        $category_ids = isset($_POST['mahimzaman_bab_category_ids'])
            ? array_map(
                'absint',
                (array) wp_unslash($_POST['mahimzaman_bab_category_ids'])
            )
            : array();

        $config = array(
            'schema'       => 1,
            'pricing_mode' => $pricing_mode,
            'pack_sizes'   => $this->build_pack_sizes(
                $pack_quantities,
                $pack_prices
            ),
            'groups'       => array(
                array(
                    'id'               => 'default',
                    'label'            => __('Products', 'mahimzaman-build-a-bundle-for-woocommerce'),
                    'source'           => $source,
                    'product_ids'      => $this->validate_product_ids(
                        $product->get_id(),
                        $product_ids
                    ),
                    'category_ids'     => $this->validate_category_ids(
                        $category_ids
                    ),
                    'allow_duplicates' => isset($_POST['mahimzaman_bab_allow_duplicates']),
                ),
            ),
        );

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
            'mahimzaman-build-a-bundle-for-woocommerce-admin',
            MAHIMZAMAN_BAB_URL . 'assets/css/admin.css',
            array(),
            MAHIMZAMAN_BAB_VERSION
        );

        wp_enqueue_script(
            'mahimzaman-build-a-bundle-for-woocommerce-admin',
            MAHIMZAMAN_BAB_URL . 'assets/js/admin.js',
            array('jquery', 'wc-enhanced-select'),
            MAHIMZAMAN_BAB_VERSION,
            true
        );
    }

    private function get_config($product)
    {
        $config = array();

        if ($product instanceof BundleProduct) {
            $config = $product->get_bundle_config();
        } elseif ($product instanceof \WC_Product) {
            $stored_config = $product->get_meta(
                BundleProduct::CONFIG_META_KEY,
                true
            );

            if (is_array($stored_config)) {
                $config = $stored_config;
            }
        }

        if (empty($config['pack_sizes'])) {
            $config['pack_sizes'] = array(
                array(
                    'quantity' => 3,
                    'price'    => '',
                ),
                array(
                    'quantity' => 6,
                    'price'    => '',
                ),
                array(
                    'quantity' => 12,
                    'price'    => '',
                ),
            );
        }

        $config['pricing_mode'] = isset($config['pricing_mode'])
            ? $config['pricing_mode']
            : 'fixed';

        if (empty($config['groups'][0])) {
            $config['groups'] = array(
                array(
                    'id'               => 'default',
                    'label'            => __('Products', 'mahimzaman-build-a-bundle-for-woocommerce'),
                    'source'           => 'products',
                    'product_ids'      => array(),
                    'category_ids'     => array(),
                    'allow_duplicates' => true,
                ),
            );
        }

        return $config;
    }

    private function build_pack_sizes($quantities, $prices)
    {
        $packs = array();

        foreach ($quantities as $index => $quantity) {
            $quantity = absint($quantity);

            if ($quantity < 1) {
                continue;
            }

            if (isset($packs[$quantity])) {
                \WC_Admin_Meta_Boxes::add_error(
                    __('Pack quantities must be unique.', 'mahimzaman-build-a-bundle-for-woocommerce')
                );

                continue;
            }

            $price = isset($prices[$index])
                ? wc_format_decimal($prices[$index])
                : '';

            $packs[$quantity] = array(
                'quantity' => $quantity,
                'price'    => $price,
            );
        }

        ksort($packs, SORT_NUMERIC);

        return array_values($packs);
    }

    private function validate_product_ids($bundle_id, $ids)
    {
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

    private function validate_category_ids($ids)
    {
        return array_values(
            array_filter(
                array_unique($ids),
                static function ($id) {
                    return (bool) term_exists(
                        $id,
                        'product_cat'
                    );
                }
            )
        );
    }
}
