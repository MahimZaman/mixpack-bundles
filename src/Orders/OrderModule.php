<?php

namespace MahimZaman\BuildABundle\Orders;

use MahimZaman\BuildABundle\Contracts\Module;

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
        $keys[] = '_mahimzaman_bab_pack';
        $keys[] = '_mahimzaman_bab_selections';
        $keys[] = '_mahimzaman_bab_components_created';
        $keys[] = '_mahimzaman_bab_component';
        $keys[] = '_mahimzaman_bab_parent_item_id';

        return array_unique($keys);
    }

    public function add_bundle_meta($item, $cart_item_key, $values, $order)
    {
        if (empty($values['mahimzaman_bab'])) {
            return;
        }

        $pack       = absint($values['mahimzaman_bab']['pack']);
        $selections = $values['mahimzaman_bab']['selections'];

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
            __('Pack', 'mahimzaman-build-a-bundle-for-woocommerce'),
            sprintf(
                /* translators: %d: Number of products in the pack. */
                __(
                    '%d-Pack',
                    'mahimzaman-build-a-bundle-for-woocommerce'
                ),
                $pack
            ),
            true
        );

        $item->add_meta_data(
            __('Contents', 'mahimzaman-build-a-bundle-for-woocommerce'),
            implode(', ', $contents),
            true
        );

        $item->add_meta_data('_mahimzaman_bab_pack', $pack, true);
        $item->add_meta_data('_mahimzaman_bab_selections', $selections, true);
    }

    public function create_component_items($order)
    {
        if (! $order instanceof \WC_Order) {
            return;
        }

        $changed = false;

        foreach ($order->get_items('line_item') as $parent_item_id => $parent_item) {
            $selections = $parent_item->get_meta('_mahimzaman_bab_selections', true);

            if (! is_array($selections) || empty($selections)) {
                continue;
            }

            if ($parent_item->get_meta('_mahimzaman_bab_components_created', true)) {
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

                $child->add_meta_data('_mahimzaman_bab_component', 'yes', true);
                $child->add_meta_data('_mahimzaman_bab_parent_item_id', $parent_item_id, true);

                $order->add_item($child);
                $changed = true;
            }

            $parent_item->update_meta_data(
                '_mahimzaman_bab_components_created',
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
            'yes' === $item->get_meta('_mahimzaman_bab_component', true)
        ) {
            return is_admin();
        }

        return $visible;
    }
}
