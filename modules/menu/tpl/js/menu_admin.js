/* 메뉴 클릭시 적용할 함수 */
function doGetMenuItemInfo(menu_id, obj) {
    // menu, menu_id, node_srl을 추출
    var fo_obj = jQuery("#fo_menu")[0];
    var node_srl = 0;
    var parent_srl = 0;

    if(typeof(obj)!="undefined") {
        if(typeof(obj.getAttribute)!="undefined") { 
          node_srl = obj.getAttribute("node_srl");
        } else {
            node_srl = obj.node_srl; 
            parent_srl = obj.parent_srl; 
        }
    }

    var params = {menu_item_srl:node_srl, parent_srl:parent_srl};

    // 서버에 요청하여 해당 노드의 정보를 수정할 수 있도록 한다. 
    var response_tags = new Array('error','message','tpl');
    exec_xml('menu', 'getMenuAdminTplInfo', params, completeGetMenuItemTplInfo, response_tags, params);
}

function completeGetMenuItemTplInfo(ret_obj, response_tags) {
    var obj = jQuery('#menu_zone_info');
	var sc_top = jQuery(document).scrollTop();

    if(sc_top > 200) {
		obj.css('margin-top', (sc_top-210)+'px');
    } else {
		obj.css('margin-top', 0);
    }

    var tpl = ret_obj['tpl'];
	obj.html(tpl).show();
}

/* 메뉴 목록 갱신 */
function doReloadTreeMenu(menu_srl) {
    var params = new Array();
    params["menu_srl"] = menu_srl;

    // 서버에 요청하여 해당 노드의 정보를 수정할 수 있도록 한다. 
    var response_tags = new Array('error','message', 'xml_file', 'menu_title');
    exec_xml('menu', 'procMenuAdminMakeXmlFile', params, completeRemakeCache, response_tags, params);
}

function completeRemakeCache(ret_obj) {
	if(ret_obj.error == 0)
	{
		document.location.reload();
	}
}

/* 레이아웃의 메뉴에 mid 추가 */
function doInsertMid(mid, menu_id) {
    if(!opener) {
        window.close();
        return;
    }

    var fo_obj = opener.document.getElementById("fo_menu");
    if(!fo_obj) {
        window.close();
        return;
    }

    fo_obj.menu_url.value = mid;
    window.close();
}
