if (!Dialog._vaimoMenuOldConfirm) {
    Dialog._vaimoMenuOldConfirm = Dialog.confirm;
    Dialog._vaimoMenuOldOkCallback = Dialog.okCallback;

    Dialog.confirm = function(content, params) {
        if (!params || !params.vaimoMenuDialog) {
            return Dialog._vaimoMenuOldConfirm(content, params);
        }

        this._definedHeight = params.height;
        var contentLoaded = false;
        if (content && typeof content == 'string') {
            content.evalScripts.bind(content).defer();
            contentLoaded = true;
        }

        var dialog = Dialog._vaimoMenuOldConfirm(content, params);

        if (dialog) {
            this.fixDialogSize = function() {
                var container = dialog.getContent().down('.magento_message').up('div');
                var dialogContainer = container.up('.dialog');
                var h = dialogContainer.offsetHeight - container.offsetHeight;
                var w = 0;

                $(container).childElements().each(
                    function (element) {
                        w = Math.max($(element).offsetWidth, w);
                        h += $(element).offsetHeight;
                    }
                );

                if (h > 0) {
                    dialog.setSize(w, Math.max(this._definedHeight, h), true);
                }
            }.bind(this);

        }

        if (contentLoaded && params.onContentLoadCallback && dialog) {
            params.onContentLoadCallback(dialog);
        }

        if (contentLoaded) {
            setTimeout(this.fixDialogSize.bind(this), 100);
        }
    };

    Dialog.okCallback = function() {
        var win = Windows.focusedWindow;

        if (!this.parameters || this.parameters.onOkCallback(win)) {
            Dialog._vaimoMenuOldOkCallback();
        }
    };
}
