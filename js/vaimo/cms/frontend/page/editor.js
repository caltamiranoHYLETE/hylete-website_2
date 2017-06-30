;(function ($) {
    "use strict";

    $.widget('vaimo.cmsPageEditor', $.vaimo.cmsEditorBase, {
        options: {
            selectors: {
                toolbarButtonsGroup: '',
                toolbarButtons: {
                    'save': '',
                    'cancel': ''
                }
            },
            uri: {
                save: '',
                cancel: ''
            },
            revision: '',
            attributes: {
                content: ''
            },
            initial: {
                page_draft: 0
            },
            fuzzySelectorDelimiter: true
        },
        _create: function() {
            this._super();

            this.options.io.addHandler('write', this._addRevisionToPost.bind(this));
            this.options.io.addHandler('read', this._updateToolbar.bind(this));

            var selectors = this.options.selectors;
            $(selectors.toolbarButtons.save).click($.proxy(this._publish, this));
            $(selectors.toolbarButtons.cancel).click($.proxy(this._discard, this));

            if (!this.options.initial) {
                return;
            }

            this._updateToolbar(this.options.initial);
        },
        _addRevisionToPost: function(data) {
            if (!this.options.revision) {
                return false;
            }

            return this._getPost({
                revision: this.options.revision
            });
        },
        _publish: function() {
            var data = {
                revision: this.options.revision
            };

            this._save(data, Translator.translate('Publishing Draft'), 'save');
        },
        _discard: function() {
            var data = {
                revision: this.options.revision
            };

            this._save(data, Translator.translate('Discarding Draft'), 'cancel');
        },
        _update: function(data) {
            var options = this.options;

            if (!data.structures) {
                return;
            }

            if (!options.attributes.content) {
                console.error('Content update failed. Content data-attribute not defined');
                return;
            }

            var name = options.attributes.content;
            var delimiter = '';

            if (typeof options.attributes.content == 'object') {
                name = options.attributes.content.name + '*';
                delimiter = options.attributes.content.delimiter;
            }

            $.each(data.structures, function(index, update) {
                var value = update.reference;

                if (delimiter.length) {
                    value = delimiter + value + delimiter;
                }

                var $items = $('[' + name + '="' + value + '"]');

                $(update.html).insertBefore(this._getInsertionPoint($items));
                $items.remove();
            }.bind(this));
        },
        _showStagingButtons: function(show) {
            $(this.options.selectors.toolbarButtonsGroup)
                .toggleClass('vcms-page-with-revision', show == true);
        },
        _updateToolbar: function(response) {
            if (typeof response != 'object') {
                return;
            }

            this._showStagingButtons(response && response['page_draft'] == 1);
        }
    });
})(jQuery);