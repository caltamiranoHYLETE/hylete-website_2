<?php

class Ebizmarts_BakerlooRestful_Model_Api_Giftcards extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = "bakerloo_restful/integrationDispatcher";
    public $defaultSort = "giftvoucher_id"; //"date_created";

    protected $returnFields = array(
        'code', 'status', 'date_created', 'date_expires', 'website_id',
        'balance', 'state', 'is_redeemable', 'is_salable', 'state_text', 'id'
    );

    /**
     * Create a Giftcard.
     *
     * @return $this
     */
    public function post() {
        parent::post();

        $postData = $this->getJsonPayload();

        $storeId = isset($postData->store_id) ? $postData->store_id: null;
        if(is_null($storeId))
            Mage::throwException('Please provide a Store ID.');

        Mage::app()->setCurrentStore($storeId);

        $this->setStoreId($storeId);

        if(isset($postData->gift_code)) {
            $card = $this->_giftcardInstance($postData->gift_code);
            $code = $card->addBalance((float)$postData->amount, $postData);
        }
        else {
            $card = $this->_giftcardInstance();
            $code = $card->create($storeId, ((float)$postData->amount));
        }

        $customerId    = isset($postData->customer_id) ? $postData->customer_id : null;
        $customerEmail = isset($postData->customer_email) ? $postData->customer_email : null;
        $creditNoteId  = isset($postData->creditnote_id) ? $postData->creditnote_id : null;

        Mage::dispatchEvent("pos_giftcard_create_after", array("code"           => $code,
                                                               "customer_id"    => $customerId,
                                                               "customer_email" => $customerEmail,
                                                               "creditnote_id"  => $creditNoteId,
                                                         )
        );

        return $this->_createDataObject($code);
    }

    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get() {

        $this->_verifyModuleInstalled();

        Mage::app()->setCurrentStore($this->getStoreId());

        $this->checkGetPermissions();

        $identifier = $this->_getIdentifier(true);

        if($identifier) { //get item by code

            if(!empty($identifier))
                return $this->_createDataObject($identifier);
            else
                throw new Exception('Incorrect request.');

        }
        else
            throw new Exception('Incorrect request.');

    }

    public function _createDataObject($id = null, $data = null) {
        $result = new stdClass;

        if(!is_null($data))
        {
            $giftCard = $data;
        }
        else
        {
            $card = $this->_giftcardInstance($id);
            $giftCard = $card->getImp();
        }

        if($giftCard->getId()) {
            $resultTemp = $giftCard->toArray();

            if(isset($resultTemp['giftcardaccount_id'])) { //Enterprise_GiftCard
                $resultTemp['id'] = (int)$resultTemp['giftcardaccount_id'];
                unset($resultTemp['giftcardaccount_id']);
            }
            elseif(isset($resultTemp['entity_id'])) { //AW_GiftCard
                $resultTemp['id'] = (int)$resultTemp['entity_id'];
                unset($resultTemp['entity_id']);
            }
            elseif(isset($resultTemp['giftvoucher_id'])) { //Magestore_Giftvoucher
                $resultTemp['id'] = (int)$resultTemp['giftvoucher_id'];
                unset($resultTemp['giftvoucher_id']);
            }

            if(isset($resultTemp['gift_code'])){
                $resultTemp['code'] = $resultTemp['gift_code'];
                unset($resultTemp['gift_code']);
            }

            //null causes Error converting value {null} to type 'System.Int32'. Path 'is_redeemable', line 1, position 126.
            $result = array_fill_keys($this->returnFields, 0);

            foreach($result as $key => $value) {
                if( array_key_exists($key, $resultTemp) )
                    $result[$key] = $resultTemp[$key];
            }

            if(isset($resultTemp['website_id']) and $resultTemp['website_id'] == 0)
                $result['is_salable'] = 1;

            ksort($result);
        }

        return $result;
    }

    /**
     * Validate provided gift card.
     * Receives an order and gift card code.
     *
     * PUT
     */
    public function put() {

        $this->_verifyModuleInstalled();

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        //Apply gift cards and validate
        $giftCards = $data->gift_card;

        if(empty($giftCards) or !is_array($giftCards))
            Mage::throwException(Mage::helper('bakerloo_restful')->__('No gift cards found in data.'));

        $quote = Mage::helper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data, true);

        $session = Mage::getSingleton('checkout/session');
        $session->unsetData('gift_codes')
            ->unsetData('codes_discount');

        foreach($giftCards as $_giftCardCode)
            $this->_giftcardInstance($_giftCardCode)->addToCart($quote);

        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();

        $cartData = Mage::helper('bakerloo_restful/sales')->getCartData($quote);

        $returnData = array(
            'order'              => $cartData,
            'applied_gift_cards' => $this->_giftcardInstance()->getQuoteGiftCards($quote),
        );

        $quote->delete();

        return $returnData;

    }

    public function getOrderItemData(Mage_Sales_Model_Order_Item $item) {
        $result = array();
        $type = $item->getProductType();

        if($type == 'giftcard') {

            $enterpriseCodes = $item->getProductOptionByCode('giftcard_created_codes');
            $enterpriseCodes = is_array($enterpriseCodes) ? $enterpriseCodes : array();

            foreach($enterpriseCodes as $_eCode) {
                $_eCode = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->loadByCode($_eCode);

                if($_eCode->getId()) {

                    if(empty($result))
                        $result = Mage::getModel('bakerloo_gifting/enterpriseGiftcard')->getItemData($item, $_eCode);
                    else
                        $result['gift_code'][] = $_eCode->getCode();

                }
            }

        }
        elseif($type == 'aw_giftcard') {
            $awCodes = $item->getProductOptionByCode('aw_gc_created_codes');
            $awCodes = is_array($awCodes) ? $awCodes : array();

            foreach($awCodes as $_awCode) {
                $_awCode = Mage::getModel('aw_giftcard/giftcard')->loadByCode($_awCode);

                if($_awCode->getId()) {
                    if(empty($result))
                        $result = Mage::getModel('bakerloo_gifting/aheadworksGiftcard')->getItemData($item, $_awCode);
                    else
                        $result['gift_code'][] = $_awCode->getCode();
                }
            }

        }
        elseif($type == 'giftvoucher') {

            $gifts = Mage::getModel('giftvoucher/history')
                ->getCollection()
                ->addFieldToFilter('order_item_id', array('eq' => $item->getId()));
            $gifts->getSelect()->group('giftvoucher_id');

            foreach($gifts as $_gift) {

                $voucher = Mage::getModel('giftvoucher/giftvoucher')->load($_gift->getGiftvoucherId());

                if($voucher->getId()) {
                    if(empty($result))
                        $result = Mage::getModel('bakerloo_gifting/magestoreGiftvoucher')->getItemData($item, $voucher);
                    else
                        $result['gift_code'][] = $voucher->getGiftCode();
                }
            }
        }

        return $result;
    }

    protected function _verifyModuleInstalled() {

        $giftingOk = Mage::helper('bakerloo_gifting')->canUse();

        $giftcard = $this->_giftcardInstance();

        if( (false === $giftingOk) or (!$giftcard->isEnabled()))
            Mage::throwException(Mage::helper('bakerloo_restful')->__("Feature is not available."));

    }

    protected function _giftcardInstance($code = null) {

        $websiteId = Mage::app()->getStore($this->getStoreId())->getWebsiteId();

        return Mage::getModel('bakerloo_restful/integrationDispatcher',
            array('integration_type' => 'gifting', 'website_id' => $websiteId, 'store_id' => $this->getStoreId(), 'code' => $code));
    }

    protected function _getIndexId() {
        return 'gift_code'; //@TODO: check Magestore is the configured integration
    }

    public function preprinted(){

        $integration = Mage::helper('bakerloo_gifting')->getIntegrationFromConfig();

        if($integration == 'Magestore_Giftvoucher')
            return $this->_getAllItems();
        else
            Mage::throwException(Mage::helper('bakerloo_restful')->__('The configured gift card extension does not provide pre-printed cards.'));
    }

    protected function _getCollection() {
        return Mage::helper('bakerloo_gifting')->getGiftcard('giftvoucher')->getPrePrinted();
    }

 }