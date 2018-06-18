jQuery(function() {
    jQuery('div[onclick], button[onclick]').on("click",function(){
        var expireTime = jQuery('#bw_cookie_expire_time').val();
        var onclick = jQuery(this).attr('onclick');
        if(onclick && isAnyValueIn(onclick, ['cart/add', 'productAddToCartForm', 'addToCartAjax.add'])){
            var formToValidate	= jQuery('[id^=product_addtocart_form]');
            var showPrompt		= true;
            jQuery('[id^=product_addtocart_form] select').each(function( index ) {
                if(jQuery(this).hasClass('validation-failed')){
                    if(this.value==''){
                        showPrompt= false;
                    }
                }
            });
            if(showPrompt){
                openLightBox(expireTime);
            }
        }
    });
});

function abCartTrackEvent(category, descev)
{
    if (typeof(dataLayer) != 'undefined'){
        dataLayer.push({'event': category + ' - ' + descev, 'category': category, 'action': descev});
    } else if (typeof(ga) != 'undefined'){
        ga('send', 'event', category, descev);
    } else if (typeof(_gaq) != 'undefined'){
        _gaq.push(['_trackEvent', category, descev]);
    }
}

function closeLightbox(expireTime){
    jQuery('.bw_block_page').fadeOut().remove();
    if(expireTime > 0){
        var d = new Date();
        d.setTime(d.getTime()+(expireTime*1000));
        Mage.Cookies.set('bw_lightbox_off', true, d);
    }else{
        Mage.Cookies.set('bw_lightbox_off', true);
    }
}

function openLightBox(expireTime){

    if(jQuery('.bw_block_page')){

        // get the screen height and width
        var maskHeight = jQuery(window).height();
        var maskWidth = jQuery(window).width();

        // calculate the values for center alignment
        var dialogTop =  (maskHeight  - jQuery('.bw_box').height())/2;
        var dialogLeft = (maskWidth - jQuery('.bw_box').width())/2;

        jQuery('.bw_block_page').css({
            height:'100%',
            width:'100%',
            position: 'absolute'
        }).show();

        jQuery('.bw_box').css({
            top: dialogTop,
            left: dialogLeft,
            position:"fixed"
        }).show();


        jQuery('.bw_block_page').fadeIn();
        jQuery('.bw_box').fadeIn();

        jQuery('.bw_box_close').click(function(){
            jQuery('.bw_block_page').fadeOut().remove();
            if(expireTime > 0){
                var d = new Date();
                d.setTime(d.getTime()+(expireTime*1000));
                Mage.Cookies.set('bw_lightbox_off', true, d);
            }else{
                Mage.Cookies.set('bw_lightbox_off', true);
            }
        });
    }
}

function isAnyValueIn(target, values) {

    for (var i = 0; i < values.length; i++) {
        if (target.indexOf(values[i]) > -1) return true;
    }
    return false;
}


jQuery(window).on('resize', function(){
    var isVisible = jQuery('.bw_box').is(':visible');
    if(isVisible){
        openLightBox(3600);
    }
});

