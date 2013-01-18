<?php
/**
 * List table class for the earnings page
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
 * Earnings products list table class
 *
 * @since  	Marketplace 0.9
 */
class MP_Earnings_List_Table extends WP_List_Table
{
	/**
	 * Lets get it on
	 *
	 * @since  	Marketplace 0.9
	 */
	public function __construct() {
		global $post_type_object;

		$post_type_object = get_post_type_object( 'shop_order' );

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
		global $post_type_object, $avail_post_stati, $order_query, $per_page;

		$avail_post_stati = get_available_post_statuses( $post_type_object->name );
		$per_page 		  = $this->get_items_per_page( 'edit_'. $post_type_object->name .'_per_page' );
		
		$args  = array(
			'post_type' 	 => $post_type_object->name,
			'posts_per_page' => $per_page
		);
		
		if( isset( $_GET['m'] ) )
			$args['m'] = $_GET['m'];
		
		if( isset( $_GET['s'] ) )
			$args['s'] = $_GET['s'];
		
		$order_query = new WP_Query( $args );

		$this->set_pagination_args( array(
			'total_items' 	=> $order_query->found_posts,
			'total_pages' 	=> $order_query->max_num_pages,
			'per_page' 		=> apply_filters( 'edit_posts_per_page', $per_page, $post_type_object->name )
		) );
	}

	/**
	 * Check for any items
	 *
	 * @since  	Marketplace 0.9
	 */
	public function has_items() {
		global $order_query;
		
		return $order_query->have_posts();
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
	 * Display the month dropdown form
	 *
	 * @since  	Marketplace 0.9
	 */
	public function extra_tablenav( $which ) {
		global $post_type_object;
		
		?>
		<div class="alignleft actions">
			<?php		
			if( 'top' == $which ) :
				$this->months_dropdown( $post_type_object->name );
	
				do_action( 'restrict_manage_posts' );
				
				submit_button( __( 'Filter', 'marketplace' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
			endif;
			?>
		</div>
		<?php
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
		
		$posts_columns['id']  	 	= __( 'ID', 'marketplace' );
		$posts_columns['author'] 	= __( 'Author', 'marketplace' );
		$posts_columns['date'] 	 	= __( 'Date', 'marketplace' );
		$posts_columns['amount'] 	= __( 'Sales Amount', 'marketplace' );
		$posts_columns['tax']  		= __( 'Tax', 'marketplace' );
		$posts_columns['rate'] 		= __( 'Rate', 'marketplace' );
		$posts_columns['earnings']  = __( 'Earnings', 'marketplace' );

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
		global $order_query, $post;

		if( empty( $posts ) )
			$posts = $order_query->posts;

		add_filter( 'the_title', 'esc_html' );

		foreach ( $posts as $post ) :
			$order = new WC_Order();
			$order->populate( $post );
			
			foreach( $order->get_items() as $item )
				echo $this->single_row( $post, $order, $item );
		endforeach;
	}
	
	/**
	 * Display a single table row
	 *
	 * @since  	Marketplace 0.9
	 */
	public function single_row( $a_post, $order, $item ) {
		list( $columns, $hidden ) = $this->get_column_info();
		
		$product = new WC_Product( $item['id'] );
		$product->get_post_data();
		
		$author = get_user_by( 'id', $product->post->post_author );
		
		$row = '<tr class="order-'. $product->post->ID .'">';

		foreach ( $columns as $column_name => $column_display_name ) {
			switch ( $column_name ) {
				case 'id':
					$row .= '<td><a href="'. get_permalink( $product->id ) .'">#'. $product->id .'</a></td>';
					break;					

				case 'author':					
					$row .= '<td><a href="'. esc_url( add_query_arg( array( 'page' => marketplace()->folder .'-authors', 'author' => $user_object->ID ), admin_url( 'admin.php' ) ) ) .'">'. esc_html( $author->display_name ) .'</a></td>';
					break;					

				case 'date':
					$row .= '<td>'. mysql2date( get_option( 'date_format' ), $a_post->post_date ) .'</td>';
					break;					

				case 'amount':
					$row .= '<td>'. woocommerce_price( $order->get_line_subtotal( $item ) ) .'</td>';
					break;					

				case 'tax':
					$row .= '<td>'. woocommerce_price( $order->get_line_tax( $item ) ) .'</td>';
					break;					

				case 'rate':
					$row .= '<td>'. mp_get_user_rate( $author->ID ) .'%</td>';
					break;					
					
				case 'earnings':
					$user_rate  	 = mp_get_user_rate( $author->ID );
					
					$item_total	 	 = $order->get_line_subtotal( $item ) - $order->get_line_tax( $item );
					$author_earnings = ( $item_total / 100 ) * $user_rate;
					$earnings	 	 = $item_total - $author_earnings;
					
					$row .= '<td>'. woocommerce_price( $earnings ) .'('. woocommerce_price( $author_earnings ) .')</td>';
					break;					
			}
		}
		
		$row .= '</tr>';
		
		return $row;		
	}
}

/* End of file earnings.php */
/* Location: ./admin/tables/earnings.php */