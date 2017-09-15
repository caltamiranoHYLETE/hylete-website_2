/**
 * Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, Sweet Tooth is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by Sweet Tooth, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time Sweet Tooth spent 
 * during the support process.
 * Sweet Tooth does not guarantee compatibility with any other framework extension. 
 * Sweet Tooth is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2017 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
/**
 * Sweet Tooth Order Creation
 * @requires js/tbt/scriptaculous/SmoothSlider.js
 * @requires js/tbt/rewards/adminhtml/CatalogRedemptionSlider.js
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     Sweet Tooth Sweet Tooth Team <support@sweettoothrewards.com>
 */

var sweettooth =  typeof sweettooth !== 'undefined' ? sweettooth : window.sweettooth || {};
sweettooth.OrderCreate = sweettooth.OrderCreate || {};

/**
 * Used to set a static global variable to sweettooth namespace
 */
window.sweettooth.setSweettoothGlobalVar = function(varName, varValue) {
    sweettooth._global = sweettooth._global || {};
    sweettooth._global[varName] = varValue;
}

/**
 * Used to set a static global variable to sweettooth namespace
 */
window.sweettooth.addSweettoothGlobalVar = function(varName, varValue) {
    sweettooth._global = sweettooth._global || {};
    
    if (typeof sweettooth._global[varName] === 'undefined') {
        sweettooth._global[varName] = {};
    }
    
    Object.extend(sweettooth._global[varName], varValue);
}

/**
 * Used to retrieve a static global variable from sweettooth namespace
 */
window.sweettooth.getSweettoothGlobalVar = function(varName) {
    sweettooth._global = sweettooth._global || {};
    
    return sweettooth._global[varName];
}

/**
 * Used to retrieve a callable global variable from sweettooth namespace
 */
window.sweettooth.getSweettoothGlobalCallableVar = function(varName) {
    sweettooth._global = sweettooth._global || {};
    
    return sweettooth._global[varName]();
}

/**
 * Used to handle entire process of Points Spending in Admin Order Creation
 */
