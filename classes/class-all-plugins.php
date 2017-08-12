<?php
/**
 * All Plugins
 *
 * Manage plugins as groups.
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

namespace Sujin\Plugin\PluginMgr;

use Sujin\Plugin\PluginMgr\Traits\Config;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class All_Plugins extends Plugin_Base {
	use Config;

	/**
	 * Constructor.
	 *
	 * Initialization and Setting the first hooking points.
	 *
	 * @since  0.0.1
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'all_plugins', array( $this, 'modify_all_plugins' ), 20 );
	}

	/**
	 * Modify all plugins.
	 *
	 * Initialization and Setting the first hooking points.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @see  all_plugins filter
	 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @param  array $all_plugins Plugins array.
	 *
	 * @return array Plugins array.
	 */
	public function modify_all_plugins( $all_plugins ) {
		self::block_upgrade();

		if ( empty( $this->group ) )
			return $all_plugins;

		$plugins = Database::get_plugins_by_group( $this->group );

		$new_plugins = array();
		foreach( $all_plugins as $key => $value ) {
			if ( array_key_exists( $key, $plugins ) )
				$new_plugins[ $key ] = $value;
		}

		return $new_plugins;
	}

	public static function block_upgrade() {
		if ( Database::is_tables_exist() )
			return;

		$locked = Database::get_locked();
		$locked = array_map( create_function( '$a', 'return $a[ "file_name" ];' ), $locked );

		$current  = get_site_transient( 'update_plugins' );
		$current_ = $current;

		if ( !isset( $current->response ) )
			return;

		foreach( $current->response as $plugin_key => $v ) {
			if ( in_array( $plugin_key, $locked ) )
				unset( $current_->response[ $plugin_key ] );
		}

		set_site_transient( 'update_plugins', $current_ );
	}
}
