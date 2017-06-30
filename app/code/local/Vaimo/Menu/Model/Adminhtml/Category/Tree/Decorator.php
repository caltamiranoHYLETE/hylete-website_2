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

/**
 * Class Vaimo_Menu_Model_Adminhtml_Category_Tree_Decorator
 *
 * @method object setMap(array $map)
 * @method array getMap()
 */
class Vaimo_Menu_Model_Adminhtml_Category_Tree_Decorator extends Vaimo_Menu_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->_init('vaimo_menu/adminhtml_category_tree_decorator');
    }

    protected function _getDecoratorBlock($blockName)
    {
        $decorator = Mage::app()->getLayout()->getBlock($blockName);

        return $decorator->setItemDecorationMap($this->getMap());
    }


    public function initiateForCategory(Mage_Catalog_Model_Category $category, $blockName)
    {
        $block = $this->_getDecoratorBlock($blockName);
        $block->setCategory($category)->setIsDelayedDecoration(100);

        return $block;
    }

    public function initiateForCategories($ids, $blockName, $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $collection = $this->_getCategoryCollection($ids, $storeId);
        $block = $this->_getDecoratorBlock($blockName);

        $block->setCategories($collection)
            ->setIsDelayedDecoration(true);

        return $block;
    }

    protected function _getCategoryCollection($ids, $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $attributeCodes = array_keys($this->getMap());
        $categories = $this->getResource()->getCategoryCollection($storeId, $ids, $attributeCodes);

        return $categories;
    }

    protected function _getDecoratorValuesForCategory($category)
    {
        $flags = $this->getDecoratorFlagsForCategory($category);
        $map = $this->getMap();
        $values = array();

        foreach ($map as $attributeCode => $decoration) {
            if (!is_array($decoration)) {
                $values[$attributeCode] = $flags[$attributeCode] ? $decoration : '';
            } else {
                $option = $flags[$attributeCode];
                $values[$attributeCode] = isset($decoration[$option]) ? $decoration[$option] : null;
            }
        }

        return $values;
    }

    public function getDecoratorFlagsForCategory($category)
    {
        $map = $this->getMap();
        $flags = array_fill_keys(array_keys($map), '');

        foreach ($map as $attributeCode => $decoration) {
            if (!is_array($decoration)) {
                $flags[$attributeCode] = (int)$category->getData($attributeCode);
            } else {
                $flags[$attributeCode] = $category->getData($attributeCode);
            }
        }

        return $flags;
    }

    /**
     * @param array $tree Flat list of tree items
     */
    public function applyToTree(&$tree)
    {
        $itemsById = array();
        Mage::helper('vaimo_menu/tree')->treeWalkUpdate($tree, function(&$item) use (&$itemsById) {
            $itemsById[$item['id']] = &$item;
        });

        if ($category = Mage::registry('current_category')) {
            $collection = $this->_getCategoryCollection(array_keys($itemsById), $category->getStoreId());
        } else {
            $collection = $this->_getCategoryCollection(array_keys($itemsById));
        }

        foreach ($collection as $category) {
            $item = &$itemsById[$category->getEntityId()];
            $values = $this->_getDecoratorValuesForCategory($category);

            foreach ($values as $value) {
                if ($value) {
                    if (!is_array($value)) {
                        if (!isset($item['cls'])) {
                            $item['cls'] = '';
                        }

                        $item['cls'] .= ($item['cls'] ? ' ' : '') . $value;
                    } else {
                        $item['vm_style'] = $value;
                    }
                }
            }

            unset($item);
        }
    }
}