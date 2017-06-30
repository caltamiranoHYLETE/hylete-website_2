//var magicToolboxLinks = [];//defined in header.phtml
var optionLabels = {};
var optionTitles = {};
var optionProductIDs = {};
var choosedOptions = {};
//var magicToolboxProductId = 0;//defined in header.phtml
//var magicToolboxOptionTitles = '';//defined in header.phtml
//var magicToolboxSwitchMetod = 'click';//defined in header.phtml
//var magicToolboxMouseoverDelay = 0;//defined in header.phtml

var allowMagicToolboxChange = true;

function magicToolboxPrepareOptions() {
    var productId;
    if(typeof optionsPrice.productId != 'undefined') {
        productId = optionsPrice.productId;
    } else {
        var inputs = document.getElementsByName('product');
        if(inputs.length) {
            productId = inputs[0].value;
        } else {
            productId = magicToolboxProductId;
        }
    }

    var container = document.getElementById('MagicToolboxSelectors'+productId);
    if(container) {
        var aTagsArray = Array.prototype.slice.call(container.getElementsByTagName('a'));
        for(var i = 0; i < aTagsArray.length; i++) {
            if(aTagsArray[i].getElementsByTagName('img').length) {
                magicToolboxLinks.push(aTagsArray[i]);
            }
        }
    }

    //for products with options
    for(var optionID in optionLabels) {
        var elements = document.getElementsByName('options['+optionID+']');
        if(elements) {
            for(var i = 0, l = elements.length; i < l; i++) {
                var eventType = (elements[i].type == 'radio') ? 'click' : 'change';
                $mjs(elements[i])[mjsAddEventMethod](eventType, function(e) {
                    var objThis = e.target || e.srcElement;
                    var optionID = objThis.name.replace('options[', '').replace(']', '');
                    magicToolboxOnChangeOption(objThis, optionTitles[optionID]);
                });
            }
        }
    }

    //for configurable products
    if(typeof spConfig != 'undefined' && typeof spConfig.config.attributes != 'undefined') {
        for(var attributeID in spConfig.config.attributes) {
            optionLabels[attributeID] = {};
            optionProductIDs[attributeID] = {};
            optionTitles[attributeID] = spConfig.config.attributes[attributeID].label.toLowerCase();
            for(var optionID in spConfig.config.attributes[attributeID].options) {
                var option = spConfig.config.attributes[attributeID].options[optionID];
                if(typeof option == 'object') {
                    optionLabels[attributeID][option.id] = option.label.replace(/(^\s+)|(\s+$)/g, "")/*.replace(/"/g, "'")*/.toLowerCase();
                    optionProductIDs[attributeID][option.id] = {};
                    for(var i = 0, productsLength = option.products.length; i < productsLength; i++) {
                        optionProductIDs[attributeID][option.id][i] = option.products[i];
                    }
                }

                //NOTE: this fix for 'option-associated-with-images' option to work when Mage_ConfigurableSwatches module enabled
                if(typeof option.id != 'undefined') {
                    var swatchElement = document.getElementById('swatch'+option.id);
                    if(swatchElement) {
                        Element.writeAttribute(swatchElement, 'data-attribute-id', attributeID);
                        Event.observe(swatchElement, 'click', function(e) {
                            var attrID = this.getAttribute('data-attribute-id');
                            var objThis = document.getElementById('attribute'+attrID);
                            //NOTE: need delay for IE 8 because the select value is changed too late
                            setTimeout(function(){
                                magicToolboxOnChangeOptionConfigurable(objThis, optionTitles[attrID]);
                            }, 1);
                        });
                    }
                }

            }
            //NOTE: for select in configurable.phtml
            var selectEl = document.getElementById('attribute'+attributeID);
            if(selectEl) {
                $mjs(selectEl)[mjsAddEventMethod]('change', function(e) {
                    var objThis = e.target || e.srcElement;
                    var attrID = objThis.id.replace('attribute', '');
                    magicToolboxOnChangeOptionConfigurable(objThis, optionTitles[attrID]);
                });
            }
        }
    }
    //if(typeof opConfig != 'undefined') opConfig.reloadPrice();

    //NOTE: to swicth between 360, zoom and video
    var isMagicZoom = (magicToolboxTool == 'magiczoom' || magicToolboxTool == 'magiczoomplus'),
        loadVimeoJSFramework = function() {
            //NOTE: to avoid multiple loading
            if(typeof(arguments.callee.loadedVimeoJSFramework) !== 'undefined') {
                return;
            }
            arguments.callee.loadedVimeoJSFramework = true;

            //NOTE: load vimeo js framework
            if(typeof(window.$f) == 'undefined') {
                var firstScriptTag = document.getElementsByTagName('script')[0],
                    newScriptTag = document.createElement('script');
                newScriptTag.async = true;
                newScriptTag.src = 'https://secure-a.vimeocdn.com/js/froogaloop2.min.js';
                firstScriptTag.parentNode.insertBefore(newScriptTag, firstScriptTag);
            }
        },
        loadYoutubeApi = function() {
            //NOTE: to avoid multiple loading
            if(typeof(arguments.callee.loadedYoutubeApi) !== 'undefined') {
                return;
            }
            arguments.callee.loadedYoutubeApi = true;

            //NOTE: load youtube api
            if(typeof(window.YT) == 'undefined' || typeof(window.YT.Player) == 'undefined') {
                var firstScriptTag = document.getElementsByTagName('script')[0],
                    newScriptTag = document.createElement('script');
                newScriptTag.async = true;
                newScriptTag.src = 'https://www.youtube.com/iframe_api';
                firstScriptTag.parentNode.insertBefore(newScriptTag, firstScriptTag);
            }
        },
        pauseYoutubePlayer = function(iframe) {
            if(typeof(arguments.callee.youtubePlayers) === 'undefined') {
                arguments.callee.youtubePlayers = {};
            }
            var id = iframe.getAttribute('id');
            if(id && typeof(arguments.callee.youtubePlayers[id]) != 'undefined') {
                arguments.callee.youtubePlayers[id].pauseVideo();
                return;
            }
            var player = new window.YT.Player(iframe, {
                events: {
                    'onReady': function(event) {
                        event.target.pauseVideo();
                    }
                }
            });
            id = iframe.getAttribute('id');
            arguments.callee.youtubePlayers[id] = player;
            return;
        },
        switchFunction = function(event) {

            event = event || window.event;

            var element = event.target || event.srcElement,
                currentContainer = document.querySelector('.mt-active'),
                currentSlideId = null,
                newSlideId = null,
                newContainer = null,
                switchContainer = false;

            if(!currentContainer) {
                return false;
            }

            if(element.tagName.toLowerCase() != 'a') {
                element = element.parentNode;
                if(element.tagName.toLowerCase() != 'a') {
                    return false;
                }
            }

            currentSlideId = currentContainer.getAttribute('data-magic-slide');
            newSlideId = element.getAttribute('data-magic-slide-id');

            if(currentSlideId == newSlideId/* && currentSlideId == 'zoom'*/) {
                if(isMagicZoom) {
                    allowHighlightActiveSelectorOnUpdate = false;
                    magicToolboxHighlightActiveSelector(element);
                }
                return false;
            }

            //NOTE: check when one image + 360 selector
            newContainer = document.querySelector('div[data-magic-slide="'+newSlideId+'"]');

            if(!newContainer) {
                return false;
            }

            if(newSlideId == 'zoom' && isMagicZoom) {
                //NOTE: in order to magiczoom(plus) was not switching selector
                event.stopQueue && event.stopQueue();
            }

            //NOTE: switch slide container
            currentContainer.className = currentContainer.className.replace(/(\s|^)mt-active(\s|$)/, ' ');
            newContainer.className += ' mt-active';

            if(newSlideId == 'zoom' && isMagicZoom) {
                //NOTE: hide image to skip magiczoom(plus) switching effect
                if(!$mjs(element).jHasClass('mz-thumb-selected')) {
                    document.querySelector('#'+magicToolboxToolMainId+' .mz-figure > img').style.visibility = 'hidden';
                }
                //NOTE: switch image
                MagicZoom.switchTo(magicToolboxToolMainId, element);
                allowHighlightActiveSelectorOnUpdate = false;
                magicToolboxHighlightActiveSelector(element);
            }

            var videoType = null;

            //NOTE: stop previous video slide
            if(currentSlideId.match(/^video\-\d+$/)) {
                //NOTE: need to stop current video
                var iframe = currentContainer.querySelector('iframe');
                if(iframe) {
                    videoType = iframe.getAttribute('data-video-type');
                    if(videoType == 'vimeo') {
                        var vimeoPlayer = window.$f(iframe);
                        if(vimeoPlayer) {
                            vimeoPlayer.api('pause');
                        }
                    } else if(videoType == 'youtube') {
                        pauseYoutubePlayer(iframe);
                    }
                }
            }

            //NOTE: load api for video if need it
            if(newSlideId.match(/^video\-\d+$/)) {
                videoType = element.getAttribute('data-video-type');
                if(videoType == 'vimeo') {
                    loadVimeoJSFramework();
                } else if(videoType == 'youtube') {
                    loadYoutubeApi();
                }
                if(isMagicZoom) magicToolboxHighlightActiveSelector(element);
            }

            if(newSlideId == '360' && isMagicZoom) {
                magicToolboxHighlightActiveSelector(element);
            }

            event.preventDefault ? event.preventDefault() : (event.returnValue = false);

            return false;
        },
        switchEvent;

    if(isMagicZoom || magicToolboxTool == 'magicthumb') {
        if(isMagicZoom) {
            switchEvent = (magicToolboxSwitchMetod == 'click' ? 'btnclick' : magicToolboxSwitchMetod);

            //NOTE: mark thumbnail
            var activeSlide, slideId, query, thumbnail;
            activeSlide = document.querySelector('.magic-slide.mt-active');
            if(activeSlide) {
                slideId = activeSlide.getAttribute('data-magic-slide');
                query = slideId != 'zoom' ? '[data-magic-slide-id="'+slideId+'"]' : '.mz-thumb.mz-thumb-selected';
                thumbnail = document.querySelector(query);
                if(thumbnail) {
                    thumbnail.className += ' active-selector';
                }
            }
        }
        //NOTE: a[data-magic-slide-id]
        for(var j = 0, linksLength = magicToolboxLinks.length; j < linksLength; j++) {
            if(isMagicZoom) {
                //NOTE: if MagicThumb is present
                if(mjsAddEventMethod == 'je1') {
                    $mjs(magicToolboxLinks[j])[mjsAddEventMethod](magicToolboxSwitchMetod, switchFunction);
                    $mjs(magicToolboxLinks[j])[mjsAddEventMethod]('touchstart', switchFunction);
                } else {
                    $mjs(magicToolboxLinks[j])[mjsAddEventMethod](switchEvent+' tap', switchFunction, 1);
                }
            } else if(magicToolboxTool == 'magicthumb') {
                $mjs(magicToolboxLinks[j])[mjsAddEventMethod](magicToolboxSwitchMetod, switchFunction);
                $mjs(magicToolboxLinks[j])[mjsAddEventMethod]('touchstart', switchFunction);
            }
        }

        //NOTE: start magicscroll if need it
        if((typeof(window['MagicScroll']) != 'undefined') && container && container.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
            if(isMagicZoom) {
                MagicScroll.start('MagicToolboxSelectors'+productId);
            } else {
                window.checkForThumbIsReadyIntervalID = setInterval(function() {
                    if(typeof(MagicThumb.thumbs) != 'undefined' && MagicThumb.thumbs.length) {
                        var magicThumbIsReady = true;
                        for (var i = 0; i <  MagicThumb.thumbs.length; i++) {
                            if (!MagicThumb.thumbs[i].ready) {
                                magicThumbIsReady = false;
                                break;
                            }
                        }
                        if(magicThumbIsReady) {
                            MagicScroll.start('MagicToolboxSelectors'+productId);
                            clearInterval(window.checkForThumbIsReadyIntervalID);
                            window.checkForThumbIsReadyIntervalID = null;
                        }
                    }
                }, 100);
            }
        }
    }
}

