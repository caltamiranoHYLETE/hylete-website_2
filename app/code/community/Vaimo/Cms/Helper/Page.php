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

class Vaimo_Cms_Helper_Page extends Vaimo_Cms_Helper_Abstract
{
    const REVISION_PREFIX = '_REV';
    const DRAFT ='draft';

    public function getRevisionHandle($handle, $revisionId)
    {
        return strtoupper(self::REVISION_PREFIX . '_' . $revisionId . '_' . $handle);
    }

    public function createDraftForStore(Vaimo_Cms_Model_Page $source, $storeId)
    {
        $target = false;

        foreach ($source->getStructures() as $structure) {
            if ($target === false) {
                /** @var Vaimo_Cms_Model_Page $target */
                $target = $this->getFactory()->getModel('vaimo_cms/page', array(
                    'handle' => $structure->getHandle(),
                    'store' => $storeId
                ));
            }

            $target->assignStructure($structure, self::DRAFT);
        }

        return $target;
    }

    public function getStructureStoreView($structureId, $storeId)
    {
        $factory = $this->getFactory();

        /** @var Vaimo_Cms_Model_Structure $structure */
        $structure = $factory->getModel('vaimo_cms/structure')->load($structureId);

        /** @var Vaimo_Cms_Model_Page $page */
        $page = $factory->getModel('vaimo_cms/page', array(
            'handle' => $structure->getHandle(),
            'store' => $storeId
        ));

        $structure = $page->assignStructure($structure, self::DRAFT);

        if (!$structure->getId()) {
            $page->save();
        }

        return $structure;
    }

    public function publish($page, $revisionId)
    {
        $structures = $page->getStructures($revisionId);

        foreach ($structures as $reference => $structure) {
            $structure->setRevision(null);

            $page->assignStructure($structure);
        }

        $page->save();

        return $structures;
    }

    public function discard($page, $revisionId)
    {
        $structures = $page->getStructures($revisionId);

        $page->invalidate($revisionId)
            ->save();

        return array_merge($structures, array_intersect_key($page->getStructures(), $structures));
    }

    public function hasDraft($page, $revisionId)
    {
        $hasUnpublishedDrafts = false;

        foreach ($page->getStructures($revisionId) as $structure) {
            $hasUnpublishedDrafts = $hasUnpublishedDrafts || !$structure->getPublished();
        }

        return $hasUnpublishedDrafts;
    }

    public function createStructureBlocks(Vaimo_Cms_Model_Page $page, $layout, $revisionId = false)
    {
        $structureHelper = $this->getFactory()->getHelper('vaimo_cms/structure');

        foreach ($page->getStructures($revisionId) as $structure) {
            $structureHelper->createStructureBlock($structure, $layout);
        }
    }

    public function getStagedStructureForReference($page, $reference, $revisionId)
    {
        $structures = $page->getStageStructures($revisionId);

        if (!isset($structures[$reference])) {
            return false;
        }

        return $structures[$reference];
    }

    public function shouldAllowUpdate($structure, $storedStructure, $structureDataBefore)
    {
        if (!$storedStructure) {
            return true;
        }

        if (!$structureId = $structure->getId()) {
            return !count($storedStructure->getStructureData());
        }

        $structureHelper = Mage::helper('vaimo_cms/structure');

        if ($structureId == $storedStructure->getId()) {
            return !$structureHelper->hasPositionalDifferences(
                $structure->getOrigStructureData(),
                $structureDataBefore,
                'widget_page_id'
            );
        }

        return !$structureHelper->hasPositionalDifferences(
            $structure->getOrigStructureData(),
            $storedStructure->getStructureData(),
            'clone_of'
        );
    }
}