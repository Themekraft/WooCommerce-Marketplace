<?php
/**
 * Holds all global helper functions
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
 * Get sales
 *
 * @TODO 	It seems that 'total_sales' does not get updated if an order gets deleted
 * @since  	Marketplace 0.9
 * @param	mixed	$product	Either a WC_Product object or an ID
 * @param	string	$month		A month to get sales for, needs to be passed like this: Y-n
 */
function mp_get_sales( $product = 0, $month = false ) {
	$count = 0;

	if( empty( $product ) )
		return $count;
	
	if( is_numeric( $product ) )
		$product = new WC_Product( $product );
	
	if( ! $product instanceof WC_Product )
		return $count;
	
	// get total sales if we don't have a month
	if( empty( $month ) )
		return get_post_meta( $product->id, 'total_sales', true );
	
	$date = explode( '-', $month );
	
	// get all orders for the passed month
	$orders = get_posts( array(
		'post_type' 	=> 'shop_order',
		'numberposts' 	=> -1,
		'year' 			=> $date[0],
		'monthnum' 		=> $date[1],
	) );
	
	// loop through all orders and count up the sales	
	if( count( $orders ) > 0 ) :
		foreach( $orders as $o ) :
			$order = new WC_Order();
			$order->populate( $o );
			
			foreach( $order->get_items() as $item ) :
				if( $item['id'] == $product->id && $order->status == 'completed' )
					$count += (int) $item['qty'];
			endforeach;
		endforeach;
	endif;
		
	return $count;
}

/**
 * Get income for a product
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$user		Either a WP_User object or an ID
 * @param	mixed	$product	Either a WC_Product object or an ID
 * @param	string	$month		A month to get sales for, needs to be passed like this: Y-n
 * @param	bool	$format		Whether to format the income or not
 */
function mp_get_product_income( $user = 0, $product = 0, $month = false, $format = true ) {
	global $wpdb;

	$income = 0;
	
	if( empty( $product ) || empty( $user ) )
		return $income;
	
	if( is_numeric( $product ) )
		$product = new WC_Product( $product );
	
	if( ! $product instanceof WC_Product )
		return $income;	

	if( is_numeric( $user ) )
		$user = new WP_User( $user );
	
	if( ! $user instanceof WP_User )
		return $income;	
	
	$orders = mp_get_orders( $month );
	
	foreach( $orders as $result ) :
		$order = new WC_Order();
		$order->populate( $result );
			
		foreach( $order->get_items() as $item ) :
			if( $item['id'] != $product->id  )
				continue;
			
			$user_rate  	 = mp_get_user_rate( $user->ID );
			$item_total 	 = $order->get_line_subtotal( $item ) - $order->get_line_tax( $item );
			$author_earnings = ( $item_total / 100 ) * $user_rate;
			
			$income += $author_earnings;
		endforeach;
	endforeach;
		
	return $format === true ? woocommerce_price( $income ) : $income;
}

/**
 * Get earnings from a product
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$user		Either a WP_User object or an ID
 * @param	mixed	$product	Either a WC_Product object or an ID
 * @param	string	$month		A month to get sales for, needs to be passed like this: Y-n
 * @param	bool	$format		Whether to format the income or not
 */
function mp_get_product_comission( $user = 0, $product = 0, $month = false, $format = true ) {
	global $wpdb;

	$income = 0;
	
	if( empty( $product ) || empty( $user ) )
		return $income;
	
	if( is_numeric( $product ) )
		$product = new WC_Product( $product );
	
	if( ! $product instanceof WC_Product )
		return $income;	

	if( is_numeric( $user ) )
		$user = new WP_User( $user );
	
	if( ! $user instanceof WP_User )
		return $income;	
	
	$orders = mp_get_orders( $month );
	
	foreach( $orders as $result ) :
		$order = new WC_Order();
		$order->populate( $result );
			
		foreach( $order->get_items() as $item ) :
			if( $item['id'] != $product->id  )
				continue;
			
			$rate 		= 100 - mp_get_user_rate( $user->ID );
			$item_total = $order->get_line_subtotal( $item ) - $order->get_line_tax( $item );
			$earnings 	= ( $item_total / 100 ) * $rate;
			
			$income += $earnings;
		endforeach;
	endforeach;
		
	return $format === true ? woocommerce_price( $income ) : $income;
}

