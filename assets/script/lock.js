jQuery( document ).ready( function( $ ) {
	// 초기화 ( 각 tr에 class 부여 )
	$( 'table.plugins tbody tr .row-actions .lock a.button-lock' ).each( function() {
		if ( $(this).attr( 'data-locked' ) == 'locked' ) {
			Lock( $(this) );
		} else {
			Unlock( $(this) );
		}

		$(this).parents( 'tr' ).find( 'th.check-column' ).prepend( '<span class="dashicons dashicons-lock"></span>' );
	});

	// Bind Actions;
	$( '.button-lock' ).click( function( e ) {
		e.preventDefault();
		var plugin_file = $(this).attr( "data-plugin_file" );

		var data = {
			'action' : 'PIGPR_LOCK',
			'mode' : 'Plugin Manager',
			'plugin_file' : plugin_file
		};

		$obj = $(this);

		$.post( ajaxurl, data, function( response ) {
			Lock( $obj );
		}, 'json' );
	});

	$( '.button-unlock' ).click( function( e ) {
		e.preventDefault();
		var plugin_file = $(this).attr( "data-plugin_file" );

		var data = {
			'action' : 'PIGPR_UNLOCK',
			'mode' : 'Plugin Manager',
			'plugin_file' : plugin_file
		};

		$obj = $(this);

		$.post( ajaxurl, data, function( response ) {
			Unlock( $obj );
		}, 'json' );
	});

	function Lock( $obj ) {
		$obj.parents( 'tr' ).addClass( 'locked' );
		$obj.parents( 'tr' ).removeClass( 'unlocked' );
	}

	function Unlock( $obj ) {
		$obj.parents( 'tr' ).removeClass( 'locked' );
		$obj.parents( 'tr' ).addClass( 'unlocked' );
	}
});