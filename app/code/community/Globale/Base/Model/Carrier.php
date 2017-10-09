<?php
class Globale_Base_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

    const CODE = 'globale';

    protected $_code = self::CODE;

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * FreeShipping Rates Collector
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $GlobaleSDK = Mage::registry('globale_sdk');

        //If Not GE api mode and not GE operated - don't add Carrier
        if ((!$this->getConfigFlag('active') || !Mage::registry('globale_user_supported') )&& !Mage::registry('globale_api')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $this->_updateFreeMethodQuote($request);

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('standard');
        $method->setMethodTitle('Standard');

        $method->setPrice('0.00');
        $method->setCost('0.00');

        $result->append($method);

        return $result;
    }

    /**
     * Allows free shipping when all product items have free shipping (promotions etc.)
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return void
     */
    protected function _updateFreeMethodQuote($request)
    {
        $freeShipping = false;
        $items = $request->getAllItems();
        $c = count($items);
        for ($i = 0; $i < $c; $i++) {
            if ($items[$i]->getProduct() instanceof Mage_Catalog_Model_Product) {
                if ($items[$i]->getFreeShipping()) {
                    $freeShipping = true;
                } else {
                    return;
                }
            }
        }
        if ($freeShipping) {
            $request->setFreeShipping(true);
        }
    }

    public function getAllowedMethods()
    {
        return array(
            'standard' => $this->getConfigData('title'),
        );        
    }

    public function isTrackingAvailable()
    {
        return true;
    }


}

