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

class Vaimo_Cms_Model_Group_Type_Live extends Vaimo_Cms_Model_Abstract
{
    public function assignStructure($structure)
    {
        $structure->setPublished(true);

        if ($structure->getOrigData('published') != $structure->getPublished()) {
            $structure->setWidgetHandle($structure->getHandle());
        }

        return $structure;
    }

    public function ownsStructure()
    {
        return true;
    }

    public function prepareStructuresForSave($structures)
    {
        foreach ($structures as $structure) {
            if ((bool)$structure->getPublished() != (bool)$structure->getOrigData('published')) {
                $structure->setDataChanges(true);
            }
        }
    }

    public function filterStructure()
    {
        return true;
    }

    public function getFilters()
    {
        return array(
            array('published' => true)
        );
    }
}