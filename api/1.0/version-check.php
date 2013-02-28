<?php
/**
 * Checks the version of a product
 *
 * @package		Marketplace
 * @subpackage	API
 * @author		Boris Glumpler
 * @copyright 	Copyright (c) 2010 - 2012, Themekraft
 * @link		https://shabushabu.eu
 * @license		http://www.opensource.org/licenses/gpl-3.0.php GPL License
 * @since 		Marketplace 0.9.1
 * @filesource
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Marketplace_Version_Check' ) ) :
/**
 * Version Check
 *
 * @since Marketplace 0.9.1
 */
class Marketplace_Version_Check extends Marketplace_API
{
	/**
	 * Generate the output
	 *
	 * @since 	Marketplace 0.9.1
	 * @access 	protected
	 */
	protected function generate_output() {
		global $wpdb;

		// only POST requests are allowed
		if( $this->request_type == 'get' ) :
			$this->output = array(
				'code' 	=> 405,
				'valid'	=> false,
				'msg'  	=> 'GET method not allowed.'
			);
			return;
		endif;

		// we need an API key
		if( ! isset( $this->input['api_key'] ) ) :
			$this->output = array(
				'code'  => 424,
				'valid'	=> false,
				'msg'	=> 'No api key provided.'
			);
			return;
		endif;

		// we need an email
		if( ! isset( $this->input['email'] ) || ! is_email( $this->input['email'] ) ) :
			$this->output = array(
				'code'  => 424,
				'valid'	=> false,
				'msg'	=> 'No email provided.'
			);
			return;
		endif;

		// make sure we have a product to check
		if( ! isset( $this->input['product'] ) ) :
			$this->output = array(
				'code' 	=> 424,
				'valid'	=> false,
				'msg'  	=> 'No product to check.'
			);
			return;
		endif;

		$product = $wpdb->get_col( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->posts}
			WHERE post_name = %s
			AND post_type = 'product'
		", $this->input['product']['slug'] ) );

		// make sure we have a product to check
		if( ! $product->ID ) :
			$this->output = array(
				'code' 	=> 424,
				'valid'	=> false,
				'msg'  	=> 'Product does not exist.'
			);
			return;
		endif;

		// check the api key
		$data = $wpdb->get_row( $wpdb->prepare( "
			SELECT * FROM {$wpdb->prefix}woocommerce_software_licences
			WHERE licence_key = %s
			AND software_product_id = %s
			AND activation_email = %s
			LIMIT 1
		", $this->input['api_key'], $product->ID, $this->input['email'] ) );

		if( ! $data ) :
			$this->output = array(
				'code' 	=> 424,
				'valid'	=> false,
				'msg'  	=> 'Invalid API key.'
			);
			return;
		endif;

		// make sure the order has already been completed
		if ( $data->order_id ) :
			$order_status = wp_get_post_terms( $data->order_id, 'shop_order_status' );

			if( $order_status[0]->slug != 'completed' ) :
				$this->output = array(
					'code' 	=> 424,
					'valid'	=> false,
					'msg'  	=> 'Order is still incomplete.'
				);
				return;
			endif;
		endif;

		$new_version = get_post_meta( $product->ID, '_software_version', true );
		$old_version = $this->input['product']['version'];

		// go on if versions are the same
		if( version_compare( $new_version, $old_version, '<=' ) ) :
			$this->output = array(
				'code' 	=> 424,
				'valid'	=> false,
				'msg'  	=> 'No new version available.'
			);
			return;
		endif;

		$file 		= $this->input['product']['file'];
		$file_path  = apply_filters( 'woocommerce_file_download_path', get_post_meta( $product->ID, '_file_path', true ), $product->ID );

		$this->output['code']  	  = 200;
		$this->output['valid'] 	  = true;
		$this->output['msg']   	  = 'API key is valid.';
		$this->output['response'] = array();

		// put it all together
		$this->output['response'][$file] = array(
			'id'		  	 => $product->ID,
		    'url' 		  	 => get_permalink( $product->ID ),
		    'slug' 		  	 => 'themekraft_'. $product->post_name,
		    'new_version' 	 => $new_version,
		    'package'	  	 => $file_path
		);
	}
}

new Marketplace_Version_Check();
endif;