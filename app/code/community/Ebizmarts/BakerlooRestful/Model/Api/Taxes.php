<?php

class Ebizmarts_BakerlooRestful_Model_Api_Taxes extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    /**
     * Model name.
     *
     * @var string
     */
    protected $_model = "tax/calculation_rate_title";

    /**
     * Process GET requests.
     * Return PRODUCT tax classes with rates.
     *
     * @return type
     * @throws Exception
     * @deprecated after 1.7.2
     */
    public function get() {

        $taxes = array();
        $proc  = array();

        $collectionRule = Mage::getModel('tax/calculation_rule')
            ->getCollection()
            ->addCustomerTaxClassesToResult()
            ->addProductTaxClassesToResult()
            ->addRatesToResult();

        if($collectionRule->getSize()) {

            foreach ($collectionRule as $taxRule) {
                $taxClasses = $taxRule->getProductTaxClasses();
                if( !empty($taxClasses) ) {
                    foreach($taxClasses as $_taxC) {
                        foreach($taxRule->getTaxRates() as $_rate) {

                            if (in_array((((int)$_rate).$_taxC), $proc)) {
                                continue;
                            }

                            array_push($proc, (((int)$_rate).$_taxC));

                            $rt = Mage::getModel('tax/calculation_rate')->load($_rate);

                            if($rt->getId()) {

                                if (!$rt->hasTaxPostcode()) {
                                    $rt->setTaxPostcode('*');
                                }

                                $_tax = array(
                                                'country_id' => $rt->getTaxCountryId(),
                                                'region_id'  => $rt->getTaxRegionId(),
                                                'tax_class'  => (int)$_taxC,
                                                'rate'       => (float)$rt->getRate(),
                                                'postcode'   => $rt->getTaxPostcode(),
                                                'code'       => $rt->getCode(),
                                             );

                                $taxes []= $_tax;
                            }

                        }
                    }
                }
            }

        }

        return $taxes;

    }

}