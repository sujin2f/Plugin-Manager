jQuery( document ).ready( function( $ ) {
	// 텍스트 숨기기
	$( '#group-manager-setting-text' ).click( function( e ) {
		var data = {
			'action' : 'PIGPR Setting Text',
			'mode' : 'Plugin Manager',
			'status' : $( '#group-manager-setting-text:checked' ).length
		};

		$obj = $(this);

		$.post( ajaxurl, data, function( response ) {
			if ( response )
				$( '.button-plugin-manager .text' ).addClass( 'hidden' );
			else
				$( '.button-plugin-manager .text' ).removeClass( 'hidden' );
		}, 'json' );
	});

	$( '.btn-delete_group' ).click( function(e) {
		if ( !confirm( 'Do you really want to delete this?' ) )
			e.preventDefault();
	});
});

function getUrlParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++)  {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] === sParam)  {
			return sParameterName[1];
		}
	}
}
