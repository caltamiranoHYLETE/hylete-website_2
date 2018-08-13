<?php
/**
 * Created by PhpStorm.
 * User: shasan
 * Date: 06/04/16
 * Time: 2:00 PM
 */
class Nextopia_Search_Block_Head extends Mage_Core_Block_Template
{
    public function allowShowBlock()
    {
        $helper = Mage::helper("nsearch");
        return (
            ($helper->isEnabled())
            || $this->getShowInDemo()
        );
    }
    
    public function getResultUrl($query = null)
    {
        $helper = Mage::helper("nsearch");
        if(!$helper->isEnabled() && $this->getShowInDemo()) {
            return $helper->getResultUrlWhileInDemo($query);
        } else {
            return $helper->getResultUrl();
        }
    }

    /**
     * @return null or string - the nextopia id
     */
    public function getNxtId()
    {
        return Mage::getStoreConfig('nextopia_ajax_options/settings/public_client_id');
    }

    public function getCustomerGroupCode()
    {
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        if ($groupId != Mage_Customer_Model_Group::NOT_LOGGED_IN_ID) {
            return Mage::getModel('customer/group')->load($groupId)->getCode();
        }
        return "";
    }
    
    public function getAjaxVersion()
    {
        $helper = Mage::helper("nsearch");
        
        return $helper->getAjaxVersion();
    }
}