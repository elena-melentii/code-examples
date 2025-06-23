<?php
/**
 * Pup Shoppable FHM Helper.
 *
 * @package pup-shoppable-fhm-helper.
 */

/**
 * Plugin Name: Pup Shoppable FHM Helper
 * Version: 1.0.0
 * Description: Helper plugin for shoppable cart button implemented on FHM.
 * Author: Elena Melentii
 * Text Domain: pup-shoppable-fhm-helper
 * License: GPLv2
 * License URI: https://opensource.org/licenses/GPL-2.0
 *
 * @package pup-shoppable-fhm-helper.
 */
require_once 'inc/shortcake-single-card-shoppable.php';

function pup_shoppable_enqueue_script() {
	wp_enqueue_script( 'pup-shoppable', plugins_url( '/js/shoppable.js', __FILE__ ), array( 'jquery' ), '1.1.1', true );
	wp_register_style( 'pup-shoppable-style', plugins_url( 'css/pup-shoppable.css', __FILE__ ), array(), '1.0.4', 'all' );
	wp_enqueue_style( 'pup-shoppable-style' );
	wp_enqueue_script( 'shoppable-google-analytics', plugins_url( '/js/shoppable-google-analytics.js', __FILE__ ), array( 'jquery' ), '1.2.2', true );
}
add_action( 'wp_enqueue_scripts', 'pup_shoppable_enqueue_script' );

/**
 * Add shortcode to create shoppable single card
 *
 * @param array  $atts the shortcode attributes.
 * @param string $content the editor enclosed content.
 * @return string
 */
function shoppable_single_card( $atts, $content = null ) {

	$single_card_sc = shortcode_atts(
		array(
			'upc'          => '',
			'image_id'     => '',
			'title'        => '',
			'price'        => '',
			'variation'    => 'false',
			'hide_button1' => 'no',
			'button1_text' => 'Add to Cart',
			'button1_pdp'  => 'false',
			'button2_text' => 'Learn More',
		),
		$atts
	);
	$upc            = esc_attr( $single_card_sc['upc'] );
	$title          = esc_attr( $single_card_sc['title'] );
	$image_id       = esc_attr( $single_card_sc['image_id'] );
	$price          = esc_attr( $single_card_sc['price'] );
	$variation      = esc_attr( $single_card_sc['variation'] );
	$hide_button1   = esc_attr( $single_card_sc['hide_button1'] );
	$button1_text   = esc_attr( $single_card_sc['button1_text'] );
	$button1_pdp    = esc_attr( $single_card_sc['button1_pdp'] );
	$button2_text   = esc_attr( $single_card_sc['button2_text'] );
	$button1_attr   = apply_filters( 'add_analytics_data_attributes', '', 'Add to Cart', 'content engagement', 'Below Product Image' );
	$button2_attr   = apply_filters( 'add_analytics_data_attributes', '', 'Learn More', 'content engagement', 'Below Product Image' );
	$img_attr       = apply_filters( 'add_analytics_data_attributes', '', $title, 'content engagement', 'Product Image' );

	if ( $title ) {
		$title = '<h6>' . $title . '</h6>';
	}

	$button1_text = '<a class="addtocartBtn" href="javascript:;" ' . $button1_attr . ' onclick="ShoppableCart({upc: \'' . $upc . '\', variation: ' . $variation . ', pdp: ' . $button1_pdp . ' })">' . $button1_text . '</a>';
	$button2_text = '<a class="learnmoreBtn" href="javascript:;" ' . $button2_attr . ' onclick="ShoppableCart({upc: \'' . $upc . '\', variation: ' . $variation . ', pdp: true})">' . $button2_text . '</a>';
	if ( 'yes' === $hide_button1 ) {
		$button1_text = '';
	}
	$image = get_image_html( $image_id, $title );

	$pattern     = array( '/\[caption (.*?)\]/', '/\>(.*?)\[\/caption\]/' );
	$replace     = array( '', '>' );
	$content     = preg_replace( $pattern, $replace, $content );
	$single_card = '<div class="shoppable-single-card"><a href="javascript:;" ' . $img_attr . ' onclick="ShoppableCart({upc: \'' . $upc . '\', variation: ' . $variation . ', pdp: true})">' . $image . '</a>' . $title . '<b>$' . number_format( $price, 2, '.', ',' ) . '</b>' . $button1_text . $button2_text . do_shortcode( $content ) . '</div>';
	return $single_card;
}
add_shortcode( 'shoppable-single-card', 'shoppable_single_card' );

/**
 * Get html for image
 *
 * @param int    $image_id attachment id of media.
 * @param string $image_alt text of the image.
 *
 * @return string
 */
function get_image_html( $image_id, $image_alt ) {
	$image = '';
	if ( $image_id ) {
		$size_is_registered = in_array( 'square-thumbnail', get_intermediate_image_sizes(), true );
		if ( ! $size_is_registered ) {
			add_image_size( 'square-thumbnail', 225, 225, true );
		}
		$image_src = wp_get_attachment_image_src( $image_id, 'square-thumbnail' );

		$img_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		if ( empty( $img_alt ) ) {
			$img_alt = $image_alt;
		}
		$image = sprintf( '<img class="wp-image-%s" src="%s" alt="%s" width="225" height="225" />', $image_id, $image_src[0], strip_tags( $img_alt ) );
	}
	return $image;
}