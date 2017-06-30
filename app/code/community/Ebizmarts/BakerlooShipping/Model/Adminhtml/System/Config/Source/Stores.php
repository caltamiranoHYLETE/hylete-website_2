<?php

class Ebizmarts_BakerlooShipping_Model_Adminhtml_System_Config_Source_Stores {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        $collection = Mage::getModel('core/website')
            ->getCollection()
            ->joinGroupAndStore();

        $stores = array();

        foreach($collection as $_store) {
            $myStore = array();

            $optionLabel = "{$_store->getGroupTitle()} / {$_store->getStoreTitle()}";

            if(isset($stores[$_store->getWebsiteId()])) {
                $stores[$_store->getWebsiteId()]['value'][] = array('value' => $_store->getStoreId(), 'label' => $optionLabel);
            }
            else {
                $myStore['label']= "{$_store->getName()}";
                $myStore['value']= array(array('value' => $_store->getStoreId(), 'label' => $optionLabel));

                $stores[$_store->getWebsiteId()] = $myStore;
            }

        }

        return $stores;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return array();
    }

}