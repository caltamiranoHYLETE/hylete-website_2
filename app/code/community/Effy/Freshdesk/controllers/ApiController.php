<?php
/**
 * Freshdesk oAuth API
 * 
 * @category Freshdesk
 * @package Freshdesk
 * @copyright Copyright (c) 2018
 * @author Utkarsh Mishra <utkarsh@effy.co.in>
 */
 
 class Effy_Freshdesk_ApiController extends Mage_Core_Controller_Front_Action {

	public function _authorise() {
		$token = $this->getRequest()->getHeader('Authorization');
		if(!$token) {
			Mage::log('Unable to extract authorization header from request.', null);
	    	$this->getResponse()
	        ->setBody(json_encode(array('success' => false, 'message' => 'Unable to extract authorization header from request')))
	        ->setHttpResponseCode(403)
	        ->setHeader('Content-type', 'application/json', true);
    		return false;
		}

		$apiToken = Mage::getModel('effy_freshdesk/token')
		->getCollection()
		->addFilter('status', true)
		->getData();
		if(count($apiToken) > 0) {
			$apiToken = $apiToken[0]['hash'];
			// If the API is enabled then check the token
            if(!$token) {
                $this->getResponse()
                    ->setBody(json_encode(array('success' => false, 'message' => 'No authorisation token provided')))
                    ->setHttpResponseCode(401)
                    ->setHeader('Content-type', 'application/json', true);

                Mage::log('No authorisation token provided.', null);

                return false;
            }

            if($token != $apiToken) {
                $this->getResponse()
                    ->setBody(json_encode(array('success' => false, 'message' => 'Not authorised')))
                    ->setHttpResponseCode(401)
                    ->setHeader('Content-type', 'application/json', true);

                Mage::log('Not authorised.', null);

                return false;
            }
		}
		return true;
	}

	public function getCustomerAction() {
		if(!$this->_authorise()) {
            return $this;
        }
        $email = $this->getRequest()->getParams()['email'];      
        $orders = array();
        try {
    		$customerCollection = Mage::getModel("customer/customer")
    		->getCollection()
    		->addFieldToFilter('email', array('eq' => array($email)))
    		->getItems();
    		$customer = reset($customerCollection);
            if($customer) {
                $customerId = $customer->getId();

                // Lifetime sales and average sales
                $currentCustomer = Mage::getModel('customer/customer')->load($customerId);
                Mage::log($currentCustomer);
                $customerTotals = Mage::getResourceModel('sales/sale_collection')
                     ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
                     //->setCustomerFilter($customer)
                     ->load()
                     ->getTotals();
                
                $lifetimeSales = $customerTotals->getLifetime();
                $averageSales = $customerTotals->getAverage();        
                $orderCollection = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('customer_email', array('eq' => array($email)));
                $select = $orderCollection->getSelect()
                    ->order('created_at DESC')                        
                    ->limit(5);

                foreach($orderCollection as $order) {
                    Mage::log($order);
                    $orders[] = Mage::helper('freshdesk')->getOrderDetail($order);
                }

                $urlModel = Mage::getModel('adminhtml/url')->setStore('admin');

                $info = array(
                    'guest' => false,
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'email' => $customer->getEmail(),
                    'active' => (bool)$customer->getIsActive(),       
                    'admin_url' => Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $customer->getId())),         
                    'created' => $customer->getCreatedAt(),
                    'dob' => $customer->getDob(),
                    'addresses' => array(),
                    'orders' => $orders != null ? $orders : array(),
                    'average_sale' => $averageSales,
                    'lifetime_sales' => $lifetimeSales
                );

                if($billing = $customer->getDefaultBillingAddress()) {
                    $info['addresses']['billing'] = $billing->format('text');
                    $info['telephone'] = $billing->format('telephone');
                }

                if($shipping = $customer->getDefaultShippingAddress()) {
                    $info['addresses']['shipping'] = $shipping->format('text');
                    $info['telephone'] = $billing->format('telephone');
                }

            } else {
                if(count($orders) == 0) {
                    // The email address doesn't even correspond with a guest customer
                    $this->getResponse()
                        ->setBody(json_encode(array('success' => false, 'message' => 'Customer does not exist')))
                        ->setHttpResponseCode(404)
                        ->setHeader('Content-type', 'application/json', true);
                    return $this;
                }

                $info = array(
                    'guest' => true,
                    'orders' => $orders,
                );
            }
        }
        catch(Exception $e) {
            $info = array(
                'status' => 500,
                'message' => 'Something went wrong! Please contact support'
            );
            $this->getResponse()
                ->setBody(json_encode($info))
                ->setHttpResponseCode(500)
                ->setHeader('Content-type', 'application/json', true);
            return $this;       
        }
		$this->getResponse()
            ->setBody(json_encode($info))
            ->setHttpResponseCode(200)
            ->setHeader('Content-type', 'application/json', true);
        return $this;       
	}

	public function getOrderDetailsAction() {
		if(!$this->_authorise()) {
            return $this;
        }
        $orderId = $this->getRequest()->getParams()['orderId'];        
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);        
        Mage::log($order);
        if($order->getId() == null) {
            $this->getResponse()
                ->setBody(json_encode(array('success' => false, 'message' => 'Order does not exist')))
                ->setHttpResponseCode(404)
                ->setHeader('Content-type', 'application/json', true);
            return $this;
        }

        $info = Mage::helper('freshdesk')->getOrderDetail($order);
        echo json_encode($info);
        exit();
        $this->getResponse()
            ->setBody(json_encode($info))
            ->setHttpResponseCode(200)
            ->setHeader('Content-type', 'application/json', true);
        return $this;
	}

    public function getAjaxGenerateUrl(){
      return Mage::helper('adminhtml')->getUrl('adminhtml/effy_freshdesk/regenerate');
    }
 }