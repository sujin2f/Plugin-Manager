jQuery( document ).ready( function( $ ) {
	// 초기화 ( 각 tr에 class 부여 )
	$( 'table.plugins tbody tr .row-actions .hide a.button-hide' ).each( function() {
		if ( $(this).attr( 'data-hidden' ) == 'hidden' ) {
			Hide( $(this) );
		} else {
			Show( $(this) );
		}
	});

	if ( getUrlParameter( 'plugin_status' ) == 'hidden' ) {
		$( 'table.plugins' ).addClass( 'mode-show-hidden' );
	}

	// Bind Actions;
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
			Hide( $obj );

			var number = $( '.subsubsub li.view_hidden .count' ).html();
			number = parseInt( number.substr( 1, number.length - 2 ) ) + 1;
			$( '.subsubsub li.view_hidden .count' ).html( '(' + number + ')' );

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
			Show( $obj );

			var number = $( '.subsubsub li.view_hidden .count' ).html();
			number = parseInt( number.substr( 1, number.length - 2 ) ) - 1;
			$( '.subsubsub li.view_hidden .count' ).html( '(' + number + ')' );

			$obj.parents( 'tr' ).hide();
		}, 'json' );
	});

	function Show( $obj ) {
		$obj.parents( 'tr' ).addClass( 'show' );
		$obj.parents( 'tr' ).removeClass( 'hide' );
	}

	function Hide( $obj ) {
		$obj.parents( 'tr' ).removeClass( 'show' );
		$obj.parents( 'tr' ).addClass( 'hide' );
	}


/*
	// <!-- Binding 개별 항목 액션
	function bind_actions() {
		$( '.button-hide' ).unbind();
		$( '.button-show' ).unbind();

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