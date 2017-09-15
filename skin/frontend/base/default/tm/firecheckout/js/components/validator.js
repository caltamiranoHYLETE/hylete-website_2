var FC = FC || {};
FC.Validator = Class.create();
FC.Validator.prototype = {
    options: {
        focusOnError: false // we will smoothly scroll to invalid element and then focus it
    },

    initialize: function(form, options) {
        this.validator = new Validation(
            form,
            Object.extend(this.options, options || {})
        );
        this.afterValidate = [];
    },

    validate: function() {
        var isValid = this.validator.validate();

        this.afterValidate.each(function(afterValidateFunc) {
            if (!afterValidateFunc()) {
                isValid = false;
            }
        });

        if (!isValid) {
            this.scrollToError.bind(this).delay(0.1);
        }

        return isValid;
    },

    scrollToError: function() {
        var messages = $$('.validation-advice, .messages').findAll(function(el) {
            return el.innerHTML && el.visible();
        });
        if (!messages.length) {
            return;
        }

        var timeout = 0,
            visibleMessage = messages.find(this.isElementVisibleInViewport);

        if (!visibleMessage) {
            timeout = 300;
            var visibleMessage = messages[0];
            Effect.ScrollTo(visibleMessage, {
                duration: timeout / 1000,
                offset  : -80
            });
        }

        // shake it after scroll
        setTimeout(function() {
            FC.Utils.shake(visibleMessage);
        }, timeout);
    },

    isElementVisibleInViewport: function(el) {
        var viewportSize = document.viewport.getDimensions(),
            offset = el.viewportOffset();

        return (
            (offset.top > 0 || (offset.top + el.getHeight() > 30)) &&
            offset.left > 0 &&
            offset.top  < viewportSize.height &&
            offset.left < viewportSize.width
        );
    }
};
