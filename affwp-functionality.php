<?php
/**
 * Plugin Name: AffiliateWP - Functionality
 * Plugin URI: http://affiliatewp.com
 * Description: Various bits of functionality for the affiliatewp.com site
 * Author: Andrew Munro
 * Author URI: http://affiliatewp.com
 * Version: 1.0
 */

require_once( 'includes/post-types.php' );

define( 'EDD_DISABLE_ARCHIVE', true );
add_filter( 'edd_api_log_requests', '__return_false' );

/**
 * Hide the admin bar for non admins
 * Customers can logout or edit profile from their /account page
 */
function affwpcf_admin_bar( $show ) {

	if ( ! current_user_can( 'manage_options' ) ) {
		$show = false;
	}

	return $show;

}
add_filter( 'show_admin_bar', 'affwpcf_admin_bar' );

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function affwpcf_login_redirect( $redirect_to, $request, $user ) {

	//is there a user to check?
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for admins
		if ( in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return $redirect_to;
		} else {
			return site_url( '/account' );
		}
	} else {
		return $redirect_to;
	}

}
add_filter( 'login_redirect', 'affwpcf_login_redirect', 10, 3 );


/**
 * Redirect customers to their account page if they try and access /wp-admin
 */
function affwpcf_block_admin_access() {

	if ( is_admin() && is_user_logged_in() && ! current_user_can( 'manage_options' ) && ! defined( 'DOING_AJAX' ) ) {
		wp_redirect( site_url( '/account' ) ); exit;
    }

}
add_action( 'admin_init', 'affwpcf_block_admin_access' );

/**
 * Add AffiliateWP logo to wp-login.php page
 */
function affwpcf_login_logo() {
	echo '<style type="text/css"> .login h1 a { background-size: auto; width: auto; background-image:url('.get_bloginfo( 'stylesheet_directory' ).'/images/admin-logo.png) !important; height: 66px; padding-bottom:0; margin-bottom: 16px; } </style>';
}
add_action( 'login_head', 'affwpcf_login_logo' );

/**
 * Change the login header URL
 */
function affwpcf_login_headerurl() {
	return 'https://affiliatewp.com';
}
add_filter( 'login_headerurl', 'affwpcf_login_headerurl' );

/**
 * Change the login header title
 */
function affwpcf_login_headertitle() {
	return 'AffiliateWP';
}
add_filter( 'login_headertitle', 'affwpcf_login_headertitle' );

/**
 * Add rss image
 */
function affwp_rss_featured_image() {
    global $post;

    if ( has_post_thumbnail( $post->ID ) ) {
    	$thumbnail = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
    	$mime_type = get_post_mime_type( get_post_thumbnail_id( $post->ID ) );
    	?>
    	<media:content url="<?php echo $thumbnail; ?>" type="<?php echo $mime_type; ?>" medium="image" width="600" height="300"></media:content>
    <?php }
}
add_filter( 'rss2_item', 'affwp_rss_featured_image' );

/**
 * Add rss namespaces
 */
function affwp_rss_namespace() {
    echo 'xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:georss="http://www.georss.org/georss"';
}
add_filter( 'rss2_ns', 'affwp_rss_namespace' );

/**
 * Get an array of excluded category IDs
 */
function affwp_custom_get_excluded_categories() {

	$excluded_categories = array(
		'exclude-from-rss'
	);

	$ids = array();

	if ( $excluded_categories ) {
		foreach ( $excluded_categories as $category ) {
			$category = get_category_by_slug( $category );
			$ids[] = $category ? $category->cat_ID : '';
		}
	}

	if ( $ids) {
		return $ids;
	}

	return false;
}

/**
 * Hide categories from categories list on site
 */
function affwp_get_object_terms( $terms, $object_ids, $taxonomies ) {

	if ( is_admin() ) {
		return $terms;
	}

    if ( $terms ) {
    	foreach ( $terms as $id => $term ) {

    		$term_id = isset( $term->term_id ) ? $term->term_id : '';

    	    if ( in_array( $term_id, affwp_custom_get_excluded_categories() ) ) {
    	        unset( $terms[$id] );
    	    }
    	}
    }

    return $terms;

}
add_filter( 'wp_get_object_terms', 'affwp_get_object_terms', 10, 3 );

/**
 * Disable jetpack carousel comments
 */