sweettooth.OrderCreate.ProductSearch = {
    configureLinksIdentifier: null,
    url: null,
    modalWindowOpts: {
        modalWindowContainerId: 'product_rewards_configure_modal',
        popupWindowMask: 'popup-window-mask',
        screenHeight: null,
        screenWidth:  null
    },
    currentModalWindowLinkObj: null,
    currentProductId: null,
    rulesConfigs: null,
    spendingDisplayOpts: {
        displayContainerId: 'rewards-product-info-container',
        displayDiscountPriceId: 'rewards-product-info-discount-price',
        displayDiscountPointsId: 'rewards-product-info-discount-points',
        displayDiscountRemove: 'rewards-product-info-remove'
    },
    customerBalanceConfiguredId: 'rewards-customer-balance-configured',
    customerBalanceContainerId: 'rewards-order-create-customer-balance-area',
    orderSearchContainerId: 'order-search',
    productSearchGridTableId: 'sales_order_create_search_grid_table',
    totalSpentObjIdentifier: '.cart-points-total_spent span.price',
    pointsConfiguredMap: {},
    init: function(url, configureLinksIdentifier, modalWindowOpts, 
        spendingDisplayOpts, customerBalanceConfiguredId, customerBalanceContainerId,
        productSearchGridTableId
    ) {
        var self = this;
        this.url = url;
        this.configureLinksIdentifier = configureLinksIdentifier;
        this.modalWindowOpts = Object.extend(this.modalWindowOpts, modalWindowOpts);
        this.spendingDisplayOpts = Object.extend(this.spendingDisplayOpts, spendingDisplayOpts);

        if (typeof customerBalanceConfiguredId !== 'undefined') {
            this.customerBalanceConfiguredId = customerBalanceConfiguredId;
        }
        
        if (typeof customerBalanceContainerId !== 'undefined') {
            this.customerBalanceContainerId = customerBalanceContainerId;
        }
        
        if (typeof productSearchGridTableId !== 'undefined') {
            this.productSearchGridTableId = productSearchGridTableId;
        }

        this.bindEvents();
        
        setTimeout(function() {
            self.updatePointsOnlyPrices();
        },100);
        
        this.initializePredefinedData();

        return this;
    },
    bindEvents: function() {
        this.bindConfigureLinks();
        this.bindPointsOnlySelection();
        this.bindFormSubmit();
    },
    bindConfigureLinks: function() {
        var self = this;
        
        $$(self.configureLinksIdentifier).invoke('stopObserving','click');
        $$(self.configureLinksIdentifier).invoke('observe','click', function(event) {
            /* Avoid triggering magento default row selection and product configuration modal window */
            Event.stop(event);
            
            self.currentModalWindowLinkObj = this;
            var productId = this.readAttribute('product_id');
            var catalogRedemptionRule = this.readAttribute('catalog_redemption_rule');
            var catalogRedemptionRuleUses = this.readAttribute('catalog_redemption_rule_uses');

            self.helper.triggerProductSelectionIfNotConfigurable(self.currentModalWindowLinkObj);

            var response = self.fetchContent(productId);

            self.showModalWindow(response, catalogRedemptionRule, catalogRedemptionRuleUses);
        });
    },
    bindPointsOnlySelection: function() {
        var self = this;
        var productSearchTableObj = $(self.productSearchGridTableId);
        
        if (!productSearchTableObj) {
            return;
        }
        
        Element.select(productSearchTableObj, 'input:checkbox').invoke('observe', 'click', function(event) {
            self.showPointsOnlyInfo(this);
        });
    },
    bindFormSubmit: function() {
        var self = this;
        document.observe('product_to_add:afterIFrameLoaded', function() {
            self.pointsConfiguredMap = {};
            $$(self.configureLinksIdentifier).each(function(modalWindowLinkObj){
                modalWindowLinkObj.writeAttribute('catalog_redemption_rule', null);
                modalWindowLinkObj.writeAttribute('catalog_redemption_rule_uses', null);
                modalWindowLinkObj.writeAttribute('catalog_redemption_points_used', null);
                modalWindowLinkObj.writeAttribute('catalog_redemption_price_discount', null);
                
                self.hideProductInfoSpending(modalWindowLinkObj);
            });
        });
    },
    showPointsOnlyInfo: function(el) {
        var self = this;

        if (!el) {
            return;
        }
        
        var trColumn = el.up('tr');

        if (!trColumn) {
            return;
        }

        var containerObj = Element.select(trColumn, 'div.'+self.spendingDisplayOpts.displayContainerId)[0];

        if (!containerObj) {
            return;
        }

        var isPointsOnly = containerObj.readAttribute('points-only');

        if (!isPointsOnly) {
            return;
        }

        var inputCheckboxObj = Element.select(trColumn, 'input:checkbox')[0];
        
        if (inputCheckboxObj && inputCheckboxObj.checked) {
            var pointsOnlyDisplayObj = Element.select(trColumn, 'span.'+self.spendingDisplayOpts.displayDiscountPointsId)[0];
            
            if (pointsOnlyDisplayObj) {
                self.pointsConfiguredMap[inputCheckboxObj.value] = {
                    'is_points_only' : true,
                    'catalog_redemption_points_used' : pointsOnlyDisplayObj.innerHTML,
                };
            }
            
            containerObj.show();
        } else {
            delete self.pointsConfiguredMap[inputCheckboxObj.value];
            containerObj.hide();
        }
    },
    initializePredefinedData: function() {
        var self = this;
        
        self.initializePointsOnlyData();
        self.initializeRedemptionsData();
    },
    initializePointsOnlyData: function() {
        var self = this;
        var productSearchTableObj = $(self.productSearchGridTableId);
        
        if (!productSearchTableObj) {
            return;
        }
        
        Element.select(productSearchTableObj, 'input:checkbox').each(function(el) {
            self.showPointsOnlyInfo(el);
        });
    },
    initializeRedemptionsData: function() {
        var self = this;
        var prodId;
        var modalLinkObj, elCheckbox, trColumn;
        
        var productSearchTableObj = $(self.productSearchGridTableId);
        
        if (!productSearchTableObj) {
            return;
        }
        
        for (prodId in self.pointsConfiguredMap) {
            if (self.pointsConfiguredMap[prodId]['is_points_only']) {
                continue;
            }
            
            elCheckbox = Element.select(productSearchTableObj, 'input:checkbox[value='+prodId+']')[0];
            
            if (!elCheckbox) {
                continue;
            }
            
            trColumn = elCheckbox.up('tr');
            
            if (!trColumn) {
                continue;
            }
            
            modalLinkObj = trColumn.select(self.configureLinksIdentifier)[0];
            
            if (!modalLinkObj) {
                continue;
            }
            
            modalLinkObj.writeAttribute('catalog_redemption_rule', self.pointsConfiguredMap[prodId]['catalog_redemption_rule']);
            modalLinkObj.writeAttribute('catalog_redemption_rule_uses', self.pointsConfiguredMap[prodId]['catalog_redemption_rule_uses']);
            modalLinkObj.writeAttribute('catalog_redemption_points_used', self.pointsConfiguredMap[prodId]['catalog_redemption_points_used']);
            modalLinkObj.writeAttribute('catalog_redemption_price_discount', self.pointsConfiguredMap[prodId]['catalog_redemption_price_discount']);
            
            if (self.pointsConfiguredMap[prodId]['catalog_redemption_points_used'] > 0) {
                self.displayProductInfoSpending(
                    self.pointsConfiguredMap[prodId]['catalog_redemption_price_discount'],
                    self.pointsConfiguredMap[prodId]['catalog_redemption_points_used'],
                    modalLinkObj
                );
            } else {
                self.hideProductInfoSpending(modalLinkObj);
            }
        }
    },
    fetchContent: function(productId) {
        var self = this;
        var response = '';
        var configuredPoints = 0;
        
        var isProductConfigured = false;

        self.currentProductId = productId;

        configuredPoints = self.helper.getConfiguredPointsForAllItems(self)
            - self.helper.getConfiguredPointsForCurrentItem(self.currentModalWindowLinkObj);
        configuredPoints += self.helper.getTotalCartPointsSpent($$(self.totalSpentObjIdentifier)[0]);
        
        isProductConfigured = self.helper.isProductConfigured(self.currentModalWindowLinkObj);
        
        var params = {
            'id' : productId,
            'is_product_configured' : isProductConfigured,
            'configured_points' : configuredPoints
        };
        
        Object.extend(params, self.helper.prepareProductBuyRequest(productId, isProductConfigured));

        new Ajax.Request(self.url, {
            parameters: params,
            asynchronous: false,
            onSuccess: function(transport) {
                response = transport.responseText;
            }.bind(this)
        });

        return response;
    },
    showModalWindow: function(response, catalogRedemptionRule, catalogRedemptionRuleUses) {
        var self = this;

        $(self.modalWindowOpts.modalWindowContainerId).innerHTML = '';
        $(self.modalWindowOpts.modalWindowContainerId).innerHTML = response;
        var jsScripts = Array.prototype.slice.call(
            $(self.modalWindowOpts.modalWindowContainerId).getElementsByTagName('script')
        );

        /* eval modal window javascript content */
        var alljscripts = '';
        jsScripts.forEach(function(obj){
            eval(obj.innerHTML);
            alljscripts = alljscripts + '\n' + obj.innerHTML;
        });

        /* display modal window */
        self.modalWindowOpts.screenHeight = $('html-body').getHeight();
        self.modalWindowOpts.screenWidth = $('html-body').getWidth();
        $(self.modalWindowOpts.popupWindowMask).setStyle(
            {'height':self.modalWindowOpts.screenHeight+'px'}
        ).show();
        $(self.modalWindowOpts.modalWindowContainerId).setStyle({'width':'500px','margin-top':'-150px'}).show();

        /* init default values for slier */
        if (
            typeof sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance') === 'object'
        ) {
            sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').setProductPrice(self.helper.getProductPrice(self.currentModalWindowLinkObj));
            sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').setProductPriceString(self.helper.getProductPrice(self.currentModalWindowLinkObj, true));
            sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').updatePriceOnChange();

            var configuredPoints = self.helper.getConfiguredPointsForAllItems(self)
            - self.helper.getConfiguredPointsForCurrentItem(self.currentModalWindowLinkObj);
            sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').setConfiguredCustomerPoints(configuredPoints);

            if (catalogRedemptionRule) {
                $(sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').redemptionRuleSelector).select('option[value="'+catalogRedemptionRule+'"]').first().selected = true;
                self.helper.triggerObserver($(sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').redemptionRuleSelector),'change');
            }

            if (catalogRedemptionRuleUses !== null && catalogRedemptionRuleUses !== "") {
                sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').updateSliderValue(catalogRedemptionRuleUses);
            }
            
            /* init single rule by default */
            if (!catalogRedemptionRule) {
                var ruleSelector = $(sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').redemptionRuleSelector);
                
                if (ruleSelector && !ruleSelector.visible() && ruleSelector.getValue() != "") {
                    self.helper.triggerObserver($(sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').redemptionRuleSelector),'change');
                }
            }
            
            sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').updatePriceOnChange();
        }
    },
    closeModalWindow: function() {
        var self = this;
        $(self.modalWindowOpts.popupWindowMask).setStyle('').hide();
        $(self.modalWindowOpts.modalWindowContainerId).setStyle('').hide();

        if (typeof sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance') === 'object') {
            sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').destroySlider();
        }
        self.currentModalWindowLinkObj = null;
    },
    applyPoints: function() {
        var self = this;
        var catalogRedemptionRule = sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').currentRuleId;
        var catalogRedemptionRuleUses = 1;
        var catalogRedemptionPointsUsed = 0;
        var catalogRedemptionPriceDiscountString = null;
        var catalogRedemptionPriceDiscount = 0;

        if (typeof sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance') === 'object' && sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').pointsSliderInstance !== null) {
            catalogRedemptionRuleUses = sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').pointsSliderInstance.getValue();
            catalogRedemptionPointsUsed = catalogRedemptionRuleUses * sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').pointsSliderInstance.points_per_use;
            catalogRedemptionPriceDiscountString = sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').getProductPriceDiscountString();
            catalogRedemptionPriceDiscount = parseFloat(catalogRedemptionPriceDiscountString.replace(sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').getProductCurrencySymbol(), '')).toFixed(2);
        } else {
            catalogRedemptionRuleUses = null;
            catalogRedemptionPointsUsed = null;
        }

        self.currentModalWindowLinkObj.writeAttribute('catalog_redemption_rule', catalogRedemptionRule);
        self.currentModalWindowLinkObj.writeAttribute('catalog_redemption_rule_uses', catalogRedemptionRuleUses);
        self.currentModalWindowLinkObj.writeAttribute('catalog_redemption_points_used', catalogRedemptionPointsUsed);
        if (catalogRedemptionPriceDiscount > 0) {
            self.currentModalWindowLinkObj.writeAttribute('catalog_redemption_price_discount', catalogRedemptionPriceDiscountString);
            self.displayProductInfoSpending(catalogRedemptionPriceDiscountString, catalogRedemptionPointsUsed);
        } else {
            self.currentModalWindowLinkObj.writeAttribute('catalog_redemption_price_discount', catalogRedemptionPriceDiscountString);
            self.hideProductInfoSpending();
        }

        /* set values to form */
        var fields = [];
        fields.push(new Element('input', {type: 'hidden', name: "item["+self.currentProductId+"][catalog_redemption_rule]", value: catalogRedemptionRule}));
        fields.push(new Element('input', {type: 'hidden', name: "item["+self.currentProductId+"][catalog_redemption_uses]", value: catalogRedemptionRuleUses}));
        window.productConfigure.addFields(fields);

        self.pointsConfiguredMap[self.currentProductId] = {
            'is_points_only' : false,
            'catalog_redemption_rule' : catalogRedemptionRule,
            'catalog_redemption_rule_uses' : catalogRedemptionRuleUses,
            'catalog_redemption_points_used' : catalogRedemptionPointsUsed,
            'catalog_redemption_price_discount' : catalogRedemptionPriceDiscountString
        };

        self.closeModalWindow();
    },
    deletePoints: function(modalWindowLinkObj) {
        var self = this;

        modalWindowLinkObj.writeAttribute('catalog_redemption_rule', null);
        modalWindowLinkObj.writeAttribute('catalog_redemption_rule_uses', null);
        modalWindowLinkObj.writeAttribute('catalog_redemption_points_used', null);
        modalWindowLinkObj.writeAttribute('catalog_redemption_price_discount', null);

        var productId = modalWindowLinkObj.readAttribute('product_id');
        var fields = [];
        fields.push(new Element('input', {type: 'hidden', name: "item["+productId+"][catalog_redemption_rule]", value: null}));
        fields.push(new Element('input', {type: 'hidden', name: "item["+productId+"][catalog_redemption_uses]", value: null}));
        window.productConfigure.addFields(fields);
        
        delete self.pointsConfiguredMap[productId];

        self.hideProductInfoSpending(modalWindowLinkObj);
    },
    displayProductInfoSpending: function(priceDiscount, pointsDiscount, modalWindowLinkObj) {
        var self = this;

        if (typeof modalWindowLinkObj === 'undefined' || modalWindowLinkObj === null) {
            if (
                typeof self.currentModalWindowLinkObj !== 'undefined'
                && self.currentModalWindowLinkObj !== null
            ) {
                modalWindowLinkObj = self.currentModalWindowLinkObj;
            } else {
                return;
            }
        }

        var trElement = modalWindowLinkObj.up('tr');

        if (!trElement) {
            return;
        }

        var containerObj = Element.select(trElement, 'div.'+self.spendingDisplayOpts.displayContainerId)[0];
        var priceDiscountObj = Element.select(trElement, 'span.'+self.spendingDisplayOpts.displayDiscountPriceId)[0];
        var pointsDiscountObj = Element.select(trElement, 'span.'+self.spendingDisplayOpts.displayDiscountPointsId)[0];
        var discountRemoveObj = Element.select(trElement, 'img.'+self.spendingDisplayOpts.displayDiscountRemove)[0];

        if (!containerObj || !priceDiscountObj || !pointsDiscountObj) {
            return;
        }

        priceDiscountObj.update(priceDiscount);
        pointsDiscountObj.update(pointsDiscount);
        containerObj.show();

        if (discountRemoveObj) {
            discountRemoveObj.observe('click', function(event) {
                var currentEl = this;
                var trEl = currentEl.up('tr');

                if (!trEl) { 
                    Event.stop(event);
                    return;
                }

                var modalWindowObj = Element.select(trEl, 'span.'+self.configureLinksIdentifier)[0];

                if (!modalWindowObj) {
                    Event.stop(event);
                    return;
                }

                self.deletePoints(modalWindowObj);                

                /* Avoid triggering magento default row selection and product configuration modal window */
                Event.stop(event);
            });
        }
    },
    hideProductInfoSpending: function(modalWindowLinkObj) {
        var self = this;

        if (typeof modalWindowLinkObj === 'undefined' || modalWindowLinkObj === null) {
            if (
                typeof self.currentModalWindowLinkObj !== 'undefined'
                && self.currentModalWindowLinkObj !== null
            ) {
                modalWindowLinkObj = self.currentModalWindowLinkObj;
            } else {
                return;
            }
        }

        var trElement = modalWindowLinkObj.up('tr');

        if (!trElement) {
            return;
        }

        var containerObj = Element.select(trElement, 'div.'+self.spendingDisplayOpts.displayContainerId)[0];
        var priceDiscountObj = Element.select(trElement, 'span.'+self.spendingDisplayOpts.displayDiscountPriceId)[0];
        var pointsDiscountObj = Element.select(trElement, 'span.'+self.spendingDisplayOpts.displayDiscountPointsId)[0];

        if (!containerObj || !priceDiscountObj || !pointsDiscountObj) {
            return;
        }

        containerObj.hide();
        priceDiscountObj.update('');
        pointsDiscountObj.update('');
    },
    updateCustomerBalanceConfigured: function() {
        var self = this;
        var cbObj = $(self.customerBalanceConfiguredId);

        if (!cbObj) {
            return;
        }

        var configuredPoints = self.helper.getConfiguredPointsForAllItems(self)
            - self.helper.getConfiguredPointsForCurrentItem(self.currentModalWindowLinkObj);
        configuredPoints += self.helper.getTotalCartPointsSpent($$(self.totalSpentObjIdentifier)[0]);

        if (
            typeof sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance') === 'object'
            && sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').pointsSliderInstance !== null
        ) {
            configuredPoints += sweettooth.getSweettoothGlobalVar('rewardsCatalogSliderInstance').pointsSliderInstance.getPointsValue();
        }

        var pointsString;
        var currencyStr = '';
        if (window.hasOwnProperty('currency_map') && window.currency_map[1]) {
            currencyStr = window.currency_map[1];
        }
        
        if (configuredPoints === 1 || configuredPoints === -1) {
            pointsString = configuredPoints + ' ' + currencyStr.toString().trim() + ' ' + CAPTION_POINT;
        } else {
            pointsString = configuredPoints + ' ' + currencyStr.toString().trim() + ' ' + CAPTION_POINTS;
        }

        cbObj.update(pointsString);
    },
    showCustomerBalanceFieldset: function() {
        var self = this;

        if (
            $(self.customerBalanceContainerId)
            && !$(self.customerBalanceContainerId).visible()
        ) {
            var objTd = $(self.orderSearchContainerId).up('td');

            if (objTd) {
                objTd.insert({top: $(self.customerBalanceContainerId)});
                $(self.customerBalanceContainerId).show();
            }
        }
    },
    updatePointsOnlyPrices: function() {
        var self = this;
        var map = sweettooth.getSweettoothGlobalVar('rewardsMapPointsOnly');

        if (!$(self.productSearchGridTableId)) {
            return;
        }
        
        if (typeof map !== 'undefined') {
            for (var keyProd in map) {
                if (isNaN(parseInt(keyProd))) {
                    continue;
                }
                var prodInfoContainer = $(self.productSearchGridTableId).select('div.' + self.spendingDisplayOpts.displayContainerId +'[product_id='+keyProd+']')[0];
                
                if (typeof prodInfoContainer === 'undefined') {
                    continue;
                }
                
                var prodPriceColumn = prodInfoContainer.up('tr').select('td.price')[0];
                
                if (typeof prodPriceColumn === 'undefined') {
                    continue;
                }
                
                prodPriceColumn.writeAttribute('data-points',map[keyProd]);
                self.showPointsOnlyInfo(prodPriceColumn);
            }
        }
    },
    helper: {
        triggerObserver: function(element, shortEventName) {
            if (document.createEventObject) {
                var event = document.createEventObject();
                return element.fireEvent('on'+shortEventName, event);
            } else {
                var event = document.createEvent("HTMLEvents");
                event.initEvent(shortEventName, true, true );
                return !element.dispatchEvent(event);
            }
        },
        getProductPrice: function(currentModalWindowLinkObj, isWithCaption) {
            if (
                typeof currentModalWindowLinkObj === 'undefined'
                || currentModalWindowLinkObj === null
            ) {
                return 0;
            }

            if (typeof isWithCaption === 'undefined') {
                isWithCaption = false;
            }

            var trElement = currentModalWindowLinkObj.up('tr');

            if (!trElement) {
                return 0;
            }

            var priceColl = Element.select(trElement, '.price')[0];

            if (!priceColl) {
                return 0;
            }

            var priceBase = priceColl.innerHTML.match(/.*?([\d,]+\.?\d*)/);
            
            if (priceBase === null) {
                return 0;
            }

            if (isWithCaption) {
                return priceBase[0].replace(/,/g,'').trim();
            }

            return parseFloat(priceBase[1].replace(/,/g,''));
        },
        getConfiguredPointsForAllItems: function(parentInstanceObj) {
            var points = 0;
            var prodId;
            
            for (prodId in parentInstanceObj.pointsConfiguredMap) {
                points += parseInt(parentInstanceObj.pointsConfiguredMap[prodId]['catalog_redemption_points_used']);
            }
            
            return points;
        },
        getConfiguredPointsForCurrentItem: function(currentModalWindowLinkObj) {
            if (
                typeof currentModalWindowLinkObj === 'undefined'
                || currentModalWindowLinkObj === null
            ) {
                return 0;
            }

            var points = currentModalWindowLinkObj.readAttribute('catalog_redemption_points_used');

            if (!points) {
                return 0;
            }

            return parseInt(points);
        },
        getTotalCartPointsSpent: function(totalObj) {
            if (typeof totalObj === 'undefined' || totalObj === null) {
                return 0;
            }
            
            return parseInt(totalObj.innerHTML.match(/.*?([\d,]+\.?\d*)/)[0].replace(/,/g,'').trim());
        },
        isProductConfigured: function(currentModalWindowLinkObj) {
            if (
                typeof currentModalWindowLinkObj === 'undefined'
                || currentModalWindowLinkObj === null
            ) {
                return 0;
            }

            var trElement = currentModalWindowLinkObj.up('tr');

            if (!trElement) {
                return 0;
            }

            var confirmProdColumn = Element.select(trElement, 'input:checkbox')[0];

            if (!confirmProdColumn || !confirmProdColumn.checked) {
                return 0;
            }

            return 1;
        },
        triggerProductSelectionIfNotConfigurable: function(currentModalWindowLinkObj) {
            var self = this;
            if (
                typeof currentModalWindowLinkObj === 'undefined'
                || currentModalWindowLinkObj === null
            ) {
                return 0;
            }

            var canConfigure = currentModalWindowLinkObj.readAttribute('can-configure');
            var trElement = currentModalWindowLinkObj.up('tr');

            if (!trElement) {
                return 0;
            }

            var confirmProdColumn = Element.select(trElement, 'input:checkbox')[0];
            var qtyColumn = Element.select(trElement, 'input[name=qty]')[0];

            if (!confirmProdColumn) {
                return 0;
            }

            if (!confirmProdColumn.checked && canConfigure === "0") {
                self.triggerObserver(trElement, 'click');
            }

            return 1;
        },
        prepareProductBuyRequest: function(productId, isProductConfigured) {
            if (!isProductConfigured) {
                return {};
            }

            var prodConfigOptsObj = $('product_composite_configure_confirmed[product_to_add]['+productId+']');
            
            if (!prodConfigOptsObj) {
                return {};
            }
            
            var fields = $('product_composite_configure_confirmed[product_to_add]['+productId+']').select('input', 'select', 'textarea');

            return Form.serializeElements(fields, true);
        }
    }
};

/**
 * Used to handle slider for admin order create catalog points spending
 */
sweettooth.OrderCreate.CatalogSlider = {
    sliderObjs: {
        'sliderHandle' : 'sliderHandle',
        'sliderRail': 'sliderRail',
        'sliderCaption': 'sliderCaption',
        'sliderIncr' : 'sliderIncr',
        'sliderDecr' : 'sliderDecr'
    },
    sliderInfo: {
        min: 0,
        max: 1,
        step: 1,
        currentValue: 1
    },
    currentRuleId: '',
    redemptionRuleSelector: 'redemption_rule',
    redemptionRuleUses: 'redemption_rule_uses',
    redemptionRulePrev: 'redemption_rule_prev',
    redemptionRuleUseContainer: 'redemption_rule_uses_container',
    productOriginalPriceId: 'rewards-product-price-original',
    productDiscountedPriceId: 'rewards-product-price-discounted',
    pointsSliderInstance: null,
    _productPrice: 0,
    _productPriceString: '$0',
    _productPriceDiscount: 0,
    _productCurrencySymbol: '',
    _configuredPoints: 0,
    init: function(
        sliderObjs, redemptionRuleSelector, redemptionRuleUses,
        redemptionRulePrev, redemptionRuleUseContainer, initialSliderValues,
        productOriginalPriceId, productDiscountedPriceId
    ) {
        var self = this;

        self.sliderObjs = Object.extend(this.sliderObjs, sliderObjs);

        if (typeof redemptionRuleUses !== 'undefined') {
            self.redemptionRuleUses = redemptionRuleUses;
        }

        if (typeof redemptionRuleSelector !== 'undefined') {
            self.redemptionRuleSelector = redemptionRuleSelector;
        }

        if (typeof redemptionRuleUseContainer !== 'undefined') {
            self.redemptionRuleUseContainer = redemptionRuleUseContainer;
        }

        if (typeof productOriginalPriceId !== 'undefined') {
            self.productOriginalPriceId = productOriginalPriceId;
        }

        if (typeof productDiscountedPriceId !== 'undefined') {
            self.productDiscountedPriceId = productDiscountedPriceId;
        }

        self.bindEvents();

        return self;
    },
    bindEvents: function() {
        var self = this;

        self.bindRedemptionContainer();
    },
    bindRedemptionContainer: function() {
        var self = this;
        if (!$(self.redemptionRuleSelector)) {
            return;
        }

        $(self.redemptionRuleSelector).observe('change', function() {
            var ruleIdSelected = this.getValue();
            self.currentRuleId = ruleIdSelected;

            if (ruleIdSelected == "") {
                $(self.redemptionRuleUseContainer).hide();
                self.destroySlider();
                self.updatePriceOnChange();
                return;
            }

            $(self.redemptionRuleUseContainer).show();
            self.initSlider(ruleIdSelected);
            self.updatePriceOnChange();
        });
    },
    initSlider: function(ruleId) {
        var self = this;

        if (!self.pointsSliderInstance) {
            self.createSlider();
        }

        self.changeSliderRule(ruleId);

        self.pointsSliderInstance.regenerateSlider(
            self.sliderInfo.min, 
            self.sliderInfo.max, 
            self.sliderInfo.step, 
            self.sliderInfo.currentValue
        );

        self.bindSliderEvents();
    },
    createSlider: function() {
        var self = this;
        self.pointsSliderInstance =  new CatalogRedemptionSlider(
            self.sliderObjs.sliderHandle,
            self.sliderObjs.sliderRail,
            self.sliderObjs.sliderCaption,
            self.redemptionRuleUses
        );

        self.pointsSliderInstance.setUpdateCallback(self, self.updatePriceOnChange);
    },
    changeSliderRule: function(ruleId) {
        var self = this;
        var initValue = self.pointsSliderInstance.getValue();
        var uses = 1;

        if (initValue == null) {
            initValue = 1;
        }

        var amt = parseInt(window.rule_options[ruleId]['amount']);
        var curr = parseInt(window.rule_options[ruleId]['currency_id']);
        var maxUses = parseInt(window.rule_options[ruleId]['max_uses']);

        self.pointsSliderInstance.points_per_use = amt;
        self.pointsSliderInstance.points_currency = curr;

        var productPrice = self.getProductPrice();

        if (maxUses == 0) {
            maxUses = parseInt(productPrice) * 1000 + 1;
        }

        /* Adjust with general customer points */
        var customerPoints = window.customer_points ? window.customer_points[curr] : window.default_guest_points;
        var relevantCustomerPoints = Math.max(0, customerPoints - self.getConfiguredCustomerPoints());
        var priceDisposition = window.rule_options[ruleId]['price_disposition'];

        if (priceDisposition == 0) {
            priceDisposition = productPrice - self.helper.priceAdjuster(productPrice, window.rule_options[ruleId]['effect']);
        }

        maxUses = self.pointsSliderInstance.getRealMaxUses(
            maxUses, self.pointsSliderInstance.points_per_use,
            relevantCustomerPoints, productPrice, priceDisposition
        );

        if (maxUses >= 1) {
            if(initValue > maxUses) {
                initValue = maxUses;
            }

            self.sliderInfo.min = 0;
            self.sliderInfo.max = maxUses;
            self.sliderInfo.step = 1;
            self.sliderInfo.currentValue = 0;
        } else {
            /* default */
            self.sliderInfo.min = 0;
            self.sliderInfo.max = 1;
            self.sliderInfo.step = 1;
            self.sliderInfo.currentValue = 0;
        }

        /* Reset the slider to 1 if the rule has changed. */
        if (self.pointsSliderInstance.oldRuleId != ruleId) {
            self.pointsSliderInstance.setExternalValue(0);
            self.pointsSliderInstance.oldRuleId = ruleId;
        }
    },
    bindSliderEvents: function() {
        var self = this;
        $(self.sliderObjs.sliderHandle).observe('mousedown', function() {
            $(self.sliderObjs.sliderRail).addClassName('sliderRail-sliding');
            $(self.sliderObjs.sliderHandle).addClassName('sliderHandle-sliding');
        });

        $$('#'+self.sliderObjs.sliderHandle+', .cartSlider .slider').invoke('observe', 'mouseup', function() {
            $(self.sliderObjs.sliderRail).removeClassName('sliderRail-sliding');
            $(self.sliderObjs.sliderHandle).removeClassName('sliderHandle-sliding');
        });

        $(self.sliderObjs.sliderIncr).observe('click', function() {
            self.pointsSliderInstance.incr();
        });

        $(self.sliderObjs.sliderDecr).observe('click', function() {
            self.pointsSliderInstance.decr();
        });
    },
    unbindSliderEvents: function() {
        var self = this;
        Event.stopObserving(self.sliderObjs.sliderHandle, 'mousedown');
        $$('#'+self.sliderObjs.sliderHandle+', .cartSlider .slider').each(function(sliderEl){
            Event.stopObserving(sliderEl, 'mouseup');
        });

        Event.stopObserving(self.sliderObjs.sliderIncr, 'click');
        Event.stopObserving(self.sliderObjs.sliderDecr, 'click');
    },
    destroySlider: function() {
        var self = this;
        self.unbindSliderEvents();
        self.pointsSliderInstance = null;
    },
    updateSliderValue: function(val) {
        var self = this;

        if (self.pointsSliderInstance === null) {
            return;
        }

        self.pointsSliderInstance.regenerateSlider(
            self.sliderInfo.min, 
            self.sliderInfo.max, 
            self.sliderInfo.step, 
            val
        );

        self.pointsSliderInstance.setExternalValue(val);
    },
    setProductPrice: function(price) {
        var self = this;

        self._productPrice = price;

        return self;
    },
    getProductPrice: function() {
        return this._productPrice;
    },
    setProductPriceString: function(priceString) {
        var self = this;

        self._productPriceString = priceString;

        return self;
    },
    getProductPriceString: function() {
        return this._productPriceString;
    },
    setProductPriceDiscount: function(price) {
        var self = this;

        self._productPriceDiscount = price;

        return self;
    },
    getProductPriceDiscount: function() {
        return this._productPriceDiscount;
    },
    getProductPriceDiscountString: function() {
        return this.getProductCurrencySymbol() + this.getProductPriceDiscount();
    },
    setProductCurrencySymbol: function(currencySymbol) {
        var self = this;

        self._productCurrencySymbol = currencySymbol;

        return self;
    },
    getProductCurrencySymbol: function() {
        return this._productCurrencySymbol;
    },
    setConfiguredCustomerPoints: function(points) {
        var self = this;

        self._configuredPoints = points;

        return self;
    },
    getConfiguredCustomerPoints: function() {
        return this._configuredPoints;
    },
    updatePriceOnChange: function() {
        var self = this;
        var originalPriceObj = $(self.productOriginalPriceId);
        var discountedPriceObj = $(self.productDiscountedPriceId);
        var priceDiscount = 0;
        var currencySymbol = '';
        var priceAfterDiscount = self.getProductPrice();
        
        sweettooth.getSweettoothGlobalVar('rewardsOrderCreateProdSearchInstance').updateCustomerBalanceConfigured();

        if (!originalPriceObj || !discountedPriceObj) {
            return;
        }

        currencySymbol = self.getProductPriceString().replace(self.getProductPrice().toFixed(2), '');
        self.setProductCurrencySymbol(currencySymbol);
        originalPriceObj.update(self.getProductPriceString());

        if (!self.pointsSliderInstance || !self.currentRuleId) {
            discountedPriceObj.hide();
            originalPriceObj.setStyle({'text-decoration':'none'});
            return;
        }

        var ruleId = self.currentRuleId;
        var numUses = self.pointsSliderInstance.getValue();
        priceDiscount = window.rule_options[ruleId]['price_disposition'] * numUses;
        priceDiscount = priceDiscount.toFixed(2);

        if (priceDiscount < 0.01) {
            discountedPriceObj.hide();
            originalPriceObj.setStyle({'text-decoration':'none'});
            return;
        }

        self.setProductPriceDiscount(priceDiscount);
        priceAfterDiscount = priceAfterDiscount - priceDiscount;
        
        if (priceAfterDiscount < 0) {
            priceAfterDiscount = 0;
        }

        discountedPriceObj.update(currencySymbol + parseFloat(priceAfterDiscount).toFixed(2));
        originalPriceObj.setStyle({'text-decoration':'line-through'});
        discountedPriceObj.setStyle({'text-decoration':'none'});
        discountedPriceObj.show();
    },
    helper: {
        /* Calculate price adjustments */
        priceAdjuster: function(price, code) {
            if (code.indexOf("-") > -1) {
                if (code.indexOf("%") > -1) {
                    var fx    = 1 + code.replace("%", "") / 100;
                    price = price * fx;
                } else {
                    price = price + code;
                }
            } else {
                if (code.indexOf("%") > -1) {
                    var fx    = code.replace("%", "") / 100;
                    price = price * fx;
                } else {
                    price = code;
                }
            }

            return price;
        }
    }
};

sweettooth.OrderCreate.Cart = {
    mapPointsUrl: '',
    itemLineIdentifier: 'order_item_{itemId}_title',
    rewardsInfoItemIdentifier: 'item-points-undername-{itemId}',
    init: function(mapPointsUrl, itemLineIdentifier) {
        var self = this;
        self.mapPointsUrl = mapPointsUrl;
        
        if (typeof itemLineIdentifier !== 'undefined') {
            self.itemLineIdentifier = itemLineIdentifier;
        }
        
        self.updateCartItemsRewardsInfo();
        self.updatePointsOnlyPrices();
        self.updateRowSubtotalPrices();
        
        return this;
    },
    updateCartItemsRewardsInfo: function() {
        var self = this;
        
        new Ajax.Request(self.mapPointsUrl, {
            parameters: {},
            asynchronous: false,
            onSuccess: function(transport) {
                var response = transport.responseJSON;
                
                if (response.hasOwnProperty('error') && response.error === true) {
                    /* print error message if needed: console.log(response.errorMessage); */
                } else {
                    if (response.hasOwnProperty('result')) {
                        var itemId;
                        for (itemId in response.result) {
                            self.appendCartItemRewardsInfoById(itemId, response.result[itemId]);

                            var jsScripts = Array.prototype.slice.call(
                                $(self.rewardsInfoItemIdentifier.replace('{itemId}', itemId)).getElementsByTagName('script')
                            );
                            /* eval modal window javascript content */
                            jsScripts.forEach(function(obj){
                                eval(obj.innerHTML);
                            });
                        }
                    }
                }
            },
            onComplete: function() {
                self.updatePointsOnlyPrices();
                self.updateRowSubtotalPrices();
            }
        });
    },
    appendCartItemRewardsInfoById: function(itemId, htmlContent) {
        var self = this;
        var itemTitleObj = $(self.itemLineIdentifier.replace('{itemId}', itemId));

        if (!itemTitleObj) {
            return;
        }
        
        var itemTdObj = itemTitleObj.up('td');
        
        if (!itemTdObj) {
            return;
        }
        
        if (!$('item-points-undername-' + itemId)) {
            /* synced html insertion */
            itemTdObj.insertAdjacentHTML('beforeend', htmlContent);
        }
    },
    updatePointsOnlyPrices: function() {
        var self = this;
        var map = sweettooth.getSweettoothGlobalVar('rewardsCartMapPointsOnly');
        
        if (typeof map !== 'undefined') {
            for (var itemId in map) {
                if (isNaN(parseInt(itemId))) {
                    continue;
                }
                
                var itemTitleObj = $(self.itemLineIdentifier.replace('{itemId}', itemId));

                if (!itemTitleObj) {
                    continue;
                }

                var itemTrObj = itemTitleObj.up('tr');

                if (!itemTrObj) {
                    continue;
                }
                
                var priceColumn = itemTrObj.select('td.price')[0].select('span.price')[0];
                
                if (priceColumn) {
                    priceColumn.writeAttribute('data-points', map[itemId]['unit_points_str']);
                }
                
                var customPriceCheckbox = itemTrObj.select('td.price')[0].select('input:checkbox')[0];
                var customPriceCheckboxLabel = itemTrObj.select('td.price')[0].select('label')[0];
                
                if (customPriceCheckbox && customPriceCheckboxLabel) {
                    customPriceCheckbox.hide();
                    customPriceCheckboxLabel.hide();
                }
                
                var subtotalColumn = itemTrObj.select('td.price')[1].select('span.price')[0];
                
                if (subtotalColumn) {
                    subtotalColumn.writeAttribute('data-points', map[itemId]['points_str']);
                }
                
                var rowSubtotalColumn = itemTrObj.select('td.price')[3].select('span.price')[0];
                
                if (rowSubtotalColumn) {
                    rowSubtotalColumn.writeAttribute('data-points', map[itemId]['points_str']);
                }
            }
        }
    },
    updateRowSubtotalPrices: function() {
        var self = this;
        var map = sweettooth.getSweettoothGlobalVar('rewardsCartMapDiscountAmounts');

        if (typeof map !== 'undefined') {
            for (var itemId in map) {
                if (isNaN(parseInt(itemId))) {
                    continue;
                }
                
                var itemTitleObj = $(self.itemLineIdentifier.replace('{itemId}', itemId));

                if (!itemTitleObj) {
                    continue;
                }

                var itemTrObj = itemTitleObj.up('tr');

                if (!itemTrObj) {
                    continue;
                }
                
                var rowSubtotalColumnPrices = itemTrObj.select('td.price')[3].select('span.price');
                
                if (rowSubtotalColumnPrices) {
                    if (map[itemId]['cart_display_type'] === 2) {
                        if (rowSubtotalColumnPrices[0]) {
                            rowSubtotalColumnPrices[0].writeAttribute('data-points', map[itemId]['row_subtotal_excl_tax']);
                        }
                        
                        if (rowSubtotalColumnPrices[1]) {
                            rowSubtotalColumnPrices[1].writeAttribute('data-points', map[itemId]['row_subtotal_incl_tax']);
                        }
                    }
                    
                    if (map[itemId]['cart_display_type'] === 1) {
                        if (rowSubtotalColumnPrices[0]) {
                            rowSubtotalColumnPrices[0].writeAttribute('data-points', map[itemId]['row_subtotal_incl_tax']);
                        }
                    }
                    
                    if (map[itemId]['cart_display_type'] === 0) {
                        if (rowSubtotalColumnPrices[0]) {
                            rowSubtotalColumnPrices[0].writeAttribute('data-points', map[itemId]['row_subtotal_excl_tax']);
                        }
                    }
                }
            }
        }
    }
};