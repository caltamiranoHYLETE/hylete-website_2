<?php
class Desitex_Checkoutnewsletter_Model_Observer
{
    public function setCustomerIsSubscribed($observer){
		if( Icommerce_Default::getMagentoVersion()<1400 ){
            $this->setCustomerIsSubscribed13($observer);
		} else {
            $this->setCustomerIsSubscribed14($observer);
		}
    }

    public function setCustomerIsSubscribed13($observer)
    {
        if ((bool) Mage::getSingleton('checkout/session')->getCustomerIsSubscribed()){
            $quote = $observer->getEvent()->getQuote();
            $customer = $quote->getCustomer();
            switch ($quote->getCheckoutMethod()){
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
                    $customer->setIsSubscribed(1);
                    break;

                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_CUSTOMER:
                    $session = Mage::getSingleton('core/session');
                    try {
                        $status = Mage::getModel('newsletter/subscriber')->subscribe($quote->getBillingAddress()->getEmail());
                        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE){
                            $session->addSuccess(Mage::helper('checkoutnewsletter')->__('Confirmation request has been sent regarding your newsletter subscription'));
                        }
                    }
                    catch (Mage_Core_Exception $e) {
                        $session->addException($e, Mage::helper('checkoutnewsletter')->__('There was a problem with the newsletter subscription: %s', $e->getMessage()));
                    }
                    catch (Exception $e) {
                        $session->addException($e, Mage::helper('checkoutnewsletter')->__('There was a problem with the newsletter subscription'));
                    }
                    break;
            }
            Mage::getSingleton('checkout/session')->setCustomerIsSubscribed(0);
        }
    }

    public function setCustomerIsSubscribed14($observer)
    {
        if ((bool) Mage::getSingleton('checkout/session')->getCustomerIsSubscribed()){
            $quote = $observer->getEvent()->getQuote();
            $customer = $quote->getCustomer();
            switch ($quote->getCheckoutMethod(true)){
                case Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER:
                    $customer->setIsSubscribed(1);
                    break;

                case Mage_Checkout_Model_Type_Onepage::METHOD_GUEST:
                case Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER:
                    $session = Mage::getSingleton('core/session');
                    try {
                        $status = Mage::getModel('newsletter/subscriber')->subscribe($quote->getBillingAddress()->getEmail());
                        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE){
                            $session->addSuccess(Mage::helper('checkoutnewsletter')->__('Confirmation request has been sent regarding your newsletter subscription'));
                        }
                    }
                    catch (Mage_Core_Exception $e) {
                        $session->addException($e, Mage::helper('checkoutnewsletter')->__('There was a problem with the newsletter subscription: %s', $e->getMessage()));
                    }
                    catch (Exception $e) {
                        $session->addException($e, Mage::helper('checkoutnewsletter')->__('There was a problem with the newsletter subscription'));
                    }
                    break;
            }
            Mage::getSingleton('checkout/session')->setCustomerIsSubscribed(0);
        }
    }
}