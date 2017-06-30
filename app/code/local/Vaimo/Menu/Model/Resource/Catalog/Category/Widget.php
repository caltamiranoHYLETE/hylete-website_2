<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Menu_Model_Resource_Catalog_Category_Widget
{
    const FRONTEND_INPUT_TYPE = 'widget';

    protected function _getLayoutDbUpdateSelect()
    {
        $resource = Mage::getModel('core/resource');
        $select = $resource->getConnection('core_read')->select()
            ->from(array('layout_update' => $resource->getTableName('core/layout_update')), array('xml'))
            ->join(array('layout_link' => $resource->getTableName('widget/widget_instance_page_layout')),
                'layout_link.layout_update_id=layout_update.layout_update_id', array())
            ->join(array('widget_usage' => $resource->getTableName('widget/widget_instance_page')),
                'widget_usage.page_id=layout_link.page_id', array('instance_id', 'block_reference'));

        return $select;
    }

    public function getWidgetBlockInfoForBlockReferences(array $references)
    {
        $readAdapter = Mage::getModel('core/resource')->getConnection('core_read');
        $select = $this->_getLayoutDbUpdateSelect();
        $select->where($readAdapter->quoteInto("widget_usage.block_reference IN (?)", $references));
        $result = $readAdapter->fetchAll($select);

        return $this->_processUpdateXml($result);
    }

    /**
     * NOTE: We're skipping all updates that do not match normal widget layout update pattern on purpose as there
     * can be updates that have been created by some other process or extension
     *
     * @param $results
     * @return array
     */
    protected function _processUpdateXml($results)
    {
        $reference = array();

        foreach ($results as $row) {
            $updateStr = '<xml>' . $row['xml'] . '</xml>';
            $updateXml = simplexml_load_string($updateStr);

            try {
                $hash = (string)$updateXml->reference->block->attributes()->name;
                $reference[$row['instance_id']] = array(
                    'reference' => $row['block_reference'],
                    'name' => $hash,
                    'xml' => $row['xml']
                );
            } catch (Exception $e) {}
        }

        return $reference;
    }

    public function getWidgetAttributes()
    {
        $attributes = Mage::getResourceModel('catalog/category_attribute_collection');
        $attributes->setFrontendInputTypeFilter(self::FRONTEND_INPUT_TYPE);
        return $attributes;
    }
}