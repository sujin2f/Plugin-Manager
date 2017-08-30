<?php
/**
 * Config class
 *
 * @package Plugin Manager
 * @since   6.0.1
 * @author  Sujin 수진 Choi http://www.sujinc.com/donation
*/

namespace Sujin\Plugin\PluginMgr\Traits;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 404 Not Found" );
	header( "HTTP/1.1 404 Not Found" );
	exit();
}

trait Config {
	public static $_instance;

	protected $messages = array();

	public static function get_instance( $params = null ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $params );
			self::$_instance->__construct__();
		}
		return self::$_instance;
	}

	protected function __construct__() {
		add_action( 'admin_notices', array( $this, '__show_messages' ) );
	}

	public function __show_messages() {
		foreach( $this->messages as $message ) {
			$dismissible = $message['dismissible'] ? 'is-dismissible' : '';
			$text        = esc_html__( $text, SUJIN_PLUGIN_MGR_SLUG );

			printf( '<div class="notice notice-%s %s"><p>%s</a></p></div>', $message['class'], $dismissible, $text );
		}
	}

	protected function show_message( $text, $class = 'info', $dismissible = false ) {
		// error, warning, success, info
		$this->messages[] = array(
			'text'        => $text,
			'class'       => $class,
			'dismissible' => $dismissible,
		);
	}
}
