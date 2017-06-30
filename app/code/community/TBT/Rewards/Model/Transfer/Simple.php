<?php

/**
 * Class TBT_Rewards_Model_Transfer_Simple
 * Implements a simplified transfer model for fast operations.
 *
 * CAUTION
 * =======
 * This class will NOT do any error checking on the transfer attributes or check the transfer
 * amount against the customer's balance, which can result in a negative points balance for the customer.
 *
 * This class will also NOT trigger any observer events including the very important points index table.
 * Lastly, this class will NOT create or manipulate a transfer reference model.
 *
 * Don't use this class if you're unsure about any of the above. Use TBT_Rewards_Model_Transfer instead.
 *
 * @see TBT_Rewards_Model_Transfer
 */
class TBT_Rewards_Model_Transfer_Simple extends Mage_Core_Model_Abstract
{
    /**
     * Protected constructor to initiate resource model
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('rewards/transfer');
        return $this;
    }

    /**
     * Save update and created time before saving
     * @return $this
     */
    protected function _beforeSave()
    {
        $now = now();
        $this->setData('last_update_ts', $now);
        if (!$this->getData('creation_ts')) {
            $this->setData('creation_ts', $now);
        }

        parent::_beforeSave();
        return $this;
    }

    /**
     * Clearing object's data
     * @return $this
     */
    protected function _clearData()
    {
        $this->setData(array());
        $this->setOrigData();

        return $this;
    }
}
