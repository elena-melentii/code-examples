/* global jQuery*/

/**
 * File shoppable.js.
 *
 * Handles shoppable cart display in navigation bar
 */

var Shoppable_Cart_UI = {
	updateShoppableUI: function() {
		Shoppable_Cart_UI.addClassesToElements();
		Shoppable_Cart_UI.moveShoppableCartButton();
		Shoppable_Cart_UI.displayShoppableCartButton();
	},
	addClassesToElements: function() {
		jQuery( '.shoppable__v5__cart__btn_container' ).parent( 'div' ).parent( 'div' )
		.addClass( 'shoppable-cart-btn-2021' );
		jQuery( '.shoppable__v5__cart__btn_container' ).css( 'display', 'flex' );
	},
	moveShoppableCartButton: function() {
		jQuery( '.shoppable-cart-btn-2021' ).appendTo( jQuery( '.header-2021 #magazine-subscription-wrapper' ) );
	},
	displayShoppableCartButton: function() {
		var $cart_count = jQuery( '.shoppable__v5__cart__count' ).text();
		var $shop_btn_length = jQuery( '.shop-btn-shoppable' ).length;

		if ( ( $cart_count !== '0' ) || ( $shop_btn_length ) ) {
			jQuery( '.shoppable__v5__cart__btn_container' ).attr( 'style', 'display: flex !important' );
			jQuery( '.shoppable__v5__cart__btn_container_qty ' ).attr( 'style', 'display: flex !important' );
		} else {
			jQuery( '.shoppable-cart-btn-2021' ).attr( 'style', 'border: none !important' );
		}
	},
	handleCartOpen: function( e ) {
		Shoppable_Cart_UI.toggleMobileElements( 'hide' );
	},
	handleCartClose: function( e ) {
		e.stopPropagation();
		Shoppable_Cart_UI.toggleMobileElements( 'show' );
	},
	toggleMobileElements: function( showOrHide ) {
		if ( 480 < jQuery( window ).width() ) {
			return;
		}

		if ( 'show' === showOrHide ) {
			jQuery( '.sticky-utility-bar, .sticky-bottom-ad' ).show();
		} else if ( 'hide' === showOrHide ) {
			jQuery( '.sticky-utility-bar, .sticky-bottom-ad' ).hide();
		}
	},
	handleCartVisibilityChange: function ( eventType ) {
		if ( 'CART_OPEN' === eventType || 'ADD_TO_CART' === eventType ) {
			jQuery( '.jwplayer' ).addClass( 'shoppable-cart-is-open' );
		} else if ( 'CART_CLOSED' === eventType ) {
			jQuery( '.jwplayer' ).removeClass( 'shoppable-cart-is-open' );
		}
	}
};

jQuery( function( $ ) {
	$( document ).ready( function ( ) {
		setTimeout( Shoppable_Cart_UI.updateShoppableUI, 3000 );
		$( document ).on( 'click', '.shoppable-cart-btn-2021, .shop-btn-shoppable', Shoppable_Cart_UI.handleCartOpen );
		$( document ).on( 'click', '.shoppable__dtcv5__cart_close-btn', Shoppable_Cart_UI.handleCartClose );

		ShoppableEvents( "ACTIONS", function ( msg, data ) {
			var eventType = data.ShoppableEvent.type;
			Shoppable_Cart_UI.handleCartVisibilityChange( eventType );
		} );

	} );
} );