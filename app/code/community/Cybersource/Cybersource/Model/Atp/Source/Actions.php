<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Atp_Source_Actions
{
    /**
     * Provides the possible response options from the Cybersource API
     *
     * @return array
     */
    public function toOptionArray()
    {

        $options = array(
            Cybersource_Cybersource_Model_Atp_Result::STATUS_ACCEPT,
            Cybersource_Cybersource_Model_Atp_Result::STATUS_CHALLENGE,
            Cybersource_Cybersource_Model_Atp_Result::STATUS_REJECT,
        );
        $result = array();

        foreach ($options as $option) {

            $result[] = array(
                'label' => $option,
                'value' => $option
            );
        }
        return $result;
    }
}
