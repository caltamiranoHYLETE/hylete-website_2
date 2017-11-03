var $j = jQuery.noConflict();

$j(document).ready(function() {
    $j('.stay-checked').each(function(){
        var checkboxId = $j(this).attr('id');
        $j(this).prop('checked', false);
        var isChecked = localStorage.getItem(checkboxId + '-checked');
        if (isChecked === 'true') {
            $j(this).prop('checked', true);
        }
    });

    $j('.stay-checked').change(function(){
        var checkboxId = $j(this).attr('id');
        localStorage.setItem(checkboxId + '-checked', $j(this).is(':checked'));
    });
});