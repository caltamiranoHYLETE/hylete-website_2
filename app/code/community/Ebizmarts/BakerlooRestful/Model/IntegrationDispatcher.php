<?php

class Ebizmarts_BakerlooRestful_Model_IntegrationDispatcher {

    /**
     * To store implementation instance.
     */
    private $_imp;

    /**
     * Returns loyalty implementation.
     *
     * @param array $args
     * @return \Ebizmarts_BakerlooRestful_Model_IntegrationDispatcher
     */
    public function __construct($args) {

        if(isset($args['gift_type']))
            $integration = $args['gift_type'];
        elseif(isset($args['loyalty_type']))
            $integration = $args['loyalty_type'];
        else
            $integration = Mage::helper('bakerloo_restful/integrations')->getIntegrationFromConfig($args['integration_type']);

        $this->_imp = self::factory($integration);

        foreach($args as $key => $value)
            $this->_imp->setData($key, $value);

        $this->_imp->init();
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args) {

        if(count($args) == 1)
            return $this->_imp->{$method}($args[0]);


        return call_user_func_array(array($this->_imp, $method), $args);
    }

    /**
     * Loyalty integration instance factory.
     *
     * @param string $type Integration identifier.
     * @return Ebizmarts_BakerlooLoyalty_Model_Abstract
     */
    public static function factory($type) {

        $model = new stdClass;

        switch ($type) {
            case 'TBT_Rewards':
                $model = Mage::getModel('bakerloo_loyalty/sweetTooth');
                break;
            case 'Enterprise_Reward':
                $model = Mage::getModel('bakerloo_loyalty/enterpriseRewards');
                break;
            case 'AW_Points':
                $model = Mage::getModel('bakerloo_loyalty/aheadworksPoints');
                break;
            case 'Enterprise_GiftCardAccount':
                $model = Mage::getModel('bakerloo_gifting/enterpriseGiftcard');
                break;
            case 'AW_Giftcard':
                $model = Mage::getModel('bakerloo_gifting/aheadworksGiftcard');
                break;
            case 'Magestore_Giftvoucher':
                $model = Mage::getModel('bakerloo_gifting/magestoreGiftvoucher');
                break;
            default:
                Mage::throwException('No integration configured.');
        }

        return $model;

    }

}