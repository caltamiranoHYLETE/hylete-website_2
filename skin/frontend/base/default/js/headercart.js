jQuery(function($){

	$("body").on({
		mouseenter: function(){
            if($('.showcart').is(':visible') == false){
                $(".showcart").stop(true,true).slideDown("fast");
                $("#search_mini_form .button").css("visibility","hidden"); // ie7 workaround
            }

		}, mouseleave: function(){
			$(".showcart").stop(true,true).slideUp("fast");
			$("#search_mini_form .button").css("visibility","visible"); // ie7 workaround
		}
	}, "#headercart");

});