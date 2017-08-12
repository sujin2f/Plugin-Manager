/**
 * Grouping
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

angular.module( 'PluginManager' )
	.controller( 'MenuController', function( $scope, dataService ) {
		$scope.numNoneGroup = function() {
			return dataService.get.numNoneGroup();
		};

		$scope.getGroups = function() {
			return dataService.get.groups();
		};

		$scope.showMenu = true;
	});
