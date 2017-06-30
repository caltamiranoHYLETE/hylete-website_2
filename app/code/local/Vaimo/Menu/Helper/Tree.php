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

class Vaimo_Menu_Helper_Tree extends Mage_Core_Helper_Abstract
{
    const LOOP_BREAK = -1;

    /**
     * Creates a tree structure from flat array of elements that have value set for: a) node id b) parent id
     *
     * WARNING: The items in the tree are references (this means that even if you don't pass the tree as
     * reference, the items are silently still get all the modifications). Be sure to understand the underlying
     * principles and consequences of this.
     *
     * @param $array
     * @param $rootId
     *
     * @return array
     */
    public function flatArrayToArrayTreeWithReferences(&$array, $rootId = null)
    {
        $roots = array();
        $_array = array();

        foreach ($array as &$item) {
            $_array[$item['entity_id']] = &$item;
        }

        foreach ($array as &$item) {
            if (isset($item['parent_id']) && $item['parent_id'] && isset($_array[$item['parent_id']])) {
                unset($parent);
                $parent = &$_array[$item['parent_id']];

                if (!isset($parent['children'])) {
                    $parent['children'] = array();
                }

                $parent['children'][] = &$item;
            } else {
                if ($rootId === null || $rootId == $item['entity_id']) {
                    $roots[] = &$item;
                }
            }
        }

        return $roots;
    }

    /**
     * Note that we need to get rid of the references so we make a deep-copy of the array by going through serialization.
     *
     * @param $array
     * @param $rootId
     *
     * @return mixed
     */
    public function flatArrayToArrayTree(&$array, $rootId = null)
    {
        $tree = $this->flatArrayToArrayTreeWithReferences($array, $rootId);

        return unserialize(serialize($tree));
    }

    public function flatArrayToObjectTree($array)
    {
        $tree = $this->flatArrayToArrayTree($array);

        return $this->arrayTreeToObjectTree($tree);
    }

    public function arrayTreeToObjectTree($tree)
    {
        $this->treeWalkUpdate($tree, function(&$item) {
            $item = new Vaimo_Menu_Item($item);
        });

        return $tree;
    }

    public function objectTreeToArrayTree($tree)
    {
        $flat = $this->objectTreeToFlatArray($tree);
        $tree = $this->flatArrayToArrayTreeWithReferences($flat);

        return $tree;
    }

    public function objectTreeToFlatArray($tree)
    {
        $items = $this->arrayTreeToFlatArray($tree, true);
        foreach ($items as &$item) {
            $item = $item->getData();
            unset($item['children']);
        }

        return $items;
    }

    public function arrayTreeToFlatArray($tree, $keepChildren = false)
    {
        $items = array();
        $anonymousCounter = 0;
        $this->treeWalk($tree, function($item, $parent) use (&$items, &$anonymousCounter, $keepChildren) {
            if (!isset($item['entity_id']) || !($id = $item['entity_id'])) {
                $id = 'anon' . $anonymousCounter++;
                $item['entity_id'] = $id;
            }

            $item['parent_id'] = $parent ? $parent['entity_id'] : null;

            if (!$keepChildren) {
                unset($item['children']);
            }

            $items[$id] = $item;
        });

        return $items;
    }

    /**
     * Walk over all tree nodes (including pseudos) - with the full ability to update them
     *
     * @param array|$tree
     * @param callable $itemHook
     * @param callable $stackUpdateHook
     * @param bool $skipRoot
     * @return mixed
     *
     * @throws Exception
     */
    public function treeWalkUpdate(&$tree, closure $itemHook, closure $stackUpdateHook = null, $skipRoot = false)
    {
        if (!is_array($tree) && !is_a($tree, 'Vaimo_Menu_Item')) {
            throw Mage::exception('Vaimo_Menu', 'Invalid argument, array expected', Vaimo_Menu_Exception::ARRAY_VALUE_REQUIRED);
        }

        $branchStack = array(&$tree);
        $parentStack = array(false);

        $branchStackIndex = 0;
        while (isset($branchStack[$branchStackIndex]) && isset($parentStack[$branchStackIndex])) {
            unset($itemStack, $parent, $_item, $itemStackKeys);
            $itemStack = &$branchStack[$branchStackIndex];
            $parent = &$parentStack[$branchStackIndex];
            $branchStackIndex++;

            foreach ($itemStack as &$item) {
                if (isset($item['children']) && ($children = $item['children'])) {
                    /**
                     * Restore the potentially lost reference-link here (when loading tree from cache)
                     */
                    if (!is_object($item) && !is_object($item['children'])) {
                        $item['children'] = &$children;
                    }

                    $branchStack[] = &$children;
                    $parentStack[] = &$item;

                    if ($stackUpdateHook) {
                        $stackUpdateHook($item);
                    }
                }

                if ($parent || !$skipRoot) {
                    $result = $itemHook($item, $parent);

                    if ($result === self::LOOP_BREAK) {
                        break 2;
                    }
                }

                unset($item, $children);
            }
        }

        return $tree;
    }

