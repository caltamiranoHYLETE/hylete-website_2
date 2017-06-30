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
 * @comment     This collection is only needed to make sure default values work the same way that they're implemented in: flat, getModel()->load()
 */

class Vaimo_Menu_Test_Model_Resource_Catalog_Category_TreeTest extends Vaimo_Menu_Test_BaseCase
{
    protected function _setUseFlatCategories($useFlat)
    {
        /**
         * Flat is NEVER allowed for admin store, so we switch to another store for a second
         */
        Mage::app()->setCurrentStore(key(Mage::app()->getStores()));

        $mock = $this->getMock('Mage_Catalog_Helper_Category_Flat', array('isEnabled', 'isBuilt'));

        $mock->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue($useFlat));

        $mock->expects($this->any())
            ->method('isBuilt')
            ->will($this->returnValue($useFlat));

        $this->_mockHelper('catalog/category_flat', $mock);
    }

    public function tearDown()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    }

    public function testGetCategoryCollectionShouldReturnCollectionThatHasNoOrderingDefinedWithNonFlatCategories()
    {
        $this->_setUseFlatCategories(false);

        $model = new Vaimo_Menu_Model_Resource_Catalog_Category_Tree();

        $collection = $model->getCategoryCollection(key(Mage::app()->getStores()));

        $result = (bool)$collection->getSelect()->getPart(Zend_Db_Select::ORDER);

        $this->assertFalse($result, 'Collection select has no order defined');
    }

    public function testGetCategoryCollectionShouldReturnCollectionThatHasNoOrderingDefinedWithFlatCategories()
    {
        $this->_setUseFlatCategories(true);

        $model = new Vaimo_Menu_Model_Resource_Catalog_Category_Tree();

        $collection = $model->getCategoryCollection(key(Mage::app()->getStores()));

        $result = (bool)$collection->getSelect()->getPart(Zend_Db_Select::ORDER);

        $this->assertFalse($result, 'Collection select has no order defined');
    }
}