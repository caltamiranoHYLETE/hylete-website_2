

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *     https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
/**
 * This is a mod to the Script.aculo.us Control.Slider utility to make it smooth slide.
 * <b>Depends on:</b> Control.Slider, Effect.Move 
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */

var SmoothSlider = Class.create(Control.Slider, {
    initialize: function (handle, track, options) {
        var slider = this;

        if (Object.isArray(handle)) {
            this.handles = handle.collect(function (e) {
                return $(e)
            });
        } else {
            this.handles = [$(handle)];
        }

        this.track = $(track);
        this.options = options || {};

        this.axis = this.options.axis || 'horizontal';
        this.increment = this.options.increment || 1;
        this.step = parseInt(this.options.step || '1');
        this.range = this.options.range || $R(0, 1);

        this.value = 0; // assure backwards compat
        this.values = this.handles.map(function () {
            return 0
        });
        this.spans = this.options.spans ? this.options.spans.map(function (s) {
            return $(s)
        }) : false;
        this.options.startSpan = $(this.options.startSpan || null);
        this.options.endSpan = $(this.options.endSpan || null);

        this.restricted = this.options.restricted || false;

        this.maximum = this.options.maximum || this.range.end;
        this.minimum = this.options.minimum || this.range.start;

        // Will be used to align the handle onto the track, if necessary
        this.alignX = parseInt(this.options.alignX || '0');
        this.alignY = parseInt(this.options.alignY || '0');

        this.trackLength = this.maximumOffset() - this.minimumOffset();

        this.handleLength = this.isVertical() ?
                (this.handles[0].offsetHeight != 0 ?
                        this.handles[0].offsetHeight : this.handles[0].style.height.replace(/px$/, "")) :
                (this.handles[0].offsetWidth != 0 ? this.handles[0].offsetWidth :
                        this.handles[0].style.width.replace(/px$/, ""));

        this.active = false;
        this.dragging = false;
        this.disabled = false;

        if (this.options.disabled)
            this.setDisabled();

        // Allowed values array
        this.allowedValues = this.options.values ? this.options.values.sortBy(Prototype.K) : false;
        if (this.allowedValues) {
            this.minimum = this.allowedValues.min();
            this.maximum = this.allowedValues.max();
        }

        this.eventMouseDown = this.startDrag.bindAsEventListener(this);
        this.eventMouseUp = this.endDrag.bindAsEventListener(this);
        this.eventMouseMove = this.update.bindAsEventListener(this);

        // Initialize handles in reverse (make sure first handle is active)
        this.handles.each(function (h, i) {
            i = slider.handles.length - 1 - i;
            slider.setValue(parseFloat(
                    (Object.isArray(slider.options.sliderValue) ?
                            slider.options.sliderValue[i] : slider.options.sliderValue) ||
                    slider.range.start), i);
            h.makePositioned().observe("mousedown", slider.eventMouseDown);
        });

        this.track.observe("mousedown", this.eventMouseDown);
        this.track.observe("touchstart", this.eventMouseDown);

        document.observe("mouseup", this.eventMouseUp);
        document.observe("touchend", this.eventMouseUp);

        $(this.track.parentNode.parentNode).observe("mousemove", this.eventMouseMove);
        $(this.track.parentNode.parentNode).observe("touchmove", this.eventMouseMove);


        this.initialized = true;
    },
    dispose: function () {
        var slider = this;
        Event.stopObserving(this.track, "mousedown", this.eventMouseDown);
        Event.stopObserving(this.track, "touchstart", this.eventMouseDown);
        Event.stopObserving(document, "mouseup", this.eventMouseUp);
        Event.stopObserving(document, "touchend", this.eventMouseUp);
        Event.stopObserving(this.track.parentNode.parentNode, "mousemove", this.eventMouseMove);
        Event.stopObserving(this.track.parentNode.parentNode, "touchmove", this.eventMouseMove);
        this.handles.each(function (h) {
            Event.stopObserving(h, "mousedown", slider.eventMouseDown);
            Event.stopObserving(h, "touchstart", slider.eventMouseDown);
        });
    },
    setValue: function (sliderValue, handleIdx) {
        if (!this.active) {
            this.activeHandleIdx = handleIdx || 0;
            this.activeHandle = this.handles[this.activeHandleIdx];
            this.updateStyles();
        }
        handleIdx = handleIdx || this.activeHandleIdx || 0;
        if (this.initialized && this.restricted) {
            if ((handleIdx > 0) && (sliderValue < this.values[handleIdx - 1]))
                sliderValue = this.values[handleIdx - 1];
            if ((handleIdx < (this.handles.length - 1)) && (sliderValue > this.values[handleIdx + 1]))
                sliderValue = this.values[handleIdx + 1];
        }
        sliderValue = this.getNearestValue(sliderValue);
        this.values[handleIdx] = sliderValue;
        this.value = this.values[0]; // assure backwards compat

        // WDCA CODE BEGIN -->>
        if (this.slideFxBusy == true) {
            if (this.slideFx) {
                this.slideFx.cancel();
                this.slideFxBusy = false;
                this.handles[handleIdx].style[this.isVertical() ? 'top' : 'left'] = this.translateToPx(sliderValue);
            }
        } else {
            this.slideFxBusy = true;
            //Edited 10/03/2010 4:37:21 AM : to fix IE7-8 bug
            var translated_value = this.translateToPx(sliderValue);
            if (translated_value != "NaNpx") {
                var move_x = this.isVertical() ? 0 : parseInt(translated_value);
                var move_y = this.isVertical() ? parseInt(translated_value) : 0;
                this.slideFx = new Effect.Move(this.handles[handleIdx], {
                    x: move_x,
                    y: move_y,
                    mode: 'absolute',
                    duration: 0.4,
                    afterFinish: function () {
                        this.slideFxBusy = false;
                    }.bindAsEventListener(this)
                });
            }
        }
        // <<-- WDCA CODE END

        this.isMoving = false;
        //    this.handles[handleIdx].style[this.isVertical() ? 'top' : 'left'] =  this.translateToPx(sliderValue); // WDCA COMMENTED THIS

        this.drawSpans();
        if (!this.dragging || !this.event)
            this.updateFinished();
    },
    startDrag: function (event) {
        if (!this.disabled) {
            this.active = true;

            var handle = Event.element(event);
            var pointer = [this.getPointerX(event), this.getPointerY(event)];
            var track = handle;
            if (track == this.track) {
                var offsets = Position.cumulativeOffset(this.track);
                this.event = event;
                this.setValue(this.translateToValue(
                        (this.isVertical() ? pointer[1] - offsets[1] : pointer[0] - offsets[0]) - (this.handleLength / 2)
                        ));
                var offsets = Position.cumulativeOffset(this.activeHandle);
                this.offsetX = (pointer[0] - offsets[0]);
                this.offsetY = (pointer[1] - offsets[1]);
            } else {
                // find the handle (prevents issues with Safari)
                while ((this.handles.indexOf(handle) == - 1) && handle.parentNode)
                    handle = handle.parentNode;

                if (this.handles.indexOf(handle) != -1) {
                    this.activeHandle = handle;
                    this.activeHandleIdx = this.handles.indexOf(this.activeHandle);
                    this.updateStyles();

                    var offsets = Position.cumulativeOffset(this.activeHandle);
                    this.offsetX = (pointer[0] - offsets[0]);
                    this.offsetY = (pointer[1] - offsets[1]);
                }
            }
        }
        Event.stop(event);
    },
    draw: function (event) {
        var pointer = [this.getPointerX(event), this.getPointerY(event)];
        var offsets = Position.cumulativeOffset(this.track);
        pointer[0] -= this.offsetX + offsets[0];
        pointer[1] -= this.offsetY + offsets[1];
        this.event = event;
        this.setValue(this.translateToValue(this.isVertical() ? pointer[1] : pointer[0]));
        if (this.initialized && this.options.onSlide)
            this.options.onSlide(this.values.length > 1 ? this.values : this.value, this);
    },
    getPointerX: function (event) {
        var docElement = document.documentElement;
        var body = document.body || {scrollLeft: 0};

        if (event.changedTouches) {
            return (event.changedTouches[0].clientX +
                (docElement.scrollLeft || body.scrollLeft) -
                (docElement.clientLeft || 0));
        }

        return event.pageX || (event.clientX +
            (docElement.scrollLeft || body.scrollLeft) -
            (docElement.clientLeft || 0));

    },
    getPointerY: function (event) {
        var docElement = document.documentElement;
        var body = document.body || {scrollTop: 0};

        if (event.changedTouches) {
            return (event.changedTouches[0].clientY +
                (docElement.scrollTop || body.scrollTop) -
                (docElement.clientTop || 0));
        }

        return  event.pageY || (event.clientY +
            (docElement.scrollTop || body.scrollTop) -
            (docElement.clientTop || 0));
    }
});
