function getUrlParameter(t){for(var n=window.location.search.substring(1),a=n.split("&"),i=0;i<a.length;i++){var e=a[i].split("=");if(e[0]===t)return e[1]}}jQuery(document).ready(function($){$("#group-manager-setting-text").click(function(t){var n={action:"PIGPR Setting Text",mode:"Plugin Manager",status:$("#group-manager-setting-text:checked").length};$obj=$(this),$.post(ajaxurl,n,function(t){t?$(".button-plugin-manager .text").addClass("hidden"):$(".button-plugin-manager .text").removeClass("hidden")},"json")}),$("#group-manager-setting-hidden").click(function(t){var n={action:"PIGPR Setting Hidden",mode:"Plugin Manager",status:$("#group-manager-setting-hidden:checked").length};$obj=$(this),$.post(ajaxurl,n,function(t){t?$(".wp-list-table > tbody > tr.hidden").addClass("show"):$(".wp-list-table > tbody > tr.hidden").removeClass("show")},"json")})}),jQuery(document).ready(function($){function t(t){var n=getUrlParameter("plugin_group"),a=objectL10n.plugin_group+" : "+t;a+=' <a href="#" class="add-new-h2 btn-delete_group page-title-action">'+objectL10n.delete_group+"</a>",$("#wpbody .wrap").children().first().html(a),$(".btn-delete_group").click(function(t){t.preventDefault(),window.location.href=window.location.href+"&action=delete_group&group_id="+n})}function n(t){$(".wp-list-table.plugins tbody tr").each(function(){var n=$(this).find("td.plugin-title .activate a").attr("href")+"&plugin_group="+t,a=$(this).find("td.plugin-title .deactivate a").attr("href")+"&plugin_group="+t,i=$(this).find("td.plugin-title .delete a").attr("href")+"&plugin_group="+t;$(this).find("td.plugin-title .activate a").attr("href",n),$(this).find("td.plugin-title .deactivate a").attr("href",a),$(this).find("td.plugin-title .delete a").attr("href",i)})}function a(t){return t=t.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,"\\$1")}function i(t){return $(t?'.plugin_grouper_wrap input[type="checkbox"][data-id="'+a(t)+'"]':'.plugin_grouper_wrap input[type="checkbox"]')}function e(t){if(t){var n=$(".wp-list-table.plugins tr#"+a(t));return 0==n.length&&(n=$('.wp-list-table.plugins tr[data-slug="'+a(t)+'"]')),n}return $(".plugin_grouper_wrap")}function o(){i().click(function(){var t=$(this).attr("data-plugin-id"),n=$(this).attr("data-id"),a=$(this).attr("data-name"),i={mode:"Plugin Manager",plugin_id:t,group_id:n,group_name:a};l(),$(this).is(":checked")?(i.action="PIGPR_INPUT_INTO_GROUP",$.post(ajaxurl,i,function(i){var o='<a href="'+i.url+'" data-id="'+n+'" style="background-color:'+i.bgcolor+"; color:"+i.color+'" data-id="'+n+'" data-bgcolor="'+i.bgcolor+'" data-color="'+i.color+'">'+a+"</a>";e(t).find("td.column-description .groups").append(o),s()},"json")):(i.action="PIGPR_DELETE_FROM_GROUP",$.post(ajaxurl,i,function(a){e(t).find('td.column-description .groups a[data-id="'+n+'"]').remove(),s()},"json"))})}function r(){e().remove(),$(".group_open").removeClass("group_open")}function l(){$(".wp-list-table.plugins .loading_spinner").show(),i().attr("disabled",!0)}function s(){$(".wp-list-table.plugins .loading_spinner").hide(),i().removeAttr("disabled")}function u(){$(".wp-list-table.plugins .btn-close_group").click(function(t){return t.preventDefault(),r(),!0})}function d(){$(".wp-list-table.plugins .inp-create_group").keypress(function(t){10!==t.which&&13!==t.which||($(".wp-list-table.plugins .btn-create_group").click(),t.preventDefault())}),$(".wp-list-table.plugins .btn-create_group").click(function(t){if(t.preventDefault(),$(".wp-list-table.plugins .inp-create_group").val().length){var n=$(".plugin_grouper_wrap").attr("data-id"),a={action:"PIGPR_CREATE_GROUP",mode:"Plugin Manager",group_name:$(".wp-list-table.plugins .inp-create_group").val(),plugin_id:n};l(),$.post(ajaxurl,a,function(t){s();var a=t.url,i=t.group_id,e=t.group_name,r=t.bgcolor,l=t.color;$(".plugin_grouper_wrap ul").append("<li></li>"),$("#Grouping-Row ul").append("<li></li>");var u=$(".plugin_grouper_wrap ul li:last-child"),d=$("#Grouping-Row ul li:last-child"),p=u.index(),g="";g='<label for="group_radio_'+p+'">',g+='<input id="group_radio_'+p+'" type="checkbox" data-id="'+i+'"  data-name="'+e+'" data-plugin-id="'+n+'" />',g+=e,g+="</label>",g+="</label>",g+='<input type="text" value="'+r+'" class="group_colour_picker" data-id="'+i+'" />',u.html(g),g='<input type="checkbox" data-id="'+i+'"  data-name="'+e+'" data-plugin-id="'+n+'" />',g+="<label>"+e+"</label>",d.html(g),$(".subsubsub li:last-child a.group").after(" |"),$(".subsubsub li:last-child a.group").parent().after('<li class="group"><a href="'+a+'" data-bgcolor="'+r+'" data-color="'+l+'" data-id="'+i+'" class="group">'+e+"</a></li>"),$(".subsubsub li:last-child a.group").css({"background-color":$(".subsubsub li:last-child a.group").attr("data-bgcolor"),color:$(".subsubsub li:last-child a.group").attr("data-color")}),o(),c(),$(".wp-list-table.plugins .inp-create_group").val(""),$("#group_radio_"+p).click()},"json")}else $(".wp-list-table.plugins .inp-create_group").focus();return!0})}function c(){$(".wp-list-table.plugins tr.plugin_grouper_wrap .group_colour_picker").each(function(){var t=$(this).attr("data-id");$(this).spectrum({showPaletteOnly:!0,color:$(this).val(),palette:[["#000000","#444444","#666666","#999999","#CCCCCC","#EEEEEE","#F3F3F3","#FFFFFF"],["#F00F00","#F90F90","#FF0FF0","#0F00F0","#0FF0FF","#00F00F","#90F90F","#F0FF0F"],["#F4CCCC","#FCE5CD","#FFF2CC","#D9EAD3","#D0E0E3","#CFE2F3","#D9D2E9","#EAD1DC"],["#EA9999","#F9CB9C","#FFE599","#B6D7A8","#A2C4C9","#9FC5E8","#B4A7D6","#D5A6BD"],["#E06666","#F6B26B","#FFD966","#93C47D","#76A5AF","#6FA8DC","#8E7CC3","#C27BA0"],["#C00C00","#E69138","#F1C232","#6AA84F","#45818E","#3D85C6","#674EA7","#A64D79"],["#900900","#B45F06","#BF9000","#38761D","#134F5C","#0B5394","#351C75","#741B47"],["#600600","#783F04","#7F6000","#274E13","#0C343D","#073763","#20124D","#4C1130"]],change:function(n){var a={action:"PIGPR_SET_GROUP_COLOR",group_id:t,color:n.toHexString()};$.post(ajaxurl,a,function(n){$('.plugin-version-author-uri div.groups a[data-id="'+t+'"], .subsubsub.plugin-groups li.group a[data-id="'+t+'"]').css({"background-color":n.bgcolor,color:n.color}).attr("data-bgcolor",n.bgcolor).attr("data-color",n.color)},"json")}})})}function p(t){i().removeAttr("checked"),e(t).find("td.column-description .groups a").each(function(){var t=$(this).attr("data-id");i(t).attr("checked",!0)})}$("input#plugin_group_name").length&&(t($("input#plugin_group_name").val()),n(getUrlParameter("plugin_group"))),$(".button-grouping").click(function(t){if(t.preventDefault(),$(this).hasClass("group_open"))return r(),!0;r();var n=$(this).attr("data-id"),a=$("#Grouping-Row").clone();return e(n).first().after('<tr class="inactive plugin_grouper_wrap" data-id="'+n+'"><td colspan="1000">'+a.html()+"</td></tr>"),$(".plugin_grouper_wrap li").each(function(t){var a="group_radio_"+t;$(this).find("input").attr("data-plugin-id",n),$(this).find("input").attr("id",a),$(this).find("label").attr("for",a)}),$(this).addClass("group_open"),c(),u(),d(),o(),p(n),!0})}),jQuery(document).ready(function($){function t(){$(".button-lock").unbind(),$(".button-unlock").unbind(),$(".button-lock").click(function(t){t.preventDefault();var a=$(this).attr("data-plugin_file"),i={action:"PIGPR_LOCK",mode:"Plugin Manager",plugin_file:a};$obj=$(this),$.post(ajaxurl,i,function(t){n($obj)},"json")}),$(".button-unlock").click(function(t){t.preventDefault();var n=$(this).attr("data-plugin_file"),i={action:"PIGPR_UNLOCK",mode:"Plugin Manager",plugin_file:n};$obj=$(this),$.post(ajaxurl,i,function(t){a($obj)},"json")})}function n(n){var a=n.find(".text").hasClass("hidden")?"hidden":"";n.parents("tr").addClass("locked"),n.parents("tr").find('th.check-column input[type="checkbox"]').attr("type","hidden"),n.parents("tr").find("th.check-column input").hide(),n.parents("tr").find("th.check-column").prepend('<span class="dashicons dashicons-lock locked"></span>'),n.parents("tr").find(".row-actions .activate").hide(),n.parents("tr").find(".row-actions .deactivate").hide(),n.parents("tr").find(".row-actions .delete").hide(),n.html('<span class="dashicons dashicons-unlock"></span><span class="text  '+a+'">'+objectL10n.unlock+"</span>"),n.removeClass("button-lock").addClass("button-unlock"),t()}function a(n){var a=n.find(".text").hasClass("hidden")?"hidden":"";n.parents("tr").removeClass("locked"),n.parents("tr").find('th.check-column input[type="hidden"]').attr("type","checkbox"),n.parents("tr").find("th.check-column input").show(),n.parents("tr").find("th.check-column .dashicons.locked").remove(),n.parents("tr").find(".row-actions .activate").show(),n.parents("tr").find(".row-actions .deactivate").show(),n.parents("tr").find(".row-actions .delete").show(),n.html('<span class="dashicons dashicons-lock"></span><span class="text  '+a+'">'+objectL10n.lock+"</span>"),n.removeClass("button-unlock").addClass("button-lock"),t()}t(),$("table.plugins tbody tr .row-actions .lock a").each(function(){$(this).hasClass("button-unlock")?n($(this)):a($(this))})}),jQuery(document).ready(function($){function t(){$(".button-hide").unbind(),$(".button-show").unbind(),$(".button-hide").click(function(t){t.preventDefault();var a=$(this).attr("data-plugin_file"),i={action:"PIGPR_HIDE",mode:"Plugin Manager",plugin_file:a};$obj=$(this),$.post(ajaxurl,i,function(t){n($obj)},"json")}),$(".button-show").click(function(t){t.preventDefault();var n=$(this).attr("data-plugin_file"),i={action:"PIGPR_SHOW",mode:"Plugin Manager",plugin_file:n};$obj=$(this),$.post(ajaxurl,i,function(t){a($obj)},"json")})}function n(n){var a=n.find(".text").hasClass("hidden")?"hidden":"";$("#group-manager-setting-hidden:checked").length?n.parents("tr").addClass("show"):n.parents("tr").removeClass("show"),"hidden"==getUrlParameter("plugin_status")?n.parents("tr").removeClass("hidden"):n.parents("tr").addClass("hidden"),getUrlParameter("plugin_group")&&n.parents("tr").removeClass("hidden"),n.html('<span class="dashicons dashicons-visibility"></span><span class="text '+a+'">'+objectL10n.show+"</span>"),n.removeClass("button-hide").addClass("button-show"),t()}function a(n){var a=n.find(".text").hasClass("hidden")?"hidden":"";$("#group-manager-setting-hidden:checked").length?n.parents("tr").addClass("show"):n.parents("tr").removeClass("show"),"hidden"==getUrlParameter("plugin_status")?n.parents("tr").addClass("hidden"):n.parents("tr").removeClass("hidden"),getUrlParameter("plugin_group")&&n.parents("tr").removeClass("hidden"),n.html('<span class="dashicons dashicons-hidden"></span><span class="text '+a+'">'+objectL10n.hide+"</span>"),n.removeClass("button-show").addClass("button-hide"),t()}t(),$("table.plugins tbody tr .row-actions .hide a").each(function(){$(this).hasClass("button-hide")?a($(this)):n($(this))})});