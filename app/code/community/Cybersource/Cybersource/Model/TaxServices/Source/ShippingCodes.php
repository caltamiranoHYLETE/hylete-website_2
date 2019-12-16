<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Source_ShippingCodes
{
    private $shippingCodes;

    /**
     * Retrieves a list of tax classes that have a Cybersource tax code value specified
     *
     * @return mixed
     */
    public function toOptionArray()
    {
        if (!$this->shippingCodes) {

            $taxClasses = $this->getTaxClasses();

            foreach ($taxClasses as $taxClass) {
                /** @var $taxClass Mage_Tax_Model_Class */

                $this->shippingCodes[] = array(
                    'value' => $taxClass->getCsTaxCode(),
                    'label' => $taxClass->getClassName()
                );
            }
        }
        return $this->shippingCodes;
    }

    /**
     * Retrieves the collection for finding all tax classes with a Cybersource tax code
     *
     * @return Mage_Tax_Model_Resource_Class_Collection|object
     */
    private function getTaxClasses()
    {
        $classes = Mage::getModel('tax/class')->getCollection();
        /** @var $classes Mage_Tax_Model_Resource_Class_Collection */
        $classes->addFieldToFilter('cs_tax_code', array('neq' => ''));
        $classes->setClassTypeFilter(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);

        return $classes;
    }
}
