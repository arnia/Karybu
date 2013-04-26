(function($){
	$('#allow_outlink').on('change', function(){
        var $outLink = $('._outLink');
        if(this.checked) $outLink.slideDown();
        else $outLink.slideUp();
	});
 }(jQuery));