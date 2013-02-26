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
 * Redirect to PayPal
 *
 * @since  	Marketplace 0.9
 */
function mp_redirect_to_paypal() {
	if( ! isset( $_GET['xpaypal'] ) || $_GET['xpaypal'] != 'marketplace' || ! is_admin() )
		return;

	$month = isset( $_GET['month'] ) ? wp_filter_kses( $_GET['month'] ) : false;
	$author = isset( $_GET['author'] ) ? (int) $_GET['author'] : false;

	check_admin_referer( 'mb_pay_author_'. $month );

	if( empty( $month ) || empty( $author ) )
		return false;

	$link  = defined( 'ENABLE_PAYPAL_SANDBOX' ) && ENABLE_PAYPAL_SANDBOX === true ? 'https://www.sandbox.paypal.com/cgi-bin/webscr?' : 'https://www.paypal.com/cgi-bin/webscr?';
	$link .= http_build_query( array(
		'cmd' 			=> '_xclick',
		'business' 		=> get_user_meta( $author, 'mp_paypal_address', true ),
		'item_name' 	=> sprintf( __( 'Comission payment for: %s', 'marketplace' ), $month ),
		'currency_code' => get_woocommerce_currency(),
		'amount' 		=> mp_get_user_comission( $author, $month, false ),
		'quantity' 		=> 1,
		'custom' 		=> $author .'|'. $month .'|'. wp_hash( $month .'-'. $author ),
		'item_number' 	=> $month .'-'. $author,
		'notify_url' 	=> add_query_arg( array( 'xpaypal' => 'ipnhandler') , home_url() ),
		'cancel_return' => remove_query_arg( array( 'xpaypal', 'month', '_wpnonce' ) ),
		'return' 		=> add_query_arg( array( 'payment' => 'success'), remove_query_arg( array( 'xpaypal', 'month', '_wpnonce' ) ) ),
		'rm' 			=> 2
	), '', '&' );

	// cannot use bp_core_redirect or wp_redirect due to paypal
	header( 'Location: '. $link );
	exit;
}
add_action( 'admin_init', 'mp_redirect_to_paypal', 0 );

/* End of file paypal.php */
/* Location: ./admin/paypal.php */