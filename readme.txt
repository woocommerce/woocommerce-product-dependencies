=== WooCommerce Product Dependencies ===

Contributors: SomewhereWarm, franticpsyx, jasonkytros
Tags: woocommerce, products, dependencies, prerequisite, required, access, restrict, ownership, purchase, together
Requires at least: 3.8
Tested up to: 5.7
WC requires at least: 2.2
WC tested up to: 5.4
Stable tag: 1.2.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Restrict access to any WooCommerce product, depending on the ownership and/or purchase of other required products.


== Description ==

Looking for a way to restrict product access in WooCommerce? Don't want to set up a full-fledged [memberships](https://woocommerce.com/products/woocommerce-memberships/) site?

This tiny plugin allows you to restrict access to any WooCommerce product, depending on the ownership or purchase of other, required products.

Features:

* **Conditional product access** based on the ownership and/or purchase of other required products.
* Support for "ownership", "purchase" and "ownership/purchase" dependency types.

Developers can checkout and contribute to the source code on the plugin's [GitHub Repository](https://github.com/somewherewarm/woocommerce-product-dependencies/).

**Important**: Requires WooCommerce 2.2+. WooCommerce 3.0+ or higher recommended.

Like this plugin? You'll love our official WooCommerce Extensions:

* [WooCommerce Product Bundles](https://woocommerce.com/products/product-bundles/)
* [WooCommerce Composite Products](https://woocommerce.com/products/composite-products/)
* [WooCommerce Conditional Shipping and Payments](https://woocommerce.com/products/conditional-shipping-and-payments/)
* [WooCommerce Product Recommendations](https://woocommerce.com/products/product-recommendations/)
* [All Products for WooCommerce Subscriptions](https://woocommerce.com/products/all-products-for-woocommerce-subscriptions/)


== Installation ==

1. Download the plugin.
2. Go to your WordPress Dashboard and then click **Plugins > Add New**.
3. Click **Upload Plugin** at the top.
4. Click **Choose File** and select the .zip file you downloaded in **Step 1**.
5. Click **Install Now** and **Activate** the plugin.


== Documentation ==

Dependencies are evaluated when customers attempt to add a product to their cart. If validation fails, the product cannot be added to the cart and a notice is displayed. In order to evaluate "Ownership"-type dependencies, customers are prompted to log in.

= Creating Dependencies =

To add dependencies to a product:

* Go to the **Product Data > Dependencies** tab.
* Use the **Product Dependencies** field to search for and add some products and/or variations.
* Choose a **Dependency Type**.
* **Update** to save your changes.

The "Ownership" dependency type is evaluated by checking if the customer has purchased a required product in a previous order. The "Purchase" dependency type requires the customer to have a required product in the cart in order to purchase the dependent one.

= Ownership vs Purchase =

The plugin allows you to select between 3 different dependency types:

* **Ownership**: Access is granted only to customers that already own any of the products added to the Product Dependencies field.
* **Purchase**: The product can be purchased only in combination with any of the items added to the Product Dependencies field. Ownership is not taken into account.
* **Either**: Access is granted with ownership or purchase of any item added to the Product Dependencies field.


== Screenshots ==

1. Product dependencies can be created from the **Dependencies** tab, found under **Product Data**, and adding products/variations to the **Product Dependencies** field.
2. If the ownership and/or purchase conditions are not met, products with dependencies cannot be added to the cart and a notification is displayed.


== Changelog ==

= 1.2.7 =
* Fix - Prevent products from satisfying their own dependencies.

= 1.2.6 =
* Tweak - Declared support for WooCommerce 5.0.

= 1.2.5 =
* Tweak - Declared support for WooCommerce 4.2.

= 1.2.4 =
* Tweak - Declared support for WC 4.1 and WP 5.4.

= 1.2.3 =
* Tweak - Updated plugin headers to declare support for WC 3.9 and WP 5.3.

= 1.2.2 =
* Fix - Remove esc_html when rendering custom notices.

= 1.2.1 =
* Fix - Products with "Purchase"-type category dependencies cannot be added to the cart although a variation of a product that belongs to the required category has been added to the cart.

= 1.2.0 =
* Feature - Introduced category-based dependencies.
* Feature - Added custom dependency notices.
* Feature - Added AND/OR dependency relationship. Can be activated for specific products using the 'wc_pd_dependency_relationship' filter.
* Tweak - Tweaked default notices.

= 1.1.3 =
* Tweak - Updated description.
* Tweak - Added WooCommerce version headers.

= 1.1.2 =
* Fix - Variable product dependencies not validated correctly in the cart.

= 1.1.1 =
* Fix - Dependencies not working under WC < 3.0 after last update. Fixed!

= 1.1.0 =
* Refactored and cleaned up plugin.
* Fix - Added support for WooCommerce 3.0.
* Tweak - Add links to dependent products in notices.

= 1.0.7 =
* Fix - Stray "or" in dependent products list when only one dependency is present.
* Localization - Added Brazilian Portuguese translation.

= 1.0.6 =
* Fix - PHP array_values warning.

= 1.0.5 =
* Fix - WC 2.3 support.

= 1.0.4 =
* Localization - Added Brazilian translation (robertopc)

= 1.0.3 =
* Fix - Saving bug

= 1.0.2 =
* Fix - WC detection fix

= 1.0.1 =
* Tweak - Styling support for WooCommerce v2 write-panels

= 1.0 =
* Initial release


== Upgrade Notice ==

= 1.2.7 =
Prevent products from satisfying their own dependencies.
