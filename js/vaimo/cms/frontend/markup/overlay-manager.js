;(function ($) {
    'use strict';

    /**
     * Change after back-ported from M2 (17 lines)
     */
    var stickrWidget, context;
    var documentSizeOnLastRefresh = {};

    context = {
        window: window, $window: jQuery(window),
        document: document, $document: jQuery(document),
    };

    context.$document.on('click', function(event) {
        if (documentSizeOnLastRefresh.width == context.$document.width() &&
            documentSizeOnLastRefresh.height == context.$document.height()
        ) {
            return;
        }

        jQuery(event.target).trigger('vcms-overlay-refresh');
    });

    $.widget('vaimo.markupOverlayManager', {
        options: {
            overlayIdPrefix: '',
            attributes: [],
            container: '',
            minHeight: 0,
            markup: '<div id="{{ID}}" {{EXTRA}}>{{CONTENT}}</div>',
            stickyButtonsOffset: 40,
            className: false,
            templates: {},
            useParentWidth: false,
            initialUpdateDelay: 0,
            realTimeRefresh: false,
            realTimeUpdate: false,
            reactToBodyClassChanges: false,
            realTimeUpdateDelay: 50,
            selectors: {
                contentPlaceholder: false
            },
            classes: {
                hide: 'vcms-overlay-hide'
            },
            extraClasses: {
            },
            exclude: false
        },
        _create: function() {
            /**
             * Change after back-ported from M2 (3 lines)
             */
            stickrWidget = jQuery.fn.stickr;
            context.body = document.body;
            context.$body = document.$body;

            this.createdOverlays = {};
            this.$container = false;
            this.groups = {};

            this.classNameSelector = '.' + this.options.className.replace(/ /g, '.') + ':visible';

            if (!this._validateRequiredOptions()) {
                return;
            }

            this.$container = jQuery(this.options.container);

            if (!this.$container.length) {
                console.error('Defined overlay container not found');
                return;
            }

            if (this.options.initialUpdateDelay) {
                setTimeout(jQuery.proxy(this.update, this), 50);
            } else {
                this.update();
            }

            var overlayManager = this;

            if (this.options.realTimeRefresh) {
                var updateOnResize;

                jQuery(window).resize(function() {
                    clearTimeout(updateOnResize);
                    updateOnResize = setTimeout(jQuery.proxy(overlayManager.refresh, overlayManager), 50);
                });
            }

            if (this.options.realTimeUpdate && 'MutationObserver' in context.window
                && this.options.container.length
            ) {
                var observer = new MutationObserver(jQuery.proxy(this._onDomMutation, this));

                observer.observe(document.querySelector(this.options.container), {
                    subtree: true,
                    childList: true,
                    attributes: true,
                    attributeFilter: ['class', 'style']
                });

                if (this.options.reactToBodyClassChanges) {
                    observer = new MutationObserver(jQuery.proxy(this._onBodyAttributeChange, this));

                    observer.observe(context.body, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                }
            }

            this.$container.on('mousemove', jQuery.proxy(this._onMouseMove, this));

            this.$container.on('vcms-overlay-refresh', jQuery.proxy(this.refresh, this));
        },
        _onBodyAttributeChange: function() {
            this.refresh();
        },
        _getOverlaysFromPoint: function(x, y) {
            var visibleOverlays = {};

            if (!jQuery(this.classNameSelector).length) {
                return visibleOverlays;
            }

            var element = document.elementFromPoint(x, y);
            var $element = jQuery(element);

            var pageX = x + context.$document.scrollLeft();
            var pageY = y + context.$document.scrollTop();

            Object.keys(this.createdOverlays).forEach(function(id) {
                var info = this.createdOverlays[id];

                var isHovered = this._isPointInRect(pageX, pageY, info.rect);

                isVisible = info.items && info.items.length > 0;

                if (isVisible) {
                    if (!info.items.filter(':visible').length && !info.items.children(':visible').length) {
                        var isVisible = false;
                    }
                }

                if (isVisible) {
                    var ownsElement = false;

                    if ($element.parents('.js-markup-overlay').length) {
                        ownsElement = true
                    } else {
                        info.items.each(function(index, item) {
                            ownsElement = ownsElement || item.contains(element);
                            return !ownsElement;
                        });
                    }

                    isVisible = isVisible && ownsElement;
                }

                if (!isVisible || !isHovered) {
                    return;
                }

                visibleOverlays[id] = info;
            }.bind(this));

            return visibleOverlays;
        },
        _onMouseMove: function(event) {
            if (!jQuery(this.classNameSelector).length) {
                return;
            }

            var hoveredOverlays = this._getOverlaysFromPoint(event.clientX, event.clientY);

            Object.keys(this.createdOverlays).forEach(function(id) {
                var info = this.createdOverlays[id];

                info.overlay.toggleClass('active', id in hoveredOverlays);
            }.bind(this));
        },
        _onDomMutation: function(recordQueue) {
            var overlayManager = this;
            var overlayAttributes = overlayManager.options.attributes;

            jQuery(recordQueue).each(function() {
                var nodes = this.addedNodes.length ? this.addedNodes : this.removedNodes;

                if (!nodes.length) {
                    return;
                }

                for (var i = 0; i < nodes.length; i++) {
                    if (!'hasAttribute' in nodes[i]) {
                        continue;
                    }

                    var attributes = nodes[i].attributes;

                    if (!attributes) {
                        continue;
                    }

                    for (var j = 0; j < overlayAttributes.length; j++) {
                        var attributeName = overlayAttributes[j];

                        if (!attributeName in attributes) {
                            continue;
                        }

                        setTimeout(function() {
                            overlayManager.update();
                        }, overlayManager.options.realTimeUpdateDelay);

                        return false;
                    }
                }
            });
        },
        _validateRequiredOptions: function() {
            if (!this.options.attributes.length) {
                console.error('No target attributes defined');
                return false;
            }

            if (!this.options.container.length) {
                console.error('No overlay container defined');
                return false;
            }

            return true;
        },
        _calculateBounds: function(items, attribute) {
            var self = this;
            var minHeight = 0;
            var bounds = {
                left: false,
                top: false,
                width: 0,
                height: minHeight
            };

            var containerOffset = this.$container.offset();
            var useParentWidth = this.options.useParentWidth;

            if (useParentWidth) {
                var $parent = items.parent();

                if ($parent.children().not('[' + attribute + ']').length > 0) {
                    useParentWidth = false;
                }
            }

            jQuery.each(items, function() {
                var item = this;

                if (item.offsetParent === null) {
                    return;
                }

                var $item = jQuery(item);

                if ($item[0].tagName.toLowerCase() == 'script') {
                    return;
                }

                var position = $item.offset();

                var $children = $item.children();
                if (!$item.is(':visible') && !$children.is(':visible')) {
                    return;
                }

                var height = $item.outerHeight();
                var width = $item.outerWidth();

                if ($item.css('overflow') != 'hidden' && self._areChildrenInViewport($children)) {
                    height = Math.max(height, $item.prop('scrollHeight'));
                    width = Math.max(width, $item.prop('scrollWidth'));
                }

                var left = position.left;
                var top = position.top;

                if (height === 0 || width === 0) {
                    if ($children.length == 1 && $children.is(':visible')) {
                        var childrenPosition = $children.offset();

                        if (height === 0) {
                            height = $children.outerHeight();
                            top = childrenPosition.top;
                        }

                        if (width === 0) {
                            width = $children.outerWidth();
                            left = childrenPosition.left;
                        }
                    }
                }

                bounds.top = (bounds.top === false) ? top : Math.min(top, bounds.top);
                bounds.left = (bounds.left === false) ? left : Math.min(left, bounds.left);

                var cssPosition = $item.css('position');

                if (cssPosition != 'absolute') {
                    if (left + width > bounds.left + bounds.width) {
                        bounds.width = left + width - bounds.left;
                    }

                    if (useParentWidth && $parent.width() > bounds.width) {
                        bounds.width = $parent.width();
                    }

                    if (top + height > bounds.top + bounds.height) {
                        bounds.height = top + height - bounds.top;
                    }

                    if (useParentWidth && !bounds.height && bounds.width) {
                        bounds.height = $parent.height();
                    }
                } else {
                    left = left - containerOffset.left;

                    if (left + width > bounds.width) {
                        bounds.width = width + left - bounds.left;
                    }

                    if (top + height > bounds.top + bounds.height) {
                        bounds.height = top + height - bounds.top;
                    }
                }
            });

            return bounds;
        },
        _collectGroupedData: function(attributes, context) {
            var groups = {};

            var exclude = this.options.exclude;
            jQuery.each(attributes, function() {
                var attribute = this.toString();
                var attributeSelector = '[' + attribute + ']';

                if (exclude) {
                    attributeSelector += ':not(' + exclude + ')';
                }

                var $items;

                if (context && context != '') {
                    $items = jQuery(attributeSelector, context);
                } else {
                    $items = jQuery(attributeSelector);
                }

                if (!groups[attribute]) {
                    groups[attribute] = {};
                }

                $items.each(function() {
                    var element = this;

                    var value = jQuery(element).attr(attribute);

                    jQuery.each(value.split('|'), function() {
                        if (!this.length) {
                            return;
                        }

                        if (!groups[attribute][this]) {
                            groups[attribute][this] = jQuery();
                        }

                        groups[attribute][this].push(element);
                    });
                });
            });

            return groups;
        },
        update: function() {
            this._update(this.options.attributes, this.options.container);
        },
        refresh: function() {
            this._refresh(this.options.attributes, this.groups, true);
        },
        getOriginInfoForNode: function(overChildNode) {
            var selector = '';

            if (!this.options.className) {
                console.error('getOriginInfoForNode requires className to be defined');
                return;
            }

            jQuery(this.options.className.split(' ')).each(function() {
                selector += '.' + this;
            });

            var $overlay = jQuery(overChildNode).parents(selector);

            if (!$overlay.length) {
                console.error('Provided node does not belong to any overlay');
                return;
            }

            return this._getOriginInfoForOverlay($overlay);
        },
        _getOriginInfoForOverlay: function($overlay) {
            var id = $overlay.attr('id');

            return this._getOriginInfoForOverlayId(id);
        },
        _getOriginInfoForOverlayId: function(overlayId) {
            if (!overlayId || !overlayId in this.createdOverlays) {
                return false;
            }

            return this.createdOverlays[overlayId];
        },
        _getOverlayId: function(attribute, value) {
            var prefix = this.options.overlayIdPrefix.length ? (this.options.overlayIdPrefix + '-') : '';

            return prefix + attribute + '-' + value.replace(/\./g, '--');
        },
        _refresh: function(attributes, groups, disallowCreation) {
            var $overlayContainer = this.$container;
            var overlayManager = this;
            var extraClasses = Object.keys(overlayManager.options.extraClasses);

            documentSizeOnLastRefresh.width = context.$document.width();
            documentSizeOnLastRefresh.height = context.$document.height();

            jQuery.each(attributes, function() {
                var attribute = this.toString();

                var overlayContent = '';

                if (overlayManager.options.templates && overlayManager.options.templates[attribute]) {
                    var templateSelector = overlayManager.options.templates[attribute];

                    var templateContainer = jQuery(templateSelector);

                    if (!templateContainer.length) {
                        console.error('Template for "' + attribute + '" overlay not found. Continuing without overlay content.');
                    } else {
                        overlayContent = templateContainer.html();
                    }
                }

                var minimalHeight = 10;

                var valueGroups = groups[attribute];

                if (!valueGroups) {
                    valueGroups = [];
                }

                jQuery.each(valueGroups, function(value, items) {
                    var extraOptions = '';
                    var overlayId = overlayManager._getOverlayId(attribute, value);
                    var $overlay = false;

                    var bounds = overlayManager._calculateBounds(items, attribute);
                    var isHidden = bounds.width === 0 || bounds.height === 0;

                    if (overlayManager.options.selectors.contentPlaceholder) {
                        var $placeholder = items.filter(overlayManager.options.selectors.contentPlaceholder);
                        var $otherItems = items.filter(':not(' + overlayManager.options.selectors.contentPlaceholder +')');

                        if (isHidden) {
                            $placeholder.removeClass(overlayManager.options.classes.hide);
                        } else if ($otherItems.length) {
                            var _height = 0;

                            $otherItems.each(function(_, item) {
                                _height += jQuery(item).height();
                            });

                            if (_height >= minimalHeight) {
                                $placeholder.addClass(overlayManager.options.classes.hide);

                                bounds = overlayManager._calculateBounds(items, attribute);
                                isHidden = bounds.width === 0 || bounds.height === 0;
                            } else {
                                $placeholder.removeClass(overlayManager.options.classes.hide);
                            }
                        }
                    }

                    var overlaySelector = '#' + overlayId;

                    if (overlayManager.createdOverlays[overlayId]) {
                        $overlay = overlayManager.createdOverlays[overlayId].overlay;
                    } else {
                        $overlay = jQuery(overlaySelector);
                    }

                    if (!$overlay.length && !disallowCreation) {
                        var classes = [];

                        if (overlayManager.options.className) {
                            classes.push(overlayManager.options.className);
                        }

                        if (isHidden) {
                            classes.push(overlayManager.options.classes.hide);
                        }

                        classes.push('js-markup-overlay vcms-ui');

                        if (classes.length) {
                            extraOptions += ' class="' + classes.join(' ') + '"';
                        }

                        $overlayContainer.prepend(overlayManager.options.markup
                            .replace('{{ID}}', overlayId)
                            .replace('{{EXTRA}}', extraOptions)
                            .replace('{{CONTENT}}', overlayContent));

                        $overlay = jQuery(overlaySelector);

                        var $stickyItems = $overlay.find('.vcms-sticky');

                        if ($stickyItems.length) {
                            stickrWidget.call($stickyItems, {
                                duration: 0
                            });
                        }

                        overlayManager.createdOverlays[overlayId] = {
                            overlay: $overlay,
                            attribute: attribute,
                            value: value,
                            items: items,
                            bounds: {
                                top: 0,
                                left: 0,
                                width: 0,
                                height: 0
                            },
                            rect: {
                                top: 0,
                                left: 0,
                                bottom: 0,
                                right: 0
                            }
                        };
                    } else {
                        $overlay.toggleClass(overlayManager.options.classes.hide, isHidden);
                    }

                    if (!$overlay || !overlayManager.createdOverlays[overlayId]) {
                        return;
                    }

                    if (items.length) {
                        overlayManager.createdOverlays[overlayId].items = items;
                    }

                    overlayManager.createdOverlays[overlayId].bounds = bounds;

                    var overlay = $overlay[0];

                    if (isHidden || overlay.offsetParent === null) {
                        return;
                    }

                    var rect = overlayManager.createdOverlays[overlayId].rect;

                    rect.top = bounds.top;
                    rect.left = bounds.left;
                    rect.bottom = bounds.top + bounds.height;
                    rect.right = bounds.left+ bounds.width;

                    if (extraClasses.length) {
                        extraClasses.forEach(function(selector) {
                            var matches = items.filter(selector);

                            var classes = overlayManager.options.extraClasses[selector].join(' ');

                            if (!matches.length) {
                                $overlay.removeClass(classes);
                                return;
                            }

                            $overlay.addClass(classes);
                        });
                    }

                    overlay.style.width = bounds.width + 'px';
                    overlay.style.height = bounds.height + 'px';
                    overlay.style.top = bounds.top + 'px';
                    overlay.style.left = bounds.left + 'px';

                    /**
                     * Performing additional check to see that content and overlay offset match up. Updating
                     * the offset of the overlay based on this additional information.
                     */
                    var overlayOffset = $overlay.offset();

                    overlay.style.top = (2 * bounds.top - overlayOffset.top) + 'px';
                    overlay.style.left = (2 * bounds.left - overlayOffset.left) + 'px';
                });
            });

            var overlayIds = Object.keys(overlayManager.createdOverlays);

            for (var i = 0; i < overlayIds.length; i++) {
                var overlayId = overlayIds[i];

                var overlayData = overlayManager.createdOverlays[overlayId];

                var parents = overlayData.items.parents(overlayManager.options.container);

                if (parents.length == 0) {
                    overlayData.overlay.remove();
                    delete overlayManager.createdOverlays[overlayId];
                }
            }
        },
        _update: function(attributes, context) {
            this.groups = this._collectGroupedData(attributes, context);

            this._refresh(attributes, this.groups);
        },
        getOverlayCount: function() {
            var keys = Object.keys(this.createdOverlays);
            return keys.length;
        },
        updateForAttributes: function(attributes) {
            this._update(attributes, this.options.container);
        },
        _isPointInRect: function(x, y, rect) {
            return y >= rect.top && y <= rect.bottom && x >= rect.left && x <= rect.right;
        },
        _isElementInViewport: function($element) {
            var domElement = $element[0];

            if (typeof domElement == 'undefined') {
                return;
            }

            var rect = domElement.getBoundingClientRect();

            return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },
        _areChildrenInViewport: function($children) {
            var self = this;
            var elementsAreInViewport = true;

            jQuery.each($children, function () {
                jQuery.each(jQuery(this).children(), function () {
                    if (self._isElementInViewport(jQuery(this)) === false) {
                        elementsAreInViewport = false;
                        return false;
                    }
                });

                if (elementsAreInViewport === false) {
                    return false;
                }
            });

            return elementsAreInViewport;
        }
    });
})(jQuery);