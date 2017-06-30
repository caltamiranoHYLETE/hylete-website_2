<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */
class Vaimo_Cms_Model_Resource_Structure_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('vaimo_cms/structure');
    }

    public function addScopeFilter($storeId)
    {
        $select = $this->getSelect();

        $sql = '(main_table.scope=? AND s_store.store_id=main_table.scope_entity_id) OR
                (main_table.scope=? AND (s_store.website_id=main_table.scope_entity_id))';

        $select->joinLeft(
            array('s_store' => Mage::getSingleton('core/resource')->getTableName('core/store')),
            Mage::getResourceHelper('vaimo_cms')->quoteIntoMultiple($sql, array(
                Vaimo_Cms_Model_Fallback_Scope::STORE,
                Vaimo_Cms_Model_Fallback_Scope::WEBSITE,
                Vaimo_Cms_Model_Fallback_Scope::BASE
            )),
            array()
        );

        $sql = '(scope=? AND scope_entity_id=?) OR
                (scope=? and s_store.store_id=?) OR
                (scope=?)
                ';
        $sql = Mage::getResourceHelper('vaimo_cms')->quoteIntoMultiple($sql, array(
            Vaimo_Cms_Model_Fallback_Scope::STORE,
            $storeId,
            Vaimo_Cms_Model_Fallback_Scope::WEBSITE,
            $storeId,
            Vaimo_Cms_Model_Fallback_Scope::BASE
        ));

        $select->where($sql);

        return $this;
    }
}