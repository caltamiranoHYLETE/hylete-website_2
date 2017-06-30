function generatePages () {

    if($('static-pages-progress') != null) {
        $('static-pages-progress').remove();
    }

    var form = $('edit_form');

    var resource  = $('page_resource').getValue();
    var storeId   = $('page_store_id').getValue();
    var startPage = $('page_startpage').getValue();
    var pageSize  = $('page_pagesize').getValue();

    trace("********** >>> Processing `" + resource.toUpperCase() + "` **********");
    processResource(resource, storeId, startPage, pageSize);

}

function processResource(resource, storeId, startPage, pageSize) {

    new Ajax.Request($('edit_form').readAttribute('action'), {
        method:'get',
        parameters: {'resource': resource, 'store_id': storeId, 'page': startPage, 'size': pageSize},
        onSuccess: function(transport) {

            try {
                var rsp = transport.responseText.evalJSON();
            }catch(je){
                alert(transport.responseText);
                return;
            }

            var _totalPages = parseInt(rsp.total_pages);

            trace("> Processed page " + startPage + " of " + _totalPages);

            if( (_totalPages == 0) || (_totalPages == startPage) ) {
                trace("********** Finished `" + resource.toUpperCase() + "` <<< **********");


                //if(resource.toUpperCase() != 'CATEGORIES') {
                //    trace("");
                //    trace("********** >>> Generating extensive cache.");
                //
                //    new Ajax.Request($('page_extensive_cache_url').getValue(),
                //        {
                //            method: 'get',
                //            parameters: {"resource": resource + "_zip"},
                //            requestHeaders: ['B-Store-Id', $('page_store_id').getValue()],
                //            onSuccess: function(response) {
                //
                //                trace("> Processed ZIP.");
                //
                //                new Ajax.Request($('page_extensive_cache_url').getValue(),
                //                    {
                //                        method: 'get',
                //                        parameters: {"resource": resource + "_db"},
                //                        requestHeaders: ['B-Store-Id', $('page_store_id').getValue()],
                //                        onSuccess: function(response) {
                //                            trace("> Processed DB.");
                //                            trace("********** Finished extensive cache. <<< **********");
                //                        },
                //                        onFailure: function(rsp) {
                //                            alert("ERROR! \n " + rsp.responseText);
                //                        }
                //                    });
                //            },
                //            onFailure: function(rsp) {
                //                alert("ERROR! \n " + rsp.responseText);
                //            }
                //    });
                //
                //}

            }
            else {
                startPage++;
                processResource(resource, storeId, startPage, pageSize);
            }

        },
        onFailure: function(rsp) { alert(rsp.responseText); }
    });

}

function trace(message) {
    var container = $('static-pages-progress');

    if(container == null) {
        $('pos_pages_tabs_main_section_content').insert({after:'<code id="static-pages-progress" style="background:black;display:block;color:#ccc;padding: 5px;"></code>'});
    }

    $('static-pages-progress').insert(message + "<br />");
}