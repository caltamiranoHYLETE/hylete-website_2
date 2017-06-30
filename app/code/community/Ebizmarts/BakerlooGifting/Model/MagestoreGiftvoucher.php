<?php

class Ebizmarts_BakerlooGifting_Model_MagestoreGiftvoucher extends Ebizmarts_BakerlooGifting_Model_Abstract {

    public function init() {
        $giftcard = Mage::getModel('giftvoucher/giftvoucher')
            ->loadByCode(trim($this->getCode()));

        if($giftcard->getId()) {

            $createdAt = Mage::getModel('giftvoucher/history')
                ->getCollection()
                ->addFieldToFilter('giftvoucher_id', $giftcard->getId())
                ->addFieldToFilter('action', Magestore_Giftvoucher_Model_Actions::ACTIONS_CREATE)
                ->setCurPage(1)
                ->setPageSize(1);

            $giftcard->setDateCreated($createdAt->getFirstItem()->getCreatedAt());
            $giftcard->setDateExpires($giftcard->getExpiredAt());
            $giftcard->setCode($giftcard->getGiftCode());
            $giftcard->setWebsiteId((int)$giftcard->getStoreId());
            $giftcard->setState($giftcard->getStatus());
            $giftcard->setStateText($giftcard->getStatusLabel());

            $isRedeemable = (int)($giftcard->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE);
            $giftcard->setIsRedeemable($isRedeemable);

        }

        $this->_giftcard = $giftcard;

    }

    public function isValid() {
        return true;
    }

    public function isEnabled() {
        $posConfig = ($this->_getGiftingConfig() == 'Magestore_Giftvoucher');
        return $posConfig;
    }

    public function addToCart(Mage_Sales_Model_Quote $quote) {

        if(!Mage::helper('giftvoucher')->isAvailableToAddCode())
            Mage::throwException(
                Mage::helper('bakerloo_restful')->__("Giftvoucher can't be added to cart")
            );

        $code = $this->_giftcard->getGiftCode();
        $giftVoucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code);

        if(!$giftVoucher->getId())
            Mage::throwException(
                Mage::helper('bakerloo_restful')->__('Wrong giftvoucher code')
            );


        $session = Mage::getSingleton('checkout/session');
        $codes = $session->getGiftCodes();

        if(!empty($codes))
            $codes .= ',' . $code;
        else
            $codes = $code;

        $codesAr = explode(',', $codes);
        $amountUsed = array();

        foreach($codesAr as $_code)
            $amountUsed[$_code] = 0;

        $amountUsed = implode(',', $amountUsed);

