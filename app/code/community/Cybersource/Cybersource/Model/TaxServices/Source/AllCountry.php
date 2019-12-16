<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Source_AllCountry
{
    private $countries;

    /**
     * Retrieves a list of all the countries with the United States and Canada prepended to the list.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->countries) {

            $countryList = array();
            $prepend = array();

            $countries = $this->getCountries();

            // Iterate over all of the regions and extract the US and Canada so they can be placed at the top of the list.
            foreach ($countries as $country) {
                /** @var $country Mage_Directory_Model_Country */

                $id = $country->getCountryId();
                if (in_array($id, array('US', 'CA'))) {
                    $prepend[] = array(
                        'value' => $id,
                        'label' => $country->getName()
                    );
                    continue;
                }
                $countryList[] = array(
                    'value' => $id,
                    'label' => $country->getName()
                );
            }

            Mage::helper('core/string')->ksortMultibyte($prepend);
            $prepend = array_reverse($prepend);

            $this->countries = $prepend;
            foreach ($countryList as $country) {
                $this->countries[] = array(
                    'value' => $country['value'],
                    'label' => $country['label']
                );
            }
        }
        return $this->countries;
    }

    /**
     * Retrieves a key => value array in name => getResourceCollection format
     *
     * @return array
     */
    private function getCountries()
    {
        $country = Mage::getModel('directory/country');
        /** @var $country Mage_Directory_Model_Country */
        $collection = $country->getResourceCollection();
        /** @var $collection Mage_Directory_Model_Resource_Country_Collection */
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
