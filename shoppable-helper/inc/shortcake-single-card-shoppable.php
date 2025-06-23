<?php

/**
 * Gets the attributes for the Shoppable Single Card shortcode UI.
 *
 * @return array
 */
function tmbi_shooppable_product_card_attributes() {
	$attrs = array(
		array(
			'label'       => 'Product UPC',
			'attr'        => 'upc',
			'type'        => 'text',
			'description' => 'E.g. 885911113618 (do not use zeros on the left)',
		),
		array(
			'label'       => 'Add Image from library',
			'attr'        => 'image_id',
			'type'        => 'attachment',
			'description' => 'this will override the shoppable image',
		),
		array(
			'label' => 'Product Title',
			'attr'  => 'title',
			'type'  => 'text',
		),
		array(
			'label' => 'Price',
			'attr'  => 'price',
			'type'  => 'number',
		),
		array(
			'label' => 'Button 1 Text',
			'attr'  => 'button1_text',
			'type'  => 'text',
			'value' => 'Add to Cart',
		),
		array(
			'label'   => 'Hide Button 1?',
			'attr'    => 'hide_button1',
			'type'    => 'select',
			'options' => array(
				'no'  => 'No',
				'yes' => 'Yes',
			),
		),
		array(
			'label'   => 'Product Detail Overlay?',
			'attr'    => 'button1_pdp',
			'type'    => 'select',
			'options' => array(
				'true'  => 'Yes',
                'false' => 'No',
			),
		),
		array(
			'label'   => 'Variation product?',
			'attr'    => 'variation',
			'type'    => 'select',
			'options' => array(
				'false' => 'No',
				'true'  => 'Yes',
			),
		),
		array(
			'label' => 'Button 2 Text',
			'attr'  => 'button2_text',
			'type'  => 'text',
			'value' => 'Learn More',
		),
	);
	$attrs;

	$shortcode_ui_args = array(
		'label'         => esc_html__( 'Single Card Shoppable' ), // buttons rendered in alphabetical order.
		'listItemImage' => 'dashicons-cart',
		'attrs'         => $attrs,
	);

	shortcode_ui_register_for_shortcode( 'shoppable-single-card', $shortcode_ui_args );
}
add_action( 'register_shortcode_ui', 'tmbi_shooppable_product_card_attributes' );

function tmbi_single_card_ui_scripts() {
	if ( ! class_exists( 'Shortcode_UI' ) ) {
		return;
	}
	wp_register_script(
		'single_card_ui_admin_js',
		plugin_dir_url( __DIR__ ) . 'js/single-card-shoppable-ui-admin.js',
		array( 'jquery' ),
		'1.0.5',
		true
	);
	wp_enqueue_script( 'single_card_ui_admin_js' );
}
add_action( 'admin_enqueue_scripts', 'tmbi_single_card_ui_scripts' );

function tmbi_shoppable_single_card_style() {

	wp_enqueue_style(
		'shoppable-card-ui-admin',
		plugin_dir_url( __DIR__ ) . 'css/shoppable-card-ui-admin.css',
		array(),
		'1.0.0'
	);
}
add_action( 'admin_enqueue_scripts', 'tmbi_shoppable_single_card_style' );
