/* global jQuery*/

/**
 * Holds methods for handling Google Analytics events.
 */
var Shoppable_Google_Analytics = {
    /**
     * Sets up subscriptions to all Shoppable "action" API events.
     *
     * @link https://ask.shoppable.com/knowledge/how-to-setup-dtc-lite-v5
     */
    subscribeToShoppableEvents: function () {
        ShoppableEvents( "ACTIONS", function ( msg, data ) {
            var eventType = data.ShoppableEvent.type;
            Shoppable_Google_Analytics.handleShoppableEvent( eventType, data );
        } );

        ShoppableEvents( "CHECKOUT", function ( type, data ) {
            digitalData.shoppable_details = {
                cart_id: data.ShoppableCart.currentState.cartInfo.cartId,
                order_number: data.ShoppableEvent.orderNumber
            };
            wp.hooks.doAction( 'tda_purchase', digitalData );
        } );
    },
    /**
     * Handles the triggering of Google Analytics events after the relevent Shoppable events have occurred.
     *
     * @param {string} eventType The type of Shoppable event that was triggered.
     * @param {object} data      Relevant data sent from the event.
     */
    handleShoppableEvent: function ( eventType, data ) {
        var eventsList = [
            'ADD_TO_CART',
            'ORDER_PLACED',
            'CART_INITIALIZED',
            'CART_OPEN',
            'CART_CLOSED',
            'ON_CHECKOUT_VIEW',
            'ON_THANK_YOU_VIEW'
        ];

        // If event type is not in the above list, we are not going to track it.
        if ( -1 === eventsList.indexOf( eventType ) ) {
            return;
        }

        console.log( 'Shoppable Event: ', eventType, data );
        Shoppable_Google_Analytics.triggerGoogleAnalyticsEvent( eventType );
    },
    /**
     * Creates a Google Analytics tracker instance for a given Property ID.
     *
     * @param {string} propertyId ID of the property to create an instance for.
     */
    createTrackerInstance: function ( propertyId ) {
        ga( 'create', propertyId, window.location.host );
    },
    /**
     * Sends event actions to Google Analytics.
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/command-queue-reference
     *
     * @param {string} eventType The type of event being sent.
     */
    triggerGoogleAnalyticsEvent: function ( eventType ) {
        var gaEvent = {
            hitType: 'event',
            category: 'Shoppable',
            action: eventType,
            label: window.location.href,
            values: {
                dimension40: 'Shoppable'
            }
        };
        console.log( 'Send Google Analytics event: ', gaEvent );
        ga(
            'send',
            gaEvent.hitType,
            gaEvent.category,
            gaEvent.action,
            gaEvent.label,
            gaEvent.values
        );
    }
};

jQuery( function ( $ ) {
    $( document ).ready( function () {
        if ( 'undefined' === typeof window.ShoppableEvents ) {
            console.warn( 'Shoppable: ShoppableEvents API not found.' );
            return;
        }

        Shoppable_Google_Analytics.subscribeToShoppableEvents();

        if ( 'undefined' === typeof window.ga ) {
            console.warn( 'Shoppable: Google Analytics GA method not found.' );
            return;
        }

        Shoppable_Google_Analytics.createTrackerInstance( 'UA-42545046-1' );
    } );
} );

