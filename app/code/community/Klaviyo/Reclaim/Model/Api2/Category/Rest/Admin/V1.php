<?php
/**
 * Category REST API
 *
 * @category   Klaviyo
 * @package    Klaviyo_Reclaim
 * @author     Klaviyo Team <support@klaviyo.com>
 * @copyright  Copyright (c) 2013 Klaviyo Inc. (http://www.klaviyo.com)
 *
 **/
class Klaviyo_Reclaim_Model_Api2_Category_Rest_Admin_V1 extends Klaviyo_Reclaim_Model_Api2_Category
{
    /**
     * Retrieve category root for specified shop.
     *
     * @return string
     */
    protected function _retrieve()
    {
        $storeViewId = $this->getRequest()->getParam('store_view_id');
        $helper = Mage::helper('klaviyo_reclaim');
        try {
            return $helper->getStoreCategoryRoot($storeViewId);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if (empty($errorMessage)) {
                // make the best guess
                $errorMessage = 'Bad request: invalid store view id: ' . $storeViewId;
            }
            return $errorMessage;
        }
    }
    /**
     * Retrieve category roots for all shops
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $helper = Mage::helper('klaviyo_reclaim');
        try {
            return $helper->getCategoryRoots();
        } catch (Exception $e) {
            return [];
        }
    }
}
