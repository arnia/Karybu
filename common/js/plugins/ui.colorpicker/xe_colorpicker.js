/**
 * @brief XE Colorpicker
 * @author Arnia (dev@karybu.org)
 **/
jQuery(function($){

    $.fn.xe_colorpicker = function(settings){
		return this.jPicker(settings);
    }

    $('input.color-indicator').xe_colorpicker();
});
