<?php

/**
 * Class Observer
 */
class Mediotype_HyletePrice_Model_Observer extends Amasty_Rules_Model_Observer
{
    protected $_additionalAttributes = array('special_price_label');

    /**
     * Add additional price attributes to the collection select.
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addAttributeToSelect(Varien_Event_Observer $observer)
    {
        try {
            $observer->getEvent()
                ->getCollection()
                ->addAttributeToSelect($this->_additionalAttributes);
        } catch (Exception $error) {
            Mage::logException($error);
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function calculateProductFinalPriceWithMsrp(Varien_Event_Observer $observer)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $hyletePriceHelper = Mage::helper('mediotype_hyleteprice');
        $hasMsrpTargetRule = $hyletePriceHelper->quoteHasMsrpTargetRule($quote);
        $product = $observer->getProduct();
        $msrp = $product->getMsrp();

        if (!$hasMsrpTargetRule || is_null($msrp) || !$msrp) {
            return false;
        }

        $product->setFinalPrice($msrp);
    }

    /**
     * Flush CMS blocks cache by tag
     *
     * @param Varien_Event_Observer $observer
     */
    public function flushCmsBlockCacheByTags(Varien_Event_Observer $observer){
        $cache  = Mage::getSingleton('core/cache');
        $cache->clean(array(Mediotype_HyletePrice_Helper_Data::CMS_BLOCK_CACHE_TAG));
    }
}
