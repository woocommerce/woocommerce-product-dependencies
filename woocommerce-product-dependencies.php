<?php

/*
* Plugin Name: WooCommerce Product Dependencies
* Plugin URI: http://www.somewherewarm.net/
* Description: Restrict access to WooCommerce products, depending on the ownership and/or purchase of other, prerequisite products.
* Version: 1.0.6
* Author: franticpsyx, SomewhereWarm
* Author URI: http://www.somewherewarm.net/
*
* Text Domain: woocommerce-product-dependencies
* Domain Path: /languages/
*
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * WC Detection
 */

require_once 'class-wc-dependencies.php';

if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return WC_Tied_Products_Dependencies::woocommerce_active_check();
	}
}

if ( is_woocommerce_active() ) {

	class WC_Tied_Products {

		public function __construct() {

			add_action( 'plugins_loaded', array( $this, 'woo_tied_plugins_loaded' ) );
			add_action( 'init', array($this, 'woo_tied_init') );
		}

		/**
		 * Initialize.
		 *
		 * @return void
		 */
		public function woo_tied_plugins_loaded() {

			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woo_tied_validation' ), 10, 3 );
			add_action( 'woocommerce_check_cart_items', array( $this, 'woo_tied_check_cart_items' ), 1 );
			add_action( 'woocommerce_process_product_meta', array( $this, 'woo_tied_process_bundle_meta' ), 10, 2 );
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'woo_tied_products_write_panel_tab' ) );
			add_action( 'woocommerce_product_write_panels', array( $this, 'woo_tied_products_write_panel' ) );
		}

		/**
		 * Init textdomain.
		 *
		 * @return void
		 */
		public function woo_tied_init() {

			load_plugin_textdomain( 'woocommerce-product-dependencies', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add Product Data tab.
		 *
		 * @return void
		 */
		public function woo_tied_products_write_panel_tab() {
			echo '<li class="show_if_tied tied_products_tab related_product_options linked_product_options"><a href="#tied_products_data">' . __( 'Dependencies', 'woocommerce-product-dependencies' ) . '</a></li>';
		}

		/**
		 * Add Product Data tab section.
		 *
		 * @return void
		 */
		public function woo_tied_products_write_panel() {

			global $woocommerce, $post;

			$tied_products   = maybe_unserialize( get_post_meta( $post->ID, '_tied_products', true ) );
			$dependency_type = get_post_meta( $post->ID, '_dependency_type', true );

			if ( ! $dependency_type ) {
				$dependency_type = 3;
			}

			$product_id_options = array();

			if ( $tied_products ) {
				foreach ( $tied_products as $item_id ) {

					$title = get_the_title( $item_id );
					$sku   = get_post_meta( $item_id, '_sku', true );

					if ( ! $title ) {
						continue;
					}

					if ( $sku ) {
						$title .= ' (SKU: ' . $sku . ')';
					}

					$product_id_options[ $item_id ] = $title;
				}
			}

			?>
			<div id="tied_products_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">

				<p class="form-field">
					<label>
						<?php _e( 'Product Dependencies', 'woocommerce-product-dependencies' ); ?>
					</label><?php

					if ( WC_Tied_Products_Dependencies::is_wc_version_gte_2_3() ) {

						?><input type="hidden" id="tied_products" name="tied_products" class="wc-product-search" style="width: 75%;" data-placeholder="<?php _e( 'Search for products&hellip;', 'woocommerce-product-dependencies' ); ?>" data-action="woocommerce_json_search_products" data-multiple="true" data-selected="<?php

							echo esc_attr( json_encode( $product_id_options ) );

						?>" value="<?php echo implode( ',', array_keys( $product_id_options ) ); ?>" /><?php

					} else {

						?><select id="tied_products" multiple="multiple" name="tied_products[]" data-placeholder="<?php _e( 'Search for products&hellip;', 'woocommerce-product-dependencies' ); ?>" class="ajax_chosen_select_products"><?php

							if ( ! empty( $product_id_options ) ) {
								foreach ( $product_id_options as $product_id => $product_name ) {
									echo '<option value="' . $product_id . '" selected="selected">' . $product_name . '</option>';
								}
							}
						?></select><?php

			    	}

			        echo '<img class="help_tip" style="width:16px; height:16px;" data-tip="' . __( 'Restrict access to this product based on the ownership or purchase of the items added here.', 'woocommerce-product-dependencies') . '" src="' . WC()->plugin_url() . '/assets/images/help.png" />';

		    	?></p>
		    	<p class="form-field">
					<label><?php _e( 'Dependency Type', 'woocommerce-product-dependencies' ); ?>
					</label>
					<select name="dependency_type" id="dependency_type" style="min-width:150px;">
						<option value="1" <?php echo $dependency_type == 1 ? 'selected="selected"' : ''; ?>><?php _e( 'Ownership', 'woocommerce-product-dependencies' ); ?></option>
						<option value="2" <?php echo $dependency_type == 2 ? 'selected="selected"' : ''; ?>><?php _e( 'Purchase', 'woocommerce-product-dependencies' ); ?></option>
						<option value="3" <?php echo $dependency_type == 3 ? 'selected="selected"' : ''; ?>><?php _e( 'Either', 'woocommerce-product-dependencies' ); ?></option>
                   </select>
				</p>
		    </div>
		<?php
		}

		/**
		 * Save meta.
		 *
		 * @param  int     $post_id
		 * @param  WC_Post $post
		 * @return void
		 */
		public function woo_tied_process_bundle_meta( $post_id, $post ) {

			global $post;

			if ( ! isset( $_POST[ 'tied_products' ] ) || empty( $_POST[ 'tied_products' ] ) ) {
				delete_post_meta( $post_id, '_tied_products' );
			} elseif ( isset( $_POST[ 'tied_products' ] ) && ! empty( $_POST[ 'tied_products' ] ) ) {

				$tied_ids = $_POST[ 'tied_products' ];

				if ( is_array( $tied_ids ) ) {
					$tied_ids = array_map( 'intval', $tied_ids );
				} else {
					$tied_ids = array_filter( array_map( 'intval', explode( ',', $tied_ids ) ) );
				}

				update_post_meta( $post_id, '_tied_products', $tied_ids );
			}

			if ( isset( $_POST[ 'dependency_type' ] ) && ! empty( $_POST[ 'dependency_type' ] ) ) {
				update_post_meta( $post_id, '_dependency_type', stripslashes( $_POST[ 'dependency_type' ] ) );
			}
		}


		/**
		 * Validate access in cart.
		 *
		 * @return boolean
		 */
		public function woo_tied_check_cart_items() {

			$cart_items = WC()->cart->cart_contents;

			foreach ( $cart_items as $cart_item ) {

				$item_id = $cart_item['product_id'];

				if ( ! $this->woo_tied_evaluate_access( $item_id ) ) {
					return false;
				}
			}

		}

		/**
		 * Validate access.
		 * @param  boolean $add
		 * @param  int     $item_id
		 * @param  int     $quantity
		 * @return boolean
		 */
		public function woo_tied_validation( $add, $item_id, $quantity ) {

			return $add && $this->woo_tied_evaluate_access( $item_id );
		}


		/**
		 * Check conditions.
		 *
		 * @param  int $item_id
		 * @return boolean
		 */
		public function woo_tied_evaluate_access( $item_id ) {

			$tied_product_ids = get_post_meta( $item_id, '_tied_products', true );
			$dependency_type  = absint( get_post_meta( $item_id, '_dependency_type', true ) );

			if ( $tied_product_ids ) {

				$tied_product_ids = array_values( get_post_meta( $item_id, '_tied_products', true ) );

				// Check cart
				if ( $dependency_type === 2 || $dependency_type === 3 ) {

					$cart_contents = WC()->cart->cart_contents;

					foreach ( $cart_contents as $cart_item ) {

						$product_id = $cart_item[ 'product_id' ];

						if ( in_array( $product_id, $tied_product_ids ) )
							return true;
					}
				}

				// Check ownership
				if ( is_user_logged_in() && ( $dependency_type === 1 || $dependency_type === 3 ) ) {

					global $current_user;
					get_currentuserinfo();

					$is_owner = false;

					foreach ( $tied_product_ids as $id ) {

						if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $id ) ) {
							$is_owner = true;
						}
					}

					if ( ! $is_owner ) {

						$product_titles = array();

						foreach ( $tied_product_ids as $id ) {
							if ( $tied_product_ids[ count( $tied_product_ids ) - 1 ] === $id ) {
								$product_titles[] = sprintf( __( ' or &quot;%s&quot;', 'woocommerce-product-dependencies' ), get_the_title( $id ) );
							} elseif ( $tied_product_ids[ 0 ] === $id ) {
								$product_titles[] = sprintf( __( '&quot;%s&quot;', 'woocommerce-product-dependencies' ), get_the_title( $id ) );
							} else {
								$product_titles[] = sprintf( __( ', &quot;%s&quot;', 'woocommerce-product-dependencies' ), get_the_title( $id ) );
							}
						}

						if ( is_rtl() ) {
							$product_titles = array_reverse( $product_titles );
						}

						if ( $dependency_type === 1 ) {
							wc_add_notice( sprintf( __( 'Access to &quot;%2$s&quot; is restricted only to verified owners of %1$s.', 'woocommerce-product-dependencies' ), implode( '', $product_titles ), get_the_title( $item_id ) ), 'error' );
						} else {
							wc_add_notice( sprintf( __( 'Access to &quot;%2$s&quot; is restricted only to verified owners of %1$s. Alternatively, access to this item will be granted after adding a %1$s to the cart.', 'woocommerce-product-dependencies' ), implode( '', $product_titles ), get_the_title( $item_id ) ), 'error' );
						}
						return false;
					}

				} else {

					$product_titles = array();

					foreach ( $tied_product_ids as $id ) {
						if ( $tied_product_ids[ count( $tied_product_ids ) - 1 ] === $id ) {
							$product_titles[] = sprintf( __( ' or &quot;%s&quot;', 'woocommerce-product-dependencies' ), get_the_title( $id ) );
						} elseif ( $tied_product_ids[ 0 ] === $id ) {
							$product_titles[] = sprintf( __( '&quot;%s&quot;', 'woocommerce-product-dependencies' ), get_the_title( $id ) );
						} else {
							$product_titles[] = sprintf( __( ', &quot;%s&quot;', 'woocommerce-product-dependencies' ), get_the_title( $id ) );
						}
					}

					if ( is_rtl() ) {
						$product_titles = array_reverse( $product_titles );
					}

					$msg = '';

					if ( $dependency_type === 1 ) {
						$msg = __( 'Access to &quot;%2$s&quot; is restricted only to verified owners of %1$s. The verification is automatic and simply requires you to be <a href="%3$s">logged in</a>.', 'woocommerce-product-dependencies' );
					} elseif ( $dependency_type === 2 ) {
						$msg = __( '&quot;%2$s&quot; can be purchased only in combination with %1$s. Access to this item will be granted after adding a %1$s to the cart.', 'woocommerce-product-dependencies' );
					} else {
						$msg = __( '&quot;%2$s&quot; requires the purchase of %1$s. Ownership can be verified by simply <a href="%3$s">logging in</a>. Alternatively, access to this item will be granted after adding a %1$s to the cart.', 'woocommerce-product-dependencies' );
					}

					wc_add_notice( sprintf( $msg, implode( '', $product_titles ), get_the_title( $item_id ), wp_login_url() ), 'error' );

					return false;
				}
			}

			return true;
		}
	}

	$GLOBALS[ 'woocommerce_restricted_access' ] = new WC_Tied_Products();
}
