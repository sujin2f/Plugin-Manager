<?php
/**
 *
 * Plugin Manager Pro
 *
 * @author  Sujin 수진 Choi
 * @package PLGINMNGRPRO
 * @version 0.0.1
 * @website http://www.sujinc.com/
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 */

if ( !function_exists( 'PLGINMNGRPRO' ) ) {
	function load_plugin_manager_pro() {
		spl_autoload_register( function( $class_name ) {
			$namespace = 'PLGINMNGRPRO\\';

			if ( stripos( $class_name, $namespace ) === false )
		        return;

			$source_dir = __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
			$file_name  = str_replace( array( $namespace, '\\' ), array( $source_dir, DIRECTORY_SEPARATOR ), $class_name ) . '.php';

			if ( is_readable( $file_name ) )
				include $file_name;
		});
	}

	load_plugin_manager_pro();
}