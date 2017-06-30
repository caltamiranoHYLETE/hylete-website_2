<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Model_Page extends Vaimo_Cms_Model_Abstract
{
    protected $_requiredArguments = array('handle', 'store');

    protected $_handle;
    protected $_storeId;

    protected $_live;
    protected $_groups = array();

    public function __construct(array $args = array())
    {
        $required = $this->_extractRequiredArgs($args);

        $this->_handle = $required['handle'];
        $this->_storeId = $required['store'];

        parent::__construct($args);
    }

    public function hasStoreSpecificContent($revisionId = false)
    {
        $group = $this->_getGroup($revisionId);

        return $group->hasStoreSpecificContent();
    }

    protected function _getStructuresFromGroup($group)
    {
        $structures = $group->getStructures();

        return is_array($structures) ? $structures : array();
    }

    public function getStructures($revisionId = false)
    {
        $group = $this->_getGroup($revisionId);

        return $this->_getStructuresFromGroup($group);
    }

    public function getStageStructures($revisionId)
    {
        $live = $this->_getGroup();
        $draft = $this->_getGroup($revisionId);

        return array_merge($live->getStructures(), $draft->getStructures());
    }

    public function assignStructure($structure, $revisionId = false)
    {
        $group = $this->_getGroup($revisionId);

        $ownStructure = $this->getFactory()->getHelper('vaimo_cms/group')
            ->getAssignableStructureForGroup($group, $structure);

        return $group->assignStructure($ownStructure);
    }

    public function save()
    {
        $groups = $this->_getInstantiatedGroups();

        foreach ($groups as $index => $group) {
            $group->save();
        }
    }

    protected function _getInstantiatedGroups()
    {
        $groups = array_values($this->_groups);
        $groups[] = $this->_live;

        return array_filter($groups);
    }

    protected function _getGroup($revisionId = false)
    {
        if ($revisionId !== false) {
            if (isset($this->_groups[$revisionId])) {
                return $this->_groups[$revisionId];
            }

            $model = $this->getFactory()->getHelper('vaimo_cms/group')->getDraftGroup(
                $this->_handle,
                $this->_storeId,
                $revisionId
            );

            $this->_groups[$revisionId] = $model;
        } else {
            if ($this->_live) {
                return $this->_live;
            }

            $model = $this->getFactory()->getHelper('vaimo_cms/group')->getLiveGroup(
                $this->_handle,
                $this->_storeId
            );

            $this->_live = $model;
        }

        return $model;
    }

    public function invalidate($revisionId = false)
    {
        $group = $this->_getGroup($revisionId);

        $group->invalidate();

        return $this;
    }
}