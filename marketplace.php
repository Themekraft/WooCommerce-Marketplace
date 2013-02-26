<<<<<<< HEAD
<?php
/**
 * The Marketplace Plugin for Themekraft
 *
 * @package		Marketplace
 * @subpackage	Main
 * @author		Boris Glumpler
 * @copyright 	Copyright (c) 2010 - 2012, Themekraft
 * @link		https://shabushabu.eu
 * @license		http://www.opensource.org/licenses/gpl-3.0.php GPL License
 * @since 		Marketplace 0.9
 * @filesource
 *
 * Plugin Name:	Marketplace for Woocommerce
 * Plugin URI:	https://github.com/Themekraft/Marketplace
 * Description:	Turns a Woocommerce/Woocommerce for BuddyPress installation into a fully functional marketplace
 * Author: 		Marketplace Development Team
 * Version: 	0.9.1
 * Author URI: 	https://github.com/Themekraft/Marketplace
 * Text Domain: marketplace
 * Domain Path: assets/languages/
 * License: 	GPL3
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Marketplace' ) ) :
/**
 * Main Marketplace Class
 *
 * @since Marketplace 0.9
 */
final class Marketplace
{
	/**
	 * To prevent unauthorized access, Marketplace variables are stored
	 * in a private array that is magically updated using PHP 5.2+
	 * methods. This is to prevent third party plugins from tampering with
	 * essential information indirectly, which would cause issues later.
	 *
	 * @see 		Marketplace::setup_globals()
	 * @var 		array
	 * @since 		Marketplace 0.9
	 */
	private $_data = array();

	/**
	 * Marketplace instance. There can only be one!!
	 *
	 * @staticvar 	object
	 * @since 		Marketplace 0.9
	 */
	private static $_instance;

	/**
	 * Marketplace Instance
	 *
	 * Makes sure that there is only ever 1 instance of Marketplace
	 *
	 * @since 		Marketplace 0.9
	 */
	public static function instance() {
		if( ! self::$_instance instanceof self ) {
			self::$_instance = new self();
			self::$_instance->_setup_globals()
							->_includes()
							->_setup_admin()
							->_setup_actions();
		}

		return self::$_instance;
	}

	/**
	 * A dummy constructor to prevent Marketplace from being loaded more than once.
	 *
	 * @since 	Marketplace 0.9
	 * @see 	Marketplace::instance()
	 */
	private function __construct() {
		// Do nothing here
	}

	/**
	 * A magic dummy method to prevent Marketplace from being cloned
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __clone() {
		 _doing_it_wrong( __METHOD__, __( 'Cheatin&#8217; huh?', 'marketplace' ), '0.9' );
	}

	/**
	 * A magic dummy method to prevent Marketplace from being unserialized
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __wakeup() {
		 _doing_it_wrong( __METHOD__, __( 'Cheatin&#8217; huh?', 'marketplace' ), '0.9' );
	}

	/**
	 * A magic method to check for extra set properties
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __isset( $key ) {
		 return isset( $this->_data[$key] );
	}

	/**
	 * A magic method to get extra properties
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __get( $key ) {
		 return isset( $this->_data[$key] ) ? $this->_data[$key] : null;
	}

	/**
	 * A magic method to set extra properties
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __set( $key, $value ) {
		 $this->_data[$key] = $value;
	}

	/**
	 * Set some smart defaults to class variables.
	 *
	 * @since 		Marketplace 0.9
	 */
	private function _setup_globals() {
		$this->version    	= '0.9.1';
		$this->db_version 	= '100';

		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->folder	  = dirname( $this->basename );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url ( $this->file );

		return $this;
	}

	/**
	 * Set some actions
	 *
	 * @since 		Marketplace 0.9.1
	 */
	private function _setup_actions() {
		add_action( 'bp_setup_components', array( $this, 'start' ), 10 );

		return $this;
	}

	/**
	 * Set up the admin area
	 *
	 * @since 		Marketplace 0.9.1
	 */
	private function _setup_admin() {
		if( ! is_admin() )
			return $this;

		$files = array(
			'actions',
			'admin'
		);

		foreach( $files as $file )
			require $this->plugin_dir .'admin/'. $file .'.php';

		return $this;
	}

	/**
	 * Load files
	 *
	 * @since 		Marketplace 0.9.1
	 */
	private function _includes() {
		$files = array(
			'paypal',
			'template',
			'functions'
		);

		foreach( $files as $file )
			require $this->plugin_dir .'core/'. $file .'.php';

		return $this;
	}

	/**
	 * Start the application
	 *
	 * @since 		Marketplace 0.9.1
	 */
	public function start() {
		require $this->plugin_dir .'core/component.php';
	}
}

/**
 * Provides a convenient short way to access the Marketplace instance
 *
 * @since 	Marketplace 0.9
 * @see		Marketplace::instance()
 * @return	object
 */
function marketplace() {
	return Marketplace::instance();
}

/**
 * Hook Marketplace early onto the 'plugins_loaded' action to give
 * other plugins the chance to load earlier.
 */
