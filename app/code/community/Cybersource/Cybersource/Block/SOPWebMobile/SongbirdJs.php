<?php

class Cybersource_Cybersource_Block_SOPWebMobile_SongbirdJs extends Mage_Core_Block_Template
{
    const SONGBIRDJS_TEST_URL = 'https://songbirdstag.cardinalcommerce.com/edge/v1/songbird.js';
    const SONGBIRDJS_LIVE_URL = 'https://songbird.cardinalcommerce.com/edge/v1/songbird.js';

    /**
     * @var string
     */
    private $jwt;

    /**
     * @var array
     */
    private $ccaContinueDetails;

    public function getSongbirdJsUrl()
    {
        return Mage::helper('cybersource_core')->getIsTestMode()
            ? self::SONGBIRDJS_TEST_URL
            : self::SONGBIRDJS_LIVE_URL;
    }

    public function getPaUrl()
    {
        return Mage::getUrl('cybersource/sopwm/payWithPayerAuth');
    }

    public function getIsDebugMode()
    {
        return Mage::helper('cybersourcesop')->isDebugMode();
    }

    /**
     * @return string
     */
    public function getJwt()
    {
        return $this->jwt;
    }

    /**
     * @param string $jwt
     * @return Cybersource_Cybersource_Block_SOPWebMobile_SongbirdJs
     */
    public function setJwt($jwt)
    {
        $this->jwt = $jwt;
        return $this;
    }

    /**
     * @return array
     */
    public function getCcaContinueDetails()
    {
        return $this->ccaContinueDetails;
    }

    /**
     * @param array $ccaContinueDetails
     * @return Cybersource_Cybersource_Block_SOPWebMobile_SongbirdJs
     */
    public function setCcaContinueDetails($ccaContinueDetails)
    {
        $this->ccaContinueDetails = $ccaContinueDetails;
        return $this;
    }
}
