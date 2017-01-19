<?php

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add metaboxes
 */
function affwpcf_meta_boxes() {

	/**
	 * Testimonials metabox
	 */
	add_meta_box(
		'affwpcf_testimonials_metabox',
		esc_html__( 'Testimonial Meta', 'affiliatewp-functionality' ),
		'affwpcf_testimonials_fields',
		'testimonial',
		'side'
	);

	/**
	 * Integrations metabox on single downloads
	 */
	add_meta_box(
		'affwpcf_integrations_metabox',
		esc_html__( 'Supported Integrations', 'affiliatewp-functionality' ),
		'affwpcf_integrations_meta_box',
		'download',
		'side'
	);

	/**
	 * Integrations metabox on single downloads
	 */
	add_meta_box(
		'affwpcf_integrations_how_it_works_metabox',
		esc_html__( 'How it works', 'affiliatewp-functionality' ),
		'affwpcf_integrations_how_it_works_metabox',
		'integration',
		'normal'
	);

}
add_action( 'add_meta_boxes', 'affwpcf_meta_boxes' );

/**
 * Testimonial metabox content
 */
function affwpcf_testimonials_fields() {

	$company = get_post_meta( get_the_ID(), '_affwp_testimonial_company', true );

	?>

	<p><strong><?php _e( 'Company', 'affiliatewp-functionality' ); ?></strong></p>
	<p>
		<label for="affwp-testimonial-company" class="screen-reader-text">
			<?php _e( 'Company', 'affiliatewp-functionality' ); ?>
		</label>
		<input class="widefat" type="text" name="affwp_testimonial_company" id="affwp-testimonial-company" value="<?php echo esc_attr( $company ); ?>" size="30" />
	</p>

	<?php wp_nonce_field( 'affwp_testimonial_meta', 'affwp_testimonial_meta' ); ?>

	<?php
}

/**
 * Display the integrations meta box.
 *
 * @since 0.1
 */
function affwpcf_integrations_meta_box( $post_object, $box ) { ?>

	<?php wp_nonce_field( basename( __FILE__ ), 'edd_download_info_meta_box_nonce' ); ?>

	<?php
		$checked = get_post_meta( $post_object->ID, '_affwp_integration_all', true );
	?>
	<p>
		<label for="integrations-all">
		<input type="checkbox" name="affwp_integration_all" id="integrations-all" value="all" <?php checked( $checked, true ); ?>/>
		All (not integration specific)
		</label>
	</p>

    <?php

	$args = array(
	 	'posts_per_page' => -1,
	 	'post_type'      => 'integration',
	 	'post_status'    => 'publish',
		'order'          => 'ASC',
		'orderby'        => 'title'
	);
	$integration_posts = get_posts( $args );

	$integrations = array();

	if ( $integration_posts ) {
		foreach ($integration_posts as $key => $integration_post ) {
		 	$integrations[$key] = (int) $integration_post->ID;
		}
	}

    if ( $integrations ) {
        foreach ( $integrations as $key => $integration_id ) {

			$current_ids = get_post_meta( $post_object->ID, '_affwp_integration' );

			$checked = in_array( $integration_id, $current_ids ) ? true : false;

            ?>
            <p>
                <label for="integration[<?php echo $key; ?>]">
                <input type="checkbox" class="integration" name="affwp_integration[<?php echo $key; ?>]" id="integration[<?php echo $key; ?>]" value="<?php echo $integration_id; ?>" <?php checked( $checked, true ); ?>/>
                <?php echo get_the_title( $integration_id ); ?>
                </label>
            </p>


            <?php
        }
    }


    ?>

	<script>
	( function( $ ) {

	    $( document ).ready( function() {

			// The "All (not integration specific)" option
			var optionAllIntegrations = $('input[name="affwp_integration_all"]');

			// select all the paragraph tags that contain the integration inputs
			var integrations = $("input.integration:checkbox").closest('p');

			// hide or show the integrations if the first checkbox is clicked
			optionAllIntegrations.click( function() {

				if ( this.checked ) {
					$( integrations ).hide();
				} else {
					$( integrations ).show();
				}

			});

			// hide or show integrations on page load
			if ( optionAllIntegrations.is(':checked') ) {
				$( integrations ).hide();
			} else {
				$( integrations ).show();
			}

	    });

	} )( jQuery );
	</script>

	<?php
}


