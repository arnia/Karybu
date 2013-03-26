//Start editor
function editorStart_xe(editor_sequence, primary_key, content_key){
    console.log("editor start");
    editorRelKeys[editor_sequence] = new Array();
    editorRelKeys[editor_sequence]["primary"]   = document.getElementsByName(primary_key)[0];
    editorRelKeys[editor_sequence]["content"]   = document.getElementsByName(content_key)[0];
    editorRelKeys[editor_sequence]["func"]	  = editorGetContentTextarea_xe;
    editorRelKeys[editor_sequence]["pasteHTML"] = function(text){
        CKEDITOR.instances.ckeditor_instance.insertHtml(text);
    }
}

//Get content from editor
function editorGetContentTextarea_xe(editor_sequence){
    return CKEDITOR.instances.ckeditor_instanceinstance.getData();
}

function editorGetContent(editor_sequence) {
    if(!CKEDITOR.instances.ckeditor_instance) return CKEDITOR.instances[editor_sequence.id].getData()
    else return CKEDITOR.instances.ckeditor_instance.getData()
}

//Replace html content to editor
function editorReplaceHTML(iframe_obj, content) {
    CKEDITOR.instances.ckeditor_instance.insertHtml(content);
}

