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

class Vaimo_Menu_Test_Block_Navigation_Type_BreakpointsTest extends Vaimo_Menu_Test_Block_Navigation_Type_BaseCase
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

    public function _instantiateModel($configuration = null)
    {
        if ($configuration === null) {
            $configuration = array(
                0 => array(
                    'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                    'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
                ),
                1 => array(
                    'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                    'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE,
                    'break_type' => Vaimo_Menu_Model_Type::COLUMNS
                ),
                2 => array(
                    'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                )
            );
        }

        $this->_model = new Vaimo_Menu_Block_Navigation_Type_BreakpointsNonAbstract(array('_type_configuration' => $configuration));
        $this->_model->setStartLevel(1);
        $this->_model->setDisplayLevels(3);
        $this->_model->setBreakpointTemplate($this->_breakpointTemplate);
        $this->_model->setItemTemplate($this->_itemTemplate);
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'main');
        $this->_model->setTemplate($this->_container);
    }

    public function testRenderMenuShouldRenderMenuInSingleColumnIfNoBreakpointsAreSetAndEvenIfCertainLevelDisallowsColumns()
    {
        $result = $this->_model->renderMenu();
        $expected = array(
            array(
                "item" => "item2",
                "children" =>
                    array(
                        array(
                            array(
                                "item" => "item3",
                                "children" => array(
                                    array(
                                        array("item" => "item7"),
                                        array("item" => "item5")
                                    )
                                )
                            ),
                            array("item" => "item6"),
                            array("item" => "item4")
                        )
                    )
            )
        );

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldSplitMenuLevelIntoNewColumnAfterItemWithBreakpointFlagIsEncountered()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main', ),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', ),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', ),
        ));

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array(
                        array("item" => "item3"),
                        array("item" => "item6")
                    ),
                    array(
                        array("item" => "item4")
                    )
                )
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderColumnWithCustomTemplateIfColumnTemplateIsSetOnSpecifiedLevel()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main'),
            array('entity_id' => 7, 'parent_id' => 3, 'name' => 'item7', 'menu_group' => 'main'),
        ));

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array(
                        array(
                            "item" => "item3",
                            "children" => array(
                                array(
                                    "custom_column" => array(
                                        array("item" => "item7")
                                    )
                                )
                            )
                        ),
                        array("item" => "item6")
                    ),
                    array(
                        array("item" => "item4")
                    )
                )
            )
        );
        $this->_model->setBreakpointTemplate('vaimo/menu/test/breakpoint_custom.phtml', 2);

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderColumnsWithItemTemplateIfColumnTemplateIsNotSet()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main', ),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', ),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', ),
        ));

        $this->_model->setBreakpointTemplate(null);

        $result = $this->_model->renderMenu();

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array(
                        "item" => "",
                        "children" => array(
                            array("item" => "item3"),
                            array("item" => "item6")
                        )
                    ),
                    array(
                        "item" => "",
                        "children" => array(
                            array("item" => "item4")
                        )
                    )
                )
            )
        );

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldAddCorrectStructureClassesToColumnWrappers()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main', ),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', ),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', ),
        ));

        $this->_model->setBreakpointTemplate('vaimo/menu/test/breakpoint_class.phtml');

        $expected = array(
            array(
                'item' => 'item2',
                'children' => array(
                    array(
                        'item' => 'menu-bp-column even first',
                        'children' => 'level1 menu-bp-items'
                    ),
                    array(
                        'item' => 'menu-bp-column odd last',
                        'children' => 'level1 menu-bp-items'
                    )
                )
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldAddCorrectStructureClassesToGroupWhenNonDefaultGroupsAreUsed()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main', ),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', ),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', ),
            array('entity_id' => 7, 'parent_id' => 2, 'name' => 'item7', 'menu_group' => 'footer'),
            array('entity_id' => 6, 'parent_id' => 7, 'name' => 'item6', 'menu_group' => 'main'),
        ));

        $this->_model->setItemTemplate('vaimo/menu/test/item_only_groups.phtml');
        $this->_model->setGroupTemplate('vaimo/menu/test/group_structure.phtml', 'footer');

        $expected = array(
            array(
                'children' => array(array(
                    "group" => "footer",
                    "main" => "menu-group-footer",
                    "items" => "group-items"
                ))
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldAddCorrectStructureClassesToColumnWrappersWhenNonDefaultGroupsAreUsed()
    {
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main', ),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', ),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', ),
            array('entity_id' => 7, 'parent_id' => 2, 'name' => 'item7', 'menu_group' => 'footer'),
            array('entity_id' => 6, 'parent_id' => 7, 'name' => 'item6', 'menu_group' => 'main'),
        ));

        $this->_model->setBreakpointTemplate('vaimo/menu/test/breakpoint_class.phtml');
        $this->_model->setItemTemplate('vaimo/menu/test/item_only_groups.phtml');

        $expected = array(
            array(
                'children' => array(array(
                    "item" => "menu-bp-column even first",
                    "children" => "level1"
                ))
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderNonMainGroupItemsToSeparateColumnsAndBreakIntoNewColumnOnColumnBreakpoint()
    {
        $this->_instantiateModel(array(
            0 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
            ),
            1 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE,
                'break_type' => Vaimo_Menu_Model_Type::COLUMNS
            ),
            2 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                'break_type' => Vaimo_Menu_Model_Type::COLUMNS
            )
        ));
        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main'),
            array('entity_id' => 7, 'parent_id' => 2, 'name' => 'item7', 'menu_group' => 'footer'),
            array('entity_id' => 6, 'parent_id' => 7, 'name' => 'item6', 'menu_group' => 'main'),
            array('entity_id' => 4, 'parent_id' => 7, 'name' => 'item4', 'menu_group' => 'main'),
        ));

        $this->_model->setItemTemplate('vaimo/menu/test/item_groups.phtml');
        $result = $this->_model->renderMenu();

        $expected = array(
            array(
                'item' => 'item2',
                'children' => array(
                    array(
                        array('item' => 'item3')
                    ),
                    array(
                        array('item' => 'item6'),
                        array('item' => 'item4')
                    )
                )
            )
        );

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }

    public function testRenderMenuShouldRenderColumnBreaksOnlyForLevelsWhereItIsAllowedByMenyTypeConfiguration()
    {
        $this->_instantiateModel(array(
            0 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
            ),
            1 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE,
            )
        ));

        $this->_setUpMockCategories(array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main', ),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', ),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', ),
        ));

        $expected = array(
            array(
                "item" => "item2",
                "children" => array(
                    array(
                        array("item" => "item3"),
                        array("item" => "item6"),
                        array("item" => "item4")
                    )
                )
            )
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }
}

class Vaimo_Menu_Block_Navigation_Type_BreakpointsNonAbstract extends Vaimo_Menu_Block_Navigation_Type_Breakpoints
{
}