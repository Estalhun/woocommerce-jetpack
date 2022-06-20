<?php
/**
 * Booster for WooCommerce - Price By Country - Group Generator
 *
 * @version 5.5.9
 * @author  Pluggabl LLC.
 * @package Booster_For_WooCommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCJ_Price_By_Country_Group_Generator' ) ) :
		/**
		 * WCJ_Price_By_Country_Group_Generator.
		 *
		 * @version 3.9.0
		 */
	class WCJ_Price_By_Country_Group_Generator {

		/**
		 * Constructor.
		 *
		 * @version 3.9.0
		 */
		public function __construct() {
			require_once 'wcj-country-currency.php';
			add_action( 'admin_init', array( $this, 'create_all_countries_groups' ) );
			add_action( 'admin_notices', array( $this, 'create_all_countries_groups_notices' ) );
		}

		/**
		 * Create_all_countries_groups_notices.
		 *
		 * @version 3.9.0
		 * @since   3.9.0
		 */
		public function create_all_countries_groups_notices() {
			$nonce = wp_create_nonce();
			if ( isset( $_GET['wcj_generate_country_groups_finished'] ) && wp_verify_nonce( $nonce ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . wp_kses_post( 'Country groups successfully generated.', 'woocommerce-jetpack' ) . '</p></div>';
			}
			if ( isset( $_GET['wcj_generate_country_groups_error'] ) && wp_verify_nonce( $nonce ) ) {
				echo '<div class="notice notice-error"><p>' . wp_kses_post( 'Country groups generation failed.', 'woocommerce-jetpack' ) . '</p></div>';
			}
		}

		/**
		 * Get_currency_countries.
		 *
		 * @version 3.9.0
		 * @param array $limit_currencies Get limits of currencies.
		 */
		public function get_currency_countries( $limit_currencies = '' ) {
			if ( 'paypal_only' === $limit_currencies ) {
				$default_currency            = get_woocommerce_currency();
				$paypal_supported_currencies = wcj_get_paypal_supported_currencies();
			}
			$country_currency = wcj_get_country_currency();
			$currencies       = array();
			foreach ( $country_currency as $country => $currency ) {
				if ( 'paypal_only' === $limit_currencies ) {
					if ( ! in_array( $currency, $paypal_supported_currencies, true ) ) {
						$currency = $default_currency;
					}
				}
				$currencies[ $currency ][] = $country;
			}
			return $currencies;
		}

		/**
		 * Create_all_countries_groups.
		 *
		 * @version 5.5.9
		 * @todo    add nonce verification
		 */
		public function create_all_countries_groups() {
			// Verification.
			$nonce = wp_create_nonce();
			if ( ! isset( $_GET['wcj_generate_country_groups'] ) && wp_verify_nonce( $nonce ) ) {
				return;
			}
			if ( isset( $_POST['save'] ) && wp_verify_nonce( $nonce ) ) {
				return;
			}
			if ( ! wcj_is_user_role( 'administrator' ) || 1 === apply_filters( 'booster_option', 1, '' ) ) {
				wp_safe_redirect( add_query_arg( 'wcj_generate_country_groups_error', true, esc_url( remove_query_arg( 'wcj_generate_country_groups' ) ) ) );
				exit;
			}
			// Generation.
			$currencies       = $this->get_currency_countries( isset( $_GET['wcj_generate_country_groups'] ) );
			$number_of_groups = count( $currencies );
			update_option( 'wcj_price_by_country_total_groups_number', $number_of_groups );
			$i = 0;
			foreach ( $currencies as $group_currency => $countries ) {
				$i++;
				switch ( wcj_get_option( 'wcj_price_by_country_selection', 'comma_list' ) ) {
					case 'comma_list':
						update_option( 'wcj_price_by_country_exchange_rate_countries_group_' . $i, implode( ',', $countries ) );
						break;
					case 'multiselect':
						update_option( 'wcj_price_by_country_countries_group_' . $i, $countries );
						break;
					case 'chosen_select':
						update_option( 'wcj_price_by_country_countries_group_chosen_select_' . $i, $countries );
						break;
				}
				update_option( 'wcj_price_by_country_exchange_rate_currency_group_' . $i, $group_currency );
				update_option( 'wcj_price_by_country_exchange_rate_group_' . $i, 1 );
				update_option( 'wcj_price_by_country_make_empty_price_group_' . $i, 'no' );
			}
			wp_safe_redirect( add_query_arg( 'wcj_generate_country_groups_finished', true, esc_url( remove_query_arg( 'wcj_generate_country_groups' ) ) ) );
			exit;
		}

	}

endif;

return new WCJ_Price_By_Country_Group_Generator();
