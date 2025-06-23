/**
 * This script controls the frontend behavior of single card shoppable and ajax calls to shoppable API.
 */
var shortcake_collection = null;

document.addEventListener(
	"DOMContentLoaded",
	function ( event ) {
		/**
		 * Triggers when shortcake buttons are loaded.
		 */
		if ( wp.shortcake ) {
			wp.shortcake.hooks.addAction( 'shortcode-ui.render_edit', pup_set_upc_field );
			wp.shortcake.hooks.addAction( 'shortcode-ui.render_new', pup_set_upc_field );
			wp.shortcake.hooks.addAction( 'shoppable-single-card.upc', set_vars );
		}
		/*
		 * Adds a hook that waits the event that indicates the picture is uploaded then set the ID attribute on shortcode
		 */
		wp.hooks.addAction(
			'tmbi_added_media_to_library',
			'global',
			function ( image_id ) {
				if(!image_id){
					alert("Error while uploading picture to library!");
					document.querySelector( '#add_to_library' ).innerHTML = 'Error!'
					return false;
				}
				set_attribute_by_name( 'image_id', shortcake_collection ).setValue( image_id );
				document.querySelector( '#add_to_library' ).innerHTML = 'Picture Uploaded!';
				document.querySelector( '#add_to_library' ).setAttribute( 'disabled', 'disabled' );
			}
		);
	}
);

/*
 * Renders the scan API button and required attributes for validation.
 */
function pup_set_upc_field () {
	document.querySelector( '.edit-shortcode-form [name="upc"]' ).setAttribute( 'maxlength', '15' );
	var upc_wrap = document.querySelector( '.shortcode-ui-attribute-upc' );
	var p_el = document.querySelector( '.shortcode-ui-attribute-upc .description' );
	let button = document.createElement( 'button' );
	button.innerHTML = 'Scan API';
	button.setAttribute( 'class', 'scan-api' );
	button.setAttribute( 'type', 'button' );
	button.setAttribute( 'onclick', 'shoppable_scan_api()' );
	upc_wrap.insertBefore( button, p_el );
}

/* 
 * The following var declarations will store later on functions and variables necessary in global scope, mostly to be able to run jest tests.
 * changed variable is required by internal workings of wp.hooks
 */
function set_vars ( changed, collection ) {
	shortcake_collection = collection;
}

/*
 * Used to set the shortcode attributes.
 */
function set_attribute_by_name ( name, collection ) {
	return collection.find(
		function ( element ) {
			return name === element.model.get( 'attr' );
		}
	);
}

/*
 * Used to clear the API picture if a new API query will be made.
 */
function remove_img_wrap () {
	var img_wrap = document.querySelector( '.shoppable-img-wrap' );
	if ( img_wrap ) {
		img_wrap.remove()
	}
}

/*
 * Populates the form fields with API received information.
 */
function populate_fields ( response, collection, upc_code ) {
	var img_html = '<div class="shoppable-img-wrap"><img id="shoppable-img" src="' + response.image + '">';
	var button_html = '<button id="add_to_library" type="button" onclick="tmbi_upload_image()" class="shortcake-attachment-select button button-small add">Add to Media Library</button></div>';
	var image_parent = document.querySelector( '.shortcode-ui-attribute-upc' );
	image_parent.innerHTML += img_html + button_html;
	document.querySelector( '.shortcode-ui-attribute-upc input' ).value = upc_code;
	set_attribute_by_name( 'title', collection ).setValue( response.name );
	document.querySelector( '[name="title"]' ).value = response.name;
	set_attribute_by_name( 'price', collection ).setValue( response.price );
	document.querySelector( '[name="price"]' ).value = response.price;
}

/*
 * This will trigger the event that handles the image from shoppable to be uploaded to media gallery.
 * It uses external plugin upload media function, see https://readersdigest.atlassian.net/browse/SET-78
 */
function tmbi_upload_image () {
	document.querySelector( '#add_to_library' ).innerHTML = 'Uploading picture...'
	var image_url = document.querySelector( '#shoppable-img' ).src;
	wp.hooks.doAction( 'tmbi_add_media_to_library', image_url );
}

/**
 * Retrieve API information and triggers functions to populate the form.
 */
function shoppable_scan_api () {
	var upc_code = document.querySelector( '.edit-shortcode-form [name="upc"]' ).value;
	if ( !upc_code || upc_code.length < 8 || upc_code.length > 15 ) {
		alert( 'UPC invalid' )
		return false;
	}
	// remove image preview before rendering.
	remove_img_wrap();
	var requestUrl = window.tmbi_shoppable_api.api_endpoint + '/' + upc_code;
	jQuery.ajax(
		{
			url: requestUrl,
			dataType: 'json',
			beforeSend: function () {
				jQuery( 'body' ).css( 'cursor', 'wait' );
			},
			success: function ( response ) {
				jQuery( 'body' ).css( 'cursor', 'pointer' );
				if ( !response.error ) {
					if ( 'string' === typeof response && response.includes( 'Request failed' ) ) {
						alert( 'Error in fetching Shoppable API' );
						return false;
					}
					if ( !response.image ) {
						alert( 'Product Image not found!' );
						return false;
					}
					if ( !response.name ) {
						alert( 'Product Name is not found!' );
						return false;
					}
					if ( !response.price ) {
						alert( 'Product price is not found!' );
						return false;
					}
					populate_fields( response, shortcake_collection, upc_code );
				} else {
					alert( 'Error in fetching Shoppable API' );
				}
			},
			error: function () {
				alert( 'Cannot find product by UPC!' );
			}
		}
	);
}