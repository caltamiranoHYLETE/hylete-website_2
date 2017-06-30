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

class Vaimo_Menu_Model_Resource_Catalog_Category_Tree
{
    public function getCategoryCollection($storeId, array $attributes = array(), $ids = null)
    {
        $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();

        /** @var Mage_Catalog_Model_Resource_Category_Collection $collection */
        $collection = Mage::getModel('catalog/category')
            ->getCategories($rootCategoryId, 100, 'position', true, false);

        $oldSelect = $collection->getSelect();

        /**
         * Resetting collection to get rid of the sorting order that we set in Model level
         */
        $collection = Mage::getModel('vaimo_menu/catalog_category')->getCollection();

        $oldSelect->reset(Zend_Db_Select::ORDER);
        Mage::getResourceHelper('vaimo_menu')->copySelect($oldSelect, $collection->getSelect());

        $collection->addAttributeToSelect($attributes);

        if ($ids !== null) {
            $collection->addFieldToFilter('entity_id', array('in' => (array)$ids));
        }

        return $collection;
    }

    public function appendOrderBy($collection, $orderBy = array())
    {
        foreach ($orderBy as $attribute => $direction) {
            $collection->setOrder($attribute, $direction ? Varien_Db_Select::SQL_ASC : Varien_Db_Select::SQL_DESC);
        }
    }
}