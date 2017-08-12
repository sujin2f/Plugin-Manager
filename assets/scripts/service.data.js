/**
 * Grouping Modal
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

angular.module( 'PluginManager' )
	.service( 'dataService', function() {
		var service = {};

		service.settings     = objectL10n.settings;
		service.data         = objectL10n.data;
		service.colours      = objectL10n.colours;
		service.group        = objectL10n.plugin_group;

		// Get
		service.get = {};

		service.get.numHidden = function() {
			return service.data.num_hidden;
		};
		service.get.numNoneGroup = function() {
			return service.data.num_none_group;
		};

		service.get.groups = function() {
			return service.data.groups;
		};
		service.get.plugins = function() {
			return service.data.plugins;
		};
		service.get.settings = function( key ) {
			return service.settings[ key ];
		};

		service.get.colours = function() {
			return service.colours;
		};

		// Update
		service.update = {};

		service.update.data = function( newData ) {
			service.data = newData;
		};
		service.update.settings = function( newSettings ) {
			service.settings = newSettings;
		};

		// Is
		service.is = {};

		service.is.locked = function( plugin_id ) {
			var isLocked = ( service.data.plugins[ plugin_id ].locked == 1 ) ? true : false;

			if ( isLocked ) {
				jQuery( '.wp-list-table tr[data-plugin="' + plugin_id + '"]' ).addClass( 'locked' );
			} else {
				jQuery( '.wp-list-table tr[data-plugin="' + plugin_id + '"]' ).removeClass( 'locked' );
			}

			jQuery( '.wp-list-table tr th.check-column input[type="checkbox"]' ).removeAttr( 'disabled' );
			jQuery( '.wp-list-table tr.locked th.check-column input[type="checkbox"]' ).attr( 'disabled', 'disabled' );

			return isLocked;
		};
		service.is.hidden = function( plugin_id ) {
			var isHidden = ( service.data.plugins[ plugin_id ].hidden == 1 ) ? true : false;

			if ( isHidden ) {
				jQuery( '.wp-list-table tr[data-plugin="' + plugin_id + '"]' ).addClass( 'hidden' );
			} else {
				jQuery( '.wp-list-table tr[data-plugin="' + plugin_id + '"]' ).removeClass( 'hidden' );
			}

			return isHidden;
		};

		return service;
	});
