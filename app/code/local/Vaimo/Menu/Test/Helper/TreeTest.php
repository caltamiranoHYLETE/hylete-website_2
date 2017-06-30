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

class Vaimo_Menu_Test_Helper_TreeTest extends Vaimo_Menu_Test_BaseCase
{
    protected $_model;
    protected $_flatArray;
    protected $_arrayTree;
    protected $_objectTree;

    public function setUp()
    {
        parent::setUp();

        $this->_flatArray = array(
            2 => array('entity_id' => 2, 'parent_id' => null),
            3 => array('entity_id' => 3, 'parent_id' => 2),
            6 => array('entity_id' => 6, 'parent_id' => 2),
            7 => array('entity_id' => 7, 'parent_id' => 3),
            5 => array('entity_id' => 5, 'parent_id' => 6),
            9 => array('entity_id' => 9, 'parent_id' => 7)
        );

        $this->_arrayTree = array(
            array(
                'entity_id' => 2,
                'parent_id' => null,
                'children' => array(
                    array(
                        'entity_id' => 3,
                        'parent_id' => 2,
                        'children' => array(
                            array(
                                'entity_id' => 7,
                                'parent_id' => 3,
                                'children' => array(
                                    array(
                                        'entity_id' => 9,
                                        'parent_id' => 7
                                    )
                                )
                            )
                        )
                    ),
                    array(
                        'entity_id' => 6,
                        'parent_id' => 2,
                        'children' => array(
                            array(
                                'entity_id' => 5,
                                'parent_id' => 6
                            )
                        )
                    ),
                )
            )
        );

        $this->_objectTree = array(
            new Vaimo_Menu_Item(array(
                'entity_id' => 2,
                'children' => array(
                    new Vaimo_Menu_Item(array(
                        'entity_id' => 3,
                        'children' => array(
                            new Vaimo_Menu_Item(array(
                                'entity_id' => 7,
                                'children' => array(
                                    new Vaimo_Menu_Item(array('entity_id' => 9))
                                )
                            ))
                        )
                    )),
                    new Vaimo_Menu_Item(array(
                        'entity_id' => 6,
                        'children' => array(
                            new Vaimo_Menu_Item(array('entity_id' => 5))
                        )
                    )),
                )
            ))
        );


        $this->_model = new Vaimo_Menu_Helper_Tree();
    }

    public function testFlatArrayToArrayTreeWithReferencesShouldReturnArrayTreeWhereItemsAreModifiableViaInputArray()
    {
        $tree = $this->_model->flatArrayToArrayTreeWithReferences($this->_flatArray);
        $before = serialize($tree);

        $this->_flatArray[2]['entity_id'] = 'test';
        $after = serialize($tree);

        $this->assertNotEquals($before, $after);
    }

    public function testFlatArrayToArrayTreeShouldReturnArrayTreeWhereItemsCanNotBeModifiedWithOriginalArray()
    {
        $tree = $this->_model->flatArrayToArrayTree($this->_flatArray);
        $before = serialize($tree);

        $this->_flatArray[2]['entity_id'] = 'test';
        $after = serialize($tree);

        $this->assertEquals($before, $after);
    }

