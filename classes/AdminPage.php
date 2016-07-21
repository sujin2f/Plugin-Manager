<?php
/**
 * AdminPage
 *
 * project	Plugin Manager
 * version: 5.0.0
 * Author: Sujin 수진 Choi
 * Author URI: http://www.sujinc.com/
 *
*/

namespace PIGPR;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class AdminPage {
	public $hide_text, $show_hidden;
	private $PluginManager;

	function __get( $name ) {
		if ( $name == 'option' )
			return $this->PluginManager->option;

		return $this->{$name};
	}

	function __construct( $parent ) {
		$this->PluginManager = $parent;

		// WP Express
		include_once( PIGPR_PLUGIN_DIR . 'vendors/wp_express/autoload.php' );
		$this->SetOptionsPage();
	}

	// Option Page
	// TODO : For Network Admin
	private function SetOptionsPage() {
		$options_page = new \WE\AdminPage\Options( 'Plugin Manager' );
		$options_page->position = 'Plugins';

		$options_page->setting = "Show Only Icons";
		$options_page->setting->type = "checkbox";
	}
}
