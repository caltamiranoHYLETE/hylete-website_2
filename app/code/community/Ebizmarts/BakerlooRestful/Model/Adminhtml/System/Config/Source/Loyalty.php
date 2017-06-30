<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_Loyalty {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        $result = array(
            array('value' => '', 'label' => '')
        );

        $integrations = Mage::getConfig()->getNode(Ebizmarts_BakerlooLoyalty_Helper_Data::LOYALTY_INTEGRATIONS_CONFIG_PATH)->asArray();

        $helper = Mage::helper('bakerloo_restful/integrations');

        foreach($integrations as $moduleCodename => $moduleLabel) {

            if($helper->moduleInstalledAndEnabled($moduleCodename))
                array_push($result, array('value' => $moduleCodename, 'label' => $moduleLabel));

        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {

        $result = array();

        foreach($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }

}