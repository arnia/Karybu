var index =0;
var numfiles =0;
var id = '';
var chrome_index = 0;

function makeFileList(){
    var i=0;
    var input = document.getElementById('filesToUpload');
    var browserName=navigator.appName;
    var list = document.getElementById('fileList');
    if (browserName=="Microsoft Internet Explorer") {

        if(index == 0) var currentinput = input;
        else var currentinput = document.getElementById('filesToUpload'+(index-1));
        var newfileuploadinput = jQuery(currentinput).clone(true);
        jQuery(newfileuploadinput).removeAttr("id");
        jQuery(newfileuploadinput).attr('id', 'filesToUpload'+index);
        jQuery(currentinput).hide(0.1);
        jQuery(newfileuploadinput).insertAfter(currentinput);


        var li = document.createElement('li');
        var checkbox = document.createElement('input');
        checkbox.type = "radio";
        checkbox.name = "primary_image";
        checkbox.value = index;
        checkbox.id = index;

        jQuery(list).append(li);


        var elem = currentinput.value.split("\\");
        var filename = elem[elem.length-1];
        li.innerHTML = outerHTML(checkbox) + 'File ' + index + ':  ' + filename;

        if(index == 1){
            var p = document.createElement('p');
            p.innerHTML = "Select primary product image";

            jQuery(p).insertBefore(list);
        }
        index++;
        document.getElementById('0').checked = true;
    }
    else {
        chrome_index++;
        if(chrome_index == 1) var currentinput = input;
        else var currentinput = document.getElementById('filesToUpload'+(chrome_index-1));
        var newfileuploadinput = jQuery(currentinput).clone(true);
        jQuery(newfileuploadinput).removeAttr("id");
        jQuery(newfileuploadinput).attr('id', 'filesToUpload'+chrome_index);
        jQuery(currentinput).hide();
        jQuery(newfileuploadinput).insertAfter(currentinput);

        numfiles = currentinput.files.length;
        for (var x = 0; x < numfiles; x++) {

            //add to list
            var li = document.createElement('li');

            var checkbox = document.createElement('input');

            checkbox.type = "radio";
            checkbox.name = "primary_image";
            if(index == 0) checkbox.checked = true;
            checkbox.value = index;
            checkbox.id = index;



            jQuery(list).append(li);

            var image = document.createElement('image');
            image.name = "image_"+index;
            image.id = "image_"+index;
            image.src="#";

            li.innerHTML = outerHTML(checkbox) + 'Not yet saved:  ' + currentinput.files[x].name + outerHTML(image);
            index++;
        }
        if(index == numfiles){
            var p = document.createElement('p');
            p.innerHTML = "Select primary product image";

            jQuery(p).insertBefore(list);
        }
        document.getElementById('0').checked = true;
    }

    if (browserName!="Microsoft Internet Explorer") {
        var reader = new FileReader();
        reader.readAsDataURL(currentinput.files[0]);
        reader.onload = function (e) {
        if(i < numfiles){
        id = "image_"+(index-(numfiles-i));
        image = document.getElementById(id);
        image.src =  e.target.result ;
        image.width = 100;
        image.height = 100;
        i++;
        reader.readAsDataURL(currentinput.files[i]);
        }
    }
}

}
function outerHTML(node){
    // if IE, Chrome take the internal method otherwise build one
    return node.outerHTML || (
    function(n){
    var div = document.createElement('div'), h;
    div.appendChild( n.cloneNode(true) );
    h = div.innerHTML;
    div = null;
    return h;
    })(node);
}

function unique(t) {
	var a = [];
	var l = t.length;
	for(var i=0; i<l; i++) {
		for(var j=i+1; j<l; j++) {
			if (t[i] === t[j])
				j = ++i;
		}
		a.push(t[i]);
	}
	return a;
}

function deleteShopPage(module_srl, confirm_msg){
    if(confirm(confirm_msg)){
        var response_tags = new Array('error','message');
        var params = {'module_srl':module_srl}
        exec_xml('shop', 'procShopToolDeletePage', params, completeReload, response_tags);
    }
}

function completeInsertCategory(){
	jQuery('#category_info').html("");
	Tree(xml_url);
}

