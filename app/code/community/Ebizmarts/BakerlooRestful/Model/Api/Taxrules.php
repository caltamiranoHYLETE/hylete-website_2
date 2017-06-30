<?php

class Ebizmarts_BakerlooRestful_Model_Api_Taxrules extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    /**
     * Model name.
     *
     * @var string
     */
    protected $_model   = "tax/calculation_rule";
    public $defaultSort = "tax_calculation_rule_id";
    public $pageSize    = 5;

    protected $_iterator = false;

    public function _beforePaginateCollection($collection, $page, $since) {
        $this->_collection
            ->addCustomerTaxClassesToResult()
            ->addProductTaxClassesToResult()
            ->addRatesToResult();
        return $this;
    }

    public function returnDataObject($data) {

        $_data = array(
            'id'                          => (int)$data['tax_calculation_rule_id'],
            'priority'                    => (int)$data['priority'],
            'calculate_off_subtotal_only' => (int)$data['calculate_subtotal'],
            'code'                        => $data['code'],
            'sort_order'                  => (int)$data['position'],
            'customer_tax_classes'        => array_map('intval', array_values( array_unique($data['customer_tax_classes'], SORT_NUMERIC) ) ),
            'product_tax_classes'         => array_map('intval', array_values( array_unique($data['product_tax_classes'], SORT_NUMERIC) ) ),
            'rates'                       => array(),
        );

        $taxClasses = $_data['product_tax_classes'];

        $filterRate = (int)Mage::helper('bakerloo_restful')->config('general/filter_tax_rates');

        $rateObj = Mage::getModel('tax/calculation_rate');

        if( !empty($taxClasses) ) {
            foreach ($taxClasses as $_taxC) {
                foreach ($data['tax_rates'] as $_rate) {

                    //if (in_array((((int)$_rate).$_taxC), $proc))
                    //    continue;

                    //array_push($proc, (((int)$_rate).$_taxC));

                    $rt = $rateObj->load($_rate);

                    if ($rt->getId()) {

                        if($filterRate)
                            if(!$rt->getEbizmartsPosSynch())
                                continue;

                        if (!$rt->hasTaxPostcode())
                            $rt->setTaxPostcode('*');

                        $_data ['rates'][] = array(
                            'id'                => (int)$rt->getTaxCalculationRateId(),
                            'code'              => $rt->getCode(),
                            'country_id'        => $rt->getTaxCountryId(),
                            'region_id'         => $rt->getTaxRegionId(),
                            'tax_class'         => (int)$_taxC,
                            'rate'              => (float)$rt->getRate(),
                            'postcode'          => $rt->getTaxPostcode(),
                            'postcode_is_range' => (int)$rt->getZipIsRange(),
                        );

                    }

                    $rateObj->setData(null);
                }
            }
        }

        $result = new Varien_Object($_data);

        Mage::dispatchEvent($this->_eventPrefix . '_return_before', array($this->_eventObject => $result));

        return $result->getData();
    }

    public function put() {

        //@TODO: Return cart with taxes calculation.

        parent::put();

        return array();
    }

}