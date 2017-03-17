<?php

/*
* Plugin Name: WooCommerce Product Dependencies
* Plugin URI: http://somewherewarm.gr/
* Description: Restrict access to WooCommerce products, depending on the ownership and/or purchase of other, prerequisite products.
* Version: 1.1.1
* Author: SomewhereWarm
* Author URI: http://somewherewarm.gr/
*
* Text Domain: woocommerce-product-dependencies
* Domain Path: /languages/
*
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

class WC_Product_Dependencies {

	/**
	 * The single instance of the class.
	 * @var WC_Product_Dependencies
	 *
	 * @since 1.1.0
	 */
	protected static $_instance = null;

	/**
	 * Required WC version.
	 * @var string
	 */
	private $required = '2.2';

	/**
	 * Main WC_Product_Dependencies instance.
	 *
	 * Ensures only one instance of WC_Product_Dependencies is loaded or can be loaded - @see 'WC_Product_Dependencies()'.
	 *
	 * @since  1.1.0
	 *
	 * @static
	 * @return WC_Product_Dependencies - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-dependencies' ), '1.1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-dependencies' ), '1.1.0' );
	}

	/**
	 * Fire in the hole!
	 */
	public function __construct() {

		// Load plugin on 'plugins_loaded' hook.
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function plugins_loaded() {

		// Core compatibility functions.
		require_once( 'class-wc-pd-core-compatibility.php' );

		if ( ! function_exists( 'WC' ) || ! WC_PD_Core_Compatibility::is_wc_version_gte_2_2() ) {
			return;
		}

		// Helper functions.
		require_once( 'class-wc-pd-helpers.php' );

		// Init textdomain.
		add_action( 'init', array( $this, 'init') );

		// Validate add-to-cart action.
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 10, 3 );

		// Validate products in cart.
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ), 1 );

		if ( is_admin() ) {

			// Save admin options.
			if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {
				add_action( 'woocommerce_admin_process_product_object', array( $this, 'process_product_data' ) );
			} else {
				add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta' ), 10, 2 );
			}

			// Add the "Dependencies" tab in Product Data.
			if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_5() ) {
				add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'dependencies_product_data_tab' ) );
			} else {
				add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'dependencies_product_data_panel_tab' ) );
			}

			// Add the "Dependencies" tab content in Product Data.
			if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {
				add_action( 'woocommerce_product_data_panels', array( $this, 'dependencies_product_data_panel' ) );
			} else {
				add_action( 'woocommerce_product_write_panels', array( $this, 'dependencies_product_data_panel' ) );
			}
		}
	}

	/**
	 * Init textdomain.
	 *
	 * @return void
	 */
	public function init() {
		load_plugin_textdomain( 'woocommerce-product-dependencies', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Validates a product when adding to cart.
	 *
	 * @param  boolean  $add
	 * @param  int      $item_id
	 * @param  int      $quantity
	 * @return boolean
	 */
	public function add_to_cart_validation( $add, $item_id, $quantity ) {

		return $add && $this->evaluate_dependencies( $item_id );
	}

	/**
	 * Validates cart contents.
	 */
	public function check_cart_items() {

		$cart_items = WC()->cart->cart_contents;

		foreach ( $cart_items as $cart_item ) {

			$product = $cart_item[ 'data' ];

			$this->evaluate_dependencies( $product );
		}
	}

	/**
	 * Check conditions.
	 *
	 * @param  mixed  $item
	 * @return boolean
	 */
	public function evaluate_dependencies( $item ) {

		if ( is_a( $item, 'WC_Product' ) ) {
			$product    = $item;
			$product_id = $product->is_type( 'variation' ) ? WC_PD_Core_Compatibility::get_parent_id( $product ) : WC_PD_Core_Compatibility::get_id( $product );
		} else {
			$product_id = absint( $item );
			$product    = wc_get_product( $product_id );
		}

		if ( ! $product ) {
			return;
		}

		if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {
			$tied_product_ids = $product->get_meta( '_tied_products', true );
			$dependency_type  = absint( $product->get_meta( '_dependency_type', true ) );
		} else {
			$tied_product_ids = (array) get_post_meta( $product_id, '_tied_products', true );
			$dependency_type  = absint( get_post_meta( $product_id, '_dependency_type', true ) );
		}

		$product_title = $product->get_title();
		$tied_products = array();

		// Ensure dependencies exist and are purchasable.
		if ( ! empty( $tied_product_ids ) ) {
			foreach ( $tied_product_ids as $id ) {
				$tied_product = wc_get_product( $id );
				if ( $tied_product && $tied_product->is_purchasable() ) {
					$tied_products[ $id ] = $tied_product;
				}
			}
		}

		if ( ! empty( $tied_products ) ) {

			$tied_product_ids = array_keys( $tied_products );

			// Check cart.
			if ( $dependency_type === 2 || $dependency_type === 3 ) {

				$cart_contents = WC()->cart->cart_contents;

				foreach ( $cart_contents as $cart_item ) {
					$product_id   = $cart_item[ 'product_id' ];
					$variation_id = $cart_item[ 'variation_id' ];
					if ( in_array( $product_id, $tied_product_ids ) || in_array( $variation_id, $tied_product_ids ) ) {
						return true;
					}
				}
			}

			// Check ownership.
			if ( is_user_logged_in() && ( $dependency_type === 1 || $dependency_type === 3 ) ) {

				$current_user = wp_get_current_user();
				$is_owner     = false;

				foreach ( $tied_product_ids as $id ) {
					if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $id ) ) {
						$is_owner = true;
					}
				}

				if ( ! $is_owner ) {

					$merged_titles = WC_PD_Helpers::merge_product_titles( $tied_products );

					if ( $dependency_type === 1 ) {
						wc_add_notice( sprintf( __( 'Access to &quot;%2$s&quot; is restricted only to verified owners of %1$s.', 'woocommerce-product-dependencies' ), $merged_titles, $product_title ), 'error' );
					} else {
						wc_add_notice( sprintf( __( 'Access to &quot;%2$s&quot; is restricted only to verified owners of %1$s. Alternatively, access to this item will be granted after adding a %1$s to the cart.', 'woocommerce-product-dependencies' ), $merged_titles, $product_title ), 'error' );
					}
					return false;
				}

			} else {

				$merged_titles = WC_PD_Helpers::merge_product_titles( $tied_products );

				$msg = '';

				if ( $dependency_type === 1 ) {
					$msg = __( 'Access to &quot;%2$s&quot; is restricted only to verified owners of %1$s. The verification is automatic and simply requires you to be <a href="%3$s">logged in</a>.', 'woocommerce-product-dependencies' );
				} elseif ( $dependency_type === 2 ) {
					$msg = __( '&quot;%2$s&quot; can be purchased only in combination with %1$s. Access to this item will be granted after adding a %1$s to the cart.', 'woocommerce-product-dependencies' );
				} else {
					$msg = __( '&quot;%2$s&quot; requires the purchase of %1$s. Ownership can be verified by simply <a href="%3$s">logging in</a>. Alternatively, access to this item will be granted after adding a %1$s to the cart.', 'woocommerce-product-dependencies' );
				}

				wc_add_notice( sprintf( $msg, $merged_titles, $product_title, wp_login_url() ), 'error' );

				return false;
			}
		}

		return true;
	}

	/*
	|--------------------------------------------------------------------------
	| Admin Filters.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Add Product Data tab.
	 *
	 * @return void
	 */
	public function dependencies_product_data_panel_tab() {
		echo '<li class="tied_products_tab related_product_options linked_product_options"><a href="#tied_products_data">' . __( 'Dependencies', 'woocommerce-product-dependencies' ) . '</a></li>';
	}

	/**
	 * Add the "Bundled Products" panel tab.
	 * @param  array  $tabs
	 * @return array
	 */
	public static function dependencies_product_data_tab( $tabs ) {

		$tabs[ 'dependencies' ] = array(
			'label'  => __( 'Dependencies', 'woocommerce-product-dependencies' ),
			'target' => 'tied_products_data',
			'class'  => array( 'show_if_simple', 'show_if_variable', 'show_if_bundle', 'show_if_composite', 'linked_product_options' )
		);

		return $tabs;
	}

	/**
	 * Add Product Data tab section.
	 *
	 * @return void
	 */
	public function dependencies_product_data_panel() {

		global $post, $product_object;

		if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {
			$tied_products   = $product_object->get_meta( '_tied_products', true );
			$dependency_type = $product_object->get_meta( '_dependency_type', true );
		} else {
			$tied_products   = get_post_meta( $post->ID, '_tied_products', true );
			$dependency_type = get_post_meta( $post->ID, '_dependency_type', true );
		}

		if ( ! $dependency_type ) {
			$dependency_type = 3;
		}

		$product_id_options = array();

		if ( $tied_products ) {
			foreach ( $tied_products as $item_id ) {

				$title = WC_PD_Helpers::get_product_title( $item_id );

				if ( $title ) {
					$product_id_options[ $item_id ] = $title;
				}
			}
		}

		?>
		<div id="tied_products_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">

			<p class="form-field">
				<label>
					<?php _e( 'Product Dependencies', 'woocommerce-product-dependencies' ); ?>
				</label><?php

				if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {

					?><select id="tied_products" name="tied_products[]" class="wc-product-search" multiple="multiple" style="width: 75%;" data-limit="500" data-action="woocommerce_json_search_products_and_variations" data-placeholder="<?php echo  __( 'Search for products and variations&hellip;', 'woocommerce-product-dependencies' ); ?>"><?php

						if ( ! empty( $product_id_options ) ) {

							foreach ( $product_id_options as $product_id => $product_name ) {
								echo '<option value="' . $product_id . '" selected="selected">' . $product_name . '</option>';
							}
						}

					?></select><?php

				} elseif ( WC_PD_Core_Compatibility::is_wc_version_gte_2_3() ) {

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

				echo WC_PD_Core_Compatibility::wc_help_tip( __( 'Restrict product access based on the ownership or purchase of <strong>any</strong> product or variation added to this list.', 'woocommerce-product-dependencies' ) );

				?>
			</p>
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
	 * Save dependencies data. WC >= 2.7.
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public function process_product_data( $product ) {

		if ( ! isset( $_POST[ 'tied_products' ] ) || empty( $_POST[ 'tied_products' ] ) ) {

			$product->delete_meta_data( '_tied_products' );

		} elseif ( isset( $_POST[ 'tied_products' ] ) && ! empty( $_POST[ 'tied_products' ] ) ) {

			$tied_ids = $_POST[ 'tied_products' ];

			if ( is_array( $tied_ids ) ) {
				$tied_ids = array_map( 'intval', $tied_ids );
			} else {
				$tied_ids = array_filter( array_map( 'intval', explode( ',', $tied_ids ) ) );
			}

			$product->add_meta_data( '_tied_products', $tied_ids, true );
		}

		if ( isset( $_POST[ 'dependency_type' ] ) && ! empty( $_POST[ 'dependency_type' ] ) ) {
			$product->add_meta_data( '_dependency_type', stripslashes( $_POST[ 'dependency_type' ] ), true );
		}
	}

	/**
	 * Save dependencies meta. WC <= 2.6.
	 *
	 * @param  int      $post_id
	 * @param  WC_Post  $post
	 * @return void
	 */
	public function process_meta( $post_id, $post ) {

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

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Check conditions.
	 *
	 * @deprecated  1.1.0
	 *
	 * @param  int  $item_id
	 * @return boolean
	 */
	public function woo_tied_evaluate_access( $id ) {
		_deprecated_function( __METHOD__ . '()', '1.1.0', __CLASS__ . '::evaluate_access()' );
		return WC_Product_Dependencies()->evaluate_dependencies( $id );
	}
}

/**
 * Returns the main instance of WC_Product_Dependencies to prevent the need to use globals.
 *
 * @since  1.1.0
 * @return WC_Product_Dependencies
 */
function WC_Product_Dependencies() {
  return WC_Product_Dependencies::instance();
}

// Backwards compatibility with v1.0.
$GLOBALS[ 'woocommerce_restricted_access' ] = WC_Product_Dependencies();
