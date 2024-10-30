<?php
/*
Plugin Name: iSoftware Blocks
Plugin URI: http://www.isoftware.gr/wordpress/plugins/isw-blocks
Description: This plugin lets you create your own reusable blocks of content.
Version: 1.2.1
Author: iSoftware
Author URI: http://www.isoftware.gr
Text Domain: isw-blocks
Domain Path: /languages
*/

if(defined('ISW_BLOCKS_VERSION')) return;

define( 'ISW_BLOCKS_VERSION', '1.2.1');
define( 'ISW_BLOCKS_PATH', dirname(__FILE__));
define( 'ISW_BLOCKS_URL',  plugins_url('', __FILE__));
define( 'ISW_BLOCKS_PLUGIN_FILE', __FILE__ );
define( 'ISW_BLOCKS_UNDERSCORE', 'isw_block');
define( 'ISW_BLOCKS_TEXTDOMAIN', 'isw-blocks');

if ( !class_exists('ISWBlocksPlugin')):

class ISWBlocksPlugin {
 
	// Singleton
	private static $instance = null;
	public static function get_instance() 
	{
		if ( self::$instance === null )
			self::$instance = new ISWBlocksPlugin();
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() 
	{

		// Register Activation Hook
		register_activation_hook( __FILE__, array( $this,'activate' ) );

		// Register Deactivation Hook
		register_deactivation_hook( __FILE__, array( $this,'deactivate' ) );

		// Localize Plugin
    	add_action( 'plugins_loaded', array( $this, 'localize' ) );

		// Enqueue Scripts on Backend
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles') );

	}

	/**
	 * Destructor
	 */
    function __destruct()
    {
        return;
    }

	/**
	 * Plugin Activation
	 * @return bool Returns true if activation was successful. Otherwise false.
	 */
	public function activate() 
	{

		// Localize plugin
		$this->localize();
		
		// Flush rewrite rules to ensure block posts don't 404
	 	// http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
		flush_rewrite_rules();

		return true;
	}

	/**
	 * Plugin Deactivation
	 * @return bool Returns true if deactivation was successful. Otherwise false.
	 */
	public function deactivate() 
	{

		// Flush rewrite rules to ensure posts don't 404
	 	// http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
		flush_rewrite_rules();

		return true;
	}

	/**
	 * Plugin Localization
	 */
	public function localize() 
	{
		load_plugin_textdomain( ISW_BLOCKS_TEXTDOMAIN, false, basename(ISW_BLOCKS_PATH) . '/languages');
	}

	public function register_styles()
	{

		global $wp_version;
	   	if( version_compare( $wp_version, '3.8', '>=' ) ){
		
			wp_enqueue_style( 'isw-blocks-admin', plugins_url('/css/isw-blocks-admin.v3.8.css', __FILE__) );
	    	wp_enqueue_style( 'isw-blocks-icons', plugins_url('/css/isw-blocks-icons.css', __FILE__) );

  		} else {

	    	wp_enqueue_style( 'isw-blocks-admin', plugins_url('/css/isw-blocks-admin.css', __FILE__) );

    	}

	}

}

require ( ISW_BLOCKS_PATH . '/classes/class-isw-blocks.php' ); // Block object
require ( ISW_BLOCKS_PATH . '/classes/class-isw-block-widget.php' ); // Block Widget object

require ( ISW_BLOCKS_PATH . '/classes/class-isw-options-page.php' ); // Base Options object
require ( ISW_BLOCKS_PATH . '/classes/class-isw-block-options-page.php' ); // Block Settings object

$ISWBlocksPlugin = ISWBlocksPlugin::get_instance();

endif;