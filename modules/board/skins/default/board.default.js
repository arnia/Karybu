jQuery(function($){
    //autocomplete search other attachments
    $( "#search_attachments" )
        .autocomplete({
            minLength: 1,
            source: function( request, response ) {
                var params ={
                    "search":request.term
                    ,"module_instance":$('input[name=mid]').val()
                };
                $("#search_attachments").addClass( "ui-autocomplete-loading" );
                $.exec_json('document.getDocumentsFilesJson', params, function(data){
                    $("#search_attachments").removeClass( "ui-autocomplete-loading" );
                    response( data.files );
                });


            },
            focus: function(event, ui) {
                $( "#search_attachments" ).val( ui.item.source_filename );
                return false;
            },
            select: function( event, ui ) {
                $( "#search_attachments" ).val( ui.item.source_filename );
                var params = {
                    file_srl: ui.item.file_srl,
                    document_srl: $('input[name=document_srl]').val()
                };
                $.exec_json('document.procInsertForeignFile', params, function(data){
                    var $input = $('input[name=document_srl]');
                    if ($input.val() == ''){
                        $input.val(data.document_srl);
                    }
                    var $fileList = $('.fileList option');
                    if($fileList[0].value == ''){
                        $fileList[0].remove();
                    }
                    $(".fileList").append('<option selected="true" value='+data.file.file_srl+'>'+ data.file.source_filename +'</option>');

                    data.file.previewAreaID = $('.preview').attr('id');
                    uploadedFiles[data.file.file_srl] = data.file;
                    previewFiles(null,data.file.file_srl);
                })
                return false;

            }
        })
        .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
            return $( "<li>" )
                .append( "<a>" + item.source_filename + "<img class='thumb' src='" + item.uploaded_filename + "'></a>" )
                .appendTo( ul );
        };


    // delete the border for the last row
	$('.board_list tr:last-child>td').css('border','0');
	// hide last tag
	$('.read_footer .tags span:last-child').hide();
	// display/hide serach box
	var bs = $('.board_search');
	bs.hide().addClass('off');
	$('.bsToggle').click(function(){
		if(bs.hasClass('off')){
			bs.show().removeClass('off').find('.iText').focus();
		} else {
			bs.hide().addClass('off');
		};
	});
	// user input text blur/focus/change
	var iText = $('.item .iLabel').siblings('.iText');
	$('.item .iLabel').css('position','absolute');
	iText
		.focus(function(){
			$(this).siblings('.iLabel').css('visibility','hidden');
		})
		.blur(function(){
			if($(this).val() == ''){
				$(this).siblings('.iLabel').css('visibility','visible');
			} else {
				$(this).siblings('.iLabel').css('visibility','hidden');
			}
		})
		.change(function(){
			if($(this).val() == ''){
				$(this).siblings('.iLabel').css('visibility','visible');
			} else {
				$(this).siblings('.iLabel').css('visibility','hidden');
			}
		})
		.blur();
	// add class to the parent category 
	$('.cTab>li>ul>li.on_').parents('li:first').addClass('on');
	// delete the margin-top for the first child of the ccomments
	$('.feedback .xe_content>*:first-child').css('margin-top','0');
});

// SNS post
(function($){
	$.fn.snspost = function(opts) {
		var loc = '';
		opts = $.extend({}, {type:'twitter', event:'click', content:''}, opts);
		opts.content = encodeURIComponent(opts.content);
		switch(opts.type) {
			case 'me2day':
				loc = 'http://me2day.net/posts/new?new_post[body]='+opts.content;
				if (opts.tag) loc += '&new_post[tags]='+encodeURIComponent(opts.tag);
				break;
			case 'facebook':
				loc = 'http://www.facebook.com/share.php?t='+opts.content+'&u='+encodeURIComponent(opts.url||location.href);
				break;
			case 'delicious':
				loc = 'http://www.delicious.com/save?v=5&noui&jump=close&url='+encodeURIComponent(opts.url||location.href)+'&title='+opts.content;
				break;
			case 'twitter':
			default:
				loc = 'http://twitter.com/home?status='+opts.content;
				break;
		}
		this.bind(opts.event, function(){
			window.open(loc);
			return false;
		});
	};
	$.snspost = function(selectors, action) {
		$.each(selectors, function(key,val) {
			$(val).snspost( $.extend({}, action, {type:key}) );
		});
	};
})(jQuery);


