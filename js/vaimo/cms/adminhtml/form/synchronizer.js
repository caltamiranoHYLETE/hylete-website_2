;vaimo.formFieldSynchronizer = vaimo.extendableBaseObject.extend({
    _inputs: false,
    _selectors: '',
    _excludedNames: [],
    construct: function(scope, excludedNames) {
        var elements = ['input', 'select', 'textarea'];
        var selectors = [];

        if (scope) {
            if (scope instanceof Array === false) {
                scope = [scope];
            }

            $(scope).each(function(selector) {
                $(elements).each(function(element) {
                    selectors.push(selector + ' ' + element);
                });
            });
        } else {
            selectors = elements;
        }

        this._selectors = selectors.join(',');

        if (excludedNames) {
            this._excludedNames = excludedNames;
        }

        this._initiate();

        var synchronizer = this;
        Ajax.Responders.register({
            onComplete: function() {
                setTimeout(function() {
                    synchronizer._initiate()
                }, 25);
            }
        });
    },
    _getNameKey: function(input) {
        var usedName = input.name;

        if (!usedName || usedName === '') {
            return;
        }

        var affectedValueElementId, field;

        if (usedName == 'use_default[]') {
            affectedValueElementId = input.id.replace(/_default$/,'');
            field = $(affectedValueElementId);

            if (!field) {
                return;
            }
        }

        if (usedName == 'use_config[]') {
            affectedValueElementId = input.id.replace(/^use_config_/,'');
            field = $(affectedValueElementId);

            if (!field) {
                return;
            }
        }

        if (field) {
            usedName += '_' + field.name;
        }

        return usedName;
    },
    _collectionInputsData: function() {
        var inputs = {};

        var getNameKey = this._getNameKey;
        $$(this._selectors).each(function(input) {
            var nameKey = getNameKey(input);

            if (!nameKey) {
                return;
            }

            if (nameKey in inputs) {
                inputs[nameKey].push(input.id);
            } else {
                inputs[nameKey] = [input.id];
            }
        });

        this._inputs = inputs;
    },
    _initiate: function() {
        this._collectionInputsData();

        var changeHandler = this._onValueChange.bindAsEventListener(this);
        var excludedNames = this._excludedNames;

        $H(this._inputs).each(function(pair) {
            if (pair.value.length > 1) {
                $(pair.value).each(function(id) {
                    var element = $(id);

                    if (!element) {
                        return;
                    }

                    if (excludedNames.indexOf(element.name) >= 0) {
                        return;
                    }

                    if (!element.hasClassName('vcms-synchronized')) {
                        element.observe('change', changeHandler);
                        element.addClassName('vcms-synchronized');
                    }
                });
            }
        });
    },
    _onValueChange: function(event) {
        var target = event.target;
        var newValue = target.getValue();

        var nameKey = this._getNameKey(target);

        this._inputs[nameKey].each(function(id) {
            if (id == target.id) {
                return;
            }

            var $element = $(id);

            if ($element.type == 'checkbox') {
                if (target.checked != $element.checked) {
                    $element.click();
                }

                return;
            }

            $element.setValue(newValue);
            document.getElementById(id).value = newValue;
        });
    }
});