if( defined( 'MARKETPLACE_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'marketplace', (int) MARKETPLACE_LATE_LOAD );

} else {
	marketplace();
}

endif;

/**
 * Handles activation
 *
 * It would be better if Woocommerce would provide custom capabilities for products
 * for more fine-grained control. At the moment Woocommerce only provides 1 capability
 * (manage_woocommerce_products) to manage everything.
 *
 * @since 	Marketplace 0.9
 */
function marketplace_activate() {
	add_role( 'marketplace_author', 'Marketplace Author', array(
		'manage_woocommerce_products' 	=> true,
		'read'							=> true,
		'upload_files'					=> true
	) );
}
register_activation_hook( __FILE__, 'marketplace_activate' );

/* End of file marketplace.php */
=======
<?php
/**
 * The Marketplace Plugin for Themekraft
 * 
 * @package		Marketplace
 * @subpackage	Main
 * @author		Boris Glumpler
 * @copyright 	Copyright (c) 2010 - 2012, Themekraft
 * @link		https://shabushabu.eu
 * @license		http://www.opensource.org/licenses/gpl-3.0.php GPL License
 * @since 		Marketplace 0.9
 * @filesource 
 *
 * Plugin Name:	Marketplace for Woocommerce
 * Plugin URI:	https://github.com/Themekraft/Marketplace
 * Description:	Turns a Woocommerce/Woocommerce for BuddyPress installation into a fully functional marketplace
 * Author: 		Marketplace Development Team
 * Version: 	0.9
 * Author URI: 	https://github.com/Themekraft/Marketplace
 * Text Domain: marketplace
 * Domain Path: assets/languages/
 * License: 	GPL3
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Marketplace' ) ) :
/**
 * Main Marketplace Class
 *
 * @since Marketplace 0.9
 */
final class Marketplace
{
	/**
	 * To prevent unauthorized access, Marketplace variables are stored 
	 * in a private array that is magically updated using PHP 5.2+
	 * methods. This is to prevent third party plugins from tampering with
	 * essential information indirectly, which would cause issues later.
	 *
	 * @see 		Marketplace::setup_globals()
	 * @var 		array
	 * @since 		Marketplace 0.9
	 */
	private $_data = array();
	
	/**
	 * Marketplace instance. There can only be one!!
	 * 
	 * @staticvar 	object
	 * @since 		Marketplace 0.9
	 */
	private static $_instance;

	/**
	 * Marketplace Instance
	 * 
	 * Makes sure that there is only ever 1 instance of Marketplace
	 * 
	 * @since 		Marketplace 0.9
	 */
	public static function instance() {
		if( ! self::$_instance instanceof self ) {
			self::$_instance = new self();
			self::$_instance->_setup_globals()
							->_includes();
		}
		
		return self::$_instance;
	}

	/**
	 * A dummy constructor to prevent Marketplace from being loaded more than once.
	 *
	 * @since 	Marketplace 0.9
	 * @see 	Marketplace::instance()
	 */
	private function __construct() {
		// Do nothing here
	}

	/**
	 * A magic dummy method to prevent Marketplace from being cloned
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __clone() {
		 _doing_it_wrong( __METHOD__, __( 'Cheatin&#8217; huh?', 'marketplace' ), '0.9' ); 
	}

	/**
	 * A magic dummy method to prevent Marketplace from being unserialized
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __wakeup() {
		 _doing_it_wrong( __METHOD__, __( 'Cheatin&#8217; huh?', 'marketplace' ), '0.9' ); 
	}

	/**
	 * A magic method to check for extra set properties
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __isset( $key ) {
		 return isset( $this->_data[$key] );
	}

	/**
	 * A magic method to get extra properties
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __get( $key ) {
		 return isset( $this->_data[$key] ) ? $this->_data[$key] : null; 
	}

	/**
	 * A magic method to set extra properties
	 *
	 * @since 	Marketplace 0.9
	 */
	public function __set( $key, $value ) {
		 $this->_data[$key] = $value; 
	}

	/**
	 * Set some smart defaults to class variables.
	 *
	 * @since 		Marketplace 0.9
	 */
	private function _setup_globals() {
		$this->version    	= '0.9';
		$this->db_version 	= '100';
		
		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->folder	  = dirname( $this->basename );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url ( $this->file );

		return $this;
	}
	
	/**
	 * Include some files
	 *
	 * @since 		Marketplace 0.9
	 */
	private function _includes() {
		$files = array(
			'core/paypal',
			'core/template',
			'core/component',
			'core/functions'
		);
		
		if( is_admin() ) :
			$files[] = 'admin/actions';
			$files[] = 'admin/admin';
		endif;
			
		foreach( $files as $file )
			require $this->plugin_dir . $file .'.php';
			
		return $this;
	}
}

/**
 * Provides a convenient short way to access the Marketplace instance
 *
 * @since 	Marketplace 0.9
 * @see		Marketplace::instance()
 * @return	object
 */
function marketplace() {
	return Marketplace::instance();
}

/**
 * Hook Marketplace early onto the 'plugins_loaded' action to give
 * other plugins the chance to load earlier.
 */
if( defined( 'MARKETPLACE_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'marketplace', (int) MARKETPLACE_LATE_LOAD );

} else {
	marketplace();
}

endif;

/**
 * Handles activation
 * 
 * It would be better if Woocommerce would provide custom capabilities for products
 * for more fine-grained control. At the moment Woocommerce only provides 1 capability
 * (manage_woocommerce_products) to manage everything.
 *
 * @since 	Marketplace 0.9
 */
function marketplace_activate() {
	add_role( 'marketplace_author', 'Marketplace Author', array(
		'manage_woocommerce_products' 	=> true,
		'read'							=> true,
		'upload_files'					=> true
	) );
}
register_activation_hook( __FILE__, 'marketplace_activate' );

/* End of file marketplace.php */
>>>>>>> c5d7ed7160c0f3b501d58c37f8105a6f29aa7fba
/* Location: ./marketplace.php */