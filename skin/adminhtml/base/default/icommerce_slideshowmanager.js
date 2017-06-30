(function($){

	$(document).ready(function(){
        updateHotspotsConfig();

		$('.hotspot-image img').on('click', function(e){
			var imagePosition = $(this).offset();
			var x = (e.offsetX === undefined ? e.originalEvent.layerX : e.offsetX) - 7;
			var y = (e.offsetY === undefined ? e.originalEvent.layerY : e.offsetY) - 7;

			var xPercentage = ((100 * x) / $(this).parents(".hotspot-image").width()).toFixed(2);
			var yPercentage = ((100 * y) / $(this).parents(".hotspot-image").height()).toFixed(2);

			var i = $(".hotspot-image .hotspot").length;
			while ($('.hotspot_'+i).length > 0){
				i++;
			}
			var editBox = '<div class="editbox hotspot_'+i+'">' +
								'<div class="entry-edit-head"><h4>Edit Hotspot : '+i+'</h4></div>' +
								'<div class="data">' +
									'<p>Product ID</p><input type="text" id="hotspot_product_id" name="hotspot_product_id" value="" />' +
									'<p>CMS Block ID</p><input type="text" id="hotspot_cms_id" name="hotspot_cms_id" value="" />' +
									'<div class="form-button hotspot-button save" data-hotspot="hotspot_'+i+'"><span>Save</span></div>' +
									'<div class="form-button hotspot-button delete" data-hotspot="hotspot_'+i+'"><span>Delete</span></div>' +
									'<input type="hidden" id="hotspot_'+i+'" class="hotspot-input hotspot_'+i+'" data-yoffset="'+yPercentage+'" data-xoffset="'+xPercentage+'" value="" />' +
								'</div>' +
							'</div>';
			var hotspotStyle = "top: " + yPercentage + "%; left: " + xPercentage + "%; display: none;";
			var newHotspot = '<div class="hotspot hotspot_'+i+'" style="'+hotspotStyle+'"> ' +
								'<div class="hotspot-icon">+</div>' + editBox + ' </div>';

			$(this).parent(".hotspot-image").append(newHotspot);
			$(".hotspot .editbox").slideUp("700");
			$(".hotspot_"+i).fadeIn("200");

			bindHotspots();
			bindHotspotIcons();
			bindHotspotButtons();
			bindInputs();
		});

		//Bindings in case we edit a slide that already has htspots
		bindHotspots();
		bindHotspotIcons();
		bindHotspotButtons();
		bindInputs();
	});

	bindInputs = function(){
		$(".hotspot input").on("focus", function(){
			$(this).parents('.data').find(".error").remove();
		});
	}

	bindHotspots = function(){
		$(".hotspot").off('click');
		$(".hotspot").on('click', function(e){
			e.stopPropagation();
		});
	};

	bindHotspotIcons = function(){
		$(".hotspot-icon").off('click');
		$(".hotspot-icon").on('click', function(e){
			e.stopPropagation();
			if ($(this).siblings('.editbox').css("display") == "block") {
				$(".hotspot .editbox").slideUp("700");
			} else {
				//Empty values if nothing has been saved
				if ($(this).siblings('.editbox').find(".hotstop-input").val() == ""){
					$(this).siblings('.editbox').find("input[type='text']").val("");
				}
				$(".hotspot .editbox").slideUp("700");
				$(this).siblings('.editbox').slideDown('400');
			}
		});
	};

	bindHotspotButtons = function(){
		$('.hotspot-button').off("click");

		$('.hotspot-button.delete').on("click", function(e){
			e.stopPropagation();
			$('.' + $(this).data('hotspot')).remove();

            updateHotspotsConfig();
		});

		$('.hotspot-button.save').on("click", function(e){
			e.stopPropagation();
			//Check values
			$productId = $('.' + $(this).data('hotspot')).find('#hotspot_product_id').val();
			$cmsId = $('.' + $(this).data('hotspot')).find('#hotspot_cms_id').val();

			if (!$productId && !$cmsId){
				$(this).parents('.data').find(".error").remove();
				$(this).parents(".data").append("<p class='error'>* Please fill in a product or cms id.</p>");
			} else if ($productId) {
				$(this).parents('.data').find(".hotspot-input").val($productId).data("type", "product");
				$('.' + $(this).data('hotspot')).find('#hotspot_cms_id').val("");
			} else {
				$(this).parents('.data').find(".hotspot-input").val($cmsId).data("type", "cms");
			}

			//Fill in the actual value
            updateHotspotsConfig();

			$(".hotspot .editbox").slideUp("400");
		});
	};

    function setHotspotsConfig(config) {
        config = config || '';
        $('#hotspots_master').val(config);
    }

    function updateHotspotsConfig() {
        var hotspotsData = '';

        $('.hotspot-input').each(function() {
           var $this = $(this),
               id = $this.attr("id"),
               xoffset = $this.data("xoffset"),
               yoffset = $this.data("yoffset"),
               type = $this.data("type"),
               val = $this.val(),
               type = $this.data('type');

            if(!val) {
                return;
            }

            hotspotsData += '{"id": "' + id + '", "xoffset": "' + xoffset + '", "yoffset": "' + yoffset + '", "type": "' + type + '", "value": "' + val + '"};';
        });

        setHotspotsConfig(hotspotsData);
    }

})(jQuery);