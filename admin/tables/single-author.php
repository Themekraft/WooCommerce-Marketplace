<?php
/**
 * List table class for the single author products page
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
 * Single author products list table class
 *
 * @since  	Marketplace 0.9
 */
class MP_Single_Author_Product_List_Table extends WP_List_Table
{
	/**
	 * The current marketplace author
	 * 
	 * @var 		int
	 * @since 		Marketplace 0.9
	 */
	public $author;
	
	/**
	 * Lets get it on
	 *
	 * @since  	Marketplace 0.9
	 */
	public function __construct() {
		global $post_type_object;

		$post_type_object = get_post_type_object( 'product' );
		
		$this->author = isset( $_GET['author'] ) ? $_GET['author'] : false;

		parent::__construct( array(
			'plural' => 'posts',
		) );
	}

	/**
	 * Prepare the available items
	 *
	 * @since  	Marketplace 0.9
	 */
	public function prepare_items() {
		global $post_type_object, $avail_post_stati, $product_query, $per_page;

		$avail_post_stati = get_available_post_statuses( $post_type_object->name );
		$per_page 		  = $this->get_items_per_page( 'edit_'. $post_type_object->name .'_per_page' );

		$search = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';
		
		$args = array(
			'post_type' 	 => $post_type_object->name,
			'author'	 	 => $this->author,
			'posts_per_page' => $per_page
		);

		if( ! empty( $search ) )
			$args['s'] = $search;
	
		$product_query = new WP_Query( $args );

		$this->set_pagination_args( array(
			'total_items' 	=> $product_query->found_posts,
			'total_pages' 	=> $product_query->max_num_pages,
			'per_page' 		=> apply_filters( 'edit_posts_per_page', $per_page, $post_type_object->name )
		) );
	}

	/**
	 * Check for any items
	 *
	 * @since  	Marketplace 0.9
	 */
	public function has_items() {
		global $product_query;
		
		return $product_query->have_posts();
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
	 * Text if we have no items
	 *
	 * @since  	Marketplace 0.9
	 */
	public function no_items() {
		global $post_type_object;

		echo $post_type_object->labels->not_found;
	}
	
	/**
	 * Get all columns
	 *
	 * @since  	Marketplace 0.9
	 */
	public function get_column_info() {
		global $post_type_object;

		$posts_columns = $hidden = array();
		
		$sortable = $this->get_sortable_columns();

		$posts_columns['product']  	 			= __( 'Product', 'marketplace' );
		$posts_columns['published-on'] 			= __( 'Date Published', 'marketplace' );
		$posts_columns['total-sales'] 			= __( 'Total Sales', 'marketplace' );
		$posts_columns['current-month']			= __( 'Current Month Sales', 'marketplace' );
		$posts_columns['last-month']			= __( 'Last Month Sales', 'marketplace' );
		$posts_columns['total-income']			= __( 'Total Income', 'marketplace' );
		$posts_columns['current-month-income']	= __( 'Current Month Income', 'marketplace' );
		$posts_columns['last-month-income']		= __( 'Last Month Income', 'marketplace' );
		$posts_columns['sale-percerntage']		= '<abbr title="'. __( 'Total income of this product in percentage to total income of the whole site (current month only)', 'marketplace' ) .'">'. __( 'Sale Percentage', 'marketplace' ) .'</abbr>';
		

		$posts_columns = apply_filters( 'manage_posts_columns', $posts_columns, $post_type_object->name );
		$posts_columns = apply_filters( "manage_{$post_type}_posts_columns", $posts_columns );

		$this->_column_headers = array( $posts_columns, $hidden, $sortable );

		return $this->_column_headers;
	}
	
	/**
	 * Display the table rows
	 *
	 * @since  	Marketplace 0.9
	 */
	public function display_rows( $posts = array() ) {
		global $product_query, $post;

		if( empty( $posts ) )
			$posts = $product_query->posts;
		
		foreach ( $posts as $post )
			echo $this->single_row( $post );
	}
	
	/**
	 * Display a single table row
	 *
	 * @since  	Marketplace 0.9
	 */
	public function single_row( $a_post ) {
		list( $columns, $hidden ) = $this->get_column_info();
		
		$product = new WC_Product( $a_post->ID );
		$product->post = $a_post;

		$row = '<tr id="product-'. $product->id .'">';

		foreach ( $columns as $column_name => $column_display_name ) {
			switch ( $column_name ) {
				case 'product':
					$row .= '<td><a href="'. get_permalink( $product->id ) .'">'. esc_html( $product->get_title() ) .'</a></td>'; 
					break;					

				case 'published-on':
					$row .= '<td>'. mysql2date( get_option( 'date_format' ), $a_post->post_date ) .'</td>'; 
					break;					

				case 'total-sales':
					$row .= '<td>'. mp_get_sales( $product ) .'</td>';
					break;					

				case 'current-month':
					$row .= '<td>'. mp_get_sales( $product, date( 'Y-n', strtotime( 'now' ) ) ) .'</td>';
					break;					

				case 'last-month':
					$row .= '<td>'. mp_get_sales( $product, date( 'Y-n', strtotime( '-1 month' ) ) ) .'</td>'; 
					break;					

				case 'total-income':
					$row .= '<td>'. mp_get_product_income( $this->author, $product ) .' ('. mp_get_product_comission( $this->author, $product ) .')</td>';
					break;					

				case 'current-month-income':
					$row .= '<td>'. mp_get_product_income( $this->author, $product, date( 'Y-n', strtotime( 'now' ) ) ) .' ('. mp_get_product_comission( $this->author, $product, date( 'Y-n', strtotime( 'now' ) ) ) .')</td>';
					break;					

				case 'last-month-income':
					$row .= '<td>'. mp_get_product_income( $this->author, $product, date( 'Y-n', strtotime( '-1 month' ) ) ) .' ('. mp_get_product_comission( $this->author, $product, date( 'Y-n', strtotime( '-1 month' ) ) ) .')</td>'; 
					break;					
				
				case 'sale-percerntage':					
					$row .= '<td>'. mp_get_sales_percentage( $product ) .'</td>'; 
					break;					
			}
		}
		
		$row .= '</tr>';
		
		return $row;		
	}
}

/* End of file earnings.php */
/* Location: ./admin/tables/single-author.php */