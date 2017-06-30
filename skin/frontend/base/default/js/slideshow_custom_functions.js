(function($) {
	
	$.fn.slideshowCustomFunctions = function() {
		if($("#textbox-wrapper").length == 1){
			$("#textbox-wrapper").centerTextboxWrapper();
		}
	}
	
	$.fn.centerTextboxWrapper = function() {
		if(this.hasClass('center')){
			this.css('margin-left','-'+(this.width()/2)+'px');
		}
	}

})(jQuery);