<?php

class Ebizmarts_BakerlooRestful_Model_Observer {

    protected function _canProfile($controllerAction) {
        $isMod        = (bool) ('Ebizmarts_BakerlooRestful' == $controllerAction->getRequest()->getControllerModule());
        $debugEnabled = ((int)Mage::helper('bakerloo_restful')->config("general/debug") === 1);

        return (bool)($isMod && $debugEnabled);
    }

    /**
     * Add button to Cache Management to be able to clear thumbs generated for categories.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function clearCategoryImageCacheButton(Varien_Event_Observer $observer) {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Cache) {
            $message = Mage::helper('bakerloo_restful')->__('Are you sure?');
            $clearUrl = $block->getUrl('adminhtml/bakerloo/clearCategoryImagesCache');

            $block->addButton('flush_pos_category_images', array(
                'label'     => Mage::helper('bakerloo_restful')->__('Flush POS category images cache'),
                'onclick'   => 'confirmSetLocation(\''.$message.'\', \'' . $clearUrl .'\')',
                'class'     => 'delete',
            ));
        }

        return $observer;
    }

    /**
     * Add column to tax rates to show if is set to synch to POS.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function addTaxRateSynchColumn(Varien_Event_Observer $observer) {

        $block = $observer->getEvent()->getBlock();

        if($block instanceof Mage_Adminhtml_Block_Tax_Rate_Grid) {

            $filterRate = (int)Mage::helper('bakerloo_restful')->config('general/filter_tax_rates');
            if(!$filterRate)
                return $observer;

            $h = Mage::helper('bakerloo_restful');

            $block->addColumnAfter('ebizmarts_pos_synch', array(
                    'type' => 'options',
                    'header' => $h->__('POS Synchronization'),
                    'index' => 'ebizmarts_pos_synch',
                    'align' => 'center',
                    'filter' => false,
                    'options' => array(
                        '0' => $h->__('No'),
                        '1' => $h->__('Yes'),
                    ),
                    'sortable' => true,
                )
                , 'rate');
        }

        return $observer;
    }

    /**
     * Add field to tax rates form to configure if synch to POS.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function addTaxRateSynchField(Varien_Event_Observer $observer) {

        $block = $observer->getEvent()->getBlock();

        if($block instanceof Mage_Adminhtml_Block_Tax_Rate_Form) {

            $filterRate = (int)Mage::helper('bakerloo_restful')->config('general/filter_tax_rates');
            if(!$filterRate)
                return $observer;

            $form = $block->getForm();

            $h = Mage::helper('bakerloo_restful');

            $fieldset = $form->addFieldset('pos_fieldset', array('legend' => $h->__('POS Synchronization')));
            $fieldset->addField('ebizmarts_pos_synch', 'select', array(
                'name'    => 'ebizmarts_pos_synch',
                'label'   => $h->__('Synchronize to POS'),
                'options' => array(
                    '0' => $h->__('No'),
                    '1' => $h->__('Yes'),
                )
            ));

            $d = Mage::getSingleton('tax/calculation_rate')->getData();

            $form->getElement('ebizmarts_pos_synch')->setValue($d['ebizmarts_pos_synch']);

        }

        return $observer;
    }

    public function httpResponseDebug(Varien_Event_Observer $observer) {
        $requestId = Mage::registry('brest_request_id');

        if($requestId) {
            $response = $observer->getEvent()->getResponse();
            Mage::helper('bakerloo_restful')->debug($response);
        }

        return $observer;
    }

    public function addPaymentData(Varien_Event_Observer $observer){
        $dto = $observer->getOrder();

        if(!is_array($dto->getPayment())) {
            $order = Mage::getModel('sales/order')->load($dto->getEntityId());
            $posOrder = Mage::getModel('bakerloo_restful/order')->load($dto->getEntityId(), 'order_id');
            $dto->setPayment(Mage::getModel('bakerloo_restful/api_orders')->_getOrderPayments($order, $posOrder));
        }

        return $observer;
    }

}