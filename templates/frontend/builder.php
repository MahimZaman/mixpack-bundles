<?php

defined('ABSPATH') || exit;

if (empty($packs) || empty($products)) {
    return;
}

$mixpack_first_pack     = $packs[0];
$mixpack_selected_total = array_sum($selected_products);
?>

<form
    class="cart mixpack-cart-form"
    action="<?php echo esc_url($bundle->get_permalink()); ?>"
    method="post"
    enctype="multipart/form-data">
    <?php wp_nonce_field('mixpack_add_to_cart', 'mixpack_cart_nonce'); ?>

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
            name="mixpack_edit_cart_key"
            value="<?php echo esc_attr($edit_cart_key); ?>">
    <?php endif; ?>

    <div
        class="mixpack-builder"
        data-product-id="<?php echo esc_attr($bundle->get_id()); ?>"
        data-pricing-mode="<?php echo esc_attr($bundle->get_pricing_mode()); ?>"
        data-allow-duplicates="<?php echo ! empty($group['allow_duplicates']) ? '1' : '0'; ?>">
        <?php if (count($packs) > 1) : ?>

            <div class="mixpack-section mixpack-pack-section">
                <h3 class="mixpack-section-title">
                    <?php esc_html_e('Choose Your Pack', 'mixpack-bundles'); ?>
                </h3>

                <fieldset class="mixpack-pack-options">
                    <legend class="screen-reader-text">
                        <?php esc_html_e('Choose your pack size', 'mixpack-bundles'); ?>
                    </legend>

                    <?php foreach ($packs as $mixpack_index => $mixpack_pack) : ?>
                        <label class="mixpack-pack-option">
                            <input
                                type="radio"
                                name="mixpack_pack"
                                value="<?php echo esc_attr($mixpack_pack['quantity']); ?>"
                                data-price="<?php
                                            echo esc_attr(
                                                wc_get_price_to_display(
                                                    $bundle,
                                                    array(
                                                        'price' => (float) $mixpack_pack['price'],
                                                    )
                                                )
                                            );
                                            ?>"
                                <?php checked((int) $mixpack_pack['quantity'], $selected_pack); ?>>

                            <span class="mixpack-pack-option-content">
                                <strong>
                                    <?php
                                    printf(
                                        /* translators: %d: Number of products in the pack. */
                                        esc_html__('%d-Pack', 'mixpack-bundles'),
                                        (int) $mixpack_pack['quantity']
                                    );
                                    ?>
                                </strong>

                                <?php
                                if (
                                    'fixed' === $bundle->get_pricing_mode() &&
                                    '' !== $mixpack_pack['price']
                                ) :
                                ?>
                                    <span class="mixpack-pack-price">
                                        <?php
                                        echo wp_kses_post(
                                            wc_price(
                                                wc_get_price_to_display(
                                                    $bundle,
                                                    array(
                                                        'price' => (float) $mixpack_pack['price'],
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
                name="mixpack_pack"
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

        <div class="mixpack-section">
            <div class="mixpack-builder-header">
                <h3 class="mixpack-section-title">
                    <?php esc_html_e('Choose Your Products', 'mixpack-bundles'); ?>
                </h3>

                <div class="mixpack-progress">
                    <strong
                        class="mixpack-progress-count"
                        aria-live="polite">
                        <span class="mixpack-selected-count">
                            <?php echo esc_html($mixpack_selected_total); ?>
                        </span>

                        <?php esc_html_e('of', 'mixpack-bundles'); ?>

                        <span class="mixpack-required-count">
                            <?php echo esc_html($selected_pack); ?>
                        </span>

                        <?php esc_html_e('selected', 'mixpack-bundles'); ?>
                    </strong>

                    <span
                        class="mixpack-progress-text"
                        role="status"
                        aria-live="polite"
                        aria-atomic="true">
                        <?php
                        printf(
                            /* translators: %d: Required number of products in the pack. */
                            esc_html__(
                                'Choose %d items to complete your pack.',
                                'mixpack-bundles'
                            ),
                            (int) $selected_pack
                        );
                        ?>
                    </span>
                </div>
            </div>

            <div class="mixpack-products">
                <?php foreach ($products as $mixpack_item) : ?>

                    <?php
                    $mixpack_item_quantity = isset(
                        $selected_products[$mixpack_item->get_id()]
                    )
                        ? absint(
                            $selected_products[$mixpack_item->get_id()]
                        )
                        : 0;
                    ?>

                    <div
                        class="mixpack-product"
                        data-product-id="<?php echo esc_attr($mixpack_item->get_id()); ?>"
                        data-price="<?php echo esc_attr(wc_get_price_to_display($mixpack_item)); ?>"
                        aria-selected="<?php echo $mixpack_item_quantity > 0 ? 'true' : 'false'; ?>">
                        <div class="mixpack-product-image">
                            <?php
                            echo wp_kses_post(
                                $mixpack_item->get_image(
                                    'woocommerce_thumbnail',
                                    array(
                                        'loading' => 'lazy',
                                    )
                                )
                            );
                            ?>
                        </div>

                        <div class="mixpack-product-content">
                            <h4 class="mixpack-product-title">
                                <?php echo esc_html($mixpack_item->get_name()); ?>
                            </h4>

                            <?php if ('calculated' === $bundle->get_pricing_mode()) : ?>
                                <div class="mixpack-product-price">
                                    <?php
                                    echo wp_kses_post(
                                        $mixpack_item->get_price_html()
                                    );
                                    ?>
                                </div>
                            <?php endif; ?>

                            <div class="mixpack-quantity-control">
                                <button
                                    type="button"
                                    class="mixpack-quantity-button mixpack-minus"
                                    aria-label="<?php
                                                echo esc_attr(
                                                    sprintf(
                                                        /* translators: %s: Product name. */
                                                        __('Decrease %s quantity', 'mixpack-bundles'),
                                                        $mixpack_item->get_name()
                                                    )
                                                );
                                                ?>"
                                    <?php disabled(0 === $mixpack_item_quantity); ?>>
                                    −
                                </button>

                                <input
                                    type="number"
                                    class="mixpack-product-quantity"
                                    name="mixpack_products[<?php echo esc_attr($mixpack_item->get_id()); ?>]"
                                    value="<?php echo esc_attr($mixpack_item_quantity); ?>"
                                    min="0"
                                    step="1"
                                    readonly
                                    aria-label="<?php
                                                echo esc_attr(
                                                    sprintf(
                                                        /* translators: %s: Product name. */
                                                        __('%s quantity', 'mixpack-bundles'),
                                                        $mixpack_item->get_name()
                                                    )
                                                );
                                                ?>">

                                <button
                                    type="button"
                                    class="mixpack-quantity-button mixpack-plus"
                                    aria-label="<?php
                                                echo esc_attr(
                                                    sprintf(
                                                        /* translators: %s: Product name. */
                                                        __('Increase %s quantity', 'mixpack-bundles'),
                                                        $mixpack_item->get_name()
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

        <div class="mixpack-summary">
            <div class="mixpack-summary-status">
                <span class="mixpack-summary-label">
                    <?php esc_html_e('Pack progress', 'mixpack-bundles'); ?>
                </span>

                <strong
                    class="mixpack-summary-count"
                    aria-live="polite"
                    aria-atomic="true">
                    <?php echo esc_html($mixpack_selected_total); ?>
                    /
                    <?php echo esc_html($selected_pack); ?>
                </strong>
            </div>

            <div class="mixpack-summary-price">
                <span>
                    <?php esc_html_e('Total', 'mixpack-bundles'); ?>
                </span>

                <strong class="mixpack-total-price">
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
                        echo wp_kses_post(wc_price(0));
                    }
                    ?>
                </strong>
            </div>
        </div>

        <button
            type="submit"
            class="button alt mixpack-add-to-cart"
            disabled>
            <?php esc_html_e('Complete Your Pack', 'mixpack-bundles'); ?>
        </button>
    </div>
</form>