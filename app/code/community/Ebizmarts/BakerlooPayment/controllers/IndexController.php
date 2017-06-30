<?php

/**
 * POS REST Api entry point.
 */
class Ebizmarts_BakerlooPayment_IndexController extends Mage_Core_Controller_Front_Action {

	private function _validateRequest() {

        $h = Mage::helper("bakerloo_restful");

        $storeId = (int)$this->getRequest()->getParam($h->getStoreIdHeader());

        if(!$storeId) {
          Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($storeId);

		    //Check if module is active
        if(((boolean)$h->config("general/enabled")) === false) {
			   $this->getResponse()
                  ->setHeader('Content-Type', 'application/json', true)
                  ->setHeader('Connection', 'keep-alive', true)
                  ->setHttpResponseCode(410)
                  ->setBody($h->encodeResponse($h->jsonError("API module is disabled")));
        }

        //Validate Request
        $h->isCallAllowed($this->getRequest(), $storeId, (string)$this->getRequest()->getParam($h->getApiKeyHeader()));

	}

	/**
	* Process hosted payment pages, iFrame like.
	*/
	public function hostedAction() {

    $this->_validateRequest();

    $model = Mage::helper("payment")->getMethodInstance(((string)$this->getRequest()->getParam('method')));

    //If payment method is not active, redirect to 404.
    if(false === $model->isActive()) {
      $this->_forward('defaultNoRoute');
    }
    else {

      if($model->isIframe()) {
        $blockName = $model->getConfigData("iframe_block");

        $iframe = $this->getLayout()->createBlock($blockName);
        $iframe->setFormPostData(new Varien_Object($this->getRequest()->getParams()));

        $this->getResponse()->setBody($iframe->toHtml());

      }

    }

	}

  public function hostedCancelAction() {

    //@TODO: Validate request somehow.

    $methodCode = (string)$this->getRequest()->getParam('method');

    $params = array(
      'responseStatus' => 'cancel',
      //'txid'   => $this->getRequest()->getParam('txid'),
    );

    $url  = Mage::helper("bakerloo_payment")->returnUrl($methodCode, $params);

    $this->getResponse()->setBody(Mage::helper("bakerloo_payment")->jsSetLocation($url));

  }

  public function hostedSuccessAction() {

    if($this->getRequest()->isPost()) {

      $post       = array_filter($this->getRequest()->getPost());

      //Mage::log($this->getRequest()->getPost());

      $methodCode = $this->getRequest()->getParam('method');
      $model = Mage::helper("payment")->getMethodInstance((string)$methodCode);

      $returnData = $model->getReturnData($post);
      $url  = Mage::helper("bakerloo_payment")->returnUrl($methodCode, $returnData);
      $this->getResponse()->setBody(Mage::helper("bakerloo_payment")->jsSetLocation($url));

    }
    else {
      $this->_forward('defaultNoRoute');
    }

  }

}