    /**
     * Walk over all tree nodes (including pseudos) - does not fully support item updates
     *
     * @param $tree
     * @param callable $itemHook
     * @param callable $stackUpdateHook
     * @param bool $skipRoot
     * @return mixed
     *
     * @throws Exception
     */
    public function treeWalk(&$tree, closure $itemHook, closure $stackUpdateHook = null, $skipRoot = false)
    {
        $branchStack = array($tree);
        $parentStack = array($parent = null);
        $itemStack = array();
        $snipIds = array();

        while ($itemStack || (($itemStack = array_shift($branchStack))
                && ($parent = array_shift($parentStack)) !== false)
        ) {
            $keepThisItem = true;
            $item = array_shift($itemStack);

            if ($parent || !$skipRoot) {
                if ($itemHook) {
                    $keepThisItem = $itemHook($item, $parent);

                    if ($keepThisItem === self::LOOP_BREAK) {
                        break 1;
                    }
                }
            }

            if ($keepThisItem === false) {
                $children = $parent['children'];

                if (is_object($item)) {
                    array_splice($children, count($children) - count($itemStack) - 1, 1);
                    $parent->setChildren($children);
                } else {
                    $snipIds[$item['entity_id']] = count($children) - count($itemStack) - 1;
                }

                continue;
            }

            if (isset($item['children']) && ($children = $item['children'])) {
                if (!is_numeric(key($children))) {
                    throw Mage::exception('Vaimo_Menu', 'Provided tree is malformed', Vaimo_Menu_Exception::TREE_MALFORMED);
                }

                $shouldAddToStack = true;
                if ($stackUpdateHook) {
                    $shouldAddToStack = $stackUpdateHook($item);
                }

                if ($shouldAddToStack !== false) {
                    $branchStack[] = $children;
                    $parentStack[] = $item;
                }
            }
        }

        if ($snipIds) {
            $this->treeWalkUpdate($tree, function(&$item, &$parent) use ($snipIds) {
                if (isset($snipIds[$item['entity_id']])) {
                    array_splice($parent['children'], $snipIds[$item['entity_id']], 1);
                }
            });
        }

        return $tree;
    }

    /**
     * Walk over all tree nodes (EXCLUDING pseudos) - with the full ability to update them.
     *
     * Note that the function also passes actual item level down to the closures.
     *
     * @param $tree
     * @param callable $itemHook
     * @param callable $stackUpdateHook
     * @param bool $skipRoot
     * @return mixed
     */
    public function treeWalkStrict($tree, closure $itemHook, closure $stackUpdateHook = null, $skipRoot = false)
    {
        $level = 1;
        $levelStack = !$skipRoot ? array($level) : array();
        $parentId = false;

        $tree = $this->treeWalk($tree, function($item, $parent) use ($itemHook, &$parentId, &$levelStack, &$level) {
            if ($parent['entity_id'] !== $parentId) {
                $level = (int)array_shift($levelStack);
                $parentId = $parent['entity_id'];
            }

            $keepThisItem = true;
            if (!isset($item['is_pseudo'])) {
                $keepThisItem = $itemHook($item, $parent, $level);
            }

            return $keepThisItem;
        }, function($item) use ($stackUpdateHook, &$levelStack, &$level) {
            $addedToChildrenStack = true;

            if ($stackUpdateHook && !isset($item['has_pseudos'])) {
                $addedToChildrenStack = $stackUpdateHook($item, $level);
            }

            if ($addedToChildrenStack !== false) {
                if (!isset($item['has_pseudos'])) {
                    $levelStack[] = $level + 1;
                } else {
                    $levelStack[] = $level;
                }
            }

            return $addedToChildrenStack;
        }, $skipRoot);

        return $tree;
    }

    public function serializeObjectTree($tree)
    {
        return serialize($this->objectTreeToArrayTree($tree));
    }

    public function treeExtract($tree, $fromLevel = null, $rootLevelItemFilter = array())
    {
        $extractedTree = array();

        $idFilter = array_flip($rootLevelItemFilter);

        $this->treeWalkStrict($tree, function($item, $parent, $level) use (&$extractedTree, $fromLevel, $idFilter) {
            if ((!$fromLevel || $level == $fromLevel) && (!$idFilter || isset($idFilter[$item['entity_id']]))) {
                $extractedTree[] = &$item;
            }
        }, function($item, $level) use ($fromLevel) {
            if ($item && $level >= $fromLevel) {
                return false;
            }
            return true;
        });

        return $extractedTree;
    }

    public function find(&$tree, $targetId)
    {
        $foundItem = false;
        $foundChildren = array();

        $this->treeWalk($tree, function($item) use (&$foundItem, &$foundChildren, $targetId) {
            if ($item['entity_id'] == $targetId) {
                $foundItem = $item;
                return -1;
            }

            if ($item['parent_id'] == $targetId) {
                $foundChildren[] = $item;
            }

            return 0;
        });

        if (!$foundItem && $foundChildren) {
            $foundItem = array('entity_id' => $targetId, 'children' => $foundChildren);
            
            if (is_object(reset($foundChildren))) {
                $foundItem = new Vaimo_Menu_Item($foundItem);
            }
        }

        return $foundItem;
    }
}
