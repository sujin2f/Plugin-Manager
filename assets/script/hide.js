jQuery( document ).ready( function( $ ) {
/*
	// <!-- Binding 개별 항목 액션
	function bind_actions() {
		$( '.button-hide' ).unbind();
		$( '.button-show' ).unbind();

		$( '.button-hide' ).click( function( e ) {
			e.preventDefault();
			var plugin_file = $(this).attr( "data-plugin_file" );

			var data = {
				'action' : 'PIGPR_HIDE',
				'mode' : 'Plugin Manager',
				'plugin_file' : plugin_file
			};

			$obj = $(this);

			$.post( ajaxurl, data, function( response ) {
				hide( $obj );
			}, 'json' );
		});

		$( '.button-show' ).click( function( e ) {
			e.preventDefault();
			var plugin_file = $(this).attr( "data-plugin_file" );

			var data = {
				'action' : 'PIGPR_SHOW',
				'mode' : 'Plugin Manager',
				'plugin_file' : plugin_file
			};

			$obj = $(this);

			$.post( ajaxurl, data, function( response ) {
				show( $obj );
			}, 'json' );
		});
	}
	bind_actions();
	// Binding 개별 항목 액션 -->

	function hide( $obj ) {
		var text_hidden = $obj.find( '.text' ).hasClass( 'hidden' ) ? 'hidden' : '';

		// 숨김 플러그인 보기
		if ( $( '#group-manager-setting-hidden:checked' ).length )
			$obj.parents( 'tr' ).addClass( 'show' );
		else
			$obj.parents( 'tr' ).removeClass( 'show' );

		if ( getUrlParameter( 'plugin_status' ) == 'hidden' )
			$obj.parents( 'tr' ).removeClass( 'hidden' );
		else
			$obj.parents( 'tr' ).addClass( 'hidden' );

		if ( getUrlParameter( 'plugin_group' ) )
			$obj.parents( 'tr' ).removeClass( 'hidden' );

		$obj.html( '<span class="dashicons dashicons-visibility"></span><span class="text ' + text_hidden + '">' + objectL10n.show + '</span>' );
		$obj.removeClass( 'button-hide' ).addClass( 'button-show' );
		bind_actions();
	}

	function show( $obj ) {
		var text_hidden = $obj.find( '.text' ).hasClass( 'hidden' ) ? 'hidden' : '';

		// 숨김 플러그인 보기
		if ( $( '#group-manager-setting-hidden:checked' ).length )
			$obj.parents( 'tr' ).addClass( 'show' );
		else
			$obj.parents( 'tr' ).removeClass( 'show' );

		if ( getUrlParameter( 'plugin_status' ) == 'hidden' )
			$obj.parents( 'tr' ).addClass( 'hidden' );
		else
			$obj.parents( 'tr' ).removeClass( 'hidden' );

		if ( getUrlParameter( 'plugin_group' ) )
			$obj.parents( 'tr' ).removeClass( 'hidden' );

		$obj.html( '<span class="dashicons dashicons-hidden"></span><span class="text ' + text_hidden + '">' + objectL10n.hide + '</span>' );
		$obj.removeClass( 'button-show' ).addClass( 'button-hide' );
		bind_actions();
	}

	$( 'table.plugins tbody tr .row-actions .hide a' ).each( function() {
		if ( $(this).hasClass( 'button-hide' ) ) {
			show( $(this) );
		} else {
			hide( $(this) );
		}
	});
*/
});