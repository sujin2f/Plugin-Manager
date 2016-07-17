<?php
/**
 *
 * Hide
 *
 * project	Plugin Manager
 * version: 3.0.1
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

class Hide {
	private $show_hidden = false;

	function __construct() {
		// Ajax
		add_action( 'wp_ajax_PIGPR_HIDE', array( $this, 'AjaxHide' ) );
		add_action( 'wp_ajax_PIGPR_SHOW', array( $this, 'AjaxShow' ) );

		// 액션 링크
		add_filter( 'network_admin_plugin_action_links' , array( $this, 'AddActionLink' ), 15, 4 );
		add_filter( 'plugin_action_links' , array( $this, 'AddActionLink' ), 15, 4 );

/*
		add_filter( 'views_plugins',array( $this, 'AddHiddenToMenu' ) );
		add_filter( 'all_plugins', array( $this, 'GetHiddenPluginsQuery' ) );
*/
	}

/*
	public function GetHiddenPluginsQuery( $plugins ) {
		if ( !empty( $_GET[ 'plugin_status' ] ) && $_GET[ 'plugin_status' ] == 'hidden' ) {
			$this->show_hidden = true;

			$hidden_plugins = get_option( 'plugin_hidden' );

			$plugins_ = array();
			$plugin_info = get_site_transient( 'update_plugins' );

			foreach ( (array) $plugins as $plugin_file => $plugin_data ) {
				if ( isset( $plugin_info->no_update[ $plugin_file ] ) ) {
					$plugins[ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->no_update[ $plugin_file ], $plugin_data );
				}
			}

			foreach( $plugins as $key => $plugin_data ) {
				if ( array_key_exists( $key, $hidden_plugins ) ) {
					$plugins_[$key] = $plugin_data;
				}
			}

			return $plugins_;
		}

		return $plugins;
	}
*/

/*
	public function AddHiddenToMenu( $views ) {
		$nof_hidden = is_array( get_option( 'plugin_hidden' ) ) ? count( get_option( 'plugin_hidden' ) ) : 0;

		$class_current = ( $this->show_hidden ) ? 'class="current"' : '';

		$views[ 'view_hidden' ] =  sprintf('<a href="plugins.php?plugin_status=hidden" %s>Hidden <span class="count">(%s)</span></a>', $class_current, $nof_hidden );
		return $views;
	}
*/

	public function AjaxHide() {
		$plugin_file = $_POST['plugin_file'];
		$hidden_plugins = get_option( 'plugin_hidden' );
		if ( is_array( $hidden_plugins ) && isset( $hidden_plugins[ $plugin_file ] ) ) wp_die();

		$hidden_plugins[ $plugin_file ] = true;
		update_option( 'plugin_hidden', $hidden_plugins );
		wp_die();
	}

	public function AjaxShow() {
		$plugin_file = $_POST['plugin_file'];
		$hidden_plugins = get_option( 'plugin_hidden' );
		if ( is_array( $hidden_plugins ) && !isset( $hidden_plugins[ $plugin_file ] ) ) wp_die();

		unset( $hidden_plugins[ $plugin_file ] );
		update_option( 'plugin_hidden', $hidden_plugins );
		wp_die();
	}

	public function AddActionLink( $actions, $plugin_file, $plugin_data, $a ) {
		$text_class = ( $GLOBALS[ 'PIGPR' ]->ScreenOption->hide_text ) ? 'hidden' : '';

		if ( $this->isHidden( $plugin_file ) )
			$actions['hide'] = sprintf(
				'<a href="#" class="button-show button-plugin-manager" data-id="%s" data-plugin_file="%s"><span class="dashicons dashicons-visibility"></span><span class="text %s">%s</span></a>',
				sanitize_title( $plugin_data['Name'] ),
				$plugin_file,
				$text_class,
				__( 'Show', PIGPR_TEXTDOMAIN )
			);
		else
			$actions['hide'] = sprintf(
				'<a href="#" class="button-hide button-plugin-manager" data-id="%s" data-plugin_file="%s"><span class="dashicons dashicons-hidden"></span><span class="text  %s">%s</span></a>',
				sanitize_title( $plugin_data['Name'] ),
				$plugin_file,
				$text_class,
				__( 'Hide', PIGPR_TEXTDOMAIN )
			);

		return $actions;
	}

	private function isHidden( $plugin_file ) {
		$hidden_plugins = get_option( 'plugin_hidden' );
		if ( is_array( $hidden_plugins ) && !empty( $hidden_plugins[ $plugin_file ] ) ) return true;

		if ( isset( $hidden_plugins[ $plugin_file ] ) ) return true;

		return false;
	}
}
