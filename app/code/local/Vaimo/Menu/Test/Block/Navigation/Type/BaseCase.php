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

class Vaimo_Menu_Test_Block_Navigation_Type_BaseCase extends Vaimo_Menu_Test_BaseCase
{
    protected $_itemTemplate = 'vaimo/menu/test/item.phtml';
    protected $_itemGroupFlat = 'vaimo/menu/test/flat_group.phtml';
    protected $_itemBreakpointFlat = 'vaimo/menu/test/flat_breakpoint.phtml';
    protected $_expectedTree = array();
    protected $_mockCategories = array();

    public function setUp()
    {
        parent::setUp();

        $this->_mockCategories = array(
            array('entity_id' => 2, 'parent_id' => 1, 'name' => 'item2', 'menu_group' => 'main', 'url_key' => 'i2'),
            array('entity_id' => 3, 'parent_id' => 2, 'name' => 'item3', 'menu_group' => 'main', 'url_key' => 'i3'),
            array('entity_id' => 6, 'parent_id' => 2, 'name' => 'item6', 'menu_group' => 'main', 'url_key' => 'i6'),
            array('entity_id' => 4, 'parent_id' => 2, 'name' => 'item4', 'menu_group' => 'main', 'url_key' => 'i4'),
            array('entity_id' => 7, 'parent_id' => 3, 'name' => 'item7', 'menu_group' => 'main', 'url_key' => 'i7'),
            array('entity_id' => 5, 'parent_id' => 3, 'name' => 'item5', 'menu_group' => 'main', 'url_key' => 'i5'),
            array('entity_id' => 10, 'parent_id' => 5, 'name' => 'item10', 'menu_group' => 'main', 'url_key' => 'i10')
        );

        $this->_expectedTree = array(
            array(
                'item' => 'item3',
                'children' => array(
                    array('item' => 'item7'),
                    array(
                        'item' => 'item5',
                        'children' => array(
                            array('item' => 'item10')
                        )
                    ),
                )
            ),
            array('item' => 'item6'),
            array('item' => 'item4')
        );

        $this->_setUpMockCategories($this->_mockCategories);
    }

    /**
     * Using Zend decoder as it is more tolerant for whitespace and new-line occurrences
     *
     * @param $json
     * @return string
     */
    protected function _repairJson($json)
    {
        $result = str_replace(array(',}', ',]'),array('}', ']'), $json);
        $result = substr($result, 0, -1);

        $_result = json_decode($result);

        if ($_result === null) {
            $result = '[' . $result. ']';
        }

        return json_encode(Zend_Json_Decoder::decode($result));
    }
}