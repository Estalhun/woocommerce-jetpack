<?php
/**
 * Booster for WooCommerce - Module - Add to Cart
 *
 * @version 2.8.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCJ_Add_To_Cart' ) ) :

class WCJ_Add_To_Cart extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.4.6
	 */
	function __construct() {

		$this->id         = 'add_to_cart';
		$this->short_desc = __( 'Add to Cart Labels', 'woocommerce-jetpack' );
		$this->desc       = __( 'Change text for Add to Cart button by WooCommerce product type, by product category or for individual products.', 'woocommerce-jetpack' );
		$this->link       = 'http://booster.io/features/woocommerce-add-to-cart-labels/';
		parent::__construct();

		if ( $this->is_enabled() ) {
			include_once( 'add-to-cart/class-wcj-add-to-cart-per-category.php' );
			include_once( 'add-to-cart/class-wcj-add-to-cart-per-product.php' );
			include_once( 'add-to-cart/class-wcj-add-to-cart-per-product-type.php' );
		}
	}

}

endif;

return new WCJ_Add_To_Cart();
