<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Source_TaxCountry
{
    private $countries;

    /**
     * Retrieves a list of countries that Cybersource can provide tax services for.
     *
     * @return mixed
     */
    public function toOptionArray()
    {
        if (!$this->countries) {

            $countries = $this->getCountries();

            // Iterate over all of the regions and extract the US and Canada so they can be placed at the top of the list.
            foreach ($countries as $country) {
                /** @var $country Mage_Directory_Model_Country */

                $this->countries[] = array(
                    'value' => $country->getCountryId(),
                    'label' => $country->getName()
                );
            }
        }
        return $this->countries;
    }

    /**
     * Retrieves the countries US and CA
     *
     * @return array
     */
    private function getCountries()
    {
        $country = Mage::getModel('directory/country');
        /** @var $country Mage_Directory_Model_Country */
        $collection = $country->getResourceCollection();
        /** @var $collection Mage_Directory_Model_Resource_Country_Collection */
        $collection->addCountryCodeFilter(array('US', 'CA'));
        $collection->loadByStore();
        $countries = array();
        foreach ($collection as $country) {
            /** @var $country Mage_Directory_Model_Country */
            $countries[$country->getName()] = $country;
        }
        // Sort based on the name of the country, not the ID
        Mage::helper('core/string')->ksortMultibyte($countries);
        return $countries;
    }
}
