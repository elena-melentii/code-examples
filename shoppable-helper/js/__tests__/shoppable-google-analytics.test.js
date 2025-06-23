const helpers = require( '@tmbi/js-test-helpers' );

describe( 'Shoppable Google Analytics tests', () => {

	const setupGlobals = () => {
		// Mock jQuery and its' post() method.
		helpers.loadjQuery();

		// Load the Shoppable GA script.
		helpers.loadScript( './js/shoppable-google-analytics.js', 'shoppable-google-analytics' );

        global.ShoppableEvents = jest.fn();
        global.ga              = jest.fn();
	
	};
	beforeAll( setupGlobals );

    test( 'Subscribe to Shoppable action events method', () => {
        Shoppable_Google_Analytics.subscribeToShoppableEvents();
        expect( ShoppableEvents ).toHaveBeenCalledWith( 'ACTIONS', expect.anything() );
    } );

    test( 'Subscribe to Shoppable checkout events method', () => {
        Shoppable_Google_Analytics.subscribeToShoppableEvents();
        expect( ShoppableEvents ).toHaveBeenCalledWith( 'CHECKOUT', expect.anything() );
    } );

    test( 'Google Analytics tracker instance method is called', () => {
        const propertyId = 'UA-42545046-1';
        const domain     = window.location.host;

        Shoppable_Google_Analytics.createTrackerInstance( propertyId );
        expect( ga ).toHaveBeenCalledWith( 'create', propertyId, domain );
    } );

    test( 'Shoppable event handler runs GA tracking method for valid events', () => {
        const triggerGoogleAnalyticsEvent = jest.spyOn( Shoppable_Google_Analytics, 'triggerGoogleAnalyticsEvent' );

        const validEvents = [
            'ADD_TO_CART',
            'ORDER_PLACED',
            'CART_INITIALIZED',
            'CART_OPEN',
            'CART_CLOSED',
            'ON_CHECKOUT_VIEW',
            'ON_THANK_YOU_VIEW'
        ];

        validEvents.forEach( ( eventType ) => {
            Shoppable_Google_Analytics.handleShoppableEvent( eventType, {} );
            expect( triggerGoogleAnalyticsEvent ).toHaveBeenCalledWith( eventType );
        });
    } );

    test( 'Shoppable event handler does not run GA tracking method for non-relevant events', () => {
        const triggerGoogleAnalyticsEvent = jest.spyOn( Shoppable_Google_Analytics, 'triggerGoogleAnalyticsEvent' );

        Shoppable_Google_Analytics.handleShoppableEvent( 'testEventType', {} );
        expect( triggerGoogleAnalyticsEvent ).not.toHaveBeenCalledWith( 'testEventType' );
    } );

    test( 'Triggering Google Analytics events calls GA send event method', () => {
        const href           = window.location.href;
        const expectedValues = {
            'dimension40': 'Shoppable'
        };

        const eventType = 'ADD_TO_CART';
        Shoppable_Google_Analytics.triggerGoogleAnalyticsEvent( eventType );

        expect( ga ).toHaveBeenCalledWith( 'send', 'event', 'Shoppable', eventType, href, expectedValues );
    } );

} );
