jQuery(function ($) {
	$(".mixpack-builder").each(function () {
		const $builder = $(this);
		const pricingMode = $builder.data("pricing-mode");
		const allowDuplicates =
			String($builder.data("allow-duplicates")) === "1";

		const $selectedCount = $builder.find(".mixpack-selected-count");
		const $requiredCount = $builder.find(".mixpack-required-count");
		const $progressText = $builder.find(".mixpack-progress-text");
		const $summaryCount = $builder.find(".mixpack-summary-count");
		const $totalPrice = $builder.find(".mixpack-total-price");
		const $addButton = $builder.find(".mixpack-add-to-cart");

		function getPackInput() {
			const $checked = $builder.find(
				'input[name="mixpack_pack"]:checked',
			);

			if ($checked.length) {
				return $checked;
			}

			return $builder.find('input[name="mixpack_pack"][type="hidden"]');
		}

		function getRequiredQuantity() {
			return parseInt(getPackInput().val(), 10) || 0;
		}

		function getSelectedQuantity() {
			let total = 0;

			$builder.find(".mixpack-product-quantity").each(function () {
				total += parseInt($(this).val(), 10) || 0;
			});

			return total;
		}

		function getCalculatedPrice() {
			let total = 0;

			$builder.find(".mixpack-product").each(function () {
				const $product = $(this);
				const price = parseFloat($product.data("price")) || 0;
				const quantity =
					parseInt(
						$product.find(".mixpack-product-quantity").val(),
						10,
					) || 0;

				total += price * quantity;
			});

			return total;
		}

		function getFixedPrice() {
			return parseFloat(getPackInput().data("price")) || 0;
		}

		function formatPrice(amount) {
			const currency = MixPackBundles.currency;
			const decimals = parseInt(currency.decimals, 10) || 0;

			let parts = Number(amount).toFixed(decimals).split(".");
			let whole = parts[0];

			whole = whole.replace(
				/\B(?=(\d{3})+(?!\d))/g,
				currency.thousandSeparator,
			);

			let formatted = whole;

			if (decimals > 0) {
				formatted += currency.decimalSeparator + parts[1];
			}

			return currency.format
				.replace("%1$s", currency.symbol)
				.replace("%2$s", formatted);
		}

		function resetSelections() {
			$builder.find(".mixpack-product-quantity").val(0);
		}

		function updateControls() {
			const required = getRequiredQuantity();
			const selected = getSelectedQuantity();

			$builder.find(".mixpack-product").each(function () {
				const $product = $(this);
				const $input = $product.find(".mixpack-product-quantity");
				const quantity = parseInt($input.val(), 10) || 0;

				$product
					.toggleClass("is-selected", quantity > 0)
					.attr("aria-selected", quantity > 0 ? "true" : "false");

				$product.find(".mixpack-minus").prop("disabled", quantity <= 0);

				$product
					.find(".mixpack-plus")
					.prop(
						"disabled",
						selected >= required ||
							(!allowDuplicates && quantity >= 1),
					);
			});
		}

		function updateProgress() {
			const required = getRequiredQuantity();
			const selected = getSelectedQuantity();
			const remaining = Math.max(required - selected, 0);
			const complete = required > 0 && selected === required;

			$selectedCount.text(selected);
			$requiredCount.text(required);
			$summaryCount.text(selected + " / " + required);

			if (complete) {
				$progressText.text(MixPackBundles.i18n.complete);
			} else if (remaining === 1) {
				$progressText.text(MixPackBundles.i18n.oneRemaining);
			} else {
				$progressText.text(
					MixPackBundles.i18n.remaining.replace("%d", remaining),
				);
			}

			$builder.toggleClass("is-complete", complete);

			$addButton
				.prop("disabled", !complete)
				.text(
					complete
						? MixPackBundles.i18n.addToCart
						: MixPackBundles.i18n.incomplete,
				);
		}

		function updatePrice() {
			const total =
				pricingMode === "calculated"
					? getCalculatedPrice()
					: getFixedPrice();

			$totalPrice.text(formatPrice(total));
		}

		function updateBuilder() {
			updateProgress();
			updatePrice();
			updateControls();
		}

		$builder.on("click", ".mixpack-plus", function () {
			const required = getRequiredQuantity();
			const selected = getSelectedQuantity();

			if (selected >= required) {
				return;
			}

			const $input = $(this)
				.closest(".mixpack-product")
				.find(".mixpack-product-quantity");

			const current = parseInt($input.val(), 10) || 0;

			if (!allowDuplicates && current >= 1) {
				return;
			}

			$input.val(current + 1);

			updateBuilder();
		});

		$builder.on("click", ".mixpack-minus", function () {
			const $input = $(this)
				.closest(".mixpack-product")
				.find(".mixpack-product-quantity");

			const current = parseInt($input.val(), 10) || 0;

			if (current <= 0) {
				return;
			}

			$input.val(current - 1);

			updateBuilder();
		});

		$builder.on("change", 'input[name="mixpack_pack"]', function () {
			if (getSelectedQuantity() > getRequiredQuantity()) {
				resetSelections();
			}

			updateBuilder();
		});

		$builder.closest("form").on("submit", function (event) {
			if (getSelectedQuantity() !== getRequiredQuantity()) {
				event.preventDefault();
				updateBuilder();
			}
		});

		updateBuilder();
	});
});
