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

abstract class Vaimo_Menu_Block_Navigation_Type_Breakpoints extends Vaimo_Menu_Block_Navigation_Type_Base
{
    protected $_breakpointTemplates = array();

    protected function _getMenuItemTree($tree)
    {
        $block = $this;
        $tree = parent::_getMenuItemTree($tree);

        Mage::helper('vaimo_menu/tree')->treeWalkStrict($tree, function($item, $parent, $level) use ($block) {
            if (!($container = $parent->getLastChild())) {
                $container = $parent->addPseudoChild(new Vaimo_Menu_Item(array('is_container' => true)));
            }

            $container->addChild($item);

            if ($block->getConfigForAbsoluteLevel($level)->getBreakType() && $item->getColumnBreakpoint()) {
                $parent->addPseudoChild(new Vaimo_Menu_Item(array('is_container' => true)));
            }
        }, function($item) use ($block) {
            $item->unsChildren();
            $item->setChildren(array());
        }, true);

        return $tree;
    }

    protected function _increaseIterationLevel()
    {
        if (!$this->_isContainer()) {
            parent::_increaseIterationLevel();
        }
    }

    public function shouldShowChildren($group = Vaimo_Menu_Model_Group::DEFAULT_GROUP)
    {
        if ($this->_isContainer() && $this->getChildren($group)) {
            return true;
        }

        return parent::shouldShowChildren($group);
    }

    public function getChildren($group = Vaimo_Menu_Model_Group::DEFAULT_GROUP)
    {
        if ($this->_isContainer() && $group == Vaimo_Menu_Model_Group::DEFAULT_GROUP) {
            return parent::getChildren();
        }

        return parent::getChildren($group);
    }

    public function shouldShowLink()
    {
        if ($this->_isContainer()) {
            return false;
        }

        return parent::shouldShowLink();
    }

    public function _isContainer()
    {
        $category = $this->getCategory();
        return $category && $category->getIsContainer();
    }

    public function getItemHierarchyClass()
    {
        if (!$this->_isContainer()) {
            return parent::getItemHierarchyClass();
        }

        return '';
    }

    public function getItemPlacementClass($skipContainerDetection = false)
    {
        $placementClasses = parent::getItemPlacementClass();

        if ($this->_isContainer() || $skipContainerDetection) {
            if ($config = $this->getMenuLevelConfigForNextLevel()) {
                if ($config->getBreakType() == Vaimo_Menu_Model_Type::ROWS) {
                    $placementClasses .= ' menu-bp-row';
                } else {
                    $placementClasses .= ' menu-bp-column ' . (!$skipContainerDetection ? $this->getItemMarkers() : '');
                }
            }
        }

        return trim($placementClasses);
    }

    public function setBreakpointTemplate($template, $level = -1, $group = Vaimo_Menu_Model_Group::DEFAULT_GROUP)
    {
        $this->_setItemTemplate($this->_breakpointTemplates, $template, $level, $group);

        return $this;
    }

    public function getTemplateForLevel($level = -1, $group = -1)
    {
        if ($this->_isContainer()) {
            if ($template = $this->_getTemplateForLevel($this->_breakpointTemplates, $level, Vaimo_Menu_Model_Group::DEFAULT_GROUP)) {
                return $template;
            }
        }

        return parent::getTemplateForLevel($level, $group);
    }

    public function getChildListClass()
    {
        $class = '';
        $category = $this->getCategory();

        if ($this->getRelativeLevel() >= 0) {
            $class = parent::getChildListClass();
        }

        if ($this->_isContainer() || $this->belongsToNonDefaultMenuGroup()) {
            $class = str_replace(' menu-vlist', '', $class);
        }

        if ($this->belongsToNonDefaultMenuGroup()) {
            return $class;
        }

        if ($this->_isContainer()) {
            $class .= ' menu-bp-items';
        } elseif (!$category->getIsPseudo()) {
            $class .= ' menu-bp-wrapper';
        }

        if (($config = $this->getMenuLevelConfigForNextLevel()) && ($this->_isContainer() || !$config->getBreakType())) {
            if ($config->getDirection() == Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL) {
                $class .= ' menu-hlist';
            }
        }

        return trim($class);
    }

    /**
     * Extract each item as separate child
     *
     * @param $group
     * @return array|mixed
     */
    public function _getGroupMembers($group)
    {
        $members = parent::_getGroupMembers($group);
        $_members = array();

        foreach ($members as $member) {
            $children = $member->getChildren();
            $_members = array_merge($_members, $children);
        }

        return $_members;
    }
}