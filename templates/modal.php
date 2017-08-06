<?php
/**
 * Grouping Modal
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}
?>

<section id="grouping-modal" ng-controller="ModalController as modal" ng-class="{loading: status.isLoading}" ng-show="status.mode" class="ng-hide">
	<div id="content-wrapper">
		<div id="content-container">
			<!-- ! Buttons -->
			<div class="container button-container">
				<a href="" class="close-button" ng-class="{ modeDelete : status.mode == 'groups.delete.confirm' }" ng-click="modal.closeModal()"></a>

				<div id="buttons">
					<!-- ! Groups -->
					<section ng-show="status.mode == 'groups'">
						<a href="" class="button" ng-click="modal.closeModal()"><?php _e( 'Close', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
						<a href="" class="button button-primary" ng-click="fn.setMode( 'create' )"><?php _e( 'Create Group', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
					</section>

					<!-- ! Create Form -->
					<section ng-show="status.mode == 'create'">
						<a href="" class="button" ng-click="createForm.fn.closeForm()"><?php _e( 'Cancel', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
						<a href="" class="button" ng-class="createForm.data.colour" ng-click="createForm.fn.createGroup()"><?php _e( 'Create', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
					</section>

					<!-- ! Tab::Group Edit -->
					<section ng-show="status.mode == 'groups.edit'">
						<a href="" class="button" ng-click="modal.closeModal()"><?php _e( 'Cancel', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
						<a href="" class="button button-secondary" ng-click="fn.setMode( 'groups.delete.confirm' )"><?php _e( 'Delete', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
						<a href="" class="button" ng-class="createForm.data.colour" ng-click="tabs.manage.fn.editGroup()"><?php _e( 'Save', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
					</section>

					<!-- ! Tab::Group Delete -->
					<section ng-show="status.mode == 'groups.delete.confirm'">
						<p><?php _e( 'Do you really want to delete the group?', PLGINMNGRPRO_TEXTDOMAIN ) ?></p>

						<a href="" class="button" ng-click="fn.goBack()"><?php _e( 'Cancel', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
						<a href="" class="button button-primary" ng-click="tabs.manage.fn.deleteGroup()"><?php _e( 'Delete', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
					</section>

					<!-- ! Tab::Options -->
					<section ng-show="status.mode == 'options'">
						<a href="" class="button" ng-click="modal.closeModal()"><?php _e( 'Close', PLGINMNGRPRO_TEXTDOMAIN ) ?></a>
					</section>
				</div>
			</div>

			<div class="container content">
				<div id="scroll-container">
					<!-- ! Groups -->
					<section ng-show="status.mode == 'groups' || status.mode == 'bulk_groups'">
						<h3>Select Groups</h3>

						<ul class="group-list" dnd-list="groups.fn.getGroups()">
							<li ng-repeat="(key, group) in groups.fn.getGroups()"
								dnd-draggable="group"
								dnd-moved="groups.fn.setOrder( event, $index )"
							    class="{{group.colour}}"
							    ng-mouseover="group.show_button = true"
							    ng-mouseleave="group.show_button = false"
							    >
								<label for="{{group.ID}}">
									<input id="{{group.ID}}" type="checkbox" ng-class="{indeterminate : group.indeterminate}" ng-checked="group.checked" ng-click="groups.fn.toggleChecked( $event, group.ID )" ui-indeterminate="group.indeterminate" />
									{{group.group_name}}
								</label>
							</li>
						</ul>
					</section>

					<!-- ! Create Form -->
					<section id="create-form" ng-show="status.mode == 'create' || status.mode == 'groups.edit' || status.mode == 'groups.delete.confirm'">
						<h3>{{status.mode == 'create' ? 'Create Group' : 'Edit Group'}}</h3>

						<input type="text" ng-model="createForm.data.name" placeholder="<?php _e( 'Group Name', PLGINMNGRPRO_TEXTDOMAIN ) ?>" />

						<textarea ng-model="createForm.data.description" placeholder="<?php _e( 'Description', PLGINMNGRPRO_TEXTDOMAIN ) ?>"></textarea>

						<label>
							<input type="checkbox" ng-model="createForm.data.hidden_main" ng-checked="createForm.data.hidden_main" />
							<?php _e( 'Hide this group from a main page', PLGINMNGRPRO_TEXTDOMAIN ) ?>

						</label>

						<input type="hidden" ng-model="createForm.data.colour" />

						<ul class="list-color">
							<li ng-repeat="( key, color ) in createForm.colours">
								<a href="" class="{{key}}" ng-click="createForm.fn.selectColor( key );">A</a>
							</li>
						</ul>
					</section>

					<!-- ! Tab::Options -->
					<section id="options" ng-show="status.mode == 'options'">
						<label ng-for="tabs.options.formData.hide_text">
							<input type="checkbox" ng-model="tabs.options.formData.hide_text" ng-checked="tabs.options.formData.hide_text" ng-click="tabs.options.fn.updateSettings()" />
							<?php _e( 'Hide Link Text', PLGINMNGRPRO_TEXTDOMAIN ) ?>
						</label>
					</section>

					<!-- ! Error Message -->
					<div ng-show="status.errorMsg">
						<p><span class="dashicons dashicons-warning"></span> <span class="message">{{status.errorMsg}}</span></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<section id="modal-loading-spinner">
		<img src="<?php bloginfo('url') ?>/wp-admin/images/loading.gif" />
	</section>

<!-- 	<section id="modal-backgroud"></section> -->
</section>