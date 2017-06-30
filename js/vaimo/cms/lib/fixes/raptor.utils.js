;raptor(function($) {
    /**
     * Standard raptor utilities (without ANY changes to them), that are not accessible from outside of Raptor.
     */
    function rangeReplace(range, html) {
        // <strict/>

        var newNodes = [];
        range.deleteContents();
        if (html.nodeType) {
            // Node
            newNodes.push(html.cloneNode(true));
            range.insertNode(newNodes[0]);
        } else {
            // HTML string
            var wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            for (var i = 0; i < wrapper.childNodes.length; i++) {
                var clone = wrapper.childNodes[i].cloneNode(true);
                range.insertNodeAtEnd(clone);
                newNodes.push(clone);
            }
        }
        return newNodes;
    }

    function selectionEachRange(callback, selection, context) {
        selection = selection || rangy.getSelection();
        var range, i = 0;
        // Create a new range set every time to update range offsets
        while (range = selection.getAllRanges()[i++]) {
            callback.call(context, range);
        }
    }

    function selectionReplace(html, selection) {
        var newNodes = [];
        selectionEachRange(function(range) {
            newNodes = newNodes.concat(rangeReplace(range, html));
        }, selection, this);
        return newNodes;
    }

    function selectionGetHtml(selection) {
        selection = selection || rangy.getSelection();
        return selection.toHtml();
    }

    var savedSelection = false;

    function selectionSet(mixed) {
        rangy.getSelection().setSingleRange(mixed);
    }

    function selectionSave(overwrite) {
        if (savedSelection && !overwrite) return;
        savedSelection = rangy.saveSelection();
    }

    function selectionRestore() {
        if (savedSelection) {
            rangy.restoreSelection(savedSelection);
            savedSelection = false;
        }
    }

    function elementChangeTag(element, newTag) {
        // <strict/>
        var tags = [];
        for (var i = element.length - 1; 0 <= i ; i--) {
            var node = document.createElement(newTag);
            node.innerHTML = element[i].innerHTML;
            $.each(element[i].attributes, function() {
                $(node).attr(this.name, this.value);
            });
            $(element[i]).after(node).remove();
            tags[i] = node;
        }
        return $(tags);
    }

    function selectionChangeTags(changeTo, changeFrom, limitElement) {
        var elements = selectionFindWrappingAndInnerElements(changeFrom.join(','), limitElement);
        if (elements.length) {
            selectionSave();
            elementChangeTag(elements, changeTo);
            selectionRestore();
        } else {
            var limitNode = limitElement.get(0);
            if (limitNode.innerHTML.trim()) {
                selectionSave();
                limitNode.innerHTML = '<' + changeTo + '>' + limitNode.innerHTML + '</' + changeTo + '>';
                selectionRestore();
            } else {
                limitNode.innerHTML = '<' + changeTo + '>&nbsp;</' + changeTo + '>';
                selectionSelectInner(limitNode.childNodes[0]);
            }
        }
    }

    function selectionSelectInner(node, selection) {
        // <strict/>
        selection = selection || rangy.getSelection();
        var range = rangy.createRange();
        range.selectNodeContents(node);
        selection.setSingleRange(range);
    }

    function selectionFindWrappingAndInnerElements(selector, limitElement) {
        var result = new jQuery();
        selectionEachRange(function(range) {
            var startNode = range.startContainer;
            while (startNode.nodeType === Node.TEXT_NODE) {
                startNode = startNode.parentNode;
            }

            var endNode = range.endContainer;
            while (endNode.nodeType === Node.TEXT_NODE) {
                endNode = endNode.parentNode;
            }

            var filter = function() {
                if (!limitElement.is(this)) {
                    result.push(this);
                }
            };

            do {
                $(startNode).filter(selector).each(filter);

                if (!limitElement.is(startNode) && result.length === 0) {
                    $(startNode).parentsUntil(limitElement, selector).each(filter);
                }

                $(startNode).find(selector).each(filter);

                if ($(endNode).is(startNode)) {
                    break;
                }

                startNode = $(startNode).next();
            } while (startNode.length > 0 && $(startNode).prevAll().has(endNode).length === 0);
        });
        return result;
    }

    function selectionExists() {
        return rangy.getSelection().rangeCount !== 0;
    }

    function selectionRange() {
        // <strict/>
        return rangy.getSelection().getRangeAt(0);
    }

    function selectionGetElement(range, selection) {
        selection = selection || rangy.getSelection();
        if (!selectionExists()) {
            return new jQuery;
        }
        var range = selectionRange(),
            commonAncestor;
        // Check if the common ancestor container is a text node
        if (range.commonAncestorContainer.nodeType === Node.TEXT_NODE) {
            // Use the parent instead
            commonAncestor = range.commonAncestorContainer.parentNode;
        } else {
            commonAncestor = range.commonAncestorContainer;
        }
        return $(commonAncestor);
    }

    /**
     * Making the std Raptor utils globally available
     */
    jQuery.vaimoRaptorUtils = {
        rangeReplace: rangeReplace,
        selectionEachRange: selectionEachRange,
        selectionReplace: selectionReplace,
        selectionGetHtml: selectionGetHtml,
        selectionSet: selectionSet,
        selectionSave: selectionSave,
        selectionRestore: selectionRestore,
        elementChangeTag: elementChangeTag,
        selectionChangeTags: selectionChangeTags,
        selectionSelectInner: selectionSelectInner,
        selectionFindWrappingAndInnerElements: selectionFindWrappingAndInnerElements,
        selectionGetElement: selectionGetElement,
        selectionExists: selectionExists,
        selectionRange: selectionRange
    };
});