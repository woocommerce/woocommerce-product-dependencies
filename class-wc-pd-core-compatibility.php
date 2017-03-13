<?php
/**
 * WC_PD_Core_Compatibility class
 *
 * @author   SomewhereWarm <sw@somewherewarm.net>
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
	 * Returns true if the installed version of WooCommerce is 2.7 or greater.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_7() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.7', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.6 or greater.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_6() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.6', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.5 or greater.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_5() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.5', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.4 or greater.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_4() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.4', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.3 or greater.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_3() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.3', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.2 or greater.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_2() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.2', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is less than 2.2.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_lt_2_2() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.2', '<' );
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

	/**
	 * Get the WC Product instance for a given product ID or post.
	 *
	 * get_product() is soft-deprecated in WC 2.2
	 *
	 * @param  bool|int|string|WP_Post  $the_product
	 * @param  array                    $args
	 * @return WC_Product
	 */
	public static function wc_get_product( $the_product = false, $args = array() ) {
		if ( self::is_wc_version_gte_2_2() ) {
			return wc_get_product( $the_product, $args );
		} else {

			return get_product( $the_product, $args );
		}
	}

	/**
	 * Display a WooCommerce help tip.
	 *
	 * @param  string  $tip
	 * @return string
	 */
	public static function wc_help_tip( $tip ) {
		if ( self::is_wc_version_gte_2_5() ) {
			return wc_help_tip( $tip );
		} else {
			return '<img class="help_tip woocommerce-help-tip" data-tip="' . $tip . '" src="' . WC()->plugin_url() . '/assets/images/help.png" />';
		}
	}

	/**
	 * Back-compat wrapper for 'get_parent_id'.
	 *
	 * @param  WC_Product  $product
	 * @return mixed
	 */
	public static function get_parent_id( $product ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return $product->get_parent_id();
		} else {
			return $product->is_type( 'variation' ) ? absint( $product->id ) : 0;
		}
	}

	/**
	 * Back-compat wrapper for 'get_id'.
	 *
	 * @param  WC_Product  $product
	 * @return mixed
	 */
	public static function get_id( $product ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return $product->get_id();
		} else {
			return $product->is_type( 'variation' ) ? absint( $product->variation_id ) : absint( $product->id );
		}
	}
}
