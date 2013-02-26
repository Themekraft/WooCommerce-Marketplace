<?php
/**
 * Holds the main admin class
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

if( ! class_exists( 'Marketplace_Admin' ) ) :
/**
 * Main Admin Class
 *
 * @since Marketplace 0.9
 */
final class Marketplace_Admin
{
	/**
	 * Instantiate the admin area
	 *
	 * @since 	Marketplace 0.9
	 * @var 	string
	 */
	protected $page;

	/**
	 * Instantiate the admin area
	 *
	 * @since 	Marketplace 0.9
	 * @var 	bool
	 */
	protected $is_single_author;

	/**
	 * Instantiate the admin area
	 *
	 * @since 	Marketplace 0.9
	 * @access 	public
	 */
	public function __construct() {
		$this->page 		 	= isset( $_GET['page'] ) ? $_GET['page'] : false;
		$this->is_single_author = isset( $_GET['author'] ) && is_numeric( $_GET['author'] ) ? true : false;

		$this->_includes()
			 ->_setup_actions();
	}

	/**
	 * Include all admin files
	 *
	 * @since 	Marketplace 0.9
	 * @access 	private
	 */
	private function _includes() {
		$files = array(
			'functions',
			'paypal',
			'pages/single-author',
			'pages/authors',
			'pages/earnings'
		);

		switch( $this->page ) {
			case marketplace()->folder .'-earnings' :
				$files[] = 'tables/earnings';
			break;

			case marketplace()->folder .'-authors' :
				if( $this->is_single_author )
					$files[] = 'tables/single-author';
				else
					$files[] = 'tables/authors';
			break;
		}

		foreach( $files as $file )
			require marketplace()->plugin_dir .'admin/'. $file .'.php';

		return $this;
	}

	/**
	 * Set up all class actions
	 *
	 * @since 	Marketplace 0.9
	 * @access 	private
	 */
	private function _setup_actions() {
		add_action( 'admin_menu', array( $this, 'redirect_marketplace_page' ), 10 );
		add_action( 'admin_menu', array( $this, 'setup_pages' 				), 10 );
		add_action( 'admin_menu', array( $this, 'setup_pages' 				), 10 );
		add_action( 'admin_menu', array( $this, 'hide_main_page'			), 99 );
		add_action( 'admin_menu', array( $this, 'admin_head'				), 10 );
		add_action( 'admin_init', array( $this, 'update_user_rate'			), 10 );
		add_action( 'admin_init', array( $this, 'add_settings'				), 10 );

		return $this;
	}