function affwp_custom_remove_comments_on_attachments( $open, $post_id ) {
    $post = get_post( $post_id );
    if( $post->post_type == 'attachment' ) {
        return false;
    }
    return $open;
}
add_filter( 'comments_open', 'affwp_custom_remove_comments_on_attachments', 10 , 2 );

/**
 * Removes styling from Better Click To Tweet plugin
 */
function affwp_remove_stuff() {

	/**
	 * Remove the Discount field
	 * Discounts can only be applied using ?discount=code
	 */
	remove_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );

	/**
	 * Removes styling from Better click to tweet plugin
	 */
	remove_action('wp_enqueue_scripts', 'bctt_scripts');

	/**
	 * Removes styling from EDD Software licensing
	 */
	//remove_action( 'wp_enqueue_scripts', 'edd_sl_scripts' );
}
add_action( 'template_redirect', 'affwp_remove_stuff' );



/**
 * Let the customer know the discount was successfully applied
 */
function affwp_custom_discount_successful() {

	$discount = isset( $_GET['discount'] ) && $_GET['discount'] ? $_GET['discount'] : '';

	$link  = false;
	$class = '';

	// remove link and change message on account page because they will be upgrading etc
	if ( is_page( 'account' ) ) {
		$text  = 'Woohoo! Your discount was successfully added to checkout.';
	} else {
		$text  = 'Woohoo! Your discount was successfully added to checkout. Purchase AffiliateWP now &rarr;';
		$link  = true;
		$class = ' link';
	}

	if ( ! $discount ) {
		return;
	}

	?>
	<div id="notification-area" class="discount-applied<?php echo $class; ?>">
		<div id="notice-content">

		<?php if ( $link ) : ?>
			<a href="/pricing">
		<?php endif; ?>
			<svg id="announcement" width="32px" height="32px">
			   <use xlink:href="<?php echo get_stylesheet_directory_uri() . '/images/svg-defs.svg#icon-thumbs-up'; ?>"></use>
			</svg>
			<p><strong><?php echo $text; ?></strong></p>
		<?php if ( $link ) : ?>
			</a>
		<?php endif; ?>

		</div>
	</div>
		<?php
}
add_action( 'affwp_site_before', 'affwp_custom_discount_successful' );

/**
 * Prevent Discounts on Renewals
 */
function affwp_check_if_is_renewal( $return ) {

	if ( EDD()->session->get( 'edd_is_renewal' ) ) {
		edd_set_error( 'edd-discount-error', __( 'This discount is not valid with renewals.', 'edd' ) );
		return false;
	}

	return $return;

}
//add_filter( 'edd_is_discount_valid', 'affwp_check_if_is_renewal', 99, 1 );

/**
 * Prevent Auto Register from creating user accounts when customers download free downloads
 */
function affwp_edd_auto_register_disable( $return ) {

    if ( isset( $_POST['edd_action'] ) && 'free_download_process' === $_POST['edd_action'] ) {
        $return = true;
    }

    return $return;

}
add_filter( 'edd_auto_register_disable', 'affwp_edd_auto_register_disable', 10, 1 );

/* ----------------------------------------------------------- *
 * Extensions Feed
 * ----------------------------------------------------------- */

/**
 * Register the feed
 */
function affwp_register_add_ons_feed() {
	add_feed( 'feed-add-ons', 'affwp_addons_feed' );
}
add_action( 'init', 'affwp_register_add_ons_feed' );

/**
 * Initialise the feed when requested
 */
function affwp_addons_feed() {
	load_template( STYLESHEETPATH . '/feed-add-ons.php' );
}
add_action( 'do_feed_addons', 'affwp_addons_feed', 10, 1 );

/**
 * Register the rewrite rule for the feed
 */
function affwp_feed_rewrite( $wp_rewrite ) {

	$feed_rules = array(
		'feed/(.+)' => 'index.php?feed=' . $wp_rewrite->preg_index( 1 )
	);

	$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
}
add_action( 'generate_rewrite_rules', 'affwp_feed_rewrite' );

/**
 * Alter the WordPress Query for the feed
 */
function affwp_feed_request( $request ) {

	if ( isset( $request['feed'] ) && 'feed-add-ons' == $request['feed'] ) {
		$request['post_type'] = 'download';
	}

	return $request;
}
add_filter( 'request', 'affwp_feed_request' );

/**
 * Alter the WordPress Query for the feed
 */
