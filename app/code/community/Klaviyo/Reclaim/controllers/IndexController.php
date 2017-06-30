<?php

/**
 * Reclaim frontend controller
 *
 * @author Klaviyo Team (support@klaviyo.com)
 */

class Klaviyo_Reclaim_IndexController extends Mage_Core_Controller_Front_Action
{

  private static $_preservableRequestParams = array('utm_medium', 'utm_source', 'utm_campaign', 'utm_term');

  /**
   * Pre dispatch action that allows to redirect to no route page in case of disabled extension through Admin panel
   */
  public function preDispatch()
  {
    parent::preDispatch();

    if (!Mage::helper('klaviyo_reclaim')->isEnabled()) {
      $this->setFlag('', 'no-dispatch', true);
      $this->_redirect('noRoute');
    }
  }

  /**
   * Checkout item action
   */
  public function viewAction()
  {
    $request = $this->getRequest();
    $checkout_id = $request->getParam('id');

    if ($checkout_id) {
      $checkout = Mage::getModel('klaviyo_reclaim/checkout');
      $checkout->load($checkout_id);

      if ($checkout->getId()) {
        $saved_quote = Mage::getModel('sales/quote');
        $saved_quote->load($checkout->getQuoteId());
        $cart = Mage::getSingleton('checkout/cart');

        if ($saved_quote->getId() != $cart->getQuote()->getId() && !$cart->getItemsCount()) {
          $cart->getQuote()->load($checkout->getQuoteId());
          $cart->save();
        }
      }
    }
    
    $params = array();
    foreach (self::$_preservableRequestParams as $key) {
      $value = $this->getRequest()->getParam($key);

      if ($value) {
        $params[$key] = $value;
      }
    }

    $this->_redirectUrl(Mage::getUrl('checkout/cart', array('_query' => $params)));
  }

  /**
   * Save cart email action
   */
  public function saveEmailAction()
  {
    $email = $this->getRequest()->getParam('email');

    if (!Zend_Validate::is($email, 'EmailAddress')) {
      $response = array(
        'saved' => false,
        'error' => 'invalid_email'
      );
    } else {
      $cart = Mage::getSingleton('checkout/cart');
      $quote = $cart->getQuote();

      // Save email to quote object.
      $quote->setCustomerEmail($email);
      $quote->save();

      $response = array(
        'saved' => true
      );
    }

    $this->getResponse()->setHeader('Content-type', 'application/json');
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    return;
  }

  /**
   * Klaviyo extension status action
   */
  public function statusAction()
  {
    $nonce = $this->getRequest()->getParam('nonce');

    if (!$nonce) {
      $response = array('data' => NULL);
    } else {
      $helper = Mage::helper('klaviyo_reclaim');

      $is_enabled = $helper->isEnabled();
      $is_api_key_set = $helper->getPublicApiKey() != NULL;

      $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
      $hour_ago = Zend_Date::now();
      $hour_ago->sub(60, Zend_Date::MINUTE);
      $hour_ago = $adapter->convertDateTime($hour_ago);

      $recent_klaviyo_cron_jobs = Mage::getModel('cron/schedule')->getCollection()
        ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_SUCCESS)
        ->addFieldToFilter('finished_at', array('gteq' => $hour_ago))
        ->addFieldToFilter('job_code', 'klaviyo_track_quotes')
        ->addOrder('finished_at', $direction='desc');
      
      $klaviyo_cron_job_has_recently_succeeded = $recent_klaviyo_cron_jobs->count() > 0;

      if ($klaviyo_cron_job_has_recently_succeeded) {
	
        $most_recent_successful_klaviyo_cron_job = $recent_klaviyo_cron_jobs->getFirstItem();

        $most_recent_successful_klaviyo_cron_job_data = array(
          'Finished at' => Mage::getSingleton('core/date')
            ->gmtDate($most_recent_successful_klaviyo_cron_job->getFinishedAt()),
          'Messages' => $most_recent_successful_klaviyo_cron_job->getMessages()
        );
      } else {
        $most_recent_successful_klaviyo_cron_job_data = array();
      }

      $recent_unsuccessful_klaviyo_cron_jobs = Mage::getModel('cron/schedule')->getCollection()
        ->addFieldToFilter('status', array('in' => array(
          Mage_Cron_Model_Schedule::STATUS_MISSED,
          Mage_Cron_Model_Schedule::STATUS_ERROR
        )))
        ->addFieldToFilter('created_at', array('gteq' => $hour_ago))
        ->addFieldToFilter('job_code', 'klaviyo_track_quotes')
        ->addOrder('finished_at', $direction='desc');

      $klaviyo_cron_job_has_recently_not_succeeded = $recent_unsuccessful_klaviyo_cron_jobs->count() > 0;

      $recent_unsuccessful_klaviyo_cron_jobs_data = array();

      if ($klaviyo_cron_job_has_recently_not_succeeded) {
        foreach ($recent_unsuccessful_klaviyo_cron_jobs as $job) {
          $data = array(
            'ID' => $job->getScheduleId(),
            'Status' => $job->getStatus(),
            'Message' => $job->getMessage()
          );
	  
          $recent_unsuccessful_klaviyo_cron_jobs_data[] = $data;
        }
      }

      $has_reclaim_entries = Mage::getModel('klaviyo_reclaim/checkout')->getCollection()->count() > 0;
      
      $most_recent_tracking_eligible_quotes = Mage::getResourceModel('sales/quote_collection')
        ->addFieldToFilter('converted_at', array('null' => true))
        ->addOrder('updated_at', $direction='desc')
        ->setPageSize(5)
        ->setCurPage(1);
     
      $most_recent_tracking_eligible_quotes_data = array();
      foreach ($most_recent_tracking_eligible_quotes as $quote) {
        $select = array(
          'Id' => $quote->getEntityId(),
          'Store Id:' => $quote->getStoreId(),
          'Created At' => Mage::getSingleton('core/date')
            ->gmtDate($quote->getCreatedAt()),
          'Updated At' => Mage::getSingleton('core/date')
            ->gmtDate($quote->getUpdatedAt()),
          'Customer Email' => $quote->getCustomerEmail(),
          'Public API Key' => Mage::helper('klaviyo_reclaim')->getPublicApiKey($quote->getStoreId()),
          'Remote IP Address' => $quote->getRemoteIp(),
          'Quote Items' => count($quote->getItemsCollection()),
          'Is Active' => $quote->getIsActive()
        );

        $most_recent_tracking_eligible_quotes_data[] = $select;
      }

      $response = array(
        'data' => array(
          '$most_recent_successful_klaviyo_cron_job_data' => $most_recent_successful_klaviyo_cron_job_data,
          '$is_enabled' => $is_enabled,
          '$is_api_key_set' => $is_api_key_set,
          '$klaviyo_cron_job_has_recently_succeeded' => $klaviyo_cron_job_has_recently_succeeded,
          '$klaviyo_cron_job_has_recently_not_succeeded' => $klaviyo_cron_job_has_recently_not_succeeded,
          '$recent_unsuccessful_klaviyo_cron_jobs_data' => $recent_unsuccessful_klaviyo_cron_jobs_data,
          '$most_recent_tracking_eligible_quotes_data' => $most_recent_tracking_eligible_quotes_data,
          '$has_reclaim_entries' => $has_reclaim_entries
        )
      );
    }

    $this->getResponse()->setHeader('Content-type', 'application/json');
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    return;
  }
}
