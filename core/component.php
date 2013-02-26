<?php
/**
 * Holds the marketplace component
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

class Marketplace_Component extends BP_Component
{
    /**
     * Holds the ID of the component
	 *
	 * @var		string
     * @since   Marketplace 0.9
     */
	public $id = 'marketplace';

    /**
     * Start the shop component creation process
     *
     * @since     Marketplace 0.9
     */
    public function __construct() {
        parent::start( $this->id, __( 'Marketplace', 'marketplace' ), marketplace()->plugin_dir );
    }

    /**
     * Setup globals
     *
     * @since     Marketplace 0.9
     * @global    object    $bp		The one true BuddyPress instance
     */
    public function setup_globals() {
        global $bp;

        $globals = array(
            'path'          => marketplace()->plugin_dir .'core',
            'slug'          => 'marketplace',
            'has_directory' => false
        );

        parent::setup_globals( $globals );
    }

    /**
     * Setup BuddyBar navigation
     *
     * @since    Marketplace 0.9
     */
    public function setup_nav() {
    	if( ! current_user_can( 'marketplace_author' ) )
			return false;

        // Add 'Eearnings' to the main navigation
        $main_nav = array(
            'name'                      => __( 'Earnings', 'marketplace' ),
            'slug'                      => 'earnings',
            'position'                  => 75,
            'screen_function'           => 'mp_earnings_template',
            'default_subnav_slug'       => '',
            'item_css_id'               => $this->id,
            'show_for_displayed_user'	=> false
        );

        // Add 'Marketplace' to the settings navigation
		$sub_nav[] = array(
			'name' 				=> __( 'Marketplace', 'events' ),
			'slug' 				=> 'marketplace',
			'parent_url' 		=> bp_loggedin_user_domain() . bp_get_settings_slug() . '/',
			'parent_slug' 		=> bp_get_settings_slug(),
			'screen_function' 	=> 'mp_settings_template',
			'position' 			=> 46,
			'item_css_id' 		=> 'settings-marketplace',
			'user_has_access' 	=> bp_is_my_profile()
		);

        do_action( 'bp_marketplace_setup_nav' );

        parent::setup_nav( $main_nav, $sub_nav );
    }
}

/**
 * Sets up the component
 *
 * @since   Marketplace 0.9.1
 */
function mp_setup_component() {
	global $bp;

	$bp->marketplace = new Marketplace_Component();
}
add_action( 'bp_setup_components', 'mp_setup_component', 11 );

/* End of file component.php */
/* Location: ./core/component.php */