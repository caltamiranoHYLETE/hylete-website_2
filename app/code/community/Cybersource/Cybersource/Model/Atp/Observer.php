<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Atp_Observer
{
    public function observeControllerActionPostdispatch(Varien_Event_Observer $observer)
    {
        if (! $atpRedirect = Mage::registry('atp_redirect')) {
            return;
        }

        if (Mage::registry('atp_customer_prevent_login')) {
            $session = Mage::getSingleton('customer/session');
            $session->setId(null);
            $session->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        /** @var $action \Mage_Core_Controller_Varien_Action */
        $action = $observer->getControllerAction();
        $action->setRedirectWithCookieCheck($atpRedirect);

        Mage::unregister('atp_redirect');
        Mage::unregister('atp_customer_prevent_login');
    }

    /**
     * Observe a login attempt
     *
     * @param Varien_Event_Observer $observer
     */

    public function observeLogin(Varien_Event_Observer $observer)
    {
        $this->trigger(Cybersource_Cybersource_Helper_Atp_Api::TYPE_LOGIN, $observer);
    }

    /**
     * Observe a create event
     *
     * @param Varien_Event_Observer $observer
     */

    public function observeCreate(Varien_Event_Observer $observer)
    {
        if (($model = $observer->getCustomer()) !== null && !$model->getId()) {
            $this->trigger(Cybersource_Cybersource_Helper_Atp_Api::TYPE_CREATION, $observer);
        }
    }

    /**
     * Observe an update event
     *
     * @param Varien_Event_Observer $observer
     */

    public function observeUpdate(Varien_Event_Observer $observer)
    {
        if (($model = $observer->getCustomer()) !== null && $model->getId()) {
            $this->trigger(Cybersource_Cybersource_Helper_Atp_Api::TYPE_UPDATE, $observer);
        }
    }

    /**
     * Observes when a login is rejected.  Forwards to the reject controller
     *
     * @param Varien_Event_Observer $observer
     */
    public function observeLoginRejected(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled() && $this->_getHelper()->isInternalMechanismEnabled()) {
            Mage::register('atp_customer_prevent_login', true, true);
            $this->_redirect('cybersource/atp/reject', $observer);
        }
    }

    /**
     * Observes when a crud action is rejected.  Forwards to the reject controller
     *
     * @param Varien_Event_Observer $observer
     */
    public function observeUpdateRejected(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled() && $this->_getHelper()->isInternalMechanismEnabled()) {
            if ($subject = $this->getObserverSubject($observer)) {
                $this->preventSave($subject);
            }
            $this->_redirect('cybersource/atp/reject', $observer);
        }
    }

    private function trigger($type, Varien_Event_Observer $observer)
    {
        if (Mage::helper(Cybersource_Cybersource_Helper_Atp_Data::GROUPNAME)->isEnabled()) {
            $result = $this->_getApiHelper()->validate($type, $this->getReferenceCode($observer), $observer);
            $base = 'cybersource_atp';
            $eventName = $base;
            Mage::dispatchEvent($eventName, $this->_buildData($observer, $result));  // e.g. cybersource_atp

            $eventName = $base . '_' . strtolower($result->getDecision());
            Mage::dispatchEvent($eventName, $this->_buildData($observer, $result));  // e.g. cybersource_atp_challenge

            $eventName = $base . '_' . strtolower($type);
            Mage::dispatchEvent($eventName, $this->_buildData($observer, $result));  // e.g. cybersource_atp_login

            $eventName .= '_' . strtolower($result->getDecision());
            Mage::dispatchEvent($eventName, $this->_buildData($observer, $result));  // e.g. cybersource_atp_login_challenge
        }
    }

    /**
     * Builds the data parameter for dispatching an event
     *
     * @param Varien_Event_Observer $observer
     * @param Cybersource_Cybersource_Model_Atp_Result $result
     * @return array
     */
    private function _buildData(Varien_Event_Observer $observer, Cybersource_Cybersource_Model_Atp_Result $result)
    {
        $data = $observer->toArray();

        if (isset($data['password'])) {
            unset($data['password']);
        }

        unset($data['event']);
        $data['result'] = $result;
        return $data;
    }

    private function getReferenceCode(Varien_Event_Observer $observer)
    {
        $base = $this->getObserverSubject($observer) ? $this->getObserverSubject($observer)->getId() : '';
        return uniqid($base . '-', true);
    }

    /**
     * Redirects to one of the challenge paths
     *
     * @param $path
     * @param $observer
     * @throws Mage_Core_Exception
     */
    private function _redirect($path, $observer)
    {
        Mage::register('atp_redirect', $path, true);
    }

    /**
     * @return Cybersource_Cybersource_Helper_Atp_Data
     */
    private function _getHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_Atp_Data::GROUPNAME);
    }

    /**
     * @return Cybersource_Cybersource_Helper_Atp_Api
     */
    private function _getApiHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_Atp_Data::GROUPNAME . '/api');
    }

    private function getObserverSubject(Varien_Event_Observer $observer)
    {
        if ($observer->hasModel()) {
            $model = $observer->getModel();
            if ($model instanceof Mage_Core_Model_Abstract) {
                return $model;
            }
        } else if ($observer->hasCustomer()) {
            $model = $observer->getCustomer();
            if ($model instanceof Mage_Core_Model_Abstract) {
                return $model;
            }
        } else if ($observer->hasDataObject()) {
            $model = $observer->getDataObject();
            if ($model instanceof Mage_Core_Model_Abstract) {
                return $model;
            }
        }

        return false;
    }

    private function preventSave(&$subject)
    {
        $reflectionClass = new ReflectionClass($subject);
        $reflectionProperty = $reflectionClass->getProperty('_dataSaveAllowed');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($subject, false);
    }
}
