<?php


/**
 * Register an integration post type.
 */
function affwpcf_custom_post_types() {

	$labels = array(
		'name'               => _x( 'Integrations', 'post type general name', 'affiliatewp-functionality' ),
		'singular_name'      => _x( 'Integration', 'post type singular name', 'affiliatewp-functionality' ),
		'menu_name'          => _x( 'Integrations', 'admin menu', 'affiliatewp-functionality' ),
		'name_admin_bar'     => _x( 'Integration', 'add new on admin bar', 'affiliatewp-functionality' ),
		'add_new'            => _x( 'Add New', 'integration', 'affiliatewp-functionality' ),
		'add_new_item'       => __( 'Add New Integration', 'affiliatewp-functionality' ),
		'new_item'           => __( 'New Integration', 'affiliatewp-functionality' ),
		'edit_item'          => __( 'Edit Integration', 'affiliatewp-functionality' ),
		'view_item'          => __( 'View Integration', 'affiliatewp-functionality' ),
		'all_items'          => __( 'All Integrations', 'affiliatewp-functionality' ),
		'search_items'       => __( 'Search Integrations', 'affiliatewp-functionality' ),
		'parent_item_colon'  => __( 'Parent Integrations:', 'affiliatewp-functionality' ),
		'not_found'          => __( 'No integrations found.', 'affiliatewp-functionality' ),
		'not_found_in_trash' => __( 'No integrations found in Trash.', 'affiliatewp-functionality' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'affiliatewp-functionality' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'integrations', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_icon'          => 'dashicons-hammer',
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' )
	);

	register_post_type( 'integration', $args );

	$labels = array(
		'name'               => _x( 'Testimonials', 'post type general name', 'affiliatewp-functionality' ),
		'singular_name'      => _x( 'Integration', 'post type singular name', 'affiliatewp-functionality' ),
		'menu_name'          => _x( 'Testimonials', 'admin menu', 'affiliatewp-functionality' ),
		'name_admin_bar'     => _x( 'Testimonial', 'add new on admin bar', 'affiliatewp-functionality' ),
		'add_new'            => _x( 'Add New', 'testimonial', 'affiliatewp-functionality' ),
		'add_new_item'       => __( 'Add New Testimonial', 'affiliatewp-functionality' ),
		'new_item'           => __( 'New Testimonial', 'affiliatewp-functionality' ),
		'edit_item'          => __( 'Edit Testimonial', 'affiliatewp-functionality' ),
		'view_item'          => __( 'View Testimonial', 'affiliatewp-functionality' ),
		'all_items'          => __( 'All Testimonials', 'affiliatewp-functionality' ),
		'search_items'       => __( 'Search Testimonials', 'affiliatewp-functionality' ),
		'parent_item_colon'  => __( 'Parent Testimonials:', 'affiliatewp-functionality' ),
		'not_found'          => __( 'No testimonials found.', 'affiliatewp-functionality' ),
		'not_found_in_trash' => __( 'No testimonials found in Trash.', 'affiliatewp-functionality' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'affiliatewp-functionality' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'testimonials', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_icon'          => 'dashicons-testimonial',
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' )
	);

	register_post_type( 'testimonial', $args );
}
add_action( 'init', 'affwpcf_custom_post_types' );


function affwpcf_integration_taxonomies() {

	$labels = array(
		'name'              => _x( 'Features', 'taxonomy general name' ),
		'singular_name'     => _x( 'Feature', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Features' ),
		'all_items'         => __( 'All Features' ),
		'parent_item'       => __( 'Parent Feature' ),
		'parent_item_colon' => __( 'Parent Feature:' ),
		'edit_item'         => __( 'Edit Feature' ),
		'update_item'       => __( 'Update Feature' ),
		'add_new_item'      => __( 'Add New Feature' ),
		'new_item_name'     => __( 'New Feature Name' ),
		'menu_name'         => __( 'Feature' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'feature' ),
	);

	register_taxonomy( 'feature', array( 'integration' ), $args );
}
add_action( 'init', 'affwpcf_integration_taxonomies', 0 );





/**
 * Change ‘Enter Title Here’ text for the Testimonial.
 */
function affwpcf_change_default_title( $title ) {

	$screen = get_current_screen();

	if ( 'testimonial' == $screen->post_type )
		$title = esc_html__( "Enter the customer's name here", 'affiliatewp-functionality' );

	return $title;

}
add_filter( 'enter_title_here', 'affwpcf_change_default_title' );


/**
 * Add metabox
 */
function affwpcf_testimonials_meta_box() {

	add_meta_box(
		'affwp_testimonials_metabox',
		esc_html__( 'Testimonial Meta', 'affiliatewp-functionality' ),
		'affwpcf_testimonials_fields',
		'testimonial',
		'side'
	);

}
add_action( 'add_meta_boxes', 'affwpcf_testimonials_meta_box' );

/**
 *
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
 * Order integrations and testimonials by menu order
 *
 * @since 1.0.0
 */
function affwpcf_order_integrations( $query ) {

    if ( $query->is_main_query() && ! is_admin() && $query->is_post_type_archive() ) {

        if ( $query->is_post_type_archive( 'integration' ) || $query->is_post_type_archive( 'testimonial' ) ) {

			$query->set( 'orderby', array( 'menu_order' => 'ASC' ) );
            $query->set( 'order', 'ASC' );
			$query->set( 'posts_per_page', -1 );

        }

    }

}
add_action( 'pre_get_posts', 'affwpcf_order_integrations' );
