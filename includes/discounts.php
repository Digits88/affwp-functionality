<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checkbox HTML used by the edit and add discount screens
 */
function affwpcf_edd_discount_html( $checked = false ) {
	$checked = true === $checked ? ' checked' : '';
?>
	<tr>
		<th scope="row" valign="top">
			<label for="edd-sale-discount">Sale Discount</label>
		</th>
		<td>
			<input type="checkbox" id="edd-sale-discount" name="sale_discount" value="1"<?php echo $checked; ?>/>
			<span class="description">Does this discount relate to a yearly product sale? E.g. "End of Winter sale" or "Black Friday/Cyber Monday"</span>
		</td>
	</tr>
	<?php
}

/**
 * Add the sale discount HTML to the add discount screen
 */
add_action( 'edd_add_discount_form_before_use_once', 'affwpcf_edd_discount_html' );

/**
 * Add checkbox to edit discount screen
 */
function affwpcf_edd_edit_sale_discount( $discount_id, $discount ) {

	$sale_discount = get_post_meta( $discount_id, '_edd_discount_sale_discount', true );
	$checked       = $sale_discount ? true : false;

	echo affwpcf_edd_discount_html( $checked );

}
add_action( 'edd_edit_discount_form_before_use_once', 'affwpcf_edd_edit_sale_discount', 10, 2 );

/**
 * Save the post meta
 */
function affwpcf_save_discount( $discount_meta, $discount_id ) {

	if ( isset( $_POST['sale_discount'] ) ) {
		update_post_meta( $discount_id, '_edd_discount_sale_discount', true );
	} else {
		delete_post_meta( $discount_id, '_edd_discount_sale_discount' );
	}
}
add_action( 'edd_post_update_discount', 'affwpcf_save_discount', 10, 2 );
add_action( 'edd_post_insert_discount', 'affwpcf_save_discount', 10, 2 );

/**
 *
 */
function affwpcf_discount_ids() {

	$args = array(
		'posts_per_page' => -1,
		'meta_key'       => '_edd_discount_sale_discount',
		'meta_value'     => true,
		'post_type'      => 'edd_discount',
		'post_status'    => 'active',
	);

	$posts = get_posts( $args );

	if ( $posts ) {
		$posts = wp_list_pluck( $posts, 'ID' );
		return $posts;
	}

	return array();

}

/**
 * Determine if there is a current sale running
 */
function affwpcf_is_current_sale() {

	$sale_discounts = affwpcf_discount_ids();

	if ( empty( $sale_discounts ) ) {
		return false;
	}

	foreach ( $sale_discounts as $discount_id ) {

		if (
			edd_is_discount_started( $discount_id, false ) &&           // make sure discount has started, don't set error at checkout
			! empty ( edd_get_discount_start_date( $discount_id ) ) &&  // make sure discount has a start date
			! empty ( edd_get_discount_expiration( $discount_id ) ) &&  // make sure discount has an expiration date
			edd_is_discount_active( $discount_id ) &&                   // make sure discount is active
			! edd_is_discount_expired( $discount_id )                   // make sure discount has not expired
		) {
			return true;
		}

	}

	return false;

}

/**
 * Hide the discount field at checkout unless there is a current sale.
 */
function affwpcf_show_discount_field() {

	/**
	 * Remove the discount code field unless there is a current sale.
	 * Discounts can only be applied using ?discount=code
	 */
	if ( ! affwpcf_is_current_sale() ) {
		remove_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );
	} else {
		// Unhook default EDD discount field.
		remove_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );
		// Add new discount field.
		add_action( 'edd_checkout_form_top', 'affwpcf_edd_discount_field', -1 );
		// Add JS to auto show the discount field.
		add_action( 'wp_footer', 'affwpcf_edd_discount_field_js' );
	}

}
add_action( 'template_redirect', 'affwpcf_show_discount_field' );

/**
 * Add our own callback for the discount field, keeping the same CSS as before
*/
function affwpcf_edd_discount_field() {

	if ( isset( $_GET['payment-mode'] ) && edd_is_ajax_disabled() ) {
		return; // Only show before a payment method has been selected if ajax is disabled
	}

	if ( ! edd_is_checkout() ) {
		return;
	}

	if ( edd_has_active_discounts() && edd_get_cart_total() ) :
?>

	<fieldset id="edd_discount_code">

			<p id="edd-discount-code-wrap">
				<label class="edd-label" for="edd-discount">
					<?php _e( 'Discount', 'edd' ); ?>
					<img src="<?php echo EDD_PLUGIN_URL; ?>assets/images/loading.gif" id="edd-discount-loader" style="display:none;"/>
				</label>
				<span class="edd-description"><?php _e( 'Enter a coupon code if you have one.', 'edd' ); ?></span>
				<input class="edd-input" type="text" id="edd-discount" name="edd-discount" placeholder="<?php _e( 'Enter discount', 'edd' ); ?>"/>
				<input type="submit" class="edd-apply-discount edd-submit button" value="<?php echo _x( 'Apply Discount', 'Apply discount at checkout', 'edd' ); ?>"/>

				<span id="edd-discount-error-wrap" class="edd_errors edd_error edd-alert edd-alert-error" aria-hidden="true" style="display:none;"></span>
			</p>
	</fieldset>

<?php
	endif;
}

/**
 * Sprinkle a little bit of Javascript to override edd-checkout-global.js and show our field again
*/
function affwpcf_edd_discount_field_js() {
	?>
	<script>
		jQuery(document).ready(function($) {
			$('#edd-discount-code-wrap').show();
		});
	</script>
<?php }
