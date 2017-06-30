<?php

class Ebizmarts_BakerlooRestful_Model_Api_Coupons extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model   = "salesrule/rule";
    public $defaultSort = "code";

    protected function _getIndexId() {
        return 'rule_id';
    }

    public function checkPostPermissions() {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/coupons/create'));
    }

    protected function _getCollection() {

        /** @var $collection Mage_SalesRule_Model_Mysql4_Rule_Collection */
        $collection = Mage::getModel('salesrule/rule')
            ->getResourceCollection();
        $collection->addWebsitesToResult();//@TODO: Check this Magento 1620

        $collection->addFieldToFilter('code', array('neq' => ''));

        return $collection;

    }

    public function _createDataObject($id = null, $data = null) {

        $result = parent::_createDataObject($id, $data);

        if(isset($result['conditions_serialized'])) {
            $conditions = unserialize($result['conditions_serialized']);

            if(isset($conditions['conditions']) and !empty($conditions['conditions'])) {
                return array();
            }

            unset($result['conditions_serialized']);
        }
        if(isset($result['actions_serialized'])) {
            $actions = unserialize($result['actions_serialized']);

            if(isset($actions['conditions']) and !empty($actions['conditions'])) {
                return array();
            }

            unset($result['actions_serialized']);
        }
        if(isset($result['rule_id'])) {
            $result['rule_id'] = (int)$result['rule_id'];
        }
        if(isset($result['uses_per_customer'])) {
            $result['uses_per_customer'] = (int)$result['uses_per_customer'];
        }
        if(isset($result['is_active'])) {
            $result['is_active'] = (int)$result['is_active'];
        }
        if(isset($result['times_used'])) {
            $result['times_used'] = (int)$result['times_used'];
        }
        if(isset($result['uses_per_coupon'])) {
            $result['uses_per_coupon'] = (int)$result['uses_per_coupon'];
        }
        if(isset($result['coupon_type'])) {
            $result['coupon_type'] = (int)$result['coupon_type'];
        }
        if(isset($result['discount_amount'])) {
            $result['discount_amount'] = (float)$result['discount_amount'];
        }
        if(isset($result['sort_order'])) {
            $result['sort_order'] = (int)$result['sort_order'];
        }
        if(isset($result['is_rss'])) {
            unset($result['is_rss']);
        }
        if(isset($result['is_advanced'])) {
            unset($result['is_advanced']);
        }
        if(isset($result['use_auto_generation'])) {
            unset($result['use_auto_generation']);
        }
        if(isset($result['stop_rules_processing'])) {
            unset($result['stop_rules_processing']);
        }

        if(isset($result['coupon_code']))
            $result['code'] = $result['coupon_code'];

        ksort($result);

        return $result;

    }

    /**
     * Validate provided coupon code.
     * Receives an order and validates coupon code.
     *
     * PUT
     */
    public function put() {

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        //Apply coupon and validate
        $couponCode = $data->coupon_code;

        if(empty($couponCode)) {
            Mage::throwException('Invalid coupon code.');
        }

        $quote = Mage::helper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data, true);

        $quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
            ->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();

        if ($couponCode != $quote->getCouponCode()) {
            //DELETE quote so we don't leave garbage in db
            $quote->delete();

            $errorMessage = Mage::helper('bakerloo_restful/sales')->__("Coupon code %s is not valid.", Mage::helper('core')->escapeHtml($couponCode));
            Mage::throwException($errorMessage);
        }

        $coupon = Mage::getModel('salesrule/coupon');
        /** @var Mage_SalesRule_Model_Coupon */
        $coupon->load($couponCode, 'code');
        if ($coupon->getId()) {
            $ruleId = $coupon->getRuleId();
            $rule = Mage::getModel('salesrule/rule')->load($ruleId);

            $cartData = Mage::helper('bakerloo_restful/sales')->getCartData($quote);

            $_couponCode = $rule->getCouponCode();
            if(!$_couponCode)
                $_couponCode = $coupon->getCode();


            $returnData = array(
                'valid'             => true,
                'coupon_code'       => $_couponCode,
                'uses_per_coupon'   => (int)$rule->getUsesPerCoupon(),
                'uses_per_customer' => (int)$rule->getUsesPerCustomer(),
                'times_used'        => (int)$rule->getTimesUsed(),
                'discount_amount'   => (float)$rule->getDiscountAmount(),
                'discount_type'     => $rule->getSimpleAction(),
                'name'              => $rule->getName(),
                'description'       => $rule->getDescription(),
                'order'             => $cartData,
            );

            //DELETE quote so we don't leave garbage in db
            $quote->delete();

        }
        else {
            //DELETE quote so we don't leave garbage in db
            $quote->delete();

            Mage::throwException('Coupon does not exist.');
        }



        return $returnData;

    }

    /**
     * Create a coupon.
     *
     * @return $this|void
     */
    public function post() {
        parent::post();

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $postData = $this->getJsonPayload();

        $couponNameAndCode = $this->_generateCode();

        $description = '';

        if(isset($postData->credit_note_id)) {
            /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($postData->credit_note_id);

            if($creditmemo->getId()) {
                $creditmemo->addComment(Mage::helper('bakerloo_restful')->__("Coupon code %s", $couponNameAndCode), false, false)
                    ->save();
            }

            $description = Mage::helper('bakerloo_restful')->__("Credit memo #%s", $postData->credit_note_id);
        }

        $data = array(
            'name'               => $couponNameAndCode,
            'is_active'          => 1,
            'website_ids'        => array(Mage::app()->getStore()->getWebsiteId()),
            'customer_group_ids' => $postData->customer_group_ids,
            'coupon_type'        => 2,
            'coupon_code'        => $couponNameAndCode,
            'uses_per_customer'  => 1,
            'uses_per_coupon'    => 1,
            //'simple_action'      => 'by_fixed',
            'simple_action'      => 'cart_fixed',
            'discount_amount'    => $postData->amount,
            'description'        => $description
        );

        $model = Mage::getModel('salesrule/rule');
        /*Mage::dispatchEvent(
            'adminhtml_controller_salesrule_prepare_save',
            array('request' => $this->getRequest()));*/

        $validateResult = $model->validateData(new Varien_Object($data));
        if ($validateResult !== true) {
            Mage::throwException(current($validateResult));
        }

        $model->loadPost($data);

        $model->save();

        return $this->_createDataObject($model->getId());
    }

    /**
     * Generate coupon code
     *
     * @return string
     */
    protected function _generateCode() {
        $length  = (int)Mage::helper('bakerloo_restful')->config('pos_coupon/length', $this->getStoreId());
        $split   = 0;

        $splitChar = '-';

        if(class_exists('Mage_SalesRule_Helper_Coupon', false))
            $charset = Mage::helper('salesrule/coupon')->getCharset(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC);
        else
            $charset = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');

        $code = '';
        $charsetSize = count($charset);
        for ($i=0; $i<$length; $i++) {
            $char = $charset[mt_rand(0, $charsetSize - 1)];
            if ($split > 0 && ($i % $split) == 0 && $i != 0) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }

        $code = 'POS-' . $code;

        return $code;
    }


    /**
     * Send coupon code via email as an attachment.
     *
     * @return array Email sending result
     */
    public function sendEmail() {

        Mage::app()->setCurrentStore($this->getStoreId());

        try {

            $data = $this->getJsonPayload();

            $email = (string)$this->_getQueryParameter('email');

            $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            if ($validEmail === false) {
                Mage::throwException(Mage::helper('bakerloo_restful')->__('The provided email is not a valid email address.'));
            }

            $emailSent = false;

            if(isset($data->attachments) and is_array($data->attachments) and !empty($data->attachments)) {

                $couponData = current($data->attachments);

                $coupon = Mage::helper('bakerloo_restful/email')->sendCoupon($email, $couponData, $this->getStoreId());

                $emailSent = (bool)$coupon->getEmailSent();

            }

            $result['email_sent'] = $emailSent;

        } catch (Exception $e) {
            Mage::logException($e);

            $result['error_message'] = $e->getMessage();
            $result['email_sent']    = false;
        }

        return $result;

    }

}