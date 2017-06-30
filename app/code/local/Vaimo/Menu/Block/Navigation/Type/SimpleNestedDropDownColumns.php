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

class Vaimo_Menu_Block_Navigation_Type_SimpleNestedDropDownColumns extends Vaimo_Menu_Block_Navigation_Type_Breakpoints
{
    protected $_typeClasses = array('simple-nested-dropdown-columns', 'menu-columns');
    protected $_typeConfig = array(
        0 => array(
            'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
            'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE
        ),
        1 => array(
            'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
            'children' => Vaimo_Menu_Model_Type::ITEMS_NESTED,
            'break_type' => Vaimo_Menu_Model_Type::COLUMNS
        ),
        2 => array(
            'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL
        )
    );

    public function getChildListClass()
    {
        $class = parent::getChildListClass();

        if ($this->belongsToNonDefaultMenuGroup()) {
            return $class;
        }

        $config = $this->getMenuLevelConfig();
        if ($config->getChildren() == Vaimo_Menu_Model_Type::ITEMS_NESTED) {
            $class .= ' menu-children-nested';
        }

        return trim($class);
    }

    public function getItemHierarchyClass()
    {
        $class = parent::getItemHierarchyClass();

        if ($this->belongsToNonDefaultMenuGroup()) {
            return $class;
        }

        $config = $this->getMenuLevelConfig();
        if ($config->getChildren() == Vaimo_Menu_Model_Type::ITEMS_NESTED && $this->shouldShowChildren()) {
            $class .= ' menu-nested-parent';
        }

        return trim($class);
    }
}