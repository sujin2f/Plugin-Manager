<?php
/**
 * Plugin Manager
 *
 * @author  Sujin 수진 Choi
 * @package plugin-manager
 * @version 6.0.0
 * @website http://www.sujinc.com/donation
 *
 * Licensed under The GPL License
 * Redistributions of files must retain the above copyright notice
 */

if ( !function_exists( 'autoload_sujin_plugin_manager' ) ) {
	function autoload_sujin_plugin_manager() {
		spl_autoload_register( function( $class_name ) {
			$namespace = 'Sujin\\Plugin\\PluginMgr\\';

			if ( stripos( $class_name, $namespace ) === false ) {
				return;
			}

			$source_dir = __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;

			// Delete Namespace
			$path = str_replace( $namespace, '', $class_name ) . '.php';
			$path = explode( '\\', $path );

			// Separate Filename and Path
			$file_name = array_pop( $path );

			// Change Path to path-name/path-name
			$path = array_map( function( $string ) {
				$out = array();

				preg_match_all( '/((?:^|[A-Z])[a-z]+)/', $string, $matches );
				foreach( $matches[0] as $match ) {
					$out[] = strtolower( $match );
				}

				return implode( '-', $out );
			}, $path );
			$path = implode( DIRECTORY_SEPARATOR, $path );

			// Change Filename to class-class-name.php
			$file_name = strtolower( $file_name );
			$file_name = str_replace( '_', '-', $file_name );
			$file_name = '/class-' . $file_name;

			$file_name = $source_dir . $path . $file_name;

			if ( is_readable( $file_name ) ) {
				include_once( $file_name );
			}
		});
	}

	autoload_sujin_plugin_manager();
}