function affwp_feed_query( $query ) {

	if ( $query->is_feed && $query->query_vars['feed'] == 'feed-add-ons' ) {

		if ( isset( $_GET['display'] ) && 'official-free' == $_GET['display'] ) {

			$tax_query = array(
				array(
					'taxonomy' => 'download_category',
					'field'    => 'slug',
					'terms'    => 'official-free'
				)

			);

		} else {
			// pro add-ons
			$tax_query = array(

				array(
					'taxonomy' => 'download_category',
					'field'    => 'slug',
					'terms'    => 'pro'
				)
			);
		}

		$query->set( 'posts_per_page', 100 );
		$query->set( 'tax_query', $tax_query );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );

	}
}
add_action( 'pre_get_posts', 'affwp_feed_query', 99999999 );

/*
 * Sets renewal discount to 40% for any customer that purchased before April 18, 2016
 */
function affwp_grandfather_renewal_discount( $renewal_discount, $license_id ) {
	$license = get_post( $license_id );
	if ( ! empty( $license_id ) && strtotime( $license->post_date ) < strtotime( 'April 18, 2016' ) ) {
		$renewal_discount = 40;
	}
	return $renewal_discount;
}
add_filter( 'edd_sl_renewal_discount_percentage', 'affwp_grandfather_renewal_discount', 10, 2 );

/**
 * GF Help Scout sub-domain
 */
function affwp_gf_helpscout_docs_subdomain() {
	return 'affiliatewp';
}
add_filter( 'gf_helpscout_docs_subdomain', 'affwp_gf_helpscout_docs_subdomain' );

/**
 * GF Help Scout Settings
 */
function affwp_gf_helpscout_docs_script_settings( $settings ) {
	$settings['searchDelay'] = 250;

	return $settings;
}
add_filter( 'gf_helpscout_docs_script_settings', 'affwp_gf_helpscout_docs_script_settings' );

/**
 * GF Help Scout - Hide submit ticket button until results are listed
 */
function affwp_gf_helpscout_hide_button() {
	if ( ! is_page( 'support' ) ) {
		return;
	}
	?>
	<script>
	jQuery(document).ready( function($) {
		jQuery('.gform_page_footer, .gfield.need-help').hide();
	});
	jQuery(document).ajaxComplete(function( event, xhr, settings ) {
		jQuery('.gform_page_footer, .gfield.need-help').show();
	});
	</script>

	<?php
}
add_action( 'wp_footer', 'affwp_gf_helpscout_hide_button' );

/**
 * Allow SVGs to be uploaded
 */
function affwp_mime_types( $mimes ) {

    $mimes['svg'] = 'image/svg+xml';
    return $mimes;

}
add_filter( 'upload_mimes', 'affwp_mime_types' );

/**
 * Show the SVG preview in the admin Featured Image metabox
 */
function affwpcf_custom_admin_styles() {
	?>
	<style>
	#set-post-thumbnail{ display: block; }
	img.mpt-thumbnail {
		width: auto;
	    background-image: linear-gradient(45deg, #c4c4c4 25%, transparent 25%, transparent 75%, #c4c4c4 75%, #c4c4c4), linear-gradient(45deg, #c4c4c4 25%, transparent 25%, transparent 75%, #c4c4c4 75%, #c4c4c4);
	    background-position: 0 0px, 10px 10px;
	    background-size: 20px 20px;
	    vertical-align: top;
	}
	</style>
	<?php
}
add_action( 'admin_head', 'affwpcf_custom_admin_styles' );

/**
 * Remove the Jetpack menu for non admins
 */
function affwpcf_hide_jetpack_menu() {

	if ( ! current_user_can( 'manage_options' ) ) {
		remove_menu_page( 'jetpack' );
	}

}
add_action( 'jetpack_admin_menu', 'affwpcf_hide_jetpack_menu' );

/**
 * Show draft pages in the pages dropdown
 */
function affwpcf_show_draft_pages( $dropdown_args, $post ) {

	$dropdown_args['post_status'] = array( 'publish', 'draft' );

	return $dropdown_args;

}
add_filter( 'page_attributes_dropdown_pages_args', 'affwpcf_show_draft_pages', 10, 2 );

// Auto apply BFCM discount
function pw_edd_auto_apply_discount() {

	if( function_exists( 'edd_is_checkout' ) && edd_is_checkout() ) {

		if( ! edd_cart_has_discounts() && edd_is_discount_valid( 'BFCM2016' ) ) {

			edd_set_cart_discount( 'BFCM2016' );

		}

	}

}
add_action( 'template_redirect', 'pw_edd_auto_apply_discount' );
