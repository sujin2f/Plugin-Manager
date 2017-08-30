<?php
/**
 * Plugin Name:   Plugin Manager
 * Plugin URI:    https://wordpress.org/plugins/plugin-grouper/
 * Description:   Too many plugins bothers you? Put them into group!
 * Version:       6.0.1
 * Author:        Sujin 수진 Choi
 * Author URI:    http://www.sujinc.com/
 * License:       GPLv2 or later
 * Text Domain:   plugin-manager
 */

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

# Definitions
if ( !defined( 'SUJIN_PLUGIN_MGR_BASE_NAME' ) ) {
	$basename = trim( dirname( plugin_basename( __FILE__ ) ), '/' );

	if ( !is_dir( WP_PLUGIN_DIR . '/' . $basename ) ) {
		$basename = explode( '/', $basename );
		$basename = array_pop( $basename );
	}

	define( 'SUJIN_PLUGIN_MGR_BASE_NAME', $basename );
}

if ( !defined( "SUJIN_PLUGIN_MGR_NAME" ) )
	define( "SUJIN_PLUGIN_MGR_NAME", "Plugin Manager" );

if ( !defined( "SUJIN_PLUGIN_MGR_SLUG" ) )
	define( "SUJIN_PLUGIN_MGR_SLUG", sanitize_title( SUJIN_PLUGIN_MGR_NAME ) );

if ( !defined( 'SUJIN_PLUGIN_MGR_PATH' ) )
	define( 'SUJIN_PLUGIN_MGR_PATH', WP_PLUGIN_DIR . '/' . SUJIN_PLUGIN_MGR_BASE_NAME );

if ( !defined( 'SUJIN_PLUGIN_MGR_URL' ) )
	define( 'SUJIN_PLUGIN_MGR_URL', plugin_dir_url( __FILE__ ) );

if ( !defined( "SUJIN_PLUGIN_MGR_VERSION_KEY" ) )
	define( "SUJIN_PLUGIN_MGR_VERSION_KEY", "SUJIN_PLUGIN_MGR_VERSION" );

if ( !defined( "SUJIN_PLUGIN_MGR_VERSION" ) )
	define( "SUJIN_PLUGIN_MGR_VERSION", "6.0.0" );

# 가는거야~!
include_once( "autoload.php" );
Sujin\Plugin\PluginMgr\Bootstrap::get_instance();
