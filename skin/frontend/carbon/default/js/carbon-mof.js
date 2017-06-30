/**
 * Old MultiOptionFilter support. Making sure that product grid updates when product list is updated with ajax
 */
if (typeof MultiOptionFilter != 'undefined') {
    var MultiOptionFilter = MultiOptionFilter.extend({
        updateHtml: function(transport){
            arguments.callee.$.updateHtml.call(this, transport);
            carbon.adjustHeightGrid(false, true);
        }
    });
}