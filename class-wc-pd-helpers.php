<?php
/**
 * WC_PD_Helpers class
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
 * Helper functions.
 *
 * @class    WC_PD_Helpers
 */
class WC_PD_Helpers {

	public static function merge_product_titles( $products ) {

		$product_titles = array();

		if ( ! empty( $products ) ) {

			$loop = 0;

			foreach ( $products as $product_id => $product ) {

				if ( $loop === 0 ) {
					$product_title = __( '%s', 'woocommerce-product-dependencies' );
				} elseif ( count( $products ) - 1 === $loop ) {
					$product_title = __( ' or %s', 'woocommerce-product-dependencies' );
				} else {
					$product_title = __( ', %s', 'woocommerce-product-dependencies' );
				}

				$product_permalink = $product->is_visible() ? $product->get_permalink() : '';

				if ( $product_permalink ) {
					$product_title = sprintf( $product_title, sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product->get_title() ) );
				} else {
					$product_title = sprintf( $product_title, $product->get_title() );
				}

				$product_titles[] = $product_title;

				$loop++;
			}
		}

		if ( is_rtl() ) {
			$product_titles = array_reverse( $product_titles );
		}

		return implode( '', $product_titles );
	}

	/**
	 * Return a formatted product title.
	 *
	 * @param  WC_Product|int  $product
	 * @param  string          $title
	 * @return string
	 */
	public static function get_product_title( $product, $title = '' ) {

		if ( ! is_object( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product ) {
			return false;
		}

		if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {
			return $product->get_formatted_name();
		}

		$title = $title ? $title : $product->get_title();
		$sku   = $product->get_sku();
		$id    = WC_PD_Core_Compatibility::get_id( $product );

		if ( $sku ) {
			$identifier = $sku;
		} else {
			$identifier = '#' . $id;
		}

		return self::format_product_title( $title, $identifier, '', WC_PD_Core_Compatibility::is_wc_version_gte_2_7() );
	}

	/**
	 * Format a product title.
	 *
	 * @param  string  $title
	 * @param  string  $identifier
	 * @param  string  $meta
	 * @param  string  $paren
	 * @return string
	 */
	public static function format_product_title( $title, $identifier = '', $meta = '', $paren = false ) {

		if ( $identifier && $meta ) {
			if ( $paren ) {
				$title = sprintf( _x( '%1$s &ndash; %2$s (%3$s)', 'product title followed by meta and sku in parenthesis', 'woocommerce-product-dependencies' ), $title, $meta, $identifier );
			} else {
				$title = sprintf( _x( '%1$s &ndash; %2$s &ndash; %3$s', 'sku followed by product title and meta', 'woocommerce-product-dependencies' ), $identifier, $title, $meta );
			}
		} elseif ( $identifier ) {
			if ( $paren ) {
				$title = sprintf( _x( '%1$s (%2$s)', 'product title followed by sku in parenthesis', 'woocommerce-product-dependencies' ), $title, $identifier );
			} else {
				$title = sprintf( _x( '%1$s &ndash; %2$s', 'sku followed by product title', 'woocommerce-product-dependencies' ), $identifier, $title );
			}
		} elseif ( $meta ) {
			if ( $paren ) {
				$title = sprintf( _x( '%1$s (%2$s)', 'product title followed by meta in parenthesis', 'woocommerce-product-dependencies' ), $title, $meta );
			} else {
				$title = sprintf( _x( '%1$s &ndash; %2$s', 'product title followed by meta', 'woocommerce-product-dependencies' ), $title, $meta );
			}
		}

		return $title;
	}
}
