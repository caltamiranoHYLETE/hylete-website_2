(function ($) {
    "use strict";

    $.widget('vaimo.blockAjaxLoader', {
        loader: false,
        requestsInProgress: {},
        requestCounter: {},
        allowBlankUrl: false,
        lastRequest: false,
        uniqueRequests: true,
        options: {
            displayLoaderThreshold: 40,
            displayLoader: true,
            allowMultipleRequests: false,
            defaultRequestToken: '',
            loader: '#js-blockajax-loader'
        },
        _getNextRequestId: function(groupName) {
            if (typeof this.requestCounter[groupName] == 'undefined') {
                this.requestCounter[groupName] = 0;
            }

            return ++this.requestCounter[groupName];
        },
        _getLatestRequestId: function(groupName) {
            return this.requestCounter[groupName];
        },
        _send: function(url, data) {
            if (!this.options.allowBlankUrl && (!url || !url.length)) {
                console.error('Ajax call done against empty blank url. Use allowBlankUrl option to allow this');
            }

            var groupName = data.block_ajax;

            var request = $.ajax({
                type: 'GET',
                url: url,
                data: data,
                beforeSend: function() {
                    this.requestsInProgress[groupName]++;
                    this._beforeSend(groupName);
                }.bind(this),
                dataType: 'json'
            });

            request.done(function(transport) {
                if (transport) {
                    transport.url = url;
                    return this._success(transport);
                }

                return false;
            }.bind(this));

            request.error($.proxy(this._error, this));

            request.always(function() {
                if (--this.requestsInProgress[groupName] <= 0) {
                    this.requestsInProgress[groupName] = 0;
                    this._complete(groupName);
                }
            }.bind(this));
        },
        _isDuplicateRequest: function(url, data) {
            return this.lastRequest.url == url && this.lastRequest.data == JSON.stringify(data);
        },
        _createAjaxRequest: function(url, groupName) {
            if (typeof groupName == 'undefined' || !groupName) {
                groupName = this.options.defaultRequestToken;
            }

            if (!(groupName in this.requestsInProgress)) {
                this.requestsInProgress[groupName] = 0;
            }

            var requestData = {
                block_ajax: groupName
            };

            if (!this.options.allowMultipleRequests && this.requestsInProgress[groupName] > 0) {
                return;
            }

            if (this.lastRequest && this.uniqueRequests && this._isDuplicateRequest(url, requestData)) {
                return;
            }

            this.lastRequest = {
                url: url,
                data: JSON.stringify(requestData)
            };

            if (this.options.allowMultipleRequests) {
                requestData.request_id = this._getNextRequestId(groupName);
            }

            this._send(url, requestData);
        },
        _beforeSend: function(groupName) {
            if (this.options.displayLoader) {
                setTimeout(function() {
                    if (this.requestsInProgress[groupName] > 0) {
                        this._showLoader(groupName);
                    }
                }.bind(this), this.options.displayLoaderThreshold);
            }
        },
        _getLoader: function() {
            if (this.loader === false) {
                this.loader = $(this.options.loader);
            }

            return this.loader;
        },
        _showLoader: function(groupName) {
            var loader = this._getLoader();

            loader.show();
        },
        _hideLoader: function(groupName) {
            var loader = this._getLoader();

            loader.hide();
        },
        _isResponseToLatestRequest: function(transport) {
            return !this.options.allowMultipleRequests ||
                transport.request_id == this._getLatestRequestId(transport.block_ajax);
        },
        _isValidResponse: function(transport) {
            return 'blocks' in transport;
        },
        _success: function(transport) {
            if (!this._isValidResponse(transport)) {
                return;
            }

            if (!this._isResponseToLatestRequest(transport)) {
                return;
            }

            this._applyResponse(transport);
        },
        _applyResponse: function(transport) {
            transport.blocks.sort(function(a, b) {
                return this.isBefore($(a.selector), $(b.selector));
            }.bind(this));

            $.each(transport.blocks, function(_, block) {
                this._processBlockData(block);
            }.bind(this));
        },
        isBefore: function($objectA, $objectB) {
            $objectA = $objectA.eq(0);
            $objectB = $objectB.eq(0);
            var result = 0;

            var testClass = 'order-test';
            var testFirstClass = 'order-test-first';

            $objectA.addClass(testClass);

            if ($objectB.hasClass(testClass)) {
                result = 0;
            }

            $objectB.addClass(testClass);

            var $elements = $('.' + testClass);
            $elements.eq(0).addClass(testFirstClass);

            if ($objectA.hasClass(testFirstClass)) {
                result = -1;
            } else if ($objectB.hasClass(testFirstClass)) {
                result = 1;
            }

            $('.' + testClass + ', .' + testFirstClass).removeClass(testClass).removeClass(testFirstClass);

            return result;
        },
        _processBlockData: function(block) {
            if (block.target) {
                this._replaceContent(block.target.selector, block.html);
            }
        },
        _replaceContent: function(selector, html) {
            if (!selector) {
                return;
            }

            if (html) {
                $(selector).replaceWith(html);
                return;
            }

            $(selector).empty();
            $(selector).hide();
        },
        _complete: function(groupName) {
            if (this.options.displayLoader) {
                this._hideLoader(groupName);
            }
        },
        _error: function(xhr) {
            var requestWasAborted = (xhr.status === 0 && xhr.statusText === 'error' && xhr.responseText === '');
            var badResponse = (xhr.status === 200 && xhr.responseText !== '');
            var blankResponse = (xhr.status === 200 && xhr.responseText === '');

            if (badResponse) {
                console.error('BlockAjax response (' + xhr.status + '): ' + 'Invalid data');
                return;
            }

            if (blankResponse) {
                console.error('BlockAjax response (' + xhr.status + '): ' + 'Blank response');
                return;
            }

            if (requestWasAborted) {
                return;
            }

            console.error('BlockAjax response (' + xhr.status + '): ' + xhr.statusText);

            alert(Translator.translate('An error occurred. Please try again!'));
        }
    });

    /**
     * Allow multiple selectors to be defined for single block
     */
    $.widget('vaimo.blockAjaxLoader', $.vaimo.blockAjaxLoader, {
        _replaceContent: function(selector, html) {
            if (!selector) {
                return;
            }

            var selectors = selector.split(',');

            $.each(selectors, function(_, selector) {
                var $container = $(selector);
                if ($container.length) {
                    if ($container.length == 1) {
                        this._super(selector, html);

                        return false;
                    } else {
                        console.error('AjaxBlock: More than one container found for selector: ' + selector);
                        return false;
                    }
                }
            }.bind(this));
        }
    });

    /**
     * Allow script elements to be evaluated without selector
     */
    $.widget('vaimo.blockAjaxLoader', $.vaimo.blockAjaxLoader, {
        _processBlockData: function(block) {
            if (block.target && block.target.script) {
                try {
                    var html = $.parseHTML(block.html, document, true);
                    $(document).append(html);
                } catch (e) {
                    console.error('AjaxBlock: Could not evaluate script-only response (' + e.message + ')');
                }
            }

            this._superApply(arguments);
        }
    });
})(jQuery);