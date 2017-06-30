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

class Vaimo_Cms_Model_Resource_Structure extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
    * Resource initialization
    */
    protected function _construct()
    {
        $this->_init('vaimo_cms/structure', 'structure_id');
    }

    public function getStoreIdsForPageIds($pageIds)
    {
        $resource = Mage::getModel('core/resource');

        $select = $resource->getConnection('core_read')->select()
            ->from(array('widget_usage' => $resource->getTableName('widget/widget_instance_page')), array('page_id'))
            ->where('page_id IN (?)', $pageIds)
            ->join(array('widget_instance' => $resource->getTableName('widget/widget_instance')),
                'widget_instance.instance_id=widget_usage.instance_id', 'store_ids');

        /** @var Magento_Db_Adapter_Pdo_Mysql $read */
        $read = $this->_getReadAdapter();

        $result = $read->fetchAssoc($select, 'store_ids');

        foreach ($result as &$item) {
            $item = explode(',', $item['store_ids']);
        }

        return $result;
    }

    public function getWidgetTypeAndParametersForPageIds($pageIds)
    {
        $resource = Mage::getModel('core/resource');

        $select = $resource->getConnection('core_read')->select()
            ->from(array('widget_usage' => $resource->getTableName('widget/widget_instance_page')), array('page_id'))
            ->where('page_id IN (?)', $pageIds)
            ->join(array('widget_instance' => $resource->getTableName('widget/widget_instance')),
                'widget_instance.instance_id=widget_usage.instance_id', array(
                    'type' => 'instance_type',
                    'parameters' => 'widget_parameters'
                ));

        /** @var Magento_Db_Adapter_Pdo_Mysql $read */
        $read = $this->_getReadAdapter();

        $result = $read->fetchAssoc($select, 'store_ids');

        foreach ($result as &$item) {
            unset($item['page_id']);
        }

        return $result;
    }
}