<?php
/*
Plugin Name: Hide Featured Image
Version: 1.0.0
Description: Create ability to hide featured image on posts. WPDT-6114
Author: Elena Melentii
Text Domain: hide-featured-image-field
*/

class Hide_Featured_Image_Meta extends WP_Base {
	const VERSION        = '1.0.0';
	const PLUGIN_TITLE   = 'Hide Featured Image';
	const META_BOX_ID    = 'hide_featured_image';
	const META_BOX_TITLE = 'Hide Feature Image';


	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'admin_init' ) );
		add_action( 'save_post', array( $this, 'hide_featured_save_metabox' ) );

	}


	// Add the metabox
	public function admin_init() {
		add_meta_box(
			self::META_BOX_ID,
			self::META_BOX_TITLE,
			array( $this, 'hide_featured_image_metabox' ),
			array( 'post', 'page' ),
			'side',
			'default'
		);
	}

	public function hide_featured_image_metabox( $post ) {
		$meta = get_post_meta( $post->ID );
		wp_nonce_field( self::META_BOX_ID . '_meta_box', self::META_BOX_ID . '_meta_box_nonce' );
		?>
		<p>
			<label>
				<input type="checkbox" name="hide_featured_image" value="1"
					<?php if ( isset( $meta['hide_featured_image'] ) ) { checked( $meta['hide_featured_image'][0], '1' );} ?> />
				<?php esc_attr_e( 'Hide featured image' ); ?>
			</label>
		</p>
<?php

	}

	public function hide_featured_save_metabox( $post_id ) {
		if ( ! isset( $_POST['hide_featured_image_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hide_featured_image_meta_box_nonce'] ), 'hide_featured_image_meta_box' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( isset( $_POST['post_type'] ) && 'post' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		}

		if ( isset( $_POST['hide_featured_image'] ) ) {
			update_post_meta( $post_id, 'hide_featured_image', '1' );
		} else {
			update_post_meta( $post_id, 'hide_featured_image', '' );
		}
	}
}
$hfim = Hide_Featured_Image_Meta::get_instance();



