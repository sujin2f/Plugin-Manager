<?php
/**
 * Lock
 *
 * project	Plugin Manager
 * version: 3.0.1
 * Author: Sujin 수진 Choi
 * Author URI: https://www.facebook.com/WP-developer-Sujin-1182629808428000/
 *
*/

namespace PIGPR;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class  Lock {
	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	function __construct() {
		add_action( 'wp_ajax_PIGPR_LOCK', array( $this, 'lock' ) );
		add_action( 'wp_ajax_PIGPR_UNLOCK', array( $this, 'unlock' ) );

		global $pagenow;
		if ( $pagenow !== "plugins.php" ) return false;

		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
	}

	public function lock() {
		$plugin_file = $_POST['plugin_file'];
		$locked = get_option( 'plugin_locked' );
		if ( is_array( $locked ) && isset( $locked[$plugin_file] ) ) wp_die();

		$locked[$plugin_file] = true;
		update_option( 'plugin_locked', $locked );
		wp_die();
	}

	public function unlock() {
		$plugin_file = $_POST['plugin_file'];
		$locked = get_option( 'plugin_locked' );
		if ( is_array( $locked ) && !isset( $locked[$plugin_file] ) ) wp_die();

		unset( $locked[$plugin_file] );
		update_option( 'plugin_locked', $locked );
		wp_die();
	}

	public function wp_loaded() {
 		add_filter( 'network_admin_plugin_action_links' , array( $this, 'plugin_action_link' ), 15, 4 );
		add_filter( 'plugin_action_links' , array( $this, 'plugin_action_link' ), 15, 4 );
	}

	public function plugin_action_link( $actions, $plugin_file, $plugin_data, $a ) {
 		if ( $this->is_locked( $plugin_file ) ) {
			$actions['lock'] = sprintf( '<a href="#" class="button-unlock" data-id="%s" data-plugin_file="%s"><span class="dashicons dashicons-unlock"></span> %s</a>', sanitize_title( $plugin_data['Name'] ), $plugin_file, __( 'Unlock', PIGPR_TEXTDOMAIN ) );
 		} else {
			$actions['lock'] = sprintf( '<a href="#" class="button-lock" data-id="%s" data-plugin_file="%s"><span class="dashicons dashicons-lock"></span> %s</a>', sanitize_title( $plugin_data['Name'] ), $plugin_file, __( 'Lock', PIGPR_TEXTDOMAIN ) );
 		}

		return $actions;
	}

	/**
	 * is_locked
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 */
	private function is_locked( $plugin_file ) {
		$locked = get_option( 'plugin_locked' );
		if ( is_array( $locked ) && !empty( $locked[$plugin_file] ) ) return true;

		if ( isset( $locked[$plugin_file] ) ) return true;

		return false;
	}
}
