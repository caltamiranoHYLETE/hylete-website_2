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

class Vaimo_Cms_Helper_Group extends Vaimo_Cms_Helper_Abstract
{
    public function getAssignableStructureForGroup($group, $structure)
    {
        $factory = $this->getFactory();

        if ($group->ownsStructure($structure)) {
            return $structure;
        }

        $helper = $factory->getHelper('vaimo_cms/structure');
        $reference = $structure->getBlockReference();

        $currentStructure = $group->getStructureForReference($reference);

        if ($group->ownsStructure($currentStructure)) {
            return $helper->copyStructureData($structure, $currentStructure);
        }

        if ($structure->getId()) {
            $clone = $helper->getClone($structure);

            return $clone;
        }

        return $structure;
    }

    public function getLiveGroup($handle, $storeId)
    {
        $factory = $this->getFactory();

        $type = $factory->getModel('vaimo_cms/group_type_live');

        $model = $factory->getModel('vaimo_cms/revision', array(
            'handle' => $handle,
            'store' => $storeId,
            'type' => $type
        ));

        return $model;
    }

    public function getDraftGroup($handle, $storeId, $revisionId)
    {
        $factory = $this->getFactory();

        if (!Mage::getStoreConfigFlag(Vaimo_Cms_Helper_Data::XPATH_CONFIG_STAGING_ENABLED)) {
            return $this->getLiveGroup($handle, $storeId);
        }

        $type = $factory->getModel('vaimo_cms/group_type_draft', array(
            'revision' => $revisionId
        ));

        $model = $factory->getModel('vaimo_cms/revision', array(
            'handle' => $handle,
            'store' => $storeId,
            'type' => $type
        ));

        return $model;
    }
}