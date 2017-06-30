jQuery(document).ready(function ($) {
    "use strict";

    $(".show-on-condition select").change(function () {
        var $this = $(this),
        firstHeardOtherInput = $this.closest(".show-on-condition").find(".select-other");
        firstHeardOtherInput.hide().find("input").val("");
        if ($this.find("option:last-child").prop("selected")) {
            firstHeardOtherInput.show();
        }
    });

    $(".first-heard select").change(function() {
        $(".show-on-condition").hide().find("select").selectOrDie("disable");
        var selectClass = $("option:selected", this).data("select");
        $(".select-" + selectClass).show().find("select").selectOrDie("enable");
    });
});
