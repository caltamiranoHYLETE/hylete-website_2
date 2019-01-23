<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

class Mediotype_OffersTab_Model_Validation
{
    /**
     * Verify that the coupon code is valid.
     *
     * @param $code
     * @return boolean
     * @throws Mage_Core_Exception
     */
    public function validate($code)
    {
        if (!$this->approveRequest()) {
            throw new Mage_Core_Exception('Too many requests. Please try again later.');
        }
        try {
            $coupon = Mage::getModel('salesrule/coupon')->loadByCode($code);
            if (!$coupon->getId()) {
                return false;
            }
        } catch (Exception $error) {
            throw new Mage_Core_Exception('Unable to validate code.');
        }

        return true;
    }

    /**
     * Approve the current request.
     *
     * @return boolean
     */
    private function approveRequest()
    {
        try {
            return Mage::getSingleton('mediotype_offerstab/abuse')->approve();
        } catch (Exception $e) {
            return false;
        }
    }
}
