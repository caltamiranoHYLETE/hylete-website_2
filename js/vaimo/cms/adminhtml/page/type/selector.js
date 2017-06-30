;vaimo.cmsPageTypeSelector = vaimo.extendableBaseObject.extend({
    _sourceSelector: false,
    _targetSelector: false,
    _source: false,
    _target: false,
    _tabsVisibilityMap: false,
    construct: function(sourceSelector, targetSelector, tabsVisibilityMap) {
        if (!sourceSelector) {
            console.error('Source selector not defined');
            return;
        }

        if (!targetSelector) {
            console.error('Target selector not defined');
            return;
        }

        this._sourceSelector = sourceSelector;
        this._targetSelector = targetSelector;

        if (tabsVisibilityMap && tabsVisibilityMap instanceof Array) {
            if (tabsVisibilityMap.length) {
                var tabsVisibilityMapObject = {};

                tabsVisibilityMap.forEach(function(value, index) {
                    tabsVisibilityMapObject[index] = value;
                });

                tabsVisibilityMap = tabsVisibilityMapObject;
            } else {
                tabsVisibilityMap = false;
            }
        }

        this._tabsVisibilityMap = tabsVisibilityMap;

        if (!this._initiate()) {
            return;
        }

        var selector = this;
        Ajax.Responders.register({
            onComplete: function() {
                selector._initiate();

                setTimeout(function() {
                    if (!$$('#category_info_tabs li .active').findAll(function(el) { return el.up('li').visible(); }).length) {
                        selector._activateFirstTab();
                    }

                    selector._synchronizeSourceAndTarget();
                }, 20);
            }
        });
    },
    _initiate: function() {
        var source = $$(this._sourceSelector);
        var target = $$(this._targetSelector);

        if (!source.length) {
            console.error('Entity for selector (' + this._sourceSelector + ') not found');
        }

        this._source = $(source[0]);

        if (!target.length) {
            console.error('Entity for selector (' + this._targetSelector + ') not found');
        }

        this._target = $(target[0]);

        if (!this._source || !this._target) {
            return false;
        }

        if (!this._source.hasClassName('initiated')) {
            this._synchronizeSourceAndTarget();
            this._source.addClassName('initiated');
            this._source.observe('change', this._onSourceChange.bindAsEventListener(this));
        }

        return true;
    },
    _synchronizeSourceAndTarget: function() {
        this._updateTargetValue(this._source.value);
    },
    _onSourceChange: function(event) {
        this._updateTargetValue(event.target.value);
    },
    _updateTargetValue: function(newValue) {
        this._target.setValue(newValue);

        if (this._tabsVisibilityMap) {
            var show = this._tabsVisibilityMap[newValue];
            var activeTabNoLongerAvailable = false;

            $H(this._tabsVisibilityMap).each(function(pair) {
                var shouldBeVisible = (pair.key == newValue);

                $(pair.value).each(function(value) {
                    if (shouldBeVisible || show.indexOf(value) >= 0) {
                        $(value).up('li').show();
                    } else {
                        if ($(value).hasClassName('active') || $(value + '_content').visible()) {
                            $(value).removeClassName('active');
                            $(value + '_content').hide();
                            activeTabNoLongerAvailable = true;
                        }

                        $(value).up('li').hide();
                    }
                });
            });

            if (activeTabNoLongerAvailable) {
                this._activateFirstTab();
            }
        }
    },
    _activateFirstTab: function() {
        $$('#category_info_tabs li .active').findAll(function(el) { return el.removeClassName('active'); });
        var visibleItems = $$('#category_info_tabs li').findAll(function(el) { return el.visible(); });

        var itemLink = $(visibleItems[0]).select('.tab-item-link');

        var id = itemLink[0].id;

        $(id).addClassName('active');
        $(id + '_content').show();
    }
});