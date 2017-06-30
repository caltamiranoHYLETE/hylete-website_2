<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_Pagesize {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => 1),
            array('value' => 5, 'label' => 5),
            array('value' => 10, 'label' => 10),
            array('value' => 20, 'label' => 20),
            array('value' => 50, 'label' => 50),
            array('value' => 100, 'label' => 100),
            array('value' => 200, 'label' => 200),
            array('value' => 400, 'label' => 400),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return array();
    }

}