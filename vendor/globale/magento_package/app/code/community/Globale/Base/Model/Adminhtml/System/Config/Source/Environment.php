<?php

class Globale_Base_Model_Adminhtml_System_Config_Source_Environment {
    
    /**
     * Create Option array of Stores with Empty first option - for Non selected state
     * @return array
     */
    public function toOptionArray(){

        return array(
            array(
                "value" => Globale_Base_Model_Adminhtml_Environment::GLOBALE_QA,
                "label" => Globale_Base_Model_Adminhtml_Environment::QA_LABEL
            ),
            array(
                "value" => Globale_Base_Model_Adminhtml_Environment::GLOBALE_STAGING,
                "label" => Globale_Base_Model_Adminhtml_Environment::STAGE_LABEL
            ),
            array(
                "value" => Globale_Base_Model_Adminhtml_Environment::GLOBALE_PRODUCTION,
                "label" => Globale_Base_Model_Adminhtml_Environment::PROD_LABEL
            )
        );
    }
}