<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

class Hylete_Rewards_Block_Customer_Transfers_Earnings_Grid extends TBT_Rewards_Block_Customer_Transfers_Earnings_Grid
{

    public function _prepareColumns()
    {
        parent::_prepareColumns();
        if (!Mage::app()->getStore()->isAdmin()) {
            $this->removeColumn('status_id');
        }
    }
}