function magicToolboxHighlightActiveSelector(selectedElement) {
    //NOTE: to highlight selector when switching thumbnails
    for(var i = 0; i < magicToolboxLinks.length; i++) {
        magicToolboxLinks[i].className = magicToolboxLinks[i].className.replace(/(\s|^)active\-selector(\s|$)/, ' ');
    }
    selectedElement.className += ' active-selector';
}

function magicToolboxClickElement(element, eventType, eventName) {
    var event;
    if(document.createEvent) {
        event = document.createEvent(eventType);
        event.initEvent(eventName, true, true);
        element.dispatchEvent(event);
    } else {
        event = document.createEventObject();
        event.eventType = eventType;
        element.fireEvent('on' + eventName, event);
    }
    return event;
}

function magicToolboxOnChangeOption(element, optionTitle) {
    if(!allowMagicToolboxChange) {
        allowMagicToolboxChange = true;
        return;
    }

    if(magicToolboxInArray(optionTitle, magicToolboxOptionTitles)) {
        var id = '';
        if(element.type == 'radio' && element.checked) {
            id = element.name.replace('options[', '').replace(']', '');
        } else if(element.type == 'select-one') {
            id = element.id.replace('select_', '').replace('attribute', '');
        } else {
            return;
        }
        if(element.value == '' || (typeof optionLabels[id][element.value] == 'undefined')) {
            return;
        }
        var label = optionLabels[id][element.value];
        for(var j = 0, linksLength = magicToolboxLinks.length, img = null, alt = null; j < linksLength; j++) {
            img = magicToolboxLinks[j].querySelector('img');
            if(!img) continue;
            alt = img.getAttribute('alt');
            if(!alt) continue;
            if(alt.replace(/(^\s+)|(\s+$)/g, "")/*.replace(/"/g, "'")*/.toLowerCase() == label) {
                if(magicToolboxTool == 'magiczoom' || magicToolboxTool == 'magiczoomplus') {
                    if(magicToolboxSwitchMetod == 'click') {
                        //NOTE: simulate btnclick event
                        magicJS.$(magicToolboxLinks[j]).jCallEvent('btnclick', {target: magicToolboxLinks[j]});
                    } else {
                        allowMagicToolboxChange = false;
                        magicToolboxClickElement(magicToolboxLinks[j], 'MouseEvents', magicToolboxSwitchMetod);
                    }
                } else {
                    //MagicThumb
                    allowMagicToolboxChange = false;
                    magicToolboxClickElement(magicToolboxLinks[j], 'MouseEvents', magicToolboxSwitchMetod);
                }
                break;
            }
        }
    }
}

