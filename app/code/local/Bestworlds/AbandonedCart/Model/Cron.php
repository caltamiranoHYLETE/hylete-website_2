<?php
/**
 * Best Worlds
 * http://www.bestworlds.com
 * 888-751-5348
 * 
 * Need help? contact us:
 *  http://www.bestworlds.com/contact-us
 * 
 * Want to customize or need help with your store?
 *  Phone: 888-751-5348
 *  Email: info@bestworlds.com
 *
 * @category    Bestworlds
 * @package     Bestworlds_AbandonedCart
 * @copyright   Copyright (c) 2018 Best Worlds
 * @license     http://www.bestworlds.com/software_product_license.html
 */

/**
 * Cron model
 *
 * @category   Bestworlds
 * @package    Bestworlds_AbandonedCart
 * @author     Best Worlds Team <info@bestworlds.com>
 */
class Bestworlds_AbandonedCart_Model_Cron 
{
    public function setLoggedInCaptureType()
    {
        if (!Mage::getStoreConfigFlag('abandonedcart/basic/enable') || !Mage::getStoreConfigFlag('abandonedcart/basic/cron')) return $this;

        $installedExtensionDate = Mage::getStoreConfig('abandonedcart/basic/installed_time');
        $abandonedCartCloseTime = Bestworlds_AbandonedCart_Block_Adminhtml_Reports::CLOSETIME;

        $collection= Mage::getModel('sales/quote')->getCollection()
            ->addFieldToFilter('items_count', array('gt' => 0))
            ->addFieldToFilter('customer_email', array('notnull' => true))
            ->addFieldToFilter('email_captured_from', array('null' => true))
            ->addFieldToFilter('created_at', array('gteq' => $installedExtensionDate));
        $collection->getSelect()->where('TIMESTAMPDIFF(SECOND, `main_table`.updated_at, UTC_TIMESTAMP()) > ?', $abandonedCartCloseTime);

        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $item->setEmailCapturedFrom(Bestworlds_AbandonedCart_Model_Capturetypes::LOGGED_IN);
                $item->save();
            }
        }

        return true;
    }
}
