jQuery(function ($) {
	$(".mahimzaman-bab-builder").each(function () {
		const $builder = $(this);
		const pricingMode = $builder.data("pricing-mode");
		const allowDuplicates =
			String($builder.data("allow-duplicates")) === "1";

		const $selectedCount = $builder.find(".mahimzaman-bab-selected-count");
		const $requiredCount = $builder.find(".mahimzaman-bab-required-count");
		const $progressText = $builder.find(".mahimzaman-bab-progress-text");
		const $summaryCount = $builder.find(".mahimzaman-bab-summary-count");
		const $totalPrice = $builder.find(".mahimzaman-bab-total-price");
		const $addButton = $builder.find(".mahimzaman-bab-add-to-cart");

		function getPackInput() {
			const $checked = $builder.find(
				'input[name="mahimzaman_bab_pack"]:checked',
			);

			if ($checked.length) {
				return $checked;
			}

			return $builder.find('input[name="mahimzaman_bab_pack"][type="hidden"]');
		}

		function getRequiredQuantity() {
			return parseInt(getPackInput().val(), 10) || 0;
		}

		function getSelectedQuantity() {
			let total = 0;

			$builder.find(".mahimzaman-bab-product-quantity").each(function () {
				total += parseInt($(this).val(), 10) || 0;
			});

			return total;
		}

		function getCalculatedPrice() {
			let total = 0;

			$builder.find(".mahimzaman-bab-product").each(function () {
				const $product = $(this);
				const price = parseFloat($product.data("price")) || 0;
				const quantity =
					parseInt(
						$product.find(".mahimzaman-bab-product-quantity").val(),
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
			const currency = MahimZamanBuildABundle.currency;
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
			$builder.find(".mahimzaman-bab-product-quantity").val(0);
		}

		function updateControls() {
			const required = getRequiredQuantity();
			const selected = getSelectedQuantity();

			$builder.find(".mahimzaman-bab-product").each(function () {
				const $product = $(this);
				const $input = $product.find(".mahimzaman-bab-product-quantity");
				const quantity = parseInt($input.val(), 10) || 0;

				$product
					.toggleClass("is-selected", quantity > 0)
					.attr("aria-selected", quantity > 0 ? "true" : "false");

				$product.find(".mahimzaman-bab-minus").prop("disabled", quantity <= 0);

				$product
					.find(".mahimzaman-bab-plus")
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
				$progressText.text(MahimZamanBuildABundle.i18n.complete);
			} else if (remaining === 1) {
				$progressText.text(MahimZamanBuildABundle.i18n.oneRemaining);
			} else {
				$progressText.text(
					MahimZamanBuildABundle.i18n.remaining.replace("%d", remaining),
				);
			}

			$builder.toggleClass("is-complete", complete);

			$addButton
				.prop("disabled", !complete)
				.text(
					complete
						? MahimZamanBuildABundle.i18n.addToCart
						: MahimZamanBuildABundle.i18n.incomplete,
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

		$builder.on("click", ".mahimzaman-bab-plus", function () {
			const required = getRequiredQuantity();
			const selected = getSelectedQuantity();

			if (selected >= required) {
				return;
			}

			const $input = $(this)
				.closest(".mahimzaman-bab-product")
				.find(".mahimzaman-bab-product-quantity");

			const current = parseInt($input.val(), 10) || 0;

			if (!allowDuplicates && current >= 1) {
				return;
			}

			$input.val(current + 1);

			updateBuilder();
		});

		$builder.on("click", ".mahimzaman-bab-minus", function () {
			const $input = $(this)
				.closest(".mahimzaman-bab-product")
				.find(".mahimzaman-bab-product-quantity");

			const current = parseInt($input.val(), 10) || 0;

			if (current <= 0) {
				return;
			}

			$input.val(current - 1);

			updateBuilder();
		});

		$builder.on("change", 'input[name="mahimzaman_bab_pack"]', function () {
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
