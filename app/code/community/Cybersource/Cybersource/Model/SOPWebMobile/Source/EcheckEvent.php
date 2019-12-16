<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Source_EcheckEvent
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'Payment',
                'label' => 'Payment'
            ),
            array(
                'value' => 'Refund',
                'label' => 'Refund'
            ),
            array(
                'value' => 'Completed',
                'label' => 'Completed'
            ),
            array(
                'value' => 'Correction',
                'label' => 'Correction'
            ),
            array(
                'value' => 'Declined',
                'label' => 'Declined'
            ),
            array(
                'value' => 'Error',
                'label' => 'Error'
            ),
            array(
                'value' => 'Failed',
                'label' => 'Failed'
            ),
            array(
                'value' => 'Final NSF',
                'label' => 'Final NSF'
            ),
            array(
                'value' => 'First NSF',
                'label' => 'First NSF'
            ),
            array(
                'value' => 'NSF',
                'label' => 'NSF'
            ),
            array(
                'value' => 'Second NSF',
                'label' => 'Second NSF'
            ),
            array(
                'value' => 'Stop Payment',
                'label' => 'Stop Payment'
            ),
            array(
                'value' => 'Void',
                'label' => 'Void'
            ),
            array(
                'value' => 'BATCH_ERROR',
                'label' => 'BATCH_ERROR'
            ),
            array(
                'value' => 'BATCH_RESET',
                'label' => 'BATCH_RESET'
            ),
            array(
                'value' => 'CANCELLED',
                'label' => 'CANCELLED'
            ),
            array(
                'value' => 'CANCELED_REVERS',
                'label' => 'CANCELED_REVERS'
            ),
            array(
                'value' => 'FAILED',
                'label' => 'FAILED'
            ),
            array(
                'value' => 'FUNDED',
                'label' => 'FUNDED'
            ),
            array(
                'value' => 'MIPS',
                'label' => 'MIPS'
            ),
            array(
                'value' => 'PAYMENT',
                'label' => 'PAYMENT'
            ),
            array(
                'value' => 'PENDING',
                'label' => 'PENDING'
            ),
            array(
                'value' => 'REFUNDED',
                'label' => 'REFUNDED'
            ),
            array(
                'value' => 'REVERSAL',
                'label' => 'REVERSAL'
            ),
            array(
                'value' => 'REVERSING',
                'label' => 'REVERSING'
            ),
            array(
                'value' => 'TRANSMITTED',
                'label' => 'TRANSMITTED'
            ),
            array(
                'value' => 'VOIDED',
                'label' => 'VOIDED'
            )
        );
    }
}
