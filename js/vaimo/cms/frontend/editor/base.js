;(function ($) {
    "use strict";

    $.widget('vaimo.cmsEditorBase', {
        options: {
            io: false,
            updateUri: false,
            loaderIndicator: false
        },
        uri: {},
        parameterPrefix: '',
        _updateHandler: undefined,
        _create: function() {
            this._updateHandler = function(data) {
                if (!data) {
                    return;
                }

                this._update(data);
            }.bind(this);
        },
        _getPost: function(data) {
            var requestParams = {};

            $.each(data, function(key, value) {
                requestParams['__' + (this.parameterPrefix ? this.parameterPrefix + '_' : '') + key] = value;
            }.bind(this));

            return requestParams;
        },
        _toggleEditMode: function(state) {
            $(document.body).toggleClass('vcms-editing-active', state);
        },
        _getInsertionPoint: function(items) {
            for (var i = 0; i < items.length; i++) {
                if (items[i].parentNode) {
                    return items[i];
                }
            }

            return $();
        },
        _update: function(data) {
        },
        _fail: function(data) {
        },
        _save: function(data, message, action) {
            var io = this.options.io;

            writeStreamHandlers = {};

            if (this.options.loaderIndicator) {
                var self = this;

                var writeStreamHandlers = {
                    open: function() {
                        self.options.loaderIndicator.show(message);
                    },
                    close: function() {
                        self.options.loaderIndicator.hide();
                    }
                }
            }

            data = this._getPost(data);

            var uri = this.options.updateUri;

            if (action && this.options.uri[action]) {
                uri = this.options.uri[action]
            }

            if (uri) {
                var stream = io.write(uri, data, writeStreamHandlers);

                if (this._updateHandler) {
                    io.read(stream, function(data) {
                        if (typeof data == 'object' && data.error) {
                            console.warn('FAILURE: ' + data.error);
                            io.trigger('error', data);

                            return;
                        }

                        return this._updateHandler.apply(this, arguments);
                    }.bind(this), {
                        error: this._fail.bind(this)
                    });
                }
            }

            return {
                instance: stream,
                data: data,
                handlers: writeStreamHandlers
            };
        }
    });
})(jQuery);