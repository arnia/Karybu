//Start editor
function editorStart_xe(editor_sequence, primary_key, content_key){
    console.log("editor start");
    var textarea = jQuery("#xpress-editor-"+editor_sequence);
    var form	 = textarea['context'].forms[1];
    form.setAttribute('editor_sequence', editor_sequence);
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
    if(!CKEDITOR.instances.ckeditor_instance) {
        if(!CKEDITOR.instances[editor_sequence.id]) return CKEDITOR.instances["ckeditor_instance_"+editor_sequence].getData();
        else return CKEDITOR.instances[editor_sequence.id].getData();
    }
    else return CKEDITOR.instances.ckeditor_instance.getData()
}

//Replace html content to editor
function editorReplaceHTML(iframe_obj, content) {
    CKEDITOR.instances.ckeditor_instance.insertHtml(content);
}

