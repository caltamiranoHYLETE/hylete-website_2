<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Source_AllRegion
{
    private $regions;

    /**
     * Retrieves a list of regions for the united states and canada
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->regions) {
            $countries = $this->getCountryCollection();

            $regions = array();
            foreach ($countries as $country) {
                /** @var $country Mage_Directory_Model_Country */
                $regionCollection = $country->getRegionCollection();
                $regions[$country->getCountryId()] = array(
                    'label' => $country->getName(),
                    'value' => array()
                );
                $regionCollection->setOrder('region_id', Mage_Directory_Model_Resource_Region_Collection::SORT_ORDER_ASC);
                foreach ($regionCollection as $region) {
                    /** @var $region Mage_Directory_Model_Region */
                    $regions[$region->getCountryId()]['value'][] = array(
                        'value' => $region->getId(),
                        'label' => $region->getName()
                    );

                }
            }

            $this->regions = $regions;
        }
        return $this->regions;
    }

    /**
     * Retrieves a country object collection for the United States and Canada.
     *
     * @return Mage_Directory_Model_Resource_Country_Collection
     */
    private function getCountryCollection()
    {
        $collection = Mage::getModel('directory/country')->getResourceCollection();
        /** @var $collection Mage_Directory_Model_Resource_Country_Collection */
        $collection->addCountryCodeFilter(array('US', 'CA'));
        $collection->setOrder('country_id');
        return $collection;
    }
}
