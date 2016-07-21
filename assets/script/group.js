jQuery( document ).ready( function( $ ) {
	// <!-- Binding 개별 항목 액션
	$( '.button-grouping' ).click( function( e ) {	// 각 플러그인의 그룹 버튼 클릭시
		e.preventDefault();

		// 만일 열려있다면? 닫고 끝
		if ( $(this).hasClass( 'group_open' ) ) {
			CloseGrouping();
			return true;
		}

		// 일단 전부 닫고,
		CloseGrouping();

		// 현재 플러그인의 아이디 추출
		var plugin_id = $(this).attr( 'data-plugin' );
		// 폼 클론 뜨고, id와 for 부여
		var $groupingRow = $( '#Grouping-Row' ).clone();

		// tr 삽입
		GetRow( plugin_id ).first().after( '<tr class="inactive plugin_grouper_wrap" data-plugin="' + plugin_id + '"><td colspan="1000">' + $groupingRow.html() + '</td></tr>' );

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
		BindColorPicker();
		BindButtonClose();
		BindButtonCreate();
		RunCheckbox();
		PreCheckCheckbox( plugin_id );

		return true;
	});
	// Binding 개별 항목 액션 -->

	// <!-- 가져오기 시리즈 ( 체크박스 )
	function GetCheckbox( checkbox_id ) {
		if ( checkbox_id ) {
			checkbox_id = checkbox_id.replace( /([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1' );
			return $( '.plugin_grouper_wrap input[type="checkbox"][data-id="' + checkbox_id +'"]' );
		}

		return $( '.plugin_grouper_wrap input[type="checkbox"]' );
	}
	// 체크된 체크박스 수
	function NumCheckedCheckbox() {
		var number = 0;

		GetCheckbox().each( function() {
			if ( $(this).prop('checked') )
				number++;
		});

		return number;
	}

	// <!-- 가져오기 시리즈 ( 플러그인 테이블 로우 )
	function GetRow( plugin_id ) {
		if ( plugin_id ) {
			return $( '.wp-list-table.plugins tr[data-plugin="' + plugin_id + '"]' );
		}

		// 없음 입력창
		return $( '.plugin_grouper_wrap' );
	}
	// 가져오기 시리즈 -->

	// <!-- 체크박스를 클릭했을 때
	function RunCheckbox() {
		GetCheckbox().click( function() {
			var plugin_id = $(this).attr( 'data-plugin-id' );
			var group_id = $(this).attr( 'data-id' );
			var group_name = $(this).attr( 'data-name' );

			var data = {
				'mode' : 'Plugin Manager',
				'plugin_id' : plugin_id,
				'group_id' : group_id,
				'group_name' : group_name
			};

			DisableGrouping();

			// 그룹에 추가
			if ( $(this).is( ":checked" ) ) {
				data.action = 'PIGPR_INPUT_INTO_GROUP';

				$.post( ajaxurl, data, function( response ) {
					var html = '<a href="' + response.url + '" data-id="' + group_id + '" style="background-color:' + response.bgcolor + '; color:' + response.color + '" data-id="' + group_id + '" data-bgcolor="' + response.bgcolor + '" data-color="' + response.color + '">'+group_name+'</a>';
					GetRow( plugin_id ).find( 'td.column-description .groups' ).append( html );
					EnableGrouping();

					// 숫자 변경
					var number = $( '.subsubsub.plugin-groups li.' + group_id + ' .count' ).html();
					number = parseInt( number.substr( 1, number.length - 2 ) ) + 1;

					$( '.subsubsub.plugin-groups li.' + group_id + ' .count' ).html( '(' + number + ')' );

					ChangeNonNumber( 'trace adding' );

				}, 'json' );

			// 그룹에서 제외
			} else {
				data.action = 'PIGPR_DELETE_FROM_GROUP';

				$.post( ajaxurl, data, function( response ) {
					GetRow( plugin_id ).find( 'td.column-description .groups a[data-id="'+ group_id +'"]' ).remove();

					EnableGrouping();

					// 숫자 변경
					var number = $( '.subsubsub.plugin-groups li.' + group_id + ' .count' ).html();
					number = parseInt( number.substr( 1, number.length - 2 ) ) - 1;

					$( '.subsubsub.plugin-groups li.' + group_id + ' .count' ).html( '(' + number + ')' );

					ChangeNonNumber( 'trace subtraction' );
				}, 'json' );
			}
		});
	}

	function ChangeNonNumber( mode ) {
		var num_checkboxes = NumCheckedCheckbox();

		// 지정된 체크박스가 하나라면? (없다가 하나 생김) || 체크박스가 없다면? (있다가 없어짐)
		if ( num_checkboxes == 1 || num_checkboxes == 0 ) {
			var number = $( '.subsubsub.plugin-groups li.not-in-any-groups .count' ).html();
			number = parseInt( number.substr( 1, number.length - 2 ) );

			if ( num_checkboxes == 1 && mode == 'trace adding' )
				number--;

			if ( num_checkboxes == 0 && mode == 'trace subtraction'  )
				number++;

			$( '.subsubsub.plugin-groups li.not-in-any-groups .count' ).html( '(' + number + ')' );
		}
	}
	// 체크박스를 클릭했을 때 -->

	// 입력창 닫기
	function CloseGrouping() {
		GetRow().remove();
		$( '.group_open' ).removeClass( 'group_open' );
	}

	// <-- 셀렉트 폼 일시정지 & 재가동
	function DisableGrouping() {
		$( '.wp-list-table.plugins .loading_spinner' ).show();
		GetCheckbox().attr( 'disabled', true );
	}
	function EnableGrouping() {
		$( '.wp-list-table.plugins .loading_spinner' ).hide();
		GetCheckbox().removeAttr( 'disabled' );
	}
	// 셀렉트 폼 일시정지 & 재가동 -->

	// <!-- 그룹 윈도우 내부 버튼들 (생성, 닫기)
	function BindButtonClose() {
		$( '.wp-list-table.plugins .btn-close_group' ).click( function(e) {
			e.preventDefault();
			CloseGrouping();
			return true;
		});
	}
	function BindButtonCreate() {
		$( '.wp-list-table.plugins .inp-create_group' ).keypress( function(e) {
			if ( e.which === 10 || e.which === 13 ) {
				$( '.wp-list-table.plugins .btn-create_group' ).click();
				e.preventDefault();
			}
		});

		$( '.wp-list-table.plugins .btn-create_group' ).click( function(e) {
			e.preventDefault();

			if ( $( '.wp-list-table.plugins .inp-create_group' ).val().length ) {
				var plugin_id = $( '.plugin_grouper_wrap' ).attr( 'data-plugin' );
				var data = {
					'action': 'PIGPR_CREATE_GROUP',
					'mode' : 'Plugin Manager',
					'group_name' : $( '.wp-list-table.plugins .inp-create_group' ).val(),
					'plugin_id' : plugin_id
				};

				DisableGrouping();

				$.post( ajaxurl, data, function( response ) {
					EnableGrouping();

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

					// Subsubsub
					$( '.subsubsub.plugin-groups li:last-child a' ).after( ' |' );
					$( '.subsubsub.plugin-groups li:last-child a' ).parent().after( '<li class="group ' + group_name + '"><a href="' + url + '" >' + group_name + '</a> <span class="count">(0)</span></li>' );

					RunCheckbox();
					BindColorPicker();

					$( '.wp-list-table.plugins .inp-create_group' ).val('');
					$( '#group_radio_' + index ).click();

					ChangeNonNumber( 'trace adding' );
				}, 'json' );
			} else {
				$( '.wp-list-table.plugins .inp-create_group' ).focus();
			}
			return true;
		});
	}

	function BindColorPicker() {
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
						'color' : color.toHexString(),
						'mode' : 'Plugin Manager'
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
	function PreCheckCheckbox( plugin_id ) {
		GetCheckbox().removeAttr( 'checked' );

		GetRow( plugin_id ).find( 'td.column-description .groups a' ).each( function() {
			var id = $(this).attr( 'data-id' );
			GetCheckbox( id ).attr( 'checked', true );
		});
	}
	// 플러그인 선택했을 때 체크박스 체크하기 -->
});

