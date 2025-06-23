const helpers = require( '@tmbi/js-test-helpers' );

describe( 'Shoppable Google Analytics tests', () => {

    const setupGlobals = () => {
        helpers.loadjQuery();

        // Load the Shoppable script.
        helpers.loadScript( './js/shoppable.js', 'shoppable' );

        let next_methods = {
            addClass: jest.fn( ( className ) => {
                console.log( className )
            } ),
            removeClass: jest.fn( ( className ) => {
                console.log( className )
            } )
        }
        global.jQuery = jest.fn( ( selector ) => next_methods );

    };


    beforeEach( setupGlobals );

    test( 'Verify video position when shoppable cart is open', () => {
        let spy = jest.spyOn( global, 'jQuery' );
        let class_spy = jest.spyOn( global.jQuery(), 'addClass' );

        Shoppable_Cart_UI.handleCartVisibilityChange( 'CART_OPEN' );

        expect( spy ).toHaveBeenCalledWith( '.jwplayer' );
        expect( class_spy ).toHaveBeenCalledWith( 'shoppable-cart-is-open' );
    } );

    test( 'Verify video position when shoppable cart is closed', () => {
        let spy = jest.spyOn( global, 'jQuery' );
        let class_spy = jest.spyOn( global.jQuery(), 'removeClass' );

        Shoppable_Cart_UI.handleCartVisibilityChange( 'CART_CLOSED' );

        expect( spy ).toHaveBeenCalledWith( '.jwplayer' );
        expect( class_spy ).toHaveBeenCalledWith( 'shoppable-cart-is-open' );
    } );

    test( 'Verify video position when shoppable\'s add to cart happens', () => {
        let spy = jest.spyOn( global, 'jQuery' );
        let class_spy = jest.spyOn( global.jQuery(), 'addClass' );

        Shoppable_Cart_UI.handleCartVisibilityChange( 'ADD_TO_CART' );

        expect( spy ).toHaveBeenCalledWith( '.jwplayer' );
        expect( class_spy ).toHaveBeenCalledWith( 'shoppable-cart-is-open' );
    } );

} );
