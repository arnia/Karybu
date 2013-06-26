/**
 * @author Arnia (dev@karybu.org)
 * @version 0.1
 * @brief Associated script editor
 */

/**
 * The events connected with the editor using the function call
 **/

/**
 * Editor to save the state of a function or object
 **/

// editor_sequence That corresponds to the value of the textarea object return
function editorGetTextArea(editor_sequence) {
    return jQuery('#editor_textarea_' + editor_sequence)[0];
}

function editorGetPreviewArea(editor_sequence) {
    return jQuery( '#editor_preview_' + editor_sequence )[0];
}

// editor_sequence Form corresponding to the door Wanted
function editorGetForm(editor_sequence) {
    var iframe_obj = editorGetIFrame(editor_sequence);
    if(!iframe_obj) return;

    var fo_obj = iframe_obj.parentNode;
    while(fo_obj.nodeName != 'FORM') { fo_obj = fo_obj.parentNode; }
    if(fo_obj.nodeName == 'FORM') return fo_obj;
    return;
}

// return The entire contents of the editor
function editorGetContent_xe(editor_sequence) {
    var html = "";
    if(editorMode[editor_sequence]=='html') {
        var textarea_obj = editorGetTextArea(editor_sequence);
        if(!textarea_obj) return "";
        html = textarea_obj.value;
    } else {
        var iframe_obj = editorGetIFrame(editor_sequence);
        if(!iframe_obj) return "";
        html = jQuery(iframe_obj.contentWindow.document.body).html().replace(/^<br([^>]*)>$/i,'');
    }
    return html;
}

// return The editor in the selected part of the NODE
function editorGetSelectedNode(editor_sequence) {
    var iframe_obj = editorGetIFrame(editor_sequence), w, range;

	w = iframe_obj.contentWindow;

    if(w.document.selection) {
        range = w.document.selection.createRange();
        return jQuery('<div />').html(range.htmlText)[0].firstChild;
    } else {
        range = w.getSelection().getRangeAt(0);
        return jQuery('<div />').append(range.cloneContents())[0].firstChild;
    }
}

/**
 * editor Start (editor_sequence get the iframe object, switch to writing mode)
 **/
