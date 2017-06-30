<?php

class Ebizmarts_BakerlooGifting_Model_Observer {

    public function orderPlaceAfter(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();

        if ($order->getId()) {
            $items = $order->getAllItems();

            foreach ($items as $_item) {
                $product = $_item->getProduct();
                $br = $_item->getBuyRequest();
                if ($product->getTypeId() == 'giftvoucher' and $br->getGiftCode())
                    Mage::getModel('bakerloo_gifting/magestoreGiftvoucher')->addGiftVoucherForPosOrderItem($_item, $order);
            }
        }

        return $this;
    }

    /**
     * If a gift card integration is selected, disable
     * output from other gift card extensions.
     *
     * @param Varien_Event_Observer $observer
     */
    public function configChange(Varien_Event_Observer $observer) {
        $h = Mage::helper('bakerloo_gifting');

        $selected = $h->getIntegrationFromConfig();
        $supportedTypes = $h->getSupportedTypes();

        foreach($supportedTypes as $key => $type) {

            if($type == $selected)
                Mage::getModel('core/config')->saveConfig('advanced/modules_disable_output/' . $type, 0);

            elseif(Mage::helper('bakerloo_restful')->isModuleInstalled($type))
                Mage::getModel('core/config')->saveConfig('advanced/modules_disable_output/' . $type, 1);
        }
    }
}