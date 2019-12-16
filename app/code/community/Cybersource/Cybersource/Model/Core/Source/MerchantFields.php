<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Core_Source_MerchantFields
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $fields = array(
            '' => '-- None --',
            'customer_id' => 'Customer ID',
            'customer_group_id' => 'Customer Group ID',
            'customer_dob' => 'Customer Birthday',
            'customer_note' => 'Customer Note',
            'customer_gender' => 'Customer Gender',
            'customer_taxvat' => 'Customer VAT',
            'customer_tax_class_id' => 'Customer Tax Class ID',
            'coupon_code' => 'Coupon Code',
            'is_virtual' => 'Virtual Quote',
            'remote_ip' => 'Remote IP',
            'store_id' => 'Store ID',
            '^isGuest:' => 'Customer is Guest',
            '^shippingMethod:' => 'Shipping Method',
            '^raw:2100' => 'Constant Value (2100)'
        );

        foreach ($fields as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }

        return $options;
    }
}
