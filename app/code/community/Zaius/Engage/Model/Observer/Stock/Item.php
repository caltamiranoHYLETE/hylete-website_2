<?php

class Zaius_Engage_Model_Observer_Stock_Item
    extends Zaius_Engage_Model_Observer
{
    public function saveAfter($observer)
    {
        $helper = Mage::helper('zaius_engage');
        if ($helper->isEnabled()) {
            $stockItem = $observer->getEvent()->getData('item');
            if ($stockItem->getManageStock()
                && ($stockItem->getData('qty') != $stockItem->getOrigData('qty')
                    || $stockItem->getData('is_in_stock') != $stockItem->getOrigData('is_in_stock'))
            ) {
                $entity = array(
                    'product_id' => $stockItem->getProductId(),
                    'qty' => $stockItem->getQty(),
                    'is_in_stock' => $stockItem->getIsInStock()
                );
                $this->postEntity('product', $entity);
            }
        }
    }
}