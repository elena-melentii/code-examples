const helpers = require( '@tmbi/js-test-helpers' );
describe( 'Shoppable Card Triggering Test', function () {
	const setupGlobals = () => {

		helpers.loadjQuery();

		loadScript();
		setupMarkup();

		window.alert = () => { };
		window.tmbi_shoppable_api = {
			api_endpoint: 'https://mock.tmbi.api.com'
		}
		global.image_id = '';

		global.response = {
			name: 'Product Name',
			price: '14.95',
			image: 'https://example.com/abc.png'
		};

		global.shortcake_collection = [
			{
				model: {
					get: jest.fn( ( attr ) => {
						return 'title';
					} )
				},
				setValue: jest.fn( ( val ) => { } )
			},
			{
				model: {
					get: jest.fn( ( attr ) => {
						return 'price';
					} )
				},
				setValue: jest.fn( ( val ) => { } )
			},
			{
				model: {
					get: jest.fn( ( attr ) => {
						return 'image_id';
					} )
				},
				setValue: jest.fn( ( val ) => { } )
			}
		];

		var registeredActions = {};

		global.wp = {
			hooks: {
				doAction: jest.fn( ( hook_name, ...args ) => {
					if ( !registeredActions[ hook_name ] ) {
						return false;
					}
					registeredActions[ hook_name ][ 0 ]( ...args );
				} ),
				addAction: jest.fn( ( hook_name, namespace, callback ) => {
					if ( !registeredActions[ hook_name ] ) {
						registeredActions[ hook_name ] = [];
					}
					registeredActions[ hook_name ].push( callback )
				} )
			}
		};

		jQuery.ajax = ( config ) => { }

	}

	const loadScript = () => {
		helpers.loadScript( "./js/single-card-shoppable-ui-admin.js", "shoppable-card-script" );
	};

	const setupMarkup = () => {
		document.body.innerHTML = `
            <form class="edit-shortcode-form">
                <p><a href="#" class="edit-shortcode-form-cancel">← Back to list</a></p>
                <div class="edit-shortcode-form-fields shortcode-ui-edit-shoppable-single-card"><div>
                <div class="field-block shortcode-ui-field-text shortcode-ui-attribute-upc">            
                    <label for="shortcode-ui-upc-c221">Product UPC</label>
                    <input type="text" class="regular-text" name="upc" id="shortcode-ui-upc-c221" value="" placeholder="">
                </div><div>
                <div class="field-block shortcode-ui-field-attachment shortcode-ui-attribute-image_id">
                    <label for="image_id">Add Image from library</label>
                    <p class="description">this will override the shoppable image</p>
                    <button id="image_id" class="shortcake-attachment-select button button-small add">Select Attachment</button>
                    <div class="attachment-previews"></div>
                </div>
                </div><div>
                <label for="shortcode-ui-title-c223">Product Title</label>
                <input type="text" class="regular-text" name="title" id="shortcode-ui-title-c223" value="" placeholder="">
                <input type="text" class="regular-text" name="price" id="shortcode-ui-title-c223" value="" placeholder="">
            </form>`;
	}

	beforeAll( setupGlobals );

	test( 'Setup UPC field', () => {
		pup_set_upc_field();
		expect( document.querySelector( '[name="upc"]' ).maxLength ).toBe( 15 );
		expect( document.querySelector( '.scan-api' ).getAttribute( 'onclick' ) ).toBe( 'shoppable_scan_api()' );

	} );

	test( 'Check Set Attribute by  Method', () => {
		var selected_element = set_attribute_by_name( 'title', shortcake_collection );
		expect( selected_element.model.get( 'attr' ) ).toBe( 'title' );
	} );

	test( 'Remove img wrap', () => {
		remove_img_wrap();
		expect( document.querySelector( '.shoppable-img-wrap' ) ).toBe( null );
	} );

	test( 'Invalid empty UPC', () => {
		document.querySelector( '.edit-shortcode-form [name="upc"]' ).value = '';
		expect( shoppable_scan_api() ).toBe( false );
	} );

	test( 'Invalid too small UPC call API', () => {
		document.querySelector( '.edit-shortcode-form [name="upc"]' ).value = '123';
		expect( shoppable_scan_api() ).toBe( false );
	} );

	test( 'Invalid too big UPC call API', () => {
		document.querySelector( '.edit-shortcode-form [name="upc"]' ).value = '1234567901011516';
		expect( shoppable_scan_api() ).toBe( false );
	} );

	test( 'Populate Fields', () => {
		populate_fields( response, shortcake_collection, '123456' );
		expect( document.querySelector( '[name="title"]' ).value ).toBe( response.name );
		expect( document.querySelector( '[name="price"]' ).value ).toBe( response.price );
		expect( document.querySelector( '#shoppable-img' ).src ).toBe( response.image );
	} );

	test( 'Check TMBI Upload Image', () => {
		//Given
		var expected_button_text = 'Uploading picture...';

		//When
		populate_fields( response, shortcake_collection, '123456' );
		tmbi_upload_image();

		//Then
		expect( wp.hooks.doAction ).toBeCalledWith( 'tmbi_add_media_to_library', response.image );
		expect( document.querySelector( '#add_to_library' ).innerHTML ).toBe( expected_button_text );
	} );

	test( 'Check Shoppable Scan API Method', () => {
		//Given
		let spy = jest.spyOn( jQuery, 'ajax' );
		let upc = '123456789';

		//When
		populate_fields( response, shortcake_collection, upc );
		shoppable_scan_api();

		//Then
		expect( spy ).toHaveBeenCalledWith( {
			url: window.tmbi_shoppable_api.api_endpoint + '/' + upc,
			dataType: 'json',
			beforeSend: expect.any( Function ),
			success: expect.any( Function ),
			error: expect.any( Function )
		} );

	} );

	test( 'Check TMBI Receive invalid uploaded Image ID', () => {
		//Given
		//Dispatch DOMContentLoaded Event
		var DOMContentLoaded_event = document.createEvent( "Event" )
		DOMContentLoaded_event.initEvent( "DOMContentLoaded", true, true )
		window.document.dispatchEvent( DOMContentLoaded_event )

		var image_id = null;
		let spy = jest.spyOn( window, 'alert' );
		//When
		wp.hooks.doAction( 'tmbi_added_media_to_library', image_id )

		//Then
		expect( spy ).toHaveBeenCalled();
	} );

	test( 'Check TMBI Receive valid uploaded Image ID', () => {
		//Given
		//Dispatch DOMContentLoaded Event
		var DOMContentLoaded_event = document.createEvent( "Event" )
		DOMContentLoaded_event.initEvent( "DOMContentLoaded", true, true )
		window.document.dispatchEvent( DOMContentLoaded_event )

		var image_id = 123;
		let obj = set_attribute_by_name( 'image_id', global.shortcake_collection )
		let spy = jest.spyOn( obj, "setValue" )
		//When
		wp.hooks.doAction( 'tmbi_added_media_to_library', image_id )

		//Then
		expect( spy ).toHaveBeenCalled();
		expect( document.querySelector( '#add_to_library' ).innerHTML ).toBe( 'Picture Uploaded!' );
		expect( document.querySelector( '#add_to_library' ).disabled ).toBe( true );

	} );

} );