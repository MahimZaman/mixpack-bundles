jQuery(function ($) {
	const $pricing = $("#mahimzaman_bab_pricing_mode");
	const $source = $("#mahimzaman_bab_product_source");
	const currency = $(".mahimzaman-bab-currency").first().text() || "";

	function updatePricing() {
		$(".mahimzaman-bab-price-field").toggle($pricing.val() === "fixed");
	}

	function updateSource() {
		const value = $source.val();

		$(".mahimzaman-bab-source-products").toggle(value === "products");
		$(".mahimzaman-bab-source-categories").toggle(value === "categories");
	}

	$("#mahimzaman-bab-add-pack").on("click", function () {
		$("#mahimzaman-bab-pack-rows").append(`
			<span class="mahimzaman-bab-pack-row">
				<span class="mahimzaman-bab-pack-input">
					<span class="mahimzaman-bab-input-label">Quantity</span>
					<input
						type="number"
						name="mahimzaman_bab_pack_quantity[]"
						min="1"
						step="1"
					>
				</span>

				<span class="mahimzaman-bab-pack-input mahimzaman-bab-price-field">
					<span class="mahimzaman-bab-input-label">Price</span>

					<span class="mahimzaman-bab-price-input">
						<span class="mahimzaman-bab-currency">${currency}</span>
						<input
							type="text"
							name="mahimzaman_bab_pack_price[]"
							class="wc_input_price"
						>
					</span>
				</span>

				<button
					type="button"
					class="button-link-delete mahimzaman-bab-remove-pack"
				>
					Remove
				</button>
			</span>
		`);

		updatePricing();
	});

	$(document).on("click", ".mahimzaman-bab-remove-pack", function () {
		$(this).closest(".mahimzaman-bab-pack-row").remove();
	});

	$pricing.on("change", updatePricing);
	$source.on("change", updateSource);

	updatePricing();
	updateSource();
});
