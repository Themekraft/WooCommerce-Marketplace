<?php
/**
 * Holds all admin helper functions
 * 
 * @package		Marketplace
 * @subpackage	Main
 * @author		Boris Glumpler
 * @copyright 	Copyright (c) 2010 - 2012, Themekraft
 * @link		https://shabushabu.eu
 * @license		http://www.opensource.org/licenses/gpl-3.0.php GPL License
 * @since 		Marketplace 0.9
 * @filesource 
 */
 
// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Callback function for the settings section
 *
 * @since  	Marketplace 0.9
 */
function mp_settings_section() {
	// empty on purpose
}

/**
 * Callback function for the default rate
 *
 * @since  	Marketplace 0.9
 */
function mp_default_rate_setting() {
	$rate = get_option( 'mp_default_rate', 20 );
	?>
	<input name="mp_default_rate" type="text" id="mp_default_rate" value="<?php echo esc_attr( $rate ) ?>" />
	<label for="mp_default_rate"><span class="description"><?php _e( 'Set the default rate for all users.', 'marketplace' ); ?></span></label>
	<?php
}

/**
 * Callback function for the auto send money option
 *
 * @since  	Marketplace 0.9
 */
function mp_auto_send_setting() {
	$auto = get_option( 'mp_auto_send' );
	?>
	<input name="mp_auto_send" <?php checked( $auto, true ) ?> type="checkbox" id="mp_auto_send" value="1" />
	<label for="mp_auto_send"><span class="description"><?php _e( 'Check to enable automatic sending of commissions.', 'marketplace' ); ?></span></label>
	<?php
}

/**
 * Callback function for the user notice option
 *
 * @since  	Marketplace 0.9
 */
function mp_user_notice_setting() {
	$notice = get_option( 'mp_user_notice' );
	?>
	<textarea style="width:500px;" rows="7" name="mp_user_notice" id="mp_user_notice"><?php echo esc_textarea( $notice ) ?></textarea><br />
	<label for="mp_user_notice"><span class="description"><?php _e( 'Add a notice for marketplace authors.', 'marketplace' ); ?></span></label>
	<?php
}

/**
 * Callback function for auto send validation
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$setting	The setting passed to the callback function
 */
function mp_validate_auto_send( $setting ) {
	return $setting == '1' ? true : false;
}

/**
 * Callback function for user notice validation
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$setting	The setting passed to the callback function
 */
function mp_validate_user_notice( $setting ) {
	return wp_kses_post( $setting );
}

/**
 * Get the paypal button to pay the user for last month
 *
 * @since  	Marketplace 0.9
 * @param	object	$user	A WP_User instance
 */
function mp_get_paypal_button( WP_User $user ) {
	$month = date( 'Y-n', strtotime( 'now' ) ); // for testing purposes only
	//$month = date( 'Y-n', strtotime( '-1 month' ) );
	$comission = mp_get_user_comission( $user, $month, false );
	
	// don't show if there's no comission
	if( $comission <= 0 )
		return '---';
	
	// check if the comission has been paid already
	$paid = get_user_meta( $user->ID, 'mp_comission_'. $month, true );
	if( $paid == 'completed' )
		return '---';
	
	// check if the user has got a PayPal address
	$email = get_user_meta( $user->ID, 'mp_paypal_address', true );
	if( ! is_email( $email ) )
		return '---';

	$button = '<a class="button-primary" href="'. wp_nonce_url( add_query_arg( array( 'xpaypal' => 'marketplace', 'author' => $user->ID, 'month' => $month ) ), 'mb_pay_author_'. $month ) .'">'. __( 'Pay Now', 'marketplace' ).'</a>';		

	return $button;
}

/* End of file functions.php */
/* Location: ./admin/functions.php */