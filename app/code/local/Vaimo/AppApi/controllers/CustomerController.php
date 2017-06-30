<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 * @author      Tobias Åström
 */

class Vaimo_AppApi_CustomerController extends Vaimo_AppApi_Controller_Customer
{
    public function preDispatch()
    {
        if ($this->_request->getActionName() == 'create' || $this->_request->getActionName() == 'list') {
            $this->_skipCustomerAuthentication();
        }

        $result = parent::preDispatch();

        return $result;
    }

    public function createAction()
    {
        $success = false;
        $result = array('status' => 'error');

        if ($this->_isRequestPost($result)) {
            $websiteId = $this->_request->getParam('website', 0);
            $firstname = $this->_request->getParam('firstname', null);
            $lastname = $this->_request->getParam('lastname', null);
            $email = $this->_request->getParam('email', null);

            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($email);

            if (!$customer->getId()) {
                $customer->setFirstname($firstname)
                    ->setLastname($lastname)
                    ->setEmail($email)
                    ->setPassword($customer->generatePassword());
            }

            try {
                Mage::register('isSecureArea', true);

                $customer->save();
                $customer->setConfirmation(null);
                $customer->save();
                $customer->sendNewAccountEmail();

                $apiResult = Mage::getModel('appapi/customer')->listDetails(
                    $customer->getId(),
                    Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_SMART
                );

                Mage::unregister('isSecureArea');

                $result['status'] = 'success';
                $result['result'] = $apiResult;
            }
            catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function authAction()
    {
        $this->_response
            ->setHttpResponseCode(parent::HTTP_OK)
            ->setBody('Success.')
            ->sendResponse();
        exit;
    }

    public function testAction()
    {
        $this->_response
            ->setHttpResponseCode(parent::HTTP_OK)
            ->setBody('Token Valid.')
            ->sendResponse();
        exit;
    }

    public function detailsAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestGet($result) && $customerId) {
            $detailLevel = $this->_request->getParam('detail', Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT);

            try {
                Mage::register('isSecureArea', true);

                $apiResult = Mage::getModel('appapi/customer')->listDetails(
                    $customerId,
                    $detailLevel
                );

                Mage::unregister('isSecureArea');

                $success = true;
                $result['status'] = 'success';
                $result['result'] = $apiResult;
            }
            catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function updateAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestPost($result) && $customerId) {
            $firstname = $this->_request->getParam('firstname', null);
            $lastname = $this->_request->getParam('lastname', null);
            $email = $this->_request->getParam('email', null);

            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);

            if ($customer) {
                $customer->setFirstname($firstname)
                    ->setLastname($lastname)
                    ->setEmail($email);
            }

            try {
                Mage::register('isSecureArea', true);

                $customer->save();

                $apiResult = Mage::getModel('appapi/customer')->listDetails(
                    $customerId,
                    Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_SMART
                );

                Mage::unregister('isSecureArea');

                $result['status'] = 'success';
                $result['result'] = $apiResult;
            }
            catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function createAddressAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestPost($result) && $customerId) {
            $websiteId = $this->_request->getParam('website', 0);
            $telephone = $this->_request->getParam('telephone', null);
            $street = $this->_request->getParam('street', null);
            $postcode = $this->_request->getParam('postcode', null);
            $city = $this->_request->getParam('city', null);
            $countryId = $this->_request->getParam('country_id', Mage::getStoreConfig('general/country/default'));
            $isBilling = $this->_request->getParam('is_billing', 0);

            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);

            $address = Mage::getModel('customer/address');
            $address->setWebsiteId($websiteId);

            if ($customer && $address) {
                $address->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setCustomerId($customer->getId())
                    ->setTelephone($telephone)
                    ->setStreet($street)
                    ->setPostcode($postcode)
                    ->setCity($city)
                    ->setCountryId($countryId)
                    ->setSaveInAddressBook('1');

                if ($isBilling) {
                    $address->setIsDefaultBilling('1');
                } else {
                    $address->setIsDefaultShipping('1');
                }

                try {
                    Mage::register('isSecureArea', true);

                    $address->save();

                    $apiResult = Mage::getModel('appapi/customer')->listAddressDetails(
                        $address->getId(),
                        Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT
                    );

                    Mage::unregister('isSecureArea');

                    $result['status'] = 'success';
                    $result['result'] = $apiResult;
                }
                catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function updateAddressAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestPost($result) && $customerId) {
            $entityId = $this->_request->getParam('entity_id', null);
            $telephone = $this->_request->getParam('telephone', null);
            $street = $this->_request->getParam('street', null);
            $postcode = $this->_request->getParam('postcode', null);
            $city = $this->_request->getParam('city', null);

            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);

            $address = Mage::getModel('customer/address');
            $address->load($entityId);

