<?php
class TroopID_Connect_AuthorizeController extends Mage_Core_Controller_Front_Action {

    private function getSession() {
        return Mage::getSingleton("checkout/session");
    }

    private function getConfig() {
        return Mage::helper("troopid_connect");
    }

    private function getOauth() {
        return Mage::helper("troopid_connect/oauth");
    }

    private function getCart() {
        return Mage::getSingleton("checkout/cart");
    }

    public function removeAction() {
        $cart   = $this->getCart();
        $quote  = $cart->getQuote();

        $quote->setTroopidUid(NULL);
        $quote->setTroopidScope(NULL);
        $quote->setTroopidAffiliation(NULL);
        $quote->save();

        $this->_redirectUrl($_SERVER['HTTP_REFERER']);
        $this->setFlag("", self::FLAG_NO_DISPATCH, true);

        return $this;
    }

    public function authorizeAction() {
        $session    = $this->getSession();
        $scope      = $this->getRequest()->getParam("scope");
        $url        = $this->getOauth()->getAuthorizeUrl($scope);

        $session->setScope($scope);
        $this->getResponse()->setRedirect($url);
    }

    public function callbackAction() {

        $config  = $this->getConfig();
        $oauth   = $this->getOauth();
        $session = $this->getSession();

        /* scope from session */
        $scope = $session->getScope();

        /* code from initial callback */
        $code = $this->getRequest()->getParam("code");

        /* code was not found, invalid callback request */
        if (empty($code)) {
            $session->addError($config->__("ID.me verification failed, please contact the store owner (code 101)."));
        } else {

            /* request access token with the given code */
            $token = $oauth->getAccessToken($code);

            /* request user profile data with the given access token */
            $data = $oauth->getProfileData($token, $scope);

            if (empty($data)) {
                $session->addError($config->__("ID.me verification failed, please contact the store owner (code 102)."));
            } else {
                $cart   = $this->getCart();
                $quote  = $cart->getQuote();

                if ($data["verified"]) {
                    $quote->setTroopidUid($data["id"]);
                    $quote->setTroopidScope($scope);
                    $quote->setTroopidAffiliation($data["affiliation"]);
                    $quote->save();

                    $session->addSuccess($config->__("Successfully verified your affiliation via ID.me"));
                } else {
                    $session->addError($config->__("Unfortunately your have not verified your affiliation with ID.me"));
                }
            }
        }

        $this->loadLayout()->renderLayout();
    }

}