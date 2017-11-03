;raptor(function ($) {
    'use strict';

    var getLinkTypeManager = function(dialogTypeInstance, dialogNode) {
        if (dialogTypeInstance && dialogNode) {
            this.instance = new LinkTypeTelephone(dialogTypeInstance, dialogNode);
        }

        if (!this.instance) {
            throw 'Telephone link type model not instantiated';
        }

        return this.instance;
    }.bind({
        instance: undefined
    });

    function LinkTypeTelephone(linkCreate, ownerDialog) {
        this.linkCreate = linkCreate;
        this.ownerDialog = ownerDialog;
    }

    jQuery.vaimoRaptorUtils.extend(LinkTypeTelephone, {
        label: 'Telephone',
        linkPrefix: 'tel:',
        nodeId: 'vcms-raptor-telephone',
        inputPlaceholder: 'i.e. +372 555 555',
        panelTitle: 'Link to a phone number',
        inputLabel: 'Phone Number',
        inputName: 'telephone',

        getContent: function () {
            var templateName = 'link.' + this.inputName;

            this.linkCreate.raptor.templates[templateName] = '\
            <h2>{{title}}</h2> \
            <fieldset class="{{baseClass}}-{{name}}"> \
                <label for="{{baseClass}}-{{name}}">{{label}}</label> \
                <input id="{{baseClass}}-{{name}}" name="{{name}}" type="text" placeholder="{{placeholder}}"/> \
            </fieldset> \
            <fieldset class="{{baseClass}}-{{name}}"></fieldset> \
            ';

            return this.linkCreate.raptor.getTemplate(
                templateName,
                $.extend(this.linkCreate.raptor.options, {
                    title: this.panelTitle,
                    label: this.inputLabel,
                    name: this.inputName,
                    placeholder: this.inputPlaceholder
                })
            );
        },

        getInput: function (panel) {
            return panel.find('[name=' + this.inputName + ']');
        },

        getAttributes: function (panel) {
            var value = this.getInput(panel).val();

            value = $.trim(value);

            if (value === '') {
                return false;
            }

            return {
                href: 'tel:' + encodeURIComponent(value.replace(/\s/g, ''))
            };
        },

        getNode: function (type) {
            var id = this.getId(type);

            return this.ownerDialog.find('#' + id);
        },

        getId: function (type) {
            return this.nodeId + '-' + type
        },

        resetInputs: function (panel) {
            this.getInput(panel).val('');
        },

        matchLink: function (link) {
            return link.attr('href').indexOf(this.linkPrefix) === 0;
        },

        updateInputsFromElement: function (panel, link) {
            var href = link.attr('href').substring(this.linkPrefix.length);

            this.getInput(panel)
                .val(href);
        }
    });

    jQuery.vaimoRaptorUtils.extend('ui.linkCreate', {
        getDialog: function () {
            var dialog = this._vcmsSuper.apply(this, arguments);

            var element = jQuery.vaimoRaptorUtils.selectionGetElement();

            var linkType = getLinkTypeManager();

            var content = linkType.getNode('content');

            if (element.is('a')) {
                if (linkType.matchLink(element)) {
                    linkType.updateInputsFromElement(content, element);
                    linkType.ownerDialog.find('#raptor-internal-href')
                        .val('');

                    linkType.getNode('option')
                        .trigger('click');

                    setTimeout(function() {
                        var input = linkType.getInput(content);

                        if (!input.length) {
                            return;
                        }

                        input.focus()
                            .get().shift()
                            .setSelectionRange(0, 255);
                    }, 50);
                }
            } else {
                linkType.resetInputs(content);

                linkType.ownerDialog.find('[data-menu]')
                    .find(':radio:eq(0)')
                    .trigger('click');
            }

            return dialog;
        },

        getDialogTemplate: function () {
            var dialogNode = this._vcmsSuper.apply(this, arguments);
            var dialogMenu = dialogNode.find('[data-menu]');
            var dialogContent = dialogNode.find('[data-content]');

            var linkType = getLinkTypeManager(this, dialogNode);

            var $label = $(this.raptor.getTemplate('link.label', linkType));

            $label.find('input').attr('id', linkType.getId('option'));

            $label.click(function () {
                dialogContent.children('div')
                    .hide();

                dialogContent.children('div:eq(' + $(this).index() + ')')
                    .show();
            });

            $label.find(':radio')
                .val(dialogMenu.find('> label').length)
                .end()
                .appendTo(dialogMenu);

            $('<div>').attr('id', linkType.getId('content'))
                .append(linkType.getContent())
                .hide()
                .appendTo(dialogContent);

            return dialogNode;
        },

        applyAction: function (dialog) {
            var linkType = getLinkTypeManager();

            var content = linkType.getNode('content');
            var option = linkType.getNode('option');

            var linkAttributes = linkType.getAttributes(content);

            if (linkAttributes && option.is(':checked')) {
                linkType.ownerDialog.find('[data-menu]')
                    .find(':radio')
                    .prop('checked', false)
                    .first()
                    .prop('checked', true);

                dialog.find('#raptor-internal-href')
                    .val(linkAttributes.href);

                this.validateDialog(dialog);
            } else {
                linkType.resetInputs(content);
            }

            return this._vcmsSuper.apply(this, [dialog]);
        },

        validateDialog: function (dialog) {
            var linkType = getLinkTypeManager();

            var dialogContent = linkType.getNode('content');

            if (dialogContent.is(':visible')) {
                var linkAttributes = linkType.getAttributes(dialogContent);

                return linkAttributes !== false;
            }

            return this._vcmsSuper.apply(this, arguments);
        }
    });
});
