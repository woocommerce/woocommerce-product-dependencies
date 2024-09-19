<?php
/**
 * WC_PD_Helpers class
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
 * Helper functions.
 *
 * @class    WC_PD_Helpers
 * @version  2.0.0
 */
class WC_PD_Helpers {

	/**
	 * Expression of part to merge.
	 *
	 * @since  1.2.0
	 *
	 * @param  int     $loop
	 * @param  int     $count
	 * @param  string  $relationship
	 * @return string
	 */
	private static function get_part_to_merge_expression( $loop, $count, $relationship ) {

		if ( $loop === 0 ) {
			$part_to_merge = __( '%s', 'woocommerce-product-dependencies' );
		} elseif ( $count - 1 === $loop ) {
			$part_to_merge = 'and' === $relationship ? __( ' and %s', 'woocommerce-product-dependencies' ) : __( ' or %s', 'woocommerce-product-dependencies' );
		} else {
			$part_to_merge = __( ', %s', 'woocommerce-product-dependencies' );
		}

		return $part_to_merge;
	}

	/**
	 * Merges product titles.
	 *
	 * @param  array   $products
	 * @param  string  $relationship
	 * @return string
	 */
	public static function merge_product_titles( $products, $relationship ) {

		$parts_to_merge = array();

		if ( ! empty( $products ) ) {

			$loop = 0;

			foreach ( $products as $product_id => $product ) {

				$part_to_merge     = self::get_part_to_merge_expression( $loop, count( $products ), $relationship );
				$product_permalink = $product->is_visible() ? $product->get_permalink() : '';
				$product_title     = $product->get_name();

				if ( $product_permalink ) {
					$part_to_merge = sprintf( $part_to_merge, sprintf( '&quot;<a href="%1$s">%2$s</a>&quot;', esc_url( $product_permalink ), $product_title ) );
				} else {
					$part_to_merge = sprintf( $part_to_merge, sprintf( '&quot;%s&quot;', $product_title ) );
				}

				$parts_to_merge[] = $part_to_merge;

				$loop++;
			}
		}

		if ( is_rtl() ) {
			$parts_to_merge = array_reverse( $parts_to_merge );
		}

		return implode( '', $parts_to_merge );
	}

	/**
	 * Merges category titles.
	 *
	 * @since  1.2.0
	 *
	 * @param  array   $category_ids
	 * @param  string  $relationship
	 * @return string
	 */
	public static function merge_category_titles( $category_ids, $relationship ) {

		$parts_to_merge = array();

		if ( ! empty( $category_ids ) ) {

			$loop = 0;

			foreach ( $category_ids as $category_id ) {

				$part_to_merge      = self::get_part_to_merge_expression( $loop, count( $category_ids ), $relationship );
				$category_permalink = get_term_link( $category_id, 'product_cat' );

				if ( $term = get_term_by( 'id', $category_id, 'product_cat' ) ){
					$category_title = $term->name;
				} else {
					continue;
				}

				if ( $category_permalink ) {
					$part_to_merge = sprintf( $part_to_merge, sprintf( '&quot;<a href="%1$s">%2$s</a>&quot;', esc_url( $category_permalink ), $category_title ) );
				} else {
					$part_to_merge = sprintf( $part_to_merge, sprintf( '&quot;%s&quot;', $category_title ) );
				}

				$parts_to_merge[] = $part_to_merge;

				$loop++;
			}
		}

		if ( is_rtl() ) {
			$parts_to_merge = array_reverse( $parts_to_merge );
		}

		return implode( '', $parts_to_merge );
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

		return $product->get_formatted_name();
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
