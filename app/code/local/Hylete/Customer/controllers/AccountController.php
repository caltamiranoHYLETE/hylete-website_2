<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer account controller
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once Mage::getModuleDir('controllers', 'Mage_Customer') . DS . 'AccountController.php';
class Hylete_Customer_AccountController extends Mage_Customer_AccountController
{
    /**
     * Login post action
     */
    public function loginPostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');

			//verify the reCaptcha response
			$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
			$recaptcha_secret = '6LfKHLIUAAAAAK2lSpS5BJ0_lwlCgMPdl0_LZ1TL';
			$recaptcha_response = $this->getRequest()->getParam('recaptcha_response');

			$recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
			$recaptcha = json_decode($recaptcha);
			if($recaptcha->success == true){
				if ($recaptcha->score <= 0.2) {
					$session->addError($this->__('Sorry, your login and password did not pass the reCAPTCHA test. Please try again.'));
				} else{
					if (!empty($login['username']) && !empty($login['password'])) {
						try {
							$session->login($login['username'], $login['password']);
							if ($session->getCustomer()->getIsJustConfirmed()) {
								$this->_welcomeCustomer($session->getCustomer(), true);
							}
						} catch (Mage_Core_Exception $e) {
							switch ($e->getCode()) {
								case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
									$value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
									$message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
									break;
								case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
									$message = $e->getMessage();
									break;
								default:
									$message = $e->getMessage();
							}
							$session->addError($message);
							$session->setUsername($login['username']);
						} catch (Exception $e) {
							// Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
						}
					} else {
						$session->addError($this->__('Login and password are required.'));
					}
				}
			} else {
				//$session->addError($this->__('Sorry, your login and password did not pass the reCAPTCHA test. Please try again.'));
				if (!empty($login['username']) && !empty($login['password'])) {
					try {
						$session->login($login['username'], $login['password']);
						if ($session->getCustomer()->getIsJustConfirmed()) {
							$this->_welcomeCustomer($session->getCustomer(), true);
						}
					} catch (Mage_Core_Exception $e) {
						switch ($e->getCode()) {
							case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
								$value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
								$message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
								break;
							case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
								$message = $e->getMessage();
								break;
							default:
								$message = $e->getMessage();
						}
						$session->addError($message);
						$session->setUsername($login['username']);
					} catch (Exception $e) {
						// Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
					}
				} else {
					$session->addError($this->__('Login and password are required.'));
				}
			}
        }

        $this->_loginPostRedirect();
    }

    /**
     * Validate customer data and return errors if they are
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return array|string
     */
    protected function _getCustomerErrors($customer)
    {
        $errors = array();
        $request = $this->getRequest();
        if ($request->getPost('create_address')) {
            $errors = $this->_getErrorsOnCustomerAddress($customer);
        }

		$captchaErrors = $this->validateReCaptcha();
		$errors = array_merge($captchaErrors, $errors);

        $customerForm = $this->_getCustomerForm($customer);
        $customerData = $customerForm->extractData($request);
        $customerErrors = $customerForm->validateData($customerData);
        if ($customerErrors !== true) {
            $errors = array_merge($customerErrors, $errors);
        } else {
            $customerForm->compactData($customerData);
            $customer->setPassword($request->getPost('password'));
            $customer->setPasswordConfirmation($request->getPost('confirmation'));
            $customerErrors = $customer->validate();
            if (is_array($customerErrors)) {
                $errors = array_merge($customerErrors, $errors);
            }
        }
        return $errors;
    }

	protected function validateReCaptcha()
	{
		$errors = array();

		$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
		$recaptcha_secret = '6LfKHLIUAAAAAK2lSpS5BJ0_lwlCgMPdl0_LZ1TL';
		$recaptcha_response = $this->getRequest()->getParam('recaptcha_response');

		$recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
		$recaptcha = json_decode($recaptcha);
		if($recaptcha->success == true){
			if ($recaptcha->score <= 0.2) {
				$errors[] = Mage::helper('customer')->__('Sorry, your login and password did not pass the reCAPTCHA test. Please try again.');
			}
		} else {
			//Going to let errors go through at this time
		}

		return $errors;
	}

}
