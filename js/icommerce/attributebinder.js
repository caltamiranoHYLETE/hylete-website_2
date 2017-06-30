document.observe('click', function(e, el) {
    if (el = e.findElement('.add_more_bindings')) {
        var mainTable = el.up('table').id;
        var row = document.createElement('tr');
        var col1 = document.createElement('td');
        var col2 = document.createElement('td');
        var col3 = document.createElement('td');
        var trInc = 0;
        col1.innerHTML = el.up('tr').down('td').innerHTML;
        // We reset the value for main attribute value select
        col1.select('select', 'textarea', 'input').each(function(elm){
            elm.clear().checked = '';
        });
        col2.innerHTML = el.up('tr').down('td').next('td').innerHTML;
        row.appendChild(col1);
        row.appendChild(col2);
        row.appendChild(col3);
        el.up('tr').insert({'after': row});
        $(mainTable).select('tr').each(function(tr){
            tr.removeClassName('even');
            if(trInc++ % 2 == 0) {
                tr.addClassName('even');
            }
        });
        e.stop();
    }
});