/**
 * Get comission for a user
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$user		Either a WP_User object or an ID
 * @param	string	$month		A month to get sales for, needs to be passed like this: Y-n
 * @param	bool	$format		Whether to format the income or not
 */
function mp_get_user_comission( $user = 0, $month = false, $format = true ) {
	global $wpdb;

	$income = 0;
	
	if( empty( $user ) )
		return $income;
	
	if( is_numeric( $user ) )
		$user = new WP_User( $user );
	
	if( ! $user instanceof WP_User )
		return $income;	
	
	// get all products first
	$products = get_posts( array(
		'post_type' 	=> 'product',
		'author' 		=> $user->ID,
		'numberposts'	=> -1
	) );
	
	// collect all product IDs
	$ids = array();
	foreach( $products as $product )
		$ids[] = $product->ID;
	
	if( count( $ids ) <= 0 )
		return $income;
	
	$orders = mp_get_orders( $month );
	
	foreach( $orders as $result ) :
		$order = new WC_Order();
		$order->populate( $result );
			
		foreach( $order->get_items() as $item ) :
			if( ! in_array( $item['id'], $ids ) )
				continue;

			$user_rate  	 = mp_get_user_rate( $user->ID );
			$item_total 	 = $order->get_line_subtotal( $item ) - $order->get_line_tax( $item );
			$author_earnings = ( $item_total / 100 ) * $user_rate;
			
			$income += $author_earnings;
		endforeach;
	endforeach;
	
	return $format === true ? woocommerce_price( $income ) : $income;
}

/**
 * Get income for a user
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$user		Either a WP_User object or an ID
 * @param	string	$month		A month to get sales for, needs to be passed like this: Y-n
 * @param	bool	$format		Whether to format the income or not
 */
function mp_get_user_income( $user = 0, $month = false, $format = true ) {
	global $wpdb;

	$income = 0;
	
	if( empty( $user ) )
		return $income;
	
	if( is_numeric( $user ) )
		$user = new WP_User( $user );
	
	if( ! $user instanceof WP_User )
		return $income;	
	
	// get all products first
	$products = get_posts( array(
		'post_type' 	=> 'product',
		'author' 		=> $user->ID,
		'numberposts'	=> -1
	) );
	
	// collect all product IDs
	$ids = array();
	foreach( $products as $product )
		$ids[] = $product->ID;
	
	if( count( $ids ) <= 0 )
		return $income;
	
	$orders = mp_get_orders( $month );
	
	foreach( $orders as $result ) :
		$order = new WC_Order();
		$order->populate( $result );
			
		foreach( $order->get_items() as $item ) :
			if( ! in_array( $item['id'], $ids ) )
				continue;

			$user_rate  	 = mp_get_user_rate( $user->ID );
			$item_total 	 = $order->get_line_subtotal( $item ) - $order->get_line_tax( $item );
			
			$income += $item_total;
		endforeach;
	endforeach;
	
	return $format === true ? woocommerce_price( $income ) : $income;
}

/**
 * Get earnings from a user
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$user		Either a WP_User object or an ID
 * @param	string	$month		A month to get sales for, needs to be passed like this: Y-n
 * @param	bool	$format		Whether to format the income or not
 */
function mp_get_user_earnings( $user = 0, $month = false, $format = true ) {
	global $wpdb;

	$income = 0;
	
	if( empty( $user ) )
		return $income;
	
	if( is_numeric( $user ) )
		$user = new WP_User( $user );
	
	if( ! $user instanceof WP_User )
		return $income;	
	
	// get all products first
	$products = get_posts( array(
		'post_type' 	=> 'product',
		'author' 		=> $user->ID,
		'numberposts'	=> -1
	) );
	
	// collect all product IDs
	$ids = array();
	foreach( $products as $product )
		$ids[] = $product->ID;
	
	if( count( $ids ) <= 0 )
		return $income;
	
	$orders = mp_get_orders( $month );
	
	foreach( $orders as $result ) :
		$order = new WC_Order();
		$order->populate( $result );
			
		foreach( $order->get_items() as $item ) :
			if( ! in_array( $item['id'], $ids ) )
				continue;

			$rate  	 	   = 100 - mp_get_user_rate( $user->ID );
			$item_total    = $order->get_line_subtotal( $item ) - $order->get_line_tax( $item );
			$user_earnings = ( $item_total / 100 ) * $rate;
			
			$income += $user_earnings;
		endforeach;
	endforeach;
	
	return $format === true ? woocommerce_price( $income ) : $income;
}

