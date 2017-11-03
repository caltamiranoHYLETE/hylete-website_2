;raptor(function($) {
    'use strict';

    /**
     * Container for original Raptor methods that have been monkey-patched/fixed
     */
    jQuery.vaimoRaptorOriginalMethods = jQuery.vaimoRaptorOriginalMethods || {};
    jQuery.vaimoRaptorOriginalMethods.ui = jQuery.vaimoRaptorOriginalMethods.ui || {};
    jQuery.vaimoRaptorOriginalMethods.plugin = jQuery.vaimoRaptorOriginalMethods.plugin || {};
    jQuery.vaimoRaptorOriginalMethods.raptorWidget = jQuery.vaimoRaptorOriginalMethods.raptorWidget || {};

    var raptorModel = $.fn.raptor.Raptor;

    /**
     * FIX: Allow images to be inserted into blank content or content that only has white-spaces in it.
     */
    jQuery.vaimoRaptorOriginalMethods.ui.fileManager = jQuery.vaimoRaptorOriginalMethods.ui.fileManager || {};
    jQuery.vaimoRaptorOriginalMethods.ui.fileManager.insertFiles = raptorModel.ui.fileManager.insertFiles;
    raptorModel.ui.fileManager.insertFiles = function(files) {
        var selectionHtml = jQuery.vaimoRaptorUtils.selectionGetHtml();
        var $selectionNode = $('<p>' + selectionHtml + '</p>');
        var selectionText = $selectionNode.text();

        var shouldReplace = selectionText.replace(/\xA0/g,'').replace(/ /g,'').replace(/&nbsp;/g,'') == ''
            && !$selectionNode.find('img').length;

        if (files.length === 1 && this.isImage(files[0]) && shouldReplace) {
            var newNodes = jQuery.vaimoRaptorUtils.selectionReplace('<img/>');

            if (newNodes.length > 0) {
                var range = rangy.createRange();

                range.setStartBefore(newNodes[0]);
                range.setEndAfter(newNodes[newNodes.length - 1]);

                jQuery.vaimoRaptorUtils.selectionSet(range);
            }
        }

        return jQuery.vaimoRaptorOriginalMethods.ui.fileManager.insertFiles.apply(this, [files]);
    };

    /**
     * FIX: make sure that local link's base url is correct & don't open dialog partially off-screen & make
     * sure that selection in case a LINK was just added is correctly wrapping the created A
     */
    jQuery.vaimoRaptorOriginalMethods.ui.linkCreate = jQuery.vaimoRaptorOriginalMethods.ui.linkCreate || {};
    jQuery.vaimoRaptorOriginalMethods.ui.linkCreate.getDialog = raptorModel.ui.linkCreate.getDialog;

    raptorModel.ui.linkCreate.getDialog = function() {
        var element = jQuery.vaimoRaptorUtils.selectionGetElement();

        if (element.is('a')) {
            jQuery.vaimoRaptorUtils.selectionSelectInner(element[0]);
        }

        var dialog = jQuery.vaimoRaptorOriginalMethods.ui.linkCreate.getDialog.apply(this, arguments);

        dialog.find('label[for="raptor-internal-href"]')
            .html(this.options.baseUrl);

        return dialog;
    };

    /**
     * FIX: detect image file-type correctly even if the file ends with upper-case extension
     */
    jQuery.vaimoRaptorOriginalMethods.ui.fileManager = jQuery.vaimoRaptorOriginalMethods.ui.fileManager || {};

    jQuery.vaimoRaptorOriginalMethods.ui.fileManager.getFileType = raptorModel.ui.fileManager.getFileType;
    jQuery.vaimoRaptorOriginalMethods.ui.fileManager.getDialog = raptorModel.ui.fileManager.getDialog;

    raptorModel.ui.fileManager.getFileType = function(fileInfo) {
        var type = jQuery.vaimoRaptorOriginalMethods.ui.fileManager.getFileType.apply(this, [fileInfo]);

        return type.toLowerCase();
    };

    /**
     * FIX: Default to using paragraph wrapper when class menu used on non-wrapped content
     */
    jQuery.vaimoRaptorOriginalMethods.ui.classMenu = jQuery.vaimoRaptorOriginalMethods.ui.classMenu || {};
    jQuery.vaimoRaptorOriginalMethods.ui.classMenu.changeClass = raptorModel.ui.classMenu.changeClass;
    raptorModel.ui.classMenu.changeClass = function(add) {
        var savedSelection, allowedTags, tag, editableArea, unwrappedNodes, unwrappedContent, markerLimit, markerCount;

        allowedTags = ['STRONG', 'U', 'DEL', 'EM', '#text'];
        tag = 'span';

        savedSelection = rangy.saveSelection();
        editableArea = this.raptor.getElement()[0];

        markerLimit = savedSelection.rangeInfos[0].collapsed ? 1 : 2;
        unwrappedContent = [];
        markerCount = 0;

        /**
         * Detect top-level unwrapped content (within the selected area)
         */
        $.each(editableArea.childNodes, function() {
            if (!unwrappedNodes) {
                unwrappedContent.push([]);
                unwrappedNodes = unwrappedContent.last();
            }

            var isMarker = this.classList && this.classList.contains('rangySelectionBoundary');
            var shouldInclude = allowedTags.indexOf(this.nodeName) >= 0 || isMarker;

            if (shouldInclude) {
                unwrappedNodes.push(this);
            } else if (markerCount < 1) {
                unwrappedNodes.clear();
            }

            if (isMarker) {
                markerCount++;
            }

            if (this.querySelectorAll) {
                markerCount = markerCount + this.querySelectorAll('.rangySelectionBoundary').length;
            }

            if (shouldInclude) {
                return;
            }

            if (markerCount >= markerLimit) {
                return false;
            }

            if (!unwrappedNodes.length) {
                return;
            }

            unwrappedNodes = false;
        });

        /**
         * Wrap the un-wrapped content
         */
        var selection = rangy.getSelection();
        var range = selection.nativeSelection.getRangeAt(0);

        $.each(unwrappedContent, function() {
            if (!this.length) {
                return;
            }

            var start = this.shift();
            var end = this.pop();

            range.setStartBefore(start);
            range.setEndAfter(end ? end : start);

            selection.setSingleRange(range);

            range.surroundContents(document.createElement(tag));
        });

        /**
         * Toggle the classes
         */
        var index, nodes;

        var wrappers = editableArea.querySelectorAll(tag + ':not(.rangySelectionBoundary)');
        var lineBreaks = editableArea.querySelectorAll('br');

        for (index = 0; index < wrappers.length; index++) {
            wrappers[index].style.display = 'inline-block';

            var text = wrappers[index].innerText || wrappers[index].textContent;

            if (wrappers[index].children.length == 1
                && wrappers[index].children[0].classList.contains('rangySelectionBoundary')
                && (text === "" || (text.length === 1 && text.trim() === ""))
            ) {
                var tmp = document.createElement("strong");
                tmp.appendChild(document.createTextNode("tmp"));
                tmp.classList.add('vcsm-blank');
                tmp.classList.add(add[0]);

                wrappers[index].classList.add(add[0]);
                wrappers[index].appendChild(tmp);
            }
        }

        if (editableArea.querySelectorAll('.rangySelectionBoundary').length) {
            rangy.restoreSelection(savedSelection);
        }


        for (index = 0; index < lineBreaks.length; index++) {
            lineBreaks[index].classList.add(add[0]);
        }

        jQuery.vaimoRaptorOriginalMethods.ui.classMenu.changeClass.apply(this, arguments);

        /**
         * Cleanup
         */
        for (index = 0; index < wrappers.length; index++) {
            wrappers[index].style.display = '';
            wrappers[index].classList.remove('vcsm-blank');
        }

        for (index = 0; index < lineBreaks.length; index++) {
            lineBreaks[index].removeAttribute('class');
        }

        nodes = editableArea.querySelectorAll('[style=""]');
        for (index = 0; index < nodes.length; index++) {
            nodes[index].removeAttribute('style');
        }

        nodes = editableArea.querySelectorAll('[class=""]');
        for (index = 0; index < nodes.length; index++) {
            nodes[index].removeAttribute('class');
        }

        /**
         * Remove temporary DOM nodes (and extract selection markers if needed)
         */
        savedSelection = rangy.saveSelection();

        nodes = editableArea.querySelectorAll('.vcsm-blank');
        for (index = 0; index < nodes.length; index++) {
            var boundaryMarkers = nodes[index].querySelectorAll('.rangySelectionBoundary');

            for (var markerIndex = 0; markerIndex < boundaryMarkers.length; markerIndex++) {
                nodes[index].parentNode.appendChild(boundaryMarkers[markerIndex]);
            }

            nodes[index].parentNode.removeChild(nodes[index]);
        }

        rangy.restoreSelection(savedSelection);

        var len = wrappers.length;
        for (index = 0; index < len; index++) {
            if (wrappers[index].innerHTML !== "\n" && wrappers[index].innerHTML !== "") {
                continue;
            }

            if (!wrappers[index].parentNode) {
                return;
            }

            wrappers[index].parentNode.removeChild(wrappers[index]);
        }
    };

    /**
     * FIX: Pass SAME execution context to both .id and .data in saveJson to allow them to access same raptor instance.
     */
    jQuery.vaimoRaptorOriginalMethods.plugin.saveJson = jQuery.vaimoRaptorOriginalMethods.plugin.saveJson || {};
    jQuery.vaimoRaptorOriginalMethods.plugin.saveJson.save = raptorModel.plugins.saveJson.save;
    raptorModel.plugins.saveJson.save = function() {
        this.raptor.unify(function(raptorInstance) {
            var plugin = raptorInstance.getPlugin('saveJson');

            if (!plugin.options._raptorSaveInstance) {
                plugin.options._raptorSaveInstance = {};

                plugin.options._id = plugin.options.id;
                plugin.options.id = function() {
                    plugin.options._raptorSaveInstance = this;
                    return plugin.options._id.apply(this, arguments);
                };

                if (plugin.options.data) {
                    plugin.options._data = plugin.options.data;
                    plugin.options.data = function() {
                        return plugin.options._data.apply(plugin.options._raptorSaveInstance, arguments);
                    };
                }
            }
        });

        jQuery.vaimoRaptorOriginalMethods.plugin.saveJson.save.apply(this, arguments);
    };

    /**
     * FIX: setHtml & enableEditing sets selection caret to ZERO position that makes many UI functions wrap the content
     * with 'undefined'.
     *
     * (note the need to retain certain value in .fn - this comes from the fact that extending a widget will wipe some
     * values that Raptor has registered outside from normal framework usage).
     */
    var _raptor = $.fn.raptor.Raptor;

    $.widget('ui.raptor', $.ui.raptor, {
        _resetSelection: function() {
            var element = this.getElement();

            var firstChild = element.children().first();

            if (!firstChild.length) {
                return;
            }

            setTimeout(function() {
                var storedRangeMarker = '' +
                    '<span id="vcsm-range-start" class="rangySelectionBoundary"></span>' +
                    '<span id="vcsm-range-end" class="rangySelectionBoundary"></span>';

                $(firstChild).prepend(storedRangeMarker);

                var savedSelection = {
                    rangeInfos: [{
                        backward: false,
                        collapsed: false,
                        document: document,
                        startMarkerId: 'vcsm-range-start',
                        endMarkerId: 'vcsm-range-end',
                        toString: rangy.rangePrototype.toString
                    }],
                    restored: false,
                    win: undefined
                };

                rangy.restoreSelection(savedSelection);
            }, 20);
        },
        enableEditing: function() {
            this._superApply(arguments);

            this._resetSelection();
        },
        setHtml: function() {
            this._superApply(arguments);

            this._resetSelection();
        }
    });

    /**
     * FIX: WebKit (Chrome, etc) have a bug that adds extra <span> elements to the content when
     * certain events happen in the editor (one of them being: deleting a line). This bug happens when
     * multiple DOM nodes are merged together. This can be avoided if we temporarily set the values
     * that WebKit thinks are missing (mostly it's just line-height value that is required). Same happens
     * with some of the Raptor plugins - so we make sure to add the class before any of the plug-in actions
     * are applied.
     */
    var tmpClass = 'vcms-webkit-edit-fix';

    var targetedKeys = [
        $.ui.keyCode.BACKSPACE
    ];

    $.widget('ui.raptor', $.ui.raptor, {
        eventObserversRegistered: false,
        _init: function() {
            this._super();

            this.element.on('keydown', function(event) {
                if (targetedKeys.indexOf(event.keyCode) < 0) {
                    return;
                }

                $(event.currentTarget).addClass(tmpClass);
            });

            this.element.on('keyup', function(event) {
                if (targetedKeys.indexOf(event.keyCode) < 0) {
                    return;
                }

                $(event.currentTarget).removeClass(tmpClass);
            });
        },
        actionApply: function() {
            var $element = this.getElement();

            $element.addClass(tmpClass);

            var result = this._superApply(arguments);

            $element.removeClass(tmpClass);

            return result;
        },
        resume: function() {
            var result = this._superApply(arguments);

            var $element = this.getElement();

            $element.removeClass(tmpClass);

            return result;
        }
    });

    $.fn.raptor.Raptor = _raptor;

    /**
     * FIX: Make it possible to extend TagMenu options
     */
    raptorModel.ui.tagMenu.changeTag = function(tag) {
        if (typeof tag === 'undefined' || tag === 'na') {
            return;
        }

        var selectedElement = jQuery.vaimoRaptorUtils.selectionGetElement(),
            limitElement = this.raptor.getElement();

        if (selectedElement && !selectedElement.is(limitElement)) {
            var cell = selectedElement.closest('td, li, #' + limitElement.attr('id'));

            if (cell.length !== 0) {
                limitElement = cell;
            }
        }

        var allowedTags = Object.keys(raptor.fn.raptor.Raptor.ui.tagMenu.options.tags);

        var index = allowedTags.indexOf('na');
        allowedTags.splice(index, 1);

        jQuery.vaimoRaptorUtils.selectionChangeTags(tag, allowedTags, limitElement);
    };

    /**
     * FEATURE: Add SPAN as TAG wrapper option for tagMenu (span wrappers may happen when classMenu wraps non-wrapped
     * content).
     */
    raptor.fn.raptor.Raptor.ui.tagMenu.options.tags.span = 'Span';
});
