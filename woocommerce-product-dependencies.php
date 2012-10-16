<?php

/*
Plugin Name: WooCommerce Product Dependencies
Plugin URI: http://www.somewherewarm.net/
Description: Woocommerce extension for restricting access to products, depending on the ownership or purchase of other items.
Version: 1.0
Author: SomewhereWarm
Author URI: http://www.somewherewarm.net/
*/

if ( is_woocommerce_active() ) {

	class WC_Tied_Products {

		public function __construct() {

			add_action( 'plugins_loaded', array($this, 'woo_tied_plugins_loaded') );
			add_action( 'init', array($this, 'woo_tied_init') );

		}


		function woo_tied_plugins_loaded() {

			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woo_tied_validation' ), 10, 3);
			add_action('woocommerce_check_cart_items', array( &$this, 'woo_tied_check_cart_items' ), 1 );

		}


		function woo_tied_init() {

			load_plugin_textdomain( 'woo-tied', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		}


		// Validate access in cart
		function woo_tied_check_cart_items() {

			global $woocommerce;

			$cart_items = $woocommerce->cart->cart_contents;

			foreach( $cart_items as $cart_item ) {

				$item_id = $cart_item['product_id'];

				if ( ! $this->woo_tied_evaluate_access( $item_id ) )
					return false;
			}

		}

		// Validate access
		function woo_tied_validation( $add, $item_id, $quantity ) {

			return $add && $this->woo_tied_evaluate_access( $item_id );

		}


		// Check
		function woo_tied_evaluate_access( $item_id ) {

			global $woocommerce;

			$tied_products = get_post_meta( $item_id, 'tied_products', true );

			$tied_product_ids = explode( ',', $tied_products );

			if( $tied_products ) {

				$cart_contents = $woocommerce->cart->cart_contents;
	
				foreach( $cart_contents as $cart_item ) {
	
					$product_id = $cart_item['product_id'];
	
					if ( in_array( $product_id, $tied_product_ids ) )
						return true;
				}

				if ( is_user_logged_in() ) {

					global $current_user;
					get_currentuserinfo();

					$is_owner = false;

					foreach( $tied_product_ids as $id ) {

						if ( woocommerce_customer_bought_product( $current_user->user_email, $current_user->ID, $id ) )
							$is_owner = true;
					}

					if ( ! $is_owner ) {

						$product_titles = array();

						foreach( $tied_product_ids as $id ) {
							$product_titles[] = get_the_title( $id );
						}

						$woocommerce->add_error( sprintf( __( 'Access to "%2$s" is restricted only to verified owners of %1$s. If you have purchased a %1$s from one of our distributors, please contact us for verification. If you intend to purchase your %1$s now, please add it to the cart first in order to unlock this item.', 'woo-tied' ), str_replace( ', ' . $product_titles[ count($product_titles) - 1 ], ' ' . __('or', 'woo-tied' ) . ' ' . $product_titles[ count($product_titles) - 1 ], implode( ', ', $product_titles ) ), get_the_title( $item_id ) ) );
						return false;

					}

				} else {

					$product_titles = array();

					foreach( $tied_product_ids as $id ) {
						$product_titles[] = get_the_title( $id );
					}

					$woocommerce->add_error( sprintf( __( 'Access to "%2$s" is restricted only to verified owners of %1$s. The verification is automatic and simply requires you to be <a href="%3$s">'.'logged in'.'</a>. If you intend to purchase your Roller SS-P, Roller SS-M or Roller SS-Custom now, please add it to the cart first in order to unlock this item.', 'woo-tied' ), str_replace( ', ' . $product_titles[ count($product_titles) - 1 ], ' ' . __('or', 'woo-tied' ) . ' ' . $product_titles[ count($product_titles) - 1 ], implode( ', ', $product_titles ) ), get_the_title( $item_id ), wp_login_url() ) );

					return false;
				}
			}

			return true;
		}

	}

	$GLOBALS['woocommerce_restricted_access'] = new WC_Tied_Products();

}