var _editorFontColor = new Array();
function editorStart(editor_sequence, primary_key, content_key, editor_height, font_color) {

    if(typeof(font_color)=='undefined') font_color = '#000';
    _editorFontColor[editor_sequence] = font_color;

    // Seeking iframe obj
    var iframe_obj = editorGetIFrame(editor_sequence);
    if(!iframe_obj) return;
	jQuery(iframe_obj).css('width', '100%').parent().css('width', '100%');

    // Editor door that covers current form found
    var fo_obj = editorGetForm(editor_sequence);
    if(!fo_obj) return;

    // fo_obj Specify a value for editor_sequence
    fo_obj.setAttribute('editor_sequence', editor_sequence);

    // Set the key value associated with the module
    editorRelKeys[editor_sequence] = new Array();
    editorRelKeys[editor_sequence]["primary"] = fo_obj[primary_key];
    editorRelKeys[editor_sequence]["content"] = fo_obj[content_key];
    editorRelKeys[editor_sequence]["func"] = editorGetContent_xe;

    // check for saved document(Auto Save Document)
    if(typeof(fo_obj._saved_doc_title)!="undefined" ) { ///<< _saved_doc_title field If there is no auto-save

        var saved_title = fo_obj._saved_doc_title.value;
        var saved_content = fo_obj._saved_doc_content.value;

        if(saved_title || saved_content) {
            // Whether the water is automatically utilize the saved document if you do not use the auto-delete stored documents
            if(confirm(fo_obj._saved_doc_message.value)) {
                if(typeof(fo_obj.title)!='undefined') fo_obj.title.value = saved_title;
                editorRelKeys[editor_sequence]['content'].value = saved_content;

                var param = new Array();
                param['editor_sequence'] = editor_sequence;
                param['primary_key'] = primary_key;
                param['mid'] = current_mid;
                var response_tags = new Array("error","message","editor_sequence","key","title","content","document_srl");
                exec_xml('editor',"procEditorLoadSavedDocument", param, getAutoSavedSrl, response_tags);
            } else {
                editorRemoveSavedDoc();
            }
        }
    }

    // Data from the target of the form content element Wanted
    var content = editorRelKeys[editor_sequence]['content'].value;

    // If there are not the IE add <br /> (FF, etc. When selecting iframe tricks to give focus)
    if(!content && !xIE4Up) content = "<br />";

    // If IE ctrl-Enter phrase exposure guidance
    var ieHelpObj = xGetElementById("for_ie_help_"+editor_sequence);
    if(xIE4Up && ieHelpObj) {
        ieHelpObj.style.display = "block";
    }

    // content Generation
    editor_path = editor_path.replace(/^\.\//ig, '');
    var contentHtml = ''+
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'+
        '<html xmlns="http://www.w3.org/1999/xhtml><head><meta http-equiv="content-type" content="text/html; charset=utf-8"/>'+
        '<style type="text/css">'+
        'body {font-size:.75em; line-height:1.6; font-family:Sans-serif; height:'+editor_height+'px; padding:0; margin:0; background-color:transparent; color:'+font_color+';}'+
        '</style>'+
        '</head><body editor_sequence="'+editor_sequence+'">'+
        content+
        '</body></html>'+
        '';
    iframe_obj.contentWindow.document.open("text/html","replace");
    iframe_obj.contentWindow.document.write(contentHtml);
    iframe_obj.contentWindow.document.close();

    // Set on the basis of editorMode
    editorMode[editor_sequence] = null;

    // Editor starts
    try {
        iframe_obj.contentWindow.document.designMode = 'On';
    } catch(e) {
    }

    try {
        iframe_obj.contentWindow.document.execCommand("undo", false, null);
        iframe_obj.contentWindow.document.execCommand("useCSS", false, true);
    }  catch (e) {
    }

    /**
    * Double-click or keypress listener for various events, such as additional
    * Check the required events when writing
    * In this event, the windows sp1 (NT or xp sp1) iframe_obj.contentWindow.document in so by the authority of the try statement wrapping
    * Error should be ignored.
    */

    // Make a widget for monitoring the double-click event
    try {
		jQuery(iframe_obj.contentWindow.document)
			.unbind('dblclick.widget')
			.bind('dblclick.widget', editorSearchComponent);
    } catch(e) {
    }

    // Each time the key is pressed in the editor, checking the event (enter key or FF in the treatment process, such as alt-s)
    try {
        if(xIE4Up) xAddEventListener(iframe_obj.contentWindow.document, 'keydown',editorKeyPress);
        else xAddEventListener(iframe_obj.contentWindow.document, 'keypress',editorKeyPress);
    } catch(e) {
    }

    // If you have auto-save auto-save feature enable field
    if(typeof(fo_obj._saved_doc_title)!="undefined" ) editorEnableAutoSave(fo_obj, editor_sequence);


    // Ugly, but;; style variant is to prevent the html change when runners start
    if (xGetCookie('editor_mode') == 'html'){
        var iframe_obj = editorGetIFrame(editor_sequence);
        if(xGetElementById('fileUploader_'+editor_sequence)) xGetElementById('fileUploader_'+editor_sequence).style.display='block';
        textarea_obj = editorGetTextArea(editor_sequence);
        textarea_obj.value = content;
        xWidth(textarea_obj, xWidth(iframe_obj.parentNode));
        xHeight(textarea_obj, xHeight(iframe_obj.parentNode));
        editorMode[editor_sequence] = 'html';
        if(xGetElementById('xeEditor_'+editor_sequence)) {
            xGetElementById('xeEditor_'+editor_sequence).className = 'xeEditor html';
            xGetElementById('use_rich_'+editor_sequence).className = '';
            xGetElementById('preview_html_'+editor_sequence).className = '';
            xGetElementById('use_html_'+editor_sequence).className = 'active';
        }
    }
}


/**
 * Editor of the details of the settings and data handling functions defined
 **/



/**
 * Defined function keys or mouse event handling
 **/

// Check the input key event for
function editorKeyPress(evt) {
    var e = new xEvent(evt);

    // Wanted destination
    var obj = e.target;
    var body_obj = null;
    if(obj.nodeName == "BODY") body_obj = obj;
    else body_obj = obj.firstChild.nextSibling;
    if(!body_obj) return;

    //  Attribute to the body of the editor is defined as editor_sequence
    var editor_sequence = body_obj.getAttribute("editor_sequence");
    if(!editor_sequence) return;

    // IE when you press the enter key on input BR tags instead of P tags
    if (xIE4Up && !e.ctrlKey && !e.shiftKey && e.keyCode == 13 && !editorMode[editor_sequence]) {
        var iframe_obj = editorGetIFrame(editor_sequence);
        if(!iframe_obj) return;

        var contentDocument = iframe_obj.contentWindow.document;

        var obj = contentDocument.selection.createRange();

        var pTag = obj.parentElement().tagName.toLowerCase();

        switch(pTag) {
            case 'li' :
                    return;
                break;
            default :
                    obj.pasteHTML("<br />");
                break;
        }
        obj.select();
        evt.cancelBubble = true;
        evt.returnValue = false;
        return;
    }

    // ctrl-S, alt-S When you click to submit
    if( e.keyCode == 115 && (e.altKey || e.ctrlKey) ) {
        // iframe Seeking Editor
        var iframe_obj = editorGetIFrame(editor_sequence);
        if(!iframe_obj) return;

        // Finding the target form
        var fo_obj = editorGetForm(editor_sequence);
        if(!fo_obj) return;

        // Data Synchronization
        editorRelKeys[editor_sequence]['content'].value = editorGetContent(editor_sequence);

        // transfer form
        if(fo_obj.onsubmit) fo_obj.onsubmit();

        // Events
        evt.cancelBubble = true;
        evt.returnValue = false;
        xPreventDefault(evt);
        xStopPropagation(evt);
        return;
    }

    // ctrl-b, i, u, s for the key process (in Firefox shortcuts in the editor to write the state)
    if (e.ctrlKey) {
        // iframe Seeking Editor
        var iframe_obj = editorGetIFrame(editor_sequence);
        if(!iframe_obj) return;

        // html Editor mode, cancel the event
        if(editorMode[editor_sequence]) {
            evt.cancelBubble = true;
            evt.returnValue = false;
            xPreventDefault(evt);
            xStopPropagation(evt);
            return;
        }

        switch(e.keyCode) {
            // ctrl+1~6
            case 49 :
            case 50 :
            case 51 :
            case 52 :
            case 53 :
            case 54 :
                    editorDo('formatblock',"<H"+(e.keyCode-48)+">",e.target);
                    xPreventDefault(evt);
                    xStopPropagation(evt);
                break;
            // ctrl+7
            case 55 :
                    editorDo('formatblock',"<P>",e.target);
                    xPreventDefault(evt);
                    xStopPropagation(evt);
                break;
            // ctrlKey + enter ie one at the P tag input
            case 13 :
                    if(xIE4Up) {
                        if(e.target.parentElement.document.designMode!="On") return;
                        var obj = e.target.parentElement.document.selection.createRange();
                        obj.pasteHTML('<P>');
                        obj.select();
                        evt.cancelBubble = true;
                        evt.returnValue = false;
                        return;
                    }
            // bold
            case 98 :
                    editorDo('Bold',null,e.target);
                    xPreventDefault(evt);
                    xStopPropagation(evt);
                break;
            // italic
            case 105 :
                    editorDo('Italic',null,e.target);
                    xPreventDefault(evt);
                    xStopPropagation(evt);
                break;
            // underline
            case 117 :
                    editorDo('Underline',null,e.target);
                    xPreventDefault(evt);
                    xStopPropagation(evt);
                break;
            //RemoveFormat
            case 100 :
                    editorDo('RemoveFormat',null,e.target);
                    xPreventDefault(evt);
                    xStopPropagation(evt);
                break;


            // strike
            /*
            case 83 :
            case 115 :
                    editorDo('StrikeThrough',null,e.target);
                    xPreventDefault(evt);
                    xStopPropagation(evt);
                break;
            */
        }
    }
}

// Edit function is executed
function editorDo(command, value, target) {

    var doc = null;

    // depending on whether the object is a target document Seeking editor_sequence
    if(typeof(target)=="object") {
        if(xIE4Up) doc = target.parentElement.document;
        else doc = target.parentNode;
    } else {
        var iframe_obj = editorGetIFrame(target);
        doc = iframe_obj.contentWindow.document;
    }

    var editor_sequence = doc.body.getAttribute('editor_sequence');
    if(editorMode[editor_sequence]) return;

    // Focus
    if(typeof(target)=="object") target.focus();
    else editorFocus(target);

    // Execution
    doc.execCommand(command, false, value);

    // Focus
    if(typeof(target)=="object") target.focus();
    else editorFocus(target);
}

// Change the font
function editorChangeFontName(obj,srl) {
    var value = obj.options[obj.selectedIndex].value;
    if(!value) return;
    editorDo('FontName',value,srl);
    obj.selectedIndex = 0;
}

function editorChangeFontSize(obj,srl) {
    var value = obj.options[obj.selectedIndex].value;
    if(!value) return;
    editorDo('FontSize',value,srl);
    obj.selectedIndex = 0;
}

function editorUnDo(obj,srl) {
    editorDo('undo','',srl);
    obj.selectedIndex = 0;
}

function editorReDo(obj,srl) {
    editorDo('redo','',srl);
    obj.selectedIndex = 0;
}

function editorChangeHeader(obj,srl) {
    var value = obj.options[obj.selectedIndex].value;
    if(!value) return;
    value = "<"+value+">";
    editorDo('formatblock',value,srl);
    obj.selectedIndex = 0;
}

/**
 * HTML Editing active / inactive
 **/

function editorChangeMode(mode, editor_sequence) {

    if(mode == 'html' || mode ==''){
        var expire = new Date();
        expire.setTime(expire.getTime()+ (7000 * 24 * 3600000));
        xSetCookie('editor_mode', mode, expire);
    }

    var iframe_obj = editorGetIFrame(editor_sequence);
    if(!iframe_obj) return;

    var textarea_obj = editorGetTextArea(editor_sequence);
    var preview_obj = editorGetPreviewArea(editor_sequence);
    var contentDocument = iframe_obj.contentWindow.document;

    var html = null;
    if(editorMode[editor_sequence]=='html') {
        html = textarea_obj.value;
        contentDocument.body.innerHTML = textarea_obj.value;
    } else if (editorMode[editor_sequence]=='preview') {
//        html = xInnerHtml(preview_obj);
        html = textarea_obj.value;
        preview_obj.contentWindow.document.body.innerHTML = '';
//        xAddEventListener(xGetElementById('editor_preview_'+editor_sequence), 'load', function(){setPreviewHeight(editor_sequence)});
    } else {
        html = contentDocument.body.innerHTML;
        textarea_obj.value = html
        html = html.replace(/<br>/ig,"<br />\n");
        html = html.replace(/<br \/>\n\n/ig,"<br />\n");
    }

    // html When using the Edit
    if(mode == 'html' && textarea_obj) {
        preview_obj.style.display='none';
        if(xGetElementById('fileUploader_'+editor_sequence)) xGetElementById('fileUploader_'+editor_sequence).style.display='block';
        textarea_obj.value = html;
        xWidth(textarea_obj, xWidth(iframe_obj.parentNode));
        xHeight(textarea_obj, xHeight(iframe_obj.parentNode));
        editorMode[editor_sequence] = 'html';

        if(xGetElementById('xeEditor_'+editor_sequence)) {
            xGetElementById('xeEditor_'+editor_sequence).className = 'xeEditor html';
            xGetElementById('use_rich_'+editor_sequence).className = '';
            xGetElementById('preview_html_'+editor_sequence).className = '';
            xGetElementById('use_html_'+editor_sequence).className = 'active';
        }
    // Preview
    } else if(mode == 'preview' && preview_obj) {
        preview_obj.style.display='';
        if(xGetElementById('fileUploader_'+editor_sequence)) xGetElementById('fileUploader_'+editor_sequence).style.display='none';

        var fo_obj = xGetElementById("preview_form");
        if(!fo_obj) {
            fo_obj = xCreateElement('form');
            fo_obj.id = "preview_form";
            fo_obj.method = "post";
            fo_obj.action = request_uri;
            fo_obj.target = "editor_preview_"+editor_sequence;
            xInnerHtml(fo_obj,'<input type="hidden" name="module" value="editor" /><input type="hidden" name="editor_sequence" value="'+editor_sequence+'" /><input type="hidden" name="act" value="dispEditorPreview" /><input type="hidden" name="content" />');
            document.body.appendChild(fo_obj);
        }
        fo_obj.content.value = html;
        fo_obj.submit();

        xWidth(preview_obj, xWidth(iframe_obj.parentNode));
        editorMode[editor_sequence] = 'preview';

        if(xGetElementById('xeEditor_'+editor_sequence)) {
            xGetElementById('xeEditor_'+editor_sequence).className = 'xeEditor preview';
            xGetElementById('use_rich_'+editor_sequence).className = '';
            xGetElementById('preview_html_'+editor_sequence).className = 'active';
            if(xGetElementById('use_html_'+editor_sequence)) xGetElementById('use_html_'+editor_sequence).className = '';
        }
    // When using WYSIWYG mode
    } else {
        preview_obj.style.display='none';
        if(xGetElementById('fileUploader_'+editor_sequence)) xGetElementById('fileUploader_'+editor_sequence).style.display='block';
        contentDocument.body.innerHTML = html;
        editorMode[editor_sequence] = null;

        if(xGetElementById('xeEditor_'+editor_sequence)) {
            xGetElementById('xeEditor_'+editor_sequence).className = 'xeEditor rich';
            xGetElementById('use_rich_'+editor_sequence).className = 'active';
            xGetElementById('preview_html_'+editor_sequence).className = '';
            if(xGetElementById('use_html_'+editor_sequence)) xGetElementById('use_html_'+editor_sequence).className = '';
        }
    }

}

// Editor Info Close
function closeEditorInfo(editor_sequence) {
    xGetElementById('editorInfo_'+editor_sequence).style.display='none';
    var expire = new Date();
    expire.setTime(expire.getTime()+ (7000 * 24 * 3600000));
    xSetCookie('EditorInfo', '1', expire);
}


function showEditorHelp(e,editor_sequence){
	jQuery('#helpList_'+editor_sequence).toggleClass('open');
}

function showEditorExtension(evt,editor_sequence){
    var oid = '#editorExtension_'+editor_sequence;
    var e = new xEvent(evt);
    if(jQuery(oid).hasClass('extension2')){
		jQuery(oid).addClass('open');

        if(e.pageX <= xWidth('editor_component_'+editor_sequence)){
			jQuery('#editor_component_'+editor_sequence).css('right','auto').css('left', 0);
        }else{
			jQuery('#editor_component_'+editor_sequence).css('right', 0).css('left', 'auto');
        }
    }else{
		jQuery(oid).attr('class', 'extension2');
    }
}

function showPreviewContent(editor_sequence) {
    if(typeof(editor_sequence)=='undefined') return;
    if(typeof(_editorFontColor[editor_sequence])=='undefined') return;
    var preview_obj = editorGetPreviewArea(editor_sequence);
    preview_obj.contentWindow.document.body.style.color = _editorFontColor[editor_sequence];
}

function setPreviewHeight(editor_sequence){
    var h = xGetElementById('editor_preview_'+editor_sequence).contentWindow.document.body.scrollHeight;
    if(h < 400) h=400;
    xHeight('editor_preview_'+editor_sequence,h+20);
}

function getAutoSavedSrl(ret_obj, response_tags, c) {
    var editor_sequence = ret_obj['editor_sequence'];
    var primary_key = ret_obj['key'];
    var fo_obj = editorGetForm(editor_sequence);

    fo_obj[primary_key].value = ret_obj['document_srl'];
    if(uploadSettingObj[editor_sequence]) editorUploadInit(uploadSettingObj[editor_sequence], true);
}
