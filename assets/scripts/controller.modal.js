/**
 * Grouping Modal
 *
 * @package     WordPress
 * @subpackage  Plugin Manager
 * @since       6.0.0
 * @author      Sujin 수진 Choi http://www.sujinc.com/donation
*/

angular.module( 'PluginManager' )
	.controller( 'ModalController', function( $scope, $http, $document, dataService ) {
		// ESC to close
		$document.on( 'keydown', function( event ) {
			if ( event.keyCode == 27 ) {
				jQuery( '#grouping-modal .close-button' ).click();
			}
		});

		// Show Group Modal
		$scope.$on( 'modal.show.groups', function( event, plugin_id ) {
			$scope.status.plugin_id = plugin_id;
			$scope.fn.setMode( 'groups' );
		});
		$scope.$on( 'modal.show.bulkGroups', function( event, selected_plugins ) {
			$scope.fn.setMode( 'bulk_groups' );
			$scope.status.plugin_id = selected_plugins;
		});

		$scope.status = {};
		$scope.status.mode         = false;
		$scope.status.mode_history = [];
		$scope.status.isLoading    = false;

		$scope.status.errorMsg  = false;
		$scope.status.plugin_id = '';

		$scope.fn = {};
		$scope.fn.setMode = function( mode ) {
			$scope.status.mode = mode;
			$scope.status.mode_history.push( mode );
		};
		$scope.fn.goBack = function() {

			$scope.status.mode_history.pop();

			if ( $scope.status.mode_history.length ) {
				$scope.status.mode = $scope.status.mode_history[ $scope.status.mode_history.length - 1 ];
			} else {
				$scope.modal.closeModal();
			}
		};

		// Modal
		$scope.modal = {};
		$scope.modal.closeModal = function() {
			$scope.status.plugin_id    = false;
			$scope.status.mode         = false;
			$scope.status.isLoading    = false;
			$scope.status.errorMsg     = false;
			$scope.status.mode_history = [];

			$scope.tabs.tabs           = false;

			$scope.createForm.fn.closeForm( true );
		};

		// Plugin - Group
		$scope.groups    = {};
		$scope.groups.fn = {};

		$scope.groups.fn.getGroups = function() {
			var groups  = dataService.get.groups();
			var is_bulk = ( typeof $scope.status.plugin_id == 'object' );

			if ( $scope.status.plugin_id ) {
				groups.forEach( function( group_value, group_index ) {
					groups[ group_index ].checked       = false;
					groups[ group_index ].indeterminate = 0;

					if ( is_bulk ) {
						$scope.status.plugin_id.forEach( function( plugin_name ) {
							if ( plugin_name in group_value.plugins ) {
								groups[ group_index ].checked = true;
								groups[ group_index ].indeterminate ++;
							}
						});

						if ( groups[ group_index ].indeterminate == $scope.status.plugin_id.length ) {
							groups[ group_index ].indeterminate = false;
						}
					} else {
						if ( $scope.status.plugin_id in group_value.plugins ) {
							groups[ group_index ].checked = true;
						}
					}
				});
			}


			return groups;
		};

		$scope.groups.fn.toggleChecked = function( $event, group_id ) {
			$scope.status.errorMsg  = false;
			$scope.status.isLoading = true;

			let plugin_id;

			if ( typeof $scope.status.plugin_id == 'object' ) {
				plugin_id = $scope.status.plugin_id.join( '*!.*!' );
			} else {
				plugin_id = $scope.status.plugin_id;
			}

			var data = {
				action      : 'Plugin Manager Pro : Toggle Group-Plugin Link',
				plugin_id   : plugin_id,
				group_id    : group_id,
				security    : objectL10n.nonce,
				plugin_group: objectL10n.plugin_group,
				checked     : $event.currentTarget.checked,
			};

			$http({
				url   : ajaxurl,
				method: 'POST',
				params: data,

			}).then(
				function( response ) {
					$scope.status.isLoading = false;
					dataService.update.data( response.data );
				},
				function( response ) {
					$scope.status.isLoading = false;
					$scope.status.errorMsg  = objectL10n.message.something;
				}
			);
		};
		$scope.groups.fn.setOrder = function( event, $index ) {
			var groups = $scope.groups.fn.getGroups();
			groups.splice($index, 1);

			var orders = [];
			groups.forEach( function( group ) {
				orders.push( group.ID );
			});

			$scope.status.errorMsg = false;

			$scope.status.isLoading = true;
			$scope.createForm.data.action = 'Plugin Manager Pro : Set Order';

			$scope.createForm.data.orders = orders.join();

			$http({
				url   : ajaxurl,
				method: 'POST',
				params: $scope.createForm.data,
			}).then(
				function( response ) {
					dataService.update.data( response.data );
					$scope.status.isLoading = false;
				},
				function( response ) {
					$scope.status.errorMsg = objectL10n.something;
					$scope.status.isLoading = false;
				}
			);
		};
		$scope.groups.fn.showEditGroup = function( group_key ) {
			var group = dataService.get.groups();

			group.some( function( el ) {
				if ( el.ID == group_key ) {
					group = el;
					return el;
				}
			});

			$scope.fn.setMode( 'groups.edit' );

			$scope.createForm.data.ID          = group.ID;
			$scope.createForm.data.name        = group.group_name;
			$scope.createForm.data.description = group.description;
			$scope.createForm.data.colour      = group.colour;
			$scope.createForm.data.hidden_main = group.hidden_main;
		};

		// Create Form
		$scope.createForm         = {};
		$scope.createForm.fn      = {};
		$scope.createForm.data    = {
			security    : objectL10n.nonce,
			ID          : '',
			name        : '',
			description : '',
			colour      : 'Red',
			plugin_id   : '',
			hidden_main : false,
			orders      : false,
			plugin_group: objectL10n.plugin_group,
		};
		$scope.createForm.colours = dataService.get.colours();

		$scope.createForm.fn.selectColor = function( colorKey ) {
			$scope.createForm.data.colour = colorKey;
		};
		$scope.createForm.fn.createGroup = function() {
			$scope.status.errorMsg = false;

			// No name
			if ( ! $scope.createForm.data.name ) {
				$scope.status.errorMsg = objectL10n.message.text_length;
				return false;
			};

			$scope.status.isLoading = true;
			$scope.createForm.data.plugin_id = $scope.status.plugin_id;
			$scope.createForm.data.action    = 'Plugin Manager Pro : Create Group';

			$http({
				url   : ajaxurl,
				method: 'POST',
				params: $scope.createForm.data,
			}).then(
				function( response ) {
					dataService.update.data( response.data );

					$scope.createForm.fn.closeForm();
					$scope.status.isLoading = false;
				},
				function( response ) {
					$scope.status.errorMsg = objectL10n.message.something;
					$scope.status.isLoading = false;
				}
			);
		};
		$scope.createForm.fn.closeForm = function( ignoreGoBack) {
			$scope.status.errorMsg             = false;
			$scope.createForm.data.name        = '';
			$scope.createForm.data.description = '';
			$scope.createForm.data.colour      = 'Red';
			$scope.createForm.data.hidden_main = false;

			if ( !ignoreGoBack )
				$scope.fn.goBack();
		};

		// Show Options Modal
		$scope.$on( 'modal.show.options', function() {
			$scope.fn.setMode( 'options' );
		});

		$scope.$on( 'groups.edit', function() {
			$scope.groups.fn.showEditGroup( dataService.group );
		});

		// Tabs
		$scope.tabs         = {};
		$scope.tabs.tabs    = {};
		$scope.tabs.options = {};
		$scope.tabs.manage  = {};
		$scope.tabs.fn      = {};

		$scope.tabs.fn.getTabMame = function( tab_id ) {
			var name = tab_id.replace( '.', ' ' );

			name = name.split( ' ' );

			name.forEach( function( string, index ) {
				var first_letter = string.charAt(0).toUpperCase();
				name[ index ]    = first_letter + string.substr(1);
			});

			name = name.join( ' ' );

			return name;
		};
		$scope.tabs.fn.changeMode = function( tab_id ) {
			$scope.fn.setMode( 'tab_id' );
		};

		// Tabs::Options
		$scope.tabs.options.formData = {
			action      : 'Plugin Manager Pro : Update Settings',
			security    : objectL10n.nonce,
			hide_text   : dataService.get.settings( 'hide_text' ),
			plugin_group: objectL10n.plugin_group,
		};
		$scope.tabs.options.fn = {};
		$scope.tabs.options.fn.updateSettings = function() {
			$scope.status.errorMsg = false;
			$scope.status.isLoading = true;

			$http({
				url   : ajaxurl,
				method: 'POST',
				params: $scope.tabs.options.formData,
			}).then(
				function( response ) {
					dataService.update.settings( response.data );
					$scope.status.isLoading = false;
				},
				function( response ) {
					$scope.status.errorMsg = objectL10n.something;
					$scope.status.isLoading = false;
				}
			);
		};

		// Tabs::Manage
		$scope.tabs.manage.fn = {};
		$scope.tabs.manage.fn.editGroup = function() {
			$scope.status.errorMsg = false;

			// No name
			if ( ! $scope.createForm.data.name ) {
				$scope.status.errorMsg = objectL10n.text_length;
				return false;
			};

			$scope.status.isLoading = true;
			$scope.createForm.data.action = 'Plugin Manager Pro : Edit Group';

			$http({
				url   : ajaxurl,
				method: 'POST',
				params: $scope.createForm.data,
			}).then(
				function( response ) {
					dataService.update.data( response.data );

					$scope.createForm.fn.closeForm();
					$scope.status.isLoading = false;
				},
				function( response ) {
					$scope.status.errorMsg = objectL10n.something;
					$scope.status.isLoading = false;
				}
			);
		};
		$scope.tabs.manage.fn.deleteGroup = function() {
			$scope.status.errorMsg = false;

			// No name
			if ( ! $scope.createForm.data.name ) {
				$scope.status.errorMsg = objectL10n.text_length;
				return false;
			};

			$scope.status.isLoading = true;
			$scope.createForm.data.action = 'Plugin Manager Pro : Delete Group';

			$http({
				url   : ajaxurl,
				method: 'POST',
				params: $scope.createForm.data,
			}).then(
				function( response ) {
					dataService.update.data( response.data );

					$scope.modal.closeModal();
					$scope.status.isLoading = false;

					location = location.origin + location.pathname;
				},
				function( response ) {
					$scope.status.errorMsg = objectL10n.something;
					$scope.status.isLoading = false;
				}
			);
		};
	});
