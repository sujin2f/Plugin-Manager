jQuery( document ).ready( function( $ ) {
  $( '.wp-list-table tr.locked th.check-column input[type="checkbox"]' ).attr( 'disabled', 'disabled' );

  $( 'form' ).submit( function( e ) {
    var formData = new FormData( $( this )[0]);

    $('body').append( '<form id="non-bindable-angular-form" ng-non-bindable method="post"></form>' );
    $newForm = $( '#non-bindable-angular-form' );

    for( var pair of formData.entries() ) {
      $newForm.append( '<input type="hidden" name="' + pair[0] + '" value="' + pair[1] + '" />' );
    }

    $newForm.submit();
  });

  $('<option value="group-selected">'  + objectL10n.terms.group  + '</option>').appendTo("select[name='action']");
  $('<option value="group-selected">'  + objectL10n.terms.group  + '</option>').appendTo("select[name='action2']");

  $('<option value="lock-selected">'   + objectL10n.terms.lock   + '</option>').appendTo("select[name='action']");
  $('<option value="lock-selected">'   + objectL10n.terms.lock   + '</option>').appendTo("select[name='action2']");

  $('<option value="hide-selected">'   + objectL10n.terms.hide   + '</option>').appendTo("select[name='action']");
  $('<option value="hide-selected">'   + objectL10n.terms.hide   + '</option>').appendTo("select[name='action2']");

  $('<option value="unhide-selected">' + objectL10n.terms.unhide + '</option>').appendTo("select[name='action']");
  $('<option value="unhide-selected">' + objectL10n.terms.unhide + '</option>').appendTo("select[name='action2']");

  $( '.bulkactions input[type="submit"]' ).click( function( e ) {
    if( $(this).prev( 'select' ).val() == 'group-selected' ) {
      var selected_plugins = [];

      $( '.wp-list-table tr th input[type="checkbox"]:checked' ).each( function() {
        selected_plugins.push( $(this).val() );
      });

      angular.element('#grouping-modal').scope().$broadcast( 'modal.show.bulkGroups', selected_plugins );
      angular.element('#grouping-modal').scope().$apply();

      e.preventDefault();
      return;
    }
  });

  $( '#grouping-modal form' ).submit( function( e ) {
    e.preventDefault();
  });
});
