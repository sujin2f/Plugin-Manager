<?php
/**
 * Lock
 *
 * project	Plugin Manager
 * version: 4.0.0
 * Author: Sujin 수진 Choi
 * Author URI: http://www.sujinc.com/
 *
*/

namespace PIGPR;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Lock {
	private $option;

	function __construct() {
		$this->option = get_option( '_plugin-manager_', array() );

		add_action( 'wp_ajax_PIGPR_LOCK', array( $this, 'Lock' ) );
		add_action( 'wp_ajax_PIGPR_UNLOCK', array( $this, 'Unlock' ) );

		add_filter( 'network_admin_plugin_action_links' , array( $this, 'AddActionLink' ), 15, 4 );
		add_filter( 'plugin_action_links' , array( $this, 'AddActionLink' ), 15, 4 );
	}

	public function Lock() {
		$plugin_file = $_POST['plugin_file'];
		$locked = get_option( 'plugin_locked' );
		if ( is_array( $locked ) && isset( $locked[$plugin_file] ) ) wp_die();

		$locked[$plugin_file] = true;
		update_option( 'plugin_locked', $locked );
		wp_die();
	}

	public function Unlock() {
		$plugin_file = $_POST['plugin_file'];
		$locked = get_option( 'plugin_locked' );
		if ( is_array( $locked ) && !isset( $locked[$plugin_file] ) ) wp_die();

		unset( $locked[$plugin_file] );
		update_option( 'plugin_locked', $locked );
		wp_die();
	}

	public function AddActionLink( $actions, $plugin_file, $plugin_data, $a ) {
		$text_class = !empty( $this->option[ 'hide_text' ] ) ? 'hidden' : '';

		$class = $this->isLocked( $plugin_file ) ? 'locked' : '';

		$actions['lock'] = sprintf(
			'<a href="#" class="button-unlock button-plugin-manager" data-id="%s" data-plugin_file="%s">
				<span class="dashicons dashicons-unlock"></span>
				<span class="text %s">%s</span>
			</a>

			<a href="#" class="button-lock button-plugin-manager" data-locked="%s" data-id="%s" data-plugin_file="%s">
				<span class="dashicons dashicons-lock"></span>
				<span class="text %s">%s</span>
			</a>',

			sanitize_title( $plugin_data['Name'] ),
			$plugin_file,
			$text_class,
			__( 'Unlock', PIGPR_TEXTDOMAIN ),

			$class,
			sanitize_title( $plugin_data['Name'] ),
			$plugin_file,
			$text_class,
			__( 'Lock', PIGPR_TEXTDOMAIN )
		);

		return $actions;
	}

	/**
	 * is_locked
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 */
	private function isLocked( $plugin_file ) {
		$locked = get_option( 'plugin_locked' );
		if ( is_array( $locked ) && !empty( $locked[$plugin_file] ) ) return true;

		if ( isset( $locked[$plugin_file] ) ) return true;

		return false;
	}
}