function completeModifyPassword(ret_obj, response_tags, args, fo_obj) {
	var error = ret_obj['error'];
	var message = ret_obj['message'];
	alert(message);
	location.reload();
}

function completeInsertConfig(ret_obj, response_tags, args, fo_obj) {
	var error = ret_obj['error'];
	var message = ret_obj['message'];
	var mid = ret_obj['mid'];

	location.reload();

}


function addCategory(){
	var category_title= jQuery('[name=add_category]').val();
	var parent_srl = jQuery('#category').val();
	if(!category_title) return;
	var response_tags = new Array('error','message','xml_file','category_srl');
	exec_xml('document','procDocumentInsertCategory',{'mid':current_mid,'title':category_title,'parent_srl':parent_srl},completeAddCategory,response_tags);
}

function completeAddCategory(ret_obj, response_tags, args, fo_obj) {
	var error = ret_obj['error'];
	var message = ret_obj['message'];
	var xml_file = ret_obj['xml_file'];
	var category_srl = ret_obj['category_srl'];

	var sel = jQuery('#category').get(0);	
	var n = sel.options[0].text[sel.options[0].text.length-1];
	n+=n;
	for(i=0,c=sel.length;i<c;i++) sel.options[1] = null;

	jQuery.get(xml_file,function(data){
		var c = '';
			jQuery(data).find("node").each(function(j){
				var node_srl = jQuery(this).attr("node_srl");
				var document_count = jQuery(this).attr("document_count");
				var text = jQuery(this).attr("text") +'('+document_count+')';

				for(i=0,c=jQuery(this).parents('node').size();i<c;i++) text = n +text;
				sel.options[sel.options.length] = new Option(text,node_srl, false,false);
				if(node_srl == category_srl) sel.selectedIndex = j;
			});
	});
	jQuery('[name=add_category]').val('');
	jQuery('#add_category').removeClass('open');
}

function completeReload(ret_obj) {
	var error = ret_obj['error'];
	var message = ret_obj['message'];
	location.href = location.href;
}

function toggleAccessType(target) {
	switch(target) {
		case 'domain' :
				xGetElementById('shopFo').domain.value = '';
				xGetElementById('accessDomain').style.display = 'block';
				xGetElementById('accessVid').style.display = 'none';
			break;
		case 'vid' :
				xGetElementById('shopFo').vid.value = '';
				xGetElementById('accessDomain').style.display = 'none';
				xGetElementById('accessVid').style.display = 'block';
			break;
	}
}

function completeInsertProfile(ret_obj) {
	var fo = jQuery('#foProfile');
	var photo = jQuery('#photo');
	var src = photo.get(0).value;
	if(!photo.get(0).value || !/\.(jpg|jpeg|gif|png)$/i.test(src)) {
		location.reload();
		return;
	}
	fo.append('<input type="hidden" name="act" value="procShopProfileImageUpload" />');
	fo.get(0).submit();
}

function getEditorSkinColorList(skin_name,selected_colorset,type){
	if(skin_name.length>0){
		type = type || 'comment';
		var response_tags = new Array('error','message','colorset');
		exec_xml('editor','dispEditorSkinColorset',{skin:skin_name},resultGetEditorSkinColorList,response_tags,{'selected_colorset':selected_colorset,'type':type});
	}
}

function resultGetEditorSkinColorList(ret_obj,response_tags, params) {

	var selectbox = null;
	if(params.type == 'comment'){
		selectbox = xGetElementById("sel_editor_comment_colorset");
	}else{
		selectbox = xGetElementById("sel_editor_guestbook_colorset");
	}

	if(ret_obj['error'] == 0 && ret_obj.colorset){
		var it = new Array();
		var items = ret_obj['colorset']['item'];
		if(typeof(items[0]) == 'undefined'){
			it[0] = items;
		}else{
			it = items;
		}
		var sel = 0;
		for(var i=0,c=it.length;i<c;i++){
			selectbox.options[i]=new Option(it[i].title,it[i].name);
			if(params.selected_colorset && params.selected_colorset == it[i].name) sel = i;
		}
		selectbox.options[sel].selected = true;
		selectbox.style.display="";
	}else{
		selectbox.style.display="none";
		selectbox.innerHTML="";
	}
}

