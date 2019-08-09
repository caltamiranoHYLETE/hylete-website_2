<?php

class Hylete_Notifications_Model_Observer
{
	public function sendAccountCreatedEvent(Varien_Event_Observer $observer) {

		$event = $observer->getEvent();
		$controller = $event->getAccountController();
		$webhookUrl = Mage::getStoreConfig('hylete_notifications_settings/hylete_settings/new_account_url');

		if(!empty($webhookUrl)) {

			if ($controller) {

				$customer = $event->getCustomer();

				$r = new stdClass();
				$r->Email = $customer->getEmail();
				$r->Gender = $customer->getGender();
				$r->FirstName = $customer->getFirstname();
				$r->LastName = $customer->getLastname();
				$r->RefererUrl = $_SERVER['HTTP_REFERER'];
				$r->AccountFormUrl = $controller->getRequest()->getRequestString();

				try {
					$client = new Zend_Http_Client();
					$client->setUri($webhookUrl);
					$client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
					$client->setHeaders('Content-type','application/json');
					$client->setHeaders('APIKey','1b33800d-ce0e-4a52-89a6-5ba5751d4328');

					$json = Mage::helper('core')->jsonEncode($r);
					$client->setParameterPost('data', $json);
					//response will be asynchronous and always return 200
					$client->request(Zend_Http_Client::POST);

				} catch (Exception $e) {
					$debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
					Mage::log($e->getMessage(), null, 'hylete-notifications.log', true);
				}
			}
		}
	}

	public function sendOrderCreatedEvent(Varien_Event_Observer $observer) {

		$event = $observer->getEvent();
		$order = $event->getData('order');

		$webhookUrl = Mage::getStoreConfig('hylete_notifications_settings/hylete_settings/new_order_url');
		if(!empty($webhookUrl)) {

			if ($order) {

				$r = new stdClass();
				$r->IncrementId = $order->getIncrementId();
				$r->OrderId = $order->getEntityId();
				$r->CustomerId = $order->getCustomerId();

				try {
					$client = new Zend_Http_Client();
					$client->setUri($webhookUrl);
					$client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
					$client->setHeaders('Content-type','application/json');
					$client->setHeaders('APIKey','1b33800d-ce0e-4a52-89a6-5ba5751d4328');

					$json = Mage::helper('core')->jsonEncode($r);
					$client->setParameterPost('data', $json);
					//response will be asynchronous and always return 200
					$client->request(Zend_Http_Client::POST);

				} catch (Exception $e) {
					$debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
					Mage::log($e->getMessage(), null, 'hylete-notifications.log', true);
				}
			}
		}
	}
}

