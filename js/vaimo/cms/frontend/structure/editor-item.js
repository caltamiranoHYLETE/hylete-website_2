;(function ($) {
    'use strict';

    /**
     * Change after back-ported from M2 (1 line)
     */
    var __;

    $.widget('vaimo.vcmsStructureItem', {
        options: {
            pageId: '',
            name: undefined,
            description: undefined,
            type: '',
            source: null,
            selectors: {
                widgetName: '.js-vcms-widget-name',
                widgetDescription: '.js-vcms-widget-description'
            }
        },
        _create: function() {
            /**
             * Change after back-ported from M2 (1 line)
             */
            __ = Translator.translate.bind(Translator);

            if (this.options.source) {
                this.source = this.options.source;
            }

            if (this.options.pageId) {
                this.source =  $('[data-vcms-widget-page-id=' + this.options.pageId + ']:not(.vcms-gridster-widget)');
            }

            this.originalType = this.options.type;

            this.element.on('mouseenter mouseleave', function(e) {
                var isActive = e.type === 'mouseenter';
                this.element.toggleClass('vcms-active', isActive);
            }.bind(this));

            if (!this.options.pageId) {
                this.element.addClass('vcms-new-structure-item');
            }

            var name = this.options.name || ('Widget' + (this.options.pageId ? ' (page_id=' + this.options.pageId + ')': ''));
            var description = this.options.description || 'Unknown widget type';

            this.setName(name);
            this.setDescription(description);
        },
        getSource: function() {
            return this.source;
        },
        setName: function (value) {
            var nameContainer = this.element.find(this.options.selectors.widgetName);

            if (!this.options.pageId) {
                value = __('New ') + value;
            }

            if (nameContainer.length) {
                nameContainer.text(value);
            }
        },
        setDescription: function (value) {
            var descriptionContainer = this.element.find(this.options.selectors.widgetDescription);

            if (descriptionContainer.length) {
                descriptionContainer.text(value);
            }
        },
        setWidgetType: function(type) {
            this.element.attr('data-widget-type', type);
        },
        updateInfo: function(info) {
            this.setName(info.name);
            this.setDescription(info.description);

            if (info.type) {
                this.setWidgetType(info.type);
            }
        },
        update: function() {}
    });

    /**
     * Preview
     */
    $.widget('vaimo.vcmsStructureItem', $.vaimo.vcmsStructureItem, {
        options: {
            selectors: {
                previewArea: '.js-vcms-preview-area'
            },
            scalePreviewFromCentre: false,
            autoResolveCentreScaling: true
        },
        _create: function () {
            this._super();

            this.extraTransform = '';

            var $previewArea = this.element.find(this.options.selectors.previewArea);

            if ($previewArea.length) {
                this.previewArea = $previewArea[0];
            }

            if (!this.source) {
                return;
            }

            this.height = this.source.filter(':visible').height();
            this.width = this.source.filter(':visible').width();
        },

        update: function () {
            this._super();

            this.updatePreviewSize();
        },

        updatePreviewSize: function () {
            if (!this.preview) {
                return;
            }

            this.previewArea.style.transform = 'scale(' +
                Math.max(1, this.element.width() / this.width, this.element.height() / this.height) +
            ')' + this.extraTransform;
        },

        setWidgetType: function(type) {
            this._superApply(arguments);

            if (!this.preview) {
                return;
            }

            this.preview.toggle(type == this.originalType);
        },

        setPreview: function(preview) {
            this.preview = preview ? $(preview) : preview;

            if (this.options.autoResolveCentreScaling && preview.length > 0) {
                var elementsAlignFromCentreCount = 0;

                preview.each(function(_, element) {
                    if (!element.classList.contains('vcms-preview-static-styles')) {
                        return;
                    }

                    if (element.style.textAlign != 'center') {
                        return;
                    }

                    elementsAlignFromCentreCount++;
                });

                this.options.scalePreviewFromCentre = elementsAlignFromCentreCount/preview.length > 0.5;
            }

            if (this.options.scalePreviewFromCentre) {
                this.extraTransform = this.options.scalePreviewFromCentre ? ' translateX(-50%)' : '';
                this.previewArea.style.left = '50%';
            }

            /**
             * Some scripts are not very big fans of this, so we silence errors that happen due to inserting preview
             */
            try {
                $(this.previewArea).append(this.preview);
            } catch (e) {}
        }
    });
})(jQuery);