function moveDate() {
	location.href = current_url.setQuery('selected_date',jQuery('#str_selected_date').text().replace(/\./g,''));
}

function doSelectSkin(skin) {
	var params = new Array();
	var response_tags = new Array('error','message');
	params['skin'] = skin;
	params['mid'] = current_mid;
	exec_xml('shop', 'procShopToolLayoutConfigSkin', params, completeReload, response_tags);
}

function doResetLayoutConfig() {
	var params = new Array();
	var response_tags = new Array('error','message');
	params['mid'] = current_mid;
	params['vid'] = xeVid;
	exec_xml('shop', 'procShopToolLayoutResetConfigSkin', params, completeReload, response_tags);
}

function completeUpdateAllow(ret_obj) {
	jQuery('.layerCommunicationConfig').removeClass('open');
	location.href=location.href;
}

function openLayerCommuicationConfig(){
	jQuery('input[name=document_srl]','.layerCommunicationConfig').val('');
	var v,srls = [];
	jQuery("input[name=document_srl]:checked").each(function(){
		v = jQuery(this).val();
		if(v) srls.push(v);
	});
	if(srls.length<1) return;
	jQuery('input[name=document_srl]','.layerCommunicationConfig').val(srls.join(','));
	jQuery('.layerCommunicationConfig').addClass('open');
}


function hideLayerCommuicationConfig(){
	jQuery('.layerCommunicationConfig').removeClass('open');
	jQuery('input[name=document_srl]','.layerCommunicationConfig').val('');
}



function toggleLnb() {
	if(xGetCookie('tclnb')) {
		xDeleteCookie('tclnb','/');
		jQuery(document.body).addClass('lnbToggleOpen');
		jQuery(document.body).removeClass('lnbClose');
	} else {
		var d = new Date();
		d.setDate(31);
		d.setMonth(12);
		d.setFullYear(2999);
		xSetCookie('tclnb',1,d,'/');
		jQuery(document.body).removeClass('lnbToggleOpen');
		jQuery(document.body).addClass('lnbClose');
	}
}

jQuery(function(){
	
	var saved_st_menu = xGetCookie('tclnb_menu');
	if(saved_st_menu) saved_st_menu = saved_st_menu.split(',');
	else saved_st_menu = [];

	jQuery("div#tool_navigation > ul > li:has(ul) > a").click(function(evt){
		jQuery(this).parent('li').toggleClass('open');
		jQuery(document.body).addClass('lnbToggleOpen');
		jQuery(document.body).removeClass('lnbClose');

		st_menu = [];
		jQuery("div#tool_navigation > ul > li:has(ul) > a").each(function(i){
			if(jQuery(this).parent('li').hasClass('open')) st_menu.push(i);
		});

		var d = new Date();
		d.setDate(31);
		d.setMonth(12);
		d.setFullYear(2999);
		st_menu = jQuery.unique(st_menu);
		xSetCookie('tclnb_menu',st_menu.join(','),d,'/');

		return false;
	}).each(function(i){
		if(jQuery.inArray(i+'',saved_st_menu)>-1) jQuery(this).parent('li').addClass('open');
	});

jQuery("div#tool_navigation > ul > li").hover(
	function(e){
		jQuery(this).addClass('hover');
	},function(e){
		jQuery(this).removeClass('hover');
	});

	jQuery("div.dashboardNotice>button").click(function(){
		jQuery("div.dashboardNotice").toggleClass('open','');
	});
});

addNode = function(node,e) {
    var params ={ "category_srl":0,"parent_srl":node,"module_srl":jQuery("#fo_category [name=module_srl]").val() };
    jQuery.exec_json('document.getDocumentCategoryTplInfo', params, function(data){
        jQuery('#category_info').html(data.tpl).css('left',e.pageX).css('top',e.pageY);
        if(node) jQuery('#category_info').find('tr').get(4).style.display = 'none';
        else jQuery('#category_info').find('tr').get(3).style.display = 'none';
    });


}
modifyNode = function(node,e) {
    var params ={ "category_srl":node ,"parent_srl":0 ,"module_srl":jQuery("#fo_category [name=module_srl]").val() };
    jQuery.exec_json('document.getDocumentCategoryTplInfo', params, function(data){
        jQuery('#category_info').html(data.tpl).css('left',e.pageX).css('top',e.pageY);
        jQuery('#category_info').find('tr').get(3).style.display = 'none';
    });
}



