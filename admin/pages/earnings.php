<?php
/**
 * Holds the earnings admin page
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
 * Contents of the earnings admin page
 *
 * @since 	Marketplace 0.9
 */
function mp_earnings_admin_page() {
	global $post_type_object, $wp_locale;

	$screen = get_current_screen();
	$screen->post_type = $post_type_object->name;
	
	$table = new MP_Earnings_List_Table();	
	$table->prepare_items();

	?>
	<div class="wrap earnings">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Earnings', 'marketplace' ) ?></h2>
		
		<div id="poststuff" class="mp-poststuff">
			<div class="postbox">
				<?php
				if( isset( $_GET['m'] ) ) :
					$month 	= wp_filter_kses( $_GET['m'] );
					$y 		= substr( $month, 0, 4 );
					$m 		= substr( $month, -2 );
					$m_text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $m ), $y );		
					
					$title 	  = sprintf( __( 'Earnings for %1$s', 'marketplace' ), $m_text );
					$content  = mp_get_total_earnings( $y .'-'. $m ) .' ('. mp_get_total_user_comission( $y .'-'. $m ) .')';
				else :
					$title 	  = __( 'Total Earnings', 'marketplace' );
					$content  = mp_get_total_earnings() .' ('. mp_get_total_user_comission() .')';
				endif;
				?>
				<h3>
					<span><?php echo $title ?></span>
				</h3>
				<div class="inside">
					<span class="total-earnings"><?php echo $content ?></span>
				</div>
			</div>
		</div>
		
		<form id="posts-filter" action="" method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( marketplace()->folder .'-earnings' ) ?>" />

			<?php $table->search_box( $post_type_object->labels->search_items, 'post' ); ?>
			<?php $table->display(); ?>
		</form>
	</div>
	<?php
}

/* End of file earnings.php */
/* Location: ./admin/pages/earnings.php */