<?php

namespace MixPack\Bundles\Compatibility;

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema;
use Automattic\WooCommerce\Utilities\FeaturesUtil;
use MixPack\Bundles\Contracts\Module;
use MixPack\Bundles\Product\BundleProduct;

defined('ABSPATH') || exit;

final class CompatibilityModule implements Module
{

    public function register()
    {
        add_action(
            'before_woocommerce_init',
            array($this, 'declare_compatibility')
        );

        add_action(
            'woocommerce_blocks_loaded',
            array($this, 'register_store_api_data')
        );
    }

    public function declare_compatibility()
    {
        if (! class_exists(FeaturesUtil::class)) {
            return;
        }

        FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            MIXPACK_BUNDLES_BASENAME,
            true
        );

        FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            MIXPACK_BUNDLES_BASENAME,
            true
        );
    }

    public function register_store_api_data()
    {
        if (
            ! class_exists(CartItemSchema::class) ||
            ! function_exists('woocommerce_store_api_register_endpoint_data')
        ) {
            return;
        }

        woocommerce_store_api_register_endpoint_data(
            array(
                'endpoint'        => CartItemSchema::IDENTIFIER,
                'namespace'       => 'mixpack-bundles',
                'data_callback'   => array($this, 'get_cart_item_data'),
                'schema_callback' => array($this, 'get_cart_item_schema'),
                'schema_type'     => ARRAY_A,
            )
        );
    }

    public function get_cart_item_data($cart_item)
    {
        if (
            empty($cart_item['mixpack']) ||
            empty($cart_item['data']) ||
            ! $cart_item['data'] instanceof BundleProduct
        ) {
            return array(
                'is_bundle' => false,
                'pack'      => 0,
                'items'     => array(),
            );
        }

        $items = array();

        foreach ($cart_item['mixpack']['selections'] as $product_id => $quantity) {
            $product = wc_get_product($product_id);

            if (! $product) {
                continue;
            }

            $items[] = array(
                'id'       => $product->get_id(),
                'name'     => $product->get_name(),
                'quantity' => absint($quantity),
            );
        }

        return array(
            'is_bundle' => true,
            'pack'      => absint($cart_item['mixpack']['pack']),
            'items'     => $items,
        );
    }

    public function get_cart_item_schema()
    {
        return array(
            'properties' => array(
                'is_bundle' => array(
                    'type'     => 'boolean',
                    'readonly' => true,
                ),
                'pack' => array(
                    'type'     => 'integer',
                    'readonly' => true,
                ),
                'items' => array(
                    'type'     => 'array',
                    'readonly' => true,
                    'items'    => array(
                        'type'       => 'object',
                        'properties' => array(
                            'id' => array(
                                'type' => 'integer',
                            ),
                            'name' => array(
                                'type' => 'string',
                            ),
                            'quantity' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
