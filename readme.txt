=== WooCommerce Product Dependencies ===

Contributors: franticpsyx
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=psyx@somewherewarm.net&item_name=Donation+for+WooCommerce+Product+Dependencies
Tags: woocommerce, products, dependencies, prerequisite
Requires at least: 3.3.2
Tested up to: 3.9.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce extension that allows you to restrict access to certain products, depending on the ownership and/or purchase of other, prerequisite items.

== Description ==

“Product Dependencies” is a lightweight WooCommerce extension that allows you to restrict access to any product, depending on the ownership and/or purchase of other, prerequisite items.

Features:

* Enable conditional product access based on the ownership and/or purchase of prerequisite items.
* Streamlined admin interface – prerequisite products are entered in a dedicated Dependencies tab.
* Support for multiple product dependencies.
* Support for ‘ownership’, ‘purchase’ and ‘ownership/purchase’ dependency types.

Developers can checkout and contribute to the source code on the plugin's [GitHub Repository](https://github.com/franticpsyx/woocommerce-product-dependencies/).

== Installation ==

1. Ensure you have the latest version of WooCommerce installed.
2. Unzip and upload the plugin’s folder to your /wp-content/plugins/ directory.
3. Activate the extension from the ‘Plugins’ menu in WordPress.

== Documentation ==

The integration of ‘Product Dependencies’ with WooCommerce is as straightforward and simple as possible. When a product is added to the cart, it is checked for existing dependencies. If the ownership and/or purchase criteria are not met, the product will not be added to the cart and a notification will be displayed.

= Creating Dependencies =

Product dependencies can be created by simply clicking on the new ‘Dependencies’ tab, found under ‘Product Data’, and adding products to the ‘Product Dependencies’ field.

After saving, access to your product will be enabled conditionally, based on the ownership and/or purchase of ANY item that has been added to the “Product Dependencies” field.

= Ownership vs Purchase =

The extension allows you to select between 3 different dependency types:

* Ownership: Access is granted only to customers that already own any of the items added to the “Product Dependencies” field.
* Purchase: The product can be purchased only in combination with any of the items added to the “Product Dependencies” field. Ownership is not taken into account.
* Either: Access is granted with ownership or purchase of any item added to the “Product Dependencies” field.



== Screenshots ==

1. Product dependencies can be created by simply clicking on the new ‘Dependencies’ tab, found under ‘Product Data’, and adding products to the ‘Product Dependencies’ field.
2. If the ownership and/or purchase criteria are not met, products with dependencies cannot be added to the cart and a notification will be displayed, such as this one.

== Changelog ==

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

= 1.0.4 =
Localization - Added Brazilian translation (robertopc).

