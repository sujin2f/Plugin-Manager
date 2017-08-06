<?php
/**
 * Plugin Name:   Plugin Manager Pro
 * Plugin URI:    http://www.sujinc.com/
 * Description:   Too many plugins bothers you? Put them into group!
 * Version:       0.0.1
 * Author:        Sujin 수진 Choi
 * Author URI:    http://www.sujinc.com/
 * License:       GPLv2 or later
 * Text Domain:   plugin-manager-pro
 */

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

# Definitions
if ( !defined( 'PLGINMNGRPRO_PLUGIN_NAME' ) ) {
	$basename = trim( dirname( plugin_basename( __FILE__ ) ), '/' );
	if ( !is_dir( WP_PLUGIN_DIR . '/' . $basename ) ) {
		$basename = explode( '/', $basename );
		$basename = array_pop( $basename );
	}

	define( 'PLGINMNGRPRO_PLUGIN_NAME', $basename );
}

if ( !defined( "PLGINMNGRPRO_PLUGIN_FILE_NAME" ) )
	define( "PLGINMNGRPRO_PLUGIN_FILE_NAME", basename(__FILE__) );

if ( !defined( "PLGINMNGRPRO_TEXTDOMAIN" ) )
	define( "PLGINMNGRPRO_TEXTDOMAIN", "plugin-manager" );

if ( !defined( 'PLGINMNGRPRO_PLUGIN_DIR' ) )
	define( 'PLGINMNGRPRO_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . PLGINMNGRPRO_PLUGIN_NAME . '/' );

if ( !defined( 'PLGINMNGRPRO_TEMPLATE_DIR' ) )
	define( 'PLGINMNGRPRO_TEMPLATE_DIR', PLGINMNGRPRO_PLUGIN_DIR . 'templates/' );

if ( !defined( 'PLGINMNGRPRO_ASSETS_URL' ) )
	define( 'PLGINMNGRPRO_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/' );

if ( !defined( 'PLGINMNGRPRO_VENDOR_URL' ) )
	define( 'PLGINMNGRPRO_VENDOR_URL', plugin_dir_url( __FILE__ ) . 'vendors/' );

if ( !defined( "PLGINMNGRPRO_VERSION_KEY" ) )
	define( "PLGINMNGRPRO_VERSION_KEY", "PLGINMNGRPRO_version" );

if ( !defined( "PLGINMNGRPRO_VERSION_NUM" ) )
	define( "PLGINMNGRPRO_VERSION_NUM", "0.0.1" );

# 가는거야~!
include_once( PLGINMNGRPRO_PLUGIN_DIR . "/autoload.php");
$GLOBALS[ 'plugin-manager-pro' ] = new PLGINMNGRPRO\Init();
