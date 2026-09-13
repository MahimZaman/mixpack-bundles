<?php

namespace MixPack\Bundles\Product;

defined('ABSPATH') || exit;

final class BundleProduct extends \WC_Product
{

    public const CONFIG_META_KEY = '_mixpack_bundles_config';

    public function get_type()
    {
        return 'mixpack_bundle';
    }

    public function is_purchasable()
    {
        return 'publish' === $this->get_status() && $this->is_configured();
    }

    public function get_bundle_config()
    {
        $config = $this->get_meta(self::CONFIG_META_KEY, true);

        if (! is_array($config)) {
            $config = array();
        }

        return $this->normalize_config($config);
    }

    public function set_bundle_config($config)
    {
        $this->update_meta_data(
            self::CONFIG_META_KEY,
            $this->normalize_config($config)
        );
    }

    public function get_pack_sizes()
    {
        return $this->get_bundle_config()['pack_sizes'];
    }

    public function get_groups()
    {
        return $this->get_bundle_config()['groups'];
    }

    public function get_pricing_mode()
    {
        return $this->get_bundle_config()['pricing_mode'];
    }

    public function is_configured()
    {
        return ! $this->get_configuration_errors()->has_errors();
    }

    public function get_pack($quantity)
    {
        $quantity = absint($quantity);

        foreach ($this->get_pack_sizes() as $pack) {
            if ((int) $pack['quantity'] === $quantity) {
                return $pack;
            }
        }

        return null;
    }

    public function get_configuration_errors()
    {
        $config = $this->get_bundle_config();
        $errors = new \WP_Error();

        if (empty($config['pack_sizes'])) {
            $errors->add(
                'missing_pack_sizes',
                __('Add at least one pack size.', 'mixpack-bundles')
            );
        }

        if ('fixed' === $config['pricing_mode']) {
            foreach ($config['pack_sizes'] as $pack) {
                if ('' === $pack['price']) {
                    $errors->add(
                        'missing_pack_price',
                        sprintf(
                            /* translators: %d: Pack quantity. */
                            __('Enter a price for the %d-pack.', 'mixpack-bundles'),
                            $pack['quantity']
                        )
                    );
                }

                if ('' !== $pack['price'] && (float) $pack['price'] < 0) {
                    $errors->add(
                        'invalid_pack_price',
                        sprintf(
                            /* translators: %d: Pack quantity. */
                            __(
                                'The price for the %d-pack cannot be negative.',
                                'mixpack-bundles'
                            ),
                            $pack['quantity']
                        )
                    );
                }
            }
        }

        if (empty($config['groups'])) {
            $errors->add(
                'missing_products',
                __('Choose products for this bundle.', 'mixpack-bundles')
            );

            return $errors;
        }

        $group = $config['groups'][0];

        if (
            'products' === $group['source'] &&
            empty($group['product_ids'])
        ) {
            $errors->add(
                'missing_products',
                __('Choose at least one product.', 'mixpack-bundles')
            );
        }

        if (
            'categories' === $group['source'] &&
            empty($group['category_ids'])
        ) {
            $errors->add(
                'missing_categories',
                __('Choose at least one product category.', 'mixpack-bundles')
            );
        }

        return $errors;
    }

    private function normalize_config($config)
    {
        $pricing_mode = isset($config['pricing_mode'])
            ? sanitize_key($config['pricing_mode'])
            : 'fixed';

        if (! in_array($pricing_mode, array('fixed', 'calculated'), true)) {
            $pricing_mode = 'fixed';
        }

        return array(
            'schema'       => 1,
            'pricing_mode' => $pricing_mode,
            'pack_sizes'   => $this->normalize_pack_sizes(
                $config['pack_sizes'] ?? array()
            ),
            'groups'       => $this->normalize_groups(
                $config['groups'] ?? array()
            ),
        );
    }

    private function normalize_pack_sizes($pack_sizes)
    {
        if (! is_array($pack_sizes)) {
            return array();
        }

        $normalized = array();

        foreach ($pack_sizes as $pack) {
            if (! is_array($pack)) {
                continue;
            }

            $quantity = isset($pack['quantity'])
                ? absint($pack['quantity'])
                : 0;

            if ($quantity < 1) {
                continue;
            }

            $normalized[$quantity] = array(
                'quantity' => $quantity,
                'price'    => isset($pack['price'])
                    ? wc_format_decimal($pack['price'])
                    : '',
            );
        }

        ksort($normalized, SORT_NUMERIC);

        return array_values($normalized);
    }

    private function normalize_groups($groups)
    {
        if (! is_array($groups) || empty($groups)) {
            return array(
                $this->default_group(),
            );
        }

        $normalized = array();

        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }

            $source = isset($group['source'])
                ? sanitize_key($group['source'])
                : 'products';

            if (! in_array($source, array('products', 'categories'), true)) {
                $source = 'products';
            }

            $normalized[] = array(
                'id' => isset($group['id'])
                    ? sanitize_key($group['id'])
                    : 'default',

                'label' => isset($group['label'])
                    ? sanitize_text_field($group['label'])
                    : __('Products', 'mixpack-bundles'),

                'source' => $source,

                'product_ids' => $this->normalize_ids(
                    $group['product_ids'] ?? array()
                ),

                'category_ids' => $this->normalize_ids(
                    $group['category_ids'] ?? array()
                ),

                'allow_duplicates' => isset($group['allow_duplicates'])
                    ? (bool) $group['allow_duplicates']
                    : true,
            );
        }

        return ! empty($normalized)
            ? $normalized
            : array($this->default_group());
    }

    private function normalize_ids($ids)
    {
        if (! is_array($ids)) {
            return array();
        }

        $ids = array_map('absint', $ids);
        $ids = array_filter($ids);
        $ids = array_unique($ids);

        return array_values($ids);
    }

    private function default_group()
    {
        return array(
            'id'               => 'default',
            'label'            => __('Products', 'mixpack-bundles'),
            'source'           => 'products',
            'product_ids'      => array(),
            'category_ids'     => array(),
            'allow_duplicates' => true,
        );
    }
}
