;(function ($) {
    'use strict';

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

            this.showNotification = function (type, message) {
                $.notify(message, {
                    className: type,
                    globalPosition: 'bottom right',
                    showAnimation: 'fadeIn',
                    hideAnimation: 'fadeOut'
                });
            }
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
        _fail: function(data, rawResponse) {
            if (typeof data === 'object' && data.error) {
                this.showNotification('error', data.error);
                return;
            }

            if (rawResponse.substr(0, 15) !== '<!doctype html>') {
                return;
            }

            if (rawResponse.search('<meta') >= 0) {
                this.showNotification('error', 'User logged out');
                return;
            }

            this.showNotification('error', 'Unexpected response');
        },
        _save: function(data, message, action, confirmationConfig) {
            var io = this.options.io;

            if (confirmationConfig === undefined) {
                confirmationConfig = {};
            }

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

            var showNotification = function() {};

            if (Object.keys(data).length > 0) {
                showNotification = this.showNotification
            }

            if (uri) {
                var stream = io.write(uri, data, writeStreamHandlers);

                if (this._updateHandler) {
                    io.read(stream, function(data) {
                        if (typeof data == 'object' && data.error) {
                            showNotification('error', data.error);
                            console.warn('FAILURE: ' + data.error);

                            io.trigger('error', data);

                            return;
                        }

                        try {
                            var result = this._updateHandler.apply(this, arguments);

                            showNotification(
                                confirmationConfig.type || 'success',
                                confirmationConfig.message || 'Page content updated'
                            );
                        } catch (e) {
                            showNotification('error', e.message);
                        }

                        return result;
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