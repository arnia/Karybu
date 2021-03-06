/* Add a User */
function completeInsert(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var member_srl = ret_obj['member_srl'];
    var page = ret_obj['page'];

    alert(message);

    var url = current_url.setQuery('act','dispMemberAdminInfo').setQuery('member_srl',member_srl);
    if(page) url = url.setQuery('page', page);

    location.href = url;
}

/* Deleting a user */
function completeDelete(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];

    alert(message);

    var url = current_url.setQuery('act','dispMemberAdminList');

    location.href = url;
}

/* Add a Group */
function completeInsertGroup(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];

    alert(message);

    var url = current_url.setQuery('act','dispMemberAdminGroupList');

    location.href = url;
}

/* Group-related tasks */
function doUpdateGroup(group_srl, mode, message) {
    if(typeof(message)!='undefined'&&!confirm(message)) return;

    var fo_obj = get_by_id('fo_group_info');
    fo_obj.group_srl.value = group_srl;
	fo_obj.submit();
}

function completeUpdateGroup(ret_obj) {
    var page = ret_obj['page'];
    var url = current_url.setQuery('act','dispMemberAdminGroupList');
    location.href = current_url.setQuery('group_srl','');
}

/* Sign the form of work-related */
function doUpdateJoinForm(member_join_form_srl, mode, message) {
    if(typeof(message)!='undefined'&&!confirm(message)) return;

    var fo_obj = get_by_id('fo_join_form_info');
    fo_obj.member_join_form_srl.value = member_join_form_srl;
    fo_obj.mode.value = mode;

    procFilter(fo_obj, update_member_join_form);
}

/* The default value for subscription management form */
function doShowJoinFormValue(sel_obj) {
    var val = sel_obj.options[sel_obj.selectedIndex].value;
    switch(val) {
        case 'radio' :
        case 'checkbox' :
        case 'select' :
                get_by_id('zone_default_value').style.display = 'block';
            break;
        default :
                get_by_id('zone_default_value').style.display = 'none';
            break;
    }
}

function doEditDefaultValue(obj, cmd) {
    var listup_obj = get_by_id('default_value_listup');
    var item_obj = get_by_id('default_value_item');
    var idx = listup_obj.selectedIndex;
    var lng = listup_obj.options.length;
    var val = item_obj.value;
    switch(cmd) {
        case 'insert' :
                if(!val) return;
                var opt = new Option(val, val, false, true);
                listup_obj.options[listup_obj.length] = opt;
                item_obj.value = '';
                item_obj.focus();
            break;
        case 'up' :
                if(lng < 2 || idx<1) return;

                var value1 = listup_obj.options[idx].value;
                var value2 = listup_obj.options[idx-1].value;
                listup_obj.options[idx] = new Option(value2,value2,false,false);
                listup_obj.options[idx-1] = new Option(value1,value1,false,true);
            break;
        case 'down' :
                if(lng < 2 || idx == lng-1) return;

                var value1 = listup_obj.options[idx].value;
                var value2 = listup_obj.options[idx+1].value;
                listup_obj.options[idx] = new Option(value2,value2,false,false);
                listup_obj.options[idx+1] = new Option(value1,value1,false,true);
            break;
        case 'delete' :
                listup_obj.remove(idx);
                if(idx==0) listup_obj.selectedIndex = 0;
                else listup_obj.selectedIndex = idx-1;
            break;
    }

    var value_list = new Array();
    for(var i=0;i<listup_obj.options.length;i++) {
        value_list[value_list.length] = listup_obj.options[i].value;
    }

    get_by_id('fo_join_form').default_value.value = value_list.join('|@|');
}

/* Korea Zip Code related */
function doHideKrZipList(column_name) {
    var zone_list_obj = get_by_id('zone_address_list_'+column_name);
    var zone_search_obj = get_by_id('zone_address_search_'+column_name);
    var zone_addr1_obj = get_by_id('zone_address_1_'+column_name);
    var addr1_obj = get_by_id('fo_insert_member')[column_name][0];
    var field_obj = get_by_id('fo_insert_member')['_tmp_address_search_'+column_name];

    zone_addr1_obj.style.display = 'none';
    zone_list_obj.style.display = 'none';
    zone_search_obj.style.display = 'inline';
    addr1_obj.value = '';
    field_obj.focus();
}