function magicToolboxOnChangeSelector(a) {
    if(!allowMagicToolboxChange) {
        allowMagicToolboxChange = true;
        return;
    }
    var label = a.firstChild.getAttribute('alt').replace(/(^\s+)|(\s+$)/g, "").toLowerCase();
    var reloadPrice = false;
    for(var optionID in optionLabels) {
        for(var optionValue in optionLabels[optionID]) {
            if(optionLabels[optionID][optionValue] == label && magicToolboxInArray(optionTitles[optionID], magicToolboxOptionTitles)) {

                //NOTE: this fix for 'option-associated-with-images' option to work when Mage_ConfigurableSwatches module enabled
                var swatchElement = document.getElementById('swatch'+optionValue);
                if(swatchElement) {
                    setTimeout(function(element) {
                        return function() {
                            allowMagicToolboxChange = false;
                            magicToolboxClickElement(element, 'Event', 'click');
                        }
                    }(swatchElement), magicToolboxMouseoverDelay);
                    //NOTE: return to prevent double switching when click on selector that has option title
                    return;
                }

                var elementNames = ['options['+optionID+']', 'super_attribute['+optionID+']'];
                for(var elementNameIndex = 0, elementNamesLength = elementNames.length; elementNameIndex < elementNamesLength; elementNameIndex++) {
                    var elements = document.getElementsByName(elementNames[elementNameIndex]);
                    for(var i = 0, l = elements.length; i < l; i++) {
                        if(elements[i].type == 'radio') {
                            if(elements[i].value == optionValue) {
                                setTimeout(function(element) {
                                    return function() {
                                        var radios = document.getElementsByName(element.name);
                                        for(var radioIndex = 0, radiosLength = radios.length; radioIndex < radiosLength; radioIndex++) {
                                            radios[radioIndex].checked = false;
                                        }
                                        element.checked = true;
                                        allowMagicToolboxChange = false;
                                        magicToolboxClickElement(element, 'Event', 'click');
                                    }
                                }(elements[i]), magicToolboxMouseoverDelay);
                                return;
                            }
                        } else if(elements[i].type == 'select-one') {
                            if(elements[i].options && !elements[i].disabled) {
                                for(var j = 0, k = elements[i].options.length; j < k; j++) {
                                    if(elements[i].options[j].value == optionValue) {
                                        setTimeout(function(element, optionValue) {
                                            return function() {
                                                element.value = optionValue;
                                                element.selectedIndex = j;
                                                allowMagicToolboxChange = false;
                                                magicToolboxClickElement(element, 'Event', 'change');
                                            }
                                        }(elements[i], elements[i].options[j].value), magicToolboxMouseoverDelay);
                                        return;
                                    }
                                }
                            }
                        } else {
                            break;
                        }
                    }
                }
            }
        }
    }
}