/**
 * related documents
 */

(function( $ ) {

$.fn.appendKDocument = function(doc, type, limit) {
    return this.each(function() {
        if ($(this).is('ul')) {

            var count = 0;
            var exists = false;
            $(this)
                .children('li').each(function() {
                    if ($(this).data('srl') == doc.document_srl) {
                        exists = true;
                    }
                    count++;
                });

            var el = '<li data-srl="' + doc.document_srl + '" data-type="' + type + '">' +
                '<input type="hidden" checked name="related[]" id="related' + doc.document_srl + '" value="' + doc.document_srl + '">' +
                '<span class="related_content">' + doc.title + '</span>' +
                '<span class="delete_related">x</span>' +
                '</li>';

            if (type == 'auto') {
                if (!exists) {
                    $(this).append(el);
                    count++;
                }
            }
            else {
                if (!exists) {
                    $(this).prepend(el);
                    count++;
                }
                else {
                    var li = $('li[data-srl=' + doc.document_srl + ']', $(this));
                    li.attr('data-type', 'manual');
                    $(this).prepend(li);
                }
            }

            if (count > limit) {
                $('li:last', $(this)).remove();
                count--;
            }

        }
    });
};

}( jQuery ));

jQuery(function($) {

    $('#related')
        .autocomplete({
            minLength: 1,
            source: function( request, response ) {
                $.exec_json('document.getDocumentsRelated', {
                    title: request.term
                }, function(data){
                    response( data.docs );
                });
            },
            focus: function(event, ui) {
                $( "#related" ).val( ui.item.title );
                return false;
            },
            select: function( event, ui ) {
                $( "#related" ).val( ui.item.title );
                var $ul = $('ul#related_articles');
                $ul.appendKDocument(ui.item, 'manual', $ul.data('max'));
                $(event.target).val('');
                return false;
            }
        }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
            var tmp = $('<div>').html(item.content);
            var content = tmp.text().substr(0, 200);

            var re = new RegExp($('#related').val(), 'gi');
            var bTitle = item.title.replace(re, '<strong>$&</strong>');

            return $( "<li>" )
                .append( "<a>" + bTitle + "<br>" + content + "</a>" )
                .appendTo( ul );
        };
    $('.delete_related').live('click', function(){
        $(this).parent().fadeOut(function(){
            $(this).remove();
        });
    });

    setInterval(function() {
        var title = $('div.write_header input[name=title]');
        if (typeof(CKEDITOR) != "undefined") {
            var content = CKEDITOR.instances[jQuery('.write_editor .ckeditor:first > textarea:first').attr('id')].getData();
        }
        else {
            var content = tinyMCE.activeEditor.getContent();
        }
        var tags = $('#tags');

        var changedTitle = title.val() != title.data('lastTitle');
        var changedContent = content != title.data('lastContent');
        var changedTags = tags.val() != title.data('lastTags');

        if (changedTitle) {
            title.data('lastTitle', title.val());
        }
        if (changedContent) {
            title.data('lastContent', content);
        }
        if (changedTags) {
            title.data('lastTags', tags.val());
        }
        if (changedTitle || changedContent || changedTags) {
            $('ul#related_articles li:[data-type=auto]').remove();
        }

        if ((title.val().length && changedTitle) || (content.length && changedContent) || (tags.val().length && changedTags)) {
            var manuals = $('ul#related_articles li:[data-type=manual]').length;
            $.exec_json('document.getDocumentsRelated', {
                list_count: 3 - manuals,
                title: title.val(),
                content: $('<div>' + content + '</div>').text(),
                tags: tags.val()
            }, function(data) {
                $.each(data.docs, function(i, doc) {
                    var $ul = $('ul#related_articles');
                    $ul.appendKDocument(doc, 'auto', $ul.data('max'));
                });
            });
        }
    }, 2000);
});
