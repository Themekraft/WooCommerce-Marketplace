<?php
/**
 * API base class
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

if( ! class_exists( 'Marketplace_API' ) ) :
/**
 * Version Check
 *
 * @since Marketplace 0.9.1
 */
abstract class Marketplace_API
{
	/**
	 * The output
	 *
	 * @var		mixed
	 * @since 	Marketplace 0.9.1
	 */
	protected $output;

	/**
	 * Holds all POST/GET input
	 *
	 * @var		array
	 * @since 	Marketplace 0.9.1
	 */
	protected $input = array();

	/**
	 * Initialize the API method
	 *
	 * @since 	Marketplace 0.9.1
	 * @access 	public
	 */
	public function __construct() {
		switch( strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			case 'POST' :
				$this->input = $_POST;
				$this->request_type = 'post';
				break;

			case 'GET' :
				$this->input = $_GET;
				$this->request_type = 'get';
				break;

			default :
				$this->input = array();
				$this->request_type = 'get';
				break;
		}

		// generate the output
		$this->generate_output();

		// send the output to the browser
		$this->_send_output();
	}

	/**
	 * Generate the output
	 *
	 * @since 	Marketplace 0.9.1
	 * @access 	protected
	 */
	abstract protected function generate_output();

	/**
	 * Automatic destruction starts in 5 seconds
	 *
	 * @since 	Marketplace 0.9.1
	 * @access 	private
	 */
	private function _send_output() {
		global $wpdb;

		// don't cache
		nocache_headers();

		header( 'Content-type:application/json;charset='. $wpdb->charset );
		echo json_encode( $this->output );

		exit;
	}
}
endif;