function doSetupComponent(component_name) {
    popopen(request_uri.setQuery('module','editor').setQuery('act','dispEditorAdminSetupComponent').setQuery('component_name',component_name), 'SetupComponent');
}

function doEnableComponent(component_name) {
    var params = new Array();
    params['component_name'] = component_name;

    exec_xml('editor', 'procEditorAdminEnableComponent', params, completeUpdate);
}
function doDisableComponent(component_name) {
    var params = new Array();
    params['component_name'] = component_name;

    exec_xml('editor', 'procEditorAdminDisableComponent', params, completeUpdate);
}

function doMoveListOrder(component_name, mode) {
    var params = new Array();
    params['component_name'] = component_name;
    params['mode'] = mode;

    exec_xml('editor', 'procEditorAdminMoveListOrder', params, completeUpdate);
}


function completeUpdate(ret_obj) {
    location.reload();
}

function initShop() {
	var params = new Array();
	params['mid'] = current_mid;
	params['vid'] = xeVid;

    exec_xml('shop','procShopToolInit', params,
		function(ret_obj){
			alert(ret_obj['message']);
			location.href = current_url.setQuery('act','dispShopToolDashboard');
		}, new Array('error','message'));
}
function checkUserImage(f,msg){
    var filename = jQuery('[name=user_image]',f).val();
    if(/\.(gif|jpg|jpeg|gif|png|swf|flv)$/i.test(filename)){
        return true;
    }else{
        alert(msg);
        return false;
    }
}
function deleteUserImage(filename){
    var params ={
            "mid":current_mid
			,"vid":xeVid
            ,"filename":filename
            };
    jQuery.exec_json('shop.procShopToolUserImageDelete', params, function(data){
        document.location.reload();
    });
}

function deleteProductItem(srl,product_type){
    if (!confirm(xe.lang.msg_confirm_delete_product)) return false;
    var params = new Array();
    params['product_srl'] = srl;
    params['product_type'] = product_type;
    var response_tags = new Array('error','message','page','mid');
    exec_xml('shop', 'procShopToolDeleteProduct', params, completeReload, response_tags);
}

function deleteProductItems(page){
    if(!confirm(xe.lang.msg_confirm_delete_products)) return false;
    var val, srls = [];
    jQuery("input[name=product_srl]:checked").each(function(){
        val = jQuery(this).val();
        if(val) srls.push(val);
    });
    if(srls.length<1) return;
    var params = new Array();
    params['product_srls'] = srls.join(',');
    params['page'] = page;
    var response_tags = new Array('error','message','page','mid');
    exec_xml('shop', 'procShopToolDeleteProducts', params, completeReload, response_tags);
}

function deleteCustomerItem(srl){
    if (!confirm(xe.lang.msg_confirm_delete_customer)) return false;
    var response_tags = new Array('error','message','page','mid');
    exec_xml('shop', 'procShopToolDeleteCustomers', { target_member_srls: srl }, completeReload, response_tags);
}

function deleteSubscribedCustomerItem(srl){
    if (!confirm(xe.lang.msg_confirm_unsubscribe_customer)) return false;
    var response_tags = new Array('error','message','page','mid');
    exec_xml('shop', 'procShopToolUnsubscribeCustomers', { target_member_srls: srl }, completeReload, response_tags);
}

function deleteCustomerItems(page){
    if (!confirm(xe.lang.msg_confirm_delete_customers)) return false;
    var val, srls = [];
    jQuery("input[name=member_srl]:checked").each(function(){
        val = jQuery(this).val();
        if(val) srls.push(val);
    });
    if (srls.length < 1) return;
    var response_tags = new Array('error','message','page','mid');
    var params = {
        target_member_srls: srls.join(','),
        page: page
    }
    exec_xml('shop', 'procShopToolDeleteCustomers', params, completeReload, response_tags);
}

