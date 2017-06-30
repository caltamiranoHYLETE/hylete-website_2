(function ($) {
    "use strict";

    $.widget('vaimo.infiniteScroll', $.vaimo.infiniteScroll, {
        options: {
            scope: window,
            intervalFrequency: 300,
            scrollLimit: 300,
            fireDelay: 150,
            scrollAnimationDuration: 100
        },

        _create: function() {
            this._super.apply(this, arguments);
            if(this.options.state.pageNumberLanding) {
                this._intervalCheck();
            }
        },

        _reInit: function() {
            this._super.apply(this, arguments);

            this.options.state.target = undefined;
            this.options.state.targetId = undefined;
            this.options.state.lastScrollTop = 0;
            this.options.state.scrollDirection = '';

            this._detectTarget();
        },

        _registerEventHandlers: function() {
            this._super.apply(this, arguments);
            if(!this._isInfiniteLoadingDisabled()) {
                $(this.options.scope).on('scroll', this._throttle(this._onScopeScroll, 250).bind(this));
            }
        },

        _onScopeScroll: function(event) {
            if(!this.options.state.scrollLoadingDisabled) {
                return this._detectScrollDirection();;
            }
        },

        _detectScrollDirection: function() {
            var currentScrollTop;

            currentScrollTop = $(this.options.state.target).scrollTop();
            if(currentScrollTop > this.options.state.lastScrollTop) {
                this.options.state.scrollDirection = 'next';
            } else if(currentScrollTop !== this.options.state.lastScrollTop) {
                this.options.state.scrollDirection = 'prev';
            }

            return this.options.state.lastScrollTop = currentScrollTop;
        },

        _detectTarget: function() {
            this.options.state.target = $(this.options.scope);
            return this.options.state.targetId = $(this.options.state.target).attr('id');
        },

        _intervalCheck: function() {
            return setTimeout(this._intervalCheck.bind(this), this.options.intervalFrequency);
        },

        _getScrollableAreaMargin: function($container, $target) {
            var margin;
            switch (this.options.state.scrollDirection) {
                case 'next':
                    margin = $container.height() - $target.height() <= $target.scrollTop() + this.options.scrollLimit;
                    break;
                case 'prev':
                    margin = $target.scrollTop() <= this.options.scrollLimit;
            }
            return margin;
        },

        _shouldLoadPage: function() {
            if(this.options.state.scrollLoadingDisabled) {
                return false;
            }

            if(this.options.state.isDelay) {
                return false;
            }

            if(!this._getScrollableAreaMargin($(document), $(window))) {
                return false;
            }

            return true;

        },

        _setDelay: function() {
            this.options.state.isDelay = true;
            setTimeout(function() {
                this.options.state.isDelay = false;
            }.bind(this), this.options.fireDelay);
        }
    });
})(jQuery);