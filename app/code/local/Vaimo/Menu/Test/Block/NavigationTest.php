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

class Vaimo_Menu_Test_Block_NavigationTest extends Vaimo_Menu_Test_Block_Navigation_Type_BaseCase
{
    protected $_model;
    protected $_container = 'vaimo/menu/test/container.phtml';
    protected $_itemTemplate = 'vaimo/menu/test/item.phtml';
    protected $_itemGroupFlat = 'vaimo/menu/test/flat_group.phtml';
    protected $_breakpointTemplate = 'vaimo/menu/test/breakpoint.phtml';

    public function setUp()
    {
        parent::setUp();

        $this->_instantiateModel();
    }

    public function _instantiateModel()
    {
        $this->_model = new Vaimo_Menu_Block_Navigation();
        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(3);
        $this->_model->setItemTemplate($this->_itemTemplate);
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'main');
        $this->_model->setTemplate($this->_container);

    }

    public function testRenderMenuShouldIgnoreBreakpointsEvenIfTheyAreUsed()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', 'column_breakpoint' => true),
        ));

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

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldIgnoreGroupsEvenWhenTheyAreDefinedInCategories()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'footer'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'footer'),
        ));

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array("item" => "item3"),
                )
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldIgnoreGroupsEvenWhenTheyAreDefinedInCategoriesAndUsedOnTemplate()
    {
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
                )
            )
        );

        $this->_model->setItemTemplate('vaimo/menu/test/item_groups.phtml');

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldGenerateMenuStructureClassesInExpectedFormatForMenuThatHasGroupsDisabled()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_class_only.phtml');
        $this->_model->setStartLevel(2);
        $this->_model->setDisplayLevels(3);

        $expected = array(
            "level0 nav-i3 even first parent nav-1",
            "level1 nav-i7 even first nav-1-1",
            "level1 nav-i5 odd last parent nav-1-2",
            "level2 nav-i10 even first nav-1-2-1",
            "level0 nav-i6 odd nav-2",
            "level0 nav-i4 even last nav-3"
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderAsManyLevelsAsThereAreInTheMenuTree()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(6);

        $categories = array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main', 'url_key' => 'i2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', 'url_key' => 'i3'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'url_key' => 'i6'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', 'url_key' => 'i4'),
            array('entity_id' => 7, 'parent_id' => 3, 'name' => 'item7', 'menu_group' => 'main', 'url_key' => 'i7'),
            array('entity_id' => 5, 'parent_id' => 3, 'name' => 'item5', 'menu_group' => 'main', 'url_key' => 'i5'),
            array('entity_id' => 10, 'parent_id' => 5, 'name' => 'item10', 'menu_group' => 'main', 'url_key' => 'i10'),
            array('entity_id' => 11, 'parent_id' => 10, 'name' => 'item11', 'menu_group' => 'main', 'url_key' => 'i11'),
            array('entity_id' => 12, 'parent_id' => 11, 'name' => 'item12', 'menu_group' => 'main', 'url_key' => 'i12')
        );

        $this->_setUpMockCategories($categories);

        $expected = array("item2", "item3", "item7", "item5", "item10", "item11", "item12", "item6", "item4");
        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldIgnorePreRenderedDataWhenStartLevelOrDisplayLevelIsChanged()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(2);

        $result1 = $this->_model->renderMenu();
        $this->_model->setDisplayLevels(3);
        $result2 = $this->_model->renderMenu();
        $this->_model->setStartLevel(2);
        $result3 = $this->_model->renderMenu();

        $this->assertNotEquals($this->_repairJson($result2), $this->_repairJson($result1));
        $this->assertNotEquals($this->_repairJson($result3), $this->_repairJson($result1));
        $this->assertNotEquals($this->_repairJson($result3), $this->_repairJson($result2));
    }

    public function testRenderMenuShouldIgnorePreRenderedDataWhenCacheIsCleanedAndMenuConfigurationHasChanged()
    {
        $this->_model->setItemTemplate('vaimo/menu/test/item_name_only.phtml');
        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(2);

        $result1 = $this->_model->renderMenu();

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 10, 'parent_id' => 2, 'name' => 'item10', 'menu_group' => 'footer'),
            array('entity_id' => 11, 'parent_id' => 2, 'name' => 'item11', 'menu_group' => 'footer'),
        ));

        Mage::app()->cleanCache(Vaimo_Menu_Model_Navigation::CACHE_TAG);
        $result2 = $this->_model->renderMenu();

        $this->assertNotEquals($this->_repairJson($result2), $this->_repairJson($result1));
    }
}