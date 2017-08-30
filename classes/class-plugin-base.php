<?php
/**
 * Base Class to share
 *
 * @package Plugin Manager
 * @since   6.0.1
 * @author  Sujin 수진 Choi http://www.sujinc.com/donation
*/

namespace Sujin\Plugin\PluginMgr;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

abstract class Plugin_Base {
	/**
	 * Test Mode
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @const bool TEST_MODE
	 */
	const TEST_MODE = false;

	/**
	 * Selected Group ID
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @var int $group
	 */
	protected $group = false;

	public function __construct() {
		if ( !empty( $_REQUEST['group'] ) )
			$this->group = $_REQUEST['group'];

		if ( self::TEST_MODE ) {
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}
	}
}
