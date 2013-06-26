/**
 * @file   modules/page/js/page_admin.js
 * @author Arnia (dev@karybu.org)
 * @brief  page Modules for managers javascript
 **/

/*  after Module creation*/
function completeInsertPage(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];

    alert(message);

    var url = '';
    if(location.href.getQuery('module')=='admin') {
        url = current_url.setQuery('module_srl',module_srl).setQuery('act','dispPageAdminInfo');
        if(page) url = url.setQuery('page',page);
    } else {
        url = current_url;
    }

    location.href = url;
}

function completeArticleDocumentInserted(ret_obj){
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var mid = ret_obj['mid'];
	var is_mobile = ret_obj['is_mobile'];

    alert(message);

    var url = '';
	
	if(is_mobile == 'Y')
        url = current_url.setQuery('act', 'dispPageAdminMobileContent').setQuery('mid', mid);
	else
        url = current_url.setQuery('act', 'dispPageIndex').setQuery('mid', mid);


    location.href = url;
}

/* After the Save */
function completeInsertPageContent(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];
    var mid = ret_obj['mid'];

    location.href = current_url.setQuery('mid',mid).setQuery('act','');
}

function completeInsertMobilePageContent(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];
    var mid = ret_obj['mid'];

    location.href = current_url.setQuery('mid',mid).setQuery('act','dispPageAdminMobileContent');
}

/* Save the modified page content */
function doSubmitPageContent(fo_obj) {
    var html = getWidgetContent();
    fo_obj.content.value = html;
    return procFilter(fo_obj, insert_page_content);
}

function doSubmitMPageContent(fo_obj) {
    var html = getWidgetContent();
    fo_obj.content.value = html;
    return procFilter(fo_obj, insert_mpage_content);
}

/* Remove the module and */
function completeDeletePage(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

    var url = current_url.setQuery('act','dispPageAdminContent').setQuery('module_srl','');
    if(page) url = url.setQuery('page',page);

    location.href = url;
}

/* Category Go */
function doChangeCategory(fo_obj) {
    var module_category_srl = fo_obj.module_category_srl.options[fo_obj.module_category_srl.selectedIndex].value;
    if(module_category_srl==-1) {
        location.href = current_url.setQuery('act','dispModuleAdminCategory');
        return false;
    }
    return true;
}

/* Widgets recompile */
function doRemoveWidgetCache(module_srl) {
    var params = new Array();
    params["module_srl"] = module_srl;
    exec_xml('page', 'procPageAdminRemoveWidgetCache', params, completeRemoveWidgetCache);
}

function completeRemoveWidgetCache(ret_obj) {
    var message = ret_obj['message'];
    location.reload(); 
}

/* Batch Setting */
function doCartSetup(url) {
    var module_srl = new Array();
    jQuery('#fo_list input[name=cart]:checked').each(function() {
        module_srl[module_srl.length] = jQuery(this).val();
    });

    if(module_srl.length<1) return;

    url += "&module_srls="+module_srl.join(',');
    popopen(url,'modulesSetup');
}
function doCancelPageEdit(mid){
    if (confirm(xe.lang.confirm_cancel)) {
        location.href = current_url.setQuery('mid',mid).setQuery('act','')
    }
    return false;
}
jQuery(document).ready(function($){

    $("#module_category_srl").on("change", function() {
        if(doChangeCategory($("#fo_list")[0])) {
            $("#fo_list").submit();
        }
    })
    $("a.kDelete").on("click", function() {
        var module_srl = $(this).closest("tr").find(".module_srl").val();
        var mid = $(this).closest("tr").find(".mid").text();

        $("#delete_page_modal input[name='module_srl']").val(module_srl);
        $("#page_to_delete_mid").html(mid);

        $("#delete_page_modal").modal('show');
        return false;
    });
});

jQuery(document).ready(function($){
    $('body').tooltip({
        selector: "a[data-toggle=tooltip],button[data-toggle=tooltip]"
    })
});