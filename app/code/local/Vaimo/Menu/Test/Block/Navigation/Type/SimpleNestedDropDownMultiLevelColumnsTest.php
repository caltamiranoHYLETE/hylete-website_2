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

class Vaimo_Menu_Block_Navigation_Type_SimpleNestedDropDownMultiLevelColumnsTest
    extends Vaimo_Menu_Test_Block_Navigation_Type_BaseCase
{
    protected $_model;
    protected $_expectedStructure;
    protected $_container = 'vaimo/menu/test/container.phtml';
    protected $_itemTemplate = 'vaimo/menu/test/item.phtml';
    protected $_itemGroupFlat = 'vaimo/menu/test/flat_group.phtml';
    protected $_breakpointTemplate = 'vaimo/menu/test/breakpoint.phtml';


    public function setUp()
    {
        parent::setUp();

        $this->_mockCategories = array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main'),
            array('entity_id' => 5, 'parent_id' => 3, 'name' => 'item5', 'menu_group' => 'main'),
            array('entity_id' => 6, 'parent_id' => 3, 'name' => 'item6', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 7, 'parent_id' => 3, 'name' => 'item7', 'menu_group' => 'main'),
            array('entity_id' => 8, 'parent_id' => 5, 'name' => 'item8', 'menu_group' => 'main', 'column_breakpoint' => true),
            array('entity_id' => 9, 'parent_id' => 5, 'name' => 'item9', 'menu_group' => 'main'),
            array('entity_id' => 10, 'parent_id' => 5, 'name' => 'item10', 'menu_group' => 'main')
        );

        $this->_setUpMockCategories($this->_mockCategories);

        $this->_instantiateModel();
    }

    public function _instantiateModel()
    {
        $this->_model = new Vaimo_Menu_Block_Navigation_Type_SimpleNestedDropDownMultiLevelColumns();
        $this->_model->setStartLevel(2);
        $this->_model->setDisplayLevels(4);
        $this->_model->setItemTemplate($this->_itemTemplate);
        $this->_model->setGroupTemplate($this->_itemGroupFlat, 'main');
        $this->_model->setBreakpointTemplate($this->_breakpointTemplate);
    }

    public function testRenderMenuShouldUseBreakpointsOnSecondAndThirdLevelOfTheMenu()
    {
        $expected = array(
            array(
                'item' => 'item3',
                'children' => array(
                    array(
                        array(
                            'item' => 'item5',
                            'children' => array(
                                array(
                                    array('item' => 'item8')
                                ),
                                array(
                                    array('item' => 'item9'),
                                    array('item' => 'item10')
                                )
                            )
                        ),
                        array('item' => 'item6')
                    ),
                    array(
                        array('item' => 'item7')
                    )
                )
            ),
            array('item' => 'item4')
        );

        $result = $this->_model->renderMenu();

        $this->assertEquals(json_encode($expected), $this->_repairJson($result));
    }
}