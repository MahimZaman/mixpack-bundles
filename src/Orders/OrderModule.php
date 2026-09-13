<?php

namespace MixPack\Bundles\Orders;

use MixPack\Bundles\Contracts\Module;

defined('ABSPATH') || exit;

final class OrderModule implements Module
{

    public function register()
    {
        add_action(
            'woocommerce_checkout_create_order_line_item',
            array($this, 'add_bundle_meta'),
            10,
            4
        );

        add_action(
            'woocommerce_checkout_order_created',
            array($this, 'create_component_items'),
            5
        );

        add_filter(
            'woocommerce_order_item_visible',
            array($this, 'hide_component_items'),
            10,
            2
        );

        add_filter(
            'woocommerce_hidden_order_itemmeta',
            array($this, 'hide_internal_meta')
        );
    }

    public function hide_internal_meta($keys)
    {
        $keys[] = '_mixpack_pack';
        $keys[] = '_mixpack_selections';
        $keys[] = '_mixpack_components_created';
        $keys[] = '_mixpack_component';
        $keys[] = '_mixpack_parent_item_id';

        return array_unique($keys);
    }

    public function add_bundle_meta($item, $cart_item_key, $values, $order)
    {
        if (empty($values['mixpack'])) {
            return;
        }

        $pack       = absint($values['mixpack']['pack']);
        $selections = $values['mixpack']['selections'];

        $contents = array();

        foreach ($selections as $product_id => $quantity) {
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

        $item->add_meta_data(
            __('Pack', 'mixpack-bundles'),
            sprintf(
                /* translators: %d: Number of products in the pack. */
                __(
                    '%d-Pack',
                    'mixpack-bundles'
                ),
                $pack
            ),
            true
        );

        $item->add_meta_data(
            __('Contents', 'mixpack-bundles'),
            implode(', ', $contents),
            true
        );

        $item->add_meta_data('_mixpack_pack', $pack, true);
        $item->add_meta_data('_mixpack_selections', $selections, true);
    }

    public function create_component_items($order)
    {
        if (! $order instanceof \WC_Order) {
            return;
        }

        $changed = false;

        foreach ($order->get_items('line_item') as $parent_item_id => $parent_item) {
            $selections = $parent_item->get_meta('_mixpack_selections', true);

            if (! is_array($selections) || empty($selections)) {
                continue;
            }

            if ($parent_item->get_meta('_mixpack_components_created', true)) {
                continue;
            }

            $bundle_quantity = max(1, absint($parent_item->get_quantity()));

            foreach ($selections as $product_id => $quantity) {
                $product = wc_get_product($product_id);

                if (! $product) {
                    continue;
                }

                $child = new \WC_Order_Item_Product();

                $child->set_props(
                    array(
                        'name'         => $product->get_name(),
                        'product_id'   => $product->get_id(),
                        'variation_id' => 0,
                        'quantity'     => absint($quantity) * $bundle_quantity,
                        'tax_class'    => $product->get_tax_class(),
                        'subtotal'     => 0,
                        'total'        => 0,
                        'subtotal_tax' => 0,
                        'total_tax'    => 0,
                        'taxes'        => array(
                            'subtotal' => array(),
                            'total'    => array(),
                        ),
                    )
                );

                $child->add_meta_data('_mixpack_component', 'yes', true);
                $child->add_meta_data('_mixpack_parent_item_id', $parent_item_id, true);

                $order->add_item($child);
                $changed = true;
            }

            $parent_item->update_meta_data(
                '_mixpack_components_created',
                'yes'
            );

            $parent_item->save();
        }

        if ($changed) {
            $order->save();
        }
    }

    public function hide_component_items($visible, $item)
    {
        if (
            $item instanceof \WC_Order_Item_Product &&
            'yes' === $item->get_meta('_mixpack_component', true)
        ) {
            return is_admin();
        }

        return $visible;
    }
}