function deleteSubscribedCustomerItems(page){
    if (!confirm(xe.lang.msg_confirm_unsubscribe_customers)) return false;
    var val, srls = [];
    jQuery("input[name=member_srl]:checked").each(function(){
        val = jQuery(this).val();
        if(val) srls.push(val);
    });
    if (srls.length < 1) return;
    var response_tags = new Array('error','message','page','mid');
    var params = {
        target_member_srls: srls.join(','),
        page: page
    }
    exec_xml('shop', 'procShopToolUnsubscribeCustomers', params, completeReload, response_tags);
}

function deleteAddressItem(srl){
    if (!confirm(xe.lang.msg_confirm_delete_address)) return false;
    var response_tags = new Array('error','message','page','mid');
    exec_xml('shop', 'procShopToolDeleteAddresses', { address_srls: srl }, completeReload, response_tags);
}

function deleteAddressItems(page){
    if (!confirm(xe.lang.msg_confirm_delete_addresses)) return false;
    var val, srls = [];
    jQuery("input[name=address_srl]:checked").each(function(){
        val = jQuery(this).val();
        if(val) srls.push(val);
    });
    if (srls.length < 1) return;
    var response_tags = new Array('error','message','page','mid');
    var params = {
        address_srls: srls.join(','),
        page: page
    }
    exec_xml('shop', 'procShopToolDeleteAddresses', params, completeReload, response_tags);
}

function deleteNewsletterItem(srl){
    if (!confirm(xe.lang.msg_confirm_delete_newsletter)) return false;
    var response_tags = new Array('error','message','page','mid');
    exec_xml('shop', 'procShopToolDeleteNewsletters', { newsletter_srls: srl }, completeReload, response_tags);
}

function deleteNewslettersItems(page){
    if (!confirm(xe.lang.msg_confirm_delete_newsletters)) return false;
    var val, srls = [];
    jQuery("input[name=newsletter_srl]:checked").each(function(){
        val = jQuery(this).val();
        if(val) srls.push(val);
    });
    if (srls.length < 1) return;
    var response_tags = new Array('error','message','page','mid');
    var params = {
        newsletter_srls: srls.join(','),
        page: page
    }
    exec_xml('shop', 'procShopToolDeleteNewsletters', params, completeReload, response_tags);
}

function deleteAttributeItem(srl){
    if (!confirm(xe.lang.msg_confirm_delete_attribute)) return false;
    var response_tags = new Array('error','message','page','mid');
    exec_xml('shop', 'procShopToolDeleteAttributes', { attribute_srls: srl }, completeReload, response_tags);
}

function deleteAttributeItems(page){
    if (!confirm(xe.lang.msg_confirm_delete_attributes)) return false;
    var val, srls = [];
    jQuery("input[name=attribute_srl]:checked").each(function(){
        val = jQuery(this).val();
        if(val) srls.push(val);
    });
    if (srls.length < 1) return;
    var response_tags = new Array('error','message','page','mid');
    var params = {
        attribute_srls: srls.join(','),
        page: page
    }
    exec_xml('shop', 'procShopToolDeleteAttributes', params, completeReload, response_tags);
}

function deleteItem(srl, action){
    if (!confirm(xe.lang.msg_confirm_delete_coupon)) return false;
    var response_tags = new Array('error','message','page','mid');
    exec_xml('shop', action, { srls: srl }, completeReload, response_tags);
}
function deleteCouponItems(page){
    if (!confirm(xe.lang.msg_confirm_delete_coupons)) return false;
    var val, srls = [];
    jQuery("input[name=srl]:checked").each(function(){
        val = jQuery(this).val();
        if(val) srls.push(val);
    });
    if (srls.length < 1) return;
    var response_tags = new Array('error','message','page','mid');
    var params = {
        srls: srls.join(','),
        page: page
    }
    exec_xml('shop', 'procShopToolDeleteCoupons', params, completeReload, response_tags);
}


