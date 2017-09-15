(function(window) {
    window.sweettooth = window.sweetooth || {};
    window.sweettooth.slider = {
        slider: null,
        data: {
            sliderInfo: {
                min: 1,
                max: 1,
                step: 1,
                currentValue: 1
            },
            sliderSelectors: {
                handle: 'sliderHandle',
                rail: 'sliderRail',
                caption: 'sliderCaption',
                ruleUses: 'redemption_rule_uses'
            },
            urls: {
                slider: '',
                checkboxAdd: '',
                checkboxRemove: ''
            },
            onLoadTimeout: 50,
            showElementWhenCreatingSlider: false,
            lastSliderAjaxRequest: false,
            useSlider: true,
            isCheckboxAction: false
        },
        init: function(sliderInfo, urls, showElementWhenCreatingSlider) {
            this.data.sliderInfo = sliderInfo;
            this.data.urls = urls;
            this.data.showElementWhenCreatingSlider = showElementWhenCreatingSlider;
            
            if (this.data.useSlider) {
                this.createSlider();
                this.initializeSliderEvents();
                
                var self = this;
                Event.observe(window, 'orientationchange', function() {
                    self.reloadSlider(); 
                });
            }
            
            this.initializeCheckboxRules();
        },
        createSlider: function() {
            var self = this;
            var onload = function() {
                setTimeout(function() {
                    if (self.data.showElementWhenCreatingSlider) {
                        $(self.data.showElementWhenCreatingSlider).show();
                    }

                    self.slider = new RedemptionSlider(
                        self.data.sliderSelectors.handle, 
                        self.data.sliderSelectors.rail, 
                        self.data.sliderSelectors.caption,
                        self.data.sliderSelectors.ruleUses
                    );
            
                    self.slider.regenerateSlider(
                        self.data.sliderInfo.min, 
                        self.data.sliderInfo.max, 
                        self.data.sliderInfo.step, 
                        self.data.sliderInfo.currentValue
                    );
            
                    self.slider.setExternalValue(self.data.sliderInfo.currentValue);

                    if (self.data.showElementWhenCreatingSlider) {
                        $(self.data.showElementWhenCreatingSlider).hide();
                    }
                }, self.data.onLoadTimeout);
            };
            
            (document.loaded) ? onload() : document.observe("dom:loaded", onload);
        },
        initializeSliderEvents: function() {
            var self = this;
            $('sliderHandle').observe('mousedown', function() {
                $('sliderRail').addClassName('sliderRail-sliding');
                $('sliderHandle').addClassName('sliderHandle-sliding');
            });
            
            $$('#sliderHandle, .cartSlider .slider').invoke('observe', 'mouseup', function() {
                $('sliderRail').removeClassName('sliderRail-sliding');
                $('sliderHandle').removeClassName('sliderHandle-sliding');
            });
            
            $('slider_increase_points').observe('click', function() {
                self.slider.incr();
            });
            
            $('slider_reduce_points').observe('click', function() {
                self.slider.decr();
            });
            
            // Use all points checkbox
            $('use_all_points').observe('click', function() {
                (this.checked) ? self.slider.maximize() : self.slider.slider.setValue(0);
            });
        },
        disposeSliderEvents: function() {
            var self = this;
            Event.stopObserving('sliderHandle', 'mousedown');
            $$('#sliderHandle, .cartSlider .slider').each(function(sliderEl){
                Event.stopObserving(sliderEl, 'mouseup');
            });
            
            Event.stopObserving('slider_increase_points', 'click');
            Event.stopObserving('slider_reduce_points', 'click');
            Event.stopObserving('use_all_points', 'click');
        },
        updateSlider: function(amount) {
            var self = this;
            
            $('slider-wait').show();
            this.beforeAjax();
            
            if (this.data.lastSliderAjaxRequest) {
                this.data.lastSliderAjaxRequest.options.onSuccess = function(t){};
            }
            
            if (this.data.urls.slider) {
                this.data.lastSliderAjaxRequest = new Ajax.Request(
                    this.data.urls.slider, {
                        parameters: {points_spending: amount},
                        onSuccess: function (response) {
                            self.data.sliderInfo.currentValue = amount;
                            $('slider-wait').hide();
                            self.afterAjax(response);
                        }
                    }
                );
            };
        },
        initializeCheckboxRules: function() {
            var self = this;
            $$('.cart_redemption_item input[type=checkbox]').invoke('observe', 'click', function() {
                var checkbox = $(this);
                var label = checkbox.next();
                
                checkbox.disabled = true;
                checkbox.hide();
                label.addClassName('rewards-slider-refreshing-checkbox-rule');
                
                self.beforeAjax();
                
                var url = (this.checked) ? self.data.urls.checkboxAdd : self.data.urls.checkboxRemove;
                if (!url) {
                    return false;
                }
                
                url += 'rids/' + this.value;
                
                new Ajax.Request(url, {
                    onSuccess: function (response) {
                        var result = response.responseJSON;
                        
                        if (result.hasOwnProperty('error') && result.error === true) {
                            checkbox.checked = !checkbox.checked;
                        }
                        
                        checkbox.disabled = false;
                        checkbox.show();
                        label.removeClassName('rewards-slider-refreshing-checkbox-rule');
                        
                        window.sweettooth.slider.data.isCheckboxAction = true;
                        self.afterAjax(response);
                        window.sweettooth.slider.data.isCheckboxAction = false;
                    }
                });
                
                return false;
            });
        },
        beforeAjax: function() {
            return true;
        },
        afterAjax: function(response) {
            return true;
        },
        logMessage: function(message) {
            var html = '<ul class="messages"><li class="error-msg"><ul><li><span>' 
                + message 
                + '</span></li></ul></li></ul>';
            
            var pageTitle = Element.extend($$('.page-title').shift());
            pageTitle.insert({after: html});
        },
        reloadSlider: function() {
            var self = this;
            self.disposeSliderEvents();
            this.init(self.data.sliderInfo, self.data.urls, self.data.showElementWhenCreatingSlider);
        }
    };
})(window);
