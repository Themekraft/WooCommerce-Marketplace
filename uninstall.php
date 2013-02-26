<?php
/**
 * Handles uninstallation routine
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
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

remove_role( 'marketplace_author' );

/* End of file uninstall.php */
/* Location: ./uninstall.php */