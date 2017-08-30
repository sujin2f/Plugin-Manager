<?php
/**
 * Option Controller
 *
 * @package Plugin Manager
 * @since   6.0.1
 * @author  Sujin 수진 Choi http://www.sujinc.com/donation
*/

namespace Sujin\Plugin\PluginMgr;

use Sujin\Plugin\PluginMgr\Traits\Config;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Option {
	use Config;

	/**
	 * Option Key
	 *
	 * @since  0.0.1
	 * @access private static
	 */
	private static $option_key;

	/**
	 * Settings
	 *
	 * @since  0.0.1
	 * @access private static
	 */
	private static $settings;

	private static $default_option = array(
		'hide_text' => false,
	);

	/**
	 * Get Options.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @return array Settings array.
	 */
	public static function get( $key = false ) {
		self::$option_key = sprintf( '_plugin-manager-%s_', get_current_blog_id() );

		self::$settings = get_user_meta( get_current_user_id(), self::$option_key, true );

		if ( !self::$settings )
			self::$settings = self::$default_option;

		if ( $key )
			return self::$settings[ $key ];

		return self::$settings;
	}

	/**
	 * Get Options.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @return array Settings array.
	 */
	public static function set( $array = false ) {
		$request = $array;

		self::$option_key = sprintf( '_plugin-manager-%s_', get_current_blog_id() );
		self::$settings = self::get();

		if ( ! $request )
			$request = $_REQUEST;

		self::$settings['hide_text'] = ( $request['hide_text'] == 'true' ) ? true : false;

		self::update_option();

		if ( ! $array ) {
			echo json_encode( self::$settings );
			die;
		}
	}

	/**
	 * Update Option.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return void
	 */
	public static function update_option() {
		update_user_meta( get_current_user_id(), self::$option_key, self::$settings );
	}
}
