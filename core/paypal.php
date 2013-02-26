<<<<<<< HEAD
<?php
/**
 * Holds all paypal functions
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
 * Verify an IPN
 *
 * @since  	Marketplace 0.9
 */
function mp_paypal_verify_ipn() {
	$url = defined( 'ENABLE_PAYPAL_SANDBOX' ) && ENABLE_PAYPAL_SANDBOX === true ? 'https://www.sandbox.paypal.com/cgi-bin/webscr?' : 'https://www.paypal.com/cgi-bin/webscr?';
	
	$result = wp_remote_post( $url, array( 'ssl' => true, 'body' => 'cmd=_notify-validate&'. http_build_query( $_POST, '', '&' ) ) );
	
	if( wp_remote_retrieve_body( $result ) == 'VERIFIED' )
		return true;
		
	return false;
}

/**
 * Handle an IPN request
 *
 * @since  	Marketplace 0.9
 */
function mp_paypal_ipn_handler() {
	if( ! isset( $_GET['xpaypal'] ) || $_GET['xpaypal'] != 'ipnhandler' || is_admin() )
		return;
	
	// verify the IPN
	if( ! mp_paypal_verify_ipn() )
		return false;
	
	list( $user_id, $month, $hash ) = explode( '|', $_POST['custom'] );
	
	$item_number  = wp_filter_kses( $_POST['item_number'] );
	$control_hash = wp_hash( $item_number );
	$amount 	  = ( empty( $_POST['payment_gross'] ) ) ? $_POST['mc_gross'] : $_POST['payment_gross'];
	$status 	  = strtolower( $_POST['payment_status'] );
	
	// check the control hash
	if( $hash != $control_hash )
		return false;
	
	// check the amount
    if( $amount != mp_get_user_comission( $user_id, $month, false ) )
		return false;
	
	// check the currency
	if( $_POST['mc_currency'] != get_woocommerce_currency() )
		return false;
	
	// check the email address
	if( strtolower( $_POST['business'] ) != strtolower( get_user_meta( $user_id, 'mp_paypal_address', true ) ) )
		return false;

	// and... process	
	switch( $status ) {
		case 'completed' :
			// check the transaction id
			$txn_id = $_POST['txn_id'];
			$txn_ids = get_option( 'mp_txn_ids', array() );
			
			if( in_array( $txn_id, $txn_ids ) )
				return false;
			
			$txn_ids[] = $txn_id;
			update_option( 'mp_txn_ids', array_filter( array_unique( $txn_ids ) ) );
		
			update_user_meta( $user_id, 'mp_comission_'. $month, 'completed' );
			break;
	
		case 'reversed' :
		case 'canceled_reversal' :
		case 'denied' :
		case 'pending' :
		case 'refunded' :
		case 'voided' :
		case 'processed' :
		case 'failed' :
			update_user_meta( $user_id, 'mp_comission_'. $month, $status );
			break;
	}
}
add_action( 'wp', 'mp_paypal_ipn_handler', 0 );

/* End of file paypal.php */
=======
<?php
/**
 * Holds all paypal functions
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
 * Verify an IPN
 *
 * @since  	Marketplace 0.9
 */
function mp_paypal_verify_ipn() {
	$url = defined( 'ENABLE_PAYPAL_SANDBOX' ) && ENABLE_PAYPAL_SANDBOX === true ? 'https://www.sandbox.paypal.com/cgi-bin/webscr?' : 'https://www.paypal.com/cgi-bin/webscr?';
	
	$result = wp_remote_post( $url, array( 'ssl' => true, 'body' => 'cmd=_notify-validate&'. http_build_query( $_POST, '', '&' ) ) );
	
	if( wp_remote_retrieve_body( $result ) == 'VERIFIED' )
		return true;
		
	return false;
}

/**
 * Handle an IPN request
 *
 * @since  	Marketplace 0.9
 */
function mp_paypal_ipn_handler() {
	if( ! isset( $_GET['xpaypal'] ) || $_GET['xpaypal'] != 'ipnhandler' || is_admin() )
		return;
	
	// verify the IPN
	if( ! mp_paypal_verify_ipn() )
		return false;
	
	list( $user_id, $month, $hash ) = explode( '|', $_POST['custom'] );
	
	$item_number  = wp_filter_kses( $_POST['item_number'] );
	$control_hash = wp_hash( $item_number );
	$amount 	  = ( empty( $_POST['payment_gross'] ) ) ? $_POST['mc_gross'] : $_POST['payment_gross'];
	$status 	  = strtolower( $_POST['payment_status'] );
	
	// check the control hash
	if( $hash != $control_hash )
		return false;
	
	// check the amount
    if( $amount != mp_get_user_comission( $user_id, $month, false ) )
		return false;
	
	// check the currency
	if( $_POST['mc_currency'] != get_woocommerce_currency() )
		return false;
	
	// check the email address
	if( strtolower( $_POST['business'] ) != strtolower( get_user_meta( $user_id, 'mp_paypal_address', true ) ) )
		return false;

	// and... process	
	switch( $status ) {
		case 'completed' :
			// check the transaction id
			$txn_id = $_POST['txn_id'];
			$txn_ids = get_option( 'mp_txn_ids', array() );
			
			if( in_array( $txn_id, $txn_ids ) )
				return false;
			
			$txn_ids[] = $txn_id;
			update_option( 'mp_txn_ids', array_filter( array_unique( $txn_ids ) ) );
		
			update_user_meta( $user_id, 'mp_comission_'. $month, 'completed' );
			break;
	
		case 'reversed' :
		case 'canceled_reversal' :
		case 'denied' :
		case 'pending' :
		case 'refunded' :
		case 'voided' :
		case 'processed' :
		case 'failed' :
			update_user_meta( $user_id, 'mp_comission_'. $month, $status );
			break;
	}
}
add_action( 'wp', 'mp_paypal_ipn_handler', 0 );

/* End of file paypal.php */
>>>>>>> c5d7ed7160c0f3b501d58c37f8105a6f29aa7fba
/* Location: ./core/paypal.php */