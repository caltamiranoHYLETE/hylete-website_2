/**
 * Magento BSS FastOrder Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category	BSS
 * @package	BSS_FastOrder
 * @version	1.0.0
 */
 var fastorder_ajax_timer;

 window.onresize = function(event) {
    if(jQuery(window).width() <= 955 && jQuery(window).width() >= 320) {
        jQuery("#fastorder").width(jQuery(window).width()- 55);
    }else {
        if(jQuery(window).width() > 955) {
            jQuery("#fastorder").width('');
        }
    }
};

Event.observe(window, 'load', (function() {

}));

/**
 * Search all simple produtcs matching current typed sku
 */
 function fastorder_searchResult(input_id) {

 	if($(input_id).value.length >= fastorder_minAutocomplete)
 	{
 		var $autocomplete = $('fastorder_autocomplete_'+input_id.replace('fastorder-ref-', ''));

        //prepend close button
        $autocomplete.update('<ul></ul>');

        //prepend close button
        $autocomplete.update('<div class="fastorder-wrap-close"><a href="javascript:void(0);" class="fastorder-close" onclick="$(\'fastorder_autocomplete_'+input_id.replace('fastorder-ref-', '')+'\').hide();">'+fastorder_translate_close+'</a></div>'+$autocomplete.innerHTML);

        $($autocomplete).getElementsBySelector('ul').each(function(elem) {
        	$(elem).update('<li class="loader">&nbsp;</li>');
        });
        $autocomplete.show();
        $autocomplete.style.display = 'block';

        var $keyword = $(input_id).value;

        new Ajax.Request(
        	fastorder_ajax_url,
        	{
        		parameters: 'sku='+$keyword+'&max='+fastorder_maxResults,
        		method: 'post',
        		evalJSON: true,
        		onSuccess: function(transport, json) {
        			var data = transport.responseText.evalJSON(true);

                    //append all results
                    $($autocomplete).getElementsBySelector('ul').each(function(elem) {
                    	$(elem).update('');
                    });

                    if(data.length>0) //results found
                    {
                    	for(var i = 0; i < data.length; i++)
                    	{
                    		var $class = '';
                    		if(i == 0) $class = 'selected';

                    		$($autocomplete).getElementsBySelector('ul').each(function(elem) {
                    			$(elem).update($(elem).innerHTML+'<li class="'+$class+'">'+
                    				'<a rel="'+data[i].sku+'" href="javascript:void(0);" onclick="selectSku(this, \''+input_id.replace('fastorder-ref-', '')+'\', \''+data[i].sku+'\');">'+
                    				'<span class="product-image"><span class="animation"><img src="'+data[i].thumbnail+'" alt="" /></span></span>'+
                    				'<span class="product-name">'+data[i].name.replace($keyword, '<span class="ref-part">'+$keyword+'</span>')+'<br/><span class="reference">'+fastorder_translate_ref+' '+data[i].sku.replace($keyword, '<span class="ref-part">'+$keyword+'</span>')+'</span>'+'</span>'+
                                    // '<span class="product-name">'+data[i].name.replace($keyword, '<span class="ref-part">'+$keyword+'</span>')+'</span><br>'+
                                    '<span class="product-price">'+data[i].price +'</span>'+
                                    '<span class="product-url no-display">'+data[i].url +'</span>'+
                                    '</a>'+
                                    '<div class="clear"></div>'+
                                    '</li>');
});
}
                        //add hover event on <li>
                        $($autocomplete).getElementsBySelector('ul li').each(function(elem) {
                        	$(elem).observe('mouseover', function() {
                        		$($autocomplete).getElementsBySelector('ul li').each(function(all_li) {
                        			$(all_li).removeClassName('selected');
                        		});
                        		$(elem).addClassName('selected');
                        	});
                        });
                        $($autocomplete).getElementsBySelector('ul li').each(function(elem) {
                        	$(elem).observe('mouseout', function() {
                        		$(elem).removeClassName('selected');
                        	});
                        });
                    }
                    else //no results found
                    {
                    	$($autocomplete).getElementsBySelector('ul').each(function(elem) {
                    		$(elem).update('<li class="fastorder-no-results">'+fastorder_translate_no_results+'</li>');
                    	});
                    }

                }
            }
            );
}
}

