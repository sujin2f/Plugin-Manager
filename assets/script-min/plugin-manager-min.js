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

/**
 * Angular App
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

angular.module( 'PluginManager', ['PluginManager', 'dndLists', 'ui.indeterminate'] );


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
console.log(objectL10n);
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


/**
 * angular-drag-and-drop-lists v1.4.0
 *
 * Copyright (c) 2014 Marcel Juenemann marcel@juenemann.cc
 * Copyright (c) 2014-2016 Google Inc.
 * https://github.com/marceljuenemann/angular-drag-and-drop-lists
 *
 * License: MIT
 */
angular.module('dndLists', [])

  /**
   * Use the dnd-draggable attribute to make your element draggable
   *
   * Attributes:
   * - dnd-draggable      Required attribute. The value has to be an object that represents the data
   *                      of the element. In case of a drag and drop operation the object will be
   *                      serialized and unserialized on the receiving end.
   * - dnd-selected       Callback that is invoked when the element was clicked but not dragged.
   *                      The original click event will be provided in the local event variable.
   * - dnd-effect-allowed Use this attribute to limit the operations that can be performed. Options:
   *                      - "move": The drag operation will move the element. This is the default.
   *                      - "copy": The drag operation will copy the element. Shows a copy cursor.
   *                      - "copyMove": The user can choose between copy and move by pressing the
   *                        ctrl or shift key. *Not supported in IE:* In Internet Explorer this
   *                        option will be the same as "copy". *Not fully supported in Chrome on
   *                        Windows:* In the Windows version of Chrome the cursor will always be the
   *                        move cursor. However, when the user drops an element and has the ctrl
   *                        key pressed, we will perform a copy anyways.
   *                      - HTML5 also specifies the "link" option, but this library does not
   *                        actively support it yet, so use it at your own risk.
   * - dnd-moved          Callback that is invoked when the element was moved. Usually you will
   *                      remove your element from the original list in this callback, since the
   *                      directive is not doing that for you automatically. The original dragend
   *                      event will be provided in the local event variable.
   * - dnd-canceled       Callback that is invoked if the element was dragged, but the operation was
   *                      canceled and the element was not dropped. The original dragend event will
   *                      be provided in the local event variable.
   * - dnd-copied         Same as dnd-moved, just that it is called when the element was copied
   *                      instead of moved. The original dragend event will be provided in the local
   *                      event variable.
   * - dnd-dragstart      Callback that is invoked when the element was dragged. The original
   *                      dragstart event will be provided in the local event variable.
   * - dnd-dragend        Callback that is invoked when the drag operation ended. Available local
   *                      variables are event and dropEffect.
   * - dnd-type           Use this attribute if you have different kinds of items in your
   *                      application and you want to limit which items can be dropped into which
   *                      lists. Combine with dnd-allowed-types on the dnd-list(s). This attribute
   *                      should evaluate to a string, although this restriction is not enforced.
   * - dnd-disable-if     You can use this attribute to dynamically disable the draggability of the
   *                      element. This is useful if you have certain list items that you don't want
   *                      to be draggable, or if you want to disable drag & drop completely without
   *                      having two different code branches (e.g. only allow for admins).
   *                      **Note**: If your element is not draggable, the user is probably able to
   *                      select text or images inside of it. Since a selection is always draggable,
   *                      this breaks your UI. You most likely want to disable user selection via
   *                      CSS (see user-select).
   *
   * CSS classes:
   * - dndDragging        This class will be added to the element while the element is being
   *                      dragged. It will affect both the element you see while dragging and the
   *                      source element that stays at it's position. Do not try to hide the source
   *                      element with this class, because that will abort the drag operation.
   * - dndDraggingSource  This class will be added to the element after the drag operation was
   *                      started, meaning it only affects the original element that is still at
   *                      it's source position, and not the "element" that the user is dragging with
   *                      his mouse pointer.
   */
  .directive('dndDraggable', ['$parse', '$timeout', 'dndDropEffectWorkaround', 'dndDragTypeWorkaround',
                      function($parse,   $timeout,   dndDropEffectWorkaround,   dndDragTypeWorkaround) {
    return function(scope, element, attr) {
      // Set the HTML5 draggable attribute on the element
      element.attr("draggable", "true");

      // If the dnd-disable-if attribute is set, we have to watch that
      if (attr.dndDisableIf) {
        scope.$watch(attr.dndDisableIf, function(disabled) {
          element.attr("draggable", !disabled);
        });
      }

      /**
       * When the drag operation is started we have to prepare the dataTransfer object,
       * which is the primary way we communicate with the target element
       */
      element.on('dragstart', function(event) {
        event = event.originalEvent || event;

        // Check whether the element is draggable, since dragstart might be triggered on a child.
        if (element.attr('draggable') == 'false') return true;

        // Serialize the data associated with this element. IE only supports the Text drag type
        event.dataTransfer.setData("Text", angular.toJson(scope.$eval(attr.dndDraggable)));

        // Only allow actions specified in dnd-effect-allowed attribute
        event.dataTransfer.effectAllowed = attr.dndEffectAllowed || "move";

        // Add CSS classes. See documentation above
        element.addClass("dndDragging");
        $timeout(function() { element.addClass("dndDraggingSource"); }, 0);

        // Workarounds for stupid browsers, see description below
        dndDropEffectWorkaround.dropEffect = "none";
        dndDragTypeWorkaround.isDragging = true;

        // Save type of item in global state. Usually, this would go into the dataTransfer
        // typename, but we have to use "Text" there to support IE
        dndDragTypeWorkaround.dragType = attr.dndType ? scope.$eval(attr.dndType) : undefined;

        // Try setting a proper drag image if triggered on a dnd-handle (won't work in IE).
        if (event._dndHandle && event.dataTransfer.setDragImage) {
          event.dataTransfer.setDragImage(element[0], 0, 0);
        }

        // Invoke callback
        $parse(attr.dndDragstart)(scope, {event: event});

        event.stopPropagation();
      });

      /**
       * The dragend event is triggered when the element was dropped or when the drag
       * operation was aborted (e.g. hit escape button). Depending on the executed action
       * we will invoke the callbacks specified with the dnd-moved or dnd-copied attribute.
       */
      element.on('dragend', function(event) {
        event = event.originalEvent || event;

        // Invoke callbacks. Usually we would use event.dataTransfer.dropEffect to determine
        // the used effect, but Chrome has not implemented that field correctly. On Windows
        // it always sets it to 'none', while Chrome on Linux sometimes sets it to something
        // else when it's supposed to send 'none' (drag operation aborted).
        var dropEffect = dndDropEffectWorkaround.dropEffect;
        scope.$apply(function() {
          switch (dropEffect) {
            case "move":
              $parse(attr.dndMoved)(scope, {event: event});
              break;
            case "copy":
              $parse(attr.dndCopied)(scope, {event: event});
              break;
            case "none":
              $parse(attr.dndCanceled)(scope, {event: event});
              break;
          }
          $parse(attr.dndDragend)(scope, {event: event, dropEffect: dropEffect});
        });

        // Clean up
        element.removeClass("dndDragging");
        $timeout(function() { element.removeClass("dndDraggingSource"); }, 0);
        dndDragTypeWorkaround.isDragging = false;
        event.stopPropagation();
      });

      /**
       * When the element is clicked we invoke the callback function
       * specified with the dnd-selected attribute.
       */
      element.on('click', function(event) {
        if (!attr.dndSelected) return;

        event = event.originalEvent || event;
        scope.$apply(function() {
          $parse(attr.dndSelected)(scope, {event: event});
        });

        // Prevent triggering dndSelected in parent elements.
        event.stopPropagation();
      });

      /**
       * Workaround to make element draggable in IE9
       */
      element.on('selectstart', function() {
        if (this.dragDrop) this.dragDrop();
      });
    };
  }])

  /**
   * Use the dnd-list attribute to make your list element a dropzone. Usually you will add a single
   * li element as child with the ng-repeat directive. If you don't do that, we will not be able to
   * position the dropped element correctly. If you want your list to be sortable, also add the
   * dnd-draggable directive to your li element(s). Both the dnd-list and it's direct children must
   * have position: relative CSS style, otherwise the positioning algorithm will not be able to
   * determine the correct placeholder position in all browsers.
   *
   * Attributes:
   * - dnd-list             Required attribute. The value has to be the array in which the data of
   *                        the dropped element should be inserted.
   * - dnd-allowed-types    Optional array of allowed item types. When used, only items that had a
   *                        matching dnd-type attribute will be dropable.
   * - dnd-disable-if       Optional boolean expresssion. When it evaluates to true, no dropping
   *                        into the list is possible. Note that this also disables rearranging
   *                        items inside the list.
   * - dnd-horizontal-list  Optional boolean expresssion. When it evaluates to true, the positioning
   *                        algorithm will use the left and right halfs of the list items instead of
   *                        the upper and lower halfs.
   * - dnd-dragover         Optional expression that is invoked when an element is dragged over the
   *                        list. If the expression is set, but does not return true, the element is
   *                        not allowed to be dropped. The following variables will be available:
   *                        - event: The original dragover event sent by the browser.
   *                        - index: The position in the list at which the element would be dropped.
   *                        - type: The dnd-type set on the dnd-draggable, or undefined if unset.
   *                        - external: Whether the element was dragged from an external source.
   * - dnd-drop             Optional expression that is invoked when an element is dropped on the
   *                        list. The following variables will be available:
   *                        - event: The original drop event sent by the browser.
   *                        - index: The position in the list at which the element would be dropped.
   *                        - item: The transferred object.
   *                        - type: The dnd-type set on the dnd-draggable, or undefined if unset.
   *                        - external: Whether the element was dragged from an external source.
   *                        The return value determines the further handling of the drop:
   *                        - false: The drop will be canceled and the element won't be inserted.
   *                        - true: Signalises that the drop is allowed, but the dnd-drop
   *                          callback already took care of inserting the element.
   *                        - otherwise: All other return values will be treated as the object to
   *                          insert into the array. In most cases you want to simply return the
   *                          item parameter, but there are no restrictions on what you can return.
   * - dnd-inserted         Optional expression that is invoked after a drop if the element was
   *                        actually inserted into the list. The same local variables as for
   *                        dnd-drop will be available. Note that for reorderings inside the same
   *                        list the old element will still be in the list due to the fact that
   *                        dnd-moved was not called yet.
   * - dnd-external-sources Optional boolean expression. When it evaluates to true, the list accepts
   *                        drops from sources outside of the current browser tab. This allows to
   *                        drag and drop accross different browser tabs. Note that this will allow
   *                        to drop arbitrary text into the list, thus it is highly recommended to
   *                        implement the dnd-drop callback to check the incoming element for
   *                        sanity. Furthermore, the dnd-type of external sources can not be
   *                        determined, therefore do not rely on restrictions of dnd-allowed-type.
   *
   * CSS classes:
   * - dndPlaceholder       When an element is dragged over the list, a new placeholder child
   *                        element will be added. This element is of type li and has the class
   *                        dndPlaceholder set. Alternatively, you can define your own placeholder
   *                        by creating a child element with dndPlaceholder class.
   * - dndDragover          Will be added to the list while an element is dragged over the list.
   */
  .directive('dndList', ['$parse', '$timeout', 'dndDropEffectWorkaround', 'dndDragTypeWorkaround',
                 function($parse,   $timeout,   dndDropEffectWorkaround,   dndDragTypeWorkaround) {
    return function(scope, element, attr) {
      // While an element is dragged over the list, this placeholder element is inserted
      // at the location where the element would be inserted after dropping
      var placeholder = getPlaceholderElement();
      var placeholderNode = placeholder[0];
      var listNode = element[0];
      placeholder.remove();

      var horizontal = attr.dndHorizontalList && scope.$eval(attr.dndHorizontalList);
      var externalSources = attr.dndExternalSources && scope.$eval(attr.dndExternalSources);

      /**
       * The dragenter event is fired when a dragged element or text selection enters a valid drop
       * target. According to the spec, we either need to have a dropzone attribute or listen on
       * dragenter events and call preventDefault(). It should be noted though that no browser seems
       * to enforce this behaviour.
       */
      element.on('dragenter', function (event) {
        event = event.originalEvent || event;
        if (!isDropAllowed(event)) return true;
        event.preventDefault();
      });

      /**
       * The dragover event is triggered "every few hundred milliseconds" while an element
       * is being dragged over our list, or over an child element.
       */
      element.on('dragover', function(event) {
        event = event.originalEvent || event;

        if (!isDropAllowed(event)) return true;

        // First of all, make sure that the placeholder is shown
        // This is especially important if the list is empty
        if (placeholderNode.parentNode != listNode) {
          element.append(placeholder);
        }

        if (event.target !== listNode) {
          // Try to find the node direct directly below the list node.
          var listItemNode = event.target;
          while (listItemNode.parentNode !== listNode && listItemNode.parentNode) {
            listItemNode = listItemNode.parentNode;
          }

          if (listItemNode.parentNode === listNode && listItemNode !== placeholderNode) {
            // If the mouse pointer is in the upper half of the child element,
            // we place it before the child element, otherwise below it.
            if (isMouseInFirstHalf(event, listItemNode)) {
              listNode.insertBefore(placeholderNode, listItemNode);
            } else {
              listNode.insertBefore(placeholderNode, listItemNode.nextSibling);
            }
          }
        } else {
          // This branch is reached when we are dragging directly over the list element.
          // Usually we wouldn't need to do anything here, but the IE does not fire it's
          // events for the child element, only for the list directly. Therefore, we repeat
          // the positioning algorithm for IE here.
          if (isMouseInFirstHalf(event, placeholderNode, true)) {
            // Check if we should move the placeholder element one spot towards the top.
            // Note that display none elements will have offsetTop and offsetHeight set to
            // zero, therefore we need a special check for them.
            while (placeholderNode.previousElementSibling
                 && (isMouseInFirstHalf(event, placeholderNode.previousElementSibling, true)
                 || placeholderNode.previousElementSibling.offsetHeight === 0)) {
              listNode.insertBefore(placeholderNode, placeholderNode.previousElementSibling);
            }
          } else {
            // Check if we should move the placeholder element one spot towards the bottom
            while (placeholderNode.nextElementSibling &&
                 !isMouseInFirstHalf(event, placeholderNode.nextElementSibling, true)) {
              listNode.insertBefore(placeholderNode,
                  placeholderNode.nextElementSibling.nextElementSibling);
            }
          }
        }

        // At this point we invoke the callback, which still can disallow the drop.
        // We can't do this earlier because we want to pass the index of the placeholder.
        if (attr.dndDragover && !invokeCallback(attr.dndDragover, event, getPlaceholderIndex())) {
          return stopDragover();
        }

        element.addClass("dndDragover");
        event.preventDefault();
        event.stopPropagation();
        return false;
      });

      /**
       * When the element is dropped, we use the position of the placeholder element as the
       * position where we insert the transferred data. This assumes that the list has exactly
       * one child element per array element.
       */
      element.on('drop', function(event) {
        event = event.originalEvent || event;

        if (!isDropAllowed(event)) return true;

        // The default behavior in Firefox is to interpret the dropped element as URL and
        // forward to it. We want to prevent that even if our drop is aborted.
        event.preventDefault();

        // Unserialize the data that was serialized in dragstart. According to the HTML5 specs,
        // the "Text" drag type will be converted to text/plain, but IE does not do that.
        var data = event.dataTransfer.getData("Text") || event.dataTransfer.getData("text/plain");
        var transferredObject;
        try {
          transferredObject = JSON.parse(data);
        } catch(e) {
          return stopDragover();
        }

        // Invoke the callback, which can transform the transferredObject and even abort the drop.
        var index = getPlaceholderIndex();
        if (attr.dndDrop) {
          transferredObject = invokeCallback(attr.dndDrop, event, index, transferredObject);
          if (!transferredObject) {
            return stopDragover();
          }
        }

        // Insert the object into the array, unless dnd-drop took care of that (returned true).
        if (transferredObject !== true) {
          scope.$apply(function() {
            scope.$eval(attr.dndList).splice(index, 0, transferredObject);
          });
        }
        invokeCallback(attr.dndInserted, event, index, transferredObject);

        // In Chrome on Windows the dropEffect will always be none...
        // We have to determine the actual effect manually from the allowed effects
        if (event.dataTransfer.dropEffect === "none") {
          if (event.dataTransfer.effectAllowed === "copy" ||
              event.dataTransfer.effectAllowed === "move") {
            dndDropEffectWorkaround.dropEffect = event.dataTransfer.effectAllowed;
          } else {
            dndDropEffectWorkaround.dropEffect = event.ctrlKey ? "copy" : "move";
          }
        } else {
          dndDropEffectWorkaround.dropEffect = event.dataTransfer.dropEffect;
        }

        // Clean up
        stopDragover();
        event.stopPropagation();
        return false;
      });

      /**
       * We have to remove the placeholder when the element is no longer dragged over our list. The
       * problem is that the dragleave event is not only fired when the element leaves our list,
       * but also when it leaves a child element -- so practically it's fired all the time. As a
       * workaround we wait a few milliseconds and then check if the dndDragover class was added
       * again. If it is there, dragover must have been called in the meantime, i.e. the element
       * is still dragging over the list. If you know a better way of doing this, please tell me!
       */
      element.on('dragleave', function(event) {
        event = event.originalEvent || event;

        element.removeClass("dndDragover");
        $timeout(function() {
          if (!element.hasClass("dndDragover")) {
            placeholder.remove();
          }
        }, 100);
      });

      /**
       * Checks whether the mouse pointer is in the first half of the given target element.
       *
       * In Chrome we can just use offsetY, but in Firefox we have to use layerY, which only
       * works if the child element has position relative. In IE the events are only triggered
       * on the listNode instead of the listNodeItem, therefore the mouse positions are
       * relative to the parent element of targetNode.
       */
      function isMouseInFirstHalf(event, targetNode, relativeToParent) {
        var mousePointer = horizontal ? (event.offsetX || event.layerX)
                                      : (event.offsetY || event.layerY);
        var targetSize = horizontal ? targetNode.offsetWidth : targetNode.offsetHeight;
        var targetPosition = horizontal ? targetNode.offsetLeft : targetNode.offsetTop;
        targetPosition = relativeToParent ? targetPosition : 0;
        return mousePointer < targetPosition + targetSize / 2;
      }

      /**
       * Tries to find a child element that has the dndPlaceholder class set. If none was found, a
       * new li element is created.
       */
      function getPlaceholderElement() {
        var placeholder;
        angular.forEach(element.children(), function(childNode) {
          var child = angular.element(childNode);
          if (child.hasClass('dndPlaceholder')) {
            placeholder = child;
          }
        });
        return placeholder || angular.element("<li class='dndPlaceholder'></li>");
      }

      /**
       * We use the position of the placeholder node to determine at which position of the array the
       * object needs to be inserted
       */
      function getPlaceholderIndex() {
        return Array.prototype.indexOf.call(listNode.children, placeholderNode);
      }

      /**
       * Checks various conditions that must be fulfilled for a drop to be allowed
       */
      function isDropAllowed(event) {
        // Disallow drop from external source unless it's allowed explicitly.
        if (!dndDragTypeWorkaround.isDragging && !externalSources) return false;

        // Check mimetype. Usually we would use a custom drag type instead of Text, but IE doesn't
        // support that.
        if (!hasTextMimetype(event.dataTransfer.types)) return false;

        // Now check the dnd-allowed-types against the type of the incoming element. For drops from
        // external sources we don't know the type, so it will need to be checked via dnd-drop.
        if (attr.dndAllowedTypes && dndDragTypeWorkaround.isDragging) {
          var allowed = scope.$eval(attr.dndAllowedTypes);
          if (angular.isArray(allowed) && allowed.indexOf(dndDragTypeWorkaround.dragType) === -1) {
            return false;
          }
        }

        // Check whether droping is disabled completely
        if (attr.dndDisableIf && scope.$eval(attr.dndDisableIf)) return false;

        return true;
      }

      /**
       * Small helper function that cleans up if we aborted a drop.
       */
      function stopDragover() {
        placeholder.remove();
        element.removeClass("dndDragover");
        return true;
      }

      /**
       * Invokes a callback with some interesting parameters and returns the callbacks return value.
       */
      function invokeCallback(expression, event, index, item) {
        return $parse(expression)(scope, {
          event: event,
          index: index,
          item: item || undefined,
          external: !dndDragTypeWorkaround.isDragging,
          type: dndDragTypeWorkaround.isDragging ? dndDragTypeWorkaround.dragType : undefined
        });
      }

      /**
       * Check if the dataTransfer object contains a drag type that we can handle. In old versions
       * of IE the types collection will not even be there, so we just assume a drop is possible.
       */
      function hasTextMimetype(types) {
        if (!types) return true;
        for (var i = 0; i < types.length; i++) {
          if (types[i] === "Text" || types[i] === "text/plain") return true;
        }

        return false;
      }
    };
  }])

  /**
   * Use the dnd-nodrag attribute inside of dnd-draggable elements to prevent them from starting
   * drag operations. This is especially useful if you want to use input elements inside of
   * dnd-draggable elements or create specific handle elements. Note: This directive does not work
   * in Internet Explorer 9.
   */
  .directive('dndNodrag', function() {
    return function(scope, element, attr) {
      // Set as draggable so that we can cancel the events explicitly
      element.attr("draggable", "true");

      /**
       * Since the element is draggable, the browser's default operation is to drag it on dragstart.
       * We will prevent that and also stop the event from bubbling up.
       */
      element.on('dragstart', function(event) {
        event = event.originalEvent || event;

        if (!event._dndHandle) {
          // If a child element already reacted to dragstart and set a dataTransfer object, we will
          // allow that. For example, this is the case for user selections inside of input elements.
          if (!(event.dataTransfer.types && event.dataTransfer.types.length)) {
            event.preventDefault();
          }
          event.stopPropagation();
        }
      });

      /**
       * Stop propagation of dragend events, otherwise dnd-moved might be triggered and the element
       * would be removed.
       */
      element.on('dragend', function(event) {
        event = event.originalEvent || event;
        if (!event._dndHandle) {
          event.stopPropagation();
        }
      });
    };
  })

  /**
   * Use the dnd-handle directive within a dnd-nodrag element in order to allow dragging with that
   * element after all. Therefore, by combining dnd-nodrag and dnd-handle you can allow
   * dnd-draggable elements to only be dragged via specific "handle" elements. Note that Internet
   * Explorer will show the handle element as drag image instead of the dnd-draggable element. You
   * can work around this by styling the handle element differently when it is being dragged. Use
   * the CSS selector .dndDragging:not(.dndDraggingSource) [dnd-handle] for that.
   */
  .directive('dndHandle', function() {
    return function(scope, element, attr) {
      element.attr("draggable", "true");

      element.on('dragstart dragend', function(event) {
        event = event.originalEvent || event;
        event._dndHandle = true;
      });
    };
  })

  /**
   * This workaround handles the fact that Internet Explorer does not support drag types other than
   * "Text" and "URL". That means we can not know whether the data comes from one of our elements or
   * is just some other data like a text selection. As a workaround we save the isDragging flag in
   * here. When a dropover event occurs, we only allow the drop if we are already dragging, because
   * that means the element is ours.
   */
  .factory('dndDragTypeWorkaround', function(){ return {} })

  /**
   * Chrome on Windows does not set the dropEffect field, which we need in dragend to determine
   * whether a drag operation was successful. Therefore we have to maintain it in this global
   * variable. The bug report for that has been open for years:
   * https://code.google.com/p/chromium/issues/detail?id=39399
   */
  .factory('dndDropEffectWorkaround', function(){ return {} });

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


/**
 * Grouping Modal
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
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

			if ( typeof $scope.status.plugin_id == 'object' ) {
				var plugin_id = $scope.status.plugin_id.join( '*!.*!' );
			} else {
				var plugin_id = $scope.status.plugin_id;
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

			console.log( groups );
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
		$scope.createForm.fn.closeForm   = function( ignoreGoBack) {
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
			$scope.createForm.data.action    = 'Plugin Manager Pro : Edit Group';

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
			$scope.createForm.data.action    = 'Plugin Manager Pro : Delete Group';

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




