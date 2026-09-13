<?php

namespace MixPack\Bundles\Cart;

use MixPack\Bundles\Contracts\Module;
use MixPack\Bundles\Pricing\PricingEngine;
use MixPack\Bundles\Product\BundleProduct;
use MixPack\Bundles\Validation\SelectionValidator;

defined('ABSPATH') || exit;

final class CartModule implements Module
{

    public function register()
    {
        add_filter('woocommerce_add_to_cart_handler', array($this, 'use_simple_handler'), 10, 2);
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate'), 10, 3);
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 4);

        add_action('woocommerce_before_calculate_totals', array($this, 'apply_cart_data'));
        add_action('woocommerce_check_cart_items', array($this, 'validate_cart_items'));

        add_filter('woocommerce_get_item_data', array($this, 'display_item_data'), 10, 2);
        add_filter('woocommerce_cart_item_name', array($this, 'add_edit_link'), 10, 3);

        add_action(
            'woocommerce_add_to_cart',
            array($this, 'replace_edited_item'),
            20,
            6
        );
    }

    public function use_simple_handler($handler, $product)
    {
        return $product instanceof BundleProduct ? 'simple' : $handler;
    }

    public function validate($passed, $product_id, $quantity)
    {
        $product = wc_get_product($product_id);

        if (! $product instanceof BundleProduct) {
            return $passed;
        }

        if (
            empty($_POST['mixpack_cart_nonce']) ||
            ! wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['mixpack_cart_nonce'])),
                'mixpack_add_to_cart'
            )
        ) {
            wc_add_notice(
                __('Unable to validate the bundle request. Please try again.', 'mixpack-bundles'),
                'error'
            );

            return false;
        }

        if (! $this->validate_edit_request($product_id)) {
            wc_add_notice(
                __('The bundle you are editing is no longer available in your cart.', 'mixpack-bundles'),
                'error'
            );

            return false;
        }

        $pack       = $this->get_pack_from_request();
        $selections = $this->get_selections_from_request();

        $errors = (new SelectionValidator())->validate(
            $product,
            $pack,
            $selections,
            $quantity
        );

        if ($errors->has_errors()) {
            foreach ($errors->get_error_messages() as $message) {
                wc_add_notice($message, 'error');
            }

            return false;
        }

        $price = (new PricingEngine())->calculate(
            $product,
            $pack,
            $selections
        );

        if (null === $price) {
            wc_add_notice(
                __('The bundle price could not be calculated.', 'mixpack-bundles'),
                'error'
            );

            return false;
        }

        return $passed;
    }

    public function add_cart_item_data($cart_item_data, $product_id, $variation_id, $quantity)
    {
        $product = wc_get_product($product_id);

        if (! $product instanceof BundleProduct) {
            return $cart_item_data;
        }

        $pack       = $this->get_pack_from_request();
        $selections = $this->get_selections_from_request();

        $cart_item_data['mixpack'] = array(
            'pack'       => $pack,
            'selections' => $selections,
        );

        $cart_item_data['mixpack_key'] = md5(
            wp_json_encode(
                array(
                    'product_id' => $product_id,
                    'pack'       => $pack,
                    'selections' => $selections,
                )
            )
        );

        return $cart_item_data;
    }

    public function apply_cart_data($cart)
    {
        if (is_admin() && ! wp_doing_ajax()) {
            return;
        }

        $pricing = new PricingEngine();

        foreach ($cart->get_cart() as $cart_item) {
            if (
                empty($cart_item['mixpack']) ||
                ! $cart_item['data'] instanceof BundleProduct
            ) {
                continue;
            }

            $price = $pricing->calculate(
                $cart_item['data'],
                $cart_item['mixpack']['pack'],
                $cart_item['mixpack']['selections']
            );

            if (null !== $price) {
                $cart_item['data']->set_price($price);
            }

            $cart_item['data']->set_weight(
                $this->get_bundle_weight(
                    $cart_item['mixpack']['selections']
                )
            );
        }
    }

    public function validate_cart_items()
    {
        if (! WC()->cart) {
            return;
        }

        $validator          = new SelectionValidator();
        $stock_requirements = array();
        $stock_products     = array();

        foreach (WC()->cart->get_cart() as $cart_item) {
            if (
                ! empty($cart_item['mixpack']) &&
                $cart_item['data'] instanceof BundleProduct
            ) {
                $errors = $validator->validate(
                    $cart_item['data'],
                    $cart_item['mixpack']['pack'],
                    $cart_item['mixpack']['selections'],
                    $cart_item['quantity']
                );

                foreach ($errors->get_error_messages() as $message) {
                    $this->add_error_notice($message);
                }

                foreach ($cart_item['mixpack']['selections'] as $product_id => $quantity) {
                    $product = wc_get_product($product_id);

                    if (! $product) {
                        continue;
                    }

                    $this->add_stock_requirement(
                        $stock_requirements,
                        $stock_products,
                        $product,
                        absint($quantity) * absint($cart_item['quantity'])
                    );
                }

                continue;
            }

            if (! empty($cart_item['data']) && $cart_item['data'] instanceof \WC_Product) {
                $this->add_stock_requirement(
                    $stock_requirements,
                    $stock_products,
                    $cart_item['data'],
                    absint($cart_item['quantity'])
                );
            }
        }

        $this->validate_aggregate_stock(
            $stock_requirements,
            $stock_products
        );
    }

    public function display_item_data($item_data, $cart_item)
    {
        if (empty($cart_item['mixpack'])) {
            return $item_data;
        }

        $pack = absint($cart_item['mixpack']['pack']);

        $pack_label = sprintf(
            /* translators: %d: Number of products in the pack. */
            __('%d-Pack', 'mixpack-bundles'),
            $pack
        );

        $item_data[] = array(
            'key'     => __('Pack', 'mixpack-bundles'),
            'value'   => $pack_label,
            'display' => $pack_label,
        );

        $contents = array();

        foreach ($cart_item['mixpack']['selections'] as $product_id => $quantity) {
            $product = wc_get_product($product_id);

            if (! $product) {
                continue;
            }

            $contents[] = sprintf(
                '%d × %s',
                absint($quantity),
                $product->get_name()
            );
        }

        if ($contents) {
            $content = implode(', ', $contents);

            $item_data[] = array(
                'key'     => __('Contents', 'mixpack-bundles'),
                'value'   => $content,
                'display' => $content,
            );
        }

        return $item_data;
    }

    public function add_edit_link($name, $cart_item, $cart_item_key)
    {
        if (! is_cart() || empty($cart_item['mixpack'])) {
            return $name;
        }

        $product = $cart_item['data'];

        if (! $product instanceof BundleProduct) {
            return $name;
        }

        $url = add_query_arg(
            'mixpack_edit',
            $cart_item_key,
            $product->get_permalink()
        );

        $url = wp_nonce_url(
            $url,
            'mixpack_edit_' . $cart_item_key,
            'mixpack_edit_nonce'
        );

        return $name . sprintf(
            '<div><a href="%s">%s</a></div>',
            esc_url($url),
            esc_html__('Edit Bundle', 'mixpack-bundles')
        );
    }

    public function replace_edited_item(
        $cart_item_key,
        $product_id,
        $quantity,
        $variation_id,
        $variation,
        $cart_item_data
    ) {
        $old_key = $this->get_edit_cart_key_from_request();

        if (! $old_key || ! WC()->cart) {
            return;
        }

        if ($old_key === $cart_item_key) {
            WC()->cart->set_quantity(
                $cart_item_key,
                $quantity,
                false
            );

            return;
        }

        if (WC()->cart->get_cart_item($old_key)) {
            WC()->cart->remove_cart_item($old_key);
        }
    }

    private function validate_edit_request($product_id)
    {
        $key = $this->get_edit_cart_key_from_request();

        if (! $key) {
            return true;
        }

        if (! WC()->cart) {
            return false;
        }

        $item = WC()->cart->get_cart_item($key);

        return ! empty($item)
            && (int) $item['product_id'] === (int) $product_id
            && ! empty($item['mixpack']);
    }

    private function add_stock_requirement(&$requirements, &$products, $product, $quantity)
    {
        $managed_id = $product->get_stock_managed_by_id();

        if (! $managed_id || $quantity < 1) {
            return;
        }

        $requirements[$managed_id] =
            ($requirements[$managed_id] ?? 0) + $quantity;

        if (! isset($products[$managed_id])) {
            $managed_product = wc_get_product($managed_id);

            if ($managed_product) {
                $products[$managed_id] = $managed_product;
            }
        }
    }

    private function validate_aggregate_stock($requirements, $products)
    {
        $exclude_order_id = $this->get_current_order_id();

        foreach ($requirements as $product_id => $required) {
            if (empty($products[$product_id])) {
                continue;
            }

            $product = $products[$product_id];

            if (! $product->is_in_stock()) {
                $this->add_error_notice(
                    sprintf(
                        /* translators: %s: Product name. */
                        __(
                            '%s is out of stock.',
                            'mixpack-bundles'
                        ),
                        $product->get_name()
                    )
                );

                continue;
            }

            if (! $product->managing_stock() || $product->backorders_allowed()) {
                continue;
            }

            $held      = wc_get_held_stock_quantity($product, $exclude_order_id);
            $available = (int) $product->get_stock_quantity() - $held;

            if ($required > $available) {
                $this->add_error_notice(
                    sprintf(
                        /* translators: 1: Product name, 2: Available stock quantity. */
                        __(
                            'There is not enough stock available for %1$s. %2$d available.',
                            'mixpack-bundles'
                        ),
                        $product->get_name(),
                        max(0, $available)
                    )
                );
            }
        }
    }

    private function get_bundle_weight($selections)
    {
        $weight = 0.0;

        foreach ($selections as $product_id => $quantity) {
            $product = wc_get_product($product_id);

            if (! $product || ! $product->has_weight()) {
                continue;
            }

            $weight += (float) $product->get_weight() * absint($quantity);
        }

        return wc_format_decimal($weight);
    }

    private function add_error_notice($message)
    {
        if (! wc_has_notice($message, 'error')) {
            wc_add_notice($message, 'error');
        }
    }

    private function get_current_order_id()
    {
        if (! WC()->session) {
            return 0;
        }

        $order_id = absint(
            WC()->session->get('order_awaiting_payment', 0)
        );

        if ($order_id) {
            return $order_id;
        }

        return absint(
            WC()->session->get('store_api_draft_order', 0)
        );
    }

    private function get_request_data()
    {
        $data = array(
            'valid'         => false,
            'edit_cart_key' => '',
            'pack'          => 0,
            'selections'    => array(),
        );

        if (empty($_POST['mixpack_cart_nonce'])) {
            return $data;
        }

        $nonce = sanitize_text_field(
            wp_unslash($_POST['mixpack_cart_nonce'])
        );

        if (! wp_verify_nonce($nonce, 'mixpack_add_to_cart')) {
            return $data;
        }

        $data['valid'] = true;

        if (isset($_POST['mixpack_edit_cart_key'])) {
            $data['edit_cart_key'] = sanitize_text_field(
                wp_unslash($_POST['mixpack_edit_cart_key'])
            );
        }

        if (isset($_POST['mixpack_pack'])) {
            $data['pack'] = absint(
                wp_unslash($_POST['mixpack_pack'])
            );
        }

        if (
            isset($_POST['mixpack_products']) &&
            is_array($_POST['mixpack_products'])
        ) {
            $items = array_map(
                'absint',
                wp_unslash($_POST['mixpack_products'])
            );

            foreach ($items as $product_id => $quantity) {
                $product_id = absint($product_id);

                if ($product_id && $quantity) {
                    $data['selections'][$product_id] = $quantity;
                }
            }

            ksort($data['selections']);
        }

        return $data;
    }

    private function get_edit_cart_key_from_request()
    {
        $data = $this->get_request_data();

        return $data['edit_cart_key'];
    }

    private function get_pack_from_request()
    {
        $data = $this->get_request_data();

        return $data['pack'];
    }

    private function get_selections_from_request()
    {
        $data = $this->get_request_data();

        return $data['selections'];
    }
}
