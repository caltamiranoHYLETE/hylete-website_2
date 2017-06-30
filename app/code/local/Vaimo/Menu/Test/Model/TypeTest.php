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

class Vaimo_Menu_Test_Model_TypeTest extends Vaimo_Menu_Test_BaseCase
{
    protected $_model;
    protected $_expected = array();
    protected $_nonexistentCode = '_strange_type_code';

    public function setUp()
    {
        $config = new Mage_Core_Model_Config('
            <config>
                <frontend>
                    <vaimo_menu>
                        <menu1>
                            <label>Menu1</label>
                            <type>testmodule/test_type</type>
                            <description>Menu type description</description>
                        </menu1>
                        <menu2>
                            <label>Menu2</label>
                            <type>testmodule/test_type2</type>
                            <description>Another menu type description</description>
                        </menu2>
                    </vaimo_menu>
                </frontend>
            </config>
        ');

        $this->_expected = array(
            'menu1' => array(
                'label' => 'Menu1',
                'type' => 'testmodule/test_type',
                'description' => 'Menu type description'
            ),
            'menu2' => array(
                'label' => 'Menu2',
                'type' => 'testmodule/test_type2',
                'description' => 'Another menu type description'
            )
        );

        $this->_model = new Vaimo_Menu_Model_Type(array('config' => $config));
    }

    public function testGetAllShouldReturnAllDefinedMenuTypesAsArrays()
    {
        $result = $this->_model->getAll();

        $this->assertEquals($this->_expected, $result);
    }

    public function testGetDefinitionByCodeShouldReturnMenuTypeConfiguraitonByCodeIfItExists()
    {
        foreach ($this->_expected as $code => $expected) {
            $result = $this->_model->GetDefinitionByCode($code);
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * @expectedException       Vaimo_Menu_Exception
     * @expectedExceptionCode   Vaimo_Menu_Exception::TYPE_NOT_FOUND
     */
    public function testGetDefinitionByCodeShouldThrowAnExceptionWhenMenuTypeWithSpecifiedCodeIsNotFound()
    {
        $this->_model->GetDefinitionByCode($this->_nonexistentCode);
    }

    public function testGetNavigationBlockTypeShouldReturnTypeBlockNameOfMenuTypeThatHasSpecifiedCode()
    {
        foreach ($this->_expected as $code => $expected) {
            $result = $this->_model->GetNavigationBlockType($code);
            $this->assertEquals($expected['type'], $result);
        }
    }

    /**
     * @expectedException       Vaimo_Menu_Exception
     * @expectedExceptionCode   Vaimo_Menu_Exception::TYPE_NOT_FOUND
     */
    public function testGetNavigationBlockTypeShouldThrowAnExceptionWhenMenuTypeWithSpecifiedCodeIsNotFound()
    {
        $this->_model->GetNavigationBlockType($this->_nonexistentCode);
    }
}