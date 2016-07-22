<?php
/**
 * Group
 *
 * project	Plugin Manager
 * version: 5.0.0
 * Author: Sujin 수진 Choi
 * Author URI: http://www.sujinc.com/
*/

namespace PIGPR;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Group {
	const DEFAULT_COLOR = '#666666';

	private $option, $plugin_groups, $plugin_groups_match, $groups_plugin_match, $num_all_plugins;

	function __construct() {
		$this->option = get_option( '_plugin-manager_', array() );

		$this->plugin_groups = get_option( 'plugin_groups' );
		$this->plugin_groups_match = get_option( 'plugin_groups_match' );
		$this->groups_plugin_match = get_option( 'groups_plugin_match' );

		# AJAX
		add_action( 'wp_ajax_PIGPR_CREATE_GROUP', array( $this, 'create_group' ) );
		add_action( 'wp_ajax_PIGPR_INPUT_INTO_GROUP', array( $this, 'input_into_group' ) );
		add_action( 'wp_ajax_PIGPR_DELETE_FROM_GROUP', array( $this, 'AjaxDeleteFromGroup' ) );
		add_action( 'wp_ajax_PIGPR_SET_GROUP_COLOR', array( $this, 'AjaxSetGroupColour' ) );

		// Group Buttons
		add_filter( 'network_admin_plugin_action_links' , array( $this, 'PrintGroupButton' ), 15, 4 );
		add_filter( 'plugin_action_links' , array( $this, 'PrintGroupButton' ), 15, 4 );
		add_action( 'pre_current_active_plugins', array( $this, 'PrintModal' ) );

		// Data Handling
		add_filter( 'all_plugins', array( $this, 'ModifyAllPlugins' ), 20 );

		// Subsubsub
		add_filter( "views_plugins", array( $this, 'ModifySubsubsub' ) );
		add_filter( "views_plugins-network", array( $this, 'ModifySubsubsub' ) );

		// On Description Column
		add_filter( "plugin_row_meta", array( $this, 'PrintGroupsOnDescription' ), 15, 3 );

		// Delete Group
		if ( !empty( $_GET[ 'mode' ] ) && !empty( $_GET[ 'group' ] ) && $_GET[ 'mode' ] == 'delete_group' ) {
			add_action( 'init', array( $this, 'DeleteGroup' ) );
		}
	}

	public function PrintGroupButton( $actions, $plugin_file, $plugin_data, $a ) {
		$text_class = !empty( $this->option[ 'hide_text' ] ) ? 'hidden' : '';

		$actions['group'] = sprintf( '<a href="#" class="button-grouping button-plugin-manager" data-plugin="%s"><span class="dashicons dashicons-groups"></span><span class="text %s">%s</span></a>',
			$plugin_file,
			$text_class,
			__( 'Group', PIGPR_TEXTDOMAIN )
		);

		return $actions;
	}

	public function PrintGroupsOnDescription( $plugin_meta, $plugin_file, $plugin_data ) {
		echo '<div class="groups">';

		if ( !empty( $this->plugin_groups_match[$plugin_file] ) ) {
			foreach( $this->plugin_groups_match[$plugin_file] as $key => $name ) {
				$background_color = $this->plugin_groups[$key]['color'];
				$color = $this->GetContrastColour( $background_color );

				printf( '<a href="%s?plugin_group=%s" style="background-color:%s; color:%s" data-id="%s" data-bgcolor="%s" data-color="%s">%s</a>', $this->GetPluginUri(), $key, $background_color, $color, $key, $background_color, $color, $name );
			}
		}
		echo '</div>';

		return $plugin_meta;
	}

	public function create_group() {
		$group_name = $_POST[ 'group_name' ];
		$plugin_id = $_POST[ 'plugin_id' ];

		$group_id = sanitize_title( $group_name );

		if ( $group_id && empty( $this->plugin_groups[$group_id] ) ) {
			$bgcolor = apply_filters( 'plugin_group_default_color', $this::DEFAULT_COLOR );
			$color = $this->GetContrastColour( $bgcolor );

			$this->plugin_groups[$group_id] = array(
				'color' => $bgcolor,
				'name' => $group_name
			);

			update_option( 'plugin_groups', $this->plugin_groups );

			echo json_encode( array(
				'url' => $this->GetPluginUri() . '?plugin_group=' . $group_id,
				'group_id' => $group_id,
				'group_name' => $group_name,
				'bgcolor' => $bgcolor,
				'color' => $color
			));

			$this->input_into_group( $group_id, $group_name, $plugin_id, false );
		}

		wp_die();
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
			$color = $this->GetContrastColour( $background_color );

			echo json_encode( array(
				'url' => $this->GetPluginUri() . '?plugin_group=' . $group_id,
				'bgcolor' => $background_color,
				'color' => $color
			));
		}

