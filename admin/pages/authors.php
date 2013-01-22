<?php
/**
 * Holds the authors admin page
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
 * Contents of the authors admin page
 *
 * @since 	Marketplace 0.9
 */
function mp_authors_admin_page() {
	$table = new MP_Authors_List_Table();
	$table->prepare_items();

	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Authors', 'marketplace' ) ?></h2>
		
		<form id="posts-filter" action="" method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( marketplace()->folder .'-authors' ) ?>" />

			<?php $table->search_box( __( 'Search Users', 'marketplace' ), 'user' ); ?>
			<?php $table->display(); ?>
		</form>
	</div>
	<?php
}

/* End of file authors.php */
/* Location: ./admin/pages/authors.php */