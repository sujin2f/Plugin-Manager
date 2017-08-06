<?php
/**
 * Group
 *
 * Manage plugins as groups.
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

namespace PLGINMNGRPRO;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Group {
	/**
	 * Init Object
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private $init;

	/**
	 * Original Plugins Array
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private $all_plugins;

	/**
	 * Constructor.
	 *
	 * Initialization and Setting the first hooking points.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return void
	 *
	 * @todo   Delete if parent isn't used.
	 */
	public function __construct( Init $init ) {
		$this->init = $init;

		$this->add_ajax_filters();

		// Plugin data handling
// 		add_filter( 'all_plugins', array( $this, 'modify_all_plugins' ), 20 );
/*

		// Delete Group
		if ( !empty( $_GET[ 'mode' ] ) && !empty( $_GET[ 'group' ] ) && $_GET[ 'mode' ] == 'delete_group' ) {
			add_action( 'init', array( $this, 'DeleteGroup' ) );
		}
*/
	}

	/**
	 * Ajax Filters.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return void.
	 */
	private function add_ajax_filters() {
		add_action( 'wp_ajax_PLGINMNGRPRO_DELETE_FROM_GROUP',    array( $this, 'AjaxDeleteFromGroup' ) );

		add_action( 'wp_ajax_Plugin Manager Pro : Change Colour', array( $this, 'ajax_set_Group_colour' ) );
	}



	public function DeleteGroup() {
		unset( $this->plugin_groups[ $_GET[ 'group' ] ] );
		unset( $this->groups_plugin_match[ $_GET[ 'group' ] ] );

		$plugin_groups_match = $this->plugin_groups_match;
		foreach( $this->plugin_groups_match as $plugin_key => $plugin_value ) {
			foreach( $plugin_value as $group_key => $group_value ) {
				if ( $group_key == $_GET[ 'group' ] ) {
					unset( $plugin_groups_match[ $plugin_key ][ $group_key ] );
				}
			}

			if ( !count( $plugin_groups_match[ $plugin_key ] ) )
				unset( $plugin_groups_match[ $plugin_key ] );
		}

		update_option( 'plugin_groups', $this->plugin_groups );
		update_option( 'plugin_groups_match', $plugin_groups_match );
		update_option( 'groups_plugin_match', $this->groups_plugin_match );

		wp_redirect( remove_query_arg( array( 'mode', 'group' ) ) );
	}

	public function AjaxDeleteFromGroup() {
		$group_id = $_REQUEST[ 'group_id' ];
		$group_name = $_REQUEST[ 'group_name' ];
		$plugin_id = $_REQUEST[ 'plugin_id' ];

		unset( $this->plugin_groups_match[$plugin_id][$group_id] );
		if ( !count( $this->plugin_groups_match[$plugin_id] ) )
			unset( $this->plugin_groups_match[$plugin_id] );

		update_option( 'plugin_groups_match', $this->plugin_groups_match );

		unset( $this->groups_plugin_match[$group_id][$plugin_id] );
		if ( !count( $this->groups_plugin_match[$group_id] ) )
			unset( $this->groups_plugin_match[$group_id] );

		update_option( 'groups_plugin_match', $this->groups_plugin_match );

		wp_die();
	}

	public function ajax_set_group_colour() {
		$group_id = $_REQUEST['group_id'];
		$colour   = $_REQUEST['colour'];

		if ( $this->init->database->set_group_colour( $group_id, $colour ) ) {
			echo json_encode( array(
				'bgcolour' => $colour,
				'colour'   => $this->get_contrast_colour( $colour )
			) );
		} else {
			echo json_encode( array( 'error' => __( 'Something went wrong.', PLGINMNGRPRO_TEXTDOMAIN ) ) );
		}


		wp_die();
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
	 * @return array Plugins array.
	 */
/*
	public function modify_all_plugins( $plugins ) {
		$this->all_plugins = $plugins;

		if ( !empty( $_GET[ 'plugin_group' ] ) && $_GET[ 'plugin_group' ] == 'not_in_any_groups' ) {
			$plugins_ = $plugins;

			foreach( $plugins as $key => $plugin ) {
				foreach( $this->init->database->plugin_groups_match as $key_opt => $plugin_opt ) {
					if ( $key == $key_opt )
						unset( $plugins_[ $key ] );
				}
			}
			return $plugins_;

		} elseif ( !empty( $_GET[ 'plugin_group' ] ) ) {
			$plugins_ = array();

			foreach( $this->init->database->groups_plugin_match[ $_GET[ 'plugin_group' ] ] as $group_key => $value ) {
				foreach( $plugins as $key => $plugin ) {
					if ( $key == $group_key )
						$plugins_[ $key ] = $plugin;
				}
			}
			return $plugins_;
		}

		return $plugins;
	}
*/


}
