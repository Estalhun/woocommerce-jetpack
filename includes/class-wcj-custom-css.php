<?php
/**
 * WooCommerce Jetpack Custom CSS
 *
 * The WooCommerce Jetpack Custom CSS class.
 *
 * @version 2.8.0
 * @since   2.7.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCJ_Custom_CSS' ) ) :

class WCJ_Custom_CSS extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.8.0
	 * @since   2.7.0
	 * @todo    wp_safe_redirect after saving settings
	 * @todo    automatically enable the module if v <= 2.6.0 and General module enabled and `wcj_general_custom_css` or `wcj_general_custom_admin_css` are not empty
	 * @todo    (maybe) set `add_action` `priority` to `PHP_INT_MAX`
	 */
	function __construct() {

		$this->id         = 'custom_css';
		$this->short_desc = __( 'Custom CSS', 'woocommerce-jetpack' );
		$this->desc       = __( 'Separate custom CSS for front and back end.', 'woocommerce-jetpack' );
//		$this->desc       = __( 'Another custom CSS, if you need one.', 'woocommerce-jetpack' );
		$this->link       = 'http://booster.io/features/woocommerce-booster-custom-css/';
		parent::__construct();

		if ( $this->is_enabled() ) {
			if ( '' != get_option( 'wcj_general_custom_css', '' ) ) {
				add_action( 'wp_head', array( $this, 'hook_custom_css' ) );
			}
			if ( '' != get_option( 'wcj_general_custom_admin_css', '' ) ) {
				add_action( 'admin_head', array( $this, 'hook_custom_admin_css' ) );
			}
		}
	}

	/**
	 * hook_custom_css.
	 *
	 * @version 2.7.0
	 */
	function hook_custom_css() {
		echo '<style>' . get_option( 'wcj_general_custom_css', '' ) . '</style>';
	}

	/**
	 * hook_custom_admin_css.
	 *
	 * @version 2.7.0
	 */
	function hook_custom_admin_css() {
		echo '<style>' . get_option( 'wcj_general_custom_admin_css', '' ) . '</style>';
	}

}

endif;

return new WCJ_Custom_CSS();
