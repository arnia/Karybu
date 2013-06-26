/**
 * Variables for use in the editor
 **/
var editorMode = new Array(); ///<< Html editor in edit mode flag set variables (html or null)
var editorAutoSaveObj = {fo_obj:null, editor_sequence:0, title:'', content:'', locked:false} ///< Information for the object with automatic storage
var editorRelKeys = new Array(); ///< Editor and integration with each module to keep the value of key variables for
var editorDragObj = {isDrag:false, y:0, obj:null, id:'', det:0, source_height:0}

function editorGetContent(editor_sequence) {
    // Ohm input information received
    var content = editorRelKeys[editor_sequence]["func"](editor_sequence);

    // Change attachment url link time
    var reg_pattern = new RegExp( request_uri.replace(/\//g,'\\/')+"(files|common|modules|layouts|widgets)", 'ig' );
    return content.replace(reg_pattern, "$1");
}

// Zoom focus to the editor
function editorFocus(editor_sequence) {
	try {
		var iframe_obj = editorGetIFrame(editor_sequence);
		if (jQuery.isFunction(iframe_obj.setFocus)) {
			iframe_obj.setFocus();
		} else {
			iframe_obj.contentWindow.focus();
		}
	} catch(e){}
}

/**
 * Auto-save feature
 **/
// To enable auto-save function (automatically saved every 50 seconds)
function editorEnableAutoSave(fo_obj, editor_sequence, callback) {
    var title   = fo_obj.title.value;
    var content = editorRelKeys[editor_sequence]['content'].value;

    editorAutoSaveObj = {"fo_obj":fo_obj, "editor_sequence":editor_sequence, "title":title, "content":content, locked:false};

	clearTimeout(editorEnableAutoSave.timer);
    editorEnableAutoSave.timer = setTimeout(function(){_editorAutoSave(false, callback)}, 50000);
}
editorEnableAutoSave.timer = null;

// using ajax auto-save Sikkim exe editor.procEditorSaveDoc forced to call code
function _editorAutoSave(exe, callback) {
    var fo_obj = editorAutoSaveObj.fo_obj;
    var editor_sequence = editorAutoSaveObj.editor_sequence;

    // Forcing synchronization every 50 seconds except Sikkim
    if(!exe) {
		clearTimeout(editorEnableAutoSave.timer);
		editorEnableAutoSave.timer = setTimeout(function(){ _editorAutoSave(exe, callback) }, 50000);
	}

    // Auto Save is currently stopped in
    if(editorAutoSaveObj.locked == true) return;

    // If there is no auto-save function that stops itself
    if(!fo_obj || typeof(fo_obj.title)=='undefined' || !editor_sequence) return;

    // Preparing for auto-save
    var title = fo_obj.title.value;
	var content = '';
	try{
	   content = editorGetContent(editor_sequence);
	}catch(e){
	}

    // Who had previously saved information is different than that, or Force Save Auto Save automatically saved when setting
    if(title != editorAutoSaveObj.title || content != editorAutoSaveObj.content || exe) {
        var params, oDate = new Date();

        params = {
			title   : title,
			content : content,
			mid     : current_mid,
			document_srl : editorRelKeys[editor_sequence]['primary'].value
		};

        editorAutoSaveObj.title   = title;
        editorAutoSaveObj.content = content;

        // Creating a message showing
        jQuery("#editor_autosaved_message_"+editor_sequence).text(oDate.getHours()+':'+oDate.getMinutes()+' '+auto_saved_msg).show(300);

        // Auto Save is in the current set
        editorAutoSaveObj.locked = true;

        // Call server (the server and the communication of the message should not be seen)
        show_waiting_message = false;
        exec_xml(
			"editor",
			"procEditorSaveDoc",
			params,
			function() {
				var arg = jQuery.extend({}, params, {auto_saved_msg:auto_saved_msg});
			
				editorAutoSaveObj.locked = false;
				if(jQuery.isFunction(callback)) callback(arg);
			}
		);
        show_waiting_message = true;
    }
}

// Routines to automatically delete all messages stored
function editorRemoveSavedDoc() {
    var param = new Array();
    param['mid'] = current_mid;
    exec_xml("editor","procEditorRemoveSavedDoc", param);
}

/**
 * Editor to save the state of a function or object
 **/

// editor_sequence object that corresponds to the value of the return of the iframe
function editorGetIFrame(editor_sequence) {
    if(editorRelKeys != undefined && editorRelKeys[editor_sequence] != undefined && editorRelKeys[editor_sequence]['editor'] != undefined)
		return editorRelKeys[editor_sequence]['editor'].getFrame(editor_sequence);
    return document.getElementById( 'editor_iframe_'+ editor_sequence );
}
function editorGetTextarea(editor_sequence) {
    return document.getElementById( 'editor_textarea_'+ editor_sequence );
}

// Editor Option Button
function eOptionOver(obj) {
    obj.style.marginTop='-21px';
    obj.style.zIndex='99';
}
function eOptionOut(obj) {
    obj.style.marginTop='0';
    obj.style.zIndex='1';
}
function eOptionClick(obj) {
    obj.style.marginTop='-42px';
    obj.style.zIndex='99';
}

/**
 * Implementation section editor component
 **/

// Action at the click of a button at the top of the editor component handling (mouse-down event occurs every request AM)
var editorPrevSrl = null;
function editorEventCheck(e) {
    editorPrevNode = null;

    // ID of the object to which the event occurred Wanted
    var target_id = e.target.id;
    if(!target_id) return;

    // editor_sequence component name Wanted (id format is different from the return)
    var info = target_id.split('_');
    if(info[0]!="component") return;
    var editor_sequence = info[1];
    var component_name = target_id.replace(/^component_[0-9]+_/,'');

    if(!editor_sequence || !component_name) return;
    if(editorMode[editor_sequence]=='html') return;

    switch(component_name) {

        // Actions for basic functions (ready-to-run)
        case 'Bold' :
        case 'Italic' :
        case 'Underline' :
        case 'StrikeThrough' :
        case 'undo' :
        case 'redo' :
        case 'JustifyLeft' :
        case 'JustifyCenter' :
        case 'JustifyRight' :
        case 'JustifyFull' :
        case 'Indent' :
        case 'Outdent' :
        case 'InsertOrderedList' :
        case 'InsertUnorderedList' :
        case 'SaveAs' :
        case 'Print' :
        case 'Copy' :
        case 'Cut' :
        case 'Paste' :
        case 'RemoveFormat' :
        case 'Subscript' :
        case 'Superscript' :
            editorDo(component_name, '', editor_sequence);
            break;

        // Additional components in the case of a request to the server attempts to
        default :
			openComponent(component_name, editor_sequence);
			return false;
    }

    return;
}
jQuery(document).click(editorEventCheck);

// Open the pop-up component
function openComponent(component_name, editor_sequence, manual_url) {
    editorPrevSrl = editor_sequence;
    if(editorMode[editor_sequence]=='html') return;

    var popup_url = request_uri+"?module=editor&act=dispEditorPopup&editor_sequence="+editor_sequence+"&component="+component_name;
    if(typeof(manual_url)!="undefined" && manual_url) popup_url += "&manual_url="+escape(manual_url);

    popopen(popup_url, 'editorComponent');
}

// Double-click the event in the event of finding the components included within the body of the function
var editorPrevNode = null;
function editorSearchComponent(evt) {
    var e = new xEvent(evt);

    editorPrevNode = null;
    var obj = e.target;

    // Check whether one widget
    if(obj.getAttribute("widget")) {
        // editor_sequence Finding
        var tobj = obj;
        while(tobj && tobj.nodeName != "BODY") {
            tobj = xParent(tobj);
        }
        if(!tobj || tobj.nodeName != "BODY" || !tobj.getAttribute("editor_sequence")) {
            editorPrevNode = null;
            return;
        }
        var editor_sequence = tobj.getAttribute("editor_sequence");
        var widget = obj.getAttribute("widget");
        editorPrevNode = obj;

        if(editorMode[editor_sequence]=='html') return;
        popopen(request_uri+"?module=widget&act=dispWidgetGenerateCodeInPage&selected_widget="+widget+"&module_srl="+editor_sequence,'GenerateCodeInPage');
        return;
    }

    // From the top, while the selected object editor_component attribute checks
    if(!obj.getAttribute("editor_component")) {
        while(obj && !obj.getAttribute("editor_component")) {
            if(obj.parentElement) obj = obj.parentElement;
            else obj = xParent(obj);
        }
    }

    if(!obj) obj = e.target;

    var editor_component = obj.getAttribute("editor_component");

    // editor_component If you can not find the image / text / links, the basic components and connections
    if(!editor_component) {
        // If the image
        if(obj.nodeName == "IMG" && !obj.getAttribute("widget")) {
            editor_component = "image_link";
            editorPrevNode = obj;
        }
    } else {
        editorPrevNode = obj;
    }

    // If there is no return editor_component
    if(!editor_component) {
        editorPrevNode = null;
        return;
    }

    // editor_sequence Finding
    var tobj = obj;
    while(tobj && tobj.nodeName != "BODY") {
        tobj = xParent(tobj);
    }
    if(!tobj || tobj.nodeName != "BODY" || !tobj.getAttribute("editor_sequence")) {
        editorPrevNode = null;
        return;
    }
    var editor_sequence = tobj.getAttribute("editor_sequence");

    // Locate and run the component
    openComponent(editor_component, editor_sequence);
}

// Html editor to change the code of the selected area in the
function editorReplaceHTML(iframe_obj, html) {
    // Image redirected (rewrite mod)
    var srcPathRegx = /src=("|\'){1}(\.\/)?(files\/attach|files\/cache|files\/faceOff|files\/member_extra_info|modules|common|widgets|widgetstyle|layouts|addons)\/([^"\']+)\.(jpg|jpeg|png|gif)("|\'){1}/g;
    html = html.replace(srcPathRegx, 'src="'+request_uri+'$3/$4.$5"');

    // href Redirected (rewrite mod)
    var hrefPathRegx = /href=("|\'){1}(\.\/)?\?([^"\']+)("|\'){1}/g;
    html = html.replace(hrefPathRegx, 'href="'+request_uri+'?$3"');

    // Make sure the editor is activated activated and deactivated when
    var editor_sequence = iframe_obj.editor_sequence || iframe_obj.contentWindow.document.body.getAttribute("editor_sequence");

    // iframe Placing the focus to the editor
	try { iframe_obj.contentWindow.focus(); }catch(e){};
	
	if (jQuery.isFunction(iframe_obj.replaceHTML)) {
		iframe_obj.replaceHTML(html);
	} else if(xIE4Up) {
        var range = iframe_obj.contentWindow.document.selection.createRange();
        if(range.pasteHTML) {
            range.pasteHTML(html);
        } else if(editorPrevNode) {
            editorPrevNode.outerHTML = html;
        }
    } else {
        try {
            if(iframe_obj.contentWindow.getSelection().focusNode.tagName == "HTML") {
                var range = iframe_obj.contentDocument.createRange();
                range.setStart(iframe_obj.contentDocument.body,0);
                range.setEnd(iframe_obj.contentDocument.body,0);
                range.insertNode(range.createContextualFragment(html));
            } else {
                var range = iframe_obj.contentWindow.getSelection().getRangeAt(0);
                range.deleteContents();
                range.insertNode(range.createContextualFragment(html));
            }
        } catch(e) {
            xInnerHtml(iframe_obj.contentWindow.document.body, html+xInnerHtml(iframe_obj.contentWindow.document.body));
        }
    }
}

// Editor's html code within the selected portion of return
function editorGetSelectedHtml(editor_sequence) {
    var iframe_obj = editorGetIFrame(editor_sequence);
	if (jQuery.isFunction(iframe_obj.getSelectedHTML)) {
		return iframe_obj.getSelectedHTML();
    } else if(xIE4Up) {
        var range = iframe_obj.contentWindow.document.selection.createRange();
        var html = range.htmlText;
        return html;
    } else {
        var range = iframe_obj.contentWindow.getSelection().getRangeAt(0);
        var dummy = xCreateElement('div');
        dummy.appendChild(range.cloneContents());
        var html = xInnerHtml(dummy);
        return html;
    }
}


// {{{ iframe Vertical scaling
(function($){

var dragging  = false;
var startY    = 0;
var startH    = 0;
var editorId  = '';
var eventObj  = null; // event target object
var targetObj = null; // elements to be resized

function editorDragStart(e) {
    var obj = $(e.target);
	var id = obj.attr('id');

    if(!id || !/^editor_drag_bar_(.+)$/.test(id)) return;

    dragging  = true;
    startY    = e.pageY;
    eventObj  = obj;
	editorId  = RegExp.$1;

    var iframe_obj   = $( editorGetIFrame(editorId) );
    var textarea_obj = $( editorGetTextarea(editorId) );
    var preview_obj  = $('#editor_preview_'+editorId);
	var visible_obj  = iframe_obj.is(':visible')?iframe_obj:textarea_obj;

	startH = parseInt(visible_obj.css('height'));

	targetObj = $([ iframe_obj[0], textarea_obj[0] ]);
	if (preview_obj.length) targetObj.add(preview_obj[0]);

	if (!isNaN(startH) || !startH) {
		var oh_before = visible_obj[0].offsetHeight;
		visible_obj.css('height', oh_before+'px');
		var oh_after = visible_obj[0].offsetHeight;

		startH = oh_before*2 - oh_after;
		targetObj.css('height', startH+'px');
	}

	$('#xeEditorMask_' + editorId).show();
	$(document).mousemove(editorDragMove);

	return false;
}

function editorDragMove(e) {
    if(!dragging) {
        $('#xeEditorMask_' + editorId).hide();
        return;
    }

    var diff = e.pageY - startY;
	targetObj.css('height', (startH + diff)+'px');

	return false;
}

function editorDragStop(e) {
	$('#xeEditorMask_' + editorId).hide();
    if(!dragging) return;

	$(document).unbind('mousemove', editorDragMove);

	if($.isFunction(window.fixAdminLayoutFooter)) {
		var diff = parseInt(targetObj.eq(0).css('height')) - startH;

		fixAdminLayoutFooter( diff );
	}

    dragging  = false;
    startY    = 0;
    eventObj  = null;
	targetObj = null;
	editorId  = '';

	return false;
}

/*
$(document).bind({
	mousedown : editorDragStart,
	mouseup   : editorDragStop
});
*/

})(jQuery);
// }}} iframe Vertical scaling
