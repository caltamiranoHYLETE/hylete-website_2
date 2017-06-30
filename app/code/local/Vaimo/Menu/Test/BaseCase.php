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

class Vaimo_Menu_Test_BaseCase extends PHPUnit_Framework_TestCase
{
    public $mocks = array();
    protected $_resourceSingletonKeyPrefix = '_resource_singleton/';
    protected $_modelSingletonKeyPrefix = '_singleton/';
    protected $_modelHelperKeyPrefix = '_helper/';
    protected $_factorySingletons = array();
    protected $_originalStoreGroup;
    protected $_rootCategoryId = null;
    protected $_currentCategory;

    protected function _createStoreGroupMock()
    {
        $this->_originalStoreGroup = Mage::app()->getStore()->getGroup();
        $groupMock = $this->getMockBuilder('Mage_Core_Model_Store_Group')
            ->disableOriginalConstructor()
            ->getMock(array('getRootCategoryId'));

        $group = &$this->_rootCategoryId;
        $groupMock->expects($this->any())
            ->method('getRootCategoryId')
            ->will($this->returnCallback(
                function() use (&$group) {
                    return $group;
                })
            );

        Mage::app()->getStore()->setGroup($groupMock);
    }

    public function setUp()
    {
        if (!Mage::registry('current_category')) {
            $this->_currentCategory = new Varien_Object();
            Mage::register('current_category', new Varien_Object());
        }

        $this->_currentCategory = Mage::registry('current_category');

        $this->_createStoreGroupMock();

        $this->_enableCache(Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME);

        Mage::app()->cleanCache(Vaimo_Menu_Model_Navigation::CACHE_TAG);
    }

    protected function _enableCache($cacheType)
    {
        $cacheMock = $this->getMockBuilder('Mage_Core_Model_Resource_Cache')
            ->disableOriginalConstructor()
            ->getMock(array('getAllOptions'));

        $options = array($cacheType => 1);

        $cacheMock->expects($this->any())
            ->method('getAllOptions')
            ->will($this->returnValue($options));

        $this->_mockResourceSingleton('core/cache', $cacheMock);

        Mage::app()->getCacheInstance()->remove(Mage_Core_Model_Cache::OPTIONS_CACHE_ID);
    }

    public function _setUpDefaultMockCategories()
    {
    }

    protected function _setupCacheTest($cacheFlag = Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME)
    {
        Mage::app()->getCacheInstance()->remove(Mage_Core_Model_Cache::OPTIONS_CACHE_ID);
        Mage::reset();
        $this->_enableCache($cacheFlag);
        $this->_setUpDefaultMockCategories();
    }

    protected function _tearDownCacheTest()
    {
        Mage::reset();
    }

    public function tearDown()
    {
        Mage::unregister('_singleton/core/resource');

        if ($this->_currentCategory) {
            $this->_currentCategory->setData(array());
        }

        Mage::app()->getStore()->setGroup($this->_originalStoreGroup);

        foreach ($this->mocks as $resourceKey) {
            Mage::unregister($this->_modelSingletonKeyPrefix . $resourceKey);
            Mage::unregister($this->_resourceSingletonKeyPrefix . $resourceKey);
            Mage::unregister($this->_modelHelperKeyPrefix . $resourceKey);
        }

        $this->mocks = array();
        Mage::app()->cleanCache(array(Vaimo_Menu_Model_Navigation::CACHE_TAG));
    }

    public function _setUpMockCategories($categories, $resource = 'vaimo_menu/catalog_category_tree')
    {
        foreach ($categories as &$category) {
            $category = new Varien_Object($category);
        }

        $stub = $this->getMock('Vaimo_Menu_Model_Resource_Catalog_Category_Tree');
        $stub->expects($this->any())
            ->method('getCategoryCollection')
            ->will($this->returnValue($categories));

        $this->_mockResourceSingleton($resource, $stub);

        return $stub;
    }

    protected function _mockResourceSingleton($modelName, $stub)
    {
        $this->_mockSingleton($this->_resourceSingletonKeyPrefix, $modelName, $stub);
    }

    protected function _mockModelSingleton($modelName, $stub)
    {
        $this->_mockSingleton($this->_modelSingletonKeyPrefix, $modelName, $stub);
    }

    protected function _mockHelper($modelName, $stub)
    {
        $this->_mockSingleton($this->_modelHelperKeyPrefix, $modelName, $stub);
    }

    protected function _mockSingleton($prefix, $modelName, $stub)
    {
        $this->mocks[$modelName] = $modelName;

        $resourceKey = $prefix . $modelName;
        Mage::unregister($resourceKey);
        Mage::register($resourceKey, $stub);
    }

    protected function _getFactoryWithModelMocks($models)
    {
        $factory = $this->getMock('Vaimo_Menu_Model_Core_Factory');

        $factory->expects($this->any())
            ->method('getModel')
            ->will($this->returnCallback(
                function($key, $arguments) use ($models) {
                    if (isset($models[$key])) {
                        return $models[$key];
                    }

                    return Mage::getSingleton('vaimo_menu/core_factory')->getModel($key, $arguments);
                }
            ));

        $test = $this;
        $factory->expects($this->any())
            ->method('getSingleton')
            ->will($this->returnCallback(
                function($key, $arguments) use ($models, $test) {
                    $test->mocks[$key] = $key;

                    if (isset($models[$key])) {
                        return $models[$key];
                    }

                    return Mage::getSingleton('vaimo_menu/core_factory')->getSingleton($key, $arguments);
                }
            ));

        return $factory;
    }
}
