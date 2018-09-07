/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Varien
 * @package     js
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * @classDescription simple Navigation with replacing old handlers
 * @param {String} id id of ul element with navigation lists
 * @param {Object} settings object with settings
 */
var mainNav = function() {

    var main = {
        obj_nav :   $(arguments[0]) || $("nav"),

        settings :  {
            show_delay      :   0,
            hide_delay      :   0,
            _ie6            :   /MSIE 6.+Win/.test(navigator.userAgent),
            _ie7            :   /MSIE 7.+Win/.test(navigator.userAgent)
        },

        init :  function(obj, level) {
            obj.lists = obj.childElements();
            obj.lists.each(function(el,ind){
                main.handlNavElement(el);
                if((main.settings._ie6 || main.settings._ie7) && level){
                    main.ieFixZIndex(el, ind, obj.lists.size());
                }
            });
            if(main.settings._ie6 && !level){
                document.execCommand("BackgroundImageCache", false, true);
            }
        },

        handlNavElement :   function(list) {
            if(list !== undefined){
                list.onmouseover = function(){
                    main.fireNavEvent(this,true);
                };
                list.onmouseout = function(){
                    main.fireNavEvent(this,false);
                };
                if(list.down("ul")){
                    main.init(list.down("ul"), true);
                }
            }
        },

        ieFixZIndex : function(el, i, l) {
            if(el.tagName.toString().toLowerCase().indexOf("iframe") == -1){
                el.style.zIndex = l - i;
            } else {
                el.onmouseover = "null";
                el.onmouseout = "null";
            }
        },

        fireNavEvent :  function(elm,ev) {
            if(ev){
                if (jQuery(elm).hasClass("menu-search")) {
                    return;
                }
                elm.addClassName("over");
                jQuery(elm).closest("ul#nav").addClass("menu-over");

                /* START - Additional change: To avoid JS error when no <a> exists */
                var a = elm.down("a");
                if (typeof a !== 'undefined') {
                    a.addClassName("over");
                }
                /* END */

                if (elm.childElements()[1]) {
                    //main.show(elm.childElements()[1]);
                    main.show(elm.childElements()[2]);
                    setTimeout(function() {
                        jQuery('#menu-overlay').stop().fadeTo(250, 0.2);
                    }, main.settings.show_delay);
                }
            } else {
                elm.removeClassName("over");
                jQuery(elm).closest("ul#nav").removeClass("menu-over");

                /* START - Additional change: To avoid JS error when no <a> exists */
                var a = elm.down("a");
                if (typeof a !== 'undefined') {
                    a.removeClassName("over");
                }
                /* END */

                if (elm.childElements()[1]) {
                    //main.hide(elm.childElements()[1]);
                    main.hide(elm.childElements()[2]);
                    setTimeout(function() {
                        jQuery('#menu-overlay').stop().fadeOut();
                    }, main.settings.show_delay);
                }
            }
        },

        show : function (sub_elm) {
            if(sub_elm){
                if (sub_elm.hide_time_id) {
                    clearTimeout(sub_elm.hide_time_id);
                }
                sub_elm.show_time_id = setTimeout(function() {
                    if (jQuery(sub_elm).is('.level0.menu-vlist') && carbon.getCurrentGrid() != 'xs' && carbon.getCurrentGrid() != 'sm') {
                        jQuery(sub_elm).fadeIn(150);
                    }
                }, main.settings.show_delay);
            }

        },

        hide : function (sub_elm) {
            if(sub_elm){
                if (sub_elm.show_time_id) {
                    clearTimeout(sub_elm.show_time_id);
                }
                sub_elm.hide_time_id = setTimeout(function(){
                    if (jQuery(sub_elm).is('.level0.menu-vlist') && carbon.getCurrentGrid() != 'xs' && carbon.getCurrentGrid() != 'sm') {
                        jQuery(sub_elm).fadeOut(150);
                    }
                }, main.settings.hide_delay);
            }
        }

    };
    if (arguments[1]) {
        main.settings = Object.extend(main.settings, arguments[1]);
    }
    if (main.obj_nav) {
        main.init(main.obj_nav, false);
    }
    
    jQuery(document).on('touchend', function(e) {
        var withinMenu = jQuery(e.target).closest('#nav').length;
        if (withinMenu == 0) {
            window._dblTapItem = false;
            jQuery('.over').removeClass('over');
            
            if (!carbon.getCurrentGrid() == 'xs' && !carbon.getCurrentGrid() == 'sm') {
                jQuery('li.level0 >ul.level0').slideUp();
            }
        }
    });

    return main;
};

document.observe("dom:loaded", function() {
    //run navigation without delays and with default id="#nav"
    //mainNav();

    //run navigation with delays
    mainNav("nav", {"show_delay":"150","hide_delay":"150"});
});