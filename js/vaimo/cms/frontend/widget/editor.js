;(function ($) {
    "use strict";

    $.widget('vaimo.cmsWidgetEditor', $.vaimo.cmsEditorBase, {
        options: {
            io: false,
            history: false,
            overlayManager: false,
            editorViewUri: false,
            updateUri: false,
            requestIdAttribute: 'page_id',
            name: false,
            attributes: {
                id: 'data-vcms-widget-page-id',
                params: 'data-widget-parameters',
                type: 'data-widget-instance-type',
                parents: []
            },
            dialogSize: {
                width: 750,
                height: 550
            },
            selectors: {
                editButton: false,
                container: ''
            },
            handlers: {
                updateBefore: false,
                widget: false,
                resolveWidgetFromDom: false,
                resolveWidgetPageId: false
            },
            storeId: null
        },
        parameterPrefix: 'widget',
        _create: function() {
            this._super();

            if (!this._validateRequiredOptions()) {
                return;
            }

            var that = this;
            var selectors = this.options.selectors;

            var editAction = function() {
                var $widget = that._resolveWidgetFromDomNode(this);

                if (!$widget.length) {
                    console.error('Can not resolve overlay to widget to open the editor');
                    return
                }

                that.open($widget);
            };

            if (this.options.history) {
                this.options.history.registerAction(selectors.container, 'click', selectors.editButton, editAction);
            } else {
                $(selectors.container).on('click', selectors.editButton, editAction);
            }
        },
        _resolveWidgetFromDomNode: function(node) {
            var $widget;
            var overlayManager = this.options.overlayManager;

            if (this.options.handlers.widget) {
                $widget = this.options.handlers.widget(node);

                if ($widget) {
                    $widget = $widget.element ? $($widget.element) : $widget;
                }

                if ($widget && $widget.length) {
                    return $widget;
                }
            }

            if (overlayManager) {
                var originInfo = overlayManager.getOriginInfoForNode(node);

                $widget = originInfo.items;
            } else {
                $widget = $(node).parents('[' + this.options.attributes.id + ']');

                if (!$widget.length && this.options.handlers.resolveWidgetFromDom) {
                    $widget = this.options.handlers.resolveWidgetFromDom($(node));
                }

                if (!$widget.length) {
                    $widget = $(node).parents('[' + this.options.attributes.params + ']');
                }
            }

            return $widget;
        },
        getTypeForNode: function($node) {
            var id = $node.attr(this.options.attributes.id);

            return this.getTypeForId(id);
        },
        getTypeForId: function(id) {
            if (!id) {
                return false;
            }

            var $widgetInDom = $('[' + this.options.attributes.id + '="' + id + '"][' + this.options.attributes.type + ']');

            return $widgetInDom.attr(this.options.attributes.type);
        },
        setHandler: function(name, handler) {
            if (name in this.options.handlers) {
                this.options.handlers[name] = handler;
            }
        },
        open: function($widget) {
            var idAttributeName = this.options.attributes.id;
            var paramsAttributeName = this.options.attributes.params;
            var parentAttributeNames = this.options.attributes.parents;

            var dialogSize = this.options.dialogSize;

            var handlers = this.options.handlers;

            var data = {};

            data['dimensions'] = dialogSize.width + '-' + dialogSize.height;

            if (this.options.storeId) {
                data['store'] = this.options.storeId;
            }

            if ($widget) {
                if (!$widget.length) {
                    return;
                }

                var id = $widget.attr(idAttributeName);

                if (!id && handlers.resolveWidgetPageId) {
                    id = handlers.resolveWidgetPageId($widget);
                }

                if (id) {
                    data[this.options.requestIdAttribute] = id;
                }

                if ($widget.attr(paramsAttributeName)) {
                    var parameters = $widget.attr(paramsAttributeName);
                    data['configuration'] = encodeURIComponent(parameters);
                }

                if (parentAttributeNames) {
                    parentAttributeNames.forEach(function(parentAttributeName) {
                        var parentValue = $widget.parents('[' + parentAttributeName + ']').attr(parentAttributeName);

                        if (!parentValue) {
                            return;
                        }

                        var parentValueRequestVar = parentAttributeName.replace(/^data-/,'').replace(/-/g,'_');

                        data[parentValueRequestVar] = parentValue;
                    });
                }
            }

            var requestUrl = this.options.editorViewUri;
            $.each(data, function(key, value) {
                requestUrl += (requestUrl.slice(-1) != '/' ? '/' : '') + key + '/' + value;
            });

            var widgetEditor = this;
            var colorBoxOptions = {
                href: requestUrl,
                iframe: true,
                speed: 50,
                scrolling: false,
                innerWidth: dialogSize.width,
                innerHeight: dialogSize.height
            };

            if (this.options.name) {
                colorBoxOptions.onComplete = function() {
                    setTimeout(function() {
                        $('.cboxIframe').attr('name', widgetEditor.options.name);
                    }, 200);
                };
            }

            var messageName = 'message';
            var messageType = 'vcsm_widget';

            var configReceiver = function(message) {
                if (message.data == false) {
                    return;
                }

                if (typeof message.data != 'object') {
                    return;
                }

                if (message.data.type !== messageType) {
                    return;
                }

                var transport = message.data.transport;

                $(window).colorbox.close();

                window.removeEventListener(messageName, configReceiver);

                if (!transport) {
                    return;
                }

                if (transport.params.trim() == '') {
                    return;
                }

                if (!$widget) {
                    $widget = widgetEditor._resolveWidgetFromDomNode();
                }

                if (widgetEditor.options.handlers.updateBefore) {
                    widgetEditor.options.handlers.updateBefore(transport, $widget);
                }

                if (widgetEditor.options.updateUri) {
                    var data = {};

                    if (parentAttributeNames) {
                        parentAttributeNames.forEach(function(parentAttributeName) {
                            var parentValue = $widget.parents('[' + parentAttributeName + ']').attr(parentAttributeName);

                            if (parentValue) {
                                var parentValueRequestVar = parentAttributeName.replace(/^data-/,'').replace(/-/g,'_');
                                data[parentValueRequestVar] = parentValue;
                            }
                        });
                    }

                    data['parameters'] = transport.params;
                    data[widgetEditor.options.requestIdAttribute] = id;

                    widgetEditor._save(data, Translator.translate('Saving Widget'));
                } else {
                    var targetId = $widget.attr(idAttributeName);

                    if (!targetId && handlers.resolveWidgetPageId) {
                        targetId = handlers.resolveWidgetPageId($widget);
                    }

                    if (targetId == id) {
                        $widget.attr(paramsAttributeName, transport.params);
                    } else {
                        console.error('Widget wrapper (id=' + id + ') not found and no updateUri ' +
                        'defined for storing the widget parameters');
                    }
                }
            };

            colorBoxOptions.onClosed = function() {
                window.removeEventListener(messageName, configReceiver);
            };

            $.colorbox(colorBoxOptions);

            window.addEventListener(messageName, configReceiver, false);
        },
        _update: function(response) {
            var idAttributeName = this.options.attributes.id;

            if (!response.items) {
                console.error('Response does not contain required values');
                return;
            }

            var attribute = this.options.requestIdAttribute;

            response.items.forEach(function(item) {
                if (!item[attribute]) {
                    console.error('Response item does not contain required widget id key (' + attribute + ')');
                    return;
                }

                var id = item[attribute];

                var $target = $('[' + idAttributeName + '="' + id + '"]');

                $(item.html).insertBefore(this._getInsertionPoint($target));

                $target.remove();
            }.bind(this));
        },
        _validateRequiredOptions: function() {
            if (!this.options.editorViewUri) {
                console.error('Controller URI not set');
                return false;
            }

            if (!this.options.selectors.editButton) {
                console.error('Widget edit button selector not set');
                return false;
            }

            return true;
        }
    });
})(jQuery);