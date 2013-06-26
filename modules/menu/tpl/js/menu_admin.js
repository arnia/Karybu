/* Click to apply when the menu function */
function doGetMenuItemInfo(menu_id, obj) {
    // menu, menu_id, node_srl Extract
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

    // Request to the server, the node should be able to edit the information.
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

/* Updating the list of menu*/
function doReloadTreeMenu(menu_srl) {
    var params = new Array();
    params["menu_srl"] = menu_srl;

    // Request to the server, the node should be able to edit the information.
    var response_tags = new Array('error','message', 'xml_file', 'menu_title');
    exec_xml('menu', 'procMenuAdminMakeXmlFile', params, completeRemakeCache, response_tags, params);
}

function completeRemakeCache(ret_obj) {
	if(ret_obj.error == 0)
	{
		document.location.reload();
	}
}

/* Added to the menu in mid layout */
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
