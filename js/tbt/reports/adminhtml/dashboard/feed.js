var sweettooth =  typeof sweettooth !== 'undefined' ? sweettooth : window.sweettooth || {};
sweettooth.Dashboard = typeof sweettooth.Dashboard !== 'undefined' ? sweettooth.Dashboard : window.sweettooth.Dashboard || {};

sweettooth.Dashboard.Feed =
{
    _updateUrl: null,
    _lastId: null,
    _firstId: null,
    _queue: [],
    _hiddenQueue: [],

    _dom: {},
    _updateTimer: null,
    _renderTimer: null,
    _timestampTimer: null,
    _isMouseHovered: false,


    init: function(options)
    {
        var self = this;
        this._updateText = {
            singular: (options.updateText ? options.updateText.singular : "%s New Update"),
            plural: (options.updateText ? options.updateText.plural : "%s New Updates"),
        };
        this._updateUrl = options.updateUrl;
        this._previousUrl = options.previousUrl;
        this._dom.feedsArea = $('dashboard_feedsArea');
        this._dom.feedReel = $('feed-items-reel');
        this._dom.bottomLink = $('feeds-bottom-link');
        this._dom.magentoFloatingHeader = $$('.content-header-floating')[0];
        this._dom.loadPrevious = $('feed-item-load-previous');
        this._dom.emptyEndOfList = $('feed-items-end-of-list').down('.empty-list');
        this._dom.nonEmptyEndOfList = $('feed-items-end-of-list').down('.non-empty-list');
        this._dom.metricsArea = $('dashboard_metricsArea');

        this._dom.hiddenUpdatesNotice = new Element('span', {
            id:     'feeds-hidden-updates-notice',
            style:  'display: none;'
        });
        this._dom.feedsArea.down('a').down().insert({
            after: this._dom.hiddenUpdatesNotice
        });

        if (options.bottomLink) {
            this._dom.bottomLink.insert(new Element('a', {
                    href: options.bottomLink.url || '#'
                }).update(options.bottomLink.label || "See More")
            );
        }

        this._dom.loadPrevious.observe('click', function() {
            self.loadPrevious();
        });

        /*
         * Mouse Events:
         * Stop adding directly to the feed if mouse is over the feed.
         */
        this._dom.feedReel.observe('mouseover', function(e) {
            self._isMouseHovered = true;
        });

        this._dom.feedReel.observe('mouseout', function(e) {
            self._isMouseHovered = false;
        });

        /*
         * Sticky content on scroll
         */
        var repositionFeed = function () {
            var anchorPosition = self._getFeedAreaTopAnchor();
            var feedsAreaHeight = parseInt(self._dom.feedsArea.style.height);
            var feedsAreaContainer = self._dom.feedsArea.up();
            var containerPosition = feedsAreaContainer.getBoundingClientRect();
            var maxStickingTop = containerPosition.bottom - feedsAreaHeight;
            var feedsAreaComputedStyles = window.getComputedStyle(feedsAreaContainer);
            var containerPaddingLeft = parseInt(feedsAreaComputedStyles.getPropertyValue("padding-left"));
            var containerPaddingRight = parseInt(feedsAreaComputedStyles.getPropertyValue("padding-right"));
            var feedsAreaWidth = containerPosition.width - containerPaddingLeft - containerPaddingRight;
            if (containerPosition.top > anchorPosition) {
                self._dom.feedsArea.style.position = 'relative';
                self._dom.feedsArea.style.top = '0px';
                self._dom.feedsArea.style.bottom = '';
                self._dom.feedsArea.style.left = '0px';
                self._dom.feedsArea.style.width = '100%';

            } else if (maxStickingTop < anchorPosition) {
                self._dom.feedsArea.style.position = 'absolute';
                self._dom.feedsArea.style.top = '';
                self._dom.feedsArea.style.bottom = '0px';
                self._dom.feedsArea.style.left = containerPaddingLeft + 'px';
                self._dom.feedsArea.style.width =  feedsAreaWidth + 'px';

            } else {
                self._dom.feedsArea.style.position = 'fixed';
                self._dom.feedsArea.style.top = anchorPosition + 'px';
                self._dom.feedsArea.style.bottom = '';
                self._dom.feedsArea.style.left = containerPosition.left + containerPaddingLeft + 'px';
                self._dom.feedsArea.style.width =  feedsAreaWidth + 'px';
            }
        };
        Event.observe(document, 'scroll', repositionFeed);

        /*
         * Resize feed based on window height
         */
        var resizeFeed = function() {
            var clientHeight = document.documentElement.clientHeight
                || window.innerHeight
                || document.body.clientHeight;
            var feedsHeight = (parseInt(clientHeight) - self._getFeedAreaTopAnchor());
            self._dom.feedsArea.style.height = feedsHeight + 'px';
            self._dom.metricsArea.style.minHeight = feedsHeight + 'px';

        };
        Event.observe(window, 'resize', function() {
            resizeFeed();
            repositionFeed();
        });
        resizeFeed();



        /*
         * Render items off the main queue once a second
         */
        var autoRender = function() {
            var nextItem = self.pop();
            if (nextItem) {
                if (self._isMouseHovered) {
                    var queueLength = self._hiddenQueue.length + 1;
                    var message = (queueLength == 1? self._updateText.singular:self._updateText.plural)
                        .replace('%s', queueLength);

                    self._hiddenQueue.push(nextItem);
                    self._dom.hiddenUpdatesNotice.update(message);
                    self._dom.hiddenUpdatesNotice.style.display = 'inline';

                } else {
                    self.flushHiddenQueue();
                    self.renderItem(nextItem);
                }
            } else {
                self.flushHiddenQueue();
            }

            self._renderTimer = window.setTimeout(autoRender, 1000);
            return self._renderTimer;
        };

        /*
         * Update the queue once every 10 seconds following the last update
         */
        var autoUpdate = function(callback) {
            return self.update(function(response) {
                if (typeof callback == "function") {
                    callback(response);
                };
                self._updateTimer = window.setTimeout(autoUpdate, 10000);
            });
        };

        /*
         * Update all feed-item timestamps once every minute
         */
        this._timestampTimer = window.setInterval(function() {
            var timeStampElements = $$('#feed-items-reel .feed-item-timestamp');
            timeStampElements.forEach(function(element) {
                var originalTimestamp =  element.readAttribute('data-timestamp');
                var timeAgo = moment(originalTimestamp, moment.ISO_8601).fromNow();
                element.update(timeAgo);
            });
        }, 60000);

        /*
         * Do first update within next 5 seconds.
         */
        window.setTimeout(function() {
            autoUpdate(autoRender);
        }, 5000);

        /*
         * Disable tab switching
         */
        if (varienGlobalEvents) {
            this._dom.feedsArea.down('a').stopObserving('click');
            this._dom.feedsArea.down('a').observe('click', function(event) {
                Event.stop(event);
            });
        }
        return this;
    },

    push: function(item)
    {
        this._queue.push(item);
        if (!this._lastId || item.id > this._lastId) {
            this._lastId = item.id;
        }
        if (!this._firstId || item.id < this._firstId) {
            this._firstId = item.id;
        }

        return this;
    },

    pop: function()
    {
        return this._queue.shift();
    },

    flushHiddenQueue: function()
    {
        while (this._hiddenQueue.length > 0) {
            this.renderItem(this._hiddenQueue.shift());
        }
        this._dom.hiddenUpdatesNotice.style.display = 'none';
        this._dom.hiddenUpdatesNotice.update(this._updateText.plural.replace('%s', 0));

        return this;
    },

    renderItem: function (item, animate, appendToBottom)
    {
        var animate = (typeof animate == "undefined" ? true : animate);
        var timestamp = moment(item.timestamp, moment.ISO_8601);

        var feedItemWrapper = new Element('div', {
            'class':            'feed-item-wrapper'
        });
        var feedItem = new Element('div', {
            'class':            'feed-item ' + item.classes,
            'style':            animate? 'margin-top: -1000px': ''
        });
        var feedTimestamp = new Element('div', {
            'class':            'feed-item-timestamp',
            'data-timestamp':   item.timestamp,
            'title':            timestamp.format('LLLL')
        }).update(timestamp.fromNow());
        var feedMessage = new Element('div', {
            'class':            'feed-item-message'
        }).update(item.message);

        feedItemWrapper.insert(feedItem);
        feedItem.insert(feedTimestamp).insert(feedMessage);
        if (appendToBottom) {
            this._dom.loadPrevious.insert({before: feedItemWrapper});
        } else {
            this._dom.feedReel.insert({top: feedItemWrapper});
        }


        if (animate) {
            var height = parseInt(feedItem.offsetHeight)
                + parseInt(feedItem.style.marginBottom)
                + parseInt(feedItem.style.marginTop);
            feedItem.style.marginTop = (-1 * height) + 'px';
            window.setTimeout(function() {
                feedItem.addClassName('animate');
                feedItem.style.marginTop = '0px';
            }, 500);
        }

        return feedItem;
    },

    renderAllItems: function()
    {
        while (this._queue.length > 0) {
            var item = this.pop();
            this.renderItem(item, false);
        }
        return this;
    },

    /**
     * Ajax call to the server to load next set of transfers
     * @param function callback (optional). Called when ajax call is complete
     */
    update: function(callback)
    {
        var self = this;
        new Ajax.Request(this._updateUrl, {
            method:'get',
            parameters: {
                last_id: self._lastId
            },
            onCreate: function(request) {
                Ajax.Responders.unregister(varienLoaderHandler.handler);
            },
            onSuccess: function(transport) {
                Ajax.Responders.register(varienLoaderHandler.handler);
                var response = transport.responseJSON;
                if (response.error) {
                    return self.updateError(response.error);
                }

                self._queue = self._queue.concat(response.transfers);
                self._lastId = response.last_transfer;
                if (response.transfers.length > 0) {
                    self._dom.emptyEndOfList.hide();
                }
            },
            onFailure: function(transport) {
                self.updateError(transport);
            },
            onComplete: function(response) {
                if (typeof callback == "function") {
                    callback(response);
                }
            }
        });
    },

    loadPrevious: function()
    {
        var countToLoad = 20;
        var self = this;
        new Ajax.Request(self._previousUrl, {
            method:'get',
            parameters: {
                first_id:   self._firstId,
                count:      countToLoad,
            },
            onCreate: function(request) {
                self._dom.loadPrevious.addClassName('loading');
                Ajax.Responders.unregister(varienLoaderHandler.handler);
            },
            onSuccess: function(transport) {
                Ajax.Responders.register(varienLoaderHandler.handler);
                self._dom.loadPrevious.removeClassName('loading');
                var response = transport.responseJSON;
                if (response.error) {
                    return self.updateError(response.error);
                }
                self._firstId = response.first_transfer;
                response.transfers.forEach(function(item) {
                    self.renderItem(item, false, true);
                });

                if (response.transfers.length < countToLoad) {
                    self._dom.loadPrevious.hide();
                    self._dom.nonEmptyEndOfList.show();

                } else {
                    self._dom.loadPrevious.show();
                }

            },
            onFailure: function(transport) {
                self._dom.loadPrevious.down().update('Previous 20');
                self.updateError(transport);
            }
        });
    },

    updateError: function(message)
    {

    },

    _getFeedAreaTopAnchor: function()
    {
        if (this._dom.magentoFloatingHeader && this._dom.magentoFloatingHeader.offsetHeight > 0) {
            return this._dom.magentoFloatingHeader.offsetHeight;
        }

        return 34;
    }
};


if (!Prototype || ! Prototype.Version) {
    console.log('PrototypeJS is not available!');
}