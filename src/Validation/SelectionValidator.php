<?php

namespace MahimZaman\BuildABundle\Validation;

use MahimZaman\BuildABundle\Product\BundleProduct;
use MahimZaman\BuildABundle\Product\ProductResolver;

defined('ABSPATH') || exit;

final class SelectionValidator
{

    public function validate(BundleProduct $bundle, $pack_quantity, $selections, $bundle_quantity = 1)
    {
        $errors        = new \WP_Error();
        $pack_quantity = absint($pack_quantity);
        $bundle_quantity = max(1, absint($bundle_quantity));
        $selections    = $this->normalize_selections($selections);

        if (! $bundle->is_configured()) {
            $errors->add(
                'invalid_bundle',
                __('This bundle is not configured correctly.', 'mahimzaman-build-a-bundle-for-woocommerce')
            );

            return $errors;
        }

        if (! $bundle->get_pack($pack_quantity)) {
            $errors->add(
                'invalid_pack',
                __('Please choose a valid pack size.', 'mahimzaman-build-a-bundle-for-woocommerce')
            );

            return $errors;
        }

        if (empty($selections)) {
            $errors->add(
                'empty_selection',
                __('Please choose products for your pack.', 'mahimzaman-build-a-bundle-for-woocommerce')
            );

            return $errors;
        }

        $total = array_sum($selections);

        if ($total !== $pack_quantity) {
            $errors->add(
                'invalid_quantity',
                sprintf(
                    /* translators: 1: selected quantity, 2: required quantity. */
                    __('You selected %1$d of %2$d required items.', 'mahimzaman-build-a-bundle-for-woocommerce'),
                    $total,
                    $pack_quantity
                )
            );
        }

        $group            = $bundle->get_groups()[0];
        $allow_duplicates = ! empty($group['allow_duplicates']);

        $resolver     = new ProductResolver();
        $eligible_ids = $resolver->get_ids($bundle);

        foreach ($selections as $product_id => $quantity) {
            if (! in_array($product_id, $eligible_ids, true)) {
                $errors->add(
                    'invalid_product',
                    __('One or more selected products are not available for this bundle.', 'mahimzaman-build-a-bundle-for-woocommerce')
                );

                continue;
            }

            if (! $allow_duplicates && $quantity > 1) {
                $errors->add(
                    'duplicates_not_allowed',
                    __('This bundle allows only one of each product.', 'mahimzaman-build-a-bundle-for-woocommerce')
                );
            }

            $product = wc_get_product($product_id);

            $required_stock = $quantity * $bundle_quantity;

            if (! $product || ! $product->has_enough_stock($required_stock)) {
                $errors->add(
                    'insufficient_stock',
                    sprintf(
                        /* translators: %s: Product name. */
                        __('There is not enough stock available for %s.', 'mahimzaman-build-a-bundle-for-woocommerce'),
                        $product ? $product->get_name() : __('a selected product', 'mahimzaman-build-a-bundle-for-woocommerce')
                    )
                );
            }
        }

        return $errors;
    }

    private function normalize_selections($selections)
    {
        if (! is_array($selections)) {
            return array();
        }

        $normalized = array();

        foreach ($selections as $product_id => $quantity) {
            $product_id = absint($product_id);
            $quantity   = absint($quantity);

            if ($product_id && $quantity) {
                $normalized[$product_id] = $quantity;
            }
        }

        return $normalized;
    }
}
