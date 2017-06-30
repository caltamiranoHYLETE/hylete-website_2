var _onAjaxSuccess = undefined;
var _switchContainer = WysiwygWidget.Widget.prototype.switchOptionsContainer;
var _initOptionValues = WysiwygWidget.Widget.prototype.initOptionValues;
var _loadOptions = WysiwygWidget.Widget.prototype.loadOptions;

var VaimoMenuCategoryWidget = _vaimoExtendableBase.extend({
    _widgetName: 'Widget editor',
    _baseUrl: '',
    _captionTarget: false,
    _configurationButton: false,
    _configurationContainer: false,
    _toggleValueElements: false,

    construct: function(baseUrl) {
        this._baseUrl = baseUrl;

        WysiwygWidget.Widget.prototype.switchOptionsContainer = function(contentId) {
            _switchContainer.apply(this, [contentId]);
            if (typeof Dialog.fixDialogSize != 'undefined') {
                Dialog.fixDialogSize();
            }
        };

        /**
         * Changing the type can corrupt variable like 'template' (which almost every widget has) or any other hidden
         * and overlapping variable value
         */
        WysiwygWidget.Widget.prototype.loadOptions = function() {
            if (this.widgetEl.value != this.initiallyLoadedType) {
                this.optionValues = {};
            }

            _loadOptions.apply(this);
        };

        if (!this._toggleValueElements) {
            this._toggleValueElements = toggleValueElements;
            toggleValueElements = function(checkbox, container, excludedElements, checked) {
                var $container = $(container);
                var $checkbox = $(checkbox);
                var $data = $container.down('.widget-attribute');
                this._toggleValueElements(checkbox, container, excludedElements, checked);

                if ($data) {
                    var $removeButton = $container.down('.widget-remove-button');
                    var $configurationButton = $container.down('.widget-configuration-button');
                    var hasWidget = $data.value.search('widget_type') >= 0;

                    if (!$checkbox.checked && hasWidget) {
                        $removeButton.addClassName('delete');
                        $removeButton.removeClassName('disabled');
                    } else {
                        $removeButton.removeClassName('delete');
                        $removeButton.addClassName('disabled');
                    }

                    if (!$checkbox.checked) {
                        if (hasWidget) {
                            $configurationButton.addClassName('success');
                        } else {
                            $configurationButton.addClassName('add');
                        }
                    } else {
                        $configurationButton.removeClassName('success');
                        $configurationButton.removeClassName('add');
                    }
                }
            }.bind(this);
        }
    },
    remove: function(configurationContainer, instanceId) {
        var $attributeValue = $(configurationContainer);

        $attributeValue.setValue('instance_id=' + instanceId);
        var $container = $attributeValue.up('.value');

        var configurationButton = $container.down('.widget-configuration-button');
        var removeButton = $container.down('.widget-remove-button');
        var captionElementSelection = $container.down('.btn-widget-label');

        configurationButton.addClassName('add');
        configurationButton.removeClassName('success');
        configurationButton.down('span').down('span').down('span').update('Add');

        removeButton.addClassName('disabled');
        removeButton.removeClassName('delete');

        var $caption = $(captionElementSelection);
        $caption.update('');
        $caption.hide();
    },
    decodeURIComponent: function(value) {
        try {
            return decodeURIComponent(value);
        } catch (e) {
            return value;
        }
    },
    configure: function(configurationContainer, instanceId) {
        var $attributeValue = $(configurationContainer);
        var $container = $attributeValue.up('.value');

        this._configurationContainer = $attributeValue;
        this._captionTarget = $container.down('.btn-widget-label');
        this._configurationButton = $container.down('.widget-configuration-button');
        this._removeButton = $container.down('.widget-remove-button');

        if (this._baseUrl) {
            var browserControllerUrl = this._baseUrl + 'widget_target_id/block_content/?isAjax=true';

            var contentRequest = {
                url: browserControllerUrl,
                options: {
                    method: 'post'
                }
            };

            if (this._configurationContainer) {
                var configuration = this._configurationContainer.value;
                var vaimoWidgetEditor = this;

                WysiwygWidget.Widget.prototype.initOptionValues = function() {
                    if (!_initOptionValues.apply(this)) {
                        var _configuration = configuration.split("&");
                        var parameters={};
                        this.optionValues = new Hash({});

                        $(_configuration).each(function(item) {
                            var _kvp = item.split('=');
                            if (_kvp.length > 1) {
                                _kvp[0] = vaimoWidgetEditor.decodeURIComponent(_kvp[0]);
                                _kvp[1] = vaimoWidgetEditor.decodeURIComponent(_kvp[1]);

                                if (_kvp[0] == 'widget_type') {
                                    this.widgetEl.value = _kvp[1];
                                    this.initiallyLoadedType = this.widgetEl.value;
                                }

                                if (_kvp[0].substring(0, 10) == 'parameters') {
                                    var variable = _kvp[0].replace('[]','').replace('[','[\'').replace(']','\']');
                                    var value = '\'' + _kvp[1] + '\'';

                                    /**
                                     * Assign the value to the params variable
                                     */
                                    eval(variable + '=' + value);
                                }
                            }
                        }.bind(this));

                        $(Object.keys(parameters)).each(function(key) {
                            this.optionValues.set(key, parameters[key]);
                        }.bind(this));

                        this.loadOptions();
                    }
                };
            }

            var dialogParams = {
                id: 'widget_editor',
                className:'magento',
                title: this._widgetName,
                width: 950,
                height: 500,
                top: 50,
                zIndex: 1000,
                recenterAuto: false,
                minimizable: false,
                maximizable: false,
                showEffectOptions: {duration:0.4},
                hideEffectOptions: {duration:0.4},
                onOkCallback: this.onOkConfigurationWindow.bind(this),
                onContentLoadCallback: this.onDialogContentLoad.bind(this),
                onClose: function() {
                    Dialog.parameters = undefined;
                    dialogParams = {};
                },
                vaimoMenuDialog: true,
                widgetInstanceId: instanceId
            };

            Dialog.confirm(contentRequest, dialogParams);
        }
    },
    onDialogContentLoad: function(dialog) {
        $(dialog.element).down('#insert_button').hide();
        $(dialog.element).down('.content-header').hide();
        $(dialog.element).down('.magento_message').setStyle({marginTop: '5px'});

        if (typeof widgetTools != 'undefined' && _onAjaxSuccess == undefined && Dialog.fixDialogSize) {
            _onAjaxSuccess = widgetTools.onAjaxSuccess;
            widgetTools.onAjaxSuccess = function(transport) {
                _onAjaxSuccess(transport);
                setTimeout(Dialog.fixDialogSize, 100);
            };
        }
    },
    onOkConfigurationWindow: function(dialog) {
        var form = wWidget.formEl;
        var widgetOptionsForm = new varienForm(form);
        if(widgetOptionsForm.validator && widgetOptionsForm.validator.validate() || !widgetOptionsForm.validator) {
            var formElements = [];
            Form.getElements($(form)).each(function(element) {
                if(!element.hasClassName('skip-submit')) {
                    formElements.push(element);
                }
            });

            var params = Form.serializeElements(formElements);
            if (dialog && dialog.options.widgetInstanceId) {
                params = 'instance_id=' + dialog.options.widgetInstanceId + '&' + params;
            }

            var $widgetTypeSelector = $('select_widget_type');
            if (this._captionTarget) {
                var $caption = this._captionTarget;
                $caption.update($widgetTypeSelector[$widgetTypeSelector.selectedIndex].text);
                $caption.show();
            }

            if (this._configurationButton) {
                var $button = this._configurationButton;
                $button.addClassName('success');
                $button.removeClassName('add');
                $button.down('span').down('span').down('span').update('Configure');
            }

            if (this._removeButton) {
                var $removeButton = this._removeButton;
                $removeButton.addClassName('delete');
                $removeButton.removeClassName('disabled');
                $removeButton.enable();
            }

            if (this._configurationContainer) {
                this._configurationContainer.setValue(params);
            }

            return true;
        }
        return false;
    }
});