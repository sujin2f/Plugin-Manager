<?php
/**
 * Modal Controller
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

class Modal {
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
		add_action( 'admin_footer', array( $this, 'print_modal' ), 20 );
	}

	/**
	 * Print modal.
	 *
	 * @since  0.0.1
	 * @access public
	 */
	public function print_modal() {
		include_once( SUJIN_PLUGIN_MGR_PATH . '/templates/modal.php' );
	}
}