/**
 * Populate a row with selected values
 */
 function selectSku(elem, result_id, sku)
 {

 	var $autocomplete = $('fastorder_autocomplete_'+result_id);
    $autocomplete.hide(); //on cache les résultats

    var $img;
    $(elem).getElementsBySelector('.product-image').each(function(img) {
    	$img = img.innerHTML;
    }); //on récupère l'image

    var $name;
    $(elem).getElementsBySelector('.product-name').each(function(name) {
    	$name = name.innerHTML;
    }); //on récupère le nom

    var $url;
    $(elem).getElementsBySelector('.product-url').each(function(url) {
    	$url = url.innerHTML;
    });

    var $price;
    $(elem).getElementsBySelector('.product-price').each(function(price) {
    	$price = price.innerHTML;
    });

    //on place les données dans le row courant
    $$('#fastorder-'+result_id+' .fastorder-row-image').each(function(img) {
    	$(img).update($img);
    });
    $$('#fastorder-'+result_id+' .fastorder-row-name').each(function(name) {
    	$(name).update('<a class="animation" target="_blank" href="'+ $url +'">'+$name+'</a>'+'<span>'+ $price + '</span>');
    });

    //button add
    $('fastorder-add-'+result_id).removeClassName('disabled');

    //complete the search field
    $('fastorder-ref-'+result_id).value = sku;
    $$('#fastorder-'+result_id+' input.sku').each(function(input) {
    	$(input).value = sku;
    });

    //animate image & name
    $$('#fastorder-'+result_id+' .fastorder-row-image span.animation').each(function(animation) {
    	new Effect.Morph(
    		$(animation),
    		{
    			style: {left: '0px'},
    			duration: 0.5,
    			afterFinish:
    			function() {
    				$$('#fastorder-'+result_id+' .fastorder-row-name span.animation').each(function(animation2) {
    					new Effect.Morph(
    						$(animation2),
    						{
    							style: {left: '0px'},
    							duration: 0.5
    						}
    						);
    				});
    			}
    		}
    		);
    });
}

/**
 * Manage down and up arrow to select product between the ones available
 */
 function fastorder_manageArrow(elem_id, type)
 {
 	$autocomplete = $('fastorder_autocomplete_'+elem_id);
 	var $elem;

 	if(type=='down')
 	{
 		if($($autocomplete).getElementsBySelector('ul li').length > 0)
 		{
 			$($autocomplete).getElementsBySelector('li.selected').each(function(li) {
 				$elem = li;
 			});
 			$($autocomplete).getElementsBySelector('ul li:last').each(function(elem) {
                if($(elem).hasClassName('selected')) //we reached the bottom
                {
                	$($autocomplete).getElementsBySelector('ul li:first')[0].addClassName('selected');
                	$($autocomplete).getElementsBySelector('ul li:last')[0].removeClassName('selected');
                }
                else if($elem)
                {
                	$elem.removeClassName('selected');
                	$elem.next().addClassName('selected');
                }
            });
 			if(!$elem)
 			{
 				$($autocomplete).getElementsBySelector('ul li:first')[0].addClassName('selected');
 			}
 		}
 	}
 	else if(type=='up')
 	{
 		if($($autocomplete).getElementsBySelector('ul li').length > 0)
 		{
 			$($autocomplete).getElementsBySelector('li.selected').each(function(li) {
 				$elem = li;
 			});
 			$($autocomplete).getElementsBySelector('ul li:first').each(function(elem) {
                if($(elem).hasClassName('selected')) //we reached the bottom
                {
                	$($autocomplete).getElementsBySelector('ul li:first')[0].removeClassName('selected');
                	$($autocomplete).getElementsBySelector('ul li:last')[0].addClassName('selected');
                }
                else if($elem)
                {
                	$elem.removeClassName('selected');
                	$elem.previous().addClassName('selected');
                }
            });
 			if(!$elem)
 			{
 				$($autocomplete).getElementsBySelector('ul li:last')[0].addClassName('selected');
 			}
 		}
 	}
 }


/**
 * Manage enter and ok button
 */
 function fastorder_manageEnterAndOkButton(elem_id)
 {

 	$id = elem_id.replace('fastorder-ref-', '');

    if($$('#fastorder_autocomplete_'+$id+' ul li.selected').length>0) //if one product is selected, add it to the cart
    {
    	selectSku($$('#fastorder_autocomplete_'+$id+' ul li.selected a')[0], $id, $$('#fastorder_autocomplete_'+$id+' ul li.selected a')[0].readAttribute('rel'));
    }
    else //no results => redo a search
    {
    	window.clearTimeout(fastorder_ajax_timer);
    	fastorder_ajax_timer = window.setTimeout("fastorder_searchResult('"+elem_id+"')", 500);
    }
}

//reset row select
function fastorder_reset(id) {
	$('fastorder-ref-'+id).value = '';
	$$('#fastorder-'+id+' .fastorder-row-image')[0].update('&nbsp;');
	$$('#fastorder-'+id+' .fastorder-row-name')[0].update('&nbsp;');
	$$('#fastorder-'+id+' input.qty')[0].value = '1';
	$$('#fastorder-'+id+' input.sku')[0].value = '';
	$('fastorder-add-'+id+'').addClassName('disabled');
}


