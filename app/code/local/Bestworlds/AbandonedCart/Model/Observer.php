<?php
/**
 * Best Worlds
 * http://www.bestworlds.com
 * 888-751-5348
 * 
 * Need help? contact us:
 *  http://www.bestworlds.com/contact-us
 * 
 * Want to customize or need help with your store?
 *  Phone: 888-751-5348
 *  Email: info@bestworlds.com
 *
 * @category    Bestworlds
 * @package     Bestworlds_AbandonedCart
 * @copyright   Copyright (c) 2018 Best Worlds
 * @license     http://www.bestworlds.com/software_product_license.html
 */

/**
 * Observer model
 *
 * @category   Bestworlds
 * @package    Bestworlds_AbandonedCart
 * @author     Best Worlds Team <info@bestworlds.com>
 */
class Bestworlds_AbandonedCart_Model_Observer 
{
    const COOKIE_NAME = 'bw_ac';

    public function getCookieName()
    {
        return self::COOKIE_NAME;
    }

    public function emailTracking(Varien_Event_Observer $observer)
    {
        $this->_emailTracking();
    }

    protected function _emailTracking()
    {
        if (!Mage::getStoreConfigFlag('abandonedcart/basic/enable') || !Mage::getStoreConfigFlag('abandonedcart/frontend/email_tracking')) 
            return false;
        $allowedFields = explode(',',Mage::getStoreConfig('abandonedcart/frontend/tracking_fields'));
        if (!$allowedFields) return false;
        foreach ($allowedFields as $k=>$field) $allowedFields[$k] = trim(strtolower($field));
        $params = Mage::app()->getRequest()->getParams();
        if (!$params) return false;
        if (isset($params['type'])) {
            if ( $params['type']==Bestworlds_AbandonedCart_Model_Capturetypes::DURING_CHECKOUT ||
                $params['type']==Bestworlds_AbandonedCart_Model_Capturetypes::LOGGED_IN) {
                return false;
            }
        }
        foreach ($params as $q=>$v) {
            if (in_array(strtolower($q),$allowedFields)) {
                if (Zend_Validate::is($v, 'EmailAddress')) {
                    //save that we capture this quote email through our module
                    $session        = Mage::getSingleton('checkout/session');
                    $quote_id       = $session->getQuoteId();
                    if ($quote_id) {
                        $quote = Mage::getModel('sales/quote')->load($quote_id);
                        $quote->setData('email_captured_from', Bestworlds_AbandonedCart_Model_Capturetypes::EMAIL_MARKETING);
                        $quote->setData('customer_email', $v);
                        $quote->save();
                        $this->sendKlaviyoTrack($quote);
                    } else {
                        //add session to indicate that we recover this email via email tracking, in case the quote doesn't exist yet
                        Mage::getSingleton("core/session")->setBwCapture(Bestworlds_AbandonedCart_Model_Capturetypes::EMAIL_MARKETING);
                    }
                    $email= Mage::helper('abandonedcart')->encryptMe($v);
                    Mage::getModel('core/cookie')->set(self::COOKIE_NAME, $email, 0, "/", null, null, false);

                    /*ADD COOKIE TO NOT OPEN OUR ABCARTPROMPT*/
                    if (!Mage::getModel("core/cookie")->get("bw_lightbox_off")) {
                        Mage::getModel('core/cookie')->set('bw_lightbox_off', "true", 0, "/", null, null, false);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    public function sendKlaviyoTrack($quote) {
        if(Mage::getStoreConfigFlag('abandonedcart/frontend/klaviyo_integration')) {
            //send event to Klaviyo in order to fire first abandoned cart email
            $quote_id       = $quote->getId();
            $checkout       = Mage::helper('klaviyo_reclaim')->getCheckout($quote_id);
            $checkout_id    = $checkout->getId();
            $checkout_url   = $quote->getStore()->getUrl('reclaim/index/view', array('_query' => array('id' => $checkout_id)));
            $tracker        = $this->getKlaviyoTracker($quote);

            $customer_properties    = ['$email' => $quote->getCustomerEmail()];
            $quote_last_updated     = strtotime($quote->getUpdatedAt());

            $item_details               = [];
            $configurable_product_ids   = [];

            foreach ($quote->getItemsCollection() as $quote_item) {
                $response = $this->_quoteItemData($quote_item);
                $quote_item_data = $response['quote_item'];
                $is_product_configurable = $response['is_product_configurable'];
                if ($is_product_configurable) {
                    $configurable_product_ids[] = $quote_item_data['product']['id'];
                }
                $item_details[] = $quote_item_data;
            }

            $response           = $this->_mergeConfigurableAndSimpleQuoteItems($item_details, $configurable_product_ids);
            $item_details       = $response['items'];
            $item_count         = $response['item_count'];
            $item_descriptions  = $response['item_descriptions'];

            $properties = array(
                '$event_id'     => $quote_id,
                'email'         => $quote->getCustomerEmail(),
                'captured_type' => $quote->getData('email_captured_from'),
                'checkout_url'  => $checkout_url,
                '$value'        => (float) $quote->getGrandTotal(),
                'Items'         => $item_descriptions,
                'Items Count'   => $item_count,
                '$extra'        => array(
                    'checkout_url' => $checkout_url,
                    'checkout_id'  => $checkout_id,
                    'line_items'   => $item_details,
                )
            );

            $coupon_code = $quote->getCouponCode();
            if ($coupon_code) {
                $totals = $quote->getTotals();
                $properties['Discount Codes'] = array($coupon_code);
                if (array_key_exists('discount', $totals) && is_object($totals['discount'])) {
                    $properties['Total Discounts'] = (float) $totals['discount']->getValue() * -1;
                }
            }

            $tracker->track('Link2Cart Prompt', $customer_properties, $properties, $quote_last_updated);
        }
    }

    /**
     * Merged quote item (line item) details in cases where there are two line items to represent
     * a configurable product and its options.
     *
     * @param $item_details (array)
     * @param $configurable_product_ids (array)
     * @return array
     */
    protected function _mergeConfigurableAndSimpleQuoteItems($item_details, $configurable_product_ids) {
        $model_configurable = Mage::getModel('catalog/product_type_configurable');

        $merged = array();
        $item_descriptions = array();
        $item_count = 0;

        // Look for simple products that are really represented by a configurable product.
        foreach ($item_details as $line_index => $line) {
            $product_id = $line['product']['id'];
            if (in_array($product_id, $configurable_product_ids) || $line['row_total']) {
                $merged[] = $line;
            } else {
                $configurable_parent_ids = $model_configurable->getParentIdsByChild($product_id);
                $common_ids = array_intersect($configurable_product_ids, $configurable_parent_ids);

                // If it's a simple product placeholder for a configurable product, discard it. Otherwise, keep it.
                if (empty($common_ids)) {
                    $merged[] = $line;
                } else {
                    continue;
                }
            }

            if (array_key_exists('simple_name', $line['product'])) {
                $item_descriptions[] = $line['product']['simple_name'];
            } else {
                $item_descriptions[] = $line['product']['name'];
            }

            $item_count += $line['quantity'];
        }

        return array(
            'items'             => $merged,
            'item_count'        => $item_count,
            'item_descriptions' => $item_descriptions
        );
    }

    /**
     * Normalize quote item (line item) to an array that's JSON serializable.
     *
     * @param $quote_item (Mage_Sales_Model_Quote_Item)
     * @return array
     */
    protected function _quoteItemData ($quote_item) {
        $data = [];

        $quote_item_quantity = $quote_item->getQty();
        $quote_item_product = Mage::getModel('catalog/product')->load($quote_item->getProduct()->getId());

        $product_details = array(
            'id'    => $quote_item_product->getId(),
            'sku'   => $quote_item_product->getSKU(),
            'name'  => $quote_item_product->getName(),
            'price' => (float) $quote_item_product->getPrice(),
            'special_price' => (float) $quote_item_product->getFinalPrice()
        );

        $product_images = [];
        // If we have a simple product for this configurable product, get the simple name
        // and also attempt to get the images
        if ($option = $quote_item->getOptionByCode('simple_product')) {
            $simple_product = Mage::getModel('catalog/product')->load($option->getProduct()->getId());

            $product_details['simple_name'] = $simple_product->getName();
            foreach ($simple_product->getMediaGalleryImages() as $product_image) {
                if (!$product_image->getDisabled()) {
                    $product_images[] = array(
                        'url' => $product_image->getUrl()
                    );
                }
            }
        }

        // if there is no simple product or we tried but couldn't find any
        // images for the simple product just grab the configurable images
        if (empty($product_images)) {
            foreach ($quote_item_product->getMediaGalleryImages() as $product_image) {
                if (!$product_image->getDisabled()) {
                    $product_images[] = array(
                        'url' => $product_image->getUrl()
                    );
                }
            }
        }
        $product_details['images'] = $product_images;

        return array(
            'is_product_configurable' => $quote_item_product->isConfigurable(),
            'quote_item'              => array(
                'quantity'     => (float) $quote_item_quantity,
                'row_total'    => (float) $quote_item->getBaseRowTotal(),
                'row_discount' => (float) $quote_item->getBaseDiscountAmount(),
                'product'      => $product_details
            )
        );
    }

    public function getKlaviyoTracker($quote) {
        $store_id       = $quote->getStoreId();
        $public_api_key = Mage::helper('klaviyo_reclaim')->getPublicApiKey($store_id);

        if(!$public_api_key){
            return NULL;
        }

        $tracker =  new Klaviyo_Reclaim_Model_Tracker($public_api_key);

        return $tracker;
    }

    public function handleSaveBilling($observer) 
    {
        $quote = Mage::getModel('checkout/cart')->getQuote();
        $customer_email = $quote->getTargetEmail() ? $quote->getTargetEmail() : $quote->getCustomerEmail();
        $js = array('<script type="text/javascript">');

        //REMOVE LIGHTBOX ONCE WE HAVE THE QUOTE EMAIL
        if($customer_email!='') {
            $js[] = " jQuery('.bw_block_page').fadeOut().remove(); ";
        }
        $js[] = '</script>';

        $body       = json_decode($observer->getEvent()->getControllerAction()->getResponse()->getBody());
        $html       = $body->update_section->html;
        $html       = $html . implode('', $js);
        $body->update_section->html = $html;
        if (!isset($body->update_section->name)) $body->update_section->name = "payment-method";
        $body       = json_encode($body);
        $observer->getEvent()->getControllerAction()->getResponse()->setBody($body);
    }

    public function checkoutCartSaveAfter(Varien_Event_Observer $observer)
    {
        $cookie = Mage::getModel('core/cookie')->get(self::COOKIE_NAME);
        $cookie = Mage::helper('abandonedcart')->decryptMe($cookie);
        if ($cookie) {
            $cart = $observer->getEvent()->getCart();
            $quote = $cart->getQuote();
            if ($quote->getId() && !$quote->getCustomerEmail()) {
                //check for session value, to see if we capture email through our code
                if(Mage::getSingleton("core/session")->getBwCapture()) {
                    //save that we capture this quote email through our module
                    $quote->setData('customer_email', $cookie);
                    $quote->setData('email_captured_from', Mage::getSingleton("core/session")->getBwCapture());
                    $quote->save();
                    Mage::getSingleton("core/session")->unsBwCapture();
                }
            }
        }
    }

    public function handleBlockOutput($observer)
    {
        if(!Mage::getStoreConfigFlag('abandonedcart/basic/enable') ||
            (!Mage::getStoreConfigFlag('abandonedcart/frontend/lightbox_desktop') && !Mage::getStoreConfigFlag('abandonedcart/frontend/lightbox_mobile'))
        ){
            return $this;
        }

        $js = array();
        $block = $observer->getBlock();
        if ($block instanceof Bestworlds_AbandonedCart_Block_Template) {
            $js = array('<script type="text/javascript">');
            $cookie = Mage::getModel('core/cookie')->get(self::COOKIE_NAME);
            $closeCookie = Mage::getModel('core/cookie')->get('bw_lightbox_off');

            if($closeCookie!==false) {
                $js[] = " jQuery(window).bind('load', function() { jQuery('.bw_block_page').fadeOut().remove() }); ";
            }

            //$tracking= $this->_emailTracking();
            if ($cookie!==false) {
                if($closeCookie==false){
                    $js[] = " jQuery(window).bind('load', function() { jQuery('.bw_block_page').fadeOut().remove() }); ";
                }
            }else{
                $js[] = "
                        if (typeof(bwAbCartEventsHandled) == 'undefined'){
                        function bwAbCartValidateEmail(email) {
                            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            return re.test(email);
                        }

                        var bw_abandonedcarttimers = null;

                        function bwAbCartAjaxCall(value, captureType){
                            new Ajax.Request('" . Mage::getUrl('abandonedcart/main/registeremail', array('_secure'=>true)) . "', {
                                parameters: {
                                    email: value,
                                    type: captureType
                                },
                                onSuccess: function(response) {

                                }
                          });
                        }

                        function bwAbCartHandleEmailKeyUp(e, input){
                            var value = $(input).value;
                            if (bwAbCartValidateEmail(value)){

                                if (bw_abandonedcarttimers != null){
                                    clearTimeout(bw_abandonedcarttimers);
                                }

                                bw_abandonedcarttimers = setTimeout(function(){
                                    bwAbCartAjaxCall(value, '".Bestworlds_AbandonedCart_Model_Capturetypes::DURING_CHECKOUT."')
                                }, 500);
                            }
                        }


                        $(document).on('keyup', '[id=\"login-email\"]', bwAbCartHandleEmailKeyUp);
                        $(document).on('keyup', '[name=\"billing[email]\"]', bwAbCartHandleEmailKeyUp);
                 }
                ";
            }
            $js[] = '</script>';
        } elseif ($block instanceof Mage_Checkout_Block_Onepage || $block instanceof TM_FireCheckout_Block_Checkout) {
            $js = array('<script type="text/javascript">');
            $cookie = Mage::getModel('core/cookie')->get(self::COOKIE_NAME);
            $closeCookie = Mage::getModel('core/cookie')->get('bw_lightbox_off');

            if($closeCookie!==false) {
                $js[] = " jQuery(window).bind('load', function() { jQuery('.bw_block_page').fadeOut().remove() }); ";
            }

            //$tracking= $this->_emailTracking();

            if ($cookie!==false) {
                if($closeCookie==false) {
                    $js[] = " jQuery(window).bind('load', function() { jQuery('.bw_block_page').fadeOut().remove() }); ";
                }
            } else {
                $js[] = "
                        if (typeof(bwAbCartEventsHandled) == 'undefined'){
                        function bwAbCartValidateEmail(email) {
                            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            return re.test(email);
                        }

                        var bwAbCartTimers = null;

                        function bwAbCartAjaxCall(value, captureType){
                            new Ajax.Request('" . Mage::getUrl('abandonedcart/main/registeremail', array('_secure'=>true)) . "', {
                                parameters: {
                                    email: value,
                                    type: captureType
                                },
                                onSuccess: function(response) {

                                }
                          });
                        }

                        function bwAbCartHandleEmailKeyUp(e, input){
                            var value = $(input).value;
                            if (bwAbCartValidateEmail(value)){

                                if (bwAbCartTimers != null){
                                    clearTimeout(bwAbCartTimers);
                                }

                                bwAbCartTimers = setTimeout(function(){
                                    bwAbCartAjaxCall(value, '".Bestworlds_AbandonedCart_Model_Capturetypes::DURING_CHECKOUT."')
                                }, 500);
                            }
                        }
                        $(document).on('keyup', '[id=\"login-email\"]', bwAbCartHandleEmailKeyUp);
                        $(document).on('keyup', '[name=\"billing[email]\"]', bwAbCartHandleEmailKeyUp);
                    }
                ";
            }
            $js[] = '</script>';
        }
        $transport = $observer->getTransport();
        $html = $transport->getHtml().implode('', $js);
        $transport->setHtml($html);
    }
}
