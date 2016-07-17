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

	private $is_group_query, $plugin_groups, $plugin_groups_match, $groups_plugin_match, $num_all_plugins;

	function __construct() {
		$this->plugin_groups = get_option( 'plugin_groups' );
		$this->plugin_groups_match = get_option( 'plugin_groups_match' );
		$this->groups_plugin_match = get_option( 'groups_plugin_match' );

/* 		$this->is_group_query = !empty( $_GET['plugin_group'] ); */

		# AJAX
		add_action( 'wp_ajax_PIGPR_CREATE_GROUP', array( $this, 'create_group' ) );
		add_action( 'wp_ajax_PIGPR_INPUT_INTO_GROUP', array( $this, 'input_into_group' ) );
		add_action( 'wp_ajax_PIGPR_DELETE_FROM_GROUP', array( $this, 'AjaxDeleteFromGroup' ) );
		add_action( 'wp_ajax_PIGPR_SET_GROUP_COLOR', array( $this, 'set_group_color' ) );

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

		// Group View Redirection
// 		add_action( 'init', array( $this, 'GroupViewRedirection' ) );

		if ( $this->is_group_query ) {
			add_action( 'admin_footer', array( $this, 'PrintGroupInformation' ) );
			add_action( 'wp_loaded', array( $this, 'ActionDeleteGroup' ) );
		}
	}

