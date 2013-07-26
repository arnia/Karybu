/**
 * @file   modules/board/js/board.js
 * @author NHN (developers@xpressengine.com)
 * @brief  board 모듈의 javascript
 **/

/* complete tp insert document */
function completeDocumentInserted(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var category_srl = ret_obj['category_srl'];

    //alert(message);

    var url;
    if(!document_srl)
    {
        url = current_url.setQuery('mid',mid).setQuery('act','');
    }
    else
    {
        url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    }
    if(category_srl) url = url.setQuery('category',category_srl);
    location.href = url;
}

/* delete the document */
function completeDeleteDocument(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var page = ret_obj['page'];

    var url = current_url.setQuery('mid',mid).setQuery('act','').setQuery('document_srl','');
    if(page) url = url.setQuery('page',page);

    //alert(message);

    location.href = url;
}

/* document search */
function completeSearch(ret_obj, response_tags, params, fo_obj) {
    fo_obj.submit();
}

function completeVote(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    alert(message);
    location.href = location.href;
}

// current page reload
function completeReload(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    location.href = location.href;
}

/* complete to insert comment*/
function completeInsertComment(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var comment_srl = ret_obj['comment_srl'];

    var url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    if(comment_srl) url = url.setQuery('rnd',comment_srl)+"#comment_"+comment_srl;

    //alert(message);

    location.href = url;
}

/* delete the comment */
function completeDeleteComment(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var page = ret_obj['page'];

    var url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    if(page) url = url.setQuery('page',page);

    //alert(message);

    location.href = url;
}

/* delete the trackback */
function completeDeleteTrackback(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var page = ret_obj['page'];

    var url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    if(page) url = url.setQuery('page',page);

    //alert(message);

    location.href = url;
}

/* change category */
function doChangeCategory() {
    var category_srl = jQuery('#board_category option:selected').val();
    location.href = decodeURI(current_url).setQuery('category',category_srl).setQuery('page', '');
}

/* scrap */
function doScrap(document_srl) {
    var params = new Array();
    params["document_srl"] = document_srl;
    exec_xml("member","procMemberScrapDocument", params, null);
}

/* quote */
function quote(comment_srl){
    author = document.getElementById("author_"+comment_srl);
    content = document.getElementById("content_"+comment_srl);
    var quote = '[quote name="'+author.textContent+'"]'+content.textContent+"[/quote]";
    var textareaList = document.all.tags("textarea");
    if(typeof(CKEDITOR) != "undefined") {
        CKEDITOR.instances[textareaList[0].id].insertText(quote);
    }
    if(typeof(tinyMCE) != "undefined"){
        value = tinyMCE.get(textareaList[0].id).getContent() + quote;
        tinyMCE.get(textareaList[0].id).setContent(quote);
    }
}

/* reply */
function reply(comment_srl,quote){
    if(quote == 'Y'){
        author = document.getElementById("author_"+comment_srl);
        content = document.getElementById("content_"+comment_srl);
        var quote_text = '[quote name="'+author.textContent+'"]'+content.textContent+"[/quote]";
    } else quote_text = '';

    li = document.getElementById("comment_"+comment_srl);


    parent = document.getElementsByName("parent_srl");
    parent[0].value = comment_srl;

    cancelLink = document.getElementById("cancel_reply");
    cancelLink.style.display = "";


    var textareaList = document.all.tags("textarea");

    if(typeof(CKEDITOR) != "undefined") {
        config = CKEDITOR.instances[textareaList[0].id].config;
        CKEDITOR.instances[textareaList[0].id].destroy();
        editor = document.getElementById("write_comment");
        li.appendChild(editor);
        CKEDITOR.replace(textareaList[0].id,config);
        setTimeout(function(){ CKEDITOR.instances[textareaList[0].id].setData(quote_text);},300);

    }

    if(typeof(tinyMCE) != "undefined"){
        editor = document.getElementById("write_comment");
        li.appendChild(editor);
        if (tinyMCE.getInstanceById(textareaList[0].id))
        {
            tinyMCE.execCommand('mceFocus', false, textareaList[0].id);
            tinyMCE.execCommand('mceRemoveControl', false, textareaList[0].id);
        }
        tinyMCE.execCommand('mceAddControl', false, textareaList[0].id);
        tinyMCE.get(textareaList[0].id).setContent(quote_text);
    }
}

/* cancel reply and replace the editor at the bottom of the comment list */
function cancelReply(){
    cancelLink = document.getElementById("cancel_reply");
    cancelLink.style.display = "none";

    ul = document.getElementById("comment_list");


    var textareaList = document.all.tags("textarea");

    if(typeof(CKEDITOR) != "undefined") {
        config = CKEDITOR.instances[textareaList[0].id].config;
        CKEDITOR.instances[textareaList[0].id].destroy();
        editor = document.getElementById("write_comment");
        ul.appendChild(editor);
        CKEDITOR.replace(textareaList[0].id,config);
        setTimeout(function(){ CKEDITOR.instances[textareaList[0].id].setData("");},300);

    }

    if(typeof(tinyMCE) != "undefined"){
        editor = document.getElementById("write_comment");
        ul.appendChild(editor);
        if (tinyMCE.getInstanceById(textareaList[0].id))
        {
            tinyMCE.execCommand('mceFocus', false, textareaList[0].id);
            tinyMCE.execCommand('mceRemoveControl', false, textareaList[0].id);
        }
        tinyMCE.execCommand('mceAddControl', false, textareaList[0].id);
        tinyMCE.get(textareaList[0].id).setContent("");
    }
}


jQuery(function($){
	$(document.body).click(function(e){
		var t=$(e.target),act,params={};

		if(t.parents('.layer_voted_member').length==0 && !t.is('.layer_voted_member')){
			$('.layer_voted_member').hide().remove();
		}

		if(!t.is('a[class^=voted_member_]')) return;

		var srl = parseInt(t.attr('class').replace(/[^0-9]/g,''));
		if(!srl) return;

		if(t.hasClass('comment')){
			act = 'comment.getCommentVotedMemberList';
			params = {'comment_srl':srl,'point':(t.hasClass('votedup')?1:-1)};
		}else{
			act = 'document.getDocumentVotedMemberList';
			params = {'document_srl':srl,'point':(t.hasClass('votedup')?1:-1)};
		}

		$.exec_json(act, params, function(data){
				var l = data.voted_member_list;
				var ul = [];

				if(!l || l.length==0) return;
				
				$.each(l,function(){
					ul.push(this.nick_name);
				});
				
				t.after($('<ul>')
					.addClass('layer_voted_member')
					.css({'position':'absolute','top':e.pageY+5,'left':e.pageX})
					.append('<li>'+ul.join('</li><li>')+'</li>'));
			});
	});
});

