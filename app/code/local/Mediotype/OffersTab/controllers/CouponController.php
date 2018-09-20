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
                $offer = $this->getOfferByCode($code);
                $redemptionMessage = $offer->getRedemptionMessage();

                // Call custom CMS block that holds the default 'Redemption Message'
                $defaultMessage = $this->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('offers-tab-redemption-message')
                ->toHtml();

                // Replace [promocode] token in CMS block with the submitted promo code
                $defaultMessage = str_replace("[promocode]", $code, $defaultMessage);

                if (!empty ($redemptionMessage) )
                {
                   $successMessage = $redemptionMessage;

                } else {
                   $successMessage = $defaultMessage;
                }

                Mage::dispatchEvent('mediotype_coupon_apply', array('code' => $code));

                $coreSession->addSuccess($successMessage);

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
     * Attempt to load an offer by its assigned code. Returns first match.
     *
     * @param $code
     * @return Mediotype_OffersTab_Model_Offer
     */
    protected function getOfferByCode($code)
    {
        $offers = Mage::getResourceModel('mediotype_offerstab/offer_collection')
            ->setPageSize(1)
            ->addFieldToFilter('landing_page_url', array('like' => "%couponCode={$code}%"));

        return $offers->getFirstItem();
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
        if (!($isApproved = $this->approveRequest())) {
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