	/**
	 * Add some admin styles
	 *
	 * @since 	Marketplace 0.9
	 * @access 	public
	 */
	public function admin_head() {
		if( strpos( $this->page, 'marketplace' ) === false )
			return false;

		$image_url  = marketplace()->plugin_url .'assets/images/marketplace-large.png';
		?>
	    <style>
	    #icon-marketplace{
	    	background:url(<?php echo $image_url ?>) no-repeat;
	    }
	    #author-info{
	    	width:70%;
	    	float:left;
	    	margin-top:20px;
	    }
	    <?php if( isset( $_GET['author'] ) && is_numeric( $_GET['author'] ) ) : ?>
	    #posts-filter .search-box{
	    	margin-top:115px;
	    }
	    #marketplace-rate{
	    	width:50px;
	    }
	    <?php endif; ?>
	    .mp-poststuff{
	    	width:200px;
	    	float:left;
	    }
	    .earnings .search-box{
	    	margin-top:60px;
	    }
	    .mp-poststuff .total-earnings{
	    	display:block;
	    	font-size:20px;
	    	font-weight:bold;
	    	text-align:center;
	    	margin:10px 0;
	    }
	    </style>
		<?php
	}

	/**
	 * Hide the main marketplace page
	 *
	 * @since 	Marketplace 0.9
	 * @access 	public
	 */
	public function hide_main_page() {
		remove_submenu_page( marketplace()->folder, marketplace()->folder );
	}

	/**
	 * Redirect main page to the earnings page
	 *
	 * @since 	Marketplace 0.9
	 * @access 	public
	 */
	public function redirect_marketplace_page() {
		global $pagenow;

		if( $pagenow == 'admin.php' && $this->page == marketplace()->folder ) :
			wp_safe_redirect( add_query_arg( array( 'page' => marketplace()->folder .'-earnings' ), admin_url( 'admin.php' ) ) );
			exit;
		endif;
	}

	/**
	 * Set up all admin pages
	 *
	 * @since 	Marketplace 0.9
	 * @access 	public
	 */
	public function setup_pages() {
		$post_type_object = get_post_type_object( 'shop_order' );

		// main page
		add_menu_page(
			__( 'Marketplace', 'marketplace' ),
			__( 'Marketplace', 'marketplace' ),
			$post_type_object->cap->edit_others_posts,
			marketplace()->folder,
			'mp_earnings_admin_page',
			marketplace()->plugin_url .'assets/images/marketplace.png',
			3
		);

		// earnings page
		add_submenu_page(
			marketplace()->folder,
			__( 'Earnings', 'marketplace' ),
			__( 'Earnings', 'marketplace' ),
			$post_type_object->cap->edit_others_posts,
			marketplace()->folder .'-earnings',
			'mp_earnings_admin_page'
		);

		// authors page
		add_submenu_page(
			marketplace()->folder,
			__( 'Authors', 'marketplace' ),
			__( 'Authors', 'marketplace' ),
			$post_type_object->cap->edit_others_posts,
			marketplace()->folder .'-authors',
			$this->is_single_author ? 'mp_single_author_admin_page' : 'mp_authors_admin_page'
		);
	}

	/**
	 * Update a users rate
	 *
	 * @since 	Marketplace 0.9
	 * @access 	public
	 */
	public function update_user_rate() {
		if( ! isset( $_POST['update-marketplace-rate'] ) )
			return false;

		check_admin_referer( 'mp-update-user-rate' );

		$new_rate = (int) $_POST['marketplace-rate'];
		if( $new_rate <= 0 )
			$new_rate = get_option( 'mp_default_rate', 20 );

		$user_id = (int) $_POST['author-id'];

		if( $user_id )
			update_user_meta( $user_id, 'marketplace_user_rate', $new_rate );

		wp_safe_redirect( add_query_arg( 'msg', 'updated' ) );
		exit;
	}

	/**
	 * Add the backend settings
	 *
	 * @since 	Marketplace 0.9
	 * @access 	public
	 */
	public function add_settings() {
		add_settings_section( 'marketplace_settings', __( 'Marketplace Settings', 'marketplace' ), 'mp_settings_section',  'general' );

		add_settings_field( 'mp_default_rate', __( 'Default Marketplace Rate', 	  'marketplace'	), 'mp_default_rate_setting', 	'general', 'marketplace_settings' );
		add_settings_field( 'mp_auto_send',    __( 'Send payments automatically', 'marketplace' ), 'mp_auto_send_setting', 		'general', 'marketplace_settings' );
		add_settings_field( 'mp_user_notice',  __( 'User Notice', 				  'marketplace' ), 'mp_user_notice_setting', 	'general', 'marketplace_settings' );

		register_setting( 'general', 'mp_default_rate', 'intval'  					);
		register_setting( 'general', 'mp_auto_send', 	'mp_validate_auto_send' 	);
		register_setting( 'general', 'mp_user_notice', 	'mp_validate_user_notice' 	);
	}
}

/**
 * Starts the main admin class
 *
 * @since Marketplace 0.9
 */
function mp_admin() {
	marketplace()->admin = new Marketplace_Admin();
}
endif;

/* End of file admin.php */
/* Location: ./admin/admin.php */