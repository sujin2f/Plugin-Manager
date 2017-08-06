/**
 * Table Controller
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

angular.module( 'PluginManager' )
	.controller( 'PluginManagerController', function( $scope, $http, dataService ) {
		$scope.plugin_group = objectL10n.plugin_group;

		$scope.group_name = function() {
			if ( ! dataService.group )
				return '';

			var group = dataService.get.groups();

			group.some( function( el ) {
				if ( el.ID == dataService.group ) {
					group = el;
					return el;
				}
			});

			return group.group_name;
		}

		// Navigation
		$scope.getNumPlugins = function() {
			var plugins = dataService.get.plugins();
			return Object.keys(plugins).length;
		};

		$scope.getGroupDescription = function( group_id ) {
			var groups = dataService.get.groups();
			var description = '';

			groups.forEach( function( group ) {
				if ( parseInt( group.ID ) == group_id ) {
					description = group.description;
				}
			});

			return description;
		};


		// Modal
		$scope.showModal = function( plugin_id ) {
			$scope.$broadcast( 'modal.show.groups', plugin_id );
		};

		// Description Area
		$scope.getPluginGroups = function( plugin_id ) {
			var plugins = dataService.get.plugins();
			return plugins[ plugin_id ].groups;
		};

		// Button
		$scope.isHideText = function() {
			return dataService.get.settings( 'hide_text' );
		};
		// Button::Lock
		$scope.isLocked = function( plugin_id ) {
			return dataService.is.locked( plugin_id );
		};
		$scope.lockPlugin = function( plugin_id ) {
			var data = {
				action      : 'Plugin Manager Pro : Lock Plugin',
				plugin_id   : plugin_id,
				security    : objectL10n.nonce,
				plugin_group: objectL10n.plugin_group,
			};

			$http({
				url   : ajaxurl,
				method: 'POST',
				params: data,
			}).then(
				function( response ) {
					dataService.update.data( response.data );
				},
				function( response ) {
					$scope.errorMsg = objectL10n.something;
				}
			);
		};

		// Button::Hide
		$scope.isHidden = function( plugin_id ) {
			return dataService.is.hidden( plugin_id );
		};
		$scope.hidePlugin = function( plugin_id ) {
			var data = {
				action      : 'Plugin Manager Pro : Hide Plugin',
				plugin_id   : plugin_id,
				security    : objectL10n.nonce,
				plugin_group: objectL10n.plugin_group,
			};

			$http({
				url   : ajaxurl,
				method: 'POST',
				params: data,
			}).then(
				function( response ) {
					dataService.update.data( response.data );
				},
				function( response ) {
					$scope.errorMsg = objectL10n.something;
				}
			);
		};

		// Show Options
		$scope.showOptions = function() {
			var broadcast = 'modal.show.options';

			if ( dataService.group )
				broadcast = 'groups.edit';

			$scope.$broadcast( broadcast );
		};

		// Show Hidden
		$scope.num_of_hidden = function() {
			return dataService.get.numHidden();
		}
		$scope.mode_show_hidden = false;
		$scope.toggleHidden = function() {
			if ( $scope.mode_show_hidden == false ) {
				jQuery( '.wp-list-table' ).addClass( 'show-hidden' );

				$scope.mode_show_hidden = true;
			} else {
				jQuery( '.wp-list-table' ).removeClass( 'show-hidden' );

				$scope.mode_show_hidden = false;
			}
		};

		// Show
		$scope.ng_loaded = true;
	});