function magicToolboxOnChangeSelectorConfigurable(a) {
    if(!allowMagicToolboxChange) {
        allowMagicToolboxChange = true;
        return;
    }
    if(typeof useAssociatedProductImages != 'undefined') {
        var productId = a.getAttribute('data-id');
        var options = magicToolboxFindOptions(productId);
        if(typeof(spConfig) != 'undefined' && typeof(spConfig.settings) != 'undefined') {
            setTimeout(function(options) {
                return function() {
                    magicToolboxChangeOptions(0, options);
                }
            }(options), magicToolboxMouseoverDelay);
        }
    }
}

function magicToolboxFindOptions(associatedProductId) {
    var options = {};
    for(var attributeId in optionProductIDs) {
        for(var optionId in optionProductIDs[attributeId]) {
            for(var i in optionProductIDs[attributeId][optionId]) {
                if(associatedProductId == optionProductIDs[attributeId][optionId][i]) {
                    options[attributeId] = optionId;
                }
            }
        }
    }
    return options;
}

function magicToolboxChangeOptions(i, options) {
    var select = spConfig.settings[i];
    var attributeId = select.id.replace(/[a-z]*/, '');
    if(select.options && !select.disabled) {
        for(var j = 0; j < select.options.length; j++) {
            if(select.options[j].value == options[attributeId]) {
                select.value = select.options[j].value;
                select.selectedIndex = j;

                if(select.childSettings) {
                    for(var k = 0; k < select.childSettings.length; k++) {
                        var childAttributeId = select.childSettings[k].id.replace(/[a-z]*/, '');
                        if(typeof choosedOptions[childAttributeId] != 'undefined') {
                            delete choosedOptions[childAttributeId];
                        }
                    }
                }
                choosedOptions[attributeId] = select.value;

                allowMagicToolboxChange = false;
                magicToolboxClickElement(select, 'Event', 'change');
                i++;
                if(i < spConfig.settings.length) {
                    setTimeout(function(i, options) {
                        return function() {
                            magicToolboxChangeOptions(i, options);
                        }
                    }(i, options), 100);
                }
                return;
            }
        }
    }
}

