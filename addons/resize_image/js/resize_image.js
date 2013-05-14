/**
 * @brief Changed so that the image is larger than the top area of the screen, when clicked, show him the original and resize
 **/
(function($){

var xScreen = null;

// Or create a function that returns a black screen for a slide.
function getScreen() {
	var body    = $(document.body);
	var controls, imgframe, closebtn, prevbtn, nextbtn;

	// If screen.
	if (!xScreen) {
		// Black screen
		xScreen = $("<div>")
			.attr("id","xe_gallery_screen")
			.css({
				position:"absolute",
				display:"none",
				backgroundColor:"black",
				zIndex:500,
				opacity:0.5
			});

		// Dealing with the control button shows the image layer
		controls = $("<div>")
			.attr("id","xe_gallery_controls")
			.css({
				position:"absolute",
				display:"none",
				overflow:"hidden",
				zIndex:510
			});

		// Close button
		closebtn = $("<img>")
			.attr("id", "xe_gallery_closebtn")
			.attr("src", request_uri+"addons/resize_image/iconClose.png")
			.css({
				top : "10px"
			})
			.click(function(){xScreen.xeHide()})
			.appendTo(controls);

		// Previous button
		prevbtn = $("<img>")
			.attr("id", "xe_gallery_prevbtn")
			.attr("src", request_uri+"addons/resize_image/iconLeft.png")
			.css("left","10px")
			.click(function(){xScreen.xePrev()})
			.appendTo(controls);

		// Next button
		nextbtn = $("<img>")
			.attr("id", "xe_gallery_nextbtn")
			.attr("src", request_uri+"addons/resize_image/iconRight.png")
			.css("right","10px")
			.click(function(){xScreen.xeNext()})
			.appendTo(controls);

		// Button on the Common Properties
		controls.find("img")
			.attr({
				width  : 60,
				height : 60,
				className : "iePngFix"
			})
			.css({
				position : "absolute",
				width : "60px",
				height : "60px",
				zIndex : 530,
				cursor : "pointer"
			});

		// Image holder
		imgframe = $("<img>")
			.attr("id", "xe_gallery_holder")
			.css("border", "7px solid white")
			.css("zIndex", 520)
			.appendTo(controls).draggable();

		body.append(xScreen).append(controls);

		// xScreen Extended objects.
		xScreen.xeShow = function() {
			var clientWidth  = $(window).width();
			var clientHeight = $(window).height();

			$("#xe_gallery_controls,#xe_gallery_screen").css({
				display:"block",
				width  : $(document).width() + "px",
				height : $(document).height() + "px",
				left   : 0,
				top    : 0
				//width  : clientWidth + "px",
				//height : clientHeight + "px",
				// left   : $(document).scrollLeft(),
				// top    : $(document).scrollTop()
			});

			closebtn.css("left", Math.round((clientWidth-60)/2) + "px");

			$("#xe_gallery_prevbtn,#xe_gallery_nextbtn").css("top", Math.round( (clientHeight-60)/2 ) + "px");

			this.xeMove(0);
		};
		xScreen.xeHide = function(event) {
			xScreen.css("display","none");
			controls.css("display","none");
		};
		xScreen.xePrev = function() {
			this.xeMove(-1);
		};
		xScreen.xeNext = function() {
			this.xeMove(1);
		};
		xScreen.xeMove = function(val) {
			var clientWidth  = $(window).width();
			var clientHeight = $(window).height();

			this.index += val;

			prevbtn.css("visibility", (this.index>0)?"visible":"hidden");
			nextbtn.css("visibility", (this.index<this.list.size()-1)?"visible":"hidden");

            //textyle Resize the image processing
            var src = this.list.eq(this.index).attr("rawsrc");
            if(!src) src = this.list.eq(this.index).attr("src");

			imgframe.attr("src", src).css({
				left : Math.round( Math.max( parseInt($(document).scrollLeft()) + (clientWidth-imgframe.width()-14)/2, 0 ) ) + "px",
				top  : Math.round( Math.max( parseInt($(document).scrollTop()) + (clientHeight-imgframe.height()-14)/2, 0 ) ) + "px"
			});

			closebtn.css({
				left : Math.round( Math.max( parseInt($(document).scrollLeft()) + (clientWidth-closebtn.width())/2, 0 ) ) + "px",
				top  : Math.round( Math.max( parseInt($(document).scrollTop()) + 10, 0 ) ) + "px"
			});
		};

		// Situation of closing the screen
		$(document).keydown(xScreen.xeHide);
	} else {
		controls = $("#xe_gallery_controls");
		imgframe = $("#xe_gallery_holder");
		closebtn = $("#xe_gallery_closebtn");
		prevbtn  = $("#xe_gallery_prevbtn");
		nextbtn  = $("#xe_gallery_nextbtn");
	}

	return xScreen;
}

// The image to see a slide function
function slideshow(event) {
	var container  = $(this).closest('.xe_content');
	var imglist    = container.find("img[rel=xe_gallery]");
	var currentIdx = $.inArray($(this).get(0), imglist.get());
	var xScreen    = getScreen();

	// Screen shows
	xScreen.list  = imglist;
	xScreen.index = currentIdx;
	xScreen.xeShow();
}

/* DOM READY */
$(function() {
	var regx_skip = /(?:(modules|addons|classes|common|layouts|libs|widgets|widgetstyles)\/)/i;
	var regx_allow_i6pngfix = /(?:common\/tpl\/images\/blank\.gif$)/i;
	/**
	 * Object to save the body width.
	 * IE6 If the image more than the width of the body in its size, which is saved as a problem for bypass
	 **/
	var dummy = $('<div style="height:1; overflow:hidden; opacity:0; display:block; clear:both;"></div>');

	/**
	 * Resize execution function
	 **/
	function doResize(contentWidth, count) {
		// A maximum number of retry
		if(!count) count = 0;
		if(count >= 10) return;

		var $img = this;
		var beforSize = {'width':$img.width(), 'height':$img.height()};

		// Retry when you can not save the image size.
		if(!beforSize.width || !beforSize.height) {
			setTimeout(function() {
				doResize.call($img, contentWidth, ++count)
			}, 200);
			return;
		}

		// If no need to resize then return
		if(beforSize.width <= contentWidth) return;

		var resize_ratio = contentWidth / beforSize.width;

		$img
			.removeAttr('width').removeAttr('height')
			.css({
				'width':contentWidth,
				'height':parseInt(beforSize.height * resize_ratio, 10)
			});
	}

	$('div.xe_content').each(function() {
		var contentWidth = dummy.appendTo(this).width();
		dummy.remove();
		if(!contentWidth) return;

		$('img', this).each(function() {
			var $img = $(this);
			var imgSrc = $img.attr('src');
			if(regx_skip.test(imgSrc) && !regx_allow_i6pngfix.test(imgSrc)) return;

			$img.attr('rel', 'xe_gallery');

			doResize.call($img, contentWidth);
		});

		/* live Events apply (image_gallery components and compatible to), */
		$('img[rel=xe_gallery]', this).live('mouseover', function() {
			var $img = $(this);
			if(!$img.parent('a').length && !$img.attr('onclick')) {
				$img.css('cursor', 'pointer').click(slideshow);
			}
		});
	});
});

})(jQuery);
