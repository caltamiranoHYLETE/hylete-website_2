;(function ($) {
    "use strict";

    var updateCounter = 0;

    $.widget('vaimo.cmsWysiwygEditor', $.vaimo.cmsEditorBase, {
        options: {
            overlayManager: false,
            urlUtils: false,
            uriSave: null,
            fileManager: {
                uriPublic: null,
                uriAction: null,
                uriIcon: null
            },
            selectors: {
                editButton: false,
                editableBlocks: '',
                container: ''
            },
            imageEditor: {
                uriSave: null
            },
            attributes: {
                id: 'block-id',
                parents: []
            },
            editor: {
                classOptions: {}
            },
            protectedParams: []
        },
        _create: function() {
            this._super();

            updateCounter = 0;

            var selectors = this.options.selectors;

            this._initRaptor(selectors.editableBlocks);

            $(window).on('beforeunload', this._interceptPageExitWhenStructureIsOpen);

            if ('MutationObserver' in window) {
                var observer = new MutationObserver($.proxy(this._onDomMutation, this));

                observer.observe(document.querySelector(selectors.container), {
                    'subtree':true,
                    'childList': true
                });
            }

            $(selectors.container).on('click', selectors.editButton, $.proxy(this._onEnableEditing, this));

            this.options.io.addHandler('read', this._removeDisconnectedRaptorInstances);
        },
        _removeDisconnectedRaptorInstances: function() {
            var raptorInstances = raptor.fn.raptor.Raptor.instances;
            var raptorInstanceCount = raptorInstances.length;

            for (var i = 0; i < raptorInstanceCount; i++) {
                var instance = raptorInstances[i];

                if (raptor.contains(document, instance.getElement()[0]) !== false) {
                    continue;
                }

                raptorInstances.splice(i, 1);

                i--;
                raptorInstanceCount--;
            }
        },
        _onEnableEditing: function(event) {
            var originInfo = this.options.overlayManager.getOriginInfoForNode(event.target);

            var $content = originInfo.items;

            if (!$content.length) {
                console.error('Can not resolve overlay to widget to open the editor');
                return
            }

            this._toggleEditMode(true);

            var contentId = $content.attr('id');
            originInfo.overlay.addClass('hide editing-active');

            this._prepareContent(contentId);

            raptor('#' + contentId).raptor('enableEditing');
        },
        _prepareContent: function(contentId) {
            var elements = document.querySelectorAll('#' + contentId + ' a');

            var urlUtils = this.options.urlUtils;
            var protectedParams = this.options.protectedParams;

            urlUtils.walkLinkElements(elements, function(url) {
                return urlUtils.stripCurrentBaseUrl(
                    protectedParams.reduce(
                        urlUtils.removeParam.bind(urlUtils),
                        url
                    )
                );
            });

            var raptorInstances = raptor.fn.raptor.Raptor.instances;

            for (var i = 0; i < raptorInstances.length; i++) {
                var instance = raptorInstances[i];

                if (instance.getElement().attr('id') != contentId) {
                    continue;
                }

                instance.saved();
            }
        },
        _onDomMutation: function(recordQueue) {
            var initEditMode = this._initRaptor.bind(this);

            var selector = this.options.selectors.editableBlocks + ':not([data-raptor-initialised])';

            recordQueue.forEach(function(mutation) {
                var nodes = mutation.addedNodes;

                if (!nodes.length) {
                    return;
                }

                for (var i = 0; i < nodes.length; ++i) {
                    var $item = $(nodes[i]);

                    var matches = [];

                    if ($item.is(selector)) {
                        matches = [$item];
                    } else {
                        matches = $item.find(selector);
                    }

                    if (!matches.length) {
                        continue;
                    }

                    initEditMode(matches);
                }
            });
        },
        _getSavePluginConfiguration: function() {
            var options = this.options;

            var configuration = {
                url: options.uriSave,
                postName: 'updates'
            };

            configuration.data = function(html) {
                var $node = this.raptor.getElement();

                var data = {
                    block_id: $node.data(options.attributes.id),
                    content: html
                };

                $.each(options.attributes.parents, function() {
                    var parentAttributeName = this;
                    var parentValue = $node.parents('[data-' + parentAttributeName + ']').data(parentAttributeName);

                    if (!parentValue) {
                        parentValue = $node.data(parentAttributeName);
                    }

                    if (parentValue) {
                        var parentValueRequestVar = parentAttributeName.replace(/-/g,'_');
                        data[parentValueRequestVar] = parentValue;
                    }
                });

                return data;
            };

            configuration.id = $.proxy(function() {
                return updateCounter++;
            }, this);

            var saveInfo = {};
            configuration.post = function(data) {
                saveInfo = this._save(data, Translator.translate('Saving Content'));

                updateCounter = 0;

                saveInfo.handlers.open();

                return options.io.trigger('write', saveInfo.data);
            }.bind(this);

            configuration.formatResponse = function(response) {
                if (saveInfo.handlers) {
                    saveInfo.handlers.close();
                }

                if (response.error && response.trace) {
                    console.error(response.trace);
                }

                options.io.trigger('read', response);
            }.bind(this);

            return configuration;
        },
        _showEditButton: function() {
            $('.editing-active').removeClass('hide editing-active');
            $('.raptor-layout-element-hover-panel').hide();

            this._toggleEditMode(false);
        },
        _initRaptor: function(editableBlocksSelector) {
            var options = this.options;

            var saveHandler = this._getSavePluginConfiguration();

            var bindings = {
                cancel: this._showEditButton.bind(this),
                disabled: this._showEditButton.bind(this)
            };

            raptor(function($) {
                $(editableBlocksSelector).raptor({
                    classes: options.editor.classOptions,
                    bind: bindings,
                    draggable: false,
                    plugins: {
                        classMenu: Object.keys(options.editor.classOptions).length > 0,
                        alignJustify: false,
                        languageMenu: false,
                        tagMenu: true,
                        logo: false,
                        snippetMenu: false,
                        statistics: false,
                        revisions: false,
                        fontFamilyMenu: false,
                        textSuper: false,
                        textSub: false,
                        textSizeIncrease: false,
                        textSizeDecrease: false,
                        linkCreate: {
                            baseUrl: options.urlUtils.getBaseUrl()
                        },
                        save: {
                            plugin: 'saveJson'
                        },
                        dock: {
                            docked: true,
                            dockToElement: false
                        },
                        saveJson: saveHandler,
                        fileManager: options.fileManager,

                        /**
                         * This will result in odd data-structure (double uriSave), but that seems to be
                         * the format that Raptor's imageEditor expects you to provide.
                         */
                        imageEditor: {
                            uriSave: options.imageEditor
                        }
                    },
                    cssPrefix: 'vcms-'
                });
            });
        },
        _interceptPageExitWhenStructureIsOpen: function() {
            if (!$('.raptor-editable-block').hasClass('raptor-editing')) {
                return;
            }

            return Translator.translate('You are currently editing a CMS Block. All changes you have made will be lost.');
        }
    });
})(jQuery);