function magicToolboxInArray(needle, haystack) {
    var o = {};
    for(var i = 0, l = haystack.length; i < l; i++) {
        o[haystack[i]] = '';
    }
    if(needle in o) {
        return true;
    }
    return false;
}

function magicToolboxOnChangeOptionConfigurable(element, optionTitle) {

    if(!allowMagicToolboxChange) {
        allowMagicToolboxChange = true;
        return;
    }

    if(typeof useAssociatedProductImages != 'undefined') {

        var attributeId = element.id.replace(/[a-z]*/, '');

        if(typeof choosedOptions[attributeId] != 'undefined') {
            delete choosedOptions[attributeId];
        }

        //clear child elements in choosedOptions
        if(element.childSettings) {
            for(var i=0,l= element.childSettings.length;i<l;i++){
                var childAttributeId = element.childSettings[i].id.replace(/[a-z]*/, '');
                if(typeof choosedOptions[childAttributeId] != 'undefined') {
                    delete choosedOptions[childAttributeId];
                }
            }
        }

        var configurableProductId = spConfig.config.productId;
        //var mainSelectorImage = document.getElementById('imageMain'+configurableProductId);

        if(element.value.length === 0) {
            //if(mainSelectorImage) {
            //    mainSelectorImage.parentNode.click();
            //}
            return;
        }

        var associatedProductId = magicToolboxFindProduct(attributeId, element.value);
        //add new option or replace one
        choosedOptions[attributeId] = element.value;

        var associatedImage = document.getElementById('imageConfigurable'+associatedProductId);
        if(associatedImage) {
            //associatedImage.parentNode.click();
            if(magicToolboxTool == 'magiczoom' || magicToolboxTool == 'magiczoomplus') {
                if(magicToolboxSwitchMetod == 'click') {
                    //NOTE: simulate btnclick event
                    magicJS.$(associatedImage.parentNode).jCallEvent('btnclick', {target: associatedImage.parentNode});
                } else {
                    allowMagicToolboxChange = false;
                    magicToolboxClickElement(associatedImage.parentNode, 'MouseEvents', magicToolboxSwitchMetod);
                }
            } else {
                //MagicThumb
                allowMagicToolboxChange = false;
                magicToolboxClickElement(associatedImage.parentNode, 'MouseEvents', magicToolboxSwitchMetod);
            }
            return;
        } else {
            /*if(mainSelectorImage) {
                //mainSelectorImage.parentNode.click();
                allowMagicToolboxChange = false;
                magicToolboxClickElement(mainSelectorImage.parentNode, 'MouseEvents', magicToolboxSwitchMetod);
            }
            return;*/
        }

    }

    magicToolboxOnChangeOption(element, optionTitle);

}

