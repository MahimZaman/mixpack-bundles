<?php

namespace MahimZaman\BuildABundle\Frontend;

use MahimZaman\BuildABundle\Contracts\Module;
use MahimZaman\BuildABundle\Product\BundleProduct;
use MahimZaman\BuildABundle\Product\ProductResolver;
use MahimZaman\BuildABundle\Admin\SettingsModule;

defined('ABSPATH') || exit;

final class FrontendModule implements Module
{

    public function register()
    {
        add_action(
            'woocommerce_mahimzaman_bundle_add_to_cart',
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
            isset($_GET['mahimzaman_bab_edit'], $_GET['mahimzaman_bab_edit_nonce']) &&
            WC()->cart
        ) {
            $key = sanitize_text_field(
                wp_unslash($_GET['mahimzaman_bab_edit'])
            );

            $nonce = sanitize_text_field(
                wp_unslash($_GET['mahimzaman_bab_edit_nonce'])
            );

            if (! wp_verify_nonce($nonce, 'mahimzaman_bab_edit_' . $key)) {
                return;
            }

            $cart = WC()->cart->get_cart();

            if (
                isset($cart[$key]) &&
                (int) $cart[$key]['product_id'] === $bundle->get_id() &&
                ! empty($cart[$key]['mahimzaman_bab'])
            ) {
                $edit_cart_key     = $key;
                $selected_pack     = absint($cart[$key]['mahimzaman_bab']['pack']);
                $selected_products = $cart[$key]['mahimzaman_bab']['selections'];
                $edit_quantity     = max(1, absint($cart[$key]['quantity']));
            }
        }

        $active_pack = $bundle->get_pack($selected_pack);

        if (! $active_pack && ! empty($packs[0])) {
            $active_pack   = $packs[0];
            $selected_pack = (int) $packs[0]['quantity'];
        }

        include MAHIMZAMAN_BAB_PATH . 'templates/frontend/builder.php';
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
            'mahimzaman-build-a-bundle-for-woocommerce-frontend',
            MAHIMZAMAN_BAB_URL . 'assets/css/frontend.css',
            array(),
            MAHIMZAMAN_BAB_VERSION
        );

        $appearance = SettingsModule::get_values();

        $appearance_css = sprintf(
            '.mahimzaman-bab-builder{
			--mahimzaman-bab-primary:%1$s;
			--mahimzaman-bab-button-bg:%2$s;
			--mahimzaman-bab-button-text:%3$s;
			--mahimzaman-bab-builder-bg:%4$s;
			--mahimzaman-bab-card-bg:%5$s;
			--mahimzaman-bab-text:%6$s;
			--mahimzaman-bab-muted:%7$s;
			--mahimzaman-bab-border:%8$s;
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
            'mahimzaman-build-a-bundle-for-woocommerce-frontend',
            $appearance_css
        );

        wp_enqueue_script(
            'mahimzaman-build-a-bundle-for-woocommerce-frontend',
            MAHIMZAMAN_BAB_URL . 'assets/js/frontend.js',
            array('jquery'),
            MAHIMZAMAN_BAB_VERSION,
            true
        );

        wp_localize_script(
            'mahimzaman-build-a-bundle-for-woocommerce-frontend',
            'MahimZamanBuildABundle',
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
                    'complete'     => __('Pack complete!', 'mahimzaman-build-a-bundle-for-woocommerce'),
                    'oneRemaining' => __('Choose 1 more item to complete your pack.', 'mahimzaman-build-a-bundle-for-woocommerce'),

                    /* translators: %d: Number of remaining products. */
                    'remaining' => __('Choose %d more items to complete your pack.', 'mahimzaman-build-a-bundle-for-woocommerce'),

                    'incomplete' => __('Complete Your Pack', 'mahimzaman-build-a-bundle-for-woocommerce'),
                    'addToCart'  => __('Add Pack to Cart', 'mahimzaman-build-a-bundle-for-woocommerce'),
                ),
            )
        );
    }
}
