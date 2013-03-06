//Start editor
function editorStart_xe(editor_sequence, primary_key, content_key){
    console.log("editor start");
    editorRelKeys[editor_sequence] = new Array();
    editorRelKeys[editor_sequence]["primary"]   = document.getElementsByName(primary_key)[0];
    editorRelKeys[editor_sequence]["content"]   = document.getElementsByName(content_key)[0];
    editorRelKeys[editor_sequence]["func"]	  = editorGetContentTextarea_xe;
    editorRelKeys[editor_sequence]["pasteHTML"] = function(text){
            tinyMCE.activeEditor.execCommand('mceInsertContent',false,text);
    }
}

//Get content from editor
function editorGetContentTextarea_xe(editor_sequence){
    return tinyMCE.activeEditor.getContent();
}

function editorGetContent(editor_sequence) {
    return tinyMCE.activeEditor.getContent();
}

//Replace html content to editor
function editorReplaceHTML(iframe_obj, content) {
    tinyMCE.activeEditor.execCommand('mceInsertContent',false,content);
}

