<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

class Hylete_Rewards_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_REWARD_POINTS_TOOLTIP = 'rewards/autointegration/product_view_page_product_points_tooltip';

    /**
     * Retrieve reward points tooltip content
     *
     * @param null|string|int $store
     * @return string
     */
    public function getRewardPointsTooltipHtml($store = null)
    {
        $blockId = Mage::getStoreConfig(self::XML_PATH_REWARD_POINTS_TOOLTIP, $store);
        if (!$blockId) {
            return '';
        }

        return $this->getLayout()->createBlock('cms/block')
            ->setBlockId($blockId)
            ->toHtml();
    }
}