/**
 * Add product in the cart and empty the currrent row
 */
 function fastorder_manageAddButton()
 {

 	for(id = 1; id < jQuery('#fastorder .fastorder-multiple-form > div').length;id++) {
                //reset all row
                $('fastorder-ref-'+id).value = '';
                $$('#fastorder-'+id+' .fastorder-row-image')[0].update('&nbsp;');
                $$('#fastorder-'+id+' .fastorder-row-name')[0].update('&nbsp;');
                $$('#fastorder-'+id+' input.qty')[0].value = '1';
                $$('#fastorder-'+id+' input.sku')[0].value = '';
                $('fastorder-add-'+id+'').addClassName('disabled');

                //hide the row loader
                $$('#fastorder-'+id+' .row-loader')[0].hide();
                $$('#fastorder-'+id+' .row-loader-bg')[0].hide();
            };

                //check if cart is empty
                if($$('.cart').length == 0)
                {
                	$$('.cart-empty')[0].up().getElementsBySelector('.page-title')[1].remove();
                	$$('.cart-empty')[0].remove();
                	$('fastorder').insert({'after': '<div class="cart"><br/><br/><br/><br/><br/><br/></div>'})
                }

                //show the cart loader
                $$('.cart')[0].update($$('.cart')[0].innerHTML+'<div class="fastorder-cart-loader-bg"></div>');
                $$('.cart')[0].update($$('.cart')[0].innerHTML+'<div class="fastorder-cart-loader"></div>');

                new Effect.Morph(
                	$$('.cart .fastorder-cart-loader-bg')[0],
                	{
                		style: {opacity: '0.5'},
                		duration: 0.150
                	}
                	);
                new Effect.Morph(
                	$$('.cart .fastorder-cart-loader')[0],
                	{
                		style: {opacity: '0.5'},
                		duration: 0.150
                	}
                	);

                //reload the cart -> jQuery, because evalScripts from Prototype is not wirking for certain variables, like Coupon / Shipping estimates...
                jQuery.ajax({
                	url: fastorder_ajax_cart_url,
                	success: function(data) {
                		jQuery('.cart').html(data)
                	}
                });;

                //reload the cart -> old code
                /*new Ajax.Request(
                    fastorder_ajax_cart_url,
                    {
                        onSuccess: function(transport, json) {
                            data = transport.responseText;
                            $$('.cart')[0].update(data);
                        },
                        evalScripts: true
                    }
                    );*/
}

//action after click send form
function fastorder_sendform() {
	var error = false;
	var error2 = false;
	setTimeout( function () {
		jQuery('.fastorder-multiple-form div').each(function() {
			if(jQuery(this).find('.qty').hasClass('validation-failed') || jQuery(this).find('.qty').val() < 1)  {

				if(jQuery(this).children('.fastorder-row-qty').children('.sku').val() !== '') {
					error = true;
					jQuery(this).children('.fastorder-row-qty').children('.qty').addClass('validation-failed');
				}else {
					jQuery(this).children('.fastorder-row-qty').children('.qty').removeClass('validation-failed');
				};
			};
		});

		if(error) {
			alert(error3);
		}else {
			$$('.row-loader').each(function(elem) {
				$(elem).show();
				$(elem).style.display = 'block';
			});
			$$('.row-loader-bg').each(function(elem) {
				$(elem).show();
				$(elem).style.display = 'block';
			});
			jQuery.ajax({
				type: "POST",
				url: fastorder_ajax_add_cart,
				data: {items: jQuery('form#fastorder_form').serialize()},
				success: function(msg) {
					if(msg == '0') {
						fastorder_manageAddButton();
					}else {
						fastorder_resetAll();
						alert(error4);
					}
				},
				error: function() {
					alert("failure");
				}
			});
		};
	} , 1000 );
}

function fastorder_resetAll() {
	for(id = 1; id < jQuery('#fastorder .fastorder-multiple-form > div').length;id++) {
        //reset all row
        $('fastorder-ref-'+id).value = '';
        $$('#fastorder-'+id+' .fastorder-row-image')[0].update('&nbsp;');
        $$('#fastorder-'+id+' .fastorder-row-name')[0].update('&nbsp;');
        $$('#fastorder-'+id+' input.qty')[0].value = '1';
        $$('#fastorder-'+id+' input.sku')[0].value = '';
        $('fastorder-add-'+id+'').addClassName('disabled');
        //hide the row loader
        $$('#fastorder-'+id+' .row-loader')[0].hide();
        $$('#fastorder-'+id+' .row-loader-bg')[0].hide();
    };
}

