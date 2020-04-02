/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

function updateMySubscriptionPost(form) {
    jQuery("div.change,#modal_overlay").hide();
    jQuery("#please-wait").show();
    clearAjaxMessages();
    jQuery.ajax({
        url: form.attr('action'),
        method: "post",
        data: form.serialize(),
        timeout: ajaxTimeout,
        success: function(transport){
            if(transport.match("error")){
                form.parents(".subscription-block").find(".messages").append(transport);
                jQuery("div.adjust,#modal_overlay").hide();
            } else {
                form.parents(".subscription-block").html(transport).find(".messages").append('<li class="success-msg ajax">Your subscription has been updated.</li>');
            }
        },
        error: function() {
            form.parents(".subscription-block").find(".messages").append('<li class="error ajax">An error occurred while updating your subscription!</li>');
            jQuery("div.adjust,#modal_overlay").hide();
        },
        complete: function() {
            jQuery("#please-wait").hide();
        }
    });
}

function updateMySubscriptionGet(form) {
    jQuery("div.adjust,#modal_overlay").hide();
    jQuery("#please-wait").show();
    clearAjaxMessages();
    jQuery.ajax({
        url: form.attr('href'),
        method: "get",
        timeout: ajaxTimeout,
        success: function(transport){
            if(transport.match("error")){
                form.parents(".subscription-block").find(".messages").append(transport);
                jQuery("div.adjust,#modal_overlay").hide();
            } else {
                form.parents(".subscription-block").html(transport).find(".messages").append('<li class="success-msg ajax">Your subscription has been updated.</li>');
            }
        },
        error: function() {
            form.parents(".subscription-block").find(".messages").append('<li class="error ajax">An error occurred while updating your subscription!</li>');
            jQuery("div.adjust,#modal_overlay").hide();
        },
        complete: function() {
            jQuery("#please-wait").hide();
        }
    });

}

function clearAjaxMessages() {
    jQuery(".subscription-block .messages li.ajax").remove();
}

jQuery(document).ready(function(){

    jQuery(".wrapper").after("<div id='modal_overlay'></div>");
    jQuery('#modal_overlay').css({
        position: "absolute",
        top: 0,
        left: 0,
        height: jQuery(document).height(),
        width: "100%",
        zIndex: 900
    }).bind('click', function(){
        jQuery('div.adjust').hide();
        jQuery(this).hide();
    }).hide();

    jQuery(document).on('submit', 'form.payment-form', function(e){
        e.preventDefault();
        var form = jQuery(this).closest("form");
        updateMySubscriptionPost(form);
    }).on('submit', 'form.shipping-form', function(e){
        e.preventDefault();
        var form = jQuery(this).closest("form");
        updateMySubscriptionPost(form);
    }).on('change', 'input[type=radio][name=shipping_address_id]', function(e){
        var ddId = jQuery(this).attr('data-new-address-form');
        var ddEl = jQuery('#'+ddId);
        if (this.value == 'new') {
            ddEl.show();
        }
        else {
            ddEl.hide();
        }
    }).on("change", "input.delivery_qty", function(e){
        e.preventDefault();
        var form = jQuery(this).closest("form");
        updateMySubscriptionPost(form);
    }).on("click", "a.link.more.details", function(e){
        e.preventDefault();
        var link = jQuery(this);
        jQuery('#'+link.attr('href')).toggle();
    }).on("change", "select.delivery_qty", function(e){
        e.preventDefault();
        var form = jQuery(this).closest("form");
        updateMySubscriptionPost(form);
    }).on("change", "select.delivery_interval", function(e){
        e.preventDefault();
        var form = jQuery(this).closest("form");
        updateMySubscriptionPost(form);
    }).on("change", "select.cc_exp_month", function(e){
        e.preventDefault();
        var form = jQuery(this).closest("form");
        updateMySubscriptionPost(form);
    }).on("change", "select.cc_exp_year", function(e){
        e.preventDefault();
        var form = jQuery(this).closest("form");
        updateMySubscriptionPost(form);
    }).on("click", "a.adjust.skip", function(e){
        e.preventDefault();
        var box = jQuery(this).parents(".subscription-block").find("div.adjust.skip-delivery");
        box.css({
            "top": jQuery(window).scrollTop() + 50,
            "left": (jQuery(window).width() / 2) - (box.width() / 2)
            }).show();
        jQuery('#modal_overlay').show();
    }).on("click", "a.adjust.cancel", function(e){
        e.preventDefault();
        var box = jQuery(this).parents(".subscription-block").find("div.adjust.cancel");
        box.css({
            "top": jQuery(window).scrollTop() + 50,
            "left": (jQuery(window).width() / 2) - (box.width() / 2)
        }).show();
        jQuery('#modal_overlay').show();
    }).on("click", "a.adjust.pause", function(e){
        e.preventDefault();
        var box = jQuery(this).parents(".subscription-block").find("div.adjust.pause");
        box.css({
            "top": jQuery(window).scrollTop() + 50,
            "left": (jQuery(window).width() / 2) - (box.width() / 2)
        }).show();
        jQuery('#modal_overlay').show();
    }).on("click", "a.adjust.restart", function(e){
        e.preventDefault();
        var box = jQuery(this).parents(".subscription-block").find("div.adjust.restart");
        box.css({
            "top": jQuery(window).scrollTop() + 50,
            "left": (jQuery(window).width() / 2) - (box.width() / 2)
        }).show();
        jQuery('#modal_overlay').show();
    }).on("click", "button.no", function(e){
        jQuery("div.adjust,#modal_overlay").hide();
    }).on("click", "button.skip_yes", function(e){
        e.preventDefault();
        var form = jQuery(this);
        updateMySubscriptionGet(form);
    }).on("click", "button.cancel_yes", function(e){
        e.preventDefault();
        var form = jQuery(this);
        updateMySubscriptionGet(form);
    }).on("click", "button.pause_yes", function(e){
        e.preventDefault();
        var form = jQuery(this);
        updateMySubscriptionGet(form);
    }).on("click", "button.restart_yes", function(e){
        e.preventDefault();
        var form = jQuery(this);
        updateMySubscriptionGet(form);
    }).on('click', ".subscription-more-details a.adjust", function(e){
        e.preventDefault();
        var box = jQuery(this).parents(".block").find("div.adjust");
        box.css({
            "top": jQuery(window).scrollTop() + 50,
            "left": (jQuery(window).width() / 2) - (box.width() / 2)
        }).show();
        jQuery('#modal_overlay').show();
    });

});
