jQuery(function ($) {
	const $pricing = $("#mixpack_pricing_mode");
	const $source = $("#mixpack_product_source");
	const currency = $(".mixpack-currency").first().text() || "";

	function updatePricing() {
		$(".mixpack-price-field").toggle($pricing.val() === "fixed");
	}

	function updateSource() {
		const value = $source.val();

		$(".mixpack-source-products").toggle(value === "products");
		$(".mixpack-source-categories").toggle(value === "categories");
	}

	$("#mixpack-add-pack").on("click", function () {
		$("#mixpack-pack-rows").append(`
			<span class="mixpack-pack-row">
				<span class="mixpack-pack-input">
					<span class="mixpack-input-label">Quantity</span>
					<input
						type="number"
						name="mixpack_pack_quantity[]"
						min="1"
						step="1"
					>
				</span>

				<span class="mixpack-pack-input mixpack-price-field">
					<span class="mixpack-input-label">Price</span>

					<span class="mixpack-price-input">
						<span class="mixpack-currency">${currency}</span>
						<input
							type="text"
							name="mixpack_pack_price[]"
							class="wc_input_price"
						>
					</span>
				</span>

				<button
					type="button"
					class="button-link-delete mixpack-remove-pack"
				>
					Remove
				</button>
			</span>
		`);

		updatePricing();
	});

	$(document).on("click", ".mixpack-remove-pack", function () {
		$(this).closest(".mixpack-pack-row").remove();
	});

	$pricing.on("change", updatePricing);
	$source.on("change", updateSource);

	updatePricing();
	updateSource();
});
