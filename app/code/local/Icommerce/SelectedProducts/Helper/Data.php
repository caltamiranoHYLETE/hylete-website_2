<?php
/**
 * Created by JetBrains PhpStorm.
 * User: metalim
 * Date: 2011-08-17
 * Time: 10:19 AM
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_SelectedProducts_Helper_Data extends Mage_Core_Helper_Abstract
{
    public static function collRewriteUrl($coll, $cat_id = 0, $store_id = null)
    {
        if (!$store_id) $store_id = Mage::app()->getStore()->getData("store_id");
        $db = Icommerce_Default::getDbRead();
        foreach ($coll as $prod) {
            $ur = $db->query( "SELECT * FROM core_url_rewrite WHERE product_id=" . $prod->getId() . " AND category_id!=" . $cat_id . " AND store_id=" . $store_id);
            foreach( $ur as $u ){
                if (isset($u['request_path'])) {
                    $prod->setRequestPath($u['request_path']);
                    break;
                 }
            }
        }
    }

    public function sanitizeManualProductIds($productIds, $returnArray = false)
    {
        $sanitizedProductIds = array_filter(array_map('intval', explode(',', $productIds)));

        if ($returnArray) {
            return $sanitizedProductIds;
        }

        return implode(',', $sanitizedProductIds);
    }
}
