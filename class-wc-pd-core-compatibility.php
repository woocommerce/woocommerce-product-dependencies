<?php
/**
 * WC_PD_Core_Compatibility class
 *
 * @author   WooCommerce
 * @package  WooCommerce Product Dependencies
 * @since    1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions for WC core back-compatibility.
 *
 * @class  WC_PD_Core_Compatibility
 */
class WC_PD_Core_Compatibility {

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @return string
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is 8.2 or greater.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_8_2() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '8.2', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @param  string  $version the version to compare
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gt( $version ) {
		return self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
	}
}
