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

class Vaimo_Menu_Test_Block_Navigation_Type_BaseTest extends Vaimo_Menu_Test_Block_Navigation_Type_BaseCase
{
    protected $_model;
    protected $_container = 'vaimo/menu/test/container.phtml';
    protected $_itemTemplate = 'vaimo/menu/test/item.phtml';
    protected $_itemGroupFlat = 'vaimo/menu/test/flat_group.phtml';

    public function setUp()
    {
        parent::setUp();

        $this->_instantiateModel();
    }

    public function _instantiateModel($type = 'test_type', $configuration = null)
    {
        if ($configuration === null) {
            $configuration = array(
                0 => array(
                    'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                    'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
                ),
                1 => array(
                    'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                    'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE
                ),
                2 => array(
                    'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL
                )
            );
        }

        $this->_model = new Vaimo_Menu_Block_Navigation_Type_Base(array('_type_configuration' => $configuration));
        $this->_model->setType($type);
        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(2);
        $this->_model->setItemTemplate($this->_itemTemplate);
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'main');
        $this->_model->setTemplate($this->_container);

        return $this->_model;
    }

    public function testUpdateTypeConfigShouldUpdateTypeConfigCorrectlyWhenTypeConfigEmpty()
    {
        $this->_instantiateModel('test', array());

        $this->_model->updateTypeConfig(array(
            0 => array(
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE
            )
        ));

        $config = $this->_model->getMenuTypeConfig();

        $this->assertEquals(array(
            0 => new Varien_Object(array(
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE
            ))
        ), $config);
    }

    public function testUpdateTypeConfigShouldMergeChangesPerLevel()
    {
        $this->_model->updateTypeConfig(array(
            0 => array(
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE
            ),
            2 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL
            )
        ));

        $config = $this->_model->getMenuTypeConfig();

        $this->assertEquals(array(
            0 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE
            )),
            1 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE
            )),
            2 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL
            ))
        ), $config);
    }

    public function testUpdateTypeConfigShouldRemoveKeysOnCertainLevelIfKeyIsSetToFalse()
    {
        $this->_model->updateTypeConfig(array(
            1 => array(
                'children' => false
            )
        ));

        $config = $this->_model->getMenuTypeConfig();

        $this->assertEquals(array(
            0 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
            )),
            1 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
            )),
            2 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL
            ))
        ), $config);
    }

    public function testUpdateTypeConfigShouldAddNewLevelIfLevelNotPresentInTypeConfig()
    {
        $this->_model->updateTypeConfig(array(
            3 => array(
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE,
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL
            )
        ));

        $config = $this->_model->getMenuTypeConfig();

        $this->assertEquals(array(
            0 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
            )),
            1 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE
            )),
            2 => new Varien_Object(array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL
            )),
            3 => new Varien_Object(array(
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE,
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL
            ))
        ), $config);
    }

    /**
     * @expectedException       Vaimo_Menu_Exception
     * @expectedExceptionCode   Vaimo_Menu_Exception::INVALID_MENU_TYPE_CONFIGURATION
     */
    public function testUpdateTypeConfigShouldRaiseAnExceptionIfGapInTypeConfig()
    {
        $this->_model->updateTypeConfig(array(
            10 => array(
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE,
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL
            )
        ));
    }

    public function testRenderMenuShouldReturnOnlyTreeThatHasARootWithIdThatMatchesStoreRootCategoryId()
    {
        $this->_model->setStartLevel(1);
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3'),
            array('entity_id' => 5, 'parent_id' => 22, 'name' => 'item5'),
            array('entity_id' => 6, 'parent_id' => 5, 'name' => 'item6'),
        ));

        $expected = array(
            array("item" => "item5",
                "children" => array(
                    array("item" => "item6")
                )
            )
        );

        $this->_rootCategoryId = 5;

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnAllTreeRootsIfRootCategoryIdIsNull()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3'),
            array('entity_id' => 5, 'parent_id' => 22, 'name' => 'item5'),
            array('entity_id' => 6, 'parent_id' => 5, 'name' => 'item6'),
        ));

        $expected = array(
            array("item" => "item2",
                "children" => array(
                    array("item" => "item3")
                )
            ),
            array("item" => "item5",
                "children" => array(
                    array("item" => "item6")
                )
            )
        );

        $this->_rootCategoryId = null;

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldUseCachedCategoryTreeWhenCacheIsEnabled()
    {
        Mage::reset();
        $this->_enableCache(Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME);

        $this->_model->setStartLevel(1);
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3')
        ));
        $this->_model->renderMenu();
        $this->_setUpMockCategories(array(array('entity_id' => 6, 'parent_id' => 1, 'name' => 'item6')));
        $expected = array(array("item" => "item2", "children" => array(array("item" => "item3"))));

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldCreateDifferentCacheRecordsForDifferentCustomerGroups()
    {
        Mage::reset();
        $expected = array(array("item" => "item2", "children" => array(array("item" => "item3"))));

        $this->_enableCache(Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME);

        $this->_instantiateModel();
        $this->_model->setStartLevel(1);
        Mage::getSingleton('customer/session')->setCustomerGroupId(1);
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3')
        ));
        $this->_model->renderMenu();

        $this->_instantiateModel();
        Mage::getSingleton('customer/session')->setCustomerGroupId(2);
        $this->_setUpMockCategories(array(array('entity_id' => 6, 'parent_id' => 1, 'name' => 'item6')));
        $result = $this->_model->renderMenu();

        $this->assertNotEquals(json_encode($expected), $this->_repairJson($result));

        $this->_instantiateModel();
        Mage::getSingleton('customer/session')->setCustomerGroupId(1);
        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testToHtmlShouldCreateDifferentCacheRecordsForSecureAndInSecureStorePages()
    {
        Mage::reset();
        $expected = array(array("item" => "item2", "children" => array(array("item" => "item3"))));

        $this->_enableCache(Mage_Core_Block_Template::CACHE_GROUP);

        $this->_instantiateModel();
        $this->_model->setDataCacheLifetime(1);
        $this->_model->setBlockCacheLifetime(1000);
        $this->_model->setStartLevel(1);

        $_SERVER['HTTPS'] = 1;

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3')
        ));
        $this->_model->toHtml();

        $this->_instantiateModel();
        $this->_model->setDataCacheLifetime(1);
        $this->_model->setBlockCacheLifetime(1000);
        $this->_model->setStartLevel(1);

        $_SERVER['HTTPS'] = 0;

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item4'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item5')
        ));
        $result = $this->_model->toHtml();

        $this->assertNotEquals(json_encode($expected), $this->_repairJson($result));

        $this->_instantiateModel();
        $this->_model->setDataCacheLifetime(1);
        $this->_model->setBlockCacheLifetime(1000);
        $this->_model->setStartLevel(1);

        $_SERVER['HTTPS'] = 1;

        $result = $this->_model->toHtml();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));

        $_SERVER['HTTPS'] = 0;
    }

    public function testToHtmlShouldCreateDifferentCacheRecordsForSecureAndInSecureStorePagesEvenWhenSetDataCacheKeyUsed()
    {
        Mage::reset();
        $expected = array(array("item" => "item2", "children" => array(array("item" => "item3"))));

        $this->_enableCache(Mage_Core_Block_Template::CACHE_GROUP);

        $this->_instantiateModel();
        $this->_model->setDataCacheLifetime(1);
        $this->_model->setBlockCacheLifetime(1000);
        $this->_model->setStartLevel(1);
        $this->_model->setDataCacheKey('test');

        $_SERVER['HTTPS'] = 1;

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3')
        ));
        $this->_model->toHtml();

        $this->_instantiateModel();
        $this->_model->setDataCacheLifetime(1);
        $this->_model->setBlockCacheLifetime(1000);
        $this->_model->setStartLevel(1);
        $this->_model->setDataCacheKey('test');

        $_SERVER['HTTPS'] = 0;

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item4'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item5')
        ));
        $result = $this->_model->toHtml();

        $this->assertNotEquals(json_encode($expected), $this->_repairJson($result));

        $this->_instantiateModel();
        $this->_model->setDataCacheLifetime(1);
        $this->_model->setBlockCacheLifetime(1000);
        $this->_model->setStartLevel(1);
        $this->_model->setDataCacheKey('test');

        $_SERVER['HTTPS'] = 1;

        $result = $this->_model->toHtml();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));

        $_SERVER['HTTPS'] = 0;
    }

    public function testRenderMenuShouldGenerateMenuStructureBasedOnSetStartLevel()
    {
        $this->_model->setStartLevel(3);
        $this->_model->setDisplayLevels(1);
        $expected = array(array("item" => "item7"), array("item" => "item5"));

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnEmptyMenuIfDisplayLevelsIsSetToShowNothing()
    {
        $this->_model->setStartLevel(2);
        $this->_model->setDisplayLevels(0);
        $expected = array();

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderEverythingIfDisplayLevelsIsNotSet()
    {
        $this->_model->setStartLevel(2);
        $this->_model->unsDisplayLevels();

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($this->_expectedTree), $this->_repairJson($result));
    }

    public function testRenderMenuShouldIncludeAttributeWidgetHtml()
    {
        $widgetAttribute = 'test_widget';
        $menuBlockAlias = 'my_menu';
        $widgetOutput = 'test widget output html';
        $widgetNameInLayout = md5(rand());

        $this->_model->setStartLevel(1);
        $this->_model->setItemTemplate('vaimo/menu/test/item_widget.phtml');
        $this->_model->setNameInLayout($menuBlockAlias);

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', $widgetAttribute  => '1')
        ));

        $expected = array(
            array(
                "item" => "item2",
                "item_widget" => "",
                    "children" => array(
                        array(
                            "item" => "item3",
                            "item_widget" => $widgetOutput
                        )
                    )
            )
        );

        /**
         * Mock related resources
         */
        $widgetResourceStub = $this->getMock('Vaimo_Menu_Model_Resource_Catalog_Category_Widget');
        $widgetResourceStub->expects($this->any())
            ->method('getWidgetBlockInfoForBlockReferences')
            ->will($this->returnValue(array(
                '1' => array('reference' => $menuBlockAlias, 'name' => $widgetNameInLayout)
            )));

        $this->_mockResourceSingleton('vaimo_menu/catalog_category_widget', $widgetResourceStub);

        /**
         * Create widget block mock
         */
        $widget = new Mage_Core_Block_Text();
        $widget->setText($widgetOutput);
        $this->_model->setChild($widgetNameInLayout, $widget);

        /**
         * Run the rendering
         */
        sleep(1);
        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnUpdatedTreeOnEveryCallWhenCacheIsDisabled()
    {
        Mage::app()->getCacheInstance()->banUse(Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME);
        $this->_model->renderMenu();

        $this->_setUpMockCategories(array(
            array('entity_id' => 6, 'parent_id' => 1, 'name' => 'item6')
        ));
        $expected = array("item" => "item6");

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));

        Mage::reset();
    }

    public function testRenderMenuShouldReturnUpdatedTreeOnNewModelInstanceWhenCacheIsDisabled()
    {
        Mage::app()->getCacheInstance()->banUse(Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME);
        $this->_model->renderMenu();

        $this->_instantiateModel();
        $this->_setUpMockCategories(array(
            array('entity_id' => 6, 'parent_id' => 1, 'name' => 'item6')
        ));
        $expected = array("item" => "item6");

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));

        Mage::reset();
    }

    public function testRenderMenuShouldReturnUpdatedTreeWhenDataCacheLifetimeIsSetToLowValue()
    {
        $this->_model->setDataCacheLifetime(1);
        $this->_model->renderMenu();

        $this->_setUpMockCategories(array(
            array('entity_id' => 6, 'parent_id' => 1, 'name' => 'item6')
        ));
        $expected = array("item" => "item6");

        /**
         * Let the cache expire
         */
        sleep(2);
        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRetainBlocksOriginalTemplateAfterMenuRenderingIsDone()
    {
        $expected = 'my_template.phtml';
        $this->_model->setTemplate($expected);

        $this->_model->renderMenu();

        $this->assertEquals($expected, $this->_model->getTemplate());
    }

    public function testToHtmlShouldReturnSameMenuAsRenderMenu()
    {
        $renderMenuOutput = $this->_model->renderMenu();
        $toHtmlOutput = $this->_model->toHtml();

        $this->assertEquals($this->_repairJson($renderMenuOutput), $this->_repairJson($toHtmlOutput));
    }

    public function testToHtmlShouldUseBlockCacheIfBlockCacheLifetimeIsSpecified()
    {
        Mage::reset();
        $this->_enableCache(Mage_Core_Block_Template::CACHE_GROUP);

        $this->_setUpMockCategories($this->_mockCategories);
        $this->_model->setStartLevel(2);
        $this->_model->unsDisplayLevels();
        $this->_model->setDataCacheLifetime(1);
        $this->_model->setBlockCacheLifetime(1000);

        $this->_model->toHtml();
        $this->_setUpMockCategories(array(array('entity_id' => 6, 'parent_id' => 1, 'name' => 'item6')));
        $result = $this->_model->toHtml();

        $this->assertEquals(json_encode($this->_expectedTree), $this->_repairJson($result));
        Mage::reset();
    }

    public function testToHtmlShouldNotUseCategoryTreeUrlValuesIfSessionUsedInUrl()
    {
        $useSessionInUrl = Mage::app()->getUseSessionInUrl();

        $this->_model->setItemTemplate('vaimo/menu/test/item_with_url.phtml');
        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(2);
        Mage::app()->setUseSessionInUrl(true);

        $this->_setUpMockCategories(array(array('entity_id' => 6, 'parent_id' => 1, 'name' => 'item6', 'url' => 'http://some.thing/somewhere')));
        $result = $this->_model->toHtml();
        Mage::app()->setUseSessionInUrl($useSessionInUrl);

        $this->assertFalse(strstr($result, 'http://some.thing/somewhere'), 'Predefined URL not used');
    }

    protected function _instantiateModelInfiniteLevels()
    {
        return $this->_instantiateModel(array(
            0 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
            ),
            1 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE,
            )
        ));

    }

    public function testRenderMenuShouldNotRenderLevelsThatComeAfterLevelThatDoesNotHaveChildrenParameterSpecified()
    {
        $configuration = array(
            0 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
            ),
            1 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
            ),
            2 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE
            )
        );

        $model = $this->_instantiateModel('some-type', $configuration);

        $model->setDisplayLevels(3);
        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array("item" => "item3"),
                    array("item" => "item6"),
                    array("item" => "item4")
                )
            )
        );

        $result = $model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testGetRelativeLevelFromAbsoluteShouldReturnLevelRelativeToStartingLevel()
    {
        $this->_model->setStartLevel(5);
        $result = $this->_model->getRelativeLevelFromAbsolute(8);

        $this->assertEquals(3, $result);
    }

    public function testRenderMenuShouldNotRenderNonDefaultGroupItemsToMainArea()
    {
        $this->_instantiateModelInfiniteLevels();

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'footer'),
            array('entity_id' => 10, 'parent_id' => 4, 'name' => 'item10', 'menu_group' => 'main'),
        ));

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array("item" => "item3"),
                    array("item" => "item6")
                )
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function _setupCategoriesWithParentCategoryBelongingToNonDefaultGroup()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'footer'),
            array('entity_id' => 10, 'parent_id' => 4, 'name' => 'item10', 'menu_group' => 'main'),
        ));
    }

    public function _setupCategoriesWithSameLevelItemsBelongingToNonDefaultGroup()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'footer'),
            array('entity_id' => 5, 'parent_id' => 2, 'name' => 'item5', 'menu_group' => 'footer'),
        ));
    }

    public function testRenderMenuShouldOutputItemsWithoutGroupsAsDefaultGroupItems()
    {
        $this->_model->setDisplayLevels(3);

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6'),
            array('entity_id' => 4, 'parent_id' => 6, 'name' => 'item4')
        ));

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array("item" => "item3"),
                    array(
                        "item" => "item6",
                        "children" => array(
                            array("item" => "item4")
                        )
                    ),
                )
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldOutputSubItemsOfNonDefaultGroupMenuItemToParentItemLevelWhenRenderGroupChildrenUsed()
    {
        $this->_model->setDisplayLevels(2);

        $this->_setupCategoriesWithParentCategoryBelongingToNonDefaultGroup();

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array("item" => "item3"),
                    array("item" => "item6"),
                    array("item" => "item10")
                )
            )
        );

        $this->_model->setItemTemplate('vaimo/menu/test/item_groups.phtml');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldUseCorrectStructureClassesOnGroupOutputWhenAppropriateBlockMethodsAreUsed()
    {
        $this->_model->setDisplayLevels(3);

        $this->_setupCategoriesWithParentCategoryBelongingToNonDefaultGroup();

        $expected = array(
            array(
                "children" => array(
                    array(
                        "group" => "footer",
                        "main" => "menu-group-footer",
                        "items" => "group-items"
                    )
                )
            )
        );

        $this->_model->setItemTemplate('vaimo/menu/test/item_only_groups.phtml');
        $this->_model->setGroupTemplate('vaimo/menu/test/group_structure.phtml', 'footer');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderOnlyGroupsReferencedOnTemplateWhenMoreThanOneGroupIsPresentOnTreeLevel()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_only_groups.phtml');
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'footer');
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'header');
        $this->_model->setGroupItemTemplate('vaimo/menu/test/item.phtml', 'footer');

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'footer'),
            array('entity_id' => 5, 'parent_id' => 4, 'name' => 'item5', 'menu_group' => 'main'),
            array('entity_id' => 7, 'parent_id' => 2, 'name' => 'item7', 'menu_group' => 'header'),
            array('entity_id' => 8, 'parent_id' => 7, 'name' => 'item8', 'menu_group' => 'main'),
        ));

        $expected = array(
            array(
                'children' => array(
                    array('item' => 'item5')
                )
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderNothingWhenReferencedGroupDoesNotExist()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_only_groups.phtml');
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'footer');
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'header');
        $this->_model->setGroupItemTemplate('vaimo/menu/test/item.phtml', 'footer');

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'special'),
            array('entity_id' => 5, 'parent_id' => 4, 'name' => 'item5', 'menu_group' => 'main'),
            array('entity_id' => 7, 'parent_id' => 2, 'name' => 'item7', 'menu_group' => 'header'),
            array('entity_id' => 8, 'parent_id' => 7, 'name' => 'item8', 'menu_group' => 'main'),
        ));

        $expected = array('children' => array());

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldOutputItemsOfNonDefaultGroupDirectlyToCurrentLevelWhenRenderGroupUsed()
    {
        $this->_model->setDisplayLevels(2);
        $this->_setupCategoriesWithSameLevelItemsBelongingToNonDefaultGroup();

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array("item" => "item3"),
                    array("item" => "item6"),
                    array("item" => "item4"),
                    array("item" => "item5")
                )
            )
        );

        $this->_model->setItemTemplate('vaimo/menu/test/item_groups_direct.phtml');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldOutputItemsOnCustomTemplateWhenCustomTemplateOnCertainLevelDefined()
    {
        $this->_model->setDisplayLevels(2);
        $this->_setupCategoriesWithSameLevelItemsBelongingToNonDefaultGroup();

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array("item" => "item3"),
                    array("item" => "item6"),
                    array(
                        "group" => "footer",
                        "children" => array(
                            array("item" => "item4"),
                            array("item" => "item5")
                        )
                    )
                )
            )
        );

        $this->_model->setItemTemplate('vaimo/menu/test/item_groups_direct.phtml');
        $this->_model->setGroupTemplate('vaimo/menu/test/group_custom.phtml', 'footer');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    protected function _setUpCategoriesForCustomRootTesting()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6'),
            array('entity_id' => 20, 'parent_id' => 2, 'name' => 'item20'),
            array('entity_id' => 11, 'parent_id' => 6, 'name' => 'item11'),
            array('entity_id' => 12, 'parent_id' => 6, 'name' => 'item12'),
            array('entity_id' => 7, 'parent_id' => 3, 'name' => 'item7'),
            array('entity_id' => 5, 'parent_id' => 3, 'name' => 'item5'),
            array('entity_id' => 10, 'parent_id' => 5, 'name' => 'item10'),
            array('entity_id' => 30, 'parent_id' => 999, 'name' => 'item30'),
            array('entity_id' => 31, 'parent_id' => 30, 'name' => 'item31'),
            array('entity_id' => 32, 'parent_id' => 30, 'name' => 'item32'),
            array('entity_id' => 33, 'parent_id' => 31, 'name' => 'item33')
        ));
    }

    public function testRenderMenuShouldRenderOnlyActiveBranchOfCertainLevelIfCustomRootItemFromAncestorLevelIsSet()
    {
        $this->_setUpCategoriesForCustomRootTesting();
        $this->_currentCategory->setPathIds(array(2, 6, 11));

        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(3);

        $expected = array(
            array(
                "item" => "item6",
                "children" => array(
                    array("item" => "item11"),
                    array("item" => "item12")
                )
            )
        );

        $this->_model->setCustomRootFromActiveItemsAncestorAtLevel(2);

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldUseStartLevelInRelationToCustomRoot()
    {
        $this->_setUpCategoriesForCustomRootTesting();
        $this->_currentCategory->setPathIds(array(2, 6, 11));

        $this->_model->setStartLevel(2);
        $this->_model->setDisplayLevels(3);

        $expected = array(
            array("item" => "item11"),
            array("item" => "item12")
        );

        $this->_model->setCustomRootFromActiveItemsAncestorAtLevel(2);

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnMenuItemsForCustomRootIdThatIsPartOfMainTree()
    {
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();

        $model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(4);
        $model->setCustomRootId(3);

        $expected = array('item3', 'item7', 'item5', 'item10');
        $result = $model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnMenuItemsForCustomRootIdEvenWhenRootIsAnOrphan()
    {
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();

        $model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(4);
        $model->setCustomRootId(30);

        $expected = array('item30', 'item31', 'item33', 'item32');
        $result = $model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldIgnoreOrphanBranchesAndRenderOnlyItemsUnderStoreRootCategoryId()
    {
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();

        $model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(10);

        $this->_rootCategoryId = 2;

        $expected = array('item2', 'item3', 'item7', 'item5', 'item6', 'item11', 'item12', 'item20');
        $result = $model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldCacheMenuOutputWithCustomIdUnderSeparateCacheRecordFromStoreCategoryRoot()
    {
        $this->_setupCacheTest();
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();
        $model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(2);
        $this->_rootCategoryId = 2;

        $resultStoreRoot = $model->renderMenu();

        $model->setCustomRootId(3);
        $resultCustomRoot = $model->renderMenu();

        $this->assertNotEquals($resultStoreRoot, $resultCustomRoot);

        $this->_tearDownCacheTest();
    }

    public function testRenderMenuShouldCacheMenuOutputUnderDifferentCacheIdForEachCustomId()
    {
        $this->_setupCacheTest();
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();
        $model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(2);

        $model->setCustomRootId(3);
        $resultCustomId3 = $model->renderMenu();

        $model->setCustomRootId(5);
        $resultCustomId5 = $model->renderMenu();

        $this->assertNotEquals($resultCustomId3, $resultCustomId5);

        $this->_tearDownCacheTest();
    }

    public function testRenderMenuShouldReturnMenuItemsForCustomRootIdWithCorrectHierarchyValues()
    {
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();

        $model->setItemTemplate('vaimo/menu/test/item_hierarchy_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(4);
        $model->setCustomRootId(12345);

        $expected = array();
        $result = $model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnNoMenuItemsForCustomRootIdThatDoesNotExist()
    {
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();

        $model->setItemTemplate('vaimo/menu/test/item_hierarchy_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(4);
        $model->setCustomRootId(3);

        $expected = array('', '1', '2', '2-1');
        $result = $model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnMenuItemsWithCorrectHierarchyValues()
    {
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();

        $model->setItemTemplate('vaimo/menu/test/item_hierarchy_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(3);
        $this->_rootCategoryId = 2;

        $expected = array('', '1', '1-1', '1-2', '2', '2-1', '2-2', '3');
        $result = $model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnDefaultRootIfCustomRootIdIsSetToZero()
    {
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpCategoriesForCustomRootTesting();

        $model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(10);
        $model->setCustomRootId(0);

        $this->_rootCategoryId = 2;

        $expected = array('item2', 'item3', 'item7', 'item5', 'item6', 'item11', 'item12', 'item20');
        $result = $model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testShouldNotUseCustomRootIdIfThereAreNoItemsInTheCategoryTree()
    {
        $model = $this->_instantiateModelInfiniteLevels();
        $this->_setUpMockCategories(array());

        $model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $model->setStartLevel(1);
        $model->setDisplayLevels(10);
        $model->setCustomRootId(123);

        $result = $model->renderMenu();

        $this->assertEquals(json_encode(array()), $this->_repairJson($result));
    }
}