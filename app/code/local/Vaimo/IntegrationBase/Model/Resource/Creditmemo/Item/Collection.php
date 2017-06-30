<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Resource_Creditmemo_Item_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_creditmemo;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/creditmemo_item');
    }

    public function setCreditmemo($creditmemo)
    {
        $this->_creditmemo = $creditmemo;
        $creditmemoId = $creditmemo->getId();

        if ($creditmemoId) {
            $this->addFieldToFilter('parent_id', $creditmemo->getId());
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this as $item) {
            if ($this->_creditmemo) {
                $item->setCreditmemo($this->_creditmemo);
            }
        }

        $this->resetItemsDataChanged();

        return $this;
    }
}