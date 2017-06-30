;(function(){
    var _confirm = Dialog.confirm;
    Dialog.confirm = function(content, params) {
        if (content && typeof content == 'string') {
            content.evalScripts.bind(content).defer();
        }

        return _confirm.apply(this, [content, params]);
    };

    var _okCallback = Dialog.okCallback;
    Dialog.okCallback = function() {
        var window = Windows.focusedWindow;

        if (!Windows.focusedWindow) {
            return;
        }

        if (!this.parameters || !this.parameters.onOkCallback) {
            return;
        }

        if (this.parameters.onOkCallback(window)) {
            _okCallback.apply(this)
        }
    };
})();