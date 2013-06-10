/**
 * @file common.js
 * @author Arnia (dev@karybu.org)
 * @brief some useful and frequently used JavaScript functions collection
 **/

/* jQuery Remove the reference variable ($) */
if(jQuery) jQuery.noConflict();

(function($) {
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
     * @brief XE Public utility function
     * @namespace XE
     */
    window.XE = {
        loaded_popup_menus : new Array(),
        addedDocument : new Array(),
        /**
         * @brief Change of the check box is checked attribute with a specific name
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
         * @brief Output documents / members, including the pop-up menu
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

            /* The output layer */
            if(html) {
                var area = $('#popup_menu_area');
                area.find('.modal-body:first').html('<ul>'+html+'</ul>');
                var areaOffset = {top:params['page_y'], left:params['page_x']};

                if(area.outerHeight()+areaOffset.top > $(window).height()+$(window).scrollTop())
                    areaOffset.top = $(window).height() - area.outerHeight() + $(window).scrollTop();
                if(area.outerWidth()+areaOffset.left > $(window).width()+$(window).scrollLeft())
                    areaOffset.left = $(window).width() - area.outerWidth() + $(window).scrollLeft();

                //area.css({ top:areaOffset.top, left:areaOffset.left }).show();
                area.addClass('modal');
                area.modal('show');
            }
        }
    }

}) (jQuery);



/* jQuery(document).ready() */
jQuery(function($) {

    /* select - option disabled=disabled Check in the IE properties for the function */
    if($.browser.msie) {
        $('select').each(function(i, sels) {
            var disabled_exists = false;
            var first_enable = new Array();

            for(var j=0; j < sels.options.length; j++) {
                if(sels.options[j].disabled) {
                    sels.options[j].style.color = '#CCCCCC';
                    disabled_exists = true;
                }else{
                    first_enable[i] = (first_enable[i] > -1) ? first_enable[i] : j;
                }
            }

            if(!disabled_exists) return;

            sels.oldonchange = sels.onchange;
            sels.onchange = function() {
                if(this.options[this.selectedIndex].disabled) {

                    this.selectedIndex = first_enable[i];
                    /*
                    if(this.options.length<=1) this.selectedIndex = -1;
                    else if(this.selectedIndex < this.options.length - 1) this.selectedIndex++;
                    else this.selectedIndex--;
                    */

                } else {
                    if(this.oldonchange) this.oldonchange();
                }
            };

            if(sels.selectedIndex >= 0 && sels.options[ sels.selectedIndex ].disabled) sels.onchange();

        });
    }

    /* Expand / Collapse paragraph Editor fold components */
    var drEditorFold = $('.xe_content .fold_button');
    if(drEditorFold.size()) {
        var fold_container = $('div.fold_container', drEditorFold);
        $('button.more', drEditorFold).click(function() {
            $(this).hide().next('button').show().parent().next(fold_container).show();
        });
        $('button.less', drEditorFold).click(function() {
            $(this).hide().prev('button').show().parent().next(fold_container).hide();
        });
    }
	
	jQuery('input[type="submit"],button[type="submit"]').click(function(ev){
		var $el = jQuery(ev.currentTarget);
		
		setTimeout(function(){
			return function(){
				$el.attr('disabled', 'disabled');
			};
		}(), 0);
		
		setTimeout(function(){
			return function(){
				$el.removeAttr('disabled');
			};
		}(), 3000);
	});


    jQuery("#current_page").focus(function () {
        $(this).next('.btn-goto').removeClass('btn-h').addClass('btn-v');
    });

    jQuery("#current_page").click(function () {
        $(this).select();
    });

    jQuery("#current_page").blur(function () {
        var that = this;
        setTimeout(function(){
            $(that).next('.btn-goto').removeClass('btn-v').addClass('btn-h');
        }, '200');
    });


    jQuery('#current_page').keypress(function(e) {
        if(e.which == 13) {
            $(this).blur();
            $(this).next('.btn-goto').children('a').focus().click();
        }
    });
});

