<?php

class Ebizmarts_BakerlooPayment_Block_Form_Sagepayserver extends Ebizmarts_BakerlooPayment_Block_Form_Iframe {

    protected function _toHtml() {

        $data = $this->getFormPostData();

        $method = Mage::getModel('bakerloo_payment/sagepayserver');

        try{
            if($method->isSagepaySuiteInstalledAndCompatible()){

                $request = $method->buildSagepayServerRequest($data);
                $response = $method->requestPost($request);

                if($response && $response->getData('response_status')=="OK"){

                    $this->setPostFields($response->toArray());
                    $this->setPostUrl($response->getData('next_url'));
                    $this->setCode($data->getMethod());

                    return parent::_toHtml();
                }else{
                    if($response){
                        return parent::_toErrorHtml($response->getData('response_status_detail'));
                    }else{
                        return parent::_toErrorHtml("Invalid SagePay response.");
                    }
                }
            }
        }catch(Exception $e){
            Mage::logException($e);
            return parent::_toErrorHtml($e->getMessage());
        }
    }

}