        /* let all calculations and error checking be done by the Magestore Giftvoucher module */
        $session->setGiftCodes($codes);
        $session->setBaseAmountUsed($amountUsed);
        $session->setUseGiftCard(true);

    }

    public function getQuoteGiftCards(Mage_Sales_Model_Quote $quote) {

        /* @see Magestore_Giftvoucher_Block_Payment_Form::getGiftVoucherDiscount */

        $session = Mage::getSingleton('checkout/session');
        $discounts = array();
        if ($codes = $session->getGiftCodes()) {

            $codesArray = explode(',', $codes);
            $codesDiscountArray = explode(',', $session->getCodesDiscount());

            /* session not cleared if more than one giftvoucher is added to cart, so codes and discounts may not match */
            $count = min(count($codesArray), count($codesDiscountArray));
            $codesArray = array_slice($codesArray, 0, $count);
            $codesDiscountArray = array_slice($codesDiscountArray, 0, $count);

            $discounts = array_combine($codesArray, $codesDiscountArray);
        }

        $discounts = $this->_formatGiftCardResponse($discounts);

        return $discounts;
    }

    public function create($storeId, $amount, $expirationDate = null) {
        $currencyCode = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
        $username     = Mage::app()->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getUsernameHeader());

        $this->_giftcard
            ->setData('gift_code', 'POS-[A.4]-[AN.6]')
            ->setData('balance', $amount)
            ->setData('amount', $amount)
            ->setData('currency', $currencyCode)
            ->setData('giftcard_template_id', $this->getDefaultTemplateId())
            ->setData('status', Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE)
            ->setData('store_id', $storeId)
            ->setData('extra_content', Mage::helper('bakerloo_restful')->__('Created via Ebizmarts POS by %s.', $username))
            ->setIncludeHistory(true);

        if( !is_null($expirationDate) )
            $this->_giftcard->setData('expired_at', $expirationDate);

        $this->_giftcard->save();

        return $this->_giftcard->getGiftCode();
    }

    public function addBalance($amount, $data = null) {

        $currentBalance = $this->_giftcard->getBalance();
        $username = Mage::app()->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getUsernameHeader());

        $this->_giftcard->setData('balance', $currentBalance + $amount)
            ->setData('amount', $amount)
            ->setData('status', Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE)
            ->setData('action', Magestore_Giftvoucher_Model_Actions::ACTIONS_UPDATE)
            ->setData('extra_content', Mage::helper('bakerloo_restful')->__('Updated via Ebizmarts POS by %s.', $username))
            ->setIncludeHistory(true);
        $this->_giftcard->save();

        return $this->_giftcard->getGiftCode();
    }

    public function addGiftVoucherForPosOrderItem(Mage_Sales_Model_Order_Item $item, Mage_Sales_Model_Order $order){

        $buyRequest = $item->getBuyRequest();
        $giftcodes = is_array($buyRequest->getGiftCode()) ? $buyRequest->getGiftCode() : array();

        foreach($giftcodes as $_code) {
            $giftvoucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($_code);

            if (!$giftvoucher->getId())
                Mage::throwException("Gift voucher does not exist.");

            if ($giftvoucher->getCustomerId())
                Mage::throwException("Gift card already sold.");

            $amt = $giftvoucher->getBalance() ? $giftvoucher->getBalance() : $buyRequest->getAmount(); //$buyRequest->getAmount() ? $buyRequest->getAmount() : $item->getPrice();
            $giftvoucherHistory = Mage::getModel('giftvoucher/history')->load($giftvoucher->getId(), 'giftvoucher_id'); //load($item->getId(), 'order_item_id');

            $giftvoucher->setBalance($amt)
                ->setCurrency($order->getBaseCurrencyCode())
                ->setGiftcardTemplateId($buyRequest->getGiftcardTemplateId())
                ->setCustomerId($order->getCustomerId())
                ->setCustomerName($order->getCustomerName())
                ->setCustomerEmail($order->getCustomerEmail())
                ->setRecipientName($buyRequest->getRecipientName())
                ->setRecipientEmail($buyRequest->getRecipientEmail())
                ->setMessage($buyRequest->getMessage())
                ->setNotifySuccess($buyRequest->getNotifySuccess())
                ->setDayToSend($buyRequest->getDayToSend())
                ->setStoreId($order->getId())
                ->save();

            if (!$giftvoucherHistory->getId()) {
                $giftvoucherHistory->setAction(Magestore_Giftvoucher_Model_Actions::ACTIONS_CREATE)
                    ->setComments(Mage::helper('giftvoucher')->__('Created for order %s', $order->getIncrementId()))
                    ->setExtraContent(Mage::helper('giftvoucher')->__('Created by POS'))
                    ->setGiftvoucherId($giftvoucher->getId());
            }
            $giftvoucherHistory
                ->setOrderIncrementId($order->getIncrementId())
                ->setOrderItemId($item->getId())
                ->setOrderAmount($order->getGrandTotal())
                ->setAmount($amt)
                ->setBalance($amt)
                ->setCurrency($order->getBaseCurrencyCode())
                ->setCustomerId($order->getCustomerId())
                ->setCustomerEmail($order->getCustomerEmail())
                ->save();


            if ($order->getCustomerId())
                $this->_setCustomervoucher($giftvoucher, $order);

            $giftProduct = Mage::getModel('giftvoucher/product')->loadByProduct($item->getProduct());

            if ($giftProduct->getId()) {
                $giftvoucher->setDescription($giftProduct->getGiftcardDescription());

                $conditionsArr = unserialize($giftProduct->getConditionsSerialized());
                $actionsArr = unserialize($giftProduct->getActionsSerialized());

                if (!empty($conditionsArr) && is_array($conditionsArr))
                    $giftvoucher->getConditions()->loadArray($conditionsArr);

                if (!empty($actionsArr) && is_array($actionsArr))
                    $giftvoucher->getActions()->loadArray($actionsArr);

            }

            $templateImage = $buyRequest->getGiftcardTemplateImage();
            if (!empty($templateImage) and $buyRequest->getGiftcardUseCustomImage()) {
                $dir = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'giftvoucher' . DS . 'images' . DS . $templateImage;

                if (file_exists($dir)) {
                    $imageObj = new Varien_Image($dir);
                    $image_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'giftvoucher/template/images/';
                    $buyRequest['giftcard_template_image'] = time() . $buyRequest['giftcard_template_image'];
                    $imageObj->save(Mage::getBaseDir() . str_replace("/", DS, strstr($image_path, '/media')) . $buyRequest['giftcard_template_image']);
                    Mage::helper('giftvoucher')->customResizeImage($image_path, $buyRequest['giftcard_template_image'], 'images');
                    $giftvoucher->setGiftcardCustomImage(true);
                    unlink($dir);
                } else
                    $templateImage = 'default.png';

            }
            $giftvoucher->setGiftcardTemplateImage($templateImage);

            if ($buyRequest->getRecipientShip() != null)
                $giftvoucher->setRecipientAddress($order->getShippingAddress()->format('text'));


            if ($timeLife = Mage::helper('giftvoucher')->getGeneralConfig('expire', $item->getStoreId())) {
                $expire = new Zend_Date(); //$currentDate);
                $expire->addDay($timeLife);
                $giftvoucher->setExpiredAt($expire->toString('YYYY-MM-dd HH:mm:ss'));
            }

            if ($giftvoucher->getDayToSend() && strtotime($giftvoucher->getDayToSend()) > time())
                $giftvoucher->setData('dont_send_email_to_recipient', 1);

            if ($buyRequest->getRecipientShip() && !Mage::helper('giftvoucher')->getEmailConfig('send_with_ship', $order->getStoreId()))
                $giftvoucher->setData('dont_send_email_to_recipient', 1)
                    ->setData('is_sent', 1);

            $giftvoucher->save();
        }
    }

    private function _setCustomervoucher($giftvoucher, $order){
        Mage::getModel('giftvoucher/customervoucher')
            ->setCustomerId($order->getCustomerId())
            ->setVoucherId($giftvoucher->getId())
            ->setAddedDate(now(true))
            ->save();
    }

    public function getGiftvoucher($code = null, $amount){
        if(!is_null($code))
            $voucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code);

        if(!isset($voucher) or !$voucher->getId())
            $voucher = $this->getPrePrinted()->addFieldToFilter('amount', array('eq' => $amount))->getFirstItem();

        return $voucher;
    }

    protected function _formatGiftCardResponse(array $sessionGiftCards) {
        $return = array();

        foreach($sessionGiftCards as $giftcardCode => $giftcardAmount) {

            $giftcard = $giftcard = Mage::getModel('giftvoucher/giftvoucher')
                ->loadByCode($giftcardCode);

            $return []= array(
              'id'          => (int)$giftcard->getGiftvoucherId(),
              'code'        => $giftcard->getGiftCode(),
              'base_amount' => $giftcard->getBalance(),
              'amount'      => $giftcard->getBalance(),
            );

        }

        return $return;
    }

    public function getDefaultTemplateId() {
        $template = Mage::getModel('giftvoucher/gifttemplate')->getCollection()->setPageSize(1)->setCurPage(1);

        return $template->getFirstItem()->getGiftcardTemplateId();
    }

    public function getOptions(Mage_Catalog_Model_Product $product){
        $giftVoucherPriceType = $product->getGiftPriceType();
        if($giftVoucherPriceType != 1) {
            //We dont support other options than Default at this point.
            return array();
        }

        $giftValue = Mage::helper('giftvoucher/giftproduct')->getGiftValue($product);
        $giftVoucherType = (int)$product->getGiftType(); //1-Fixed value, 2-Range of values, 3-Dropdownvalues
        $giftvoucherAmounts = array();
        $websiteId = $product->getStore()->getWebsiteId();

        if($giftVoucherType == 1) {
            $giftCardTypeLabel = 'Fixed value';
            $giftvoucherAmounts[] = array(
                'website_id' => $websiteId,
                'value' => $giftValue['value']
            );
        }
        elseif($giftVoucherType == 2) {
            $giftCardTypeLabel  = 'Range of values';
        }
        else {
            $giftCardTypeLabel = 'Dropdown values';
            $valueOptions = $giftValue['options'];

            foreach($valueOptions as $_opt)
                $giftvoucherAmounts[] = array(
                    'website_id' => $websiteId,
                    'value' => $_opt
                );
        }

        $salable = true;
        $options = array(
            'type'              => $giftVoucherType, //1-Fixed value, 2-Range of values, 3-Dropdownvalues
            'type_label'        => Mage::helper('bakerloo_restful')->__($giftCardTypeLabel),
            'is_salable'        => $salable,
            'amounts'           => $giftvoucherAmounts
        );

        $allowOpenAmount = (($giftVoucherType === 2) ? true : false);
        $options['allow_open_amount'] = $allowOpenAmount;

        if($allowOpenAmount) {
            $options['open_amount_min'] = $giftValue['from'];
            $options['open_amount_max'] = $giftValue['to'];
        }

        return $options;
    }

    public function getBuyInfoOptions($data){
        $options = array();

        if(isset($data->gift_card_options)) {

            $giftCardData = $data->gift_card_options;
            $amount = $giftCardData->amount;
            $options['amount'] = $amount;

            $options['giftcard_template_id'] = $this->getDefaultTemplateId();

            $options['sender_name']       = isset($giftCardData->sender_name) ? $giftCardData->sender_name : "";
            $options['sender_email']      = isset($giftCardData->sender_email) ? $giftCardData->sender_email : "";
            $options['recipient_name']    = isset($giftCardData->recipient_name) ? $giftCardData->recipient_name : "";
            $options['recipient_email']   = isset($giftCardData->recipient_email) ? $giftCardData->recipient_email : "";
            $options['message']           = isset($giftCardData->comments) ? $giftCardData->comments : "";
            $options['recipient_ship']    = null;
            $options['recipient_address'] = null;
            $options['day_to_send']       = null;
            $options['gift_code']         = isset($giftCardData->gift_code) ? $giftCardData->gift_code : null;
        }

        if(!is_null($options['recipient_name']))
            $options['send_friend'] = true;

        return $options;
    }

    public function getItemData(Mage_Sales_Model_Order_Item $item, Magestore_Giftvoucher_Model_Giftvoucher $giftvoucher) {
        $selection = $item->getBuyRequest();
        $createdAt = Mage::getModel('giftvoucher/history')
            ->getCollection()
            ->addFieldToFilter('giftvoucher_id', $giftvoucher->getId())
            ->addFieldToFilter('action', Magestore_Giftvoucher_Model_Actions::ACTIONS_CREATE)
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        if($createdAt)
            $createdAt = $createdAt->getCreatedAt();

        $result = array(
            'gift_code' => array($giftvoucher->getGiftCode()),
            'date_created' => $createdAt,
            'date_expires' => $giftvoucher->getExpiredAt(),
            'balance' => $giftvoucher->getBalance(),
            'sender_name' => $selection->getSenderName(),
            'sender_email' => $selection->getSenderEmail(),
            'recipient_name' => $selection->getRecipientName(),
            'recipient_email' => $selection->getRecipientEmail(),
            'message' => $selection->getMessage()
        );

        return $result;
    }

    public function getAvailableCodes(){

        $cards = $this->getPrePrinted();
        $result = array();

        foreach($cards as $c)
            $result[] = $c->getGiftCode();

        return $result;
    }

    public function getPrePrinted(){

        $collection = Mage::getModel('giftvoucher/giftvoucher')->getCollection()
            ->addFieldToFilter('customer_id', array('eq' => ''));
        return $collection;
    }

    public function getCollectionWithHistory(){
        return Mage::getModel('giftvoucher/giftvoucher')->getCollection()->joinHistory();
    }

}