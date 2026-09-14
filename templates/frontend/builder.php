<?php

defined('ABSPATH') || exit;

if (empty($packs) || empty($products)) {
    return;
}

$mahimzaman_bab_first_pack     = $packs[0];
$mahimzaman_bab_selected_total = array_sum($selected_products);
?>

<form
    class="cart mahimzaman-bab-cart-form"
    action="<?php echo esc_url($bundle->get_permalink()); ?>"
    method="post"
    enctype="multipart/form-data">
    <?php wp_nonce_field('mahimzaman_bab_add_to_cart', 'mahimzaman_bab_cart_nonce'); ?>

    <input
        type="hidden"
        name="add-to-cart"
        value="<?php echo esc_attr($bundle->get_id()); ?>">

    <input
        type="hidden"
        name="quantity"
        value="<?php echo esc_attr($edit_quantity); ?>">

    <?php if ($edit_cart_key) : ?>
        <input
            type="hidden"
            name="mahimzaman_bab_edit_cart_key"
            value="<?php echo esc_attr($edit_cart_key); ?>">
    <?php endif; ?>

    <div
        class="mahimzaman-bab-builder"
        data-product-id="<?php echo esc_attr($bundle->get_id()); ?>"
        data-pricing-mode="<?php echo esc_attr($bundle->get_pricing_mode()); ?>"
        data-allow-duplicates="<?php echo ! empty($group['allow_duplicates']) ? '1' : '0'; ?>">

        <?php if (count($packs) > 1) : ?>

            <div class="mahimzaman-bab-section mahimzaman-bab-pack-section">
                <h3 class="mahimzaman-bab-section-title">
                    <?php esc_html_e('Choose Your Pack', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                </h3>

                <fieldset class="mahimzaman-bab-pack-options">
                    <legend class="screen-reader-text">
                        <?php esc_html_e('Choose your pack size', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                    </legend>

                    <?php foreach ($packs as $mahimzaman_bab_index => $mahimzaman_bab_pack) : ?>

                        <label class="mahimzaman-bab-pack-option">
                            <input
                                type="radio"
                                name="mahimzaman_bab_pack"
                                value="<?php echo esc_attr($mahimzaman_bab_pack['quantity']); ?>"
                                data-price="<?php
                                            echo esc_attr(
                                                wc_get_price_to_display(
                                                    $bundle,
                                                    array(
                                                        'price' => (float) $mahimzaman_bab_pack['price'],
                                                    )
                                                )
                                            );
                                            ?>"
                                <?php
                                checked(
                                    (int) $mahimzaman_bab_pack['quantity'],
                                    $selected_pack
                                );
                                ?>>

                            <span class="mahimzaman-bab-pack-option-content">
                                <strong>
                                    <?php
                                    printf(
                                        /* translators: %d: Number of products in the pack. */
                                        esc_html__('%d-Pack', 'mahimzaman-build-a-bundle-for-woocommerce'),
                                        (int) $mahimzaman_bab_pack['quantity']
                                    );
                                    ?>
                                </strong>

                                <?php
                                if (
                                    'fixed' === $bundle->get_pricing_mode() &&
                                    '' !== $mahimzaman_bab_pack['price']
                                ) :
                                ?>
                                    <span class="mahimzaman-bab-pack-price">
                                        <?php
                                        echo wp_kses_post(
                                            wc_price(
                                                wc_get_price_to_display(
                                                    $bundle,
                                                    array(
                                                        'price' => (float) $mahimzaman_bab_pack['price'],
                                                    )
                                                )
                                            )
                                        );
                                        ?>
                                    </span>
                                <?php endif; ?>

                            </span>
                        </label>

                    <?php endforeach; ?>

                </fieldset>
            </div>

        <?php else : ?>

            <input
                type="hidden"
                name="mahimzaman_bab_pack"
                value="<?php echo esc_attr($active_pack['quantity']); ?>"
                data-price="<?php
                            echo esc_attr(
                                wc_get_price_to_display(
                                    $bundle,
                                    array(
                                        'price' => (float) $active_pack['price'],
                                    )
                                )
                            );
                            ?>">

        <?php endif; ?>

        <div class="mahimzaman-bab-section">

            <div class="mahimzaman-bab-builder-header">

                <h3 class="mahimzaman-bab-section-title">
                    <?php esc_html_e('Choose Your Products', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                </h3>

                <div class="mahimzaman-bab-progress">

                    <strong
                        class="mahimzaman-bab-progress-count"
                        aria-live="polite">
                        <span class="mahimzaman-bab-selected-count">
                            <?php echo esc_html($mahimzaman_bab_selected_total); ?>
                        </span>

                        <?php esc_html_e('of', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>

                        <span class="mahimzaman-bab-required-count">
                            <?php echo esc_html($selected_pack); ?>
                        </span>

                        <?php esc_html_e('selected', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                    </strong>

                    <span
                        class="mahimzaman-bab-progress-text"
                        role="status"
                        aria-live="polite"
                        aria-atomic="true">
                        <?php
                        printf(
                            /* translators: %d: Required number of products in the pack. */
                            esc_html__(
                                'Choose %d items to complete your pack.',
                                'mahimzaman-build-a-bundle-for-woocommerce'
                            ),
                            (int) $selected_pack
                        );
                        ?>
                    </span>

                </div>
            </div>

            <div class="mahimzaman-bab-products">

                <?php foreach ($products as $mahimzaman_bab_item) : ?>

                    <?php
                    $mahimzaman_bab_item_quantity = isset(
                        $selected_products[$mahimzaman_bab_item->get_id()]
                    )
                        ? absint(
                            $selected_products[$mahimzaman_bab_item->get_id()]
                        )
                        : 0;
                    ?>

                    <div
                        class="mahimzaman-bab-product"
                        data-product-id="<?php echo esc_attr($mahimzaman_bab_item->get_id()); ?>"
                        data-price="<?php echo esc_attr(wc_get_price_to_display($mahimzaman_bab_item)); ?>"
                        aria-selected="<?php echo $mahimzaman_bab_item_quantity > 0 ? 'true' : 'false'; ?>">

                        <div class="mahimzaman-bab-product-image">
                            <?php
                            echo wp_kses_post(
                                $mahimzaman_bab_item->get_image(
                                    'woocommerce_thumbnail',
                                    array(
                                        'loading' => 'lazy',
                                    )
                                )
                            );
                            ?>
                        </div>

                        <div class="mahimzaman-bab-product-content">

                            <h4 class="mahimzaman-bab-product-title">
                                <?php echo esc_html($mahimzaman_bab_item->get_name()); ?>
                            </h4>

                            <?php if ('calculated' === $bundle->get_pricing_mode()) : ?>
                                <div class="mahimzaman-bab-product-price">
                                    <?php
                                    echo wp_kses_post(
                                        $mahimzaman_bab_item->get_price_html()
                                    );
                                    ?>
                                </div>
                            <?php endif; ?>

                            <div class="mahimzaman-bab-quantity-control">

                                <button
                                    type="button"
                                    class="mahimzaman-bab-quantity-button mahimzaman-bab-minus"
                                    aria-label="<?php
                                                echo esc_attr(
                                                    sprintf(
                                                        /* translators: %s: Product name. */
                                                        __('Decrease %s quantity', 'mahimzaman-build-a-bundle-for-woocommerce'),
                                                        $mahimzaman_bab_item->get_name()
                                                    )
                                                );
                                                ?>"
                                    <?php disabled(0 === $mahimzaman_bab_item_quantity); ?>>
                                    −
                                </button>

                                <input
                                    type="number"
                                    class="mahimzaman-bab-product-quantity"
                                    name="mahimzaman_bab_products[<?php echo esc_attr($mahimzaman_bab_item->get_id()); ?>]"
                                    value="<?php echo esc_attr($mahimzaman_bab_item_quantity); ?>"
                                    min="0"
                                    step="1"
                                    readonly
                                    aria-label="<?php
                                                echo esc_attr(
                                                    sprintf(
                                                        /* translators: %s: Product name. */
                                                        __('%s quantity', 'mahimzaman-build-a-bundle-for-woocommerce'),
                                                        $mahimzaman_bab_item->get_name()
                                                    )
                                                );
                                                ?>">

                                <button
                                    type="button"
                                    class="mahimzaman-bab-quantity-button mahimzaman-bab-plus"
                                    aria-label="<?php
                                                echo esc_attr(
                                                    sprintf(
                                                        /* translators: %s: Product name. */
                                                        __('Increase %s quantity', 'mahimzaman-build-a-bundle-for-woocommerce'),
                                                        $mahimzaman_bab_item->get_name()
                                                    )
                                                );
                                                ?>">
                                    +
                                </button>

                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>

        <div class="mahimzaman-bab-summary">

            <div class="mahimzaman-bab-summary-status">

                <span class="mahimzaman-bab-summary-label">
                    <?php esc_html_e('Pack progress', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                </span>

                <strong
                    class="mahimzaman-bab-summary-count"
                    aria-live="polite"
                    aria-atomic="true">
                    <?php echo esc_html($mahimzaman_bab_selected_total); ?>
                    /
                    <?php echo esc_html($selected_pack); ?>
                </strong>

            </div>

            <div class="mahimzaman-bab-summary-price">

                <span>
                    <?php esc_html_e('Total', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
                </span>

                <strong class="mahimzaman-bab-total-price">
                    <?php
                    if ('fixed' === $bundle->get_pricing_mode()) {
                        echo wp_kses_post(
                            wc_price(
                                wc_get_price_to_display(
                                    $bundle,
                                    array(
                                        'price' => (float) $active_pack['price'],
                                    )
                                )
                            )
                        );
                    } else {
                        echo wp_kses_post(
                            wc_price(0)
                        );
                    }
                    ?>
                </strong>

            </div>

        </div>

        <button
            type="submit"
            class="button alt mahimzaman-bab-add-to-cart"
            disabled>
            <?php esc_html_e('Complete Your Pack', 'mahimzaman-build-a-bundle-for-woocommerce'); ?>
        </button>

    </div>
</form>