(function($){

var inputPublish, submitButtons;
var validator = xe.getApp('Validator')[0];

validator.cast('ADD_CALLBACK', ['save_post', function callback(form) {
	var params={}, responses=[], elms=form.elements, data=jQuery(form).serializeArray();
	$.each(data, function(i, field) {
		var val = $.trim(field.value);
		if(!val) return true;
		if(/\[\]$/.test(field.name)) field.name = field.name.replace(/\[\]$/, '');
		if(params[field.name]) params[field.name] += '|@|'+val;
		else params[field.name] = field.value;
	});
	responses = ['error','message','mid','document_srl','category_srl', 'redirect_url'];
	exec_xml('shop','procShopPostsave', params, completePostsave, responses, params, form);

	inputPublish.val('N');
}]);

$(function(){
	inputPublish  = $('input[name=publish]');
	inputPreview  = $('input[name=preview]');
	submitButtons = $('#wPublishButtonContainer button');

	submitButtons.click(function(){
		inputPublish.val( $(this).parent().hasClass('_publish')?'Y':'N' );
		inputPreview.val( $(this).parent().hasClass('_preview')?'Y':'N' );
		$('input:text,textarea', this.form).each(function(){
			var t = $(this);
			var v = $.trim(t.val());
			if (v && v == t.attr('title')) t.val('');
		});

		if(editorRelKeys[1]) editorRelKeys[1].content.value = editorRelKeys[1].func();
	});
});

})(jQuery);



function changeMenuType(obj) {
    var sel = obj.options[obj.selectedIndex].value;
    if(sel == 'url') {
        jQuery('#urlForm').css("display","block");
    } else {
        jQuery('#urlForm').css("display","none");
    }
}


function isLive(){
	exec_xml('shop', 'procShopToolLive', []);
}

jQuery(function($){
	// Label Text Clear
	var iText = $('.fItem>.iLabel').next('.iText');
	$('.fItem>.iLabel').css('position','absolute');
	iText
		.focus(function(){
			$(this).prev('.iLabel').css('visibility','hidden');
		})
		.blur(function(){
			if($(this).val() == ''){
				$(this).prev('.iLabel').css('visibility','visible');
			} else {
				$(this).prev('.iLabel').css('visibility','hidden');
			}
		})
		.change(function(){
			if($(this).val() == ''){
				$(this).prev('.iLabel').css('visibility','visible');
			} else {
				$(this).prev('.iLabel').css('visibility','hidden');
			}
		})
		.blur();
});

jQuery(function($){
    // Label Text Clear
    var iTextarea = $('.fItem>.iLabel').next('.iTextarea');
    $('.fItem>.iLabel').css('position','absolute');
    iTextarea
        .focus(function(){
        $(this).prev('.iLabel').css('visibility','hidden');
    })
        .blur(function(){
            if($(this).val() == ''){
                $(this).prev('.iLabel').css('visibility','visible');
            } else {
                $(this).prev('.iLabel').css('visibility','hidden');
            }
        })
        .change(function(){
            if($(this).val() == ''){
                $(this).prev('.iLabel').css('visibility','visible');
            } else {
                $(this).prev('.iLabel').css('visibility','hidden');
            }
        })
        .blur();
});

// Function for checking / unchecking categories in a hierarchy
// Taking into account parents and children
// (when checking a child, parents should also be checked
function checkOrUncheckParents(clicked_category_checkbox, root_ul_id)
{
    // Select/deselect parent categories
    var parent = clicked_category_checkbox.parent();
    var this_is_checked = clicked_category_checkbox.is(':checked');
    while(parent.attr("id") != root_ul_id) // Iterate to all elements above current one until root ul
    {
        if(parent.is("ul"))
        {
            parent_checkbox = parent.parent().children("span").children("input[type='checkbox']");

            // If we are about to change the parent value,
            // we need to make sure it doesn't need to stay checked for other children
            if(this_is_checked == false)
            {
                var atLeastOneChildIsChecked = false;
                if(parent.find("input:checked").length > 0)
                {
                    var atLeastOneChildIsChecked = true;
                }

                if(atLeastOneChildIsChecked)
                {
                    parent = parent.parent();
                    continue;
                }
            }
            parent_checkbox.attr("checked", this_is_checked);
        }
        parent = parent.parent();
    }
}

// For upgrading the module directly from shop admin
function doUpdateModule(module) {
    var params = new Array();
    params['module_name'] = module;
    exec_xml('install','procInstallAdminUpdate',params, completeInstallModule);
}

function completeInstallModule(ret_obj) {
    alert(ret_obj['message']);
    location.reload();
}