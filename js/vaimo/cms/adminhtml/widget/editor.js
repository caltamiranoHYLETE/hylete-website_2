;vaimo.widgetEditor = vaimo.extendableBaseObject.extend({
    construct: function(baseUrl, dimensions, configuration) {
        if (!baseUrl) {
            console.error('Widget viewer base-url is required');
            return;
        }

        if (!dimensions) {
            console.error('Editor area dimensions required');
            return;
        }

        if (!dimensions.width || !dimensions.height) {
            console.error('Invalid dimension configuration');
            return;
        }

        if (configuration && (!configuration.widget_type || !configuration.parameters)) {
            console.error('Invalid widget configuration format');
            return;
        }

        if (configuration) {
            var _loadOptions = WysiwygWidget.Widget.prototype.loadOptions;
            WysiwygWidget.Widget.prototype.loadOptions = function() {
                if (this.widgetEl.value != this.initiallyLoadedType) {
                    this.optionValues = {};
                }

                _loadOptions.apply(this);
            };

            var _initOptionValues = WysiwygWidget.Widget.prototype.initOptionValues;
            WysiwygWidget.Widget.prototype.initOptionValues = function() {
                _initOptionValues.apply(this);

                this.widgetEl.value = configuration.widget_type;
                this.initiallyLoadedType = configuration.widget_type;
                this.optionValues = configuration.parameters;

                this.loadOptions();
            };
        }

        Window.prototype.showCenter = function(modal) {
            this.centered = true;
            this.centerTop = 0;
            this.centerLeft = 0;
            this.width = dimensions.width;

            this.show(modal);
        };

        if (typeof(Mediabrowser) != 'undefined') {
            var _mediaBrowserInitialize = Mediabrowser.prototype.initialize;
            Mediabrowser.prototype.initialize = function() {
                _mediaBrowserInitialize.apply(this, arguments);

                var browser = document.querySelector('#browser_window_content');

                if (!browser) {
                    return;
                }

                browser.querySelector('.wrapper-popup').style.minWidth = (dimensions.width - 50) + 'px';
            };
        }

        this._openEditorDialog(baseUrl, dimensions)
    },
    _openEditorDialog: function(baseUrl, dimensions) {
        var contentRequest = {
            url: baseUrl + 'widget_target_id/block_content/?isAjax=true',
            options: {
                method: 'post'
            }
        };

        var dialogParams = {
            id: 'widget_window',
            className: 'magento',
            width: dimensions.width,
            height: dimensions.height,
            onOkCallback: this.onOkConfigurationWindow.bind(this),
            closeCallback: this.onCloseWindow.bind(this)
        };

        Dialog.confirm(contentRequest, dialogParams);
    },
    onCloseWindow: function() {
        if (Windows.windows.length > 1) {
            return;
        }

        this._closeEditor(false);
    },
    onOkConfigurationWindow: function() {
        if (!wWidget) {
            return;
        }

        var form = wWidget.formEl;

        return this._processWidgetForm(form);
    },
    _processWidgetForm: function(form) {
        var widgetOptionsForm = new varienForm(form);

        if(widgetOptionsForm.validator && !widgetOptionsForm.validator.validate()) {
            return false;
        }

        var elements = Form.getElements($(form));

        var $typeSelector = $('select_widget_type');
        var widgetParameters = {
            type: $typeSelector.value,
            name: $typeSelector[$typeSelector.selectedIndex].text.trim(),
            description: '',
            params: Form.serializeElements(elements)
        };

        if ($$('.nm').length) {
            widgetParameters.description = $$('.nm')[0].textContent.trim();
        }

        this._closeEditor(widgetParameters);

        return true;
    },
    _closeEditor: function(widgetParameters) {
        if (!window.parent || !window.parent.window) {
            return;
        }

        var receiver = window.parent.window;

        var data = {
            type: 'vcsm_widget',
            transport: widgetParameters
        };

        receiver.postMessage(data, '*');
    }
});