<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/**
 * Helper for Cminds coupons
 */
class Hylete_Coupons_Helper_Cminds_Data extends Cminds_Coupon_Helper_Data
{
    /**
     * Retrieve error message
     *
     * @param $errorType
     * @param null $rule
     * @return string
     */
    public function getErrorMessage($errorType, $rule = null)
    {
        $result = parent::getErrorMessage($errorType, $rule);
        return html_entity_decode($result);
    }
}
