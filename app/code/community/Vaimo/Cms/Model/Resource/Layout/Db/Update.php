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

class Vaimo_Cms_Model_Resource_Layout_Db_Update extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('core/layout_update', 'layout_update_id');
    }

    protected function _getLayoutDbUpdateSelect()
    {
        $resource = Mage::getModel('core/resource');
        $select = $resource->getConnection('core_read')->select()
            ->from(array('layout_update' => $resource->getTableName('core/layout_update')), array('xml'))
            ->join(array('layout_link' => $resource->getTableName('widget/widget_instance_page_layout')),
                'layout_link.layout_update_id=layout_update.layout_update_id', array('page_id'))
            ->join(array('widget_usage' => $resource->getTableName('widget/widget_instance_page')),
                'widget_usage.page_id=layout_link.page_id', array('instance_id', 'block_reference'))
            ->join(array('widget_instance' => $resource->getTableName('widget/widget_instance')),
                'widget_instance.instance_id=widget_usage.instance_id', 'instance_type');

        return $select;
    }

    public function getWidgetDbLayoutUpdatesForHandles($handles)
    {
        $read = $this->_getReadAdapter();
        $select = $this->_getLayoutDbUpdateSelect();

        $select->where('layout_update.handle IN (?)', $handles);

        return $read->fetchAll($select);
    }
}