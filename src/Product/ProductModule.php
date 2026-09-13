<?php

namespace MixPack\Bundles\Product;

use MixPack\Bundles\Contracts\Module;

defined('ABSPATH') || exit;

final class ProductModule implements Module
{

    public function register()
    {
        add_filter(
            'product_type_selector',
            array($this, 'add_product_type')
        );

        add_filter(
            'woocommerce_product_class',
            array($this, 'product_class'),
            10,
            2
        );
    }

    public function add_product_type($types)
    {
        $types['mixpack_bundle'] = __(
            'MixPack Bundle',
            'mixpack-bundles'
        );

        return $types;
    }

    public function product_class($classname, $product_type)
    {
        if ('mixpack_bundle' === $product_type) {
            return BundleProduct::class;
        }

        return $classname;
    }
}
