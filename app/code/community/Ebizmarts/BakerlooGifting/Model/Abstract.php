<?php

/**
 * Gifting abstraction providing support for any integration.
 *
 * @package Ebizmarts_BakerlooGifting
 */
abstract class Ebizmarts_BakerlooGifting_Model_Abstract extends Varien_Object {

    /**
     * @var Stores gift card instance.
     */
    protected $_giftcard;

    /**
     * @var bool Stores if the integration can be used.
     */
    protected $_canUse = false;

    /**
     * Check if the integration can be used.
     *
     * @return bool
     */
    public function canUse() {
        return $this->_canUse;
    }

    /**
     * Check if the integration is enabled in config.
     */
    public function isEnabled() {
        $config = $this->_getGiftingConfig();

        return ($config != '');
    }

    /**
     * Return config data from settings.
     *
     * @return string
     */
    protected function _getGiftingConfig() {
        return (string)Mage::helper('bakerloo_restful')->config('integrations/gifting');
    }

    public function getImp() {
        return $this->_giftcard;
    }

    /**
     * Return WebsiteId for a given StoreId.
     *
     * @param $storeId
     * @return null|int Website Id.
     */
    public function websiteIdByStoreId($storeId) {
        $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();

        return $websiteId;
    }

    /**
     * @return array array(
     *                  'id'          => <giftcard_id>,
     *                  'code'        => <giftcard_code>,
     *                  'base_amount' => <giftcard_base_amount>,
     *                  'amount'      => <giftcard_amount>,
     *               );
     */
    abstract public function init();

    abstract public function isValid();

    abstract public function addToCart(Mage_Sales_Model_Quote $quote);

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array array(
     *                  'id'          => <giftcard_id>,
     *                  'code'        => <giftcard_code>,
     *                  'base_amount' => <giftcard_base_amount>,
     *                  'amount'      => <giftcard_amount>,
     *               );
     */
    abstract public function getQuoteGiftCards(Mage_Sales_Model_Quote $quote);

    /**
     * Create a new giftcard.
     *
     * @param $storeId
     * @param $amount
     * @param null $expirationDate
     * @return null|string Giftcard code or null if not created.
     */
    abstract public function create($storeId, $amount, $expirationDate = null);

    /**
     * Add balance to an existing giftcard.
     *
     * @param $amount
     * @param null $data
     * @return null|string Giftcard code or null if not created.
     */
    abstract public function addBalance($amount, $data = null);

    /**
     * Get giftcard product options.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array();
     */
    abstract public function getOptions(Mage_Catalog_Model_Product $product);

    /**
     * Add type-dependent buy info on giftcard product add to cart.
     *
     * @param $data
     * @return mixed
     */
    abstract public function getBuyInfoOptions($data);


}