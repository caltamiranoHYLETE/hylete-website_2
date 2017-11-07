<?php

class Globale_Base_Model_Adminhtml_System_Config_Source_Allowredirects {

    /**
     * Create options for the system.xml Allow redirects
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array("label" => "No",  "value" => 0),
            array("label" => "Server side with HTTP Code 301", "value" => 1),
            array("label" => "Server side with HTTP Code 302", "value" => 2),
			array("label" => "Show GE switcher in blocking mode", "value" => 3)
        );
        return $options;
    }
}