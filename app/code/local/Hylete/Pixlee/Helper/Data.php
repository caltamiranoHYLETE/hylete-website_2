<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

class Hylete_Pixlee_Helper_Data extends Pixlee_Base_Helper_Data
{
    /**
     * Retrieve Account ID
     *
     * @return string
     */
    public function getAccountId()
    {
        return Mage::getStoreConfig('pixlee/pixlee/account_id');
    }

    /**
     * Retrieve Account API Key
     *
     * @return string
     */
    public function getAccountApiKey()
    {
        return Mage::getStoreConfig('pixlee/pixlee/account_api_key');
    }

    /**
     * Retrieve Category Widget ID
     *
     * @return string
     */
    public function getCategoryWidgetId()
    {
        return Mage::getStoreConfig('pixlee/widget_options/category_widget_id');
    }

    /**
     * Retrieve Category Widget ID
     *
     * @return string
     */
    public function getProductWidgetId()
    {
        return Mage::getStoreConfig('pixlee/widget_options/widget_id');
    }

    /**
     * Retrieve Pixlee Album ID by Product ID
     *
     * @param $productId
     * @return int
     */
    public function getAlbumIdByProductId($productId)
    {
        $album = $this->getPixleeAlbum()->load($productId, 'product_id');
        if ($album === null) {
            return '';
        }

        return $album->getPixleeAlbumId();
    }
}
