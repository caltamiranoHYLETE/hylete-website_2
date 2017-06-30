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
 * @comment     JS class wrapper (heavily bound to js class with the same name) to introduce add-ons to category-tree.
 */

/**
 * Class Vaimo_Menu_Block_Adminhtml_Catalog_Category_Tree_Decorator
 *
 * @method setItemDecorationMap(array $map)
 * @method array getItemDecorationMap()
 * @method setIsDelayedDecoration(bool $delayed)
 * @method bool getIsDelayedDecoration()
 * @method object setCategories()
 * @method array getCategories()
 *
 * @method null _initiateDelayedExecution()
 * @method null _updateCategoryTreeItemClasses($data, $decoration, $delay)
 * @method null _updateCategoryTreeItemClassesOnTreeRender($data, $decoration, $delay)
 */
class Vaimo_Menu_Block_Adminhtml_Catalog_Category_Tree_Decorator extends Vaimo_Menu_Block_Adminhtml_Js_Lib
{
    protected $_decorators = array();
    protected $_jsClassName = 'VaimoMenuCatalogCategoryTreeDecorator';
    protected $_instanceName = 'vaimoMenuTreeExt';

    protected function _resetDecorators()
    {
        $this->_decorators = array();

        foreach ($this->getItemDecorationMap() as $key => $class) {
            $this->_decorators[] = array('decoration' => $class, 'key' => $key, 'data' => array());
        }

        return $this;
    }

    public function setCategory($category)
    {
        $this->setCategories(array($category));

        return $this;
    }

    protected function _toHtml()
    {
        $this->_resetDecorators();

        $updateOnTreeRender = false;
        if (($delay = $this->getIsDelayedDecoration()) === true) {
            $this->_initiateDelayedExecution();
            $updateOnTreeRender = true;
            $delay = false;
        }

        $decorator = Mage::helper('vaimo_menu')->getDecoratorWithPredefinedMap();
        foreach ($this->getCategories() as $category) {
            $flags = $decorator->getDecoratorFlagsForCategory($category);
            foreach ($this->_decorators as &$definition) {
                $definition['data'][$category->getId()] = $flags[$definition['key']];
                unset($definition);
            }
        }

        foreach ($this->_decorators as $definition) {
            if ($updateOnTreeRender) {
                $this->_updateCategoryTreeItemClassesOnTreeRender($definition['data'], $definition['decoration'], $delay);
            } else {
                $this->_updateCategoryTreeItemClasses($definition['data'], $definition['decoration'], $delay);
            }
        }

        return parent::_toHtml();
    }
}