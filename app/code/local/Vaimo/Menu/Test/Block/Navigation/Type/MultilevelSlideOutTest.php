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

class Vaimo_Menu_Test_Block_Navigation_Type_MultilevelSlideOutTest
    extends Vaimo_Menu_Test_Block_Navigation_Type_BaseCase
{
    protected $_model;
    protected $_expectedStructure;

    public function setUp()
    {
        parent::setUp();

        $this->_instantiateModel();
    }

    public function _instantiateModel()
    {
        $this->_model = new Vaimo_Menu_Block_Navigation_Type_MultilevelSlideOut();
        $this->_model->setStartLevel(2);
        $this->_model->setDisplayLevels(4);
        $this->_model->setItemTemplate($this->_itemTemplate);
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'main');
        $this->_model->setBreakpointTemplate($this->_itemBreakpointFlat);
    }

    public function testRenderMenuShouldGenerateMenuStructureAndClassesInExpectedFormat()
    {
        $expected = array(
            array(
                "item" => "item3",
                "item_class" => "level0 nav-i3 even first parent nav-1",
                "children" => array(
                    array(
                        "item" => "item7",
                        "item_class" => "level1 nav-i7 even first nav-1-1"
                    ),
                    array(
                        "item" => "item5",
                        "item_class" => "level1 nav-i5 odd last parent nav-1-2",
                        "children" => array(
                            array(
                                "item" => "item10",
                                "item_class" => "level2 nav-i10 even first nav-1-2-1"
                            )
                        )
                    )
                )
            ),
            array(
                "item" => "item6",
                "item_class" => "level0 nav-i6 odd nav-2"
            ),
            array(
                "item" => "item4",
                "item_class" => "level0 nav-i4 even last nav-3"
            ),
        );
        $this->_model->setItemTemplate('vaimo/menu/test/item_class.phtml');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldOutputCorrectStructureAndClassesWithOldTemplates()
    {
        $expected = array(
            array(
                "item" => "item3",
                "item_class" => "level0 nav-i3 even first level-top parent nav-1",
                "children" => array(
                    array(
                        "item" => "item7",
                        "item_class" => "level1 nav-i7 even first nav-1-1"
                    ),
                    array(
                        "item" => "item5",
                        "item_class" => "level1 nav-i5 odd last parent nav-1-2",
                        "children" => array(
                            array(
                                "item" => "item10",
                                "item_class" => "level2 nav-i10 even first nav-1-2-1"
                            )
                        )
                    )
                )
            ),
            array(
                "item" => "item6",
                "item_class" => "level0 nav-i6 odd level-top nav-2"
            ),
            array(
                "item" => "item4",
                "item_class" => "level0 nav-i4 even last level-top nav-3"
            ),
        );
        $this->_model->setItemTemplate('vaimo/menu/test/item_compatibility.phtml');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldSkipNavClassIfItemDoesNotHaveUrlKey()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'url_key' => 'i4'),
        ));
        $expected = array(
            array("item" => "item6", "item_class" => "level0 even first nav-1"),
            array("item" => "item4", "item_class" => "level0 nav-i4 odd last nav-2"),
        );
        $this->_model->setItemTemplate('vaimo/menu/test/item_class.phtml');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRetainPersistentStructureOnRepeatedCallOnSameBlockInstance()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'url_key' => 'i4'),
        ));

        $this->_model->setItemTemplate('vaimo/menu/test/item_class.phtml');
        $result1 = $this->_model->renderMenu();
        $result2 = $this->_model->renderMenu();

        $this->assertEquals($this->_repairJson($result1), $this->_repairJson($result2));
    }

    public function testRenderMenuShouldRetainPersistentStructureOnRepeatedCallOnDifferentBlockInstance()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4'),
        ));

        $this->_model->setItemTemplate('vaimo/menu/test/item_class.phtml');
        $result1 = $this->_model->renderMenu();

        $this->_instantiateModel();
        $this->_model->setItemTemplate('vaimo/menu/test/item_class.phtml');
        $result2 = $this->_model->renderMenu();

        $this->assertEquals($this->_repairJson($result1), $this->_repairJson($result2));
    }

    public function testRenderMenuShouldRetainPersistentStructOnRepeatedCallOnDiffBlockInstanceAndItemsHavePositions()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'position' => 1),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'position' => 2),
        ));

        $this->_model->setItemTemplate('vaimo/menu/test/item_class.phtml');
        $result1 = $this->_model->renderMenu();

        $this->_instantiateModel();
        $this->_model->setItemTemplate('vaimo/menu/test/item_class.phtml');
        $result2 = $this->_model->renderMenu();

        $this->assertEquals($this->_repairJson($result1), $this->_repairJson($result2));
    }

    public function testRenderMenuShouldGenerateMenuStructureBasedOnSetDisplayLevelsLimitations()
    {
        $this->_model->setDisplayLevels(1);
        $expected = array(array("item" => "item3"), array("item" => "item6"), array("item" => "item4"));

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderOnlyActiveTreeBranchIfOnlySkipIfInCurrentPathIsSetToTrue()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6'),
            array('entity_id' => 11, 'parent_id' => 6, 'name' => 'item11'),
            array('entity_id' => 12, 'parent_id' => 6, 'name' => 'item12'),
            array('entity_id' => 7, 'parent_id' => 3, 'name' => 'item7'),
            array('entity_id' => 5, 'parent_id' => 3, 'name' => 'item5'),
            array('entity_id' => 10, 'parent_id' => 5, 'name' => 'item10')
        ));
        $expected = array(
            array("item" => "item3"),
            array(
                "item" => "item6",
                "children" => array(
                    array("item" => "item11"),
                    array("item" => "item12")
                )
            )
        );

        $this->_currentCategory->setPathIds(array(2, 6, 11));
        $this->_model->setOnlySkipIfInCurrentPath(true);

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldReturnCategoryNameWhenUsedGetItemLabelIsUsedInTheTemplate()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_getlabel.phtml');
        $this->_model->setStartLevel(1);

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main')
        ));

        $expected = array(array("item" => "item2", "children" => array(array("item" => "item3"))));

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderMenuWithoutGroupsIfMenuGroupIsNotPresentInCategoryData()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_getlabel.phtml');
        $this->_model->setStartLevel(1);

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3')
        ));

        $expected = array(array("item" => "item2", "children" => array(array("item" => "item3"))));

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldNotShowMoreLevelsThanSpecifiedInConfigurationEvenWhenSpecifiedWithDisplayLevels()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(6);

        $expected1 = array("item2", "item3", "item7", "item5", "item6", "item4");
        $expected2 = array("item3", "item7", "item5", "item10", "item6", "item4");

        $result1 = $this->_model->renderMenu();

        Mage::app()->cleanCache(Vaimo_Menu_Model_Navigation::CACHE_TAG);
        $this->_model->setStartLevel(2);
        $result2 = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected1), $this->_repairJson($result1));
        $this->assertEquals(json_encode($expected2), $this->_repairJson($result2));
    }

    public function testRenderMenuShouldRenderMenuOnCorrectTemplatesSetForEachLevel()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_alternative.phtml', 1);

        $expected = array(
            array(
                "item" => "item3",
                "children" => array(
                    array("alternative" => "item7"),
                    array(
                        "alternative" => "item5",
                        "children" => array(
                            array("item" => "item10")
                        )
                    )
                )
            ),
            array("item" => "item6"),
            array("item" => "item4")
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderNonDefaultGroupsCalledFromTemplateAsDefaultGroups()
    {
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'footer');
        $this->_model->setStartLevel(1);

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'footer'),
            array('entity_id' => 4, 'parent_id' => 6, 'name' => 'item4', 'menu_group' => 'main'),
        ));

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array("item" => "item3"),
                    array("item" => "item4")
                )
            )
        );

        $this->_model->setItemTemplate('vaimo/menu/test/item_groups.phtml');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }
}