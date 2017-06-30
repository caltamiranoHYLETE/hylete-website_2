<?php

/**
 * Main helper for the loyalty abstraction.
 *
 * @package Ebizmarts_BakerlooGifting
 */
class Ebizmarts_BakerlooGifting_Helper_Data extends Mage_Core_Helper_Abstract {

    const GIFTING_INTEGRATIONS_CONFIG_PATH = 'default/bakerloo_gifting/integrations';

    /**
     * Simple config check.
     */
    public function canUse() {
        return Mage::helper('bakerloo_restful/integrations')->canUse('gifting');
    }

    /**
     * Returns selected integration from config.
     *
     * @return string
     */
    public function getIntegrationFromConfig() {
        return Mage::helper('bakerloo_restful/integrations')->getIntegrationFromConfig('gifting');
    }

    public function getSupportedTypes() {
        return array(
            'giftcard' => 'Enterprise_GiftCardAccount',
            'aw_giftcard' => 'AW_Giftcard',
            'giftvoucher' => 'Magestore_Giftvoucher'
        );
    }

    /**
     * @param $product
     * @return bool
     *
     * Determines whether given product type is one of the supported giftcard types.
     * Supported types are:
     * - Magento EE => 'giftcard'
     * - AW Giftcard => 'aw_giftcard'
     * - Magestore => 'giftvoucher'
     */
    public function productIsGiftcard(Mage_Catalog_Model_Product $product){
        $model = $this->getGiftcard($product->getTypeId());
        return (is_null($model) ? false : true);
    }

    public function getGiftcardOptions(Mage_Catalog_Model_Product $product){
        $model = $this->getGiftcard($product->getTypeId());
        return $model->getOptions($product);
    }

    public function getGiftcard($type){
        $model = null;

        switch($type){
            case 'giftcard':
                $model = Mage::getModel('bakerloo_gifting/enterpriseGiftcard');
                break;
            case 'aw_giftcard':
                $model = Mage::getModel('bakerloo_gifting/aheadworksGiftcard');
                break;
            case 'giftvoucher':
                $model = Mage::getModel('bakerloo_gifting/magestoreGiftvoucher');
                break;
            default:
                break;
        }

        return $model;
    }


}