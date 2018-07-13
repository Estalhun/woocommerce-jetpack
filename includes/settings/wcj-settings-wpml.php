<?php
/**
 * Booster for WooCommerce - Settings - WPML
 *
 * @version 3.7.1
 * @since   2.8.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$all_modules = array();
if ( function_exists( 'WCJ' ) && ! empty( WCJ()->modules ) ) {
	foreach ( WCJ()->modules as $module_key => $module ) {
		$desc_prefix = ( false !== strpos( $module_key, 'pdf_invoicing_' ) ? __( 'PDF Invoicing', 'woocommerce-jetpack' ) . ': ' : '' );
		$all_modules[ $module_key ] = $desc_prefix . $module->short_desc;
	}
}

$settings = array(
	array(
		'title'    => __( 'General Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_wpml_general_options',
	),
	array(
		'title'    => __( 'Use Translation Product IDs', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'type'     => 'checkbox',
		'id'       => 'wcj_wpml_use_translation_product_id',
		'default'  => 'yes',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_wpml_general_options',
	),
	array(
		'title'    => __( 'WPML Language Configuration File Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'desc'     => sprintf( __( 'Options for regenerating %s file.', 'woocommerce-jetpack' ), '<code>wpml-config.xml</code>' ),
		'id'       => 'wcj_wpml_config_xml_options',
	),
	array(
		'title'    => __( 'Automatically Regenerate', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'desc_tip' => sprintf( __( 'Automatically regenerate %s file on each Booster version update.', 'woocommerce-jetpack' ), '<code>wpml-config.xml</code>' ),
		'type'     => 'checkbox',
		'id'       => 'wcj_wpml_config_xml_auto_regenerate',
		'default'  => 'no',
	),
	array(
		'title'    => __( 'Modules to Skip', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Select modules, which options you wish to exclude from wpml-config.xml file.', 'woocommerce-jetpack' ),
		'type'     => 'multiselect',
		'class'    => 'chosen_select',
		'id'       => 'wcj_wpml_config_xml_modules_to_skip',
		'options'  => $all_modules,
		'default'  => $this->get_default_modules_to_skip(),
	),
	array(
		'title'    => __( 'Option IDs to Skip', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Select options, which you wish to exclude from wpml-config.xml file.', 'woocommerce-jetpack' ),
		'desc'     => sprintf( __( 'Full or part of option ID. Separated by vertical bar %s.', 'woocommerce-jetpack' ), '( | )' ),
		'type'     => 'textarea',
		'id'       => 'wcj_wpml_config_xml_values_to_skip',
		'default'  => $this->get_default_values_to_skip(),
		'css'      => 'width:100%;',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_wpml_config_xml_options',
	),
	array(
		'title'    => __( 'Tools', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_' . $this->id . '_tools_options',
	),
	array(
		'title'    => __( 'Module Tools', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'To use tools, module must be enabled.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_module_tools',
		'type'     => 'custom_link',
		'link'     => ( $this->is_enabled() ) ?
			'<code>' . '<a href="' . add_query_arg( 'create_wpml_xml_file', '1' ) . '">' .
				__( 'Regenerate wpml-config.xml file', 'woocommerce-jetpack' ) . '</a>' . '</code>' .
				'<pre>' . $this->notice . '</pre>' :
			'<code>' . __( 'Regenerate wpml-config.xml file', 'woocommerce-jetpack' ) . '</code>',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_' . $this->id . '_tools_options',
	),
);
$this->notice = '';
return $settings;
