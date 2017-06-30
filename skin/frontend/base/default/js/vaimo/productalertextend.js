(function($){
	
	$(document).ready(function(){
		
		$('#notify_popup').css('width', $("#product_addtocart_form").width());
		$('#notify_popup').css('height', 'auto');//css('height', $("#product_addtocart_form").height()+10*1);
		
		$('#notify_me_stock').on('click', function(){
			$('#notify_popup').slideToggle();
		});
		
		    	
    	$('#notify_data.configurable').each(function(){
        	var lookupMalformedJSON = $('.product-data-mine-for-notification').data('lookup');
        	var JSONLookup = lookupMalformedJSON.replace(/'/g, '\"');
        	var optionsArray = {
        		attributeIds : $('.product-data-mine-for-notification').data('attributeids'),
        		confProductId : $('.product-data-mine-for-notification').data('confproductid'),
        		messageMoreOptions : $('.product-data-mine-for-notification').data('messagemoreoptions'),
        		lookup : $.parseJSON(JSONLookup)
        	};
        	$(this).vaimoJpiFrontendMini(optionsArray);        	
    	});
    	
    	$('#notify_data input').on('focusout', function(){
    		if (notifyStockForm.validator.validate()){
    			$('#notify_data button.submit').prop('disabled', '');
    		} else {
    			$('#notify_data button.submit').prop('disabled', 'disabled');
    		}
    	});
    	
    	$('#notify_data button.submit').on('click',function(){
    		var url = $(this).parents('form').data('ajaxurl');
    		var mail = $(this).siblings('#customer_mail').val();
    		var pid = $(this).siblings('#product_id').val();
    		$.ajax({
    			type: 'POST',
    			url : url,
    			data : { 'email' : mail, 'product_id' : pid },
    		}).done(function(response) {
    		    var decodedResponse = $.parseJSON(response);
    		    if (decodedResponse['status'] == 1) {
    		    	$('#notify_me_stock').click().html(decodedResponse['message']).css('color','green').addClass('no-link-styling').off('click');
    		    } else {
    		    	$('#notify_me_stock').html(decodedResponse['message']);
    		    }
    		}).fail(function(response) {
    		    alert( "error" );
    		});
    	});
			
	});
	
})(jQuery);

/*************** Vaimo.JpiFrontend ***************/
/*
 * Developed by Vaimo Sweden AB @ 2012
 * 
 * Notes:
 * All the selectors used must have the jQuery syntax.
 * The default selectors correspond to the "Vaimo Imitate" theme. Other themes may require the changing of the selectors.
 * 
 * More info: wiki.vaimo.com/docu/wiki
 *
 */

(function($){
	
	//"use strict";
	
	$.fn.vaimoJpiFrontendMini = function(options, customFunctions, customBinders){
		
		//Private variables
		var thisProductContainer = this;
		var optionElementToLoad = null;
		var lookupLastPos;
	    var lookupLastPos_ix;
	    var gallery;
	    
		var settings = $.extend({
			attributeIds : null,
            msg_more_options : null,
            confProductId : null,
    		lookup : null,
            selectedItemInfo : null,
            qty_sel : 0
		}, options || {});
		
		var functions = $.extend({
				        
			hasStockBelow : function(lut, ix) {
	            // Are we at bottom level ?
	            if (typeof lut["qty"] != "undefined" && typeof lut["qty"] != "object") {
	                return lut["qty"];
	            }
	            
	            if (ix < settings.attributeIds.length){
	                // Not at bottom, we need positive result from at least one below
	                for( var optId in lut ){
	                    if (lut.hasOwnProperty(optId)){
	                        var qtyArray = this.hasStockBelow(lut[optId], ix+1);
	                        if( qtyArray ) { return qtyArray; }
	                    }
	                }
	            }
	            
	            // None had stock below, return false
	            return false;
	        },
	        
	        lookupFirstBelow : function(lut, key, ix) {
	            // Are we at bottom level ?
	            if (typeof lut[key] !="undefined" && lut[key]) {
	                return lut[key];
	            }
	            
	            // Not at bottom, keep looking deeper
	            for (var optId in lut) {
	                if (ix+1 > settings.attributeIds.length) { break };
	                if (lut.hasOwnProperty(optId)) {
	                    var r = this.lookupFirstBelow(lut[optId], key, ix+1);
	                    if (r) { return r; }
	                }
	            }
	            
	            // None had key defined below (or it was empty)
	            return false;
	        },
	        
			getOptionsStockInfo : function(lut, ix) {
	            // Check each option, if it has stock below
	            qtyArray = {};
	            for( var optId in lut ){
	                if( lut.hasOwnProperty(optId) ){
	                	qtyArray[optId] = this.hasStockBelow(lut[optId], ix+1);
	                }
	            }
	            return qtyArray;
	        },
	        
	        hasAllChildrenInStock : function (oid2) {
	        	lut = settings.lookup;
	        	if (typeof lut[oid2] == "object" && typeof lut[oid2].stock_status === "undefined") {
	        		for (var simple in lut[oid2]) {
	        			if (typeof lut[oid2][simple].stock_status !== "undefined" && lut[oid2][simple].stock_status == 0) {
	        				return false;
	        			}
	        		}
	        	}
	        	return true;
	        },
			//This will walk through attributes after position "ix" and
			enableDisableFromLevel : function(lut, ix, productId){ 
	            //Here we decide if we start from the top of the lookup array
	            if( !lut ){
	                lut = settings.lookup;
	                ix = 0;
	            }

	            // Keep track of selected variant and qty
	            var selectedItemInfo;

	            // Go through options, trace selection
	            // Enable/disable options according to stock status
	            while(ix < settings.attributeIds.length) {
	                var aid = settings.attributeIds[ix];

	                // Enable buttons at this level according to stock
	                var stockInfo = this.getOptionsStockInfo(lut, ix);
	                var firstOpt, option;
	                var optionsArray = new Array();
	                for (var oid2 in stockInfo){
	                    if (stockInfo.hasOwnProperty(oid2)){
	                        if (!firstOpt ) { firstOpt = oid2; }
	                        optionsArray[0] = $('#' + productId + '_jpi_option_-for-notification' + aid + "-" + oid2); // Buttons
	                        for (var i=0; i<optionsArray.length; i++) {
	                        	option = optionsArray[i];
		                        if (option){
		                            if (stockInfo[oid2] && functions.hasAllChildrenInStock(oid2)){
		                                option.remove();
		                            } else {
		                            	option.prop('disabled', '');
		                                option.removeClass('disabled');
		                            }
		                        }
	                    	}
	                    }
	                }
	                
	                var optionValue = 0;
	                //  Continue in LUT
	                lut = lut[ optionValue && optionValue > 0 ? optionValue : firstOpt ];
	                ix++;
	            }
	            
	        },
		    
		    is_int : function (value){
		        if((parseFloat(value) == parseInt(value)) && !isNaN(value)){
		            return true;
		        } else {
		            return false;
		        }
		    },
		    
		    optionClickEd : function ( thisButton, attributeId, optionId, productId, useTransitionEffects ){
		    	$(thisButton).parents("ul").find('.attribute-selected').removeClass('attribute-selected');
		        $(thisButton).addClass('attribute-selected');
		        var currentLookup = settings.lookup;
		        if ($('.attribute-button-text-for-notification.attribute-selected').length == settings.attributeIds.length){
		        	var selectedOptions = $('.attribute-button-text-for-notification.attribute-selected');
		        	$tempLookup = currentLookup;
		        	for (var o=0;o<selectedOptions.length;o++){
		        		$tempLookup = $tempLookup[$(selectedOptions[o]).data("optionid")];
		        	}
		        	$simpleId = $tempLookup["id"];
		        	$("#notify_data #product_id").val($simpleId).trigger("focusout");
		        	
		        	//alert($simpleId);
		        }
		    }
		    
		}, customFunctions || {});
		
		var binders = $.extend({

			bindOptionButtons : function(){
				thisProductContainer.find('.attribute-button-text-for-notification').on('click', function(){
					functions.optionClickEd(this, $(this).data('attributeid'), $(this).data('optionid'), $(this).data('productid'));
		    	});
		    }
		}, customBinders || {});
		
		this.each(function () {
			
			this.attributeIds = settings.attributeIds;
			this.msg_more_options = settings.msg_more_options;
			this.confProductId = settings.confProductId;
			this.lookup = settings.lookup;
			this.selectedItemInfo = settings.selectedItemInfo;
			this.qty_sel = settings.qty_sel;
						
			this.hasStockBelow = functions.hasStockBelow;
			this.lookupFirstBelow = functions.lookupFirstBelow;
			this.getOptionsStockInfo = functions.getOptionsStockInfo;
			this.enableDisableFromLevel = functions.enableDisableFromLevel;
			this.optionClick = functions.optionClick;
			this.is_int = functions.is_int;
			
			this.bindOptionButtons = binders.bindOptionButtons;
			
		});

		this.get(0).enableDisableFromLevel(false, 0, settings.confProductId);
		binders.bindOptionButtons();
		
	};
	
})(jQuery);