<?php
/**
 * Template Name: Recipe Page 2023
 * Template Post Type: recipe
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package bumblebee
 */
namespace RD_Toh\Content\Recipe;

use RD_Toh\Resource;

require 'inc/parse-content.php';

add_action(
	'wp_enqueue_scripts',
	/**
	 * Enqueue required front-end assets for the Recipe template.
	 */
	function() {
		wp_register_script( 'recipe-comments', get_stylesheet_directory_uri() . '/js/recipes/comments.js', array(), '1.0.1', true );
		wp_register_script( 'recipe-reviews', get_stylesheet_directory_uri() . '/js/recipes/reviews.js', array( 'wp-util' ), '1.0.0', true );
		$review_localized_data = array(
			'ajax_url'                       => admin_url( 'admin-ajax.php' ),
			'review_load_more_action'        => 'toh_review_load_more',
			'review_load_more_template_name' => 'toh-load-more-reviews',
			'review_load_more_nonce'         => wp_create_nonce( 'recipe_load_more_reviews' ),
			'review_submission_action'       => 'toh_review_submission',
			'review_submission_nonce'        => wp_create_nonce( 'recipe_submit_review' ),
		);
		wp_localize_script( 'recipe-reviews', 'toh', $review_localized_data );

		$openweb_is_disabled = ! function_exists( 'tmbi_ow_output_conversation_widget' );
		if ( $openweb_is_disabled ) {
			wp_enqueue_script( 'recipe-reviews' );
			wp_enqueue_script( 'recipe-comments' );
		}

		wp_enqueue_style( 'page-css', get_template_directory_uri() . '/page.css', array(), '1.0.0' );
		wp_enqueue_style( 'recipe-css', get_stylesheet_directory_uri() . '/recipe-2023.css', array(), '1.0.0' );
		wp_enqueue_script( 'review-scroll', get_stylesheet_directory_uri() . '/js/recipes/recipe-review-scroll.js', array(), '1.0.1', true );

		if ( toh_child_is_chicory_enabled() ) {
			$tmbi_chicory_settings = get_option( 'tmbi_chicory_settings' );
			$script_slug           = ! empty( $tmbi_chicory_settings['chicory_lazy_loading'] ) ? 'chicory-async-script' : 'chicory-script';
			wp_enqueue_script( $script_slug );
			wp_enqueue_script( 'chicory-events' );
		}

		wp_enqueue_script( 'toh-social-share', get_stylesheet_directory_uri() . '/js/recipes/social-share-toggle.js', array(), '1.0.0', true );
		wp_enqueue_script( 'pinterest-pinit' );

		if ( function_exists( 'toh_recipe_box_enqueue_save_to_recipe_box' ) ) {
			toh_recipe_box_enqueue_save_to_recipe_box();
		}

		if ( 'ABT235Variant' === bumblebee_get_ab_variant() ) {
			wp_enqueue_script( 'hero-image-recipe-script', get_theme_file_uri( 'js/recipes/native-recipe.js' ), array( 'jquery', 'wp-util' ), '1.1.0', true );
		}

		wp_enqueue_style( 'owl-style', get_template_directory_uri() . '/styles/owl-css/owl-carousel-min.css', array(), '1.0.0' );
		wp_enqueue_style( 'owl-theme-default-style', get_template_directory_uri() . '/styles/owl-css/owl-theme-default-min.css', array(), '1.0.0' );
		wp_enqueue_script( 'category-re-circ-slider', get_template_directory_uri() . '/js/category-re-circ.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'owl-carousel', get_template_directory_uri() . '/js/util/owl-carousel-min.js', array( 'jquery' ), '1.0.0', true );
	}
);

add_filter(
	'ad_unit_path_3',
	function() {
		return bumblebee_get_ad_unit_path( get_page_type() );
	}
);

add_filter(
	'ads_global_targeting',
	function ( $targeting ) {
		$recipe      = new Resource\RecipeResource();
		$ingredients = $recipe->get_ingredients();
		foreach ( $ingredients as $ingredient ) {
			if ( $ingredient['branded'] ) {
				if ( isset( $ingredient['campaign'] ) ) {
					$campaign_id             = $ingredient['campaign'];
					$targeting['campaignID'] = $campaign_id;
				}
				if ( isset( $ingredient['branded_ingredient'] ) ) {
					$raw_branded_text     = $ingredient['branded_ingredient'];
					$branded_text         = str_replace( ' ', '-', strtolower( $raw_branded_text ) );
					$targeting['branded'] = $branded_text;
				}
			}
		}
		return $targeting;
	}
);

// WPDT-11148 : Update prop8/eVar13 to populate with RMS Recipe ID.

add_filter(
	'dtm_wordpress_content_id',
	function() {
		return get_post_meta( get_the_ID(), 'rms_legacy_id', true );

	}
);

add_filter(
	'body_class',
	function( $classes ) {
		if ( 'ABT270Variant' === bumblebee_get_ab_variant() ) {
			$classes[] = 'single-recipe recipe-recirc-strip';
		} else {
			$classes[] = 'single-recipe';
		}
		return $classes;
	}
);

$recipe = new Resource\RecipeResource();
//print("<pre>".print_r($recipe,true)."</pre>");

$id                    = get_the_ID();
$recipe_video_id       = $recipe->get_recipe_video();
$enhanced_recipe_title = get_post_meta( $id, 'enhanced_recipe_title', true );
$recipe_modified_date  = get_the_modified_date( 'M. d, Y' );
$post_thumbnail_id =  get_post_thumbnail_id($id);
$attachment = get_post_meta($post_thumbnail_id, 'photographer_credit_name', true);
print("<pre>".print_r($attachment,true)."</pre>");
?>

<?php get_header(); ?>
<main id="content" class="pure-g recipe-template recipe-template-2023">
	<?php do_action( 'tmbi_recipe_recirc_strip', get_the_ID() ); ?>
	<?php get_partial( 'partials/recipes/mod-header', array( 'recipe' => $recipe ) ); ?>	

	<div class="pure-g pure-u-lg-1">
		<div class="page-content">
			<section class="pure-u-1 pure-u-lg-5-8">
				<?php get_partial( 'partials/ads/pre-article-ad-unit', array( 'recipe' => $recipe ) ); ?>
				<div class="entry-content">
					<?php echo apply_filters( 'the_content', parse_body_content() ); ?>
				</div>
				<?php if ( $recipe_video_id ) : ?>
					<div class="video-recipe-placeholder">
						<h2>Watch how to Make <?php echo wp_kses_post( get_the_title() ); ?></h2>
						<?php echo do_shortcode( $recipe_video_id ); ?>
					</div>
				<?php endif; ?>
				<div class="ad-container-wrapper">
					<?php 	bumblebee_render_ad(
								uniqid( 'ad' ),
								array(
									'slot-name'        => 'content_3',
									'responsive-sizes' => array(
										'mobile'       => array( array( 300, 250 ), array( 320, 50 ), array( 300, 50 ), array( 300, 100 ), array( 320, 100 ), array( 3, 3 ) ),
										'tablet'       => array( array( 728, 90 ), array( 300, 250 ), array( 320, 50 ), array( 300, 50 ), array( 300, 100 ), array( 320, 100 ) ),
										'desktop'      => array( array( 728, 90 ) ),
										'large_screen' => array( array( 728, 90 ) ),
									),
									'targeting'        => array(
										'tf'       => 'atf',
										'pos'      => 'content_3',
										'location' => 'top',
									),
									'threshold'    => '50',
								)
							);
					?>
				</div>
			</section>
			<section id="recipe-single-right-sidebar-container-top" class="pure-u-1 pure-u-lg-8-24 hide-on-mobile right-sidebar-container">
				<div class="ad-container-wrapper sticky-ad-container">
					<div class="stickied-ad">
						<?php 	bumblebee_render_ad(
									uniqid( 'ad' ),
									array(
										'slot-name'        => 'rail_1',
										'responsive-sizes' => array(
											'mobile'       => array( ),
											'tablet'       => array( ),
											'desktop'      => array( array( 300, 250 ) ),
											'large_screen' => array( array( 300, 250 ) ),
										),
										'targeting'        => array(
											'tf'       => 'atf',
											'pos'      => 'rail_1',
											'location' => 'rail',
										),
										'threshold'    => '50',
									)
								);
						?>
					</div>
				</div>
			</section>
		</div>
	</div>
	<div class="pure-g pure-u-lg-1">
		<div class="page-content">
			<section id="quick-recipe" class="pure-u-1 pure-u-lg-5-8">
				<div class="recipe-single-container">
					<div class="wrapper">
						<h2 class="recipe-title"><?php echo wp_kses_post( wp_unslash( $enhanced_recipe_title ) ) ?: the_title(); ?></h2>

						<?php get_partial( 'partials/recipes/tagline', array( 'recipe' => $recipe ) ); ?>

						<?php get_partial( 'partials/recipes/featured-image-2023', array( 'recipe' => $recipe ) ); ?>

						<div class="recipe-toolbar">
							<?php get_partial( 'partials/recipes/social-toolbar', array( 'recipe' => $recipe ) ); ?>
						</div>

						<div class="ad-container-wrapper desktop-hide">
							<?php 	bumblebee_render_ad(
										uniqid( 'ad' ),
										array(
											'slot-name'        => 'content_4',
											'responsive-sizes' => array(
												'mobile'       => array( array( 300, 250 ), array( 320, 50 ), array( 300, 50 ), array( 300, 100 ), array( 320, 100 ), array( 3, 3 ) ),
												'tablet'       => array( array( 728, 90 ), array( 300, 250 ), array( 320, 50 ), array( 300, 50 ), array( 300, 100 ), array( 320, 100 ) ),
												'desktop'      => array( ),
												'large_screen' => array( ),
											),
											'targeting'        => array(
												'tf'       => 'atf',
												'pos'      => 'content_4',
												'location' => 'top',
											),
											'threshold'    => '50',
										)
									);
							?>
						</div>


						<?php get_partial( 'partials/recipes/time', array( 'recipe' => $recipe ) ); ?>

						<?php do_action( 'toh_recipe_affiliate_disclaimer' ); ?>
						<?php get_partial( 'partials/recipes/about-recipe', array( 'recipe' => $recipe ) ); ?>
						<div class="recipe-detail pure-u-1">
							<div class="pure-u-1 pure-u-lg-1-2">
								<?php get_partial( 'partials/recipes/ingredients', array( 'recipe' => $recipe ) ); ?>
							</div>
							<div class="pure-u-1 pure-u-lg-1-2">
								<?php
									get_partial( 'partials/recipes/directions', array( 'recipe' => $recipe ) );
									get_partial( 'partials/recipes/freezer-directions', array( 'recipe' => $recipe ) );
									get_partial( 'partials/recipes/nutrition-facts', array( 'recipe' => $recipe ) );
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="recipe-byline-container">
					<?php bumblebee_posted_by(); ?>
					<div class="updated-date pure-u-1 pure-u-lg-1-2">
						<svg xmlns="http://www.w3.org/2000/svg" width="26" height="27" viewBox="0 0 26 27" fill="none">
							<path d="M19.5 5.70044H23.4V25.2004H2.59998V5.70044H6.49998V4.40044C6.49998 3.86744 6.69498 3.41244 7.07198 3.02244C7.44898 2.64544 7.91698 2.45044 8.44998 2.45044C8.98298 2.45044 9.45098 2.64544 9.82798 3.02244C10.205 3.41244 10.4 3.86744 10.4 4.40044V5.70044H15.6V4.40044C15.6 3.86744 15.795 3.41244 16.172 3.02244C16.549 2.64544 17.017 2.45044 17.55 2.45044C18.083 2.45044 18.551 2.64544 18.928 3.02244C19.305 3.41244 19.5 3.86744 19.5 4.40044V5.70044ZM7.79998 4.40044V7.65044C7.79839 7.73624 7.81412 7.82147 7.84622 7.90105C7.87832 7.98063 7.92614 8.05292 7.98682 8.1136C8.0475 8.17427 8.11979 8.22209 8.19937 8.25419C8.27894 8.2863 8.36418 8.30203 8.44998 8.30044C8.53577 8.30203 8.62101 8.2863 8.70059 8.25419C8.78016 8.22209 8.85245 8.17427 8.91313 8.1136C8.97381 8.05292 9.02163 7.98063 9.05373 7.90105C9.08583 7.82147 9.10156 7.73624 9.09998 7.65044V4.40044C9.09998 4.21844 9.03498 4.06244 8.90498 3.94544C8.78798 3.81544 8.63198 3.75044 8.44998 3.75044C8.26798 3.75044 8.11198 3.81544 7.99498 3.94544C7.86498 4.06244 7.79998 4.21844 7.79998 4.40044ZM16.9 4.40044V7.65044C16.9 7.83244 16.965 7.98844 17.082 8.11844C17.212 8.23544 17.368 8.30044 17.55 8.30044C17.732 8.30044 17.888 8.23544 18.018 8.11844C18.135 7.98844 18.2 7.83244 18.2 7.65044V4.40044C18.2016 4.31464 18.1858 4.22941 18.1537 4.14983C18.1216 4.07025 18.0738 3.99796 18.0131 3.93728C17.9525 3.87661 17.8802 3.82879 17.8006 3.79668C17.721 3.76458 17.6358 3.74885 17.55 3.75044C17.4642 3.74885 17.3789 3.76458 17.2994 3.79668C17.2198 3.82879 17.1475 3.87661 17.0868 3.93728C17.0261 3.99796 16.9783 4.07025 16.9462 4.14983C16.9141 4.22941 16.8984 4.31464 16.9 4.40044ZM22.1 23.9004V10.9004H3.89998V23.9004H22.1ZM9.09998 12.2004V14.8004H6.49998V12.2004H9.09998ZM11.7 12.2004H14.3V14.8004H11.7V12.2004ZM16.9 14.8004V12.2004H19.5V14.8004H16.9ZM9.09998 16.1004V18.7004H6.49998V16.1004H9.09998ZM11.7 16.1004H14.3V18.7004H11.7V16.1004ZM16.9 18.7004V16.1004H19.5V18.7004H16.9ZM9.09998 20.0004V22.6004H6.49998V20.0004H9.09998ZM14.3 22.6004H11.7V20.0004H14.3V22.6004ZM19.5 22.6004H16.9V20.0004H19.5V22.6004Z" fill="black"/>
						</svg>
						<span class="update-label"><?php esc_html_e( 'Updated: ', 'bumblebee' ); ?><?php echo esc_html( $recipe_modified_date ); ?></span>
					</div>
				</div>
				<div class="ad-container-wrapper">
					<?php 	bumblebee_render_ad(
								uniqid( 'ad' ),
								array(
									'slot-name'        => 'content_6',
									'responsive-sizes' => array(
										'mobile'       => array( array( 300, 250 ), array( 320, 50 ), array( 300, 50 ), array( 300, 100 ), array( 320, 100 ), array( 3, 3 ) ),
										'tablet'       => array( array( 728, 90 ), array( 300, 250 ), array( 320, 50 ), array( 300, 50 ), array( 300, 100 ), array( 320, 100 ) ),
										'desktop'      => array( array( 728, 90 ) ),
										'large_screen' => array( array( 728, 90 ) ),
									),
									'targeting'        => array(
										'tf'       => 'atf',
										'pos'      => 'content_6',
										'location' => 'top',
									),
									'threshold'    => '50',
								)
							);
					?>
				</div>
				<div class="recipe-review">
					<?php
						/*
						* Apply before recipe review hook
						*/
						do_action( 'recipe_review_before' );

						get_partial( 'partials/recipes/reviews', array( 'recipe' => $recipe ) );

						/*
						* Apply after recipe review hook
						* best hook for non-contextual video player
						*/
						do_action( 'recipe_review_after' );
					?>
				</div>
			</section>
			<section class="pure-u-1 pure-u-lg-8-24 hide-on-mobile right-sidebar-container">
				<div class="ad-container-wrapper sticky-ad-container">
					<div class="stickied-ad">
						<?php 	bumblebee_render_ad(
									uniqid( 'ad' ),
									array(
										'slot-name'        => 'rail_2',
										'responsive-sizes' => array(
											'mobile'       => array( ),
											'tablet'       => array( ),
											'desktop'      => array( array( 300, 600 ), array( 300, 250 ), array( 160, 600 ) ),
											'large_screen' => array( array( 300, 600 ), array( 300, 250 ), array( 160, 600 ) ),
										),
										'targeting'        => array(
											'tf'       => 'atf',
											'pos'      => 'rail_2',
											'location' => 'rail',
										),
										'threshold'    => '50',
									)
								);
						?>
					</div>
				</div>
			</section>
		</div>
	</div>

	<?php do_action( 'single_recipe_after_content' ); ?>
</main><!-- #primary -->
<?php

get_footer();
