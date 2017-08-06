/**
 * Grouping Modal
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

jQuery( document ).ready( function( $ ) {
	var $elems = {
		modal            : $( '#grouping-modal' ),
		background       : $( '#grouping-modal-backgroud' ),
		loading_spinner  : $( '#grouping-modal-loading-spinner' ),
		create_text      : $( '#grouping-modal-text-create-group' ),
		create_button    : $( '#grouping-modal-create-button' ),
		close_button     : $( '#grouping-modal-close-button' ),
		error            : $( '#grouping-modal-error' ),
		group_list       : $( '#grouping-modal-list' ),
	};

	var plugin_id;

	var methods = {
		load : function( id ) {
			methods.show();
			methods.set_processing();

			plugin_id = id;

			$.ajax({
				url:  ajaxurl,
				type: 'POST',
				data: {
					action    : 'Plugin Manager Pro : Load Groups',
					mode      : 'Plugin Manager Pro',
					plugin_id : plugin_id,
				},
				dataType: 'json',
				success: completeHandler = function( json ) {
					methods.release_processing();
					methods.hide_error();
					methods.bind_colour_picker();

					$.each( json, function() {
						$elems.group_list.find( '[data-id="' + this + '"]' ).attr( 'checked', 'checked' );
					});



					$elems.create_text.focus();

				},
				error: errorHandler = function() {
					methods.show_error( objectL10n.something );
				},
			});
		},
		show : function() {
			$elems.background.show();
			$elems.modal.css( 'display', 'flex' );

			methods.release_processing();
			methods.hide_error();
		},
		hide : function() {
			$elems.background.hide();
			$elems.modal.css( 'display', 'none' );

			methods.release_processing();
			methods.hide_error();
		},
		set_processing : function() {
			$elems.modal.css( 'z-index', 9999998 );
			$elems.loading_spinner.css( 'display', 'flex' );

			methods.hide_error();
		},
		release_processing : function() {
			$elems.modal.css( 'z-index', 10000000 );
			$elems.loading_spinner.css( 'display', 'none' );

			methods.hide_error();
		},
		show_error : function( msg ) {
			$elems.error.show();
			$elems.error.find( '.message' ).html( msg );
		},
		hide_error : function() {
			$elems.error.hide();
			$elems.error.find( '.message' ).html('');
		},
		create_group : function() {
			if ( $elems.create_text.val().length == 0 ) {
				methods.show_error( objectL10n.text_length );
				$elems.create_text.focus();

				return;
			}

			methods.set_processing();
			$.ajax({
				url:  ajaxurl,
				type: 'POST',
				data: {
					action:     'Plugin Manager Pro : Create Group',
					mode:       'Plugin Manager Pro',
					group_name: $elems.create_text.val(),
					plugin_id:  plugin_id,
				},
				dataType: 'json',
				success: completeHandler = function( json ) {
					methods.release_processing();
					methods.hide_error();

					if ( 'error' in json ) {
						methods.show_error( json.error );
						return;
					}

					$elems.group_list.append( json.html );
					methods.bind_colour_picker();
					$.fn.wp_group_manager( 'insert_into_group', json );
				},
				error: errorHandler = function() {
					methods.show_error( objectL10n.something );
				},
			});
		},
		bind_colour_picker : function() {
			$elems.group_list.find( '.group-colour-picker' ).each( function() {
				var group_id = $(this).attr( 'data-id' );

				$(this).spectrum({
					showPaletteOnly: true,
					colour: $(this).val(),
					palette:[
						["#000000","#444444","#666666","#999999","#CCCCCC","#EEEEEE","#F3F3F3","#FFFFFF"],
						["#F00F00","#F90F90","#FF0FF0","#0F00F0","#0FF0FF","#00F00F","#90F90F","#F0FF0F"],
						["#F4CCCC","#FCE5CD","#FFF2CC","#D9EAD3","#D0E0E3","#CFE2F3","#D9D2E9","#EAD1DC"],
						["#EA9999","#F9CB9C","#FFE599","#B6D7A8","#A2C4C9","#9FC5E8","#B4A7D6","#D5A6BD"],
						["#E06666","#F6B26B","#FFD966","#93C47D","#76A5AF","#6FA8DC","#8E7CC3","#C27BA0"],
						["#C00C00","#E69138","#F1C232","#6AA84F","#45818E","#3D85C6","#674EA7","#A64D79"],
						["#900900","#B45F06","#BF9000","#38761D","#134F5C","#0B5394","#351C75","#741B47"],
						["#600600","#783F04","#7F6000","#274E13","#0C343D","#073763","#20124D","#4C1130"]
					],
					change: function(colour) {
console.log(colour);
						$.ajax({
							url:  ajaxurl,
							type: 'POST',
							data: {
								action:     'Plugin Manager Pro : Change Colour',
								mode:       'Plugin Manager Pro',
								group_id : group_id,
								colour : colour.toHexString(),
							},
							dataType: 'json',
							success: completeHandler = function( json ) {
console.log(json);
								$( '.plugin-version-author-uri div.groups a[data-id="' + group_id + '"], .subsubsub.plugin-groups li.group a[data-id="' + group_id + '"]' ).css({
									'background-color' : json.bgcolour,
									'color' : json.colour
								}).attr( 'data-bgcolour', json.bgcolour ).attr( 'data-colour', json.colour );
							},
							error: errorHandler = function() {
								methods.show_error( objectL10n.something );
							},
						});
					}
				});
			});
		},
	};

	$.fn.group_modal = function( options ) {
		if ( methods[ options ] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );

		} else if ( typeof options === 'object' || ! options ) {
			return methods.load.apply( this, arguments );

		} else {
			$.error( 'Method ' +  options + ' does not exist on jQuery.tooltip' );
		}

		return this;
	};

	// Create Group
	$elems.create_text.keypress( function(e) {
		if ( e.which === 10 || e.which === 13 ) {
			methods.create_group();
			e.preventDefault();
		}
	});
	$elems.create_button.click( function( e ) {
		e.preventDefault();
		methods.create_group();
	});

	function create_group() {
	}

	// Close Modal
	$elems.close_button.click( function( e ) {
		e.preventDefault();
		methods.hide();
	});

	// When Press ESC Key : Close Modal
	$(document).keyup( function( e ) {
		if ( e.keyCode == 27 ) {
			methods.hide();
		}
	});
});
