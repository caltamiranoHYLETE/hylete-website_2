jQuery.widget('vaimo.vaimoPrevNextLocal', {
    options: {
        links: '.prevnext a',
        nextClass: 'next',
        previousClass: 'previous',
        disabledClass: 'disabled'
    },
    
    data: {},
    
    _init: function() {
        var that = this;
        
        document.addEventListener("vaimoPrevNextLocalReady", function(e) {
            var $productId = localStorage.getItem("vaimo_prevnextlocal_product_id");
            $prevNextLocal.getProductData({"product_id":$productId, "spread":1 });
        });
        
        document.addEventListener("vaimoPrevNextLocalHaveData", function(e) {
            that.data.detail = e.detail;
            that.data.lastKey = e.detail.data_array.length-1;
            
            console.log('detail current_position', e.detail);
            console.log('lastkey', that.data.lastKey);
            
            that._eventHandlers();
            that._enableDisableLinks();
        });
    },
    
    _eventHandlers: function() {
        var that = this;
        
        jQuery(document).on('click', that.options.links, function(e) {
            e.preventDefault();
            
            if (jQuery(this).hasClass(that.options.previousClass)) {
                window.location.href = that._getPreviousUrl();
                
            } else if (jQuery(this).hasClass(that.options.nextClass)) {
                window.location.href = that._getNextUrl();
            }
        });
    },
    
    _getPreviousUrl: function() {
        var position = (this._isFirst()) ? this.data.detail.lastKey : this.data.detail.current_position-1;
        return this.data.detail.data_array[position].url_key;
    },
    
    _getNextUrl: function() {
        var position = (this._isLast()) ? 0 : this.data.detail.current_position+1;
        return this.data.detail.data_array[position].url_key;
    },
    
    _isFirst: function() {
        return (this.data.detail.current_position == 0);
    },
    
    _isLast: function() {
        return (this.data.detail.current_position == this.data.lastKey);
    },
    
    _enableDisableLinks: function() {
        jQuery(this.options.links).removeClass(this.options.disabledClass);
        
        if (typeof this.data.detail.current_position == 'undefined') {
            jQuery(this.options.links).addClass(this.options.disabledClass);
            
        } else if (this.data.detail.current_position == 0) {
            jQuery(this.options.links).hasClass(this.options.previousClass).addClass(this.options.disabledClass);
            
        } else if (this.data.detail.current_position == this.data.lastKey) {
            jQuery(this.options.links).hasClass(this.options.nextClass).addClass(this.options.disabledClass);
        }
    }
});