    public function testFlatArrayToArrayTreeWithReferencesShouldReturnAllItemsThatReferToParentThatDoesNotExistAsRoots()
    {
        $flatArray = array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 3, 'parent_id' => 2),
            array('entity_id' => 10, 'parent_id' => 3),
            array('entity_id' => 4, 'parent_id' => 5),
            array('entity_id' => 6, 'parent_id' => 4),
            array('entity_id' => 7, 'parent_id' => 22),
            array('entity_id' => 8, 'parent_id' => 7),
        );

        $expected = array(
            array(
                'entity_id' => 2,
                'parent_id' => 1,
                'children' => array(
                    array(
                        'entity_id' => 3,
                        'parent_id' => 2,
                        'children' => array(

                            array(
                                'parent_id' => 3,
                                'entity_id' => 10
                            )
                        )
                    )
                )
            ),
            array(
                'entity_id' => 4,
                'parent_id' => 5,
                'children' => array(
                    array(
                        'parent_id' => 4,
                        'entity_id' => 6
                    )
                )
            ),
            array(
                'entity_id' => 7,
                'parent_id' => 22,
                'children' => array(
                    array(
                        'parent_id' => 7,
                        'entity_id' => 8
                    )
                )
            )
        );

        $result = $this->_model->flatArrayToArrayTreeWithReferences($flatArray);

        $this->assertEquals($expected, $result);
    }

    public function testFlatArrayToArrayTreeWithReferencesShouldReturnOnlyRootSpecifiedInParameters()
    {
        $flatArray = array(
            array('entity_id' => 2, 'parent_id' => 1),
            array('entity_id' => 10, 'parent_id' => 3),
            array('entity_id' => 7, 'parent_id' => 22),
        );

        $expected1 = array(array('entity_id' => 10, 'parent_id' => 3));
        $expected2 = array(array('entity_id' => 2, 'parent_id' => 1));

        $result1 = $this->_model->flatArrayToArrayTreeWithReferences($flatArray, 10);
        $result2 = $this->_model->flatArrayToArrayTreeWithReferences($flatArray, 2);

        $this->assertEquals($expected1, $result1);
        $this->assertEquals($expected2, $result2);
    }

    public function testFlatArrayToArrayTreeWithReferencesShouldRemoveParentIdFromGeneratedTreeElements()
    {
        $flatArray = array(
            array('entity_id' => 2, 'parent_id' => 1, 'other_value' => 'test'),
            array('entity_id' => 3, 'parent_id' => 2, 'other_value2' => 'test2'),
        );

        $expected = array(
            array(
                'entity_id' => 2,
                'parent_id' => 1,
                'other_value' => 'test',
                'children' => array(
                    array(
                        'entity_id' => 3,
                        'parent_id' => 2,
                        'other_value2' => 'test2'
                    )
                )
            )
        );

        $result = $this->_model->flatArrayToArrayTreeWithReferences($flatArray);

        $this->assertEquals($expected, $result);
    }

    public function testFlatArrayToArrayTreeWithReferencesShouldReturnTreeStructureThatIsDerivedFromArrayItemValues()
    {
        $expected = $this->_arrayTree;

        $result = $this->_model->flatArrayToArrayTreeWithReferences($this->_flatArray);

        $this->assertEquals($expected, $result);
    }

    public function testFlatArrayToArrayTreeWithReferencesShouldReturnTreeSturctureEvenWhenFlatItemKeyDoNotMatchWithNodeIds()
    {
        $expected = $this->_arrayTree;

        $result = $this->_model->flatArrayToArrayTreeWithReferences(array_values($this->_flatArray));

        $this->assertEquals($expected, $result);
    }

    public function testFlatArrayToObjectTreeShouldReturnObjectTreeThatIsDerivedFromArrayItemValues()
    {
        $expected = '{entity_id="2"; parent_id=""; children=({entity_id="3"; parent_id="2"; children=({entity_id="7"; parent_id="3"; children=({entity_id="9"; parent_id="7"})})}; {entity_id="6"; parent_id="2"; children=({entity_id="5"; parent_id="6"})})}';

        $roots = $this->_model->flatArrayToObjectTree($this->_flatArray);
        $results = $roots[0]->serialize();

        $this->assertEquals($expected, $results);
    }

    public function testArrayTreeToObjectTreeShouldReturnObjectTreeThatIsBasedOnTheInputArrayTree()
    {
        $expected = '{entity_id="2"; parent_id=""; children=({entity_id="3"; parent_id="2"; children=({entity_id="7"; parent_id="3"; children=({entity_id="9"; parent_id="7"})})}; {entity_id="6"; parent_id="2"; children=({entity_id="5"; parent_id="6"})})}';

        $roots = $this->_model->arrayTreeToObjectTree($this->_arrayTree);
        $results = $roots[0]->serialize();

        $this->assertEquals($expected, $results);
    }

    public function testArrayTreeToObjectTreeShouldNotModifyTheInputVariable()
    {
        $expected = serialize($this->_arrayTree);
        $this->_model->arrayTreeToObjectTree($this->_arrayTree);

        $this->assertEquals($expected, serialize($this->_arrayTree));
    }

    public function testObjectTreeToArrayTreeShouldReturnTreeWithArrayNodesThatIsBasedOnInputTreeBasedOnObjects()
    {
        $expected = $this->_arrayTree;

        $results = $this->_model->objectTreeToArrayTree($this->_objectTree);

        $this->assertEquals($expected, $results);
    }

    public function testObjectTreeToFlatArrayShouldReturnFlatArrayWithItemValuesAreDerivedFromTreeNodesAndIdsAsKeys()
    {
        $expected = $this->_flatArray;

        $results = $this->_model->objectTreeToFlatArray($this->_objectTree);

        $this->assertEquals($expected, $results);
    }

    public function testArrayTreeToFlatArrayShouldReturnFlatArrayWhereArrayItemValuesAreDerivedFromArrayTreeNodes()
    {
        $expected = $this->_flatArray;

        $results = $this->_model->arrayTreeToFlatArray($this->_arrayTree);

        $this->assertEquals($expected, $results);
    }

    public function testArrayTreeToFlatArrayShouldKeepChildrenIfKeepChildrenFlagIsSet()
    {
        $expected = array(
            2 => array('entity_id' => 2, 'parent_id' => null, 'children' => array(array('entity_id' => 3), array('entity_id' => 6))),
            3 => array('entity_id' => 3, 'parent_id' => 2),
            6 => array('entity_id' => 6, 'parent_id' => 2)
        );
        $tree = array(
            array(
                'entity_id' => 2,
                'children' => array(
                    array('entity_id' => 3),
                    array('entity_id' => 6),
                )
            )
        );

        $results = $this->_model->arrayTreeToFlatArray($tree, true);

        $this->assertEquals($expected, $results);
    }

    public function testTreeWalkUpdateShouldUpdateItemsInArrayTree()
    {
        $tree = array(
            array(
                'entity_id' => 2,
                'children' => array(
                    array('entity_id' => 3),
                    array('entity_id' => 6),
                )
            )
        );
        $expected = array(
            array(
                'entity_id' => 2,
                'test' => 'test123',
                'children' => array(
                    array(
                        'entity_id' => 3,
                        'test' => 'test123'
                    ),
                    array(
                        'entity_id' => 6,
                        'test' => 'test123'
                    ),
                )
            )
        );

        $this->_model->treeWalkUpdate($tree, function(&$item) {
            $item['test'] = 'test123';
        });

        $this->assertEquals($expected, $tree);
    }

    /**
     * @expectedException       Vaimo_Menu_Exception
     * @expectedExceptionCode   Vaimo_Menu_Exception::ARRAY_VALUE_REQUIRED
     */
    public function testTreeWalkUpdateShouldThrowAnExceptionIfNullIsPassedInAsFirstArgument()
    {
        $tree = null;
        $this->_model->treeWalkUpdate($tree, function($item) {});
    }

    /**
     * @expectedException       Vaimo_Menu_Exception
     * @expectedExceptionCode   Vaimo_Menu_Exception::ARRAY_VALUE_REQUIRED
     */
    public function testTreeWalkUpdateShouldThrowAnExceptionIfBooleanIsPassedInAsFirstArgument()
    {
        $tree = false;
        $this->_model->treeWalkUpdate($tree, function($item) {});
    }

    /**
     * @expectedException       Vaimo_Menu_Exception
     * @expectedExceptionCode   Vaimo_Menu_Exception::ARRAY_VALUE_REQUIRED
     */
    public function testTreeWalkUpdateShouldThrowAnExceptionIfStringIsPassedInAsFirstArgument()
    {
        $tree = '';
        $this->_model->treeWalkUpdate($tree, function($item) {});
    }

    public function testTreeWalkUpdateShouldNotThrowAnExceptionIfArrayIsPassedInAsFirstArgument()
    {
        $tree = array();
        $this->_model->treeWalkUpdate($tree, function($item) {});
    }

    public function testTreeWalkUpdateShouldNotThrowAnExceptionIfVaimoMenuItemObjectIsPassedInAsFirstArgument()
    {
        $tree = new Vaimo_Menu_Item();
        $this->_model->treeWalkUpdate($tree, function($item) {});
    }

    public function testTreeWalkShouldNotUpdateItemsInArrayTree()
    {
        $expected = $tree = array(
            array(
                'entity_id' => 2,
                'children' => array(
                    array('entity_id' => 3),
                    array('entity_id' => 6),
                )
            )
        );

        $this->_model->treeWalk($tree, function(&$item) {
            $item['test'] = 'test123';
        });

        $this->assertEquals($expected, $tree);
    }

    public function testTreeWalkShouldIterateThroughAllTreeItems()
    {
        $result = array();
        $expected = array_keys($this->_flatArray);

        $this->_model->treeWalk($this->_arrayTree, function($item) use (&$result) {
            $result[] = $item['entity_id'];
        });

        $this->assertEquals($expected, $result);
    }

    public function testTreeWalkShouldSendRelevantItemParentsToClosureFunctions()
    {
        $result = array();
        $expected = $this->_flatArray;

        $this->_model->treeWalk($this->_arrayTree, function($item, $parent) use (&$result) {
            $id = $item['entity_id'];
            $result[$id] = array('entity_id' => $id, 'parent_id' => $parent ? $parent['entity_id'] : $parent);
        });

        $this->assertEquals($expected, $result);
    }

    public function testTreeWalkShouldIterateThroughAllTreeItemsEvenIfTheyAreObjects()
    {
        $expected = array_keys($this->_flatArray);
        $result = array();

        $this->_model->treeWalk($this->_objectTree, function(&$item) use (&$result) {
            $result[] = $item->getEntityId();
        });

        $this->assertEquals($expected, $result);
    }

    public function testTreeWalkShouldRemoveBranchFromObjectTreeIfItemHandlerReturnsFalse()
    {
        $expected = '{entity_id="2"; children=({entity_id="6"; children=({entity_id="5"})})}';
        $result = array();

        $this->_model->treeWalk($this->_objectTree, function(&$item) use (&$result) {
            if ($item['entity_id'] == 3) {
                return false;
            }

            return true;
        });

        $result = $this->_objectTree[0]->serialize();

        $this->assertEquals($expected, $result);
    }

    public function testTreeWalkShouldRemoveBranchFromArrayTreeIfItemHandlerReturnsFalse()
    {
        $expected = array(
            array(
                'entity_id' => 2,
                'parent_id' => null,
                'children' => array(
                    array(
                        'entity_id' => 6,
                        'parent_id' => 2,
                        'children' => array(
                            array(
                                'entity_id' => 5,
                                'parent_id' => 6
                            )
                        )
                    ),
                )
            )
        );

        $result = array();

        $this->_model->treeWalk($this->_arrayTree, function(&$item) use (&$result) {
            if ($item['entity_id'] == 3) {
                return false;
            }

            return true;
        });

        $this->assertEquals($expected, $this->_arrayTree);
    }

    public function testTreeWalkStrictShouldSkipAllPseudoItems()
    {
        $tree = array(
            array(
                'entity_id' => 2,
                'children' => array(
                    array(
                        'entity_id' => 3
                    ),
                    array(
                        'entity_id' => 6
                    ),
                    array(
                        'entity_id' => 7,
                        'is_pseudo' => true,
                        'children' => array(
                            array(
                                'entity_id' => 'pseudo1',
                                'is_pseudo' => true,
                                'children' => array(
                                    array('entity_id' => 9),
                                    array('entity_id' => 10),
                                    array('entity_id' => 11, 'is_pseudo' => true),
                                    array('entity_id' => 12)
                                )
                            ),
                            array(
                                'entity_id' => 13,
                                'is_pseudo' => true,
                                'children' => array(
                                        array(
                                            'entity_id' => 14,
                                            'is_pseudo' => true,
                                            'children' => array(
                                                array('entity_id' => 15)
                                            )
                                    )
                                )
                            )
                        )
                    ),
                )
            )
        );

        $expected = array(2, 3, 6, 9, 10, 12,15);
        $result = array();

        $this->_model->treeWalkStrict($tree, function(&$item) use (&$result) {
            $result[] = $item['entity_id'];
        });

        $this->assertEquals($expected, $result);
    }

    public function testSerializeObjectTreeShouldReturnSerializedArrayTree()
    {
        $expected = serialize($this->_arrayTree);

        $result = $this->_model->serializeObjectTree($this->_objectTree);

        $this->assertEquals($expected, $result);
    }

    public function testTreeWalkStrictShouldIterateThroughItemsInTheOrderOfItemDepth()
    {
        $tree = array(
            array(
                'entity_id' => 2,
                'children' => array(
                    array(
                        'entity_id' => 3,
                        'children' => array(
                            array(
                                'entity_id' => 7,
                                'children' => array(
                                    array(
                                        'entity_id' => 9,
                                        'children' => array(
                                            array('entity_id' => 10),
                                            array(
                                                'entity_id' => 16,
                                                'children' => array(
                                                    array('entity_id' => 17)
                                                )
                                            ),
                                            array('entity_id' => 18),
                                        )
                                    ),
                                )
                            ),
                        )
                    ),
                    array(
                        'entity_id' => 6,
                        'children' => array(
                            array('entity_id' => 5)
                        ),
                    ),
                    array(
                        'entity_id' => 11,
                        'children' => array(
                            array('entity_id' => 12),
                            array('entity_id' => 15)
                        ),
                    ),
                    array(
                        'entity_id' => 13,
                        'children' => array(
                            array('entity_id' => 14)
                        ),
                    )
                )
            )
        );

        $expected = '2,3,6,11,13,7,5,12,15,14,9,10,16,18,17';

        $result = array();
        $this->_model->treeWalkStrict($tree, function($item) use (&$result) {
            $result[] = $item['entity_id'];
        });

        $this->assertEquals($expected, implode(',', $result));

    }

    public function testTreeWalkStrictShouldPassCorrectCurrentLevelToItemHandler()
    {
        $tree = array(
            array(
                'entity_id' => 2,
                'children' => array(
                    array('entity_id' => 3),
                    array('entity_id' => 6),
                    array(
                        'entity_id' => 7,
                        'has_pseudos' => true,
                        'children' => array(
                            array(
                                'entity_id' => 'pseudo1',
                                'is_pseudo' => true,
                                'children' => array(
                                    array('entity_id' => 9),
                                    array('entity_id' => 10),
                                    array('entity_id' => 12)
                                )
                            ),
                            array(
                                'entity_id' => 13,
                                'is_pseudo' => true,
                                'children' => array(
                                    array(
                                        'entity_id' => 14,
                                        'children' => array(
                                            array(
                                                'entity_id' => 15,
                                                'children' => array(
                                                    array('entity_id' => 66),
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            array(
                                'entity_id' => 'pseudo55',
                                'is_pseudo' => true,
                                'has_pseudos' => true,
                                'children' => array(
                                    array(
                                        'entity_id' => 'pseudo66',
                                        'is_pseudo' => true,
                                        'has_pseudos' => true,
                                        'children' => array(
                                            array(
                                                'entity_id' => 'pseudo77',
                                                'is_pseudo' => true,
                                                'children' => array(
                                                    array(
                                                        'entity_id' => 100,
                                                        'children' => array(
                                                            array('entity_id' => 101),
                                                            array(
                                                                'entity_id' => 'pseudo88',
                                                                'is_pseudo' => true,
                                                                'children' => array(
                                                                    array('entity_id' => 102),
                                                                )
                                                            )
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    array(
                        'entity_id' => 20,
                        'children' => array(
                            array('entity_id' => 21),
                            array(
                                'entity_id' => 22,
                                'children' => array(
                                    array('entity_id' => 23),
                                )
                            )
                        )
                    ),
                )
            ),
            array('entity_id' => 30)
        );

        $expected = array(
            2 => 1,
            30 => 1,
            3 => 2,
            6 => 2,
            7 => 2,
            20 => 2,
            21 => 3,
            22 => 3,
            9 => 3,
            10 => 3,
            12 => 3,
            14 => 3,
            23 => 4,
            15 => 4,
            66 => 5,
            100 => 3,
            101 => 4,
            102 => 5
        );

        $result = array();
        $this->_model->treeWalkStrict($tree, function($item, $parent, $level) use (&$result) {
            $result[$item['entity_id']] = $level;
        });

        $this->assertEquals($expected, $result);
    }

    public function testTreeExtractShouldReturnTreeNodesFromCertainLevel()
    {
        $tree =array(
            array(
                'entity_id' => 2,
                'children' => array(
                    array(
                        'entity_id' => 3,
                        'children' => array(
                            array(
                                'entity_id' => 7,
                                'children' => array(array('entity_id' => 9))
                            )
                        )
                    ),
                    array(
                        'entity_id' => 6,
                        'children' => array(array('entity_id' => 5))
                    ),
                    array(
                        'entity_id' => 10,
                        'children' => array(
                            array(
                                'entity_id' => 11,
                                'children' => array(array('entity_id' => 12))
                            )
                        )
                    )
                ),
            )
        );

        $expectedLevel2 = array(
            array(
                'entity_id' => 3,
                'children' => array(
                    array(
                        'entity_id' => 7,
                        'children' => array(array('entity_id' => 9))
                    )
                )
            ),
            array(
                'entity_id' => 6,
                'children' => array(array('entity_id' => 5))
            ),
            array(
                'entity_id' => 10,
                'children' => array(
                    array(
                        'entity_id' => 11,
                        'children' => array(array('entity_id' => 12))
                    )
                )
            )
        );

        $expectedLevel3 = array(
            array(
                'entity_id' => 7,
                'children' => array(array('entity_id' => 9))
            ),
            array('entity_id' => 5),
            array(
                'entity_id' => 11,
                'children' => array(array('entity_id' => 12))
            )
        );

        $expectedLevel4 = array(array('entity_id' => 9), array('entity_id' => 12));

        $resultLevel2 = $this->_model->treeExtract($tree, 2);
        $resultLevel3 = $this->_model->treeExtract($tree, 3);
        $resultLevel4 = $this->_model->treeExtract($tree, 4);

        $this->assertEquals($expectedLevel2, $resultLevel2);
        $this->assertEquals($expectedLevel3, $resultLevel3);
        $this->assertEquals($expectedLevel4, $resultLevel4);
    }

    public function testTreeExtractShouldReturnFullTreeIfLevelMatchesStartingLevel()
    {
        $result = $this->_model->treeExtract($this->_arrayTree, 1);

        $this->assertEquals($this->_arrayTree, $result);
    }

    public function testTreeExtractShouldReturnOnlyThoseMainLevelItemsIfEntityIdFilterIsSpecified()
    {
        $filter = array(6);
        $expected = array(
            array(
                'entity_id' => 6,
                'parent_id' => 2,
                'children' => array(
                    array(
                        'entity_id' => 5,
                        'parent_id' => 6
                    )
                )
            )
        );

        $result = $this->_model->treeExtract($this->_arrayTree, 2, $filter);

        $this->assertEquals($expected, $result);
    }

    public function testTreeExtractShouldReturnNothingIfIdFilterMatchesWithItemsFromLowerLevel()
    {
        $filter = array(5);
        $expected = array();

        $result = $this->_model->treeExtract($this->_arrayTree, 2, $filter);

        $this->assertEquals($expected, $result);
    }

    public function testTreeExtractShouldReturnNothingIfNoFilterItemsMatch()
    {
        $filter = array(123, 333, 22, 50);
        $expected = array();

        $result = $this->_model->treeExtract($this->_arrayTree, 2, $filter);

        $this->assertEquals($expected, $result);
    }

    public function testTreeExtractShouldReturnEmptyListIfTreeDoesNotHaveSpecifiedLevel()
    {
        $result = $this->_model->treeExtract($this->_arrayTree, 10);

        $this->assertEquals(array(), $result);
    }

    /**
     * @expectedException       Vaimo_Menu_Exception
     * @expectedExceptionCode   Vaimo_Menu_Exception::TREE_MALFORMED
     */
    public function testTreeWalkShouldThrowAnExceptionWhenMalformedTreeIsProvided()
    {
        $tree = array(
            array(
                'entity_id' => 2,
                'children' => array(
                    'entity_id' => 3,
                    'is_pseudo' => true,
                    'children' => array(
                        'entity_id' => 4,
                        'children' => array('entity_id' => 5)
                    )
                )
            )
        );

        $this->_model->treeWalk($tree, function($item) {});
    }

    public function testTreeWalkShouldBreakIfItemClosureReturnsLoopBreakFlag()
    {
        $expected = array(2);
        $result = array();

        $breakFlag = Vaimo_Menu_Helper_Tree::LOOP_BREAK;
        $this->_model->treeWalk($this->_objectTree, function(&$item) use (&$result, $breakFlag) {
            $result[] = $item['entity_id'];
            return $breakFlag;
        });

        $this->assertEquals($expected, $result);
    }

    public function testFindShouldReturnItemWithSpecifiedIdAndAllItsChildren()
    {
        $expected = '{entity_id="3"; children=({entity_id="7"; children=({entity_id="9"})})}';

        $result = $this->_model->find($this->_objectTree, 3);

        $this->assertEquals($expected, $result->serialize());
    }

    public function testFindShouldReturnFalseIfItemWithSpecifiedIdIsNotFound()
    {
        $expected = false;

        $result = $this->_model->find($this->_objectTree, 12345);

        $this->assertEquals($expected, $result);
    }

    public function testFindShouldReturnItemsWrappedInPseudoItemIfSearchedIdIsFoundOnlyAsParentId()
    {
        $tree = array(
            array(
                'entity_id' => 2,
                'parent_id' => null,
                'children' => array(
                    array(
                        'entity_id' => 3,
                        'parent_id' => 2
                    ),
                    array(
                        'entity_id' => 6,
                        'parent_id' => 1234,
                        'children' => array(
                            array(
                                'entity_id' => 5,
                                'parent_id' => 6
                            )
                        )
                    ),
                    array(
                        'entity_id' => 20,
                        'parent_id' => 1234,
                        'children' => array(
                            array(
                                'entity_id' => 21,
                                'parent_id' => 22
                            )
                        )
                    ),
                )
            )
        );

        $expected = array(
            'entity_id' => 1234,
            'children' => array(
                array(
                    'entity_id' => 6,
                    'parent_id' => 1234,
                    'children' => array(
                        array(
                            'entity_id' => 5,
                            'parent_id' => 6
                        )
                    )
                ),
                array(
                    'entity_id' => 20,
                    'parent_id' => 1234,
                    'children' => array(
                        array(
                            'entity_id' => 21,
                            'parent_id' => 22
                        )
                    )
                ),
            )
        );

        $result = $this->_model->find($tree, 1234);

        $this->assertEquals($expected, $result);
    }
}