<?php
/**
 * WC Dependency Checker
 *
 * Checks if WooCommerce is enabled
 */
class WC_Tied_Products_Dependencies {

	private static $active_plugins;

	function init() {

		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() )
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	/**
	 * Checks if WC is active.
	 *
	 * @return boolean
	 */
	public static function woocommerce_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}
		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );

	}

	/**
	 * Helper method to get the version of the currently installed WooCommerce
	 *
	 * @since 1.0.5
	 * @return string woocommerce version number or null
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.3 or greater
	 *
	 * @since 1.0.5
	 * @return boolean true if the installed version of WooCommerce is 2.2 or greater
	 */
	public static function is_wc_version_gte_2_3() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.3', '>=' );
	}
}


