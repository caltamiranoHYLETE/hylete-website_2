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

class Vaimo_Cms_Model_Group_Type_Draft extends Vaimo_Cms_Model_Abstract
{
    protected $_requiredArguments = array('revision');

    protected $_revisionId;

    public function __construct(array $args = array())
    {
        $required = $this->_extractRequiredArgs($args);

        $this->_revisionId = $required['revision'];

        parent::__construct($args);
    }

    public function ownsStructure($structure)
    {
        if ($structure->getRevision() != $this->_revisionId) {
            return false;
        }

        return true;
    }

    public function filterStructure($structure, $scope)
    {
        return $scope->ownsEntity($structure);
    }

    public function assignStructure($structure)
    {
        $structure->setRevision($this->_revisionId);
        $structure->setPublished(false);

        return $structure;
    }

    public function prepareStructuresForSave($structures, $scope)
    {
        $helper = $this->getFactory()->getHelper('vaimo_cms/page');

        $storeIds = array($scope->getStoreId());

        foreach ($structures as $structure) {
            if ($structure->getPublished()) {
                $structure->setDataChanges(false);
                continue;
            }

            if (!$structure->getId()) {
                $structure->setOrigData('structure', null);
            }

            $handle = $helper->getRevisionHandle(
                $structure->getHandle(),
                $this->_revisionId
            );

            $structure->setWidgetHandle($handle);
            $structure->setStoreIds($storeIds);

            $structure->setResetStores(true);
        }
    }

    public function getFilters()
    {
        return array(
            array('revision' =>  $this->_revisionId)
        );
    }
}