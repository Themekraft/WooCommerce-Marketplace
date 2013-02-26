<?php
/**
 * List table class for the authors pages
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
 * Authors products list table class
 *
 * @since  	Marketplace 0.9
 */
class MP_Authors_List_Table extends WP_List_Table
{
	/**
	 * Lets get it on
	 *
	 * @since  	Marketplace 0.9
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'user',
			'plural'   => 'users'
		) );
	}

	/**
	 * Prepare the available items
	 *
	 * @since  	Marketplace 0.9
	 */
	public function prepare_items() {
		global $search;

		$search 	= isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';
		$per_page 	= $this->get_items_per_page( 'users_per_page' );
		$paged 		= $this->get_pagenum();

		$args = array(
			'number' => $per_page,
			'offset' => ( $paged - 1 ) * $per_page,
			'role' 	 => 'marketplace_author',
			'search' => $search,
			'fields' => 'all_with_meta'
		);

		if( ! empty( $args['search'] ) )
			$args['search'] = '*'. $args['search'] .'*';

		if ( isset( $_REQUEST['orderby'] ) )
			$args['orderby'] = $_REQUEST['orderby'];

		if ( isset( $_REQUEST['order'] ) )
			$args['order'] = $_REQUEST['order'];

		$users = new WP_User_Query( $args );

		$this->items = $users->get_results();

		$this->set_pagination_args( array(
			'total_items' 	=> $users->get_total(),
			'per_page' 		=> $per_page,
		) );
	}

	/**
	 * Text if we have no items
	 *
	 * @since  	Marketplace 0.9
	 */
	public function no_items() {
		_e( 'No matching users were found.' );
	}

	/**
	 * Get all sortable columns
	 * Note: Not used at the moment
	 *
	 * @since  	Marketplace 0.9
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Get all columns
	 *
	 * @since  	Marketplace 0.9
	 */
	public function get_column_info() {
		$user_columns = $hidden = array();

		$sortable = $this->get_sortable_columns();

		$user_columns['name'] 			= __( 'Name', 'marketplace' );
		$user_columns['products'] 		= __( 'Products', 'marketplace' );
		$user_columns['total-earnings'] = __( 'Earnings (total)', 'marketplace' );
		$user_columns['month-earnings'] = __( 'Earnings (current month)', 'marketplace' );
		$user_columns['member-since'] 	= __( 'Member Since', 'marketplace' );
		$user_columns['pay-now'] 		= __( 'Pay Now', 'marketplace' );

		$user_columns = apply_filters( 'manage_user_columns', $user_columns );

		$this->_column_headers = array( $user_columns, $hidden, $sortable );

		return $this->_column_headers;
	}

	/**
	 * Display the table rows
	 *
	 * @since  	Marketplace 0.9
	 */
	public function display_rows() {
		$style = '';
		foreach( $this->items as $user_id => $user_object ) {
			$role = reset( $user_object->roles );

			if( is_multisite() && empty( $role ) )
				continue;

			$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
			echo "\n\t", $this->single_row( $user_object, $style );
		}
	}

	/**
	 * Display a single table row
	 *
	 * @since  	Marketplace 0.9
	 */
	public function single_row( $user_object, $style = '' ) {
		if( ! ( is_object( $user_object ) && $user_object instanceof WP_User ) )
			$user_object = get_userdata( (int) $user_object );

		list( $columns, $hidden ) = $this->get_column_info();

		$row = '<tr id="user-'. $user_object->ID .'">';

		foreach ( $columns as $column_name => $column_display_name ) {
			switch ( $column_name ) {
				case 'name':
					$row .= '<td><a href="'. esc_url( add_query_arg( array( 'page' => marketplace()->folder .'-authors', 'author' => $user_object->ID ), admin_url( 'admin.php' ) ) ) .'">'. esc_html( $user_object->display_name ) .'</a></td>';
					break;

				case 'products':
					$products = get_posts( array(
						'post_type' 	=> 'product',
						'author' 		=> $user_object->ID,
						'numberposts'	=> -1
					) );

					$row .= '<td>';
						$row .= '<ul>';

						if( count( $products ) > 0 ) :
							foreach( $products as $product ) :
								$row .= '<li><a href="'. get_permalink( $product->ID ) .'">'. $product->post_title .'</a></li>';
							endforeach;
						else :
							$row .= __( 'No products yet.', 'marketplace' );
						endif;

						$row .= '<ul>';
					$row .= '</td>';
					break;

				case 'total-earnings':
					$row .= '<td>'. mp_get_user_comission( $user_object ) .' ('. mp_get_user_earnings( $user_object ) .')</td>';
					break;

				case 'month-earnings':
					$row .= '<td>'. mp_get_user_comission( $user_object, date( 'Y-n', strtotime( 'now' ) ) ) .' ('. mp_get_user_earnings( $user_object, date( 'Y-n', strtotime( 'now' ) ) ) .')</td>';
					break;

				case 'member-since':
					$row .= '<td>'. mysql2date( get_option( 'date_format' ), $user_object->user_registered ) .'</td>';
					break;

				case 'pay-now':
					$row .= '<td>'. mp_get_paypal_button( $user_object ) .'</td>';
					break;
			}
		}

		$row .= '</tr>';

		return $row;
	}
}

/* End of file authors.php */
/* Location: ./admin/tables/authors.php */