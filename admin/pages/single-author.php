<<<<<<< HEAD
<?php
/**
 * Holds the single author admin page
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
 * Contents of the singlle author admin page
 *
 * @since 	Marketplace 0.9
 */
function mp_single_author_admin_page() {
	global $post_type_object;

	$screen = get_current_screen();
	$screen->post_type = $post_type_object->name;
	
	$table = new MP_Single_Author_Product_List_Table();	
	$table->prepare_items();
	
	$author = get_user_by( 'id', $table->author );
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php printf( __( 'Author: <a href="%s">%s</a>', 'marketplace' ), esc_url( add_query_arg( array( 'user_id' => $author->ID ), admin_url( 'user-edit.php' ) ) ), $author->display_name ); ?></h2>

		<?php if( isset( $_GET['msg'] ) && $_GET['msg'] == 'updated' ) : ?>
			<div id="message" class="updated">
				<p><?php _e( 'User rate has been updated.', 'marketplace' ) ?></p>
			</div>
		<?php endif; ?>

		<div id="author-info">
			<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th><?php _e( 'Earnings all time', 'marketplace' ) ?></th>
						<th><?php _e( 'Earnings current month', 'marketplace' ) ?></th>
						<th><?php _e( 'Member since', 'marketplace' ) ?></th>
						<th><?php _e( 'Rate', 'marketplace' ) ?></th>
						<th><?php _e( 'Pay now', 'marketplace' ) ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'Earnings all time', 'marketplace' ) ?></th>
						<th><?php _e( 'Earnings current month', 'marketplace' ) ?></th>
						<th><?php _e( 'Member since', 'marketplace' ) ?></th>
						<th><?php _e( 'Rate', 'marketplace' ) ?></th>
						<th><?php _e( 'Pay now', 'marketplace' ) ?></th>
				</tr>
				</tfoot>
				<tbody>
					<tr>
						<td><?php echo mp_get_user_comission( $author ) ?> (<?php echo mp_get_user_earnings( $author ) ?>)</td>
						<td><?php echo mp_get_user_comission( $author, date( 'Y-n', strtotime( 'now' ) ) ) ?> (<?php echo mp_get_user_earnings( $author, date( 'Y-n', strtotime( 'now' ) ) ) ?>)</td>
						<td><?php echo mysql2date( get_option( 'date_format' ), $author->user_registered ) ?></td>
						<td>
							<form method="post" action="">
								<?php wp_nonce_field( 'mp-update-user-rate' ) ?>
								<input type="hidden" name="author-id" value="<?php echo esc_attr( $author->ID ) ?>" />
								<input type="text" maxlength="2" id="marketplace-rate" name="marketplace-rate" value="<?php echo esc_attr( mp_get_user_rate( $author->ID ) ) ?>" />%
								<input class="button" name="update-marketplace-rate" type="submit" value="<?php _e( 'Update', 'marketplace' ) ?>" />
							</form>
						</td>
						<td><?php echo mp_get_paypal_button( $author ) ?></td>
					</tr>
				</tbody>
			</table>
		</div>
				
		<form id="posts-filter" action="" method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( marketplace()->folder .'-authors' ) ?>" />
			<input type="hidden" name="author" value="<?php echo esc_attr( $author->ID ) ?>" />
			
			<?php $table->search_box( $post_type_object->labels->search_items, 'post' ); ?>
			<?php $table->display(); ?>
		</form>
	</div>
	<?php
}

/* End of file single-author.php */
=======
<?php
/**
 * Holds the single author admin page
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
 * Contents of the singlle author admin page
 *
 * @since 	Marketplace 0.9
 */
function mp_single_author_admin_page() {
	global $post_type_object;

	$screen = get_current_screen();
	$screen->post_type = $post_type_object->name;
	
	$table = new MP_Single_Author_Product_List_Table();	
	$table->prepare_items();
	
	$author = get_user_by( 'id', $table->author );
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php printf( __( 'Author: <a href="%s">%s</a>', 'marketplace' ), esc_url( add_query_arg( array( 'user_id' => $author->ID ), admin_url( 'user-edit.php' ) ) ), $author->display_name ); ?></h2>

		<?php if( isset( $_GET['msg'] ) && $_GET['msg'] == 'updated' ) : ?>
			<div id="message" class="updated">
				<p><?php _e( 'User rate has been updated.', 'marketplace' ) ?></p>
			</div>
		<?php endif; ?>

		<div id="author-info">
			<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th><?php _e( 'Earnings all time', 'marketplace' ) ?></th>
						<th><?php _e( 'Earnings current month', 'marketplace' ) ?></th>
						<th><?php _e( 'Member since', 'marketplace' ) ?></th>
						<th><?php _e( 'Rate', 'marketplace' ) ?></th>
						<th><?php _e( 'Pay now', 'marketplace' ) ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'Earnings all time', 'marketplace' ) ?></th>
						<th><?php _e( 'Earnings current month', 'marketplace' ) ?></th>
						<th><?php _e( 'Member since', 'marketplace' ) ?></th>
						<th><?php _e( 'Rate', 'marketplace' ) ?></th>
						<th><?php _e( 'Pay now', 'marketplace' ) ?></th>
				</tr>
				</tfoot>
				<tbody>
					<tr>
						<td><?php echo mp_get_user_comission( $author ) ?> (<?php echo mp_get_user_earnings( $author ) ?>)</td>
						<td><?php echo mp_get_user_comission( $author, date( 'Y-n', strtotime( 'now' ) ) ) ?> (<?php echo mp_get_user_earnings( $author, date( 'Y-n', strtotime( 'now' ) ) ) ?>)</td>
						<td><?php echo mysql2date( get_option( 'date_format' ), $author->user_registered ) ?></td>
						<td>
							<form method="post" action="">
								<?php wp_nonce_field( 'mp-update-user-rate' ) ?>
								<input type="hidden" name="author-id" value="<?php echo esc_attr( $author->ID ) ?>" />
								<input type="text" maxlength="2" id="marketplace-rate" name="marketplace-rate" value="<?php echo esc_attr( mp_get_user_rate( $author->ID ) ) ?>" />%
								<input class="button" name="update-marketplace-rate" type="submit" value="<?php _e( 'Update', 'marketplace' ) ?>" />
							</form>
						</td>
						<td><?php echo mp_get_paypal_button( $author ) ?></td>
					</tr>
				</tbody>
			</table>
		</div>
				
		<form id="posts-filter" action="" method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( marketplace()->folder .'-authors' ) ?>" />
			<input type="hidden" name="author" value="<?php echo esc_attr( $author->ID ) ?>" />
			
			<?php $table->search_box( $post_type_object->labels->search_items, 'post' ); ?>
			<?php $table->display(); ?>
		</form>
	</div>
	<?php
}

/* End of file single-author.php */
>>>>>>> c5d7ed7160c0f3b501d58c37f8105a6f29aa7fba
/* Location: ./admin/pages/single-author.php */