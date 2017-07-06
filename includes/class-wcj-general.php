<?php
/**
 * Booster for WooCommerce - Module - General
 *
 * @version 2.9.1
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCJ_General' ) ) :

class WCJ_General extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.9.1
	 */
	function __construct() {

		$this->id         = 'general';
		$this->short_desc = __( 'General', 'woocommerce-jetpack' );
		$this->desc       = __( 'Custom roles tool. Shortcodes in WordPress text widgets.', 'woocommerce-jetpack' );
		$this->link_slug  = 'woocommerce-booster-general-tools';
		parent::__construct();

		$this->add_tools( array(
			'products_atts'    => array(
				'title'     => __( 'Products Attributes', 'woocommerce-jetpack' ),
				'desc'      => __( 'All Products and All Attributes.', 'woocommerce-jetpack' ),
			),
			'custom_roles' => array(
				'title'     => __( 'Add/Manage Custom Roles', 'woocommerce-jetpack' ),
				'tab_title' => __( 'Custom Roles', 'woocommerce-jetpack' ),
				'desc'      => __( 'Manage Custom Roles.', 'woocommerce-jetpack' ),
			),
		) );

		if ( $this->is_enabled() ) {

			// Recalculate cart totals
			if ( 'yes' === get_option( 'wcj_general_advanced_recalculate_cart_totals', 'no' ) ) {
				add_action( 'wp_loaded', array( $this, 'fix_mini_cart' ), PHP_INT_MAX );
			}

			// Product revisions
			if ( 'yes' === get_option( 'wcj_product_revisions_enabled', 'no' ) ) {
				add_filter( 'woocommerce_register_post_type_product', array( $this, 'enable_product_revisions' ) );
			}

			// Shortcodes in text widgets
			if ( 'yes' === get_option( 'wcj_general_shortcodes_in_text_widgets_enabled' ) ) {
				add_filter( 'widget_text', 'do_shortcode' );
			}

			// PayPal email per product
			if ( 'yes' === get_option( 'wcj_paypal_email_per_product_enabled', 'no' ) ) {
				add_action( 'add_meta_boxes',    array( $this, 'add_meta_box' ) );
				add_action( 'save_post_product', array( $this, 'save_meta_box' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_payment_gateways', array( $this, 'maybe_change_paypal_email' ) );
			}

			// Session expiration
			if ( 'yes' === get_option( 'wcj_session_expiration_section_enabled', 'no' ) ) {
				add_filter( 'wc_session_expiring',   array( $this, 'change_session_expiring' ),   PHP_INT_MAX );
				add_filter( 'wc_session_expiration', array( $this, 'change_session_expiration' ), PHP_INT_MAX );
			}

			// Booster role user changer
			if ( wcj_is_booster_role_changer_enabled() ) {
				add_action( 'admin_bar_menu', array( $this, 'add_user_role_changer' ), PHP_INT_MAX );
				add_action( 'init',           array( $this, 'change_user_role_meta' ) );
			}

			// Track users
			if ( 'yes' === get_option( 'wcj_track_users_enabled', 'no' ) ) {
				add_action( 'init',               array( $this, 'track_users' ) );
				add_action( 'wp_dashboard_setup', array( $this, 'add_track_users_dashboard_widget' ) );
				add_action( 'admin_init',         array( $this, 'maybe_delete_track_users_stats' ) );
			}
		}
	}

	/**
	 * maybe_delete_track_users_stats.
	 *
	 * @version 2.9.1
	 * @since   2.9.1
	 * @todo    (maybe) wp_nonce
	 */
	function maybe_delete_track_users_stats() {
		if ( isset( $_GET['wcj_delete_track_users_stats'] ) /* && is_super_admin() */ ) {
			delete_option( 'wcj_track_users_stats_by_country' );
//			delete_option( 'wcj_track_users_stats_by_country_by_day' );
			if ( wp_safe_redirect( remove_query_arg( 'wcj_delete_track_users_stats' ) ) ) {
				exit;
			}
		}
	}

	/**
	 * Add a widget to the dashboard.
	 *
	 * @version 2.9.1
	 * @since   2.9.1
	 */
	function add_track_users_dashboard_widget() {
		wp_add_dashboard_widget(
			'wcj_track_users_dashboard_widget',                     // Widget slug.
			__( 'Users by Country', 'woocommerce-jetpack' ),        // Title.
			array( $this, 'track_users_dashboard_widget_function' ) // Display function.
		);
	}

	/**
	 * Create the function to output the contents of our Dashboard Widget.
	 *
	 * @version 2.9.1
	 * @since   2.9.1
	 * @todo    display stats by state
	 */
	function track_users_dashboard_widget_function() {
		// By day
		/* $stats = get_option( 'wcj_track_users_stats_by_country_by_day', array() );
		$totals = array();
		foreach ( $stats as $day => $country ) {
			foreach ( $country as $country_code => $stats_by_state ) {
				if ( ! isset( $totals[ $country_code ] ) ) {
					$totals[ $country_code ] = 0;
				}
				$totals[ $country_code ] += array_sum( $stats_by_state );
			}
		}
		$table_data = array();
		foreach ( $totals as $country_code => $visits ) {
			$country_info = ( '' != $country_code ? wcj_get_country_flag_by_code( $country_code ) . ' ' . wcj_get_country_name_by_code( $country_code ) : 'N/A' );
			$table_data[] = array( $country_info, $visits );
		}
		echo wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'none' ) ); */
		// Total
		$stats = get_option( 'wcj_track_users_stats_by_country', array() );
		if ( ! empty( $stats ) ) {
			$totals = array();
			foreach ( $stats as $country_code => $stats_by_state ) {
				$totals[ $country_code ] = array_sum( $stats_by_state );
			}
			arsort( $totals );
			$table_data = array();
			$table_data[] = array( __( 'Country', 'woocommerce-jetpack' ), __( 'Visits', 'woocommerce-jetpack' ) );
			foreach ( $totals as $country_code => $visits ) {
				$country_info = ( '' != $country_code ? wcj_get_country_flag_by_code( $country_code ) . ' ' . wcj_get_country_name_by_code( $country_code ) : 'N/A' );
				$table_data[] = array( $country_info, $visits );
			}
			echo wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'horizontal' ) );
			echo '<p>' .
				'<a href="' . add_query_arg( 'wcj_delete_track_users_stats', '1' ) . '" ' .
					'onclick="return confirm(\'' . __( 'Are you sure?', 'woocommerce-jetpack' ) . '\')"' .
				'>' . __( 'Delete stats', 'woocommerce-jetpack' ) . '</a>' .
			'</p>';
		} else {
			echo '<p>' .
				'<em>' . __( 'No stats yet.', 'woocommerce-jetpack' ) . '</em>' .
			'</p>';
		}
	}

	/**
	 * track_users.
	 *
	 * @version 2.9.1
	 * @since   2.9.1
	 * @todo    optionally do not track selected user roles (e.g. admin)
	 * @todo    (maybe) stats - by day
	 * @todo    (maybe) stats - by month
	 * @todo    (maybe) make new function for geolocation
	 */
	function track_users() {
		// Country by IP
		if ( class_exists( 'WC_Geolocation' ) ) {
			$location = WC_Geolocation::geolocate_ip();
		} else {
			$location = array( 'country' => '', 'state' => '' );
		}
		// Saving stats by country - by day
		/* $date = date( 'Y-m-d', current_time( 'timestamp' ) );
		$stats_by_day = get_option( 'wcj_track_users_stats_by_country_by_day', array() );
		if ( ! isset( $stats_by_day[ $date ][ $location['country'] ][ $location['state'] ] ) ) {
			$stats_by_day[ $date ][ $location['country'] ][ $location['state'] ] = 0;
		}
		$stats_by_day[ $date ][ $location['country'] ][ $location['state'] ]++;
		update_option( 'wcj_track_users_stats_by_country_by_day', $stats_by_day ); */
		// Saving stats by country - total
		$stats = get_option( 'wcj_track_users_stats_by_country', array() );
		if ( ! isset( $stats[ $location['country'] ][ $location['state'] ] ) ) {
			$stats[ $location['country'] ][ $location['state'] ] = 0;
		}
		$stats[ $location['country'] ][ $location['state'] ]++;
		update_option( 'wcj_track_users_stats_by_country', $stats );
	}

	/**
	 * change_user_role_meta.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 * @todo    (maybe) optionally via cookies
	 */
	function change_user_role_meta() {
		if ( isset( $_GET['wcj_booster_user_role'] ) ) {
			$current_user_id = get_current_user_id();
			update_user_meta( $current_user_id, '_' . 'wcj_booster_user_role', $_GET['wcj_booster_user_role'] );
		}
	}

	/**
	 * add_user_role_changer.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 */
	function add_user_role_changer( $wp_admin_bar ) {
		$current_user_id  = get_current_user_id();
		$user_roles       = wcj_get_user_roles_options();
		if ( '' != ( $current_booster_user_role = get_user_meta( $current_user_id, '_' . 'wcj_booster_user_role', true ) ) ) {
			$current_booster_user_role = ( isset( $user_roles[ $current_booster_user_role ] ) ) ? $user_roles[ $current_booster_user_role ] : $current_booster_user_role;
			$current_booster_user_role = ' [' . $current_booster_user_role . ']';
		}
		$args = array(
			'parent' => false,
			'id'     => 'booster-user-role-changer',
			'title'  => __( 'Booster User Role', 'woocommerce-jetpack' ) . $current_booster_user_role,
			'href'   => false,
		);
		$wp_admin_bar->add_node( $args );
		foreach ( $user_roles as $user_role_key => $user_role_name ) {
			$args = array(
				'parent' => 'booster-user-role-changer',
				'id'     => 'booster-user-role-changer-role-' . $user_role_key,
				'title'  => $user_role_name,
				'href'   => add_query_arg( 'wcj_booster_user_role', $user_role_key ),
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	/**
	 * fix_mini_cart.
	 *
	 * @version 2.5.2
	 * @since   2.5.2
	 * @todo    this is only temporary solution!
	 */
	function fix_mini_cart() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			if ( null !== ( $wc = WC() ) ) {
				if ( isset( $wc->cart ) ) {
					$wc->cart->calculate_totals();
				}
			}
		}
	}

	/**
	 * change_session_expiring.
	 *
	 * @version 2.5.7
	 * @since   2.5.7
	 */
	function change_session_expiring( $the_time ) {
		return get_option( 'wcj_session_expiring', 47 * 60 * 60 );
	}

	/**
	 * change_session_expiration.
	 *
	 * @version 2.5.7
	 * @since   2.5.7
	 */
	function change_session_expiration( $the_time ) {
		return get_option( 'wcj_session_expiration', 48 * 60 * 60 );
	}

	/**
	 * create_custom_roles_tool.
	 *
	 * @version 2.5.3
	 * @since   2.5.3
	 */
	function create_custom_roles_tool() {
		if ( isset( $_POST['wcj_add_new_role'] ) ) {
			if (
				! isset( $_POST['wcj_custom_role_id'] )   || '' == $_POST['wcj_custom_role_id'] ||
				! isset( $_POST['wcj_custom_role_name'] ) || '' == $_POST['wcj_custom_role_name']
			) {
				echo '<p style="color:red;font-weight:bold;">' . __( 'Both fields are required!', 'woocommerce-jetpack') . '</p>';
			} else {
				if ( is_numeric( $_POST['wcj_custom_role_id'] ) ) {
					echo '<p style="color:red;font-weight:bold;">' . __( 'Role ID must not be numbers only!', 'woocommerce-jetpack') . '</p>';
				} else {
					$result = add_role( $_POST['wcj_custom_role_id'], $_POST['wcj_custom_role_name'] );
					if ( null !== $result ) {
						echo '<p style="color:green;font-weight:bold;">' . __( 'Role successfully added!', 'woocommerce-jetpack') . '</p>';
					} else {
						echo '<p style="color:red;font-weight:bold;">' . __( 'Role already exists!', 'woocommerce-jetpack') . '</p>';
					}
				}
			}
		}

		if ( isset( $_GET['wcj_delete_role'] ) && '' != $_GET['wcj_delete_role'] ) {
			remove_role( $_GET['wcj_delete_role'] );
			echo '<p style="color:green;font-weight:bold;">' . sprintf( __( 'Role %s successfully deleted!', 'woocommerce-jetpack'), $_GET['wcj_delete_role'] ) . '</p>';
		}

		echo $this->get_tool_header_html( 'custom_roles' );

		$table_data = array();
		$table_data[] = array( __( 'ID', 'woocommerce-jetpack'), __( 'Name', 'woocommerce-jetpack'), __( 'Actions', 'woocommerce-jetpack'), );
		$existing_roles = wcj_get_user_roles();
		$default_wp_wc_roles = array( 'guest', 'administrator', 'editor', 'author', 'contributor', 'subscriber', 'customer', 'shop_manager', );
		foreach ( $existing_roles as $role_key => $role_data ) {
			$delete_html = ( in_array( $role_key, $default_wp_wc_roles ) )
				? ''
				: '<a href="' . add_query_arg( 'wcj_delete_role', $role_key ). '">' . __( 'Delete', 'woocommerce-jetpack') . '</a>';
			$table_data[] = array( $role_key, $role_data['name'], $delete_html );
		}
		echo '<h3>' . __( 'Existing Roles', 'woocommerce-jetpack') . '</h3>';
		echo wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped' ) );

		$table_data = array();
		$table_data[] = array( __( 'ID', 'woocommerce-jetpack'),   '<input type="text" name="wcj_custom_role_id">' );
		$table_data[] = array( __( 'Name', 'woocommerce-jetpack'), '<input type="text" name="wcj_custom_role_name">' );
		echo '<h3>' . __( 'Add New Role', 'woocommerce-jetpack') . '</h3>';
		echo '<form method="post" action="' . remove_query_arg( 'wcj_delete_role' ) . '">' .
			wcj_get_table_html( $table_data, array( 'table_class' => 'widefat', 'table_heading_type' => 'vertical', 'table_style' => 'width:20%;min-width:300px;', ) )
			. '<p>' . '<input type="submit" name="wcj_add_new_role" class="button-primary" value="' . __( 'Add', 'woocommerce-jetpack' ) . '">' . '</p>'
			. '</form>';
	}

	/**
	 * maybe_change_paypal_email.
	 *
	 * @version 2.5.2
	 * @since   2.5.2
	 */
	function maybe_change_paypal_email( $load_gateways ) {
		if ( isset( WC()->cart ) ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				if ( '' != ( $email = get_post_meta( $values['product_id'], '_' . 'wcj_paypal_per_product_email', true ) ) ) {
					foreach ( $load_gateways as $key => $gateway ) {
						if ( is_string( $gateway ) && 'WC_Gateway_Paypal' === $gateway ) {
							$load_gateway = new $gateway();
							$load_gateway->receiver_email = $load_gateway->email = $load_gateway->settings['receiver_email'] = $load_gateway->settings['email'] = $email;
							$load_gateways[ $key ] = $load_gateway;
						}
					}
					break;
				}
			}
		}
		return $load_gateways;
	}

	/**
	 * enable_product_revisions.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 */
	function enable_product_revisions( $args ) {
		$args['supports'][] = 'revisions';
		return $args;
	}

	/**
	 * create_products_atts_tool.
	 *
	 * @version 2.3.9
	 * @since   2.3.9
	 */
	function create_products_atts_tool() {
		$html = '';
		$html .= $this->get_products_atts();
		echo $html;
	}

	/*
	 * get_products_atts.
	 *
	 * @version 2.7.0
	 * @since   2.3.9
	 */
	function get_products_atts() {

		$total_products = 0;

		$products_attributes = array();
		$attributes_names = array();
		$attributes_names['wcj_title']    = __( 'Product', 'woocommerce-jetpack' );
		$attributes_names['wcj_category'] = __( 'Category', 'woocommerce-jetpack' );

		$offset = 0;
		$block_size = 96;
		while( true ) {

			$args_products = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $block_size,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'offset'         => $offset,
			);
			$loop_products = new WP_Query( $args_products );
			if ( ! $loop_products->have_posts() ) break;
			while ( $loop_products->have_posts() ) : $loop_products->the_post();

				$total_products++;
				$product_id = $loop_products->post->ID;
				$the_product = wc_get_product( $product_id );

				$products_attributes[ $product_id ]['wcj_title']    = '<a href="' . get_permalink( $product_id ) . '">' . $the_product->get_title() . '</a>';
				$products_attributes[ $product_id ]['wcj_category'] = ( WCJ_IS_WC_VERSION_BELOW_3 ? $the_product->get_categories() : wc_get_product_category_list( $product_id ) );

				foreach ( $the_product->get_attributes() as $attribute ) {
					$products_attributes[ $product_id ][ $attribute['name'] ] = $the_product->get_attribute( $attribute['name'] );
					if ( ! isset( $attributes_names[ $attribute['name'] ] ) ) {
						$attributes_names[ $attribute['name'] ] = wc_attribute_label( $attribute['name'] );
					}
				}

			endwhile;

			$offset += $block_size;

		}

		$table_data = array();
		if ( isset( $_GET['wcj_attribute'] ) && '' != $_GET['wcj_attribute'] ) {
			$table_data[] = array(
				__( 'Product', 'woocommerce-jetpack' ),
				__( 'Category', 'woocommerce-jetpack' ),
				$_GET['wcj_attribute'],
			);
		} else {
//			$table_data[] = array_values( $attributes_names );
			$table_data[] = array_keys( $attributes_names );
		}
		foreach ( $attributes_names as $attributes_name => $attribute_title ) {

			if ( isset( $_GET['wcj_attribute'] ) && '' != $_GET['wcj_attribute'] ) {
				if ( 'wcj_title' != $attributes_name && 'wcj_category' != $attributes_name && $_GET['wcj_attribute'] != $attributes_name ) {
					continue;
				}
			}

			foreach ( $products_attributes as $product_id => $product_attributes ) {
				$table_data[ $product_id ][ $attributes_name ] = isset( $product_attributes[ $attributes_name ] ) ? $product_attributes[ $attributes_name ] : '';
			}
		}

		return '<p>' . __( 'Total Products:', 'woocommerce-jetpack' ) . ' ' . $total_products . '</p>' . wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped' ) );
	}

}

endif;

return new WCJ_General();
