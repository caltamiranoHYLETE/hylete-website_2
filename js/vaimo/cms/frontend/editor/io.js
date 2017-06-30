;(function ($) {
    "use strict";

    $.widget('vaimo.cmsEditorIO', {
        _create: function() {
            this.handlers = {
                write: [],
                read: [],
                error: []
            };
        },
        write: function(uri, data, handlers) {
            data = this.trigger('write', data);

            var request = {
                type: 'POST',
                url: uri,
                data: data,
                cache : false,
                dataType: 'json'
            };

            if (handlers && handlers.open) {
                request.beforeSend = handlers.open;
            }

            var connection = $.ajax(request);

            if (handlers && handlers.close) {
                connection.always(handlers.close);
            }

            return connection;
        },
        read: function(connection, handler, handlers) {
            if (handlers) {
                if (handlers.open) {
                    handlers.open();
                }

                if (handlers.close) {
                    connection.always(handlers.close);
                }
            }

            connection.done(function(transport) {
                handler(transport);
                
                this.trigger('read', transport, handler);
            }.bind(this));

            connection.error(function(request) {
                try {
                    if (handlers) {
                        handlers.error();
                    }

                    var transport = JSON.parse(request.responseText);

                    if (transport.error) {
                        this.trigger('error', transport);

                        return;
                    }
                } catch (e) {}

                console.error('Error encountered on read');
            }.bind(this));

            return connection;
        },
        addHandler: function(type, callable) {
            this.handlers[type].push(callable);
        },
        trigger: function(type, data, skip) {
            $.each(this.handlers[type], function() {
                if (this === skip) {
                    return;
                }

                var extra = this.apply(this, [data]);

                if (extra) {
                    data = $.extend(true, data, extra);
                }
            });

            return data;
        }
    });
})(jQuery);