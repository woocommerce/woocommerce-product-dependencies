<?php

/*
Plugin Name: WooCommerce Product Dependencies
Plugin URI: http://www.somewherewarm.net/apps/woocommerce-product-dependencies
Description: Restrict access to WooCommerce products, depending on the ownership and/or purchase of other, prerequisite products.
Version: 1.0.3
Author: SomewhereWarm
Author URI: http://www.somewherewarm.net/
*/

/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WC_Dependencies' ) )
	require_once 'class-wc-dependencies.php';

/**
 * WC Detection
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return WC_Dependencies::woocommerce_active_check();
	}
}

if ( is_woocommerce_active() ) {

	class WC_Tied_Products {

		public function __construct() {

			add_action( 'plugins_loaded', array($this, 'woo_tied_plugins_loaded') );
			add_action( 'init', array($this, 'woo_tied_init') );

		}


		function woo_tied_plugins_loaded() {

			add_filter( 'woocommerce_add_to_cart_validation', array($this, 'woo_tied_validation'), 10, 3 );
			add_action( 'woocommerce_check_cart_items', array($this, 'woo_tied_check_cart_items'), 1 );
			add_action( 'woocommerce_process_product_meta', array($this, 'woo_tied_process_bundle_meta'), 10, 2 );
			add_action( 'woocommerce_product_write_panel_tabs', array($this, 'woo_tied_products_write_panel_tab') );
			add_action( 'woocommerce_product_write_panels', array($this, 'woo_tied_products_write_panel') );

		}


		function woo_tied_init() {

			load_plugin_textdomain( 'woo-tied', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		}


		// Write panel tab
		function woo_tied_products_write_panel_tab() {
			echo '<li class="show_if_tied tied_products_tab related_product_options linked_product_options"><a href="#tied_products_data">'.__('Dependencies', 'woo-tied').'</a></li>';
		}


		// Back-end meta boxes
		function woo_tied_products_write_panel() {

			global $woocommerce, $post;

			$tied_products 		= maybe_unserialize( get_post_meta( $post->ID, '_tied_products', true ) );
			$dependency_type 	= get_post_meta( $post->ID, '_dependency_type', true );

			if ( ! $dependency_type )
				$dependency_type = 3;

			?>

			<div id="tied_products_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">

				<p class="form-field">
					<label><?php _e( 'Product Dependencies', 'woo-tied' ); ?>
					</label>
		            <select id="tied_products" multiple="multiple" name="tied_products[]" data-placeholder="<?php _e('Search for products&hellip;', 'woo-tied'); ?>" class="ajax_chosen_select_products">
			        	<?php
			        		if ( $tied_products )
			        			foreach ( $tied_products as $item_id ) {

									$title 	= get_the_title( $item_id );
									$sku 	= get_post_meta( $item_id, '_sku', true );

									if ( !$title ) continue;

									if ( isset($sku) && $sku ) $sku = ' (SKU: ' . $sku . ')';
									echo '<option value="'.$item_id.'" selected="selected">'. $title . $sku . '</option>';
		            			}
		            	?>
			        </select>
			        <?php echo '<img class="help_tip" style="width:16px; height:16px;" data-tip="' . __('Restrict access to this product based on the ownership or purchase of the items added here.', 'woo-tied') . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />';  ?>
		    	</p>
		    	<p class="form-field">
					<label><?php _e( 'Dependency Type', 'woo-tied' ); ?>
					</label>
					<select name="dependency_type" id="dependency_type" style="min-width:150px;">
						<option value="1" <?php echo $dependency_type == 1 ? 'selected="selected"' : ''; ?>><?php _e( 'Ownership', 'woo-tied' ); ?></option>
						<option value="2" <?php echo $dependency_type == 2 ? 'selected="selected"' : ''; ?>><?php _e( 'Purchase', 'woo-tied' ); ?></option>
						<option value="3" <?php echo $dependency_type == 3 ? 'selected="selected"' : ''; ?>><?php _e( 'Either', 'woo-tied' ); ?></option>
                   </select>
				</p>
		    </div>
		<?php
		}


		// Process meta

		function woo_tied_process_bundle_meta( $post_id, $post ) {

			global $post;

			if ( ! isset( $_POST['tied_products'] ) || empty( $_POST['tied_products'] ) ) {
				delete_post_meta( $post_id, '_tied_products' );
			} elseif ( isset( $_POST['tied_products'] ) && ! empty( $_POST['tied_products'] ) ) {
				update_post_meta( $post_id, '_tied_products', $_POST['tied_products'] );
			}

			if ( isset( $_POST['dependency_type'] ) && ! empty( $_POST['dependency_type'] ) ) {
				update_post_meta( $post_id, '_dependency_type', $_POST['dependency_type'] );
			}

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

			$tied_product_ids 	= maybe_unserialize( get_post_meta( $item_id, '_tied_products', true ) );
			$dependency_type 	= get_post_meta( $item_id, '_dependency_type', true );

			if( $tied_product_ids ) {

				// Check cart
				if ( $dependency_type == 2 || $dependency_type == 3 ) {

					$cart_contents = $woocommerce->cart->cart_contents;

					foreach( $cart_contents as $cart_item ) {

						$product_id = $cart_item['product_id'];

						if ( in_array( $product_id, $tied_product_ids ) )
							return true;
					}
				}

				// Check ownership
				if ( is_user_logged_in() && ( $dependency_type == 1 || $dependency_type == 3 ) ) {

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
							$product_titles[] = '"' . get_the_title( $id ) . '"';
						}

						$woocommerce->add_error( sprintf( __( 'Access to "%2$s" is restricted only to verified owners of %1$s.', 'woo-tied' ) . ( $dependency_type == 3 ? ' ' . __('Alternatively, ', 'woo-tied') . __( 'access to this item will be granted after adding a %1$s to the cart.', 'woo-tied' ) : '' ), str_replace( ', ' . $product_titles[ count($product_titles) - 1 ], ' ' . __('or', 'woo-tied' ) . ' ' . $product_titles[ count($product_titles) - 1 ], implode( ', ', $product_titles ) ), get_the_title( $item_id ) ) );
						return false;

					}

				// Msg
				} else {

					$product_titles = array();

					foreach( $tied_product_ids as $id ) {
						$product_titles[] = '"' . get_the_title( $id ) . '"';
					}

					$msg = '';

					if ( $dependency_type == 1 )
						$msg = __( 'Access to "%2$s" is restricted only to verified owners of %1$s. The verification is automatic and simply requires you to be <a href="%3$s">'.'logged in'.'</a>.', 'woo-tied' );
					elseif ( $dependency_type == 2 )
						$msg = __( '"%2$s" can be purchased only with %1$s.', 'woo-tied' ) . ' ' . __('Note that ', 'woo-tied');
					else
						$msg = __( '"%2$s" requires the purchase of %1$s. Ownership can be verified by simply <a href="%3$s">'.'logging in'.'</a>.', 'woo-tied' ) . ' ' . __('Alternatively, ', 'woo-tied');

					$woocommerce->add_error( sprintf( $msg . ( $dependency_type == 2 || $dependency_type == 3 ? __( 'access to this item will be granted after adding a %1$s to the cart.', 'woo-tied' ) : '' ), str_replace( ', ' . $product_titles[ count($product_titles) - 1 ], ' ' . __('or', 'woo-tied' ) . ' ' . $product_titles[ count($product_titles) - 1 ], implode( ', ', $product_titles ) ), get_the_title( $item_id ), wp_login_url() ) );

					return false;
				}
			}

			return true;
		}

	}

	$GLOBALS['woocommerce_restricted_access'] = new WC_Tied_Products();

}
