var AW_CatalogPermissions = Class.create({
    initialize: function () {
        this.mssProductSelect = $$('#aw_cp_mss_disable_product').first() || null;
        this.cpProductSelect = $$('#aw_cp_disable_product').first() || null;
        this.mssPriceSelect = $$('#aw_cp_mss_disable_price').first() || null;
        this.cpPriceSelect = $$('#aw_cp_disable_price').first() || null;
        this.mssCategorySelect = $$('select[name="general[aw_cp_mss_disable_category]"]').first() || null;
        this.cpCategorySelect = $$('select[name="general[aw_cp_categorydisable][]"]').first() || null;
        this.init();
    },

    init: function () {
        if (this.mssProductSelect) {
            if (parseInt(this.mssProductSelect.value)) {
                this.hideGroups(this.cpProductSelect);
            }
            this.mssProductSelect.observe('change', this.changeSelect.bind(this));
        }
        if (this.mssPriceSelect) {
            if (parseInt(this.mssPriceSelect.value)) {
                this.hideGroups(this.cpPriceSelect);
            }
            this.mssPriceSelect.observe('change', this.changeSelect.bind(this));
        }
        if (this.mssCategorySelect) {
            if (parseInt(this.mssCategorySelect.value)) {
                this.hideGroups(this.cpCategorySelect);
            }
            this.mssCategorySelect.observe('change', this.changeSelect.bind(this));
        }

    },

    hideGroups: function (select) {
        var options = select.options;
        for (i = 2; i < options.length; i++) {
            options[i].disabled = 1;
        }
    },

    showGroups: function (select) {
        var options = select.options;
        for (i = 2; i < options.length; i++) {
            options[i].disabled = 0;
        }
    },

    changeSelect: function (event) {
        var target = null;
        var select = Event.element(event);
        if (select.id == 'aw_cp_mss_disable_product') {
            target = this.cpProductSelect;
        } else if (select.id == 'aw_cp_mss_disable_price') {
            target = this.cpPriceSelect;
        } else {
            target = this.cpCategorySelect;
        }

        if (parseInt(select.value)) {
            this.hideGroups(target);
        } else {
            this.showGroups(target);
        }
    }

});

Ajax.Responders.register({
    onComplete: function() {
        new AW_CatalogPermissions();
    }
});

document.observe("dom:loaded", function() {
    new AW_CatalogPermissions();
});
