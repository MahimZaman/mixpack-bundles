# MixPack Bundles for WooCommerce

## Version 1.0 Functional Specification

**Plugin:** MixPack Bundles for WooCommerce
**Slug:** `mixpack-bundles`
**Text Domain:** `mixpack-bundles`
**PHP Namespace:** `MixPack\Bundles`

---

# 1. Product Goal

MixPack Bundles allows WooCommerce merchants to create visual mix-and-match product packs.

A customer should be able to:

**Choose Pack → Choose Products → Complete Pack → Add to Cart**

The plugin must feel like a bundle-building experience rather than repeatedly adding separate WooCommerce products.

The first release is optimized for products such as functional shots, while the internal architecture must allow future bundle types containing products such as gummies, tablets, capsules, shots, and other WooCommerce products.

---

# 2. Core WooCommerce Product Type

MixPack Bundles will introduce a custom WooCommerce product type:

**MixPack Bundle**

The product will still use normal WooCommerce functionality for:

- Product title
- Product description
- Featured image
- Product gallery
- Product status
- Catalog visibility
- Categories
- Tax configuration
- General WooCommerce product functionality where applicable

Bundle-specific settings will appear inside the standard WooCommerce Product Data interface.

No separate top-level WordPress admin menu will be required for creating bundles.

---

# 3. Pack Sizes

A MixPack Bundle may contain one or more configurable pack sizes.

Example:

| Pack    | Required Quantity |  Price |
| ------- | ----------------: | -----: |
| 3-Pack  |                 3 | $14.99 |
| 6-Pack  |                 6 | $26.99 |
| 12-Pack |                12 | $47.99 |

Pack sizes are configurable and must not be hard-coded to 3, 6, and 12.

A merchant may create other sizes later, such as:

- 4-Pack
- 8-Pack
- 10-Pack
- 24-Pack

Each enabled pack size must have a unique positive quantity.

---

# 4. Customer Pack Selection

If a bundle contains multiple pack sizes, the customer must first choose the desired pack.

Example:

**Choose Your Pack**

3 Pack
6 Pack
12 Pack

After selecting a pack, the product selection interface becomes available.

If a bundle contains only one pack size, the plugin may automatically select it and avoid showing an unnecessary pack-selection step.

---

# 5. Eligible Products

Version 1.0 will support two simple methods for determining which products appear inside the builder.

**Selected Products**

The merchant manually chooses individual WooCommerce products.

Example:

PSYNQ
IGNITE
PSUNQ
KAVANNA

**Product Categories**

The merchant selects one or more WooCommerce product categories and eligible products are automatically loaded from those categories.

The v1 admin interface will not contain advanced include/exclude rule builders, tag logic, attribute logic, price conditions, or similar complex targeting systems.

---

# 6. Supported Product Types

Version 1.0 will officially support:

**WooCommerce Simple Products**

Variable products and other complex product types are outside the v1.0 user-facing feature set.

The internal architecture must avoid assumptions that permanently prevent variable-product support from being added later.

---

# 7. Mix-and-Match Selection

Customers may mix different eligible products inside the selected pack.

Multiple units of the same product are allowed by default.

Example 6-Pack:

2 × PSYNQ
2 × IGNITE
1 × PSUNQ
1 × KAVANNA

Total selected:

6 of 6

A pack is complete only when the exact required quantity has been selected.

---

# 8. Quantity Controls

Each product displayed in the builder will provide clear quantity controls.

Conceptually:

**− 2 +**

The customer must be able to increase or decrease selected quantities without page reloads.

The interface must prevent the total quantity from exceeding the selected pack size.

The server must independently validate these rules and must never rely exclusively on JavaScript.

---

# 9. Progress

The builder must display live progress.

Examples:

**0 of 6 selected**

**4 of 6 selected**

**6 of 6 selected**

When incomplete, the interface may also display helpful text such as:

**2 more items to complete your pack**

When complete:

**Pack Complete**

