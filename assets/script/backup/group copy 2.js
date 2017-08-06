/**
 * Grouping
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

jQuery( document ).ready( function( $ ) {
	var $elems = {
		error : $( '#grouping-modal-error' ),
	};

	var plugin_id;

	var methods = {
		get_group_id : function( $element ) {
			if ( $element.attr( 'data-plugin' ) )
				return $element.attr( 'data-plugin' );

			return $element.parent( '[data-plugin!=""]' ).attr( 'data-plugin' );
		},
		insert_into_group : function( json ) {
		},
	};

	$.fn.wp_group_manager = function( options ) {
		if ( methods[ options ] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );

		} else if ( typeof options === 'object' || ! options ) {
			return methods.load.apply( this, arguments );

		} else {
			$.error( 'Method ' +  options + ' does not exist on jQuery.tooltip' );
		}

		return this;
	};

	$( '.button-grouping' ).click( function( e ) {
		e.preventDefault();
		$.fn.group_modal( 'load', methods.get_group_id( $(this) ) );
	});
});
