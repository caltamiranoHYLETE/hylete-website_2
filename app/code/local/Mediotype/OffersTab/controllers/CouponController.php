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
        $escapedCode = Mage::helper('core')->escapeHtml($code);

        try {
            if (Mage::getSingleton('mediotype_offerstab/validation')->validate($code)) {
                $checkoutSession->setData('automaticCouponCode', $code);
                $offer = Mage::getResourceModel('mediotype_offerstab/offer_collection')->getOfferByCode($code);
                $redemptionMessage = $offer->getRedemptionMessage();

                // Call custom CMS block that holds the default 'Redemption Message'
                $defaultMessage = $this->getLayout()
                    ->createBlock('cms/block')
                    ->setBlockId('offers-tab-redemption-message')
                    ->toHtml();

                // Replace the [promocode] token in CMS block with the applied promotion code
                $defaultMessage = str_replace("[promocode]", $escapedCode, $defaultMessage);

                if (!empty ($redemptionMessage)) {
                    $successMessage = $redemptionMessage;

                } else {
                    $successMessage = $defaultMessage;
                }

                Mage::dispatchEvent('mediotype_coupon_apply', array('code' => $code));

                $coreSession->addSuccess($successMessage);

            } else {
                throw new Mage_Core_Exception(
                    $this->__(sprintf('Unable to apply promo code `%s`.', $escapedCode))
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
            $coreSession->addError($this->__('Unable to apply promo code `%s`.', $escapedCode));

            $redirectUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        }

        $this->_redirectUrl($redirectUrl);
    }

}
