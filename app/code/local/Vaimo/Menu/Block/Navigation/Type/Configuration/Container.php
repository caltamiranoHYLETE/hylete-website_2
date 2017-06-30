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

class Vaimo_Menu_Block_Navigation_Type_Configuration_Container extends Mage_Core_Block_Text_List
{
    protected $_typeCode;
    protected $_callStack = array();
    protected $_children = array();

    public function getIsConfigurationContainer()
    {
        return true;
    }

    public function __call($method, $args)
    {
        if ($method != 'setType') {
            $this->_callStack[] = array('method' => $method, 'args' => $args);
        } else {
            parent::__call($method, $args);
        }
    }

    public function setMenuType($type)
    {
        $this->_typeCode = $type;
    }

    protected function _toHtml()
    {
        $blockType = Mage::getSingleton('vaimo_menu/type')->getNavigationBlockType($this->_typeCode);
        $navigationBlock = Mage::app()->getLayout()->createBlock($blockType);

        if (!$navigationBlock) {
            Mage::throwException('Type block type for menu type not found: "' . $this->_typeCode . '"');
        }

        foreach ($this->getChild() as $child) {
            $this->unsetChild($child->getBlockAlias());
            $navigationBlock->insert($child);
        }

        $this->insert($navigationBlock);

        foreach ($this->_callStack as $call) {
            call_user_func_array(array($navigationBlock, $call['method']), $call['args']);
        }

        $this->_callStack = array();

        return $navigationBlock->toHtml();
    }
}