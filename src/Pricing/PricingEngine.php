<?php

namespace MahimZaman\BuildABundle\Pricing;

use MahimZaman\BuildABundle\Product\BundleProduct;

defined('ABSPATH') || exit;

final class PricingEngine
{

    public function calculate(BundleProduct $bundle, $pack_quantity, $selections)
    {
        $pack_quantity = absint($pack_quantity);

        if (! $bundle->get_pack($pack_quantity)) {
            return null;
        }

        if ('calculated' === $bundle->get_pricing_mode()) {
            return $this->calculate_from_products($selections);
        }

        return $this->get_fixed_pack_price($bundle, $pack_quantity);
    }

    private function get_fixed_pack_price(BundleProduct $bundle, $pack_quantity)
    {
        foreach ($bundle->get_pack_sizes() as $pack) {
            if ((int) $pack['quantity'] !== $pack_quantity) {
                continue;
            }

            if ('' === $pack['price']) {
                return null;
            }

            return (float) wc_format_decimal($pack['price']);
        }

        return null;
    }

    private function calculate_from_products($selections)
    {
        if (! is_array($selections)) {
            return null;
        }

        $total = 0.0;

        foreach ($selections as $product_id => $quantity) {
            $product_id = absint($product_id);
            $quantity   = absint($quantity);

            if (! $product_id || ! $quantity) {
                continue;
            }

            $product = wc_get_product($product_id);

            if (! $product || '' === $product->get_price()) {
                return null;
            }

            $total += (float) $product->get_price() * $quantity;
        }

        return (float) wc_format_decimal($total);
    }
}
