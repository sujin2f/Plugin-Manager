<?php
/**
 * Ajax Controller
 *
 * @package Plugin Manager
 * @since   6.0.0
 * @author  Sujin 수진 Choi http://www.sujinc.com/donation
*/

namespace Sujin\Plugin\PluginMgr;

use Sujin\Plugin\PluginMgr\Traits\Config;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Ajax {
	use Config;

	/*
	 * Constructor.
	 *
	 * Initialization and Setting the first hooking points.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_Plugin Manager Pro : Create Group',             array( $this, 'exercute_ajax_reauest' ) );

		add_action( 'wp_ajax_Plugin Manager Pro : Lock Plugin',              array( $this, 'exercute_ajax_reauest' ) );
		add_action( 'wp_ajax_Plugin Manager Pro : Hide Plugin',              array( $this, 'exercute_ajax_reauest' ) );

		add_action( 'wp_ajax_Plugin Manager Pro : Toggle Group-Plugin Link', array( $this, 'exercute_ajax_reauest' ) );

		add_action( 'wp_ajax_Plugin Manager Pro : Delete Group',             array( $this, 'exercute_ajax_reauest' ) );
		add_action( 'wp_ajax_Plugin Manager Pro : Edit Group',               array( $this, 'exercute_ajax_reauest' ) );
		add_action( 'wp_ajax_Plugin Manager Pro : Set Order',                array( $this, 'exercute_ajax_reauest' ) );

		add_action( 'wp_ajax_Plugin Manager Pro : Update Settings',          array( 'Sujin\Plugin\PluginMgr\Option',  'set' ) );
	}

	/**
	 * All Ajax Goes to here
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return void.
	 */
	public function exercute_ajax_reauest() {
		switch( $_REQUEST[ 'action' ] ) {
			case 'Plugin Manager Pro : Create Group' :
				$this->create_group();
				break;

			case 'Plugin Manager Pro : Lock Plugin' :
				$this->lock_plugin();
				break;

			case 'Plugin Manager Pro : Hide Plugin' :
				$this->hide_plugin();
				break;

			case 'Plugin Manager Pro : Toggle Group-Plugin Link' :
				$this->toggle_relationship();
				break;

			case 'Plugin Manager Pro : Delete Group' :
				$this->delete_group();
				break;

			case 'Plugin Manager Pro : Edit Group' :
				$this->edit_group();
				break;

			case 'Plugin Manager Pro : Set Order' :
				$this->set_order();
				break;
		}

		echo json_encode( Database::get_json_array( $_REQUEST[ 'plugin_group' ] ) );
		wp_die();
	}

	/**
	 * Create Group
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @return void.
	 */
	private function create_group() {
		$plugin_id   = $_REQUEST[ 'plugin_id' ];
		$group_name  = esc_html( $_REQUEST[ 'name' ] );
		$description = esc_html( $_REQUEST[ 'description' ] );
		$colour      = $_REQUEST[ 'colour' ];

		Database::create_group( array(
			'group_name'  => $group_name,
			'description' => $description,
			'colour'      => $colour,
			'plugin_id'   => $plugin_id,
			'hidden_main' => ( $_REQUEST[ 'hidden_main' ] == "true" ) ? 1 : 0,
		));
	}

	/**
	 * Lock Plugin
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @filter plugin_group_default_colour Default colour
	 *
	 * @return void.
	 */
	private function lock_plugin() {
		$plugin_id = $_REQUEST[ 'plugin_id' ];

		Database::toggle_lock_plugin( $plugin_id );
	}

	/**
	 * Hide Plugin
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @filter plugin_group_default_colour Default colour
	 *
	 * @return void.
	 */
	private function hide_plugin() {
		$plugin_id = $_REQUEST[ 'plugin_id' ];

		Database::toggle_hide_plugin( $plugin_id );
	}

	/**
	 * Toggle Group-Plugin Relationship
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private function toggle_relationship() {
		$plugin_ids = $_REQUEST[ 'plugin_id' ];
		$group_id   = $_REQUEST[ 'group_id' ];
		$checked    = $_REQUEST[ 'checked' ];

		$plugin_ids = explode( '*!.*!', $plugin_ids );

		foreach( $plugin_ids as $plugin_id ) {
			Database::toggle_relationship( $group_id, $plugin_id, $checked );
		}
	}

	/**
	 * Delete Group
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private function delete_group() {
		Database::delete_group( $_REQUEST );
	}

	/**
	 * Edit Group
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @return void.
	 */
	private function edit_group() {
		Database::edit_group( $_REQUEST );
	}

	/**
	 * Set Order
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private function set_order() {
		$orders = $_REQUEST[ 'orders' ];
		Database::set_order( $orders );
	}
}
