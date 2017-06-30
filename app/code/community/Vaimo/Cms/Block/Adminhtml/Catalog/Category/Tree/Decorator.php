<?php
/**
 * Copyright (c) 2009-2014 Vaimo Group
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

/**
 * Class Vaimo_Cms_Block_Adminhtml_Catalog_Category_Tree_Decorator
 *
 * @method _initiateTreeNodesAppearances(array $nodes);
 */
class Vaimo_Cms_Block_Adminhtml_Catalog_Category_Tree_Decorator extends Vaimo_Cms_Block_Js_Lib
{
    protected $_jsClassName = 'categoryTreeDecorator';

    protected function _init()
    {
        $this->_initiateTreeNodesAppearances($this->getTreeNodesAppearances());
    }

    public function getCategories()
    {
        $categories = $this->getFactory()->getModel('catalog/category')
            ->getCollection()
            ->setStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->addAttributeToSelect('page_type');

        return $categories;
    }

    /**
     * Return an array with tree nodes appearances containing css classes depending on the category page type.
     * Used for updating the appearance of the category tree in admin.
     *
     * @return array
     */
    public function getTreeNodesAppearances()
    {
        $categories = $this->getCategories()->getItems();
        $currentCategory = Mage::registry('current_category');
        $treeNodes = array();

        foreach($categories as $category) {
            if ($category->getLevel() == '1') {
                $treeNodes[$category->getId()] = Vaimo_Cms_Helper_Data::TREE_ICON_STARTPAGE;
            } else if ($category->getPageType() == Vaimo_Cms_Model_Page_Type::TYPE_CMS) {
                $treeNodes[$category->getId()] = Vaimo_Cms_Helper_Data::TREE_ICON_CONTENT;
            } else if ($currentCategory->getId() == $category->getId()) {
                $treeNodes[$category->getId()] = '';
            }
        }

        return $treeNodes;
    }

    public function getTreeNodesAppearancesAsJson()
    {
        return json_encode($this->getTreeNodesAppearances());
    }
}