function doSelectKrZip(column_name) {
    var zone_list_obj = get_by_id('zone_address_list_'+column_name);
    var zone_search_obj = get_by_id('zone_address_search_'+column_name);
    var zone_addr1_obj = get_by_id('zone_address_1_'+column_name);
    var sel_obj = get_by_id('fo_insert_member')['_tmp_address_list_'+column_name];
    var value = sel_obj.options[sel_obj.selectedIndex].value;
    var addr1_obj = get_by_id('fo_insert_member')[column_name][0];
    var addr2_obj = get_by_id('fo_insert_member')[column_name][1];
    addr1_obj.value = value;
    zone_search_obj.style.display = 'none';
    zone_list_obj.style.display = 'none';
    zone_addr1_obj.style.display = 'inline';
    addr2_obj.focus();
}

function doSearchKrZip(column_name) {
    var field_obj = get_by_id('fo_insert_member')['_tmp_address_search_'+column_name];
    var addr = field_obj.value;
    if(!addr) return;

    var params = new Array();
    params['addr'] = addr;
    params['column_name'] = column_name;

    var response_tags = new Array('error','message','address_list');
    exec_xml('krzip', 'getZipCodeList', params, completeSearchKrZip, response_tags, params);
}

function completeSearchKrZip(ret_obj, response_tags, callback_args) {
    if(!ret_obj['address_list']) {
            alert(alert_msg['address']);
            return;
    }
    var address_list = ret_obj['address_list'].split("\n");
    var column_name = callback_args['column_name'];

    var zone_list_obj = get_by_id('zone_address_list_'+column_name);
    var zone_search_obj = get_by_id('zone_address_search_'+column_name);
    var zone_addr1_obj = get_by_id('zone_address_1_'+column_name);
    var sel_obj = get_by_id('fo_insert_member')['_tmp_address_list_'+column_name];

    for(var i=0;i<address_list.length;i++) {
            var opt = new Option(address_list[i],address_list[i],false,false);
            sel_obj.options[i] = opt;
    }

    for(var i=address_list.length-1;i<sel_obj.options.length;i++) {
            sel_obj.remove(i);
    }

    sel_obj.selectedIndex = 0;

    zone_search_obj.style.display = 'none';
    zone_addr1_obj.style.display = 'none';
    zone_list_obj.style.display = 'inline';
}


/* Profile image, image name, mark Delete */
function doDeleteProfileImage(member_srl) {
	if (!member_srl) return;

	if (!confirm(xe.lang.deleteProfileImage)) return false;

	exec_xml(
		'member',
		'procMemberDeleteProfileImage',
		{member_srl:member_srl},
		function(){jQuery('#profile_imagetag').remove()},
		['error','message','tpl']
	);
}

function doDeleteImageName(member_srl) {
	if (!member_srl) return;

	if (!confirm(xe.lang.deleteImageName)) return false;
	exec_xml(
		'member',
		'procMemberDeleteImageName',
		{member_srl:member_srl},
		function(){jQuery('#image_nametag').remove()},
		['error','message','tpl']
	);
}

function doDeleteImageMark(member_srl) {
	if (!member_srl) return;

	if (!confirm(xe.lang.deleteImageMark)) return false;
	exec_xml(
		'member',
		'procMemberDeleteImageMark',
		{member_srl:member_srl},
		function(){jQuery('#image_marktag').remove()},
		['error','message','tpl']
	);
}

/* Group and bulk changes */
function completeUpdateMemberGroup(ret_obj) {
    alert(ret_obj['message']);
    opener.location.href = opener.current_url;
    window.close();
}

/* The bulk delete */
function completeDeleteMembers(ret_obj) {
    alert(ret_obj['message']);
    opener.location.href = opener.current_url;
    window.close();
}

jQuery(function($) {
	$("#fo_group_order > table")
		.find("a._up")
			.click(function(e){
				var $tr = $(this).parent().parent();
				var $prev = $tr.prev("tr");
				if($prev.length) 
				{
					$prev.before($tr);
					$tr.parent().find("tr").removeClass("bg1").filter(":odd").addClass("bg1");
				}
				e.preventDefault();
			})
		.end()
		.find("a._down")
			.click(function(){
				var $tr = $(this).parent().parent();
				var $next = $tr.next("tr");
				if($next.length)
				{
					$next.after($tr);
					$tr.parent().find("tr").removeClass("bg1").filter(":odd").addClass("bg1");
				}
				e.preventDefault();
			})
		.end()
			
});
