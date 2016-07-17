jQuery( document ).ready( function( $ ) {
	// <!-- Binding 개별 항목 액션
	function bind_actions() {
		$( '.button-lock' ).unbind();
		$( '.button-unlock' ).unbind();

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
				lock( $obj );
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
				unlock( $obj );
			}, 'json' );
		});
	}
	bind_actions();
	// Binding 개별 항목 액션 -->

	function lock( $obj ) {
		var text_hidden = $obj.find( '.text' ).hasClass( 'hidden' ) ? 'hidden' : '';

		$obj.parents( 'tr' ).addClass( 'locked' );
		$obj.parents( 'tr' ).find( 'th.check-column input[type="checkbox"]' ).attr( 'type', 'hidden' );
		$obj.parents( 'tr' ).find( 'th.check-column input' ).hide();

		$obj.parents( 'tr' ).find( 'th.check-column' ).prepend( '<span class="dashicons dashicons-lock locked"></span>' );

		$obj.parents( 'tr' ).find( '.row-actions .activate' ).hide();
		$obj.parents( 'tr' ).find( '.row-actions .deactivate' ).hide();
		$obj.parents( 'tr' ).find( '.row-actions .delete' ).hide();

		$obj.html( '<span class="dashicons dashicons-unlock"></span><span class="text  ' + text_hidden + '">' + objectL10n.unlock + '</span>' );

		$obj.removeClass( 'button-lock' ).addClass( 'button-unlock' );
		bind_actions();
	}

	function unlock( $obj ) {
		var text_hidden = $obj.find( '.text' ).hasClass( 'hidden' ) ? 'hidden' : '';

		$obj.parents( 'tr' ).removeClass( 'locked' );
		$obj.parents( 'tr' ).find( 'th.check-column input[type="hidden"]' ).attr( 'type', 'checkbox' );
		$obj.parents( 'tr' ).find( 'th.check-column input' ).show();

		$obj.parents( 'tr' ).find( 'th.check-column .dashicons.locked' ).remove();

		$obj.parents( 'tr' ).find( '.row-actions .activate' ).show();
		$obj.parents( 'tr' ).find( '.row-actions .deactivate' ).show();
		$obj.parents( 'tr' ).find( '.row-actions .delete' ).show();

		$obj.html( '<span class="dashicons dashicons-lock"></span><span class="text  ' + text_hidden + '">' + objectL10n.lock + '</span>' );

		$obj.removeClass( 'button-unlock' ).addClass( 'button-lock' );
		bind_actions();
	}

	$( 'table.plugins tbody tr .row-actions .lock a' ).each( function() {
		if ( $(this).hasClass( 'button-unlock' ) ) {
			lock( $(this) );
		} else {
			unlock( $(this) );
		}
	});
});