(function(){ // String extension methods

function isSameUrl(a,b) {
	return (a.replace(/#.*$/, '') === b.replace(/#.*$/, ''));
}

var isArray = Array.isArray || function(obj){ return Object.prototype.toString.call(obj)=='[object Array]' };

/**
 * @brief location.href return the value of a specific key
 **/
String.prototype.getQuery = function(key) {
	var loc = isSameUrl(this, window.location.href) ? current_url : this;
    var idx = loc.indexOf('?');
    if(idx == -1) return null;
    var query_string = loc.substr(idx+1, this.length), args = {};
    query_string.replace(/([^=]+)=([^&]*)(&|$)/g, function() { args[arguments[1]] = arguments[2]; });

    var q = args[key];
    if(typeof(q)=='undefined') q = '';

    return q;
}

/**
 * @brief location.href The value of a specific key
 **/
String.prototype.setQuery = function(key, val) {
	var loc = isSameUrl(this, window.location.href) ? current_url : this;
    var idx = loc.indexOf('?');
    var uri = loc.replace(/#$/, '');
	var act, re, v;

	if (typeof(val)=='undefined') val = '';

    if (idx != -1) {
        var query_string = uri.substr(idx+1, loc.length), args = {}, q_list = [];
		uri = loc.substr(0, idx);
        query_string.replace(/([^=]+)=([^&]*)(&|$)/g, function(all,key,val) { args[key] = val; });

        args[key] = val;

		for (var prop in args) {
			if (!args.hasOwnProperty(prop)) continue;
			if (!(v = String(args[prop]).trim())) continue;
			q_list.push(prop+'='+decodeURI(v));
		}

		query_string = q_list.join('&');
		uri = uri+(query_string?'?'+query_string:'');
    } else {
        if (String(val).trim()) uri = uri+'?'+key+'='+val;
    }

	re = /^https:\/\/([^:\/]+)(:\d+|)/i;
    if (re.test(uri)) {
        var toReplace = 'http://'+RegExp.$1;
        if (window.http_port && http_port != 80) toReplace += ':' + http_port;
        uri = uri.replace(re, toReplace);
    }

    var bUseSSL = !!window.enforce_ssl;
	if (!bUseSSL && isArray(window.ssl_actions) && (act=uri.getQuery('act'))) {
        for (var i=0,c=ssl_actions.length; i < c; i++) {
            if (ssl_actions[i] === act) {
                bUseSSL = true;
                break;
            }
        }
    }

	re = /http:\/\/([^:\/]+)(:\d+|)/i;
	if (bUseSSL && re.test(uri)) {
		var toReplace = 'https://'+RegExp.$1
		if (window.https_port && https_port != 443) toReplace += ':' + https_port;
		uri = uri.replace(re, toReplace);
	}

	// insert index.php if it isn't included
	uri = uri.replace(/\/(index\.php)?\?/, '/index.php?');

    return encodeURI(uri);
}

/**
 * @brief string prototype Add trim function
 **/
String.prototype.trim = function() {
    return this.replace(/(^\s*)|(\s*$)/g, "");
}

})();

/**
 * @brief xSleep(micro time)
 **/
function xSleep(sec) {
    sec = sec / 1000;
    var now = new Date();
    var sleep = new Date();
    while( sleep.getTime() - now.getTime() < sec) {
        sleep = new Date();
    }
}

/**
 * @brief Any of the given argument If it is not defined return false
 **/
function isDef() {
    for(var i=0; i < arguments.length; ++i) {
        if(typeof(arguments[i]) == "undefined") return false;
    }
    return true;
}

/**
 * @brief Open Windows
 * Implementation similar on FF window.focus (), through the management of the open window
 **/
var winopen_list = new Array();
function winopen(url, target, attribute) {
    if(typeof(xeVid)!='undefined' && url.indexOf(request_uri)>-1 && !url.getQuery('vid')) url = url.setQuery('vid',xeVid);
    try {
        if(target != "_blank" && winopen_list[target]) {
            winopen_list[target].close();
            winopen_list[target] = null;
        }
    } catch(e) {
    }

    if(typeof(target) == 'undefined') target = '_blank';
    if(typeof(attribute) == 'undefined') attribute = '';
    var win = window.open(url, target, attribute);
    win.focus();
    if(target != "_blank") winopen_list[target] = win;
}

/**
 * @brief Offset pop-up only
 * common/tpl/popup_layout.html The XE in the pop-up is requested.
 **/
function popopen(url, target) {
    if(typeof(target) == "undefined") target = "_blank";
    if(typeof(xeVid)!='undefined' && url.indexOf(request_uri)>-1 && !url.getQuery('vid')) url = url.setQuery('vid',xeVid);
    winopen(url, target, "width=650,height=500,scrollbars=yes,resizable=yes,toolbars=no");
}

/**
 * @brief Send mail to
 **/
function sendMailTo(to) {
    location.href="mailto:"+to;
}

/**
 * @brief url Movement (open_window If the value of N Float in a new window.)
 **/
function move_url(url, open_window) {
    if(!url) return false;
    if(typeof(open_window) == 'undefined') open_window = 'N';
    if(open_window=='N') {
        open_window = false;
    } else {
        open_window = true;
    }

    if(/^\./.test(url)) url = request_uri+url;

    if(open_window) {
        winopen(url);
    } else {
        location.href=url;
    }

    return false;
}

/**
 * @brief Multimedia output (IEFlash / Video bands dotted around)
 **/
function displayMultimedia(src, width, height, options) {
    var html = _displayMultimedia(src, width, height, options);
    if(html) document.writeln(html);
}
function _displayMultimedia(src, width, height, options) {
    if(src.indexOf('files') == 0) src = request_uri + src;

    var defaults = {
        wmode : 'transparent',
        allowScriptAccess : 'sameDomain',
        quality : 'high',
        flashvars : '',
        autostart : false
    };

    var params = jQuery.extend(defaults, options || {});
	var autostart = (params.autostart && params.autostart != 'false') ? 'true' : 'false';
	delete(params.autostart);

    var clsid = "";
    var codebase = "";
    var html = "";

    if(/\.(gif|jpg|jpeg|bmp|png)$/i.test(src)){
        html = '<img src="'+src+'" width="'+width+'" height="'+height+'" />';
    } else if(/\.flv$/i.test(src) || /\.mov$/i.test(src) || /\.moov$/i.test(src) || /\.m4v$/i.test(src)) {
        html = '<embed src="'+request_uri+'common/img/flvplayer.swf" allowfullscreen="true" autostart="'+autostart+'" width="'+width+'" height="'+height+'" flashvars="&file='+src+'&width='+width+'&height='+height+'&autostart='+autostart+'" wmode="'+params.wmode+'" />';
    } else if(/\.swf/i.test(src)) {
        clsid = 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000';

        if(typeof(enforce_ssl)!='undefined' && enforce_ssl){ codebase = "https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0"; }
        else { codebase = "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0"; }
        html = '<object classid="'+clsid+'" codebase="'+codebase+'" width="'+width+'" height="'+height+'" flashvars="'+params.flashvars+'">';
        html += '<param name="movie" value="'+src+'" />';
        for(var name in params) {
            if(params[name] != 'undefined' && params[name] != '') {
                html += '<param name="'+name+'" value="'+params[name]+'" />';
            }
        }
        html += ''
            + '<embed src="'+src+'" autostart="'+autostart+'"  width="'+width+'" height="'+height+'" flashvars="'+params.flashvars+'" wmode="'+params.wmode+'"></embed>'
            + '</object>';
    }  else {
		if (jQuery.browser.mozilla || jQuery.browser.opera) {
			// firefox and opera uses 0 or 1 for autostart parameter.
			autostart = (params.autostart && params.autostart != 'false') ? '1' : '0';
		}

        html = '<embed src="'+src+'" autostart="'+autostart+'" width="'+width+'" height="'+height+'"';
        if(params.wmode == 'transparent') {
            html += ' windowlessvideo="1"';
        }
        html += '></embed>';
    }
    return html;
}

/**
 * @brief The content in the editor code and close (fixed for zbxe)
 **/
function zbxe_folder_open(id) {
    jQuery("#folder_open_"+id).hide();
    jQuery("#folder_close_"+id).show();
    jQuery("#folder_"+id).show();
}
function zbxe_folder_close(id) {
    jQuery("#folder_open_"+id).show();
    jQuery("#folder_close_"+id).hide();
    jQuery("#folder_"+id).hide();
}

/**
 * @brief Adjust the size of the current window to fit the contents of the pop-up ongoing
 * Increasing the size to fit the contents of the pop-up ... Not easily
 * popup_layout nn the window.onload automatically when requested
 **/
function setFixedPopupSize() {
	var $ = jQuery, $win = $(window), $pc = $('body>.popup'), w, h, dw, dh, offset;

	offset = $pc.css({overflow:'scroll'}).offset();

	w = $pc.width(10).height(10000).get(0).scrollWidth + offset.left*2;
	h = $pc.height(10).width(10000).get(0).scrollHeight + offset.top*2;

	if(w < 600) w = 600 + offset.left*2;

	dw = $win.width();
	dh = $win.height();

	if(w != dw) window.resizeBy(w - dw, 0);
	if(h != dh) window.resizeBy(0, h - dh);

	$pc.width(w-offset.left*2).css({overflow:'',height:''});
}

/**
 * @brief Srl specific recommendations / negative, scrap, and reporting functions for a particular module / action to call the function
 **/
function doCallModuleAction(module, action, target_srl) {
    var params = {
		target_srl : target_srl,
		cur_mid    : current_mid,
		mid        : current_mid
	};
    exec_xml(module, action, params, completeCallModuleAction);
}

function completeCallModuleAction(ret_obj, response_tags) {
    if(ret_obj['message']!='success') alert(ret_obj['message']);
    location.reload();
}

function completeMessage(ret_obj) {
    alert(ret_obj['message']);
    location.reload();
}



/* change the cookie value language code (lang_type)*/
function doChangeLangType(obj) {
    if(typeof(obj) == "string") {
        setLangType(obj);
    } else {
        var val = obj.options[obj.selectedIndex].value;
        setLangType(val);
    }
	location.href = location.href.setQuery('l', '');
}
function setLangType(lang_type) {
    var expire = new Date();
    expire.setTime(expire.getTime()+ (7000 * 24 * 3600000));
    setCookie('lang_type', lang_type, expire, '/');
}

/* Preview */
function doDocumentPreview(obj) {
    var fo_obj = obj;
    while(fo_obj.nodeName != "FORM") {
        fo_obj = fo_obj.parentNode;
    }
    if(fo_obj.nodeName != "FORM") return;
    var editor_sequence = fo_obj.getAttribute('editor_sequence');

    var content = editorGetContent(editor_sequence);

    var win = window.open("", "previewDocument","toolbars=no,width=700px;height=800px,scrollbars=yes,resizable=yes");

    var dummy_obj = jQuery("#previewDocument");

    if(!dummy_obj.length) {
        jQuery(
            '<form id="previewDocument" target="previewDocument" method="post" action="'+request_uri+'">'+
            '<input type="hidden" name="module" value="document" />'+
            '<input type="hidden" name="act" value="dispDocumentPreview" />'+
            '<input type="hidden" name="content" />'+
            '</form>'
        ).appendTo(document.body);

        dummy_obj = jQuery("#previewDocument")[0];
    } else {
		dummy_obj = dummy_obj[0];
	}

    if(dummy_obj) {
        dummy_obj.content.value = content;
        dummy_obj.submit();
    }
}

/* Save posts */
function doDocumentSave(obj) {
    var editor_sequence = obj.form.getAttribute('editor_sequence');
    var prev_content = editorRelKeys[editor_sequence]['content'].value;
    if(typeof(editor_sequence)!='undefined' && editor_sequence && typeof(editorRelKeys)!='undefined' && typeof(editorGetContent)=='function') {
        var content = editorGetContent(editor_sequence);
        editorRelKeys[editor_sequence]['content'].value = content;
    }

	var params={}, responses=['error','message','document_srl'], elms=obj.form.elements, data=jQuery(obj.form).serializeArray();;
	jQuery.each(data, function(i, field){
		var val = jQuery.trim(field.value);
		if(!val) return true;
		if(/\[\]$/.test(field.name)) field.name = field.name.replace(/\[\]$/, '');
		if(params[field.name]) params[field.name] += '|@|'+val;
		else params[field.name] = field.value;
	});

	exec_xml('document','procDocumentTempSave', params, completeDocumentSave, responses, params, obj.form);

    editorRelKeys[editor_sequence]['content'].value = prev_content;
    return false;
}

function completeDocumentSave(ret_obj) {
    jQuery('input[name=document_srl]').eq(0).val(ret_obj['document_srl']);
    alert(ret_obj['message']);
}

/* Recalling Stored posts. */
var objForSavedDoc = null;
function doDocumentLoad(obj) {
    // Import a list of saved posts
    objForSavedDoc = obj.form;
    popopen(request_uri.setQuery('module','document').setQuery('act','dispTempSavedList'));
}

/* Select the saved posts */
function doDocumentSelect(document_srl) {
    if(!opener || !opener.objForSavedDoc) {
        window.close();
        return;
    }

    // Posts importing register.
    opener.location.href = opener.current_url.setQuery('document_srl', document_srl);
    window.close();
}
function doDocumentCancel(obj) {

    if (confirm(xe.lang.confirm_cancel)) {
        var mid = obj.mid;
        if(obj.is_mobile == 'Y'){
            url = current_url.setQuery('act', 'dispPageAdminMobileContent').setQuery('mid', mid);
        }
        else {
            url = current_url.setQuery('act', 'dispPageIndex').setQuery('mid', mid);
        }
        location.href = url;
    }
    return false;
}

/* Skin */
function viewSkinInfo(module, skin) {
    popopen("./?module=module&act=dispModuleSkinInfo&selected_module="+module+"&skin="+skin, 'SkinInfo');
}


/* Into the session when selecting an administrator to manage documents */
var addedDocument = new Array();
function doAddDocumentCart(obj) {
    var srl = obj.value;
    addedDocument[addedDocument.length] = srl;
    setTimeout(function() { callAddDocumentCart(addedDocument.length); }, 100);
}

function callAddDocumentCart(document_length) {
    if(addedDocument.length<1 || document_length != addedDocument.length) return;
    var params = new Array();
    params["srls"] = addedDocument.join(",");
    exec_xml("document","procDocumentAddCart", params, null);
    addedDocument = new Array();
}

/* Change to # ff rgb (a, b, c) ...  */
function transRGB2Hex(value) {
    if(!value) return value;
    if(value.indexOf('#') > -1) return value.replace(/^#/, '');

    if(value.toLowerCase().indexOf('rgb') < 0) return value;
    value = value.replace(/^rgb\(/i, '').replace(/\)$/, '');
    value_list = value.split(',');

    var hex = '';
    for(var i = 0; i < value_list.length; i++) {
        var color = parseInt(value_list[i], 10).toString(16);
        if(color.length == 1) color = '0'+color;
        hex += color;
    }
    return hex;
}

/* Login security mode */
function toggleSecuritySignIn() {
    var href = location.href;
    if(/https:\/\//i.test(href)) location.href = href.replace(/^https/i,'http');
    else location.href = href.replace(/^http/i,'https');
}

function reloadDocument() {
    location.reload();
}


/**
*
* Base64 encode / decode
* http://www.webtoolkit.info/
*
**/

var Base64 = {

    // private property
    _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    // public method for encoding
    encode : function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output +
            this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
            this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode : function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}






/* ----------------------------------------------
 * DEPRECATED
 * Reserved for backward compatibility and dropping
 * ------------------------------------------- */

if(typeof(resizeImageContents) == 'undefined') {
    function resizeImageContents() {}
}

if(typeof(activateOptionDisabled) == 'undefined') {
    function activateOptionDisabled() {}
}

objectExtend = jQuery.extend;

/**
 * @brief Options toggle the display of a particular Element
 **/
function toggleDisplay(objId) {
    jQuery('#'+objId).toggle();
}

/* Select the check box */
function checkboxSelectAll(formObj, name, checked) {
    var itemName = name;
    var option = {};
    if(typeof(formObj) != "undefined") option.wrap = formObj;
    if(typeof(checked) != "undefined") option.checked = checked;

    XE.checkboxToggleAll(itemName, option);
}

/* If the check box is checked */
function clickCheckBoxAll(formObj, name) {
    var itemName = name;
    var option = { doClick:true };
    if(typeof(formObj) != "undefined") option.wrap = formObj;

    XE.checkboxToggleAll(itemName, option);
}

/**
 * @brief The contents and close the code editor, but leave it as for the beta zb5beta compatible (release)
 **/
function svc_folder_open(id) {
    jQuery("#_folder_open_"+id).hide();
    jQuery("#_folder_close_"+id).show();
    jQuery("#_folder_"+id).show();
}
function svc_folder_close(id) {
    jQuery("#_folder_open_"+id).show();
    jQuery("#_folder_close_"+id).hide();
    jQuery("#_folder_"+id).hide();
}

/**
 * @brief Date picker (calendar Open)
 **/
function open_calendar(fo_id, day_str, callback_func) {
    if(typeof(day_str)=="undefined") day_str = "";

    var url = "./common/tpl/calendar.php?";
    if(fo_id) url+="fo_id="+fo_id;
    if(day_str) url+="&day_str="+day_str;
    if(callback_func) url+="&callback_func="+callback_func;

    popopen(url, 'Calendar');
}

var loaded_popup_menus = XE.loaded_popup_menus;
function createPopupMenu() {}
function chkPopupMenu() {}
function displayPopupMenu(ret_obj, response_tags, params) {
    XE.displayPopupMenu(ret_obj, response_tags, params);
}

function GetObjLeft(obj) {
    return jQuery(obj).offset().left;
}
function GetObjTop(obj) {
    return jQuery(obj).offset().top;
}

function replaceOuterHTML(obj, html) {
    jQuery(obj).replaceWith(html);
}

function getOuterHTML(obj) {
    return jQuery(obj).html().trim();
}

function setCookie(name, value, expire, path) {
	var s_cookie = name + "=" + escape(value) +
		((!expire) ? "" : ("; expires=" + expire.toGMTString())) +
		"; path=" + ((!path) ? "/" : path);

	document.cookie = s_cookie;
}

function getCookie(name) {
	var match = document.cookie.match(new RegExp(name+'=(.*?)(?:;|$)'));
	if(match) return unescape(match[1]);
}

function is_def(v) {
	return (typeof(v)!='undefined');
}

function ucfirst(str) {
	return str.charAt(0).toUpperCase() + str.slice(1);
}

function get_by_id(id) {
	return document.getElementById(id);
}
function submitAndLoadArea(action, formSelector, areaSelector, replace, dataType){
    if (typeof  dataType == "undefined"){
        dataType = "html"
    }
    if (dataType == 'json'){
        jQuery.exec_json(
            action,
            jQuery(formSelector).serializeArray(),
            function(response){
                var html = response.content;
                if (replace) {
                    jQuery(areaSelector).replaceWith(html);
                }
                else {
                    jQuery(areaSelector).html(html);
                }
            }
        )
    }
    else {
        jQuery.ajax({
            type: "POST",
            url: jQuery(formSelector).attr('action'),
            data: jQuery(formSelector).serialize(),
            dataType: dataType,
            success: function(response){
                if (replace) {
                    jQuery(areaSelector).replaceWith(response);
                }
                else {
                    jQuery(areaSelector).html(response);
                }
            }
        })
    }
}
jQuery(function($){
    $('.lang_code').each(
		function() 
		{
			var objText = $(this);
			var targetName = objText.attr("id");
			if(typeof(targetName) == "undefined") targetName = objText.attr("name");
			if(typeof(targetName) == "undefined") return;
			objText.after("<a href='"+request_uri.setQuery('module','module').setQuery('act','dispModuleAdminLangcode').setQuery('target',targetName)+"' class='buttonSet buttonSetting' onclick='popopen(this.href);return false;'><span>find_langcode</span></a>"); 
		}
    );

	// display popup menu that contains member actions and document actions
	$(document).click(function(evt) {
		var $area = $('#popup_menu_area');
		if(!$area.length) {
            //$area = $('<div id="popup_menu_area" style="display:none;z-index:9999" />').appendTo(document.body);
            $area = $('<div id="popup_menu_area" class="modal fade" tabindex="-1" role="dialog">');
            var innerHtml = '<div class="modal-header">';
            innerHtml += '    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
            innerHtml += '</div>';
            innerHtml += '<div class="modal-body"></div>';
            innerHtml += '<div class="modal-footer"></div>';
            $area.html(innerHtml);
            $area.appendTo(document.body);
        }

		// hide popup menu
		//$area.hide();

		var $target = $(evt.target).filter('a,div,span');
		if(!$target.length) $target = $(evt.target).closest('a,div,span');
		if(!$target.length) return;

        // get value of className from the object
		var cls = $target.attr('class'), match;
		if(cls) match = cls.match(new RegExp('(?:^| )((document|comment|member)_([1-9]\\d*))(?: |$)',''));
		if(!match) return;

		var action = 'get'+ucfirst(match[2])+'Menu';
		var params = {
			mid        : current_mid,
			cur_mid    : current_mid,
			menu_id    : match[1],
			target_srl : match[3],
			cur_act    : current_url.getQuery('act'),
			page_x     : evt.pageX,
			page_y     : evt.pageY
		};
		var response_tags = 'error message menus'.split(' ');

		// prevent default action
		evt.preventDefault();
		evt.stopPropagation();

		if(is_def(window.xeVid)) params.vid = xeVid;
		if(is_def(XE.loaded_popup_menus[params.menu_id])) return XE.displayPopupMenu(params, response_tags, params);

		show_waiting_message = false;
		exec_xml('member', action, params, XE.displayPopupMenu, response_tags, params);
        show_waiting_message = true;
    });

	/**
	 * Create popup windows automatically.
	 * Find anchors that have the '_xe_popup' class, then add popup script to them.
	 */
	$('a._xe_popup').click(function(){
		var $this = $(this), name = $this.attr('name'), href = $this.attr('href'), win;

		if(!name) name = '_xe_popup_'+Math.floor(Math.random()*1000);

		win = window.open(href, name, 'left=10,top=10,width=10,height=10,resizable=no,scrollbars=no,toolbars=no');
		if(win) win.focus();

		// cancel default action
		return false;
	});

	// date picker default settings
	if($.datepicker) {
		$.datepicker.setDefaults({
			dateFormat : 'yy-mm-dd'
		});
	}
});
function insertModuleModal(id, module_srl, mid, browser_title, multi_select) {
    var separator = '';
    if (typeof  documentListHref == "undefined"){
        documentListHref = window.location.href;
    }
    var parts = documentListHref.split('#');
    documentListHref = parts[0];
    if (documentListHref.indexOf('?') == -1) {
        separator = '?';
    }
    else {
        separator = '&';
    }
    var newHref = documentListHref + separator + 'selected_module_srl=' + module_srl;
    window.location.href = newHref;
}
function karybuCheckAllCheckboxes(elem,selector){
    if (jQuery(elem).is(':checked')){
        jQuery(selector).attr('checked', 'checked');
    }
    else {
        jQuery(selector).removeAttr('checked');
    }
}
function goToPage(elem){
    var value = parseInt(jQuery(elem).val());
    var parent = jQuery(elem).closest('#FileBoxGoTo');
    if (!isNaN(value)){
        if (!parent.length){
            window.location.href = current_url.setQuery('page', value);
        }
    }
    else{
        jQuery(elem).focus();
    }
}