function magicToolboxFindProduct(attributeId, optionId) {

    for(var i in optionProductIDs[attributeId][optionId]) {
        //product associated with current option
        var pId = optionProductIDs[attributeId][optionId][i];
        for(var attrId in choosedOptions) {
            //selected option's ID
            var optId = choosedOptions[attrId];
            for(var j in optionProductIDs[attrId][optId]) {
                if(pId == optionProductIDs[attrId][optId][j]) {
                    optId = null;
                    break;
                }
            }
            if(optId != null) {
                pId = null;
                break;
            }
        }
        if(pId != null) {
            return pId;
        }
    }
    return optionProductIDs[attributeId][optionId][0];

}

//NOTE: add "PRESELECT COLORS PLUS SWATCHES" support (#58917)
if(typeof(colorSelected) == 'function') {
    function get_image_name(str) {
        return str.replace(/.*\/(.*?)$/i,'$1');
    }
    var colorSelectedBusy = false;
    window['old_colorSelected'] = window['colorSelected'];
    window['colorSelected'] = function() {
        jQuery('#image').attr('style', 'position:absolute;top:-10000px;');
        old_colorSelected.apply(undefined, arguments);
        if(!colorSelectedBusy) {
            jQuery('.MagicToolboxSelectorsContainer a:not([rel=""])').each(function() {
                if(get_image_name(jQuery(this).attr('href')) == get_image_name(jQuery('#image').attr('src'))) {
                    //magicToolboxClickElement(this, 'MouseEvents', magicToolboxSwitchMetod);
                    if(magicToolboxTool == 'magiczoom' || magicToolboxTool == 'magiczoomplus') {
                        MagicZoom.update(magicToolboxToolMainId, this);
                    } else {
                        //MagicThumb
                        magicToolboxClickElement(this, 'MouseEvents', magicToolboxSwitchMetod);
                    }
                }
            });
        }
    }
    window['old_magicToolboxOnChangeSelector'] = window['magicToolboxOnChangeSelector'];
    window['magicToolboxOnChangeSelector'] = function() {
        old_magicToolboxOnChangeSelector.apply(undefined, arguments);
        jQuery('li.swatchContainer img[onclick*="'+get_image_name(jQuery(arguments[0]).attr('href'))+'"]').each(function() {
            colorSelectedBusy = true;
            eval(jQuery(this).attr('onclick'));
            colorSelectedBusy = false;
        });
    }
}
