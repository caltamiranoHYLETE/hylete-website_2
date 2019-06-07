<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */

require_once 'TM/FireCheckout/controllers/IndexController.php';

class Hylete_Firecheckout_controllers_IndexController extends TM_FireCheckout_IndexController
{
    /**
     * Index Action
     *
     * @return void|Zend_Controller_Response_Abstract
     */
    public function indexAction()
    {
        $session = Mage::getSingleton('customer/session');
        $data = $this->getRequest()->getPost('billing', array());
        if (isset($data['register_account']) && $data['register_account'] !== 0 && !$session->isLoggedIn()) {
            try {
                $customer = $this->_getCustomer();
                $customer->setFirstname(isset($data['firstname']) ? $data['firstname'] : null)
                    ->setLastname(isset($data['lastname']) ? $data['lastname'] : null)
                    ->setEmail(isset($data['email']) ? $data['email'] : null)
                    ->setPassword(isset($data['customer_password']) ? $data['customer_password'] : null)
                    ->setForceConfirmed(true);

                $newCustomer = $customer->save();

                $session->setCustomer($newCustomer);
                $session->save();

                $quote = $this->getOnepage()->getQuote();
                $quote->setCustomer($newCustomer);

                $storeId = $customer->getStore()->getId();
                $customer->sendNewAccountEmail('registered', '', $storeId);

                $this->saveBillingAction();
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
                $this->_redirectReferer();

                return;
            } catch (Exception $e) {
                $this->_redirectReferer();
                Mage::log($e->getMessage());

                return;
            }
        }
        parent::indexAction();
    }

    /**
     * Retrive Customer Model
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return $this->getOnepage()->getQuote()->getCustomer();
    }
}
