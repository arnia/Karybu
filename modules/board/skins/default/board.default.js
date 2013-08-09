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
                    if($('input[name=document_srl]').val() == ''){
                        $('input[name=document_srl]').val(data.document_srl);
                    }
                    if($(".fileList option")[0].value == ''){
                        $(".fileList option")[0].remove();
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
        console.log(item)


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
