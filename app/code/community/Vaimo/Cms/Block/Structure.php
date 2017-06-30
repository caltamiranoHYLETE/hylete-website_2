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

class Vaimo_Cms_Block_Structure extends Vaimo_Cms_Block_Abstract
{
    const NESTED_ROW_CLASS = 'vcms-nested-structure-row';

    protected $_template = 'vaimo/cms/structure.phtml';

    public function getItemHtml($item)
    {
        if (isset($item['rows'])) {
            return $this->_rowsToHtml($item['rows']);
        }

        try {
            if (!isset($item['name'])) {
                throw Mage::exception('Vaimo_Cms', 'Could not resolve the widget block name in layout',
                    Vaimo_Cms_Exception::WIDGET_NAME_MISSING);
            }

            $block = $this->getChild($item['name']);

            if (!$block) {
                throw Mage::exception('Vaimo_Cms', 'Could not find the widget block with specified name in layout',
                    Vaimo_Cms_Exception::WIDGET_NOT_FOUND);
            }

            return $block->toHtml();
        } catch (Exception $exception) {
            return $this->_generateErrorOutput('page-' . $item['widget_page_id'], $exception);
        }
    }

    public function getRowClass()
    {
        if ($this->hasRows()) {
            return self::NESTED_ROW_CLASS;
        }

        return '';
    }

    public function getRows()
    {
        $rows = array();

        if ($this->hasRows()) {
            $rows = $this->getData('rows');
        } else if ($this->hasGrid()) {
            $rows = $this->getGrid();
        }

        if (!array_filter($rows)) {
            return array();
        }

        if ($rows) {
            return $this->_prepareRows($rows);
        }

        return $rows;
    }

    public function _rowsToHtml($rows)
    {
        if ($rows && is_string(key($rows[0]))) {
            $rows = array($rows);
        }

        $this->setRows($rows);

        return $this->toHtml();
    }

    protected function _toHtml()
    {
        if (!$this->getChild()) {
            return '';
        }

        try {
            return parent::_toHtml();
        } catch (Exception $exception) {
            return $this->_generateErrorOutput('structure-' . $this->getStructureId(), $exception);
        }
    }

    protected function _generateErrorOutput($pageId, Exception $exception)
    {
        Mage::logException($exception);

        return $this->getFactory()->getHelper('vaimo_cms')
            ->getWidgetErrorHtml($pageId, $exception);
    }

    protected function _prepareRows($rows)
    {
        $bootstrapHelper = $this->getFactory()->getHelper('vaimo_cms/bootstrap');

        foreach ($rows as &$row) {
            if (!array_filter($row)) {
                continue;
            }

            $row = $bootstrapHelper->addClasses($row, Vaimo_Cms_Helper_Bootstrap::ALL);
        }

        return $rows;
    }

    public function prepareGrid($structureDataWithLayoutNames)
    {
        $factory = $this->getFactory();

        $gridHelper = $factory->getSingleton('vaimo_cms/grid');
        $layoutHelper = $factory->getHelper('vaimo_cms/layout');

        $names = array();
        foreach ($structureDataWithLayoutNames as $structureItem) {
            if (!isset($structureItem['name'])) {
                continue;
            }

            $names[$structureItem['name']] = array(
                'vaimo_cms_structure_item_configuration' => $structureItem
            );
        }

        $transport = new Varien_Object(array(
            'structure_items' => $structureDataWithLayoutNames
        ));

        Mage::dispatchEvent('vaimo_cms_prepare_structure_grid_before', array(
            'transport' => $transport
        ));

        $structureDataWithLayoutNames = $transport->getStructureItems();

        $this->setGrid($gridHelper->flatGridDefinitionToNestedRows($structureDataWithLayoutNames));

        $layoutHelper->adoptBlocks($this->getParentBlock(), $this, $names);
    }

    /**
     * @deprecated Function renamed to getItemHtml to match the getChild/getChildHtml pattern used by Magento
     *
     * @param $item
     * @return string
     */
    public function rowItemToHtml($item)
    {
        return $this->getItemHtml($item);
    }
}