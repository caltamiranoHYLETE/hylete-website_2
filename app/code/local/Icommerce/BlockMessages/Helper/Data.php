<?php
class Icommerce_BlockMessages_Helper_Data extends Mage_AdminNotification_Helper_Data
{
    protected $_latestNotice;

    public function getLatestNotice()
    {
        if (is_null($this->_latestNotice)) {
            $this->_latestNotice = parent::getLatestNotice();
            $this->_latestNotice->unsetData();
        }
        return $this->_latestNotice;
    }

    public function getUnreadNoticeCount($severity)
    {
        return 0;
    }

    public function isReadablePopupObject()
    {
        return false;
    }
}
