<?php

class Ebizmarts_BakerlooGifting_Model_AheadworksGiftcard extends Ebizmarts_BakerlooGifting_Model_Abstract {

    public function init() {
        $giftcard = Mage::getModel('aw_giftcard/giftcard')
            ->loadByCode(trim($this->getCode()));

        $giftcard->setDateCreated($giftcard->getCreatedAt());
        $giftcard->setDateExpires($giftcard->getExpireAt());

        $this->_giftcard = $giftcard;
    }

    public function isValid() {
        return $this->_giftcard->isValidForRedeem($this->getStoreId());
    }

    public function isEnabled() {
        $posConfig = ($this->_getGiftingConfig() == 'AW_Giftcard');
        return $posConfig;
    }

    public function addToCart(Mage_Sales_Model_Quote $quote) {
        Mage::helper('aw_giftcard/totals')->addCardToQuote($this->_giftcard, $quote);
    }

    public function getQuoteGiftCards(Mage_Sales_Model_Quote $quote) {
        $quoteGiftCards = array();

        $_quoteGift = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards($quote->getId());
        if(!empty($_quoteGift))
            $quoteGiftCards = $this->_formatGiftCardResponse($_quoteGift);

        return $quoteGiftCards;
    }

    public function create($storeId, $amount, $expirationDate = null) {
        $this->_giftcard
            ->setData('status', 1)
            ->setData('website_id', $this->websiteIdByStoreId($storeId))
            ->setData('balance', $amount);

        if( !is_null($expirationDate) )
            $this->_giftcard->setData('expire_at', $expirationDate);

        $this->_giftcard->save();

        return $this->_giftcard->getCode();
    }

    public function addBalance($amount, $data = null) {
        $currentAmount = $this->_giftcard->getBalance();
        $username = Mage::app()->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getUsernameHeader());
        $user = Mage::getModel('admin/user')->loadByUsername($username);
        Mage::getSingleton('admin/session')->setUser($user);

        $this->_giftcard
            ->setData('status', 1)
            ->setData('balance', $currentAmount + $amount);

        if(isset($data->creditnote_id)) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($data->creditnote_id);
            $this->_giftcard->setData('creditmemo', $creditmemo);
        }

        $this->_giftcard->save();

        return $this->_giftcard->getCode();
    }

    protected function _formatGiftCardResponse(array $quoteGiftCards) {

        $return = array();

        foreach($quoteGiftCards as $giftcard) {

            $return []= array(
              'id'          => (int)$giftcard->getGiftcardId(),
              'code'        => $giftcard->getCode(),
              'base_amount' => $giftcard->getCardBalance(),
              'amount'      => $giftcard->getCardBalance(),
            );

        }

        return $return;
    }

    public function getOptions(Mage_Catalog_Model_Product $product){
        $allowOpenAmount = ((int)$product->getAllowOpenAmount() === 1 ? true : false);

        $giftCardType = (int)$product->getGiftcardType();
        $giftCardTypeLabel = $this->_getGiftCardTypeLabel($giftCardType);

        $amounts = $product->getPriceModel()->getAmountOptions($product);

        $options = array(
            'type'              => $giftCardType,
            'type_label'        => Mage::helper('bakerloo_restful')->__($giftCardTypeLabel),
            'amounts'           => $amounts,
            'allow_open_amount' => $allowOpenAmount,
        );

        $minAmount = (int)$product->getAwGcOpenAmountMin();
        $maxAmount = (int)$product->getAwGcOpenAmountMax();

        if($allowOpenAmount) {
            $options['open_amount_min'] = (is_null($minAmount) ? 0.0000 : $minAmount);
            $options['open_amount_max'] = (is_null($maxAmount) ? 0.0000 : $maxAmount);
        }

        return $options;
    }

    public function getBuyInfoOptions($data){
        $options = array();

        if(isset($data->gift_card_options)){

            $giftCardData = $data->gift_card_options;
            $amount = $giftCardData->amount;
            $amounts = $giftCardData->amounts;

            $customAmount = true;

            if(!empty($amounts)) {

                foreach($amounts as $_gcAmount) {
                    if( ($amount == $_gcAmount->value)
                        or ($amount == $_gcAmount->website_value) ) {
                        $customAmount = false;
                    }
                }

            }

            $options['aw_gc_custom_amount']   = ($customAmount ? $giftCardData->amount : '');
            $options['aw_gc_amount']          = ($customAmount ? '' : $giftCardData->amount);
            $options['aw_gc_sender_name']     = $giftCardData->sender_name;
            $options['aw_gc_sender_email']    = $giftCardData->sender_email;
            $options['aw_gc_recipient_name']  = $giftCardData->recipient_name;
            $options['aw_gc_recipient_email'] = $giftCardData->recipient_email;
            $options['aw_gc_message']         = $giftCardData->comments;
        }

        return $options;
    }

    public function getItemData(Mage_Sales_Model_Order_Item $item, AW_Giftcard_Model_Giftcard $gift) {
        $selection = $item->getBuyRequest();

        $result = array(
            'gift_code' => array($gift->getCode()),
            'date_created' => $gift->getCreatedAt(),
            'date_expires' => $gift->getExpireAt(),
            'balance' => $gift->getBalance(),
            'sender_name' => $selection->getAwGcSenderName(),
            'sender_email' => $selection->getAwGcSenderEmail(),
            'recipient_name' => $selection->getAwGcRecipientName(),
            'recipient_email' => $selection->getAwGcRecipientEmail(),
            'message' => $selection->getAwGcMessage()
        );

        return $result;
    }

    private function _getGiftCardTypeLabel($type){
        $label = '';

        switch($type){
            case 0:
                $label = 'Virtual';
                break;
            case 1:
                $label = 'Physical';
                break;
            case 2:
                $label = 'Combined';
                break;
            default:
                break;
        }

        return $label;
    }
}