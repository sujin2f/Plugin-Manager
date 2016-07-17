<?php
/**
 * Init
 *
 * project	Plugin Manager
 * version: 4.0.0
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

class  Init {
	private $Group, $Lock, $AdminPage;
	private $version = PIGPR_VERSION_NUM;

	function __construct() {
		if ( !is_admin() ) return false;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->Group = new Group();
			$this->Lock = new Lock();
		}

		if ( is_multisite() ) {
		# Single Site
			add_action( 'plugins_loaded', array( $this, 'activateMultisite' ) );

		} else {
		# Multi Site
			global $pagenow;
			if ( $pagenow !== "plugins.php" ) return false;

			$this->Group = new Group();
			$this->Lock = new Lock();

			# 텍스트도메인
			add_action( 'plugins_loaded', array( $this, 'LoadTextDomain' ) );
	 		add_action( 'admin_enqueue_scripts', array( $this, 'EnqueueScripts' ) );
			add_filter( 'wp_redirect', array( $this, 'wp_redirect' ) );
		}
	}

	public function activateMultisite() {
//		if ( is_network_admin() ) {
//			$this->CreateNetworkAdminPage();
//		}

		global $pagenow;
		if ( $pagenow !== "plugins.php" ) return false;

		$this->Group = new Group();
		$this->Lock = new Lock();

		# 텍스트도메인
		add_action( 'plugins_loaded', array( $this, 'LoadTextDomain' ) );
 		add_action( 'admin_enqueue_scripts', array( $this, 'EnqueueScripts' ) );
		add_filter( 'wp_redirect', array( $this, 'wp_redirect' ) );
	}

	private function CreateNetworkAdminPage() {
		$this->AdminPage = new \WE\AdminPage\Options( 'Plugin Manager' );
		$this->AdminPage->position = 'Plugins';

		$this->AdminPage->version = '4.0.0';

		$this->AdminPage->setting = 'Allow Managing';
		$this->AdminPage->setting->type = 'checkbox';
	}

	public function LoadTextDomain() {
		$lang_dir = PIGPR_PLUGIN_NAME . '/languages';
		load_plugin_textdomain( PIGPR_TEXTDOMAIN, 'wp-content/plugins/' . $lang_dir, $lang_dir );
	}

	# 스크립트 & 스타일
	public function EnqueueScripts() {
		# Adding Grouping Actions on Dropdown Menu
		wp_enqueue_script( 'plugin-grouper-group', PIGPR_ASSETS_URL . 'script/min/group-min.js', array( 'jquery' ), '4.0.0' );
		wp_enqueue_script( 'plugin-grouper-lock', PIGPR_ASSETS_URL . 'script/min/lock-min.js', array( 'jquery' ), '4.0.0' );

		wp_enqueue_style( 'plugin-grouper', PIGPR_ASSETS_URL . 'css/plugin-grouper.css' );

		wp_enqueue_script( 'spectrum', PIGPR_VENDOR_URL . 'spectrum.js', array( 'jquery' ), '4.0.0' );
		wp_enqueue_style( 'spectrum', PIGPR_VENDOR_URL . 'spectrum.css' );

		# Localization
		wp_localize_script( 'plugin-grouper-group', 'objectL10n', array(
			'plugin_group'  => __( 'Plugin Group', PIGPR_TEXTDOMAIN ),
			'delete_group' => __( 'Delete Group', PIGPR_TEXTDOMAIN ),
		) );
	}

	public function wp_redirect( $location ) {
		if ( isset( $_REQUEST['plugin_group'] ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== 'delete_group' ) {
			$location = add_query_arg( 'plugin_group', $_REQUEST['plugin_group'], $location );
		}
		return $location;
	}
}