The Add to Cart button remains unavailable until the configuration is valid.

---

# 10. Pricing

Version 1.0 will support two pricing methods.

## Fixed Pack Pricing

This is the default and simplest pricing mode.

Each pack size receives its own fixed price.

Example:

3-Pack — $14.99
6-Pack — $26.99
12-Pack — $47.99

This naturally allows merchants to provide quantity discounts without installing a separate dynamic-pricing plugin.

## Calculated Product Pricing

The bundle price is calculated from the current prices of the selected WooCommerce products.

Example:

2 × Product A
2 × Product B
2 × Product C

The plugin totals the prices of all selected components.

Pricing must always be calculated and verified on the server.

Prices received from JavaScript or request parameters must never be trusted.

---

# 11. Discount Engine

A separate advanced discount engine will not be included in v1.0.

Fixed pack prices already allow merchants to create discounted pack sizes.

Advanced functionality such as BOGO rules, customer-role pricing, scheduled promotions, complex tier tables, conditional discounts, and dynamic-pricing rule builders are intentionally excluded from v1.0.

Normal WooCommerce coupons should continue to work wherever WooCommerce normally permits them.

---

# 12. Frontend Builder

The default frontend sequence is:

**Choose Your Pack**

↓

**Choose Your Products**

↓

**Selection Progress**

↓

**Pack Complete**

↓

**Add to Cart**

The visual product grid should display, where available:

- Product image
- Product name
- Quantity
- Increase control
- Decrease control
- Availability state

Pricing may also be displayed when appropriate.

The interface must remain understandable without requiring the customer to read documentation.

---

# 13. Frontend Design Standard

The frontend must use a clean and restrained visual style.

It must:

- Work with common WooCommerce themes
- Avoid excessive fixed widths
- Avoid assumptions about theme colors
- Remain responsive
- Use scoped CSS classes
- Avoid overriding unrelated WooCommerce/theme styles
- Work on mobile, tablet, and desktop
- Use clear selected and completed states

Remix Icon may be used for meaningful interface actions and feedback.

Icons must supplement understandable controls rather than replace important text without accessible labels.

---

# 14. WordPress Admin Design Standard

The admin interface should look like part of WooCommerce.

Bundle settings must be located inside the existing WooCommerce Product Data area wherever practical.

The plugin should primarily use:

- Standard WordPress fields
- WooCommerce product selectors
- WooCommerce-style tabs/panels
- Existing WordPress notices
- Existing WordPress buttons
- Standard spacing and typography

The plugin will not introduce an unnecessary custom dashboard.

Advanced internal concepts must not be exposed simply because they exist in the code.

---

# 15. Admin Bundle Configuration

A merchant should be able to configure a basic bundle using only these concepts:

**Pack Sizes**

Quantity and price.

**Products**

Selected products or product categories.

**Pricing**

Fixed pack price or calculated product price.

**Selection**

Allow customers to select multiple quantities of the same product.

This should be enough for the majority of merchants to publish a bundle without reading a tutorial.

---

# 16. Add to Cart Validation

Every Add to Cart request must be validated by PHP.

Validation must verify:

- The bundle exists
- The selected pack size exists
- The selected pack size is enabled
- Selected product IDs are valid
- Products belong to the bundle
- Products are purchasable
- Products have valid quantities
- Total selected quantity matches the pack requirement
- Stock requirements can be satisfied
- Pricing is recalculated correctly

Manipulating browser JavaScript must not allow customers to bypass bundle rules.

---

# 17. Cart Architecture

The customer should perceive the configured pack as one bundle.

Example:

**Build Your Own 6-Pack — $26.99**

2 × PSYNQ
2 × IGNITE
1 × PSUNQ
1 × KAVANNA

Internally, the system should retain the relationship between the parent bundle and its actual WooCommerce product components.

The final technical implementation will use a parent/child cart relationship appropriate for WooCommerce inventory and order handling.

---

# 18. Cart Identity

Two bundles with different configurations must remain separate cart configurations.

