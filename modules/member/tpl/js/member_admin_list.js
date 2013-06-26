jQuery(function ($){
	
    $("#listManager").on("show", function() {
		var $memberList = $('._memberList input[name=user]:checked');
		if ($memberList.length == 0){
			alert(xe.lang.msg_select_user);
			return false;
		}
		var memberInfo, memberSrl;
		var memberTag = "";
		$('input[name="groups[]"]:checked').removeAttr('checked');
		$('#message').val('');
		for (var i = 0; i<$memberList.length; i++){
			memberInfo = $memberList.eq(i).val().split('\t');
			memberSrl = memberInfo.shift();
			memberTag += '<tr><td>'+memberInfo.join("</td><td>")+'<input type="hidden" name="member_srls[]" value="'+memberSrl+'"/></td></tr>' 
		}
		$('#popupBody').empty().html(memberTag);
	});
});



jQuery(document).ready( function() {
    $ = jQuery;
    /* OS check */
    var UA = navigator.userAgent.toLowerCase();
    $.os = {
        Linux: /linux/.test(UA),
        Unix: /x11/.test(UA),
        Mac: /mac/.test(UA),
        Windows: /win/.test(UA)
    };
    $.os.name = ($.os.Windows) ? 'Windows' :
        ($.os.Linux) ? 'Linux' :
            ($.os.Unix) ? 'Unix' :
                ($.os.Mac) ? 'Mac' : '';

    /**
     * @brief Karybu Public utility function
     * @namespace Karybu
     */
    window.XE = {
        loaded_popup_menus : new Array(),
        addedDocument : new Array(),
        /**
         * @brief Check box with a name change of the checked attribute
         * @param [itemName='cart',][options={}]
         */
        checkboxToggleAll : function(itemName) {
            if(!is_def(itemName)) itemName='cart';
            var options = {
                wrap : null,
                checked : 'toggle',
                doClick : false
            };

            switch(arguments.length) {
                case 1:
                    if(typeof(arguments[0]) == "string") {
                        itemName = arguments[0];
                    } else {
                        $.extend(options, arguments[0] || {});
                        itemName = 'cart';
                    }
                    break;
                case 2:
                    itemName = arguments[0];
                    $.extend(options, arguments[1] || {});
            }

            if(options.doClick == true) options.checked = null;
            if(typeof(options.wrap) == "string") options.wrap ='#'+options.wrap;

            if(options.wrap) {
                var obj = $(options.wrap).find('input[name='+itemName+']:checkbox');
            } else {
                var obj = $('input[name='+itemName+']:checkbox');
            }

            if(options.checked == 'toggle') {
                obj.each(function() {
                    $(this).attr('checked', ($(this).attr('checked')) ? false : true);
                });
            } else {
                (options.doClick == true) ? obj.click() : obj.attr('checked', options.checked);
            }
        },

        /**
         * @brief Documentation in / outputs, including the pop-up menu
         */
        displayPopupMenu : function(ret_obj, response_tags, params) {
            var target_srl = params["target_srl"];
            var menu_id = params["menu_id"];
            var menus = ret_obj['menus'];
            var html = "";

            if(this.loaded_popup_menus[menu_id]) {
                html = this.loaded_popup_menus[menu_id];

            } else {
                if(menus) {
                    var item = menus['item'];
                    if(typeof(item.length)=='undefined' || item.length<1) item = new Array(item);
                    if(item.length) {
                        for(var i=0;i<item.length;i++) {
                            var url = item[i].url;
                            var str = item[i].str;
                            var icon = item[i].icon;
                            var target = item[i].target;

                            var styleText = "";
                            var click_str = "";
                            /* if(icon) styleText = " style=\"background-image:url('"+icon+"')\" "; */
                            switch(target) {
                                case "popup" :
                                    click_str = " onclick=\"popopen(this.href,'"+target+"'); return false;\"";
                                    break;
                                case "javascript" :
                                    click_str = " onclick=\""+url+"; return false; \"";
                                    url="#";
                                    break;
                                default :
                                    click_str = " onclick=\"window.open(this.href); return false;\"";
                                    break;
                            }

                            html += '<li '+styleText+'><a href="'+url+'"'+click_str+'>'+str+'</a></li> ';
                        }
                    }
                }
                this.loaded_popup_menus[menu_id] =  html;
            }

            /* Output layer */
            if(html) {
                var area = $('#popup_menu_area').html('<ul>'+html+'</ul>');
                var areaOffset = {top:params['page_y'], left:params['page_x']};

                if(area.outerHeight()+areaOffset.top > $(window).height()+$(window).scrollTop())
                    areaOffset.top = $(window).height() - area.outerHeight() + $(window).scrollTop();
                if(area.outerWidth()+areaOffset.left > $(window).width()+$(window).scrollLeft())
                    areaOffset.left = $(window).width() - area.outerWidth() + $(window).scrollLeft();

                area.css({ top:areaOffset.top, left:areaOffset.left }).show();
            }
        }
    }

})

