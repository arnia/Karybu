if(jQuery)
jQuery(function($){
	var ctn = $('#container');
	var box = $('<div style="position:absolute;top:0;left:0;width:100%;height:100%;margin:0"></div>').appendTo(document.body);
	
	// Reduce the size of the window after one
	window.resizeTo(620,400);
	
	var dw = 620 - ctn.get(0).offsetWidth;
	var dh = 400 - box.get(0).offsetHeight;

	// Window resize
	window.resizeBy(dw, dh);
	
	// Eliminate box
	box.remove();
	
	// Menu
	$('#navigation a').click(function(){
		var type = $(this).parent().attr('class');
		var Type = type.substring(0,1).toUpperCase()+type.substring(1);
		
		$('#body').attr('class', 'open'+Type);
		$('#navigation').attr('class', 'active'+Type);
		
		return false;
	});
	
	// Image Resize
	$('#img button > img').each(function(){
		var img = $(this);
		$('<img>').load(function(){
			var w = this.width;
			var h = this.height;
			
			if (w < 100 && h < 100) {
				img.attr('width', w);
				img.attr('height', h);
			} else if (w > h) {
				img.attr('width', 100);
				img.attr('height', Math.floor(100*h/w));
			} else {
				img.attr('width', Math.floor(100*w/h));
				img.attr('height', 100);
			}
			if(w>100&&h>100){	
				img.show();
			}else{
				img.parent().parent().remove();
			}
		}).attr('src', this.src);
	});
	
	// Select an image
	$('#img button').click(function(){
		this.form.elements['image'].value = $('img', this).attr('src');
		$(this).parent().parent().find('button.active').removeClass('active');
		$(this).addClass('active');
	});
	
	// Comment
	$('input[type=text],textarea')
		.focus(function(){
			var t = $(this);
			if (t.attr('title') == t.val()) t.val('');
		})
		.blur(function(){
			var t = $(this);
			if ($.trim(t.val()) == '') t.val(t.attr('title'));
		})
		.blur();
});




function completeInsertMaterial(ret_obj){
    var error = ret_obj['error'];
    var message = ret_obj['message'];
	window.close();
	
}

function insertMaterial(obj,filter){
	jQuery('input[type=text],textarea').each(function(){
		var t = jQuery(this);
		if(t && t.attr('title') == t.val()) t.val('');
	});
	var c = jQuery('<div>');
	var type = jQuery('input[name=type]',obj).val();
	var from = jQuery('input[name=from]',obj).val();
	var p = jQuery('.p',obj).val();
	switch(type){
		default:
		case 'txt':
			if(p) jQuery('<p>'+p+from+'</p>').appendTo(c);
		break;
		case 'img':
			var b = '<p><img src="'+jQuery('input.pimg',obj).val()+ '" alt="" /></p>';
			if(p) b+= '<p class="desc">'+p+'<p>';
			b+= '<p class="cite">'+from+'</p>';
			c.append(b);
		break;
		case 'link':
			var b='<p>';
			var pstrong = jQuery('input.pstrong',obj).val();
			var pa = jQuery('input.pa',obj).val();
			if(pstrong) b += '<strong>'+pstrong+'</strong>';
			if(pa) b += '<a href="'+pa+'">'+pa+'</a>';
			b+='</p>';
			if(p) b += '<p>'+p+'</p>';
			if(pa && pstrong) c.html(b);
		break;
		case 'mov':
			jQuery(jQuery('.null',obj).val()).appendTo(c);
			if(p) jQuery('<p class="desc">'+p+'</p>').appendTo(c);
			jQuery('<p class="cite">'+from+'</p>').appendTo(c);
		break;
		case 'blockquote':
			var b = jQuery('<blockquote>');
			var blockquotep = jQuery('.blockquotep',obj).val();
			if(blockquotep) jQuery('<p>'+blockquotep+'</p>').appendTo(b);
			jQuery(from).appendTo(b);
			c.append(b);
			if(!blockquotep) c.html('');
		break;
	}
	if(c.html()) jQuery('input[name=content]',obj).val('<div class="eArea xe_dr_'+type+'">'+c.html()+'</div>');
	return procFilter(obj,filter);
}

