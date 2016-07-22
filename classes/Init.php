<?php
/**
 * Init
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

class Init {
	private $Group, $Lock, $Hide, $ScreenOption;
	public $upgrade = false;

	function __construct() {
		add_action( 'wp_loaded', array( $this, 'Upgrade' ) );

		if ( !is_admin() )
			return false;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST[ 'mode' ] ) && $_POST[ 'mode' ] == 'Plugin Manager' )
			$this->ActivateObjects();

		if ( is_multisite() )
			add_action( 'plugins_loaded', array( $this, 'ActivatePlugin' ) );
		else
			$this->ActivatePlugin();
	}

	private function ActivateObjects() {
		$this->Group = new Group();
		$this->Lock = new Lock();
		$this->Hide = new Hide();
		$this->ScreenOption = new ScreenOption();
	}

	public function ActivatePlugin() {
		global $pagenow;
		if ( $pagenow !== "plugins.php" ) return false;

		$this->ActivateObjects();

		# 텍스트도메인
		add_action( 'plugins_loaded', array( $this, 'LoadTextDomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'EnqueueScripts' ) );
		add_filter( 'wp_redirect', array( $this, 'WP_Redirect' ) );
	}

	public function LoadTextDomain() {
		$lang_dir = PIGPR_PLUGIN_NAME . '/languages';
		load_plugin_textdomain( PIGPR_TEXTDOMAIN, 'wp-content/plugins/' . $lang_dir, $lang_dir );
	}

	public function EnqueueScripts() {
		# Adding Grouping Actions on Dropdown Menu
		wp_enqueue_script( 'plugin-grouper-group', PIGPR_ASSETS_URL . 'script/min/plugin_grouper-min.js', array( 'jquery' ), '4.0.0' );

		wp_enqueue_style( 'plugin-grouper', PIGPR_ASSETS_URL . 'css/plugin-grouper.css' );

		wp_enqueue_script( 'spectrum', PIGPR_VENDOR_URL . 'spectrum.js', array( 'jquery' ), '4.0.0' );
		wp_enqueue_style( 'spectrum', PIGPR_VENDOR_URL . 'spectrum.css' );

		# Localization // objectL10n.delete_group
		wp_localize_script( 'plugin-grouper-group', 'objectL10n', array(
			'plugin_group'  => __( 'Plugin Group', PIGPR_TEXTDOMAIN ),
			'delete_group' => __( 'Delete Group', PIGPR_TEXTDOMAIN ),
			'show' => __( 'Show', PIGPR_TEXTDOMAIN ),
			'hide' => __( 'Hide', PIGPR_TEXTDOMAIN ),
			'lock' => __( 'Lock', PIGPR_TEXTDOMAIN ),
			'unlock' => __( 'Unlock', PIGPR_TEXTDOMAIN ),
		) );
	}

	public function WP_Redirect( $location ) {
		if ( isset( $_REQUEST['plugin_group'] ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== 'delete_group' ) {
			$location = add_query_arg( 'plugin_group', $_REQUEST['plugin_group'], $location );
		}

		return $location;
	}

	public function Upgrade() {
		$current_version = get_option( 'PIGPR_VERSION_NUM' );
		$upgraded = false;

		// From Version 1.0.0
		if ( version_compare( $current_version, '2.0.0', '<' ) ) {
			$this->Upgrade2();
			$upgraded = true;
		}

		if ( version_compare( $current_version, '5.0.0', '<' ) ) {
			$this->Upgrade5();
			$upgraded = true;
		}

		if ( $upgraded )
			update_option( 'PIGPR_VERSION_NUM', PIGPR_VERSION_NUM );
	}

	private function Upgrade2() {
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
		delete_option( 'plugin_groups' );
	}

	private function Upgrade5() {
		$plugins = get_plugins();

		$plugin_info = get_site_transient( 'update_plugins' );

		foreach ( (array) $plugins as $plugin_file => $plugin_data ) {
			// Extra info if known. array_merge() ensures $plugin_data has precedence if keys collide.
			if ( isset( $plugin_info->response[ $plugin_file ] ) ) {
				$plugins[ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->response[ $plugin_file ], $plugin_data );

			} elseif ( isset( $plugin_info->no_update[ $plugin_file ] ) ) {
				$plugins[ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->no_update[ $plugin_file ], $plugin_data );
			}
		}

		$plugin_groups_match = get_option( 'plugin_groups_match' );
		$groups_plugin_match = get_option( 'groups_plugin_match' );
		$plugin_groups_match_new = array();
		$groups_plugin_match_new = array();

		// Delete Empty Array
		foreach( $plugin_groups_match as $key => $value ) {
			if ( count( $value ) ) {
				$plugin_groups_match_new[ $key ] = $plugin_groups_match[ $key ];
			}
		}

		update_option( 'plugin_groups_match', $plugin_groups_match_new );

		$plugin_groups_match = $plugin_groups_match_new;
		$plugin_groups_match_new = array();

		foreach( $plugin_groups_match as $option_plugin_key => $option_plugin_value ) {
			$matched = false;
			foreach( $plugins as $plugin_key => $plugin ) {
				if ( strstr( $plugin_key, $option_plugin_key . '/' ) !== false ) {
					$matched = $plugin_key;
				}
			}

			if ( $matched ) {
				$plugin_groups_match_new[ $matched ] = $plugin_groups_match[ $option_plugin_key ];

				foreach( $groups_plugin_match as $option_group_key => $option_group_value ) {
					foreach( $option_group_value as $key => $value ) {
						if ( $value == $option_plugin_key ) {
							$groups_plugin_match_new[ $option_group_key ][ $matched ] = $groups_plugin_match[ $option_group_key ][ $option_plugin_key ];
						}
					}
				}
			}
		}

		update_option( 'plugin_groups_match', $plugin_groups_match_new );
		update_option( 'groups_plugin_match', $groups_plugin_match_new );
	}
}