For example:

Bundle A:

3 × PSYNQ
3 × IGNITE

must not automatically merge with:

Bundle B:

2 × PSYNQ
2 × IGNITE
2 × KAVANNA

even if both use the same six-pack parent product.

Identical configurations may follow normal WooCommerce quantity behavior when safe.

---

# 19. Edit Bundle

Cart items should provide an **Edit Bundle** action.

The action returns the customer to the bundle product with their existing configuration restored.

The customer can modify selections and update the bundle.

The complete bundle builder does not need to be embedded directly inside the cart.

---

# 20. Inventory

Inventory will come from the underlying WooCommerce products.

Example stock:

PSYNQ: 20

Customer purchases:

2 × PSYNQ

WooCommerce should account for two units of PSYNQ.

The plugin must not maintain a second competing inventory system for component products.

A bundle cannot be purchased when its selected components cannot satisfy required stock rules.

---

# 21. Out-of-Stock Products

Products that cannot currently be purchased should not allow invalid selections.

Depending on context they may be:

- Disabled in the builder
- Marked unavailable
- Excluded from the selectable product list

The plugin must still perform server-side stock validation when adding to cart and during WooCommerce checkout processing.

---

# 22. Checkout

Bundle configuration must survive the complete WooCommerce checkout lifecycle.

The plugin must support:

- Classic cart/checkout
- WooCommerce Cart Block
- WooCommerce Checkout Block

Customers must see understandable bundle information during checkout.

---

# 23. Orders

Orders must permanently retain the configuration purchased at checkout.

Changing the bundle product later must not rewrite historical orders.

Order information should make it clear which products and quantities were contained in the purchased bundle.

This information must be available to store administrators for fulfillment.

---

# 24. Customer Order Information

Where appropriate, bundle contents should remain understandable in:

- Order received page
- My Account order details
- WooCommerce order emails

The presentation should be concise rather than displaying internal metadata.

---

# 25. Refunds

MixPack Bundles will preserve component information sufficiently for WooCommerce administrators to understand what was purchased when processing refunds.

Version 1.0 will use WooCommerce's existing refund system rather than implementing a separate refund engine.

---

# 26. Taxes

The plugin will use WooCommerce tax functionality.

It will not create a separate tax calculation engine.

Exact tax handling will depend on the selected bundle pricing strategy and WooCommerce configuration.

---

# 27. Shipping

Where product components affect shipping, their relevant WooCommerce product information should remain available to the bundle/cart/order system.

MixPack Bundles will not implement a separate shipping-rate engine.

---

# 28. Bundle Editing After Product Changes

A customer's existing cart configuration must be revalidated when necessary.

If a merchant removes an eligible product, changes availability, or stock changes after a customer initially builds a pack, the plugin must reject or safely update an invalid configuration rather than completing an impossible order.

---

# 29. Internal Future-Proofing

Although v1.0 will expose a simple bundle configuration, the internal architecture will model product selections around selection groups.

Conceptually:

Bundle

→ Selection Group

→ Eligible Products

→ Quantity Rules

This allows the initial implementation:

**Shots — choose exactly 6**

to eventually become:

**Gummies — choose 1**

**Tablets — choose 1**

**Shots — choose 4**

without replacing the entire pricing, cart, order, or validation architecture.

---

# 30. Multi-Group Bundles

Multiple customer-facing selection groups are not required in the initial v1.0 admin interface.

The architecture must support introducing them in a future version.

This prevents unnecessary complexity for the initial release while maintaining extensibility.

---

# 31. Accessibility

Interactive controls must be usable and understandable beyond visual styling alone.

Relevant controls should have:

- Accessible labels
- Keyboard-friendly interaction
- Clear disabled states
- Clear focus states
- Semantic buttons
- Understandable progress feedback

Important information must not depend entirely on color or icons.

---

# 32. Remix Icon

Remix Icon will be bundled locally with the plugin where required.