		wp_die();
	}

	public function AjaxDeleteFromGroup() {
		$group_id = $_POST[ 'group_id' ];
		$group_name = $_POST[ 'group_name' ];
		$plugin_id = $_POST[ 'plugin_id' ];

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

	public function AjaxSetGroupColour() {
		$group_id = $_POST['group_id'];
		$color = $_POST['color'];

		if ( !isset( $this->plugin_groups[$group_id] ) ) wp_die();

		$this->plugin_groups[$group_id]['color'] = $color;

		update_option( 'plugin_groups', $this->plugin_groups );

		echo json_encode( array(
			'bgcolor' => $color,
			'color' => $this->GetContrastColour( $color )
		) );

		wp_die();
	}


	public function ModifyAllPlugins( $plugins ) {
		$this->num_all_plugins = count( $plugins );

		if ( !empty( $_GET[ 'plugin_group' ] ) && $_GET[ 'plugin_group' ] == 'not_in_any_groups' ) {
			$plugins_ = $plugins;

			foreach( $plugins as $key => $plugin ) {
				foreach( $this->plugin_groups_match as $key_opt => $plugin_opt ) {
					if ( $key == $key_opt )
						unset( $plugins_[ $key ] );
				}
			}
			return $plugins_;

		} else if ( !empty( $_GET[ 'plugin_group' ] ) ) {
			$plugins_ = array();

			foreach( $this->groups_plugin_match[ $_GET[ 'plugin_group' ] ] as $group_key => $value ) {
				foreach( $plugins as $key => $plugin ) {
					if ( $key == $group_key )
						$plugins_[ $key ] = $plugin;
				}
			}
			return $plugins_;
		}

		return $plugins;
	}

	public function PrintModal() {
		include_once( PIGPR_TEMPLATE_DIR . 'modal_table.php' );
	}

	public function ModifySubsubsub( $views ) {
		if ( isset( $_GET[ 'plugin_group' ] ) ) {

			foreach( $views as $key => &$html ) {
				$doc = new \DOMDocument();
				$doc->loadHTML( $html );

				foreach( $doc->getElementsByTagName( 'a' ) as $link) {
					$link_new = add_query_arg( 'plugin_group', $_GET[ 'plugin_group' ], $link->getAttribute('href') );
					$link->setAttribute( 'href', $link_new );
				}

				$html = $doc->saveHTML();
			}
		}

		$num_not_in_groups = $this->num_all_plugins - count( $this->plugin_groups_match );
		$groups = array();

		$class = ( !$_GET[ 'plugin_group' ] ) ? 'current' : '';
		$groups[ 'all in any groups' ] = sprintf( '<li class="group"><a href="plugins.php" class="%s">All <span class="count">(%s)</span></a>', $class, $this->num_all_plugins );
		$class = ( $_GET[ 'plugin_group' ] == 'not_in_any_groups' ) ? 'current' : '';
		$groups[ 'not in any groups' ] = sprintf( '<li class="group not-in-any-groups"><a href="plugins.php?plugin_group=not_in_any_groups" class="%s">None <span class="count">(%s)</span></a>', $class, $num_not_in_groups );

		foreach( $this->plugin_groups as $key => $value ) {
			$background_color = $value['color'];

			$class = ( $_GET[ 'plugin_group' ] == $key ) ? 'current' : '';
			$groups[ $key ] = sprintf( '<li class="group %s"><a href="plugins.php?plugin_group=%s" class="%s"><span class="colour" style="background-color:%s"></span>%s <span class="count">(%s)</span></a>', $key, $key, $class, $background_color, $value['name'], count( $this->groups_plugin_match[ $key ] ) );
		}

		?>
		<ul class='subsubsub plugin-groups'>
			<li><strong><?php _e( 'Groups', PIGPR_TEXTDOMAIN ) ?></strong> |</li>
			<?php echo implode( " |</li>\n", $groups ) ?></li>
		</ul>

		<div class='clear'></div>
		<?php

		return $views;
	}


	private function GetPluginUri() {
		$url = get_bloginfo( 'url' ) . '/wp-admin/';
		$url .= ( is_network_admin() ) ? 'network/' : '';
		$url .= 'plugins.php';

		return $url;
	}

	private function GetContrastColour( $hex ) {
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
}
