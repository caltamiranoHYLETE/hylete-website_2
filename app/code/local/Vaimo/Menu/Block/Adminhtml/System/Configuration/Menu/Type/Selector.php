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

/**
 * Class Vaimo_Menu_Block_Adminhtml_System_Configuration_Menu_Type_Selector
 *
 * @method setSelectName(string $name)
 * @method string getSelectName()
 *
 * @method null _setMenuTypesData($types)
 * @method null _init(string $selectName, $skinImgPath)
 */
class Vaimo_Menu_Block_Adminhtml_System_Configuration_Menu_Type_Selector extends Vaimo_Menu_Block_Adminhtml_Js_Lib
{
    protected $_jsClassName = 'VaimoMenuTypeSelector';
    protected $_instanceName = 'vaimoMenuType';

    protected function _toHtml()
    {
        $types = Mage::getSingleton('vaimo_menu/type')->getAll();
        $types = array_map(function($item) {
            unset($item['label']);
            return $item;
        }, $types);

        $this->_setMenuTypesData($types);
        $this->_init($this->getSelectName(), $this->getSkinUrl('images/menu/types/'));

        return parent::_toHtml();
    }
}