function fastorder_create() {
	var length = jQuery('#fastorder .fastorder-multiple-form > div').length;
	if(length % 2 == 0) {
		var form1 = '<div class="fastorder-row odd-row" id="fastorder-'+ length +'">'+form+'</div>';
	}else {
		var form1 = '<div class="fastorder-row" id="fastorder-'+ length +'">'+form+'</div>';
	}
	form1 = form1.replace(/fastorder-ref-1/g,"fastorder-ref-"+length);
	form1 = form1.replace(/fastorder-1-search/g,"fastorder-"+length+'-search');
	form1 = form1.replace(/fastorder_autocomplete_1/g,"fastorder_autocomplete_"+length);
	form1 = form1.replace(/fastorder-add-1/g,"fastorder-add-"+length);
	jQuery('.fastorder-multiple-form').append(form1);
}

jQuery(document).ready(function($) {

    //lightbox
    jQuery('.fastorder-row-image').on('click',function() {
    	if(jQuery(this).parent().attr('id').replace('fastorder-','') != 0 && jQuery(this).children().length > 0 ) {
    		var html = jQuery(this).children().html();
    		jQuery('.fastorder-thumbnail-container').html(html);
    		jQuery('.fastorder-thumbnail-container').css({
    			width:'100%',height:'100%',position:'fixed','z-index':'999',
    			'background-color':'rgba(53, 53, 53, 0.64)',display:'block',top:0,left:0
    		});
    		jQuery('.fastorder-thumbnail-container').children().css({
    			width:'auto',height:'85%','z-index':'1000',margin:'0 auto', display: 'block','margin-top':'2%'
    		});
    	};
    });

    //turn off lightbox
    jQuery('.fastorder-thumbnail-container').click(function() {
    	jQuery(this).hide();
    })

    //stop send form
    jQuery('#fastorder_form').submit(function(e) {
    	return false;
    });

    //turn off submit form when press enter
    jQuery('.fastorder-row-ref .input-text').on('keydown',function(e) {
    	if(e.keyCode == 13)
    	{
    		e.preventDefault();
    	};
    });
    jQuery('.fastorder-row-qty .qty').on('keydown',function(e) {
    	if(e.keyCode == 13)
    	{
    		e.preventDefault();
    	};
    });

    //event when type in input
    jQuery('.fastorder-row-ref .input-text').on('keyup',function(e) {
        if(e.keyCode == 13) //touche enter
        {
        	fastorder_manageEnterAndOkButton(jQuery(this).attr('id'));
        }
        if(e.keyCode == 8 || e.keyCode == 46) //suppr // backspace
        {
        	window.clearTimeout(fastorder_ajax_timer);
        	fastorder_ajax_timer = window.setTimeout("fastorder_searchResult('"+jQuery(this).attr('id')+"')", 500);
        }
        else if(e.keyCode == 38) //arrow top => move selection
        {
        	fastorder_manageArrow(jQuery(this).attr('id').replace('fastorder-ref-', ''), 'up');
        }
        else if(e.keyCode == 40) //arrow down => move selection
        {
        	fastorder_manageArrow(jQuery(this).attr('id').replace('fastorder-ref-', ''), 'down');
        }
        else if(e.keyCode == 27) //touche echap => close autocomplete
        {
        	jQuery('fastorder_autocomplete_'+jQuery(this).attr('id').replace('fastorder-ref-', '')).hide();
        }
        else if(fastorder_allowed_character.indexOf(String.fromCharCode(e.keyCode)) != -1) //other allowed char.
        {
        	window.clearTimeout(fastorder_ajax_timer);
        	fastorder_ajax_timer = window.setTimeout("fastorder_searchResult('"+jQuery(this).attr('id')+"')", 500);
        };

                //If sku is too small, disabled the OK button
                if(!jQuery(this).val().length >= fastorder_minAutocomplete)
                {
                	jQuery(this).parent().next('.fastorder_autocomplete').hide();
                };
            });

        /**
        * Add button
        */
        jQuery('.fastorder-row-add .btn-ok').on('click',function() {
        	if(!jQuery(this).hasClass('disabled')) {
        		fastorder_reset(jQuery(this).attr('id').replace('fastorder-add-',''));
        	}
        });


        /**
        * Event triggered when pressing OK button of the search field
        */
        jQuery('.fastorder-row-ref button').on('click',function() {
        	if(!jQuery(this).hasClass('disabled'))
        	{
        		fastorder_manageEnterAndOkButton(jQuery(this).prev().attr('id'));
        	};
        });

    });
