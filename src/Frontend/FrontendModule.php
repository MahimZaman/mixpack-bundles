<?php

namespace MixPack\Bundles\Frontend;

use MixPack\Bundles\Contracts\Module;
use MixPack\Bundles\Product\BundleProduct;
use MixPack\Bundles\Product\ProductResolver;
use MixPack\Bundles\Admin\SettingsModule;

defined('ABSPATH') || exit;

final class FrontendModule implements Module
{

    public function register()
    {
        add_action(
            'woocommerce_mixpack_bundle_add_to_cart',
            array($this, 'render_builder')
        );

        add_action(
            'wp_enqueue_scripts',
            array($this, 'enqueue_assets')
        );
    }

    public function render_builder()
    {
        global $product;

        if (! $product instanceof BundleProduct) {
            return;
        }

        $resolver = new ProductResolver();

        $bundle            = $product;
        $packs             = $bundle->get_pack_sizes();
        $products          = $resolver->resolve($bundle);
        $group             = $bundle->get_groups()[0] ?? array();
        $selected_products = array();
        $edit_cart_key     = '';
        $edit_quantity     = 1;

        $selected_pack = ! empty($packs[0]['quantity'])
            ? (int) $packs[0]['quantity']
            : 0;

        if (
            isset($_GET['mixpack_edit'], $_GET['mixpack_edit_nonce']) &&
            WC()->cart
        ) {
            $key = sanitize_text_field(
                wp_unslash($_GET['mixpack_edit'])
            );

            $nonce = sanitize_text_field(
                wp_unslash($_GET['mixpack_edit_nonce'])
            );

            if (! wp_verify_nonce($nonce, 'mixpack_edit_' . $key)) {
                return;
            }

            $cart = WC()->cart->get_cart();

            if (
                isset($cart[$key]) &&
                (int) $cart[$key]['product_id'] === $bundle->get_id() &&
                ! empty($cart[$key]['mixpack'])
            ) {
                $edit_cart_key     = $key;
                $selected_pack     = absint($cart[$key]['mixpack']['pack']);
                $selected_products = $cart[$key]['mixpack']['selections'];
                $edit_quantity     = max(1, absint($cart[$key]['quantity']));
            }
        }

        $active_pack = $bundle->get_pack($selected_pack);

        if (! $active_pack && ! empty($packs[0])) {
            $active_pack   = $packs[0];
            $selected_pack = (int) $packs[0]['quantity'];
        }

        include MIXPACK_BUNDLES_PATH . 'templates/frontend/builder.php';
    }

    public function enqueue_assets()
    {
        if (! is_product()) {
            return;
        }

        global $post;

        if (! $post) {
            return;
        }

        $product = wc_get_product($post->ID);

        if (! $product instanceof BundleProduct) {
            return;
        }

        wp_enqueue_style(
            'mixpack-bundles-frontend',
            MIXPACK_BUNDLES_URL . 'assets/css/frontend.css',
            array(),
            MIXPACK_BUNDLES_VERSION
        );

        $appearance = SettingsModule::get_values();

        $appearance_css = sprintf(
            '.mixpack-builder{
			--mixpack-primary:%1$s;
			--mixpack-button-bg:%2$s;
			--mixpack-button-text:%3$s;
			--mixpack-builder-bg:%4$s;
			--mixpack-card-bg:%5$s;
			--mixpack-text:%6$s;
			--mixpack-muted:%7$s;
			--mixpack-border:%8$s;
		}',
            $appearance['primary'],
            $appearance['button_bg'],
            $appearance['button_text'],
            $appearance['builder_bg'],
            $appearance['card_bg'],
            $appearance['text'],
            $appearance['muted'],
            $appearance['border']
        );

        wp_add_inline_style(
            'mixpack-bundles-frontend',
            $appearance_css
        );

        wp_enqueue_script(
            'mixpack-bundles-frontend',
            MIXPACK_BUNDLES_URL . 'assets/js/frontend.js',
            array('jquery'),
            MIXPACK_BUNDLES_VERSION,
            true
        );

        wp_localize_script(
            'mixpack-bundles-frontend',
            'MixPackBundles',
            array(
                'currency' => array(
                    'symbol' => html_entity_decode(
                        get_woocommerce_currency_symbol(),
                        ENT_QUOTES,
                        'UTF-8'
                    ),
                    'decimals'          => wc_get_price_decimals(),
                    'decimalSeparator'  => wc_get_price_decimal_separator(),
                    'thousandSeparator' => wc_get_price_thousand_separator(),
                    'format'            => html_entity_decode(
                        get_woocommerce_price_format(),
                        ENT_QUOTES,
                        'UTF-8'
                    ),
                ),
                'i18n' => array(
                    'complete'     => __('Pack complete!', 'mixpack-bundles'),
                    'oneRemaining' => __('Choose 1 more item to complete your pack.', 'mixpack-bundles'),

                    /* translators: %d: Number of remaining products. */
                    'remaining' => __('Choose %d more items to complete your pack.', 'mixpack-bundles'),

                    'incomplete' => __('Complete Your Pack', 'mixpack-bundles'),
                    'addToCart'  => __('Add Pack to Cart', 'mixpack-bundles'),
                ),
            )
        );
    }
}
