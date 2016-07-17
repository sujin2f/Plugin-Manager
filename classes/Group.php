<?php
/**
 * Group
 *
 * project	Plugin Manager
 * version: 3.0.3
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

class Group {
	private $is_group_query;
	private $default_color = '#666666';

	private $plugin_groups;
	private $plugin_groups_match;
	private $groups_plugin_match;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	function __construct() {
		$this->upgrade();

		$this->plugin_groups = get_option( 'plugin_groups' );
		$this->plugin_groups_match = get_option( 'plugin_groups_match' );
		$this->groups_plugin_match = get_option( 'groups_plugin_match' );

		# AJAX
		add_action( 'wp_ajax_PIGPR_CREATE_GROUP', array( $this, 'create_group' ) );
		add_action( 'wp_ajax_PIGPR_INPUT_INTO_GROUP', array( $this, 'input_into_group' ) );
		add_action( 'wp_ajax_PIGPR_DELETE_FROM_GROUP', array( $this, 'delete_from_group' ) );
		add_action( 'wp_ajax_PIGPR_SET_GROUP_COLOR', array( $this, 'set_group_color' ) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		$this->is_group_query = !empty( $_GET['plugin_group'] );

		# Hooks
		add_filter( "views_plugins", array( $this, 'views_plugins' ) );
		add_filter( "views_plugins-network", array( $this, 'views_plugins' ) );

		// Group Buttons
		add_filter( 'network_admin_plugin_action_links' , array( $this, 'plugin_action_link' ), 15, 4 );
		add_filter( 'plugin_action_links' , array( $this, 'plugin_action_link' ), 15, 4 );
		add_action( 'pre_current_active_plugins', array( $this, 'print_modal' ) );

		add_filter( "plugin_row_meta", array( $this, 'print_groups' ), 15, 3 );

		if ( $this->is_group_query ) {
			add_filter( 'all_plugins', array( $this, 'all_plugins' ) );
			add_action( 'admin_footer', array( $this, 'print_group_information' ) );
			add_action( 'wp_loaded', array( $this, 'delete_group' ) );
		}
	}

	/**
	 * Filter[plugin_action_links] : Group Buttons
	 *
	 * @since 1.5.2
	 * @access public
	 *
	 */
	public function plugin_action_link( $actions, $plugin_file, $plugin_data, $a ) {
		$actions['group'] = sprintf( '<a href="#" class="button-grouping" data-id="%s"><span class="dashicons dashicons-groups"></span> %s</a>',
			( isset( $plugin_data[ 'slug' ] ) && $plugin_data[ 'slug' ] ) ? $plugin_data[ 'slug' ] : sanitize_title( $plugin_data['Name'] ),
			__( 'Group', PIGPR_TEXTDOMAIN )
		);

		return $actions;
	}

	/**
	 * Filter[plugin_row_meta] : Print Groups set on each Plugins.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param: (array) $plugin_meta, (string) $plugin_file, (array) $plugin_data
	 * @return: (array) $plugin_meta
	 *
	 * see	/wp-admin/includes/class-wp-list-table.php
	 */
	public function print_groups( $plugin_meta, $plugin_file, $plugin_data ) {
		$group_info = get_option( 'plugin_groups' );
		$groups = get_option( 'plugin_groups_match' );

		$slug = ( isset( $plugin_data[ 'slug' ] ) && $plugin_data[ 'slug' ] ) ? $plugin_data[ 'slug' ] : sanitize_title( $plugin_data['Name'] );

		echo '<div class="groups">';

		if ( !empty( $this->plugin_groups_match[$slug] ) ) {
			foreach( $this->plugin_groups_match[$slug] as $key => $name ) {
				$background_color = $this->plugin_groups[$key]['color'];
				$color = $this->get_contrast_color( $background_color );

				printf( '<a href="%s?plugin_group=%s" style="background-color:%s; color:%s" data-id="%s" data-bgcolor="%s" data-color="%s">%s</a>', $this->get_plugins_url(), $key, $background_color, $color, $key, $background_color, $color, $name );
			}
		}
		echo '</div>';

		return $plugin_meta;
	}

	/**
	 * Action[admin_footer] : Print Selected Group Name in Input From.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_group_information() {
		$group_key = $_GET['plugin_group'];

		printf( '<input type="hidden" id="plugin_group_name" value="%s" />', $this->plugin_groups[$group_key]['name'] );
	}

	/**
	 * Action[init] : Delete Selected Group.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function delete_group() {
		if ( empty( $_GET['action'] ) || empty( $_GET['group_id'] ) ) return false;

		$action = $_GET['action'];
		$group_id = strtolower( urlencode( $_GET['group_id'] ) );

		if ( $action != 'delete_group' || empty( $group_id ) ) return false;

		$plugin_groups_match_replace = $this->plugin_groups_match;

		unset( $this->plugin_groups[$group_id] );
		unset( $this->groups_plugin_match[$group_id] );

		foreach ( $this->plugin_groups_match as $plugin_key => $plugin_groups ) {
			foreach( $plugin_groups as $group_key => $value ) {
				if ( $group_key == $group_id ) {
					unset( $plugin_groups_match_replace[$plugin_key][$group_key] );
				}
			}
		}

		update_option( 'plugin_groups', $this->plugin_groups );
		update_option( 'plugin_groups_match', $plugin_groups_match_replace );
		update_option( 'groups_plugin_match', $this->groups_plugin_match );

		wp_redirect( $this->get_plugins_url() );
		die();
	}

	/**
	 * Ajax: Create Group.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function create_group() {
		$group_name = $_POST[ 'group_name' ];
		$plugin_id = $_POST[ 'plugin_id' ];

		$group_id = sanitize_title( $group_name );

		if ( $group_id && empty( $this->plugin_groups[$group_id] ) ) {
			$bgcolor = apply_filters( 'plugin_group_default_color', $this->default_color );
			$color = $this->get_contrast_color( $bgcolor );

			$this->plugin_groups[$group_id] = array(
				'color' => $bgcolor,
				'name' => $group_name
			);

			update_option( 'plugin_groups', $this->plugin_groups );

			echo json_encode( array(
				'url' => $this->get_plugins_url() . '?plugin_group=' . $group_id,
				'group_id' => $group_id,
				'group_name' => $group_name,
				'bgcolor' => $bgcolor,
				'color' => $color
			));

			$this->input_into_group( $group_id, $group_name, $plugin_id, false );
		}

		wp_die();
	}

	/**
	 * Ajax: Input Plugin into Group.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function input_into_group( $group_id = false, $group_name = false, $plugin_id = false, $echo = true ) {
		$group_id = ( !$group_id ) ? $_POST[ 'group_id' ] : $group_id;
		$group_name = ( !$group_name ) ? $_POST[ 'group_name' ] : $group_name;
		$plugin_id = ( !$plugin_id ) ? $_POST[ 'plugin_id' ] : $plugin_id;

		if ( !is_array( $this->plugin_groups_match ) || empty( $this->plugin_groups_match ) ) $this->plugin_groups_match = array();
		if ( empty( $this->plugin_groups_match[$plugin_id] ) || !is_array( $this->plugin_groups_match[$plugin_id] )  ) $this->plugin_groups_match[$plugin_id] = array();

		if ( !is_array( $this->groups_plugin_match ) || empty( $this->groups_plugin_match ) ) $this->groups_plugin_match = array();
		if ( empty( $this->groups_plugin_match[$group_id] ) || !is_array( $this->groups_plugin_match[$group_id] ) ) $this->groups_plugin_match[$group_id] = array();

		$this->plugin_groups_match[$plugin_id][$group_id] = $group_name;
		$this->groups_plugin_match[$group_id][$plugin_id] = $plugin_id;

		update_option( 'plugin_groups_match', $this->plugin_groups_match );
		update_option( 'groups_plugin_match', $this->groups_plugin_match );

		if ( $echo ) {
			$background_color = $this->plugin_groups[$group_id]['color'];
			$color = $this->get_contrast_color( $background_color );

			echo json_encode( array(
				'url' => $this->get_plugins_url() . '?plugin_group=' . $group_id,
				'bgcolor' => $background_color,
				'color' => $color
			));
		}

		wp_die();
	}

	/**
	 * Ajax: Delete Plugin from Group.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function delete_from_group() {
		$group_id = $_POST[ 'group_id' ];
		$group_name = $_POST[ 'group_name' ];
		$plugin_id = $_POST[ 'plugin_id' ];

		unset( $this->plugin_groups_match[$plugin_id][$group_id] );
		update_option( 'plugin_groups_match', $this->plugin_groups_match );

		unset( $this->groups_plugin_match[$group_id][$plugin_id] );
		update_option( 'groups_plugin_match', $this->groups_plugin_match );

		wp_die();
	}

	/**
	 * Ajax: Set Group Color.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function set_group_color() {
		$group_id = $_POST['group_id'];
		$color = $_POST['color'];

		if ( !isset( $this->plugin_groups[$group_id] ) ) wp_die();

		if ( !is_array( $this->plugin_groups[$group_id] ) ) $this->plugin_groups[$group_id] = $this->upgrade( $group_id );
		$this->plugin_groups[$group_id]['color'] = $color;

		update_option( 'plugin_groups', $this->plugin_groups );

		echo json_encode( array(
			'bgcolor' => $color,
			'color' => $this->get_contrast_color( $color )
		) );

		wp_die();
	}

	/**
	 * Filter[all_plugins]: Change Plugin List Items on Group View Screen.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param: (array) $plugins
	 * @return: (array) $plugins
	 */
	public function all_plugins( $plugins ) {
		if ( $this->is_group_query ) {
			$plugins_ = array();
			$plugin_info = get_site_transient( 'update_plugins' );

			foreach ( (array) $plugins as $plugin_file => $plugin_data ) {
				if ( isset( $plugin_info->no_update[ $plugin_file ] ) ) {
					$plugins[ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->no_update[ $plugin_file ], $plugin_data );
				}
			}

			foreach( $plugins as $key => $plugin_data ) {
				$slug = ( $plugin_data[ 'slug' ] ) ? $plugin_data[ 'slug' ] : sanitize_title( $plugin_data['Name'] );

				if ( !empty( $this->groups_plugin_match[$_GET['plugin_group']][$slug] ) ) {
					$plugins_[$key] = $plugin_data;
				}
			}

			return $plugins_;
		}

		return $plugins;
	}

	/**
	 * Action[pre_current_active_plugins]: Print Basic Grouping Option Element.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_modal() {
		$groups = $this->plugin_groups;
		include_once( PIGPR_TEMPLATE_DIR . 'modal_table.php' );
	}

	/**
	 * Filter[views_plugins] : Adding Plugin Category on Table Filter.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param: (array) $views List Tabs
	 * @return: (array) List Tabs
	 *
	 * see	/wp-admin/includes/class-wp-list-table.php
	 */
	public function views_plugins( $views ) {
		$groups = $this->plugin_groups;

		if ( empty( $groups ) ) return $views;
		if ( $this->is_group_query ) {
			unset($views);
			$views['all'] = sprintf( '<a href="plugins.php?plugin_status=all">%s</a>', __( 'All', PIGPR_TEXTDOMAIN ) );

			foreach( $groups as $key => $value ) {
				$background_color = $value['color'];
				$color = $this->get_contrast_color( $background_color );
				$class = ( strtolower( urlencode( $_GET['plugin_group'] ) ) == strtolower( $key ) ) ? 'current' : '';

				$views[$key] = sprintf( '<a href="plugins.php?plugin_group=%s" class="%s group" data-color="%s" style="background-color:%s; color:%s">%s</a>', $key, $class, $value['color'], $background_color, $color, $value['name'] );
			}
		} else {
			echo "<ul class='subsubsub plugin-groups'>\n";
			printf( "<li><strong>%s</strong> |</li>", __( 'Groups', PIGPR_TEXTDOMAIN ) );

			foreach( $groups as $key => $value ) {
				$background_color = $value['color'];
				$color = $this->get_contrast_color( $background_color );

				$groups[ $key ] = sprintf( '<li class="group"><a href="plugins.php?plugin_group=%s" data-id="%s" data-bgcolor="%s" data-color="%s" class="group" style="background-color:%s; color:%s">%s</a>', $key, $key, $background_color, $color, $background_color, $color, $value['name'] );
			}
			echo implode( " |</li>\n", $groups ) . "</li>\n";
			echo "</ul>";
			echo "<div class='clear'></div>";

		}

		return $views;
	}

	/**
	 * Get Admin Plugins URL * inc. network site.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @return: (string) $url
	 */
	private function get_plugins_url() {
		$url = get_bloginfo( 'url' ) . '/wp-admin/';
		$url .= ( is_network_admin() ) ? 'network/' : '';
		$url .= 'plugins.php';

		return $url;
	}

	/**
	 * Get Brightness
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 */

	private function get_contrast_color( $hex ) {
		$hex = str_replace( '#', '', $hex );

		if( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ).substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ).substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ).substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}

		$contrast = ( $r + $g + $b ) / 3;

		return ( $contrast < 128 ) ? "#FFFFFF" : "#000000";
	}

	/**
	 * upgrade
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 */
	private function upgrade() {
		$current_version = get_option( 'PIGPR_VERSION_NUM' );

		if ( version_compare( $current_version, '2.0.0', '<' ) ) {
			update_option( 'PIGPR_VERSION_NUM', PIGPR_VERSION_NUM );

			$groups = get_option( 'plugin_groups' );
			if ( $groups ) {
				foreach( $groups as &$group ) {
					if ( !is_array( $group ) ) {
						$group = array(
							'color' => '#666666',
							'name' => $group
						);
					}
				}
			}

			update_option( 'plugin_groups', $groups );
		}
	}
}
