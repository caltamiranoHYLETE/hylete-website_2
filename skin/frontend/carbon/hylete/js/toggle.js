jQuery.fn.mySlideToggle = function(speed, show) {
    return this.each(function() {
        if (show) {
            jQuery(this).slideDown(speed, function() {
                jQuery(this).css('overflow', 'visible');
            });
        } else {
            jQuery(this).slideUp(speed);
        }
    });    
}

var activeToggle = false,
    toggleTimeout = false;
    
jQuery.widget('vaimo.vaimoToggle', {
    options: {
        link: 'data-togglelink',
        content: 'data-togglecontent',
        group: 'data-togglegroup',
        action: 'data-toggleaction',
        autoclose: 'data-toggleautoclose',
        defaultAction: 'click',
        activeClass: 'active',
        animationSpeed: 150,
        timeoutDelay: 150,
    },
    
    values: {},
    
    _init: function() {
        this._eventHandlers();
    },
    
    _eventHandlers: function() {
        var that = this;
        jQuery(document).on('click mouseenter', that._getAttribute('link'), function(e) {
            e.stopPropagation();
            if (that._isApprovedAction(e.type, jQuery(this))) {
                var timeout = e.type == 'mouseenter';
                that._initToggle(jQuery(this), timeout);
            }
            
        }).on('mouseleave', that._getAttribute('link'), function(e) {
            if (that._isApprovedAction(e.type, jQuery(this))) {
                that._initToggle(jQuery(this), true, true);
            }
            
        }).on('mouseleave', that._getAttribute('content'), function(e) {
            if (that._isApprovedAction(e.type, jQuery(this))) {
                that._initToggle(jQuery(this), true, true);
            }

        }).on('mouseenter', that._getAttribute('content'), function(e) {
            clearTimeout(toggleTimeout);
            
        }).on('click touchend', function(e) {
            clearTimeout(toggleTimeout);
            if (!jQuery(e.target).is(that._getAttribute('link') + ',' + that._getAttribute('content')) && jQuery(e.target).closest(that._getAttribute('content')).length == 0) {
                jQuery(that._getAttribute('autoclose')).removeClass(that.options.activeClass);
                jQuery(that._getAttribute('autoclose') + that._getAttribute('content')).slideUp(Number(that.options.animationSpeed));
            }
        });
    },
    
    _initToggle: function(element, timeout, forcedState) {
        var that = this;
        
        that._setValues(element);
        clearTimeout(toggleTimeout);
        
        if (timeout) {
            toggleTimeout = setTimeout(function() {
                that._toggleNow(element, forcedState);
            }, that.options.timeoutDelay);
            
        } else {
            that._toggleNow(element, forcedState);
        }
    },
    
    _isApprovedAction: function(action, element) {
        var thisAction = element.attr(this.options.action);
        
        if (!thisAction && action == this.options.defaultAction) {
            return true;
        } else if (thisAction) {
            return element.attr(this.options.action).indexOf(action) > -1;
        } else {
            return false;
        }
    },
    
    _toggleNow: function(element, forcedState) {
        var toggle = (typeof forcedState === 'undefined') ? this._isCurrentActive(element) : forcedState;
        this._resetAll();
        this._toggleThis(toggle);
        activeToggle = (!toggle) ? this.values.link : false;
    },
    
    _setValues: function(element) {
        this.values.link = element.attr(this.options.link);    
        this.values.group = element.attr(this.options.group);
    },
    
    _getAttribute: function(attribute) {
        return '[' + this.options[attribute] + ']';
    },
    
    _getAll: function(attribute) {
        return jQuery('[' + this.options[attribute] + ']' + this._getThisGroupAttribute());
    },
    
    _getThis: function(attribute) {
        return jQuery('[' + this.options[attribute] + '="' + this.values.link + '"]' + this._getThisGroupAttribute());
    },
    
    _getThisGroupAttribute: function() {
        return '[' + this.options.group + '="' + this.values.group + '"]';
    },
    
    _resetAll: function() {
        this._getAll('link').removeClass(this.options.activeClass);
        this._getAll('content').slideUp(Number(this.options.animationSpeed));
    },
    
    _toggleThis: function(toggle) {
        this._getThis('link').toggleClass(this.options.activeClass, !toggle);
        this._getThis('content').toggleClass(this.options.activeClass, !toggle).mySlideToggle(Number(this.options.animationSpeed), !toggle);
    },
    
    _isCurrentActive: function(element) {
        return (element.hasClass(this.options.activeClass));
    },
});