/*
	public function GroupViewRedirection() {
		if ( isset( $_POST[ 'gm-plugin' ] ) && !empty( $_POST[ 'gm-plugin' ] ) ) {

			$arg = implode( 'plugin_grouper_url_needle', $_POST[ 'gm-plugin' ] );
			wp_redirect( add_query_arg( 'plugin_group', $arg ) );
			die;
		}
	}
*/

	public function PrintGroupButton( $actions, $plugin_file, $plugin_data, $a ) {
		$text_class = ( $GLOBALS[ 'PIGPR' ]->ScreenOption->hide_text ) ? 'hidden' : '';

		$actions['group'] = sprintf( '<a href="#" class="button-grouping button-plugin-manager" data-id="%s"><span class="dashicons dashicons-groups"></span><span class="text %s">%s</span></a>',
			( isset( $plugin_data[ 'slug' ] ) && $plugin_data[ 'slug' ] ) ? $plugin_data[ 'slug' ] : sanitize_title( $plugin_data['Name'] ),
			$text_class,
			__( 'Group', PIGPR_TEXTDOMAIN )
		);

		return $actions;
	}

	public function PrintGroupsOnDescription( $plugin_meta, $plugin_file, $plugin_data ) {
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

	// On Footer
	public function PrintGroupInformation() {
		$group_key = $_GET['plugin_group'];

		printf( '<input type="hidden" id="plugin_group_name" value="%s" />', $this->plugin_groups[$group_key]['name'] );
	}

	public function ActionDeleteGroup() {
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

	public function create_group() {
		$group_name = $_POST[ 'group_name' ];
		$plugin_id = $_POST[ 'plugin_id' ];

		$group_id = sanitize_title( $group_name );

		if ( $group_id && empty( $this->plugin_groups[$group_id] ) ) {
			$bgcolor = apply_filters( 'plugin_group_default_color', $this::DEFAULT_COLOR );
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

	public function set_group_color() {
		$group_id = $_POST['group_id'];
		$color = $_POST['color'];

		if ( !isset( $this->plugin_groups[$group_id] ) ) wp_die();

		$this->plugin_groups[$group_id]['color'] = $color;

		update_option( 'plugin_groups', $this->plugin_groups );

		echo json_encode( array(
			'bgcolor' => $color,
			'color' => $this->get_contrast_color( $color )
		) );

		wp_die();
	}

	public function ModifyAllPlugins( $plugins ) {
		$this->num_all_plugins = count( $plugins );

		if ( !empty( $_GET[ 'plugin_group' ] ) && $_GET[ 'plugin_group' ] == 'not_in_any_groups' ) {
			$plugins_ = $plugins;

			foreach( $plugins as $key => $plugin ) {
				foreach( $this->plugin_groups_match as $key_opt => $plugin_opt ) {
					if ( strstr( $key, $key_opt . '/' ) !== false )
						unset( $plugins_[ $key ] );

					if ( sanitize_title( $plugin[ 'Name' ] ) == $key_opt )
						unset( $plugins_[ $key ] );
				}
			}
			return $plugins_;

		} else if ( !empty( $_GET[ 'plugin_group' ] ) ) {
			$plugins_ = array();

			foreach( $this->groups_plugin_match[ $_GET[ 'plugin_group' ] ] as $group_key => $value ) {
				foreach( $plugins as $key => $plugin ) {
					if ( strstr( $key, $group_key . '/' ) !== false )
						$plugins_[ $key ] = $plugin;

					if ( sanitize_title( $plugin[ 'Name' ] ) == $group_key )
						$plugins_[ $key ] = $plugin;
				}
			}
			return $plugins_;
		}
/*
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
*/

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
		$groups[ 'not in any groups' ] = sprintf( '<li class="group"><a href="plugins.php?plugin_group=not_in_any_groups" class="%s">None <span class="count">(%s)</span></a>', $class, $num_not_in_groups );

		foreach( $this->plugin_groups as $key => $value ) {
			$background_color = $value['color'];

		$class = ( $_GET[ 'plugin_group' ] == $key ) ? 'current' : '';
			$groups[ $key ] = sprintf( '<li class="group"><a href="plugins.php?plugin_group=%s" class="%s"><span class="colour" style="background-color:%s"></span>%s <span class="count">(%s)</span></a>', $key, $class, $background_color, $value['name'], count( $this->groups_plugin_match[ $key ] ) );
		}

		?>
		<ul class='subsubsub plugin-groups'>
			<li><strong><?php _e( 'Groups', PIGPR_TEXTDOMAIN ) ?></strong> |</li>
			<?php echo implode( " |</li>\n", $groups ) ?></li>
		</ul>

		<div class='clear'></div>
		<?php



/*
			unset($views);
			$views['all'] = sprintf( '<a href="plugins.php?plugin_status=all">%s</a>', __( 'All', PIGPR_TEXTDOMAIN ) );

			foreach( $groups as $key => $value ) {
				$background_color = $value['color'];
				$color = $this->get_contrast_color( $background_color );
				$class = ( strtolower( urlencode( $_GET['plugin_group'] ) ) == strtolower( $key ) ) ? 'current' : '';

				$views[$key] = sprintf( '<a href="plugins.php?plugin_group=%s" class="%s group" data-color="%s" style="background-color:%s; color:%s">%s</a>', $key, $class, $value['color'], $background_color, $color, $value['name'] );
			}
*/
/*
		} else {

		}
*/

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

	public function AddOptionTextMode( $screen_settings ) {
		$plugin_group_var = ( isset( $_GET[ 'plugin_group' ] ) ) ? explode( 'plugin_grouper_url_needle', $_GET[ 'plugin_group' ] ) : false;

		ob_start();
		?>
		<fieldset class="screen-options">
			<legend>
				<?php _e( 'Group', PIGPR_TEXTDOMAIN ) ?>
				<?php if ( $plugin_group_var ) : ?>
				&nbsp;&nbsp;&nbsp;
				<label><button name="gm-show_all" id="group-manager-group-show_all" value="gm-text"> Show All</button>
				<?php endif; ?>
			</legend>

			<?php foreach( $this->plugin_groups as $key => $group ) : ?>
			<label>
				<input name="gm-plugin[<?php echo $key ?>]" type="checkbox" id="group-manager-plugin-<?php echo $key ?>" value="<?php echo $key ?>" <?php echo in_array( $key, $plugin_group_var ) ? 'checked="checked"' : '' ?>>
					<?php echo $group[ 'name' ] . ' (' . count( $this->groups_plugin_match[ $key ] ) . ')' ?>
			</label>&nbsp;&nbsp;&nbsp;
			<?php endforeach; ?>
		</fieldset>
		<?php
		$screen_settings .= ob_get_clean();

		return $screen_settings;
	}
}
