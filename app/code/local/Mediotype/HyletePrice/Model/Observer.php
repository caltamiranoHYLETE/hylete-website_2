<?php

/**
 * Class Observer
 */
class Mediotype_HyletePrice_Model_Observer extends Amasty_Rules_Model_Observer
{
	/**
	 * Observer constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Responsible for adding the "is_on_flash_sale" attribute to catalog_product SELECTs
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function addIsFlashSaleAttribute(Varien_Event_Observer $observer)
	{
		if ($observer) {
			$collection = $observer->getEvent()->getCollection();
			$collection->addAttributeToSelect('is_on_flash_sale');
		}

		return $this;
	}

    /**
     * @param Varien_Event_Observer $observer
     * @return bool
     */
	public function calculateProductFinalPriceWithMsrp(Varien_Event_Observer $observer)
    {
        $quote = Mage::getSingleton('checkout/session');
        $hyletePriceHelper = Mage::helper('mediotype_hyleteprice');
        $hasMsrpTargetRule = $hyletePriceHelper->quoteHasMsrpTargetRule($quote);
        $product = $observer->getProduct();
        $msrp = $product->getMsrp();

        if (!$hasMsrpTargetRule || is_null($msrp) || !$msrp) {
            return false;
        }

        $product->setFinalPrice($msrp);
    }
}