            if ($customer && $address && $customer->getId() == $address->getParentId()) {
                $address->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setTelephone($telephone)
                    ->setStreet($street)
                    ->setPostcode($postcode)
                    ->setCity($city)
                    ->setSaveInAddressBook('1');

                try {
                    Mage::register('isSecureArea', true);

                    $address->save();

                    $apiResult = Mage::getModel('appapi/customer')->listAddressDetails(
                        $entityId,
                        Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT
                    );

                    Mage::unregister('isSecureArea');

                    $result['status'] = 'success';
                    $result['result'] = $apiResult;
                }
                catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function deleteAddressAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestPost($result) && $customerId) {
            $entityId = $this->_request->getParam('entity_id', null);

            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);

            $address = Mage::getModel('customer/address');
            $address->load($entityId);

            if ($customer && $address && $customer->getId() == $address->getParentId()) {
                try {
                    Mage::register('isSecureArea', true);

                    $address->delete();

                    Mage::unregister('isSecureArea');

                    $result['status'] = 'success';
                } catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function subscribeAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestPost($result) && $customerId) {
            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);

            $subscription = Mage::getModel('newsletter/subscriber');

            if ($customer) {
                $subscription->loadByEmail($customer->getEmail());

                if ($subscription) {
                    $subscription->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                }
            }

            try {
                Mage::register('isSecureArea', true);

                $subscription->save();

                Mage::unregister('isSecureArea');

                $result['status'] = 'success';
            }
            catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function unsubscribeAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestPost($result) && $customerId) {
            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);

            $subscription = Mage::getModel('newsletter/subscriber');

            if ($customer) {
                $subscription->loadByEmail($customer->getEmail());

                if ($subscription) {
                    $subscription->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                }
            }

            try {
                Mage::register('isSecureArea', true);

                $subscription->save();

                Mage::unregister('isSecureArea');

                $result['status'] = 'success';
            }
            catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function wishlistAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestGet($result) && $customerId) {
            $detailLevel = $this->_request->getParam('detail', Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT);

            try {
                Mage::register('isSecureArea', true);

                $apiResult = Mage::getModel('appapi/customer')->listWishlistDetails(
                    $customerId,
                    $detailLevel
                );

                Mage::unregister('isSecureArea');

                $success = true;
                $result['status'] = 'success';
                $result['result'] = $apiResult;
            }
            catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function addToWishlistAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestPost($result) && $customerId) {
            $productId = $this->_request->getParam('product_id', null);

            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);

            $wishlist = Mage::getModel('wishlist/wishlist');
            $wishlist->loadByCustomer($customer, true);

            $product = Mage::getModel('catalog/product');
            $product->load($productId);

            if ($customer && $wishlist && $product) {
                $wishlist->addNewItem($product, new Varien_Object());

                try {
                    Mage::register('isSecureArea', true);

                    $wishlist->save();

                    $apiResult = Mage::getModel('appapi/customer')->listWishlistDetails(
                        $customerId,
                        Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT
                    );

                    Mage::unregister('isSecureArea');

                    $result['status'] = 'success';
                    $result['result'] = $apiResult;
                }
                catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function removeFromWishlistAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestPost($result) && $customerId) {
            $entityId = $this->_request->getParam('entity_id', null);

            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);

            $wishlist = Mage::getModel('wishlist/wishlist');
            $wishlist->loadByCustomer($customer, true);

            $item = Mage::getModel('wishlist/item');
            $item->load($entityId);

            if ($customer && $wishlist && $item && $wishlist->getId() == $item->getWishlistId()) {
                try {
                    Mage::register('isSecureArea', true);

                    $item->delete();

                    Mage::unregister('isSecureArea');

                    $result['status'] = 'success';
                }
                catch (Exception $e) {
                    $result['result'] = $e->getMessage();
                }
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function ordersAction()
    {
        $success = false;
        $result = array('status' => 'error');
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($this->_isRequestGet($result) && $customerId) {
            $detailLevel = $this->_request->getParam('detail', Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT);

            try {
                Mage::register('isSecureArea', true);

                $apiResult = Mage::getModel('appapi/customer')->listOrders(
                    $customerId,
                    $detailLevel
                );

                Mage::unregister('isSecureArea');

                $success = true;
                $result['status'] = 'success';
                $result['result'] = $apiResult;
            }
            catch (Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHttpResponseCode($success ? self::HTTP_OK : self::HTTP_INTERNAL_ERROR)
            ->setHeader('Content-type', 'application/json')
            ->setBody($jsonData)
            ->sendResponse();
        exit;
    }

    public function listAction()
    {
        $result = array();

        if ($this->_isRequestGet($result)) {

            $websiteId = $this->_request->getParam('website', 0);

            try {
                Mage::register('isSecureArea', true);

                $apiResult = Mage::getModel('appapi/customer')->getCustomerList($websiteId);

                Mage::unregister('isSecureArea');

                $result['status'] = 'success';
                $result['result'] = $apiResult;

            } catch (Exception $e) {
                $result['status'] = 'error';
                $result['result'] = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHeader('Content-type', 'application/json');
        $this->_response->setBody($jsonData);
    }
}