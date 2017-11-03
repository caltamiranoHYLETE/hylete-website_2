;(function ($) {
    'use strict';

    /**
     * Change after back-ported from M2 (3 lines)
     */
    var structureItem = $.vaimo.vcmsStructureItem;
    var gridster = Gridster;
    var __;

    /**
     * Change after back-ported from M2 (1 line)
     */
    $.widget('vaimo.cmsStructureEditor', $.vaimo.cmsEditorBase, {
        options: {
            io: false,
            history: false,
            overlayManager: false,
            widgetEditor: false,
            previewGenerator: false,
            markup: {
                editor: '<div class="vcms-ui {{CLASS}}" style="{{STYLE}}"><ul></ul></div>' +
                    '<div class="vcms-grid-placeholder vcms-hide" style="{{STYLE}}">' +
                    '<div class="vcms-grid-placeholder-item"></div></div>',
                editorItem: '<li class="vcms-ui {{CLASS}}" {{EXTRA}}>{{CONTENT}}</li>'
            },
            selectors: {
                widgetName: '.js-vcms-widget-name',
                widgetDescription: '.js-vcms-widget-description',
                container: false,
                itemTemplate: false,
                editStructureButton: false,
                toolbarButtonGroup: {
                    container: false,
                    addWidget: false,
                    addCmsBlock: false,
                    save: false,
                    cancel: false
                },
                gridster: {
                    widget: false,
                    remove: false,
                    configure: false
                }
            },
            classNames: {
                gridster: {
                    container: false,
                    widget: false,
                    actionContainer: false,
                    configure: false,
                    remove: false
                }
            },
            attributes: {
                gridItemWidgetId: 'data-widget-page-id',
                widgetPageId: 'data-vcms-widget-page-id',
                widgetType: 'data-widget-instance-type'
            },
            layoutHandle: false,
            structureDefinitions: [],
            widgetTypes: {},
            adoptWidgets: false
        },
        currentStructureOverlay: false,
        gridster: false,
        _create: function () {
            this._super();

            /**
             * Change after back-ported from M2 (3 lines)
             */
            __ = Translator.translate.bind(Translator);
            this.io = this.options.io;
            this.loaderIndicator = this.options.loaderIndicator;

            this.structureDefinitions = this.options.structureDefinitions;

            if (!this._validateRequiredOptions()) {
                return;
            }

            var selectors = this.options.selectors;

            if (this.options.widgetEditor) {
                var widgetEditor = this.options.widgetEditor;

                widgetEditor.setHandler('widget', $.proxy(function (sourceNode) {
                    if (!sourceNode) {
                        var instance = this._addWidget();
                        return instance.element;
                    }
                }, this));

                widgetEditor.setHandler('updateBefore', $.proxy(function (data, $widget) {
                    $widget.vcmsStructureItem('updateInfo', {
                        name: data.name,
                        description: data.name,
                        type: data.type
                    });
                }, this));

                widgetEditor.setHandler('resolveWidgetFromDom', $.proxy(function ($node) {
                    return $node.parents('[' + this.options.attributes.gridItemWidgetId + ']');
                }, this));

                widgetEditor.setHandler('resolveWidgetPageId', $.proxy(function ($widget) {
                    return $widget.attr('data-widget-page-id');
                }, this));

                $(selectors.toolbarButtonGroup.addWidget).click(function () {
                    widgetEditor.open();
                });
            }

            if (!this.options.layoutHandle) {
                $(selectors.editStructureButton).addClass('disabled');
            }

            this.io.addHandler('read', this._updateHandler);

            var toolbar = selectors.toolbarButtonGroup;

            this.options.history.registerAction(
                selectors.container,
                'click',
                selectors.editStructureButton,
                $.proxy(this.editStructure, this)
            );

            $(toolbar.save).click($.proxy(this.saveStructure, this));
            $(toolbar.cancel).click($.proxy(this.cancelStructure, this));
            $(toolbar.addCmsBlock).click($.proxy(this.addCmsBlock, this));

            $(selectors.container).on('click', '.vcms-grid-placeholder', $.proxy(this.addCmsBlock, this));

            $(selectors.container).on('click', selectors.gridster.remove, $.proxy(this.removeGridsterWidget, this));

            $(window).on('beforeunload', $.proxy(this._interceptPageExitWhenStructureIsOpen, this));
        },

        _getWidgetConfigByRole: function (role) {
            var config = false;
            var types = this.options.widgetTypes;

            Object.keys(types).forEach(function(typeKey) {
                if (!types[typeKey]['role']) {
                    return;
                }

                if (types[typeKey]['role'] != role) {
                    return;
                }

                config = types[typeKey];
            }.bind(this));

            return config;
        },

        addStandardContentOutputWidget: function(source, callback) {
            return this._addWidgetByRole('default_output', {
                source: source
            }, callback);
        },

        addCmsBlock: function () {
            return this._addWidgetByRole('cms');
        },

        _addWidgetByRole: function(roleCode, config, callback) {
            var typeConfig = this._getWidgetConfigByRole(roleCode);

            if (!typeConfig) {
                return;
            }

            if (!config) {
                config = {};
            }

            config.widget_type = typeConfig['type'];

            if (typeConfig['default_size']) {
                config.size_x = typeConfig['default_size'][0];
                config.size_y = typeConfig['default_size'][1];
            }

            return this._addWidget(config, callback);
        },
        _addWidget: function (config, callback) {
            config = this._prepareWidgetConfig(config);

            var $widget = this.gridster.add_widget(this._getGridsterItemHtml(config), config.size_x, config.size_y);
            this.$gridEditor.removeClass('vcms-hide');
            return this._createStructureInstance($widget, config, callback);
        },

        _createStructureInstance: function ($widget, widgetConfiguration, callback) {
            var type = widgetConfiguration.widget_type;

            callback = callback || function () {
                this.$gridEditor.removeClass('vcms-hide');
            }.bind(this);

            /**
             * This is used to get the correct type when widget editor has updated something
             */
            var $clonedWidgetInDom = $('[data-widget-clone-of="' + widgetConfiguration.widget_page_id + '"]');

            var widgetPageId = widgetConfiguration['widget_page_id'];
            var typeFromNode;

            if ($clonedWidgetInDom.length) {
                typeFromNode = $clonedWidgetInDom.data('widget-instance-type');
                widgetPageId = $clonedWidgetInDom.data('vcms-widget-page-id');
            }

            if (this.options.widgetEditor && !typeFromNode) {
                typeFromNode = this.options.widgetEditor.getTypeForId(widgetPageId);
                widgetPageId = $widget.attr(this.options.attributes.gridItemWidgetId);
            }

            if (typeFromNode) {
                type = typeFromNode;
                $widget.attr('data-widget-type', type);
            }

            var structureItemInstance = structureItem({
                pageId: widgetPageId,
                source: widgetConfiguration.source,
                type: type
            }, $widget);

            var typeKey;

            if (type) {
                typeKey = type.toLowerCase().replace(/\\/g, '_').replace(/^_/g, '').replace(/_$/g, '');
            }

            if (type && this.options.widgetTypes[typeKey]) {
                var typeData = this.options.widgetTypes[typeKey];

                structureItemInstance.updateInfo({
                    name: typeData.name,
                    description: typeData.description
                });
            }

            $('.vcms-grid-placeholder').addClass('vcms-hide');
            this.$gridEditor.addClass('vcms-with-content');

            var itemSource = structureItemInstance.getSource();

            if (itemSource) {
                this.options.previewGenerator.create(itemSource, function ($preview) {
                    structureItemInstance.setPreview($preview);
                });
            } else {
                return structureItemInstance;
            }

            callback();

            return structureItemInstance;
        },

        _getStructureDefinition: function (reference) {
            var structure = false;

            $(this.structureDefinitions).each(function (_, item) {
                if (item.reference != reference) {
                    return true;
                }

                structure = item;

                return false;
            });

            if (structure === false) {
                structure = {
                    'reference': reference
                };

                this.structureDefinitions.push(structure);
            }

            return structure;
        },

        _removeStructureDefinition: function (reference) {
            $.each(this.structureDefinitions, function (index) {
                var structure = this.structureDefinitions[index];

                if (structure.reference != reference) {
                    return true;
                }

                this.structureDefinitions.splice(index, 1);

                return false;
            }.bind(this));
        },

        editStructure: function (event, structureItems) {
            var $button = $(event.target);

            if ($button.hasClass('disabled')) {
                return;
            }

            $(this.options.selectors.container).addClass('vcms-structure-editing-active vcms-editing-active');

            this.currentStructureOverlay = this.options.overlayManager.getOriginInfoForNode(event.target);

            var structureReference = this.currentStructureOverlay.value;

            var structure = this._getStructureDefinition(structureReference);

            if (structureItems) {
                structure = $.extend({}, structure, {
                    items: structureItems
                });
            }

            this.loaderIndicator.show('Preparing the editor');

            setTimeout(function() {
                this._open(structure, function($editor) {
                    var overlay = this.currentStructureOverlay;
                    var contentItems = overlay.items;

                    contentItems.addClass('vcms-hide');

                    this._toggleEditStructureButtons(true, $button);

                    this.loaderIndicator.hide();

                    if ($editor.find('.vcms-gridster-widget').length) {
                        $editor.removeClass('vcms-hide');
                    }
                }.bind(this));
            }.bind(this), 150);
        },

        saveStructure: function (event) {
            var data = this._serialize();

            this._close();

            this.options.history.attachState(data.structure);

            var definition = this._getStructureDefinition(data.block_reference);

            if (definition && definition.items) {
                data.oldStructure = definition.items;
            }

            this._save(data, __('Saving Structure'), undefined, {
                message: 'Content placement updated'
            });
        },

        _serialize: function (extra) {
            var items = this.gridster.serialize();

            if (items.length === 0) {
                items = null;
            }

            var data = {
                handle: this.options.layoutHandle,
                structure: items
            };

            var reference = this.currentStructureOverlay.value;
            var structure = this._getStructureDefinition(reference);

            if (structure.id) {
                data.structure_id = structure.id;
            } else {
                structure.items = items;
            }

            data.block_reference = reference;

            if (extra) {
                data = jQuery.extend(data, extra);
            }

            return data;
        },

        _update: function (data) {
            if (!data['structures']) {
                return;
            }

            if (typeof data.html != 'undefined') {
                var $items = this.currentStructureOverlay.items;

                try {
                    $(data.html).insertBefore(this._getInsertionPoint($items));
                } catch (error) {
                    console.error('HTML response after saving structure have Javascript errors: ' + error.message);
                }

                $items.remove();
            }

            $.each(data['structures'], function (index, structure) {
                if (structure.id) {
                    var definition = this._getStructureDefinition(structure.reference);

                    definition.id = structure.id;
                    definition.items = structure.items;
                } else {
                    this._removeStructureDefinition(structure.reference);
                }
            }.bind(this));

            $(document).trigger('vaimoCmsAfterStructureUpdate');
        },

        cancelStructure: function (event) {
            this._close();
        },

        removeGridsterWidget: function (event) {
            var $widgetItem = $(event.target).closest(this.options.selectors.gridster.widget);

            /**
             * Disable hardcoded jQuery fade-out implemented in gridster library for this element removal
             */
            $widgetItem.fadeOut = function (onAnimationComplete) {
                onAnimationComplete();
            };

            this.gridster.remove_widget($widgetItem);

            if (!jQuery('.vcms-gridster-widget').length) {
                $('.vcms-grid-placeholder').removeClass('vcms-hide');
                $('.gridster').removeClass('vcms-with-content');
            }
        },

        _toggleEditStructureButtons: function (show, $currentButton) {
            if (show) {
                $(this.options.selectors.editStructureButton)
                    .addClass('disabled')
                    .not($currentButton);

                $currentButton.addClass('hide');

                $(this.options.selectors.toolbarButtonGroup.container).addClass('vcms-show');
            } else {
                $(this.options.selectors.editStructureButton).removeClass('disabled hide');
                $(this.options.selectors.toolbarButtonGroup.container).removeClass('vcms-show');
            }
        },

        calculateGridsterItemDimensions: function (gridsterWidth) {
            var margin = 4;
            var columnCount = 12;
            var itemHeight = 55;

            gridsterWidth = gridsterWidth - margin;
            var itemWidth = Math.floor(gridsterWidth / columnCount);
            return {margin: margin, columnCount: columnCount, itemHeight: itemHeight, itemWidth: itemWidth};
        },

        getGridsterStyles: function (gridsterWidth) {
            var __ret = this.calculateGridsterItemDimensions(gridsterWidth);
            var margin = __ret.margin;
            var columnCount = __ret.columnCount;
            var itemWidth = __ret.itemWidth;

            var gridEditorStyle = [];
            gridEditorStyle.push('width:' + (itemWidth * columnCount + margin) + 'px');

            return gridEditorStyle.join(';');
        },

        createGridsterContainer: function (gridStyles, originInfo, structureId) {
            $(this.options.markup.editor
                .replace('{{CLASS}}', this.options.classNames.gridster.container)
                .replace('{{STYLE}}', gridStyles))
                .insertBefore(originInfo.items[0]);

            this.$gridEditor = $('.gridster');
            this.$gridEditorItemsContainer = $('.gridster > ul');
            this.$gridEditorPlaceholder = $('.vcms-grid-placeholder');
            this.$gridEditor.attr(originInfo.attribute, originInfo.value);
            this.$gridEditorPlaceholder.attr(originInfo.attribute, originInfo.value);
            this.$gridEditor.attr('data-structure-id', structureId);
        },

        updateGridsterContainerPosition: function (originInfo) {
            var gridEditorOffset = this.$gridEditor.offset();

            var styleMarginLeft = parseInt(originInfo.items.css('marginLeft'));
            styleMarginLeft = styleMarginLeft ? styleMarginLeft : 0;

            var marginLeftChange = originInfo.bounds.left - gridEditorOffset.left + Math.max(0, styleMarginLeft);

            this.$gridEditor.css({
                'margin-left': marginLeftChange
            });

            this.$gridEditorPlaceholder.css({
                'margin-left': marginLeftChange
            });

            this.$gridEditor.addClass('vcms-hide');
        },

        structureItemUpdater: function(e, ui, $widget) {
            this.options.overlayManager.refresh();
            $widget.vcmsStructureItem('update');
        },

        setGridsterConfig: function (config) {
            this.gridsterConfig = {
                widget_margins: [config.margin, config.margin],
                widget_base_dimensions: [config.itemWidth - config.margin * 2, config.itemHeight],
                min_cols: config.columnCount,
                max_cols: config.columnCount,
                resize: {
                    enabled: true,
                    resize: this.structureItemUpdater.bind(this),
                    stop: this.structureItemUpdater.bind(this)
                }
            };

            return this.gridsterConfig;
        },

        _open: function (structure, doneHandler) {
            var items = structure.items || [];

            var isNewStructure = !items.length;
            var originInfo = this.currentStructureOverlay;
            var editorWidth = originInfo.bounds.width;
            var originItems = originInfo.items;

            this.createGridsterContainer(this.getGridsterStyles(editorWidth), originInfo, structure.id);
            this.updateGridsterContainerPosition(originInfo);
            this.setGridsterConfig(this.calculateGridsterItemDimensions(editorWidth));

            if (this.options.adoptWidgets) {
                this.addOrphanWidgets(structure, originInfo.items);
            }

            this.populate(items, function () {
                if (isNewStructure) {
                    var $container = $(this.options.selectors.container);
                    $container.addClass('vcms-default-content-only');

                    if (this.options.adoptWidgets) {
                        $container.addClass('vcms-hide-orphans');
                    }

                    if (!originInfo.items.filter(':visible:not(.vcms-placeholder)').length) {
                        $container.removeClass('vcms-default-content-only');
                        $container.removeClass('vcms-hide-orphans');
                    } else {
                        this.addStandardContentOutputWidget(originItems, function() {
                            $container.removeClass('vcms-default-content-only');
                            $container.removeClass('vcms-hide-orphans');
                        }.bind(this));
                    }
                }

                doneHandler(this.$gridEditor);

                originInfo.overlay.addClass('vcms-overlay-focus');

                $(this.options.selectors.gridster.widget).vcmsStructureItem('update');
            }.bind(this));
        },

        _close: function () {
            this._toggleEditStructureButtons(false);

            if (this.currentStructureOverlay.items) {
                this.currentStructureOverlay.items.removeClass('vcms-hide');
            }

            $('.vcms-overlay-focus').removeClass('vcms-overlay-focus');

            $(this.options.selectors.container).removeClass('vcms-structure-editing-active vcms-editing-active');

            if (this.gridster) {
                this.gridster.$el.parent().remove();
                $('.vcms-grid-placeholder').remove();
            }
        },

        addOrphanWidgets: function(structure, originItems) {
            var attributes = this.options.attributes;
            var orphans = originItems.filter('[' + attributes.widgetType + ']:not([data-vcms-widget-in-structure])');

            orphans.each(function(_, item) {
                var $item = $(item);
                this._addWidget({
                    widget_page_id: $item.attr(attributes.widgetPageId),
                    widget_type: $item.attr(attributes.widgetType),
                    size_x: 12,
                    size_y: 2
                }, $.noop);
            }.bind(this));
        },

        populate: function (items, completionCallback) {
            if (!items.length) {
                $('.vcms-grid-placeholder').removeClass('vcms-hide');
                this.$gridEditor.removeClass('vcms-with-content');
                this.gridster = this.$gridEditorItemsContainer.gridster(this.gridsterConfig).data('gridster');
                this.gridster.options.serialize_params = this._extendSerializeParams();

                completionCallback();
                return;
            }

            gridster.sort_by_row_and_col_asc(items).map(function (config, i) {
                var widgetTemplateHtml = this._createWidgetTemplate(config);
                var widget = this._createStructureInstance($(widgetTemplateHtml), config, $.noop);
                this.$gridEditorItemsContainer.append(widget.element);

                return widget;
            }.bind(this));

            this.gridster = this.$gridEditorItemsContainer.gridster(this.gridsterConfig).data('gridster');
            this.gridster.options.serialize_params = this._extendSerializeParams();

            completionCallback();
        },

        _createWidgetTemplate: function (config) {
            config = this._prepareWidgetConfig(config);
            return this._getGridsterItemHtml(config);
        },

        _prepareWidgetConfig: function(config) {
            return $.extend({
                size_x: 4,
                size_y: 2
            }, config || {});
        },

        _extendSerializeParams: function () {
            var oldFunction = this.gridster.options.serialize_params,
                pattern = /^data\-(.+)$/,
                persistentAttributes = ['data-col', 'data-row', 'data-sizex', 'data-sizey'];

            return function ($w, wgd) {
                var params = oldFunction($w, wgd);

                $.each($w.get(0).attributes, function (index, attr) {
                    var name = attr.nodeName;
                    if (pattern.test(name) && persistentAttributes.indexOf(name) == -1) {
                        var key = name.match(pattern)[1].replace(/-/g, '_');
                        params[key] = attr.nodeValue;
                    }
                });

                return params;
            };
        },

        _getGridsterItemHtml: function (config) {
            var dataAttributes = Object.keys(config).map(function(c) {
                return 'data-' + c.replace(/_/g, '-') + '="' + config[c] + '"';
            }).map(function(c) {
                c = c.replace('size-y', 'sizey');
                c = c.replace('size-x', 'sizex');
                return c;
            }).join(' ');

            if (!this.itemContent) {
                var $template = $(this.options.selectors.itemTemplate);
                this.itemContent = $template.length ? $template.html().trim() : '';
            }

            return $(this.options.markup.editorItem
                .replace('{{CLASS}}', this.options.classNames.gridster.widget)
                .replace('{{EXTRA}}', dataAttributes)
                .replace('{{CONTENT}}', this.itemContent));
        },

        _validateRequiredOptions: function () {
            if (!this.options.overlayManager) {
                console.error('Overlay manager not defined');
                return false;
            }

            if (!this.options.selectors.container) {
                console.error('Container selector not set');
                return false;
            }

            if (!this.options.selectors.editStructureButton) {
                console.error('Structure edit button selector not set');
                return false;
            }

            if (!this.options.classNames.gridster.container) {
                console.error('Gridster container class not set');
                return false;
            }

            return true;
        },

        _interceptPageExitWhenStructureIsOpen: function () {
            if (!$(this.options.selectors.toolbarButtonGroup.container).hasClass('vcms-show')) {
                return;
            }

            return __('You are currently editing a structure. All changes you have made will be lost.');
        }
    });
})(jQuery);