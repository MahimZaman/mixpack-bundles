<?php

namespace MahimZaman\BuildABundle\Product;

defined('ABSPATH') || exit;

final class ProductResolver
{

    public function resolve(BundleProduct $bundle)
    {
        $groups = $bundle->get_groups();

        if (empty($groups[0])) {
            return array();
        }

        $group = $groups[0];

        if ('categories' === $group['source']) {
            $products = $this->from_categories($group['category_ids']);
        } else {
            $products = $this->from_products($group['product_ids']);
        }

        return array_values(
            array_filter(
                $products,
                array($this, 'is_eligible')
            )
        );
    }

    public function get_ids(BundleProduct $bundle)
    {
        return array_map(
            static function ($product) {
                return $product->get_id();
            },
            $this->resolve($bundle)
        );
    }

    private function from_products($product_ids)
    {
        if (empty($product_ids)) {
            return array();
        }

        $products = wc_get_products(
            array(
                'include' => $product_ids,
                'status'  => 'publish',
                'type'    => 'simple',
                'limit'   => -1,
                'return'  => 'objects',
            )
        );

        $indexed = array();

        foreach ($products as $product) {
            $indexed[$product->get_id()] = $product;
        }

        $ordered = array();

        foreach ($product_ids as $product_id) {
            if (isset($indexed[$product_id])) {
                $ordered[] = $indexed[$product_id];
            }
        }

        return $ordered;
    }

    private function from_categories($category_ids)
    {
        if (empty($category_ids)) {
            return array();
        }

        $slugs = get_terms(
            array(
                'taxonomy'   => 'product_cat',
                'include'    => $category_ids,
                'hide_empty' => false,
                'fields'     => 'slugs',
            )
        );

        if (is_wp_error($slugs) || empty($slugs)) {
            return array();
        }

        return wc_get_products(
            array(
                'status'   => 'publish',
                'type'     => 'simple',
                'category' => $slugs,
                'limit'    => -1,
                'orderby'  => 'menu_order',
                'order'    => 'ASC',
                'return'   => 'objects',
            )
        );
    }

    private function is_eligible($product)
    {
        return $product instanceof \WC_Product
            && $product->is_type('simple')
            && $product->is_purchasable()
            && $product->is_in_stock();
    }
}
