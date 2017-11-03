;(function ($) {
    'use strict';

    $.widget('vaimo.cmsHeartBeat',  $.vaimo.cmsEditorBase, {
        options: {
            frequency: 10000,
            failThreshold: 1,
            classes: {
                offline: 'vcms-editor-offline'
            }
        },
        failures: 0,
        _create: function() {
            this._super();

            this.queueTick();
        },
        _fail: function() {
            if (++this.failures >= this.options.failThreshold) {
                $(document.body).addClass(this.options.classes.offline);

                return;
            }

            this.queueTick();
        },
        _update: function(data) {
            this.failures = 0;

            if (data !== '1') {
                return;
            }

            this.queueTick();
        },
        queueTick: function() {
            setTimeout(function() {
                this._save({}, null, 'pingEditor');
                this._save({}, null, 'pingAdmin');
            }.bind(this), this.options.frequency);
        }
    });
})(jQuery);