/**
 * Update the menu_order when a testimonial is saved, based on the word count
 *
 * @since 1.0.0
*/
function affwpcf_save_testimonial( $post_id ) {

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( ! isset( $_POST['affwp_testimonial_meta'] ) || ! wp_verify_nonce( $_POST['affwp_testimonial_meta'], 'affwp_testimonial_meta' ) ) {
		return;
	}

	if ( ( isset( $_POST['post_type'] ) && 'testimonial' == $_POST['post_type'] )  ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	$company = ! empty( $_POST['affwp_testimonial_company'] ) ? sanitize_text_field( $_POST['affwp_testimonial_company'] ) : '';

	if ( $company ) {
		update_post_meta( $post_id, '_affwp_testimonial_company', $company );
	} else {
		delete_post_meta( $post_id, '_affwp_testimonial_company' );
	}

    remove_action( 'save_post', 'affwpcf_save_testimonial' );

	$content = $_POST['post_content'];

	$word_count = str_word_count( $content );

    wp_update_post( array( 'ID' => $post_id, 'menu_order' => $word_count ) );

    add_action( 'save_post', 'affwpcf_save_testimonial' );

}
add_action( 'save_post', 'affwpcf_save_testimonial' );



/**
 * Save integration data
 *
 * @since 1.0.0
 */
function affwpcf_save_integration( $post_id, $post ) {

	// Verify the nonce before proceeding
	if ( ! isset( $_POST['edd_download_info_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['edd_download_info_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// Check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// Get the post type object
	$post_type = get_post_type_object( $post->post_type );

	// Check if the current user has permission to edit the post
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	// Get meta keys links in an array and save.
	$fields = apply_filters( 'affwp_theme_save_meta_boxes_fields_save', array(
			'affwp_integration',
			'affwp_integration_all'
		)
	);

	// Loop through
	foreach ( $fields as $field ) {

		// create the meta key
		$meta_key = '_' . $field;

		if ( 'affwp_integration' === $field ) {

			// Integration array that is posted
			$integrations = isset( $_POST['affwp_integration'] ) ? $_POST['affwp_integration'] : array();

			// Get an array of the current IDs
			$current_ids = get_post_meta( $post_id, $meta_key );

			// integrations array
			if ( $integrations ) {

				$ids_to_remove = array_diff( $current_ids, $integrations );

				if ( $ids_to_remove ) {
					foreach ( $ids_to_remove as $id ){
						delete_post_meta( $post_id, $meta_key, $id );
					}
				}

				// loop through each integration
				foreach ( $integrations as $integration_id ) {

					// Integration ID hasn't been added yet, let's add it
					if ( ! in_array( $integration_id, $current_ids ) ) {
						add_post_meta( $post_id, $meta_key, $integration_id );
					}

				}

			} elseif ( ! $integrations ) {
				// if no integrations are posted, remove all of them
				delete_post_meta( $post_id, $meta_key );
			}

		}

		// supports all integrations
		if ( 'affwp_integration_all' === $field ) {

			if ( isset( $_POST['affwp_integration_all'] ) ) {
				update_post_meta( $post_id, $meta_key, true );
				// delete any integration specific keys
			//	delete_post_meta( $post_id, '_affwp_integration' );
			} else {
				delete_post_meta( $post_id, $meta_key, true );
			}

		}

	}

}
add_action( 'save_post', 'affwpcf_save_integration', 10, 2 );

/**
 * Loads the new Featured Icon metabox
 * Requires the Multi Post Thumbnails plugin
 */
function affwpcf_admin_load_mpt() {

    if ( class_exists( 'MultiPostThumbnails' ) ) {

        new MultiPostThumbnails(
            array(
                'label'     => 'Featured Icon',
                'id'        => 'feature-icon',
                'post_type' => 'post'
            )
        );

		new MultiPostThumbnails(
            array(
                'label'     => 'Download Icon',
                'id'        => 'feature-icon',
                'post_type' => 'download'
            )
        );

    }

}
add_action( 'wp_loaded', 'affwpcf_admin_load_mpt' );


/* When the post is saved, saves our custom data */

/**
 * Save integration data
 *
 * @since 1.0.0
 */
function affwpcf_save_integration_how_it_works( $post_id ) {

	// verify if this is an auto save routine.
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( ( isset ( $_POST['affwp_integration_how_it_works'] ) ) && ( ! wp_verify_nonce( $_POST['affwp_integration_how_it_works'], plugin_basename( __FILE__ ) ) ) ) {
		return;
	}

	// Check permissions
	if ( ( isset ( $_POST['post_type'] ) ) && ( 'page' == $_POST['post_type'] )  ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

	}

	// OK, we're authenticated: we need to find and save the data
	if ( isset ( $_POST['_affwp_integration_how_it_works'] ) ) {
		update_post_meta( $post_id, '_affwp_integration_how_it_works', $_POST['_affwp_integration_how_it_works'] );
	}

}
add_action( 'save_post', 'affwpcf_save_integration_how_it_works' );

/**
 * Show the editor
 */
function affwpcf_integrations_how_it_works_metabox( $post ) {

	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'affwp_integration_how_it_works' );

	$field_value = get_post_meta( $post->ID, '_affwp_integration_how_it_works', true );

	wp_editor( $field_value, '_affwp_integration_how_it_works' );

}
