"use strict";
jQuery.widget('vaimo.vaimoStyle', {
    options: {
        form: 'form',
        checkboxes: '[type=checkbox]',
        styleProduct: 'input[name="styleProduct"]',
        products: 'input[name="addToCartProducts[]"]',
        count: '.count strong',
        total: '.total strong',
        submit: 'button',
        required: '.required',
        error: '.error',
        item: '.item',
        options: '[name^="super_attribute"]'
    },
    
    elements: {},
    
    _init: function() {
        this.elements.form = this.element.find(this.options.form);
        this.elements.checkboxes = this.element.find(this.options.checkboxes);
        this.elements.count = this.element.find(this.options.count);
        this.elements.total = this.element.find(this.options.total);
        this.elements.submit = this.element.find(this.options.submit);
        this.elements.required = this.element.find(this.options.required);
        this.elements.error = this.element.find(this.options.error);
        
        this._eventHandlers();
        this._calculateCount();
        this._calculateTotal();
        this._enableDisableBuyButton();
    },
    
    _eventHandlers: function() {
        var that = this;
        this.elements.checkboxes.on('change', function() {
            that._calculateCount();
            that._calculateTotal();
            that._enableDisableBuyButton();
        });
        
        this.elements.form.on('submit', function(e) {
            e.preventDefault();
            that._submit();
        });
    },
    
    _getCheckedCount: function() {
        return this.element.find('input[type=checkbox]:checked').length;
    },
    
    _enableDisableBuyButton: function() {
        this.elements.submit.attr('disabled', !(this._getCheckedCount()));
    },
    
    _calculateCount: function() {
        this.elements.count.html(this._getCheckedCount());
    },
    
    _calculateTotal: function() {
        var sum = 0;
        this.element.find('input[type=checkbox]:checked ~ [data-price]').each(function() {
            sum += Number(jQuery(this).data('price'));
        });
        
        this.elements.total.html(formatCurrency(sum, priceFormat));
    },
    
    _submit: function() {
        var that = this,
            emptyOption = false,
            url = this.elements.form.attr('action'),
            $checked = this.element.find('input[type=checkbox]:checked'),
            $checkedItems = $checked.closest(this.options.item),
            $options = $checkedItems.find(this.options.options);

        this.elements.required.css('visibility', 'hidden');
        this.elements.error.hide();

        $options.each(function() {
            if (jQuery(this).val() == '') {
                emptyOption = true;
                jQuery(this).closest(that.options.item).find(that.options.required).css('visibility', 'visible');
                that.elements.error.show();
            }
        });
        
        if (!emptyOption && !addToCartAjax.isLoading) {
            var $styleProduct = this.element.find(this.options.styleProduct),
                $addToCartProducts = $checkedItems.find(this.options.products),
                data = $styleProduct.add($addToCartProducts).add($options).serialize();

            addToCartAjax.isLoading = true;
            addToCartAjax.resetPopup();
            if (addToCartAjax.options.showPopupWhenAdding == '1') {
                addToCartAjax.showPopup();
            }
            
            new Ajax.Request(url, {
                method: 'post',
                parameters: data,
                onSuccess: function(response) {
                    addToCartAjax.isLoading = false;
                    addToCartAjax.onSuccess(response);
                },
                onFailure: function() {
                    addToCartAjax.isLoading = false;
                    addToCartAjax.onFailure();
                }
            });
        }
    }
});