The plugin must not depend on a public CDN for core interface icons.

Required third-party licensing information will be included with the plugin.

Icons will be used only where they improve usability.

---

# 33. Responsive Behavior

The bundle builder must work without broken layouts on:

Mobile
Tablet
Laptop/Desktop
Wide desktop

Product cards should adapt naturally to the available container width rather than depend on the entire screen width.

---

# 34. Theme Compatibility

Plugin frontend styles must be scoped under plugin-specific classes.

MixPack Bundles must avoid global CSS selectors that alter unrelated WordPress, WooCommerce, or theme elements.

The interface should inherit sensible typography and colors from the active theme where practical.

---

# 35. High-Performance Order Storage

WooCommerce High-Performance Order Storage compatibility is required.

Order handling must use supported WooCommerce APIs rather than assuming that orders are stored in WordPress posts and post meta.

---

# 36. WooCommerce Blocks

Cart and Checkout Blocks are part of the supported v1.0 environment.

Where bundle data must be exposed to WooCommerce's Store API, the plugin will use supported WooCommerce extensibility mechanisms.

---

# 37. Security Requirements

All incoming data must be treated as untrusted.

The plugin will use appropriate:

- Capability checks
- Nonces
- Sanitization
- Validation
- Contextual output escaping
- WooCommerce/WordPress APIs
- Prepared database queries if custom SQL is ever necessary

Frontend bundle configuration cannot be trusted as authoritative pricing or authorization data.

---

# 38. Performance Requirements

Bundle-specific frontend assets should load only where they are required.

Admin assets should load only on relevant administration screens.

The plugin should avoid:

- Unnecessary global queries
- N+1 product queries
- Repeated expensive product lookups
- Loading unused CSS or JavaScript site-wide

Performance optimizations must not compromise correctness.

---

# 39. Internationalization

All merchant-facing and customer-facing strings must be translation-ready.

Text domain:

`mixpack-bundles`

The plugin must follow WordPress internationalization practices.

---

# 40. Plugin Configuration Philosophy

MixPack Bundles should require almost no global configuration.

Normal workflow:

**Install Plugin**

↓

**Add Product**

↓

**Choose MixPack Bundle**

↓

**Configure Pack Sizes**

↓

**Choose Products**

↓

**Choose Pricing**

↓

**Publish**

A merchant should not need to work through a setup wizard before creating their first bundle.

---

# 41. Version 1.0 Non-Goals

The following features are intentionally excluded from the first release:

Variable-product selection UI
Subscription-specific behavior
Composite-product engine
Advanced conditional logic
Conditional product dependencies
BOGO engine
Customer-role pricing
Date-based pricing schedules
Complex tiered discount rules
Custom shipping engine
Custom tax engine
Separate inventory system
Tag-based eligibility rules
Attribute-based eligibility rules
Product recommendation engine
Analytics dashboard
Dedicated visual page builder
Mandatory onboarding wizard

These may be evaluated individually in future versions.

---

# 42. Developer Extensibility

Core business logic should be modular.

Where useful, WordPress actions and filters may be provided for functionality such as:

Eligible products
Bundle validation
Pricing
Frontend display data
Cart metadata
Order metadata

Extension points should only be added where they have a clear use case.

---

# 43. Version 1.0 Success Criteria

Version 1.0 is considered functionally successful when a merchant can create a MixPack Bundle, configure pack sizes, choose eligible simple products or categories, select a pricing mode, and publish the product without writing code.

A customer must then be able to choose a pack size, mix and match eligible products, see live selection progress, complete the required quantity, add the valid bundle to cart, checkout normally, and receive an order containing the correct bundle configuration.

WooCommerce administrators must be able to understand exactly what was purchased and fulfill the corresponding component products.

Underlying inventory must remain accurate.

The experience must work with supported classic WooCommerce flows, Cart/Checkout Blocks, and HPOS.

The admin and frontend interfaces must remain clean, responsive, accessible, and understandable without requiring extensive tutorials.
