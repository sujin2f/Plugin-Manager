jQuery( document ).ready( function( $ ) {
	// <!-- 그룹 보기 모드일 경우 타이틀 바꾸기
	if ( $( 'input#plugin_group_name' ).length ) {
		change_header( $( 'input#plugin_group_name' ).val() );
		change_links( getUrlParameter( 'plugin_group' ) );
	}

	function change_header( plugin_group_name ) {
		var plugin_group_id = getUrlParameter( 'plugin_group' );

		var html = objectL10n.plugin_group + ' : ' + plugin_group_name;
		html += ' <a href="#" class="add-new-h2 btn-delete_group page-title-action">' + objectL10n.delete_group + '</a>';

		$( '#wpbody .wrap' ).children().first().html( html );

		$( '.btn-delete_group' ).click( function(e) {
			e.preventDefault();
			window.location.href = window.location.href + "&action=delete_group&group_id=" + plugin_group_id;
		});
	}
	function change_links( plugin_group ) {
		$( '.wp-list-table.plugins tbody tr' ).each( function() {
			var _activate = $(this).find( 'td.plugin-title .activate a' ).attr( 'href' ) + '&plugin_group=' + plugin_group;
			var _deactivate = $(this).find( 'td.plugin-title .deactivate a' ).attr( 'href' ) + '&plugin_group=' + plugin_group;
			var _delete = $(this).find( 'td.plugin-title .delete a' ).attr( 'href' ) + '&plugin_group=' + plugin_group;

			$(this).find( 'td.plugin-title .activate a' ).attr( 'href', _activate );
			$(this).find( 'td.plugin-title .deactivate a' ).attr( 'href', _deactivate );
			$(this).find( 'td.plugin-title .delete a' ).attr( 'href', _delete );
		});
	}
	// 그룹 보기 모드일 경우 타이틀 바꾸기 -->

	function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++)  {
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] === sParam)  {
				return sParameterName[1];
			}
		}
	}
	function escape_special_character( text ) {
		text = text.replace( /([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1' );
		return text;
	}

	// <!-- 가져오기 시리즈 ( 체크박스 )
	function get_checkbox( checkbox_id ) {
		if ( checkbox_id ) {
			return $( '.plugin_grouper_wrap input[type="checkbox"][data-id="' + escape_special_character( checkbox_id ) +'"]' );
		}

		return $( '.plugin_grouper_wrap input[type="checkbox"]' );
	}

	// <!-- 가져오기 시리즈 ( 플러그인 테이블 로우 )
	function get_row( plugin_id ) {
		if ( plugin_id ) {
			var row = $( '.wp-list-table.plugins tr#' + escape_special_character( plugin_id ) );

			if ( row.length == 0 )
				row = $( '.wp-list-table.plugins tr[data-slug="' + escape_special_character( plugin_id ) + '"]' );

			return row;
		}

		// 없음 입력창
		return $( '.plugin_grouper_wrap' );
	}
	// 가져오기 시리즈 -->

	// <!-- Binding 개별 항목 액션
	$( '.button-grouping' ).click( function( e ) {	// 각 플러그인의 그룹 버튼 클릭시
		e.preventDefault();

		// 만일 열려있다면? 닫고 끝
		if ( $(this).hasClass( 'group_open' ) ) {
			close_grouping();
			return true;
		}
		// 일단 전부 닫고,
		close_grouping();

		// 현재 플러그인의 아이디 추출
		var plugin_id = $(this).attr( 'data-id' );
		// 폼 클론 뜨고, id와 for 부여
		var $groupingRow = $( '#Grouping-Row' ).clone();

		// tr 삽입
		get_row( plugin_id ).first().after( '<tr class="inactive plugin_grouper_wrap" data-id="' + plugin_id + '"><td colspan="1000">' + $groupingRow.html() + '</td></tr>' );

		// radio 버튼 조정
		$( '.plugin_grouper_wrap li' ).each( function( number ) {
			var id = 'group_radio_' + number;

			$(this).find( 'input' ).attr( 'data-plugin-id', plugin_id );
			$(this).find( 'input' ).attr( 'id', id );
			$(this).find( 'label' ).attr( 'for', id );

		});

		// 클라스 지정
		$(this).addClass( 'group_open' );

		// 바인딩
		bind_color_picker();
		bind_button_close();
		bind_button_create();
		checkbox_action();
		checkbox_checking( plugin_id );

		return true;
	});
	// Binding 개별 항목 액션 -->

	// <!-- 체크박스를 클릭했을 때
	function checkbox_action() {
		get_checkbox().click( function() {
			var plugin_id = $(this).attr( 'data-plugin-id' );
			var group_id = $(this).attr( 'data-id' );
			var group_name = $(this).attr( 'data-name' );

			var data = {
				'plugin_id' : plugin_id,
				'group_id' : group_id,
				'group_name' : group_name
			};

			disable_grouping();

			// 그룹에 추가
			if ( $(this).is( ":checked" ) ) {
				data.action = 'PIGPR_INPUT_INTO_GROUP';

				$.post( ajaxurl, data, function( response ) {
					var html = '<a href="' + response.url + '" data-id="' + group_id + '" style="background-color:' + response.bgcolor + '; color:' + response.color + '" data-id="' + group_id + '" data-bgcolor="' + response.bgcolor + '" data-color="' + response.color + '">'+group_name+'</a>';
					get_row( plugin_id ).find( 'td.column-description .groups' ).append( html );
					enable_grouping();
				}, 'json' );

			// 그룹에서 제외
			} else {
				data.action = 'PIGPR_DELETE_FROM_GROUP';

				$.post( ajaxurl, data, function( response ) {
					get_row( plugin_id ).find( 'td.column-description .groups a[data-id="'+ group_id +'"]' ).remove();

					enable_grouping();
				}, 'json' );
			}
		});
	}
	// 체크박스를 클릭했을 때 -->

	// <!-- 입력창 닫기
	function close_grouping() {
		get_row().remove();
		$( '.group_open' ).removeClass( 'group_open' );
	}
	// 입력창 닫기 -->

	// <-- 셀렉트 폼 일시정지 & 재가동
	function disable_grouping() {
		$( '.wp-list-table.plugins .loading_spinner' ).show();
		get_checkbox().attr( 'disabled', true );
	}
	function enable_grouping() {
		$( '.wp-list-table.plugins .loading_spinner' ).hide();
		get_checkbox().removeAttr( 'disabled' );
	}
	// 셀렉트 폼 일시정지 & 재가동 -->

	// <!-- 그룹 윈도우 내부 버튼들 (생성, 닫기)
	function bind_button_close() {
		$( '.wp-list-table.plugins .btn-close_group' ).click( function(e) {
			e.preventDefault();
			close_grouping();
			return true;
		});
	}
	function bind_button_create() {
		$( '.wp-list-table.plugins .inp-create_group' ).keypress( function(e) {
			if ( e.which === 10 || e.which === 13 ) {
				$( '.wp-list-table.plugins .btn-create_group' ).click();
				e.preventDefault();
			}
		});

		$( '.wp-list-table.plugins .btn-create_group' ).click( function(e) {
			e.preventDefault();

			if ( $( '.wp-list-table.plugins .inp-create_group' ).val().length ) {
				var plugin_id = $( '.plugin_grouper_wrap' ).attr( 'data-id' );
				var data = {
					'action': 'PIGPR_CREATE_GROUP',
					'group_name' : $( '.wp-list-table.plugins .inp-create_group' ).val(),
					'plugin_id' : plugin_id
				};

				disable_grouping();

				$.post( ajaxurl, data, function( response ) {
					enable_grouping();

					var url = response.url;
					var group_id = response.group_id;
					var group_name = response.group_name;
					var bgcolor = response.bgcolor;
					var color = response.color;

					$( '.plugin_grouper_wrap ul' ).append( '<li></li>' );
					$( '#Grouping-Row ul' ).append( '<li></li>' );

					var $li = $( '.plugin_grouper_wrap ul li:last-child' );
					var $gr_li = $( '#Grouping-Row ul li:last-child' );

					var index = $li.index();

					var html = '';
					html = '<label for="group_radio_' + index + '">';
					html += '<input id="group_radio_' + index + '" type="checkbox" data-id="' + group_id + '"  data-name="' + group_name + '" data-plugin-id="' + plugin_id + '" />';
					html += group_name;
					html += '</label>';
					html += '</label>';
					html += '<input type="text" value="' + bgcolor + '" class="group_colour_picker" data-id="' + group_id + '" />';

					$li.html( html );

					html = '<input type="checkbox" data-id="' + group_id + '"  data-name="' + group_name + '" data-plugin-id="' + plugin_id + '" />';
					html += '<label>' + group_name + '</label>';

					$gr_li.html( html );

					$( '.subsubsub li:last-child a.group' ).after( ' |' );
					$( '.subsubsub li:last-child a.group' ).parent().after( '<li class="group"><a href="' + url + '" data-bgcolor="' + bgcolor + '" data-color="' + color + '" data-id="' + group_id + '" class="group">' + group_name + '</a></li>' );
					$( '.subsubsub li:last-child a.group' ).css({
						'background-color' : $( '.subsubsub li:last-child a.group' ).attr( 'data-bgcolor' ),
						'color' : $( '.subsubsub li:last-child a.group' ).attr( 'data-color' )
					});

					checkbox_action();
					bind_color_picker();

					$( '.wp-list-table.plugins .inp-create_group' ).val('');
					$( '#group_radio_' + index ).click();
				}, 'json' );
			} else {
				$( '.wp-list-table.plugins .inp-create_group' ).focus();
			}
			return true;
		});
	}
	function bind_color_picker() {
		$( '.wp-list-table.plugins tr.plugin_grouper_wrap .group_colour_picker' ).each( function() {
			var group_id = $(this).attr( 'data-id' );

			$(this).spectrum({
				showPaletteOnly: true,
				color: $(this).val(),
				palette:[
					["#000000","#444444","#666666","#999999","#CCCCCC","#EEEEEE","#F3F3F3","#FFFFFF"],
					["#F00F00","#F90F90","#FF0FF0","#0F00F0","#0FF0FF","#00F00F","#90F90F","#F0FF0F"],
					["#F4CCCC","#FCE5CD","#FFF2CC","#D9EAD3","#D0E0E3","#CFE2F3","#D9D2E9","#EAD1DC"],
					["#EA9999","#F9CB9C","#FFE599","#B6D7A8","#A2C4C9","#9FC5E8","#B4A7D6","#D5A6BD"],
					["#E06666","#F6B26B","#FFD966","#93C47D","#76A5AF","#6FA8DC","#8E7CC3","#C27BA0"],
					["#C00C00","#E69138","#F1C232","#6AA84F","#45818E","#3D85C6","#674EA7","#A64D79"],
					["#900900","#B45F06","#BF9000","#38761D","#134F5C","#0B5394","#351C75","#741B47"],
					["#600600","#783F04","#7F6000","#274E13","#0C343D","#073763","#20124D","#4C1130"]
				],
				change:function(color) {
					var data = {
						'action' : 'PIGPR_SET_GROUP_COLOR',
						'group_id' : group_id,
						'color' : color.toHexString()
					};

					$.post( ajaxurl, data, function( response ) {
						$( '.plugin-version-author-uri div.groups a[data-id="' + group_id + '"], .subsubsub.plugin-groups li.group a[data-id="' + group_id + '"]' ).css({
							'background-color' : response.bgcolor,
							'color' : response.color
						}).attr( 'data-bgcolor', response.bgcolor ).attr( 'data-color', response.color );
					}, 'json' );
				}
			});
		});
	}
	// 그룹 윈도우 내부 버튼들 (생성, 닫기) -->

	// <!-- 플러그인 선택했을 때 체크박스 체크하기
	function checkbox_checking( plugin_id ) {
		get_checkbox().removeAttr( 'checked' );

		get_row( plugin_id ).find( 'td.column-description .groups a' ).each( function() {
			var id = $(this).attr( 'data-id' );
			get_checkbox( id ).attr( 'checked', true );
		});
	}
	// 플러그인 선택했을 때 체크박스 체크하기 -->
});

