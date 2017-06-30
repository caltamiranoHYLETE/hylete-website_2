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

class Vaimo_Menu_Test_Model_Catalog_Category_TreeTest extends Vaimo_Menu_Test_BaseCase
{
    /** @var Vaimo_Menu_Model_Catalog_Category_Tree */
    protected $_model;
    protected $_expected;

    public function _setUpDefaultMockCategories()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 3, 'parent_id' => 2),
            array('entity_id' => 6, 'parent_id' => 2),
            array('entity_id' => 4, 'parent_id' => 2),
            array('entity_id' => 7, 'parent_id' => 3),
            array('entity_id' => 5, 'parent_id' => 3)
        ));
    }

    public function setUp()
    {
        $this->_setUpDefaultMockCategories();

        $this->_expected = '{entity_id="2"; children=({entity_id="3"; children=({entity_id="7"}; {entity_id="5"})}; {entity_id="6"}; {entity_id="4"})}';
        $this->_instantiateModel();

        parent::setUp();
    }

    public function _instantiateModel()
    {
        $this->_model = new Vaimo_Menu_Model_Catalog_Category_Tree();
    }

    public function testGetCategoryTreeShouldReturnCategoryTreeStructureWhenCategoryItemsOrderedByPath()
    {
        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($this->_expected, $result);
    }

    public function testGetCategoryTreeShouldCacheTheTreeOnTheFirstTreeLoad()
    {
        $this->_setupCacheTest();

        $this->_model->getCategoryTree();
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($this->_expected, $result);

        $this->_tearDownCacheTest();
    }

    public function testGetCategoryTreeShouldCacheDifferentRecordForEachCustomerGroupIdValueInSession()
    {
        $this->_setupCacheTest();

        /**
         * Generate first cache record
         */
        $this->_instantiateModel();
        Mage::getSingleton('customer/session')->setCustomerGroupId(1);
        $this->_model->getCategoryTree();

        /**
         * Generate second cache record
         */
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $this->_instantiateModel();
        Mage::getSingleton('customer/session')->setCustomerGroupId(2);
        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));
        $expected = '{entity_id="2"}';

        $this->assertEquals($expected, $result);

        /**
         * Generate test first cache record
         */
        $this->_instantiateModel();
        Mage::getSingleton('customer/session')->setCustomerGroupId(1);
        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($this->_expected, $result);

        $this->_tearDownCacheTest();
    }

    public function testGetCategoryTreeShouldCacheTheTreeOnTheFirstLoadEvenIfNewClassIsInstantiated()
    {
        $this->_setupCacheTest();

        $this->_model->getCategoryTree();
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $this->_instantiateModel();

        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($this->_expected, $result);

        $this->_tearDownCacheTest();
    }

    public function testGetCategoryTreeShouldReloadCategoriesIfNewAttributeIsIntroducedInParameters()
    {
        $this->_model->getCategoryTree(array('attr1', 'attr2'));
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $expected = '{entity_id="2"}';

        $tree = $this->_model->getCategoryTree(array('attr3'));
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldReloadCategoriesIfSomeOldAndOneNewAttributeIsIntroducedInParameters()
    {
        $this->_model->getCategoryTree(array('attr1', 'attr2'));
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $expected = '{entity_id="2"}';

        $tree = $this->_model->getCategoryTree(array('attr1', 'attr2', 'attr3'));
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldNotReloadCategoriesIfAttributeReappearsInTheParameters()
    {
        $this->_setupCacheTest();

        $expected = '{entity_id="2"}';
        $this->_model->getCategoryTree(array('attr1', 'attr2'));
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $this->_model->getCategoryTree(array('attr3'));
        $this->_setUpMockCategories(array(array('entity_id' => 3, 'parent_id' => 1)));

        $tree = $this->_model->getCategoryTree(array('attr2'));
        $result = $tree[0]->serialize(array('entity_id'));

        $this->assertEquals($expected, $result);

        $this->_tearDownCacheTest();
    }

    public function testGetCategoryTreeShouldReturnUpdatedTreeAfterMenuCacheTagIsCleaned()
    {
        $this->_model->getCategoryTree();
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $expected = '{entity_id="2"}';

        Mage::app()->cleanCache(Vaimo_Menu_Model_Navigation::CACHE_TAG);

        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldReturnUpdatedTreeAfterMenuCacheTagIsCleanedEvenIfNewClassIsInstantiated()
    {
        $this->_model->getCategoryTree();
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $expected = '{entity_id="2"}';
        $this->_instantiateModel();

        Mage::app()->cleanCache(Vaimo_Menu_Model_Navigation::CACHE_TAG);
        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldReturnCategoryTreeStructureWhenCategoryItemsNotOrderedByPath()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 3, 'parent_id' => 2),
            array('entity_id' => 5, 'parent_id' => 3),
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 4, 'parent_id' => 2),
            array('entity_id' => 6, 'parent_id' => 2),
            array('entity_id' => 7, 'parent_id' => 3)
        ));

        $expected = '{entity_id="2"; children=({entity_id="3"; children=({entity_id="5"}; {entity_id="7"})}; {entity_id="4"}; {entity_id="6"})}';

        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldExcludeItemsThatExclusivelyHaveIsActiveFlagSetToFalse()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 3, 'parent_id' => 2),
            array('entity_id' => 5, 'parent_id' => 3, 'is_active' => false),
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 4, 'parent_id' => 2),
            array('entity_id' => 6, 'parent_id' => 2, 'is_active' => false),
            array('entity_id' => 7, 'parent_id' => 3)
        ));

        $expected = '{entity_id="2"; children=({entity_id="3"; children=({entity_id="7"})}; {entity_id="4"})}';

        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldReturnSubItemsThatHaveTheirParentsSetToInActiveAsAdditionalTreeRoots()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 3, 'parent_id' => 2, 'is_active' => false),
            array('entity_id' => 5, 'parent_id' => 3),
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 4, 'parent_id' => 2),
            array('entity_id' => 6, 'parent_id' => 2),
            array('entity_id' => 7, 'parent_id' => 3)
        ));

        $expected = array(
            '{entity_id="5"}',
            '{entity_id="2"; children=({entity_id="4"}; {entity_id="6"})}',
            '{entity_id="7"}',
        );

        $tree = $this->_model->getCategoryTree();

        foreach ($tree as &$item) {
            $item = $item->serialize(array('entity_id', 'children'));
        }

        $this->assertEquals($expected, $tree);
    }

    public function testGetCategoryTreeShouldReturnCategoryTreeWithMultipleRootsIfItemsReferToParentsNotInTheArray()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 4, 'parent_id' => 2),
            array('entity_id' => 6, 'parent_id' => 2),
            array('entity_id' => 100, 'parent_id' => 10),
            array('entity_id' => 9, 'parent_id' => 100)
        ));

        $expectedRoots = array(
            '{entity_id="2"; children=({entity_id="4"}; {entity_id="6"})}',
            '{entity_id="100"; children=({entity_id="9"})}'
        );

        $tree = $this->_model->getCategoryTree();

        foreach ($expectedRoots as $expectedRoot) {
            $root = array_shift($tree);
            $result = $root->serialize(array('entity_id', 'children'));
            $this->assertEquals($expectedRoot, $result);
        }
    }

    public function testGetCategoryTreeWithAttributesSetToNullShouldReturnCategories()
    {
        $this->_setUpMockCategories(array(array('entity_id' => 2)));
        $expectedRoot = '{entity_id="2"}';

        $tree = $this->_model->getCategoryTree(null);
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expectedRoot, $result);
    }

    public function testGetCategoryTreeShouldGetUrlKeyFromLastPartOfUrlIfUrlKeyIsNull()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 3, 'parent_id' => 2, 'url' => 'http://www.someaddress.com/something/test2'),
            array('entity_id' => 5, 'parent_id' => 2, 'url_key' => 'test1'),
            array('entity_id' => 6, 'parent_id' => 2, 'url' => 'http://www.someaddress.com/something/test3/'),
            array('entity_id' => 7, 'parent_id' => 2)
        ));

        $expected = '{entity_id="2"; children=({entity_id="3"; url_key="test2"}; {entity_id="5"; url_key="test1"}; {entity_id="6"; url_key="test3"}; {entity_id="7"})}';

        $tree = $this->_model->getCategoryTree(null);
        $result = $tree[0]->serialize(array('entity_id', 'children', 'url_key'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldCopyUrlPathToUrlKeyIfUrlKeyIsNull()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'url_path' => 'test5')
        ));

        $expected = '{url_key="test5"}';

        $tree = $this->_model->getCategoryTree(null);
        $result = $tree[0]->serialize(array('url_key'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldStringUrlParameters()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'url' => 'http://some.thing/cat6?param=1&SID=123')
        ));

        $expected = '{url="http://some.thing/cat6"}';

        $tree = $this->_model->getCategoryTree(null);
        $result = $tree[0]->serialize(array('url'));

        $this->assertEquals($expected, $result);
    }

    public function testGetCategoryTreeShouldFetchCategoriesWithAttributesThatAreDefinedAsWidgetAttributesInTheLayout()
    {
        $widgetAttribute = 'test_widget';

        Mage::reset();
        $widgetResourceStub = $this->getMock('Vaimo_Menu_Model_Resource_Catalog_Category_Widget');
        $widgetResourceStub->expects($this->any())
            ->method('getWidgetAttributes')
            ->will($this->returnValue(array(
                array('attribute_code' => $widgetAttribute)
            )));
        $widgetResourceStub->expects($this->any())
            ->method('getWidgetBlockInfoForBlockReferences')
            ->will($this->returnValue(array(
                '1' => array('reference' => 'test.menublock', 'name' => 'test')
            )));
        $this->_mockResourceSingleton('vaimo_menu/catalog_category_widget', $widgetResourceStub);

        $updates = Mage::app()->getConfig()->getNode('frontend/layout/updates');

        $testUpdate = new Mage_Core_Model_Config_Element('<vaimo_menu_test><file>test/vaimo_menu_test.xml</file></vaimo_menu_test>');
        $updates->appendChild($testUpdate);

        $stub = $this->getMock('Vaimo_Menu_Model_Resource_Catalog_Category_Tree');

        $callArgs = array();
        $stub->expects($this->any())
            ->method('getCategoryCollection')
            ->will($this->returnCallback(function($storeId, $attributes) use (&$callArgs) {
                $callArgs = $attributes;
                return array();
            }));
        $this->_mockResourceSingleton('vaimo_menu/catalog_category_tree', $stub);

        sleep(1);

        $this->_model->getCategoryTree();

        $this->assertContains($widgetAttribute, $callArgs);
    }

    public function testGetCategoryTreeShouldReturnUpdatedTreeOnEveryCallWhenCacheIsDisabled()
    {
        Mage::app()->getCacheInstance()->banUse(Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME);

        $this->_model->getCategoryTree();
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $expected = '{entity_id="2"}';

        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expected, $result);

        Mage::reset();
    }

    public function testGetCategoryTreeShouldReturnLatestTreeOnNewModelInstanceWheCacheIsDisabled()
    {
        Mage::app()->getCacheInstance()->banUse(Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME);

        $this->_model->getCategoryTree();
        $this->_setUpMockCategories(array(array('entity_id' => 2, 'parent_id' => 1)));
        $expected = '{entity_id="2"}';

        $this->_instantiateModel();

        $tree = $this->_model->getCategoryTree();
        $result = $tree[0]->serialize(array('entity_id', 'children'));

        $this->assertEquals($expected, $result);

        Mage::reset();
    }
}