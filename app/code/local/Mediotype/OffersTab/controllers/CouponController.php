<?php

/**
 * Class Mediotype_OffersTab_CouponController
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_CouponController extends Mage_Core_Controller_Front_Action
{
    /**
     * Responsible for reading the parameter `coupon_code` and storing it in the session,
     * and deciding where to redirect to afterwards.
     *
     * @return void
     */
    public function applyAction()
    {
        $code = $this->getRequest()->getParam('couponCode');
        $redirectUrl = $this->getRequest()->getParam('redirectUrl');
        $checkoutSession = Mage::getSingleton('checkout/session');
        $coreSession = Mage::getSingleton('core/session');

        try {
            if ($this->validate($code)) {
                $checkoutSession->setData('automaticCouponCode', $code);

                Mage::dispatchEvent('mediotype_coupon_apply', array('code' => $code));

                $coreSession->addSuccess(
                    $this->__(sprintf(
                        'Promo code `%s` was added to cart and will automatically apply to your cart during checkout.',
                        $code
                    ))
                );
            } else {
                throw new Mage_Core_Exception(
                    $this->__(sprintf('Unable to apply promo code `%s`.', $code))
                );
            }

            if (!$redirectUrl) {
                $this->_redirectReferer();

                return;
            }
        } catch (Mage_Core_Exception $error) {
            $coreSession->addError($error->getMessage());

            $redirectUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        } catch (Exception $error) {
            $coreSession->addError($this->__('Unable to apply promo code `%s`.', $code));

            $redirectUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        }

        $this->_redirectUrl($redirectUrl);
    }

    /**
     * Approve the current request.
     *
     * @return boolean
     * @throws Exception
     */
    protected function approveRequest()
    {
        return Mage::getSingleton('mediotype_offerstab/abuse')->approve();
    }

    /**
     * Verify that the coupon code is valid.
     *
     * @param $code
     * @return boolean
     * @throws Mage_Core_Exception
     */
    protected function validate($code)
    {
        try {
            $isApproved = $this->approveRequest();
        } catch (Exception $error) {
            throw new Mage_Core_Exception('Too many requests. Please try again later.');
        }

        try {
            $isValid = Mage::getResourceModel('salesrule/rule_collection')
                // @todo evaluate usage - should customer group filter should be enforced now or at time of activation?
                //->addWebsiteGroupDateFilter(
                //    Mage::app()->getStore()->getWebsiteId(),
                //    Mage::getSingleton('customer/session')->getCustomerGroupId(),
                //    null
                //)
                ->addFieldToFilter('code', $code)
                ->getSize() > 0;
        } catch (Exception $error) {
            throw new Mage_Core_Exception('Unable to validate code.');
        }

        return $isApproved && $isValid;
    }
}
