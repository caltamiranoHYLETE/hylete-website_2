<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_Length {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 3, 'label' => 3),
            array('value' => 4, 'label' => 4),
            array('value' => 5, 'label' => 5),
            array('value' => 6, 'label' => 6),
            array('value' => 7, 'label' => 7),
            array('value' => 8, 'label' => 8),
            array('value' => 9, 'label' => 9),
            array('value' => 10, 'label' => 10),
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