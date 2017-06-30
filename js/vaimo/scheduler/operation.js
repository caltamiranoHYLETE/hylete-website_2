function refreshOperations(url) {
    var ids = $$('table#operation_grid_table .id').pluck('innerHTML');
    for (var i = 0; i < ids.length; i++) {
        ids[i] = ids[i].trim();
    }
    new Ajax.Request(url, {
        loaderArea: false,
        parameters: {
            ids: ids.join()
        },
        onSuccess: function (transport) {
            if (transport.responseText.isJSON()) {
                var response = transport.responseText.evalJSON();
                $$('table#operation_grid_table tr').each(function (tr) {
                    if (tr.down('.id')) {
                        var id = tr.down('.id').innerHTML.trim();
                        if (response.operations[id]) {
                            if (tr.down('.status')) {
                                tr.down('.status').innerHTML = response.operations[id].status;
                            }
                            if (tr.down('.next-run')) {
                                tr.down('.next-run').innerHTML = response.operations[id].next_run;
                            }
                            if (tr.down('.last-run')) {
                                tr.down('.last-run').innerHTML = response.operations[id].last_run;
                            }
                            if (tr.down('.last-status')) {
                                tr.down('.last-status').innerHTML = response.operations[id].last_status;
                            }
                        }
                    }
                });
            }
        }.bind(this)
    });
}