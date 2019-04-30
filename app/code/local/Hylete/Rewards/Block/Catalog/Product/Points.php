<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */

class Hylete_Rewards_Block_Catalog_Product_Points extends TBT_Rewards_Block_Product_View_Points_Earned
{
    /**
     * {@inheritdoc}
     */
    public function getDistriRewards()
    {
        $product = $this->getProduct();
        $basePrice = $product->getPriceModel()->getBasePrice($product);

        return array($this->getCurrencyId() => $basePrice);
    }

    /**
     * Returns rewards currency ID
     *
     * @return int
     */
    private function getCurrencyId()
    {
        $currencyId = $this->helper('hylete_rewards')->getProductCurrencyId();
        $currencyModel = Mage::getModel('rewards/currency')->load($currencyId);

        if (!$currencyModel->getId()) {
            $currencyId = Mage::helper('rewards/currency')->getDefaultCurrencyId();
        }
        return (int)$currencyId;
    }
}