/**
 * Get the total earnings
 *
 * @since  	Marketplace 0.9
 * @param	string	$month	The month to get earnings for, format: Y-n
 */
function mp_get_total_earnings( $month = '' ) {
	$orders = mp_get_orders( $month );
	
	$total = 0;
	
	foreach( $orders as $result ) :
		$order = new WC_Order();
		$order->populate( $result );
			
		foreach( $order->get_items() as $item ) :
			$user_id	= get_post_field( 'post_author', $item['id'], 'raw' );
			$user_rate  = mp_get_user_rate( $user_id );
			$item_total = $order->get_line_subtotal( $item );
			$item_total = $item_total  - $order->get_line_tax( $item );
			
			$author_earnings = ( $item_total / 100 ) * $user_rate;
			
			$total		 	 += $item_total - $author_earnings;
		endforeach;
	endforeach;
	
	return woocommerce_price( $total );
}

/**
 * Get the total comission
 *
 * @since  	Marketplace 0.9
 * @param	string	$month	The month to get earnings for, format: Y-n
 */
function mp_get_total_user_comission( $month = '' ) {
	$orders = mp_get_orders( $month );
	
	$total = 0;
	
	foreach( $orders as $result ) :
		$order = new WC_Order();
		$order->populate( $result );
			
		foreach( $order->get_items() as $item ) :
			$user_id	= get_post_field( 'post_author', $item['id'], 'raw' );
			$user_rate  = mp_get_user_rate( $user_id );
			$item_total = $order->get_line_subtotal( $item );
			$item_total = $item_total  - $order->get_line_tax( $item );
			
			$author_earnings = ( $item_total / 100 ) * $user_rate;
			
			$total		 	 += $author_earnings;
		endforeach;
	endforeach;
	
	return woocommerce_price( $total );
}
/**
 * Get the base query
 *
 * @since  	Marketplace 0.9
 * @param	string	$month		A month to get sales for, needs to be passed like this: Y-n
 */
function mp_get_orders( $month = false ) {
	$args = array(
		'post_type' 	=> 'shop_order',
		'numberposts' 	=> -1,
		'tax_query'		=> array(
			array(
		        'taxonomy' 	=> 'shop_order_status',
		        'terms' 	=> array( 'completed' ),
		        'field' 	=> 'slug',			
			)
		)
	);
	
	if( ! empty( $month ) ) :
		$date = explode( '-', $month );
		
		$args['year'] 	  = $date[0];
		$args['monthnum'] = $date[1];
	endif;
	
	$orders = get_posts( $args );
	
	return $orders;
}

/**
 * Get the total income of this product in percentage to total income of the whole site for the current month
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$product	Either a WC_Product object or an ID
 * @param	string	$month		A month to get sales for, needs to be passed like this: Y-n
 */
function mp_get_sales_percentage( $product = 0, $month = false ) {
	global $wpdb;
	
	if( ! $month )
		$month = date( 'Y-n', strtotime( 'now' ) );

	$product_income = $total_income = 0;
		
	if( empty( $product ) )
		return $product_income;
	
	if( is_numeric( $product ) )
		$product = new WC_Product( $product );
	
	if( ! $product instanceof WC_Product )
		return $product_income;	
		
	$orders = mp_get_orders( $month );
	
	foreach( $orders as $result ) :
		$order = new WC_Order();
		$order->populate( $result );
			
		foreach( $order->get_items() as $item ) :
			$total_income += $order->get_line_subtotal( $item );
			
			if( $item['id'] != $product->id  )
				continue;
			
			$product_income += $order->get_line_subtotal( $item );
		endforeach;
	endforeach;
	
	if( $total_income > 0 )
		$percentage = ( $product_income / $total_income ) * 100;
	else 
		$percentage = '0';
	
	return $percentage .'%';
}

/**
 * Get the rate for the passed user
 *
 * @since  	Marketplace 0.9
 * @param	mixed	$user	Either a WP_User object or an ID
 */
function mp_get_user_rate( $user = 0 ) {
	if( empty( $user ) )
		return 0;
	
	if( is_numeric( $user ) )
		$user_id = $user;
		
	if( $user instanceof WP_User )
		$user_id = $user->ID;
		
	$rate = get_user_meta( $user_id, 'marketplace_user_rate', true );
	
	if( ! $rate )
		$rate = get_option( 'mp_default_rate', 20 );
		
	return $rate;
}

/* End of file functions.php */
/* Location: ./core/functions.php */