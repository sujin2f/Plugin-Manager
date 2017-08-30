<?php
/**
 * Constant which contains the colour data
 *
 * @package Plugin Manager
 * @since   6.0.1
 * @author  Sujin 수진 Choi http://www.sujinc.com/donation
*/

namespace Sujin\Plugin\PluginMgr\Constants;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Colour {
	public static $COLOURS = array(
		'Red'         => '#A60000',
		'Orange'      => '#FF7000',
		'Apricot'     => '#FFD060',

		'Blue'        => '#0020A0',
		'LightBlue'   => '#3777CD',
		'SkyBlue'     => '#B0C0EA',

		'Brown'       => '#464000',
		'LightBrown'  => '#948700',
		'Yellow'      => '#FFE900',

		'Green'       => '#007039',
		'LightGreen'  => '#67C700',
		'PaleGreen'   => '#B5F167',

		'DeepPurple'  => '#620056',
		'Purple'      => '#A1008D',
		'LightPurple' => '#EDA4D6',

		'Black'       => '#000000',
		'Grey'        => '#737373',
		'White'       => '#FFFFFF',
	);
}
