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

class Vaimo_Menu_Test_Model_Adminhtml_Category_Tree_DecoratorTest extends Vaimo_Menu_Test_BaseCase
{
    protected $_model;
    protected $_category;

    public function setUp()
    {
        $this->_model = Mage::getModel('vaimo_menu/adminhtml_category_tree_decorator');
        $this->_category = new Varien_Object();
    }

    public function testGetDecoratorFlagsForCategoryShouldReturnIntegerFlagsBasedOnAttributeBooleanEvaluation()
    {
        $this->_model->setMap(array(
            'attr1' => 'class1',
            'attr2' => 'class2',
            'attr3' => 'class3',
            'attr4' => 'class4',
            'attr5' => 'class5',
        ));

        $this->_category->setData(array(
            'attr1' => 0,
            'attr2' => 1,
            'attr3' => false,
            'attr4' => true,
            'attr5' => ''
        ));

        $expected = array(
            'attr1' => 0,
            'attr2' => 1,
            'attr3' => 0,
            'attr4' => 1,
            'attr5' => 0
        );

        $result = $this->_model->getDecoratorFlagsForCategory($this->_category);

        $this->assertEquals($expected, $result);
    }

    public function testGetDecoratorFlagsForCategoryShouldNotReturnFlagsThatDoNotHaveValueInMap()
    {
        $this->_model->setMap(array(
            'attr1' => 'class1',
            'attr4' => 'class4',
        ));

        $this->_category->setData(array(
            'attr1' => 0,
            'attr2' => 1,
            'attr3' => false,
            'attr4' => true,
            'attr5' => ''
        ));

        $expected = array(
            'attr1' => 0,
            'attr4' => 1,
        );

        $result = $this->_model->getDecoratorFlagsForCategory($this->_category);

        $this->assertEquals($expected, $result);
    }

    public function testGetDecoratorFlagsForCategoryShouldReturnFalseIntegerFlagForMissingAttributes()
    {
        $this->_model->setMap(array(
            'attr1' => 'class1',
            'attr4' => 'class4',
            'attr8' => 'class8',
        ));

        $this->_category->setData(array(
            'attr1' => 1,
        ));

        $expected = array(
            'attr1' => 1,
            'attr4' => 0,
            'attr8' => 0,
        );

        $result = $this->_model->getDecoratorFlagsForCategory($this->_category);

        $this->assertEquals($expected, $result);
    }


    public function testGetDecoratorFlagsForCategoryShouldReturnAttributeValuesWithCustomFlagsWithoutModifyingThem()
    {
        $this->_model->setMap(array(
            'attr1' => array(
                'val1' => 'flag1',
                'val2' => 'flag2',
            ),
            'attr2' => array(
                'val10' => 'flag6',
                'val20' => 'flag4',
            )
        ));

        $this->_category->setData(array(
            'attr1' => 'val1',
            'attr2' => 'val20'
        ));

        $expected = array(
            'attr1' => 'val1',
            'attr2' => 'val20'
        );

        $result = $this->_model->getDecoratorFlagsForCategory($this->_category);

        $this->assertEquals($expected, $result);
    }

    public function testApplyToTreeShouldModifyTreeToIncludeFlags()
    {
        $tree = array(
            array('id' => '1', 'cls' => '', 'children' => array(
                array('id' => '2', 'cls' => ''),
                array('id' => '3', 'cls' => '', 'children' => array(
                    array('id' => '5', 'cls' => ''),
                    array('id' => '6', 'cls' => ''),
                )),
                array('id' => '4', 'cls' => ''),
            )),
        );

        $categories = array(
            array('entity_id' => 1, 'attr1' => 'val1', 'attr2' => 'val20'),
            array('entity_id' => 2, 'attr1' => 'val2', 'attr2' => 'val10'),
            array('entity_id' => 3, 'attr1' => 'val3'),
            array('entity_id' => 5, 'attr1' => array('123')),
            array('entity_id' => 4, 'attr1' => 'val4'),
        );

        $this->_setUpMockCategories($categories, 'vaimo_menu/adminhtml_category_tree_decorator');

        $this->_model->setMap(array(
            'attr1' => array(
                'val1' => 'flag1',
                'val2' => 'flag2',
                'val3' => 'flag3'
            ),
            'attr2' => array(
                'val10' => 'flag6',
                'val20' => 'flag4',
            )
        ));

        $this->_category->setData(array(
            'attr1' => 'val1',
            'attr2' => 'val20'
        ));

        $expected = array(
            array('id' => '1', 'cls' => 'flag1 flag4', 'children' => array(
                array('id' => '2', 'cls' => 'flag2 flag6'),
                array('id' => '3', 'cls' => 'flag3', 'children' => array(
                    array('id' => '5', 'cls' => ''),
                    array('id' => '6', 'cls' => ''),
                )),
                array('id' => '4', 'cls' => ''),
            )),
        );

        $this->_model->applyToTree($tree);

        $this->assertEquals($expected, $tree);
    }
}