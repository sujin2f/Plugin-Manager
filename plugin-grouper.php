<?php
/**
 * Plugin Name:		Plugin Manager
 * Plugin URI:		http://www.sujinc.com/
 * Description:		Too many plugins bothers you? Put them into group!
 * Version:				5.0.4
 * Author:				Sujin 수진 Choi
 * Author URI:		http://www.sujinc.com/
 * License:				GPLv2 or later
 * Text Domain:		plugin-grouper
 */

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

# Definitions
if ( !defined( 'PIGPR_PLUGIN_NAME' ) ) {
	$basename = trim( dirname( plugin_basename( __FILE__ ) ), '/' );
	if ( !is_dir( WP_PLUGIN_DIR . '/' . $basename ) ) {
		$basename = explode( '/', $basename );
		$basename = array_pop( $basename );
	}

	define( 'PIGPR_PLUGIN_NAME', $basename );
}

if ( !defined( "PIGPR_PLUGIN_FILE_NAME" ) )
	define( "PIGPR_PLUGIN_FILE_NAME", basename(__FILE__) );

if ( !defined( "PIGPR_TEXTDOMAIN" ) )
	define( "PIGPR_TEXTDOMAIN", "plugin-grouper" );

if ( !defined( 'PIGPR_PLUGIN_DIR' ) )
	define( 'PIGPR_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . PIGPR_PLUGIN_NAME . '/' );

if ( !defined( 'PIGPR_TEMPLATE_DIR' ) )
	define( 'PIGPR_TEMPLATE_DIR', PIGPR_PLUGIN_DIR . 'templates/' );

if ( !defined( 'PIGPR_ASSETS_URL' ) )
	define( 'PIGPR_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/' );

if ( !defined( 'PIGPR_VENDOR_URL' ) )
	define( 'PIGPR_VENDOR_URL', plugin_dir_url( __FILE__ ) . 'vendors/' );

if ( !defined( "PIGPR_VERSION_KEY" ) )
	define( "PIGPR_VERSION_KEY", "PIGPR_version" );

if ( !defined( "PIGPR_VERSION_NUM" ) )
	define( "PIGPR_VERSION_NUM", "5.0.0" );

# 가는거야~!
include_once( PIGPR_PLUGIN_DIR . "/autoload.php");
new PIGPR\Init();
