function showShippingMethodTooltip(offsetNode, info){

    var offset = $(offsetNode).cumulativeOffset(),
        x = offset.left + 20,
        y = offset.top;

    $$('body').each(function(node){

        var toolTipHtml = '<div id="shipping-method-tooltip">'+info+'</div>';
        
        $(node).insert( {bottom: toolTipHtml} );
        
        $('shipping-method-tooltip').setStyle({
            top: y + 'px',
            left: x + 'px'
        });

    });
}

function removeShippingMethodToolTip(){
    $('